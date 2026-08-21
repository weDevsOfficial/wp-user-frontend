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
    wpufEncrypt,
} from '../utils/wpEnvCli';
import { Urls } from '../utils/testData';

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

test.describe( 'Post-Form-Guest-And-Setup', () => {
    configureSpecFailFast();

    /**--------------- GUEST POSTING + FORM SETUP (§8.7) ---------------**
     *
     * @TestScenario : [Posting Control → Guest Post, and General setup options]
     *
     * @Test_PFG0060 : A guest submission is held until the email is verified
     * @Test_PFG0061 : The verification link publishes the post
     * @Test_PFG0062 : Admin notification fires after verification, not at submit
     * @Test_PFG0063 : A tampered verification link is rejected
     * @Test_PFG0064 : The Post Form template ships its documented field set
     * @Test_PFG0065 : Default category applies when the form has no Category field
     * @Test_PFG0066 : A non-WooCommerce post type is created as that type
     * @Test_PFG0067 : Copying a field keeps the meta keys unique
     *
     */

    const guestFormName = `GP Form ${ faker.string.alphanumeric( 5 ) }`;
    const guestSlug = `gp-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const setupFormName = `SETUP Form ${ faker.string.alphanumeric( 5 ) }`;
    const setupSlug = `setup-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const guestEmail = `guest.${ faker.string.alphanumeric( 6 ) }@example.test`.toLowerCase();

    let guestFormId: string;
    let setupFormId: string;
    let guestPostTitle: string;
    let guestPostId: number;

    test( 'PFG0060 : A guest submission is held until the email is verified', { tag: [ '@Lite', '@Test_PFG0060' ] }, async () => {
        test.skip( ! wpCliAvailable(), 'Guest-post assertions need wp-cli (post author/status)' );

        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( guestFormName );

        const editor = new PostFormGapsPage( page );
        guestFormId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doSaveForm();

        await editor.doOpenSettingsSection( 'General' );
        await editor.doSelectSetting( 'post_permission', 'guest_post' );
        await editor.doToggleSetting( 'guest_details', true );
        await editor.doToggleSetting( 'guest_email_verify', true );
        // publish_guest_post only accepts draft/pending/auto-draft posts, so the
        // verification flow needs a non-published submission status.
        await editor.doSelectSetting( 'post_status', 'draft' );
        await editor.doSaveForm();

        await postForm.createPageWithShortcode( `[wpuf_form id="${ guestFormId }"]`, guestSlug );

        guestPostTitle = `GP Post ${ faker.string.alphanumeric( 5 ) }`;

        await editor.withGuest( async ( guestPage ) => {
            await guestPage.goto( `${ Urls.baseUrl }/${ guestSlug }/` );
            await guestPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="guest_name"]' ).fill( 'QA Guest' );
            await guestPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="guest_email"]' ).fill( guestEmail );
            await guestPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( guestPostTitle );

            const contentArea = guestPage.locator( '//form[contains(@class,"wpuf-form")]//textarea[@name="post_content"]' );

            if ( await contentArea.isVisible().catch( () => false ) ) {
                await contentArea.fill( faker.lorem.paragraph() );
            } else {
                await guestPage.frameLocator( 'form[class*="wpuf-form"] iframe[id$="_ifr"]' ).locator( 'body' ).fill( faker.lorem.paragraph() );
            }

            await guestPage.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).click();
            await expect( guestPage.locator( 'body' ) ).toContainText( /confirmation email/i, { timeout: 60000 } );
        } );

        guestPostId = getPostIdByTitle( guestPostTitle );
        expect( guestPostId ).toBeGreaterThan( 0 );
        // The submission is held, unpublished, until the email is verified.
        expect( [ 'draft', 'pending' ] ).toContain( getPostField( guestPostId, 'post_status' ) );
    } );

    // BUG (see BUGS-FOUND.md #24): Frontend_Form::publish_guest_post() refuses to
    // verify unless `post_author === 0`, but Frontend_Form_Ajax::set_post_author()
    // always assigns an author for a guest post — the freshly created user when
    // `guest_details` is on, `default_post_owner` when it is off. The two halves
    // contradict each other, so guest email verification can never succeed.
    test( 'PFG0060a : An unverified guest post stays authorless', { tag: [ '@Lite', '@Test_PFG0060a' ] }, async () => {
        test.skip( ! guestPostId, 'PFG0060 did not produce a guest submission' );
        test.fail();
        expect( getPostField( guestPostId, 'post_author' ) ).toBe( '0' );
    } );

    // Blocked by the same defect as PFG0060a — the guard rejects every guest post
    // because none of them is authorless.
    test( 'PFG0061 : The verification link publishes the post', { tag: [ '@Lite', '@Test_PFG0061' ] }, async () => {
        test.fail();
        const editor = new PostFormGapsPage( page );

        // WPUF mails `?post_msg=verified&p_id=<enc>&f_id=<enc>`; rebuilding it with
        // the site's own wpuf_encryption() proves the flow without an SMTP stack.
        const encodedPost = encodeURIComponent( wpufEncrypt( guestPostId ) );
        const encodedForm = encodeURIComponent( wpufEncrypt( guestFormId ) );
        const verifyUrl = `${ Urls.baseUrl }/?post_msg=verified&p_id=${ encodedPost }&f_id=${ encodedForm }`;

        await editor.withGuest( async ( guestPage ) => {
            await guestPage.goto( verifyUrl );
            await expect( guestPage.locator( 'body' ) ).toContainText( /Email successfully verified/i );
        } );

        expect( getPostField( guestPostId, 'post_status' ) ).toBe( 'draft' );
    } );

    test( 'PFG0062 : Admin notification fires after verification, not at submit', { tag: [ '@Lite', '@Test_PFG0062' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await page.goto( editor.wpMailLogPage ).catch( () => {} );
        const hasMailLog = await page.locator( '//table' ).first().isVisible().catch( () => false );
        // `wpuf_guest_post_email_verified` sends the admin mail; without a mail-log
        // plugin there is nothing to read it back from.
        test.skip( ! hasMailLog, 'No mail-log plugin on this site — guest notification cannot be read' );

        // Blocked by BUGS-FOUND #24: verification never completes, so the
        // post-verification admin notification is never sent.
        test.fail();
        await expect( page.locator( 'body' ) ).toContainText( guestPostTitle );
    } );

    test( 'PFG0063 : A tampered verification link is rejected', { tag: [ '@Lite', '@Test_PFG0063' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const verifyUrl = `${ Urls.baseUrl }/?post_msg=verified&p_id=not-a-real-payload&f_id=also-bogus`;

        await editor.withGuest( async ( guestPage ) => {
            await guestPage.goto( verifyUrl );
            await expect( guestPage.locator( 'body' ) ).toContainText( /Invalid post|cannot be published/i );
        } );
    } );

    /**------------------------- FORM SETUP OPTIONS -------------------------**/

    test( 'PFG0064 : The Post Form template ships its documented field set', { tag: [ '@Lite', '@Test_PFG0064' ] }, async () => {
        const postForm = new PostFormPage( page );
        const editor = new PostFormGapsPage( page );

        await postForm.createPresetPostForm( `PRESET ${ faker.string.alphanumeric( 5 ) }` );
        // The shipped "Post Form" preset is the post-fields + taxonomy baseline.
        await editor.validateStageHasFieldTypes( [ 'post_title', 'post_content', 'post_excerpt', 'featured_image' ] );
    } );

    test( 'PFG0065 : Default category applies when the form has no Category field', { tag: [ '@Lite', '@Test_PFG0065' ] }, async () => {
        const postForm = new PostFormPage( page );
        const editor = new PostFormGapsPage( page );

        await postForm.createBlankFormPostForm( setupFormName );
        setupFormId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doSaveForm();

        await editor.doOpenSettingsSection( 'General' );
        await editor.doPickMultiSelectSetting( 'default_category_select', 'Uncategorized' );
        await editor.doSaveForm();

        await postForm.createPageWithShortcode( `[wpuf_form id="${ setupFormId }"]`, setupSlug );

        const title = `DEFCAT ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( setupSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        expect( wpCli( `post term list ${ postId } category --field=name` ) ).toContain( 'Uncategorized' );
    } );

    test( 'PFG0066 : A non-WooCommerce post type is created as that type', { tag: [ '@Lite', '@Test_PFG0066' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await editor.openFormEditor( setupFormId );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doSelectSetting( 'post_type', 'page' );
        await editor.doSaveForm();

        const title = `CPT ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( setupSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        expect( getPostIdByTitle( title, 'page' ) ).toBeGreaterThan( 0 );
        expect( getPostIdByTitle( title, 'post' ) ).toBe( 0 );

        await editor.openFormEditor( setupFormId );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doSelectSetting( 'post_type', 'post' );
        await editor.doSaveForm();
    } );

    test( 'PFG0067 : Copying a field keeps the meta keys unique', { tag: [ '@Lite', '@Test_PFG0067' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await editor.openFormEditor( setupFormId );
        await editor.doAddField( 'Text' );
        await editor.doEditField( 'text_field' );
        await editor.validateLabelUpdatesStage( 'text_field', 'Copy Source' );
        await editor.validateCopyDuplicatesField( 'text_field' );
        await editor.doSaveForm();

        await editor.openFrontendForm( setupSlug );

        // Two text controls, two distinct names — a shared meta key would make the
        // second value overwrite the first on submit.
        const names = await page
            .locator( '//form[contains(@class,"wpuf-form")]//li[contains(@class,"wpuf-el")]//input[@type="text"][not(@name="post_title")]' )
            .evaluateAll( ( inputs ) => inputs.map( ( input ) => ( input as HTMLInputElement ).name ) );

        expect( names.length ).toBeGreaterThanOrEqual( 2 );
        expect( new Set( names ).size ).toBe( names.length );

        const title = `COPY ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );

        for ( const [ index, name ] of names.entries() ) {
            await page.locator( `//form[contains(@class,"wpuf-form")]//input[@name="${ name }"]` ).first().fill( `value-${ index }` );
        }

        await editor.doSubmitFrontendForm();

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );

        for ( const [ index, name ] of names.entries() ) {
            expect( wpCli( `post meta get ${ postId } ${ name }` ).trim() ).toBe( `value-${ index }` );
        }
    } );
} );
