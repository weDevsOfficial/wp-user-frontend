import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import {
    wpCliAvailable,
    seedUserWithRole,
    seedUserSubscriptionPack,
    wpCli,
} from '../utils/wpEnvCli';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll( async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    page.on( 'dialog', async ( dialog ) => {
        await dialog.accept().catch( () => {} );
    } );
} );

test.describe( 'Post-Form-View-Control', () => {
    configureSpecFailFast();

    /**------------------------- VIEW CONTROL (§8.1) -------------------------**
     *
     * @TestScenario : [Post form → Settings → General → View Control]
     *
     * View Control filters `the_content` of the *submitted post* (wpuf-pro
     * Post_View_Control\Frontend), not the form page — so every assertion below
     * opens the created post, not the form. Administrators always pass.
     *
     * @Test_PFG0000 : Setup — form, page, and a post to guard
     * @Test_PFG0001 : Restrict by roles — an allowed role sees the content
     * @Test_PFG0002 : Restrict by roles — a disallowed role gets the message
     * @Test_PFG0003 : Restrict by roles — a logged-out visitor is blocked
     * @Test_PFG0004 : Restrict by packs — a subscriber holding the pack passes
     * @Test_PFG0005 : Restrict by packs — a user without the pack is blocked
     * @Test_PFG0006 : Both gates on — passing one is not enough
     *
     */

    const formName = `VC Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `vc-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const postTitle = `VC Post ${ faker.string.alphanumeric( 5 ) }`;
    const postBody = `VC body ${ faker.string.alphanumeric( 8 ) }`;
    const roleMessage = `Roles only ${ faker.string.alphanumeric( 5 ) }`;
    const packMessage = `Pack only ${ faker.string.alphanumeric( 5 ) }`;

    const editorLogin = `vced${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const subscriberLogin = `vcsub${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const userPass = faker.internet.password( { length: 14 } );

    let formId: string;
    let postUrl: string;
    let packName: string;
    let packId: number;

    test( 'PFG0000 : Setup — build the form, page and a post to guard', { tag: [ '@Pro', '@Test_PFG0000' ] }, async () => {
        // Roles/packs are seeded over wp-cli; without the wp-env container this
        // spec has no way to create the viewers it needs.
        test.skip( ! wpCliAvailable(), 'View control needs wp-cli to seed roles and packs' );

        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();

        // View Control ships with wpuf-pro.
        await editor.doOpenSettingsSection( 'General' );
        test.skip( ! await editor.isSettingAvailable( 'view_control_role_enabled' ), 'View Control requires wpuf-pro' );

        await editor.doOpenTab( 'Form Editor' );
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doSaveForm();

        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( postTitle, postBody );

        // Default redirection is "Newly created post", so the browser is parked
        // on the post itself — that URL is what every viewer below opens.
        postUrl = page.url().split( '?' )[ 0 ];
        expect( postUrl ).toContain( '/' );

        seedUserWithRole( editorLogin, `${ editorLogin }@example.test`, userPass, 'editor' );
        seedUserWithRole( subscriberLogin, `${ subscriberLogin }@example.test`, userPass, 'subscriber' );
    } );

    test( 'PFG0001 : Restrict by roles — an allowed role sees the content', { tag: [ '@Pro', '@Test_PFG0001' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doEnableRoleViewControl( 'Editor', roleMessage );

        await editor.withUser( editorLogin, userPass, async ( editorPage ) => {
            await editor.validatePostContentVisible( editorPage, postUrl, postBody );
        } );
    } );

    test( 'PFG0002 : Restrict by roles — a disallowed role gets the message', { tag: [ '@Pro', '@Test_PFG0002' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await editor.withUser( subscriberLogin, userPass, async ( subscriberPage ) => {
            await editor.validatePostRestricted( subscriberPage, postUrl, roleMessage, postBody );
        } );
    } );

    test( 'PFG0003 : Restrict by roles — a logged-out visitor is blocked', { tag: [ '@Pro', '@Test_PFG0003' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await editor.withGuest( async ( guestPage ) => {
            await editor.validatePostRestricted( guestPage, postUrl, roleMessage, postBody );
        } );
    } );

    test( 'PFG0004 : Restrict by packs — a subscriber holding the pack passes', { tag: [ '@Pro', '@Test_PFG0004' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        // A free pack is enough: view control only asks whether the user holds a
        // *completed* pack, which the seed writes straight to user meta.
        packName = `VC Pack ${ faker.string.alphanumeric( 5 ) }`;
        packId = Number(
            wpCli( `post create --post_type=wpuf_subscription --post_status=publish --post_title="${ packName }" --porcelain` )
                .trim()
                .split( /\s+/ )
                .pop()
        );
        expect( packId ).toBeGreaterThan( 0 );

        // Admin\Subscription::get_subscriptions() orders by `meta_key => '_sort_order'`,
        // and WP_Query drops posts missing that key — so a pack created straight
        // through wp-cli never reaches the View Control dropdown without it.
        wpCli( `post meta update ${ packId } _sort_order 1` );

        await editor.openFormEditor( formId );
        await editor.doDisableViewControl();
        await editor.doEnableSubscriptionViewControl( packName, packMessage );

        seedUserSubscriptionPack( subscriberLogin, packId );

        await editor.withUser( subscriberLogin, userPass, async ( subscriberPage ) => {
            await editor.validatePostContentVisible( subscriberPage, postUrl, postBody );
        } );
    } );

    test( 'PFG0005 : Restrict by packs — a user without the pack is blocked', { tag: [ '@Pro', '@Test_PFG0005' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        // The editor account was never given the pack.
        await editor.withUser( editorLogin, userPass, async ( editorPage ) => {
            await editor.validatePostRestricted( editorPage, postUrl, packMessage, postBody );
        } );
    } );

    test( 'PFG0006 : Both gates on — passing one is not enough', { tag: [ '@Pro', '@Test_PFG0006' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        // Role gate = Editor, pack gate = the pack only the subscriber holds, so
        // neither viewer can satisfy both.
        await editor.doEnableRoleViewControl( 'Editor', roleMessage );

        await editor.withUser( editorLogin, userPass, async ( editorPage ) => {
            await editor.validatePostRestricted( editorPage, postUrl, packMessage, postBody );
        } );

        await editor.withUser( subscriberLogin, userPass, async ( subscriberPage ) => {
            await editor.validatePostRestricted( subscriberPage, postUrl, roleMessage, postBody );
        } );

        // Hand the form back unrestricted for any later spec that reuses it.
        await editor.openFormEditor( formId );
        await editor.doDisableViewControl();
    } );
} );
