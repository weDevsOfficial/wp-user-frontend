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
    wpCli,
    getPostIdByTitle,
    getPostField,
    getPostMeta,
    setPostMeta,
    deletePostMeta,
    runCronHook,
    seedUserWithRole,
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

test.describe( 'Post-Form-Expiration-And-Edit-Lock', () => {
    configureSpecFailFast();

    /**--------------- POST EXPIRATION + EDIT LOCK (§8.6) ---------------**
     *
     * @TestScenario : [Settings → Post Expiration, and General → Lock User Editing]
     *
     * Expiry itself runs on the daily `wpuf_remove_expired_post_hook` cron
     * (wpuf-pro/includes/Post_Expiration.php), so the tests move the stored
     * expiration date into the past and fire the hook instead of waiting a day.
     *
     * @Test_PFG0050 : Expiration settings round-trip through Save
     * @Test_PFG0051 : An expired post flips to the configured status
     * @Test_PFG0052 : Duration types write the matching expiration date
     * @Test_PFG0053 : Expiration email is queued for the author (env-gated)
     * @Test_PFG0054 : Expiration disabled writes no expiry metadata
     * @Test_PFG0055 : Inside the edit window the post is still editable
     * @Test_PFG0056 : After the edit window the edit form is refused
     *
     */

    const formName = `EXP Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `exp-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const dashboardSlug = `exp-dash-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const editSlug = `exp-edit-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const expirationMessage = `Expired notice ${ faker.string.alphanumeric( 5 ) }`;
    const authorLogin = `expauth${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const authorPass = faker.internet.password( { length: 14 } );

    let formId: string;
    // Set by PFG0055, read by PFG0056 (serial spec).
    let lockPostTitle: string;

    test( 'PFG0050 : Expiration settings round-trip through Save', { tag: [ '@Pro', '@Test_PFG0050' ] }, async () => {
        test.skip( ! wpCliAvailable(), 'Expiration assertions need wp-cli (post meta + cron)' );

        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doSaveForm();
        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );

        await editor.doOpenSettingsSection( 'Post Expiration' );
        // Post expiration ships with wpuf-pro.
        test.skip( ! await editor.isSettingAvailable( 'enable_post_expiration' ), 'Post Expiration requires wpuf-pro' );

        await editor.doToggleSetting( 'enable_post_expiration', true );
        await editor.doFillSetting( 'expiration_time_value', '1' );
        await editor.doSelectSetting( 'expiration_time_type', 'day' );
        await editor.doSelectSetting( 'expired_post_status', 'draft' );
        await editor.doToggleSetting( 'enable_mail_after_expired', true );
        await editor.doFillSetting( 'post_expiration_message', expirationMessage );
        await editor.doSaveForm();

        await page.reload();
        await editor.doOpenSettingsSection( 'Post Expiration' );
        await editor.validateSettingChecked( 'enable_post_expiration', true );
        await editor.validateSettingText( 'expiration_time_value', '1' );
        await editor.validateSettingSelected( 'expiration_time_type', 'day' );
        await editor.validateSettingSelected( 'expired_post_status', 'draft' );
        await editor.validateSettingChecked( 'enable_mail_after_expired', true );
    } );

    // Set by PFG0051, reused by PFG0051a.
    let expiringPostId = 0;

    test( 'PFG0051 : The form expiration settings land on the submitted post', { tag: [ '@Pro', '@Test_PFG0051' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const title = `EXP Post ${ faker.string.alphanumeric( 5 ) }`;

        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        expiringPostId = getPostIdByTitle( title );
        expect( expiringPostId ).toBeGreaterThan( 0 );
        expect( getPostField( expiringPostId, 'post_status' ) ).toBe( 'publish' );
        expect( getPostMeta( expiringPostId, 'wpuf-expired_post_status' ) ).toBe( 'draft' );
    } );

    // BUG (see BUGS-FOUND.md #21): Frontend_Form_Ajax::wpuf_user_subscription_pack()
    // enters the subscription-pack branch on `isset($pack['_enable_post_expiration'])`
    // rather than on its value, so a pack with expiration switched OFF still writes
    // `wpuf-post_expiration_date = 1970-01-01` and the pack's `_expired_post_status`.
    // Pro's Post_Expiration::save_expiration_meta() then bails on the same isset()
    // test, so the form's own settings never reach the post. PFG0051 above passes
    // only because the submitting user holds no pack yet.
    test( 'PFG0051b : A pack with expiration off does not override the form', { tag: [ '@Pro', '@Test_PFG0051b' ] }, async () => {
        test.fail();
        const editor = new PostFormGapsPage( page );
        const title = `EXP Pack ${ faker.string.alphanumeric( 5 ) }`;

        // A completed pack that explicitly disables its own post expiration.
        const pack = {
            pack_id: '0',
            status: 'completed',
            expire: new Date( Date.now() + 86400000 ).toISOString().slice( 0, 19 ).replace( 'T', ' ' ),
            _enable_post_expiration: 'no',
            _post_expiration_time: '',
            _expired_post_status: 'publish',
        };

        try {
            wpCli( `user meta update ${ Users.adminUsername } _wpuf_subscription_pack '${ JSON.stringify( pack ) }' --format=json` );

            await editor.openFrontendForm( pageSlug );
            await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

            const postId = getPostIdByTitle( title );
            expect( postId ).toBeGreaterThan( 0 );
            // The form says draft / 1 day; the disabled pack must not overrule it.
            expect( getPostMeta( postId, 'wpuf-expired_post_status' ) ).toBe( 'draft' );
            expect( getPostMeta( postId, 'wpuf-post_expiration_date' ) ).not.toBe( '1970-01-01' );
        } finally {
            wpCli( `user meta delete ${ Users.adminUsername } _wpuf_subscription_pack` );
        }
    } );

    test( 'PFG0051a : The expiry cron flips a due post to its expired status', { tag: [ '@Pro', '@Test_PFG0051a' ] }, async () => {
        test.skip( ! expiringPostId, 'PFG0051 did not produce a post to expire' );

        // Seed both metas directly so this covers process_expired_posts() itself,
        // independent of the save-time defect PFG0051 records.
        const yesterday = new Date( Date.now() - 86400000 ).toISOString().slice( 0, 10 );
        setPostMeta( expiringPostId, 'wpuf-expired_post_status', 'draft' );
        setPostMeta( expiringPostId, 'wpuf-post_expiration_date', yesterday );
        deletePostMeta( expiringPostId, 'wpuf-post_expired' );

        runCronHook( 'wpuf_remove_expired_post_hook' );

        expect( getPostField( expiringPostId, 'post_status' ) ).toBe( 'draft' );
        // The guard meta stops the same post being processed twice.
        expect( getPostMeta( expiringPostId, 'wpuf-post_expired' ) ).not.toBe( '' );
    } );

    test( 'PFG0052 : Duration types write the matching expiration date', { tag: [ '@Pro', '@Test_PFG0052' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        // Post_Form.php offers exactly day / week / month — there is no "year".
        // `expiration_time_value` is 1 (set in PFG0050), so each type is one unit.
        for ( const type of [ 'week', 'month' ] as const ) {
            await editor.openFormEditor( formId );
            await editor.doOpenSettingsSection( 'Post Expiration' );
            await editor.doSelectSetting( 'expiration_time_type', type );
            await editor.doSaveForm();

            const title = `EXP ${ type } ${ faker.string.alphanumeric( 5 ) }`;
            await editor.openFrontendForm( pageSlug );
            await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

            const postId = getPostIdByTitle( title );

            // WPUF stores gmdate( 'Y-m-d', strtotime( '+1 <type>' ) ).
            const expected = new Date();

            if ( type === 'week' ) {
                expected.setDate( expected.getDate() + 7 );
                expect( getPostMeta( postId, 'wpuf-post_expiration_date' ) )
                    .toBe( expected.toISOString().slice( 0, 10 ) );
            } else {
                expected.setMonth( expected.getMonth() + 1 );
                // Compare on year-month: day-of-month rolls over for long months.
                expect( getPostMeta( postId, 'wpuf-post_expiration_date' ).slice( 0, 7 ) )
                    .toBe( expected.toISOString().slice( 0, 7 ) );
            }
        }

        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Post Expiration' );
        await editor.doSelectSetting( 'expiration_time_type', 'day' );
        await editor.doSaveForm();
    } );

    test( 'PFG0053 : Expiration email is queued for the author', { tag: [ '@Pro', '@Test_PFG0053' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const title = `EXP Mail ${ faker.string.alphanumeric( 5 ) }`;

        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        // The message meta is only written when "send email" is on, so it is the
        // stable pre-condition for the mail itself.
        expect( getPostMeta( postId, 'wpuf-post_expiration_message' ) ).toContain( expirationMessage );

        const yesterday = new Date( Date.now() - 86400000 ).toISOString().slice( 0, 10 );
        setPostMeta( postId, 'wpuf-post_expiration_date', yesterday );
        runCronHook( 'wpuf_remove_expired_post_hook' );

        // Reading the delivered mail needs a mail-log plugin; without one only the
        // status flip above is provable.
        await page.goto( new PostFormGapsPage( page ).wpMailLogPage ).catch( () => {} );
        const hasMailLog = await page.locator( '//table' ).first().isVisible().catch( () => false );
        test.skip( ! hasMailLog, 'No mail-log plugin on this site — expiration mail cannot be read' );

        await expect( page.locator( 'body' ) ).toContainText( title );
    } );

    test( 'PFG0054 : Expiration disabled writes no expiry metadata', { tag: [ '@Pro', '@Test_PFG0054' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Post Expiration' );
        await editor.doToggleSetting( 'enable_post_expiration', false );
        await editor.doSaveForm();

        const title = `EXP Off ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        expect( getPostMeta( postId, 'wpuf-post_expiration_date' ) ).toBe( '' );
        expect( getPostField( postId, 'post_status' ) ).toBe( 'publish' );
    } );

    /**------------------------- EDIT LOCK WINDOW -------------------------**/

    test( 'PFG0055 : Inside the edit window the post is still editable', { tag: [ '@Lite', '@Test_PFG0055' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const postForm = new PostFormPage( page );

        // The lock only applies to the post author, so the post is submitted by a
        // plain author account driving the dashboard → edit link.
        seedUserWithRole( authorLogin, `${ authorLogin }@example.test`, authorPass, 'author' );

        await postForm.createPageWithShortcodeGeneral( '[wpuf_dashboard]', dashboardSlug );
        await postForm.createPageWithShortcodeGeneral( '[wpuf_edit]', editSlug );
        // The dashboard builds its Edit href from this global option.
        await editor.doSetGlobalEditPage( editSlug );

        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doFillSetting( 'lock_edit_post', '2' );
        await editor.doSaveForm();

        const title = `LOCK Post ${ faker.string.alphanumeric( 5 ) }`;

        await editor.withUser( authorLogin, authorPass, async ( authorPage ) => {
            await authorPage.goto( `${ editor.siteHomePage }/${ pageSlug }/` );
            await authorPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
            await authorPage.locator( '//form[contains(@class,"wpuf-form")]//textarea[@name="post_content"]' )
                .fill( faker.lorem.paragraph() )
                .catch( async () => {
                    await authorPage.frameLocator( 'form[class*="wpuf-form"] iframe[id$="_ifr"]' ).locator( 'body' ).fill( faker.lorem.paragraph() );
                } );
            await authorPage.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).click();
            await expect( authorPage.locator( '//form[contains(@class,"wpuf-form")]' ) ).toBeHidden( { timeout: 60000 } );

            const href = await editor.getDashboardEditHref( authorPage, dashboardSlug, title );
            expect( href, 'the dashboard produced no Edit link — check Settings → Frontend Posting → Edit Page' ).toContain( 'pid=' );
            await editor.validateEditFormOpens( authorPage, href );
        } );

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );
        expect( Number( getPostMeta( postId, '_wpuf_lock_user_editing_post_time' ) ) ).toBeGreaterThan( Date.now() / 1000 );

        lockPostTitle = title;
    } );

    test( 'PFG0056 : After the edit window the edit form is refused', { tag: [ '@Lite', '@Test_PFG0056' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const title = lockPostTitle;
        const postId = getPostIdByTitle( title );

        // Move the stored lock timestamp into the past — same effect as waiting
        // out the configured hours.
        setPostMeta( postId, '_wpuf_lock_user_editing_post_time', String( Math.floor( Date.now() / 1000 ) - 60 ) );

        await editor.withUser( authorLogin, authorPass, async ( authorPage ) => {
            const href = await editor.getDashboardEditHref( authorPage, dashboardSlug, title );
            await editor.validateEditRefused( authorPage, href, /allocated time for editing this post has been expired/i );
        } );

        // Leave the shared form without an edit lock.
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doFillSetting( 'lock_edit_post', '0' );
        await editor.doSaveForm();
    } );
} );
