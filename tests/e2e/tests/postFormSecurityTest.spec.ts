import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users, Urls } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import { wpCliAvailable, seedUserWithRole } from '../utils/wpEnvCli';

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

test.describe( 'Post-Form-Security', () => {
    configureSpecFailFast();

    /**--------------------- SECURITY / API (§8.10) ---------------------**
     *
     * @TestScenario : [Form-save guards and escaping of admin-entered strings]
     *
     * The builder saves over admin-ajax (`wpuf_form_builder_save_form`), guarded
     * by the `wpuf_form_builder_save_form` nonce plus `current_user_can(
     * wpuf_admin_role() )` — includes/Ajax/Admin_Form_Builder_Ajax.php.
     *
     * @Test_PFG0100 : Saving without a nonce is refused
     * @Test_PFG0101 : Saving with an invalid nonce is refused
     * @Test_PFG0102 : Saving as a non-admin is refused
     * @Test_PFG0103 : A malformed field payload does not corrupt the form
     * @Test_PFG0104 : Script payloads in label / help / placeholder are escaped
     * @Test_PFG0105 : Script payloads in the unauthorized messages are escaped (@Pro)
     * @Test_PFG0106 : Custom HTML renders as authored (documented behaviour)
     * @Test_PFG0107 : A missing captcha answer blocks submit (env-gated)
     *
     */

    const formName = `SEC Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `sec-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const xssPayload = '<script>window.qaXssFired = true;</script>';
    const labelMarker = `QA_LABEL_${ faker.string.alphanumeric( 5 ) }`;
    const subscriberLogin = `secsub${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const subscriberPass = faker.internet.password( { length: 14 } );

    let formId: string;
    const ajaxUrl = `${ Urls.baseUrl }/wp-admin/admin-ajax.php`;

    // The builder page carries the nonce the ajax handler demands.
    const readBuilderNonce = async ( target: Page ): Promise<string> =>
        target.locator( '//input[@name="wpuf_form_builder_nonce"]' ).first().inputValue().catch( () => '' );

    test( 'PFG0100 : Saving without a nonce is refused', { tag: [ '@Lite', '@Test_PFG0100' ] }, async () => {
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doAddField( 'Text' );
        await editor.doSaveForm();
        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );

        const response = await page.request.post( ajaxUrl, {
            form: {
                action: 'wpuf_form_builder_save_form',
                form_data: `wpuf_form_id=${ formId }&post_title=${ formName }`,
            },
        } );

        const body = await response.json();
        expect( body.success, 'a nonce-less save must not succeed' ).toBeFalsy();
        expect( JSON.stringify( body ) ).toMatch( /Unauthorized/i );
    } );

    test( 'PFG0101 : Saving with an invalid nonce is refused', { tag: [ '@Lite', '@Test_PFG0101' ] }, async () => {
        const response = await page.request.post( ajaxUrl, {
            form: {
                action: 'wpuf_form_builder_save_form',
                form_data: `wpuf_form_builder_nonce=deadbeef&wpuf_form_id=${ formId }&post_title=${ formName }`,
            },
        } );

        const body = await response.json();
        expect( body.success ).toBeFalsy();
        expect( JSON.stringify( body ) ).toMatch( /Unauthorized/i );
    } );

    test( 'PFG0102 : Saving as a non-admin is refused', { tag: [ '@Lite', '@Test_PFG0102' ] }, async () => {
        test.skip( ! wpCliAvailable(), 'Needs wp-cli to seed the subscriber account' );

        const editor = new PostFormGapsPage( page );
        seedUserWithRole( subscriberLogin, `${ subscriberLogin }@example.test`, subscriberPass, 'subscriber' );

        // Even with a syntactically valid-looking payload the capability check
        // must stop a subscriber.
        const nonce = await readBuilderNonce( page );

        const body = await editor.withUser( subscriberLogin, subscriberPass, async ( subscriberPage ) => {
            const response = await subscriberPage.request.post( ajaxUrl, {
                form: {
                    action: 'wpuf_form_builder_save_form',
                    form_data: `wpuf_form_builder_nonce=${ nonce }&wpuf_form_id=${ formId }&post_title=hijacked`,
                },
            } );

            return response.json();
        } );

        expect( body.success ).toBeFalsy();

        // The form itself is untouched.
        await editor.openFormEditor( formId );
        await expect( page.locator( '//form[@id="wpuf-form-builder"]//input[@name="post_title"]' ) ).toHaveValue( formName );
    } );

    test( 'PFG0103 : A malformed field payload does not corrupt the form', { tag: [ '@Lite', '@Test_PFG0103' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        const nonce = await readBuilderNonce( page );
        expect( nonce, 'no builder nonce on the page' ).not.toBe( '' );

        const response = await page.request.post( ajaxUrl, {
            form: {
                action: 'wpuf_form_builder_save_form',
                form_data: `wpuf_form_builder_nonce=${ nonce }&wpuf_form_id=${ formId }&post_title=${ formName }`,
                // A field with no input_type and an unknown template.
                'form_fields[0][label]': 'Broken',
                'form_fields[0][template]': 'definitely_not_a_field',
            },
        } );

        expect( response.status() ).toBe( 200 );
        await expect( page.locator( 'body' ) ).not.toContainText( 'Fatal error' );

        // The handler replaces the whole settings blob with whatever `form_data`
        // carries, so this deliberately minimal payload also clears the form's
        // settings — the frontend then renders the restriction notice rather than
        // the form. That is the documented save contract, not corruption, and it
        // cannot be avoided from the browser: the settings panel mounts only the
        // active section (`v-if="active_settings_tab === ..."`), so the DOM never
        // holds a complete settings payload to echo back. What must hold is that
        // the malformed field neither fatals nor leaves the builder unopenable.
        await editor.openFormEditor( formId );
        await editor.validateEditorShell();
        await expect( page.locator( 'body' ) ).not.toContainText( 'Fatal error' );
        await expect( page.locator( 'body' ) ).not.toContainText( 'There has been a critical error' );

        // The builder must still save cleanly afterwards, i.e. the form is not
        // wedged into an unrecoverable state by the bad field.
        await editor.doSaveForm();

        // Restore the posting permission the wiped payload cleared, so the rest of
        // this spec still has a form the frontend will render.
        await editor.doOpenSettingsSection( 'Posting Control' );
        await editor.doSelectSetting( 'post_permission', 'everyone' );
        await editor.doSaveForm();
        await editor.openFrontendForm( pageSlug );
    } );

    test( 'PFG0104 : Script payloads in label / help / placeholder are escaped', { tag: [ '@Lite', '@Test_PFG0104' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doEditField( 'text_field' );
        await editor.validateLabelUpdatesStage( 'text_field', `${ labelMarker }${ xssPayload }` );
        await editor.validateHelpTextUpdatesStage( 'text_field', `help ${ xssPayload }` );
        await editor.doExpandAdvancedOptions();
        await editor.doSetFieldOptionInput( 'Placeholder text', `hint ${ xssPayload }` );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );

        // The payload is visible as text, never executed.
        const fired = await page.evaluate( () => ( window as any ).qaXssFired === true );
        expect( fired, 'a field option payload executed on the frontend' ).toBeFalsy();
        expect( await page.locator( '//form[contains(@class,"wpuf-form")]//script' ).count() ).toBe( 0 );
        await editor.validateFrontendText( labelMarker );
    } );

    test( 'PFG0105 : Script payloads in the unauthorized messages are escaped', { tag: [ '@Pro', '@Test_PFG0105' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'General' );
        test.skip( ! await editor.isSettingAvailable( 'view_control_role_enabled' ), 'View Control requires wpuf-pro' );
        test.skip( ! wpCliAvailable(), 'Needs wp-cli to seed a blocked viewer' );

        const marker = `QA_MSG_${ faker.string.alphanumeric( 5 ) }`;
        await editor.doEnableRoleViewControl( 'Editor', `${ marker }${ xssPayload }` );

        const title = `SEC Post ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );
        const postUrl = page.url().split( '?' )[ 0 ];

        seedUserWithRole( subscriberLogin, `${ subscriberLogin }@example.test`, subscriberPass, 'subscriber' );

        await editor.withUser( subscriberLogin, subscriberPass, async ( subscriberPage ) => {
            await subscriberPage.goto( postUrl );
            await expect( subscriberPage.locator( '//div[contains(@class,"wpuf-restricted-content")]' ).first() ).toContainText( marker );
            expect( await subscriberPage.evaluate( () => ( window as any ).qaXssFired === true ) ).toBeFalsy();
        } );

        await editor.openFormEditor( formId );
        await editor.doDisableViewControl();
    } );

    test( 'PFG0106 : Custom HTML renders as authored', { tag: [ '@Lite', '@Test_PFG0106' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const marker = `QA_RAW_${ faker.string.alphanumeric( 5 ) }`;

        await editor.openFormEditor( formId );
        await editor.doAddField( 'Custom HTML' );
        await editor.doEditField( 'custom_html' );
        // Custom HTML is an admin-only field and is rendered raw by design; this
        // test locks that behaviour in, so a future change to escaping is caught.
        await editor.doSetFieldOptionTextarea( 'Html Codes', `<div id="qa-raw-probe" data-qa="${ marker }">${ marker }</div>` );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await expect( page.locator( '#qa-raw-probe' ) ).toHaveText( marker );
    } );

    test( 'PFG0107 : A missing captcha answer blocks submit', { tag: [ '@Pro', '@Test_PFG0107' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Math Captcha' ), 'Math Captcha requires wpuf-pro' );

        await editor.doAddField( 'Math Captcha' );
        await editor.doSaveForm();
        await editor.openFrontendForm( pageSlug );

        // Captcha fields that need external keys (reCaptcha, Turnstile, Really
        // Simple Captcha) render nothing without them — Math Captcha is the one
        // that always renders, so it is the assertable spam gate.
        test.skip( ! await editor.checkFrontendRow( 'Math Captcha' ), 'Math Captcha did not render on this environment' );

        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( `CAPTCHA ${ faker.string.alphanumeric( 5 ) }` );
        await editor.doFillPostContent( faker.lorem.paragraph() );
        await editor.doAnswerMathCaptcha( '999999' );
        await editor.doSubmitFrontendForm();

        const errors = await editor.getVisibleValidationErrors();
        expect( errors.join( ' ' ) ).toMatch( /captcha|answer|wrong/i );
        await expect( page.locator( '//form[contains(@class,"wpuf-form")]' ) ).toBeVisible();
    } );
} );
