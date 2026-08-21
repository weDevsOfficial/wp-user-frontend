import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users, PostForm } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import { wpCliAvailable, wpCli, getPostIdByTitle, installHookProbe, removeHookProbe } from '../utils/wpEnvCli';

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

test.describe( 'Post-Form-Field-Behaviour', () => {
    configureSpecFailFast();

    /**------------------ STRUCTURAL / VALUE FIELDS (§8.8) ------------------**
     *
     * @TestScenario : [Fields whose behaviour, not just their stage row, matters]
     *
     * The existing allFieldTypes spec proves every field can be added and
     * rendered. These tests prove what each of them actually *does*.
     *
     * @Test_PFG0070 : Section Break prints its title and description
     * @Test_PFG0071 : Custom HTML output reaches the rendered form
     * @Test_PFG0072 : A Shortcode field is executed, not printed
     * @Test_PFG0073 : An Action Hook field fires its hook (probe env-gated)
     * @Test_PFG0074 : Columns render as a multi-column row (@Pro)
     * @Test_PFG0075 : A required Terms & Conditions blocks submit (@Pro)
     * @Test_PFG0076 : A Ratings value is stored on the post (@Pro)
     * @Test_PFG0077 : An Embed value is stored on the post (@Pro)
     * @Test_PFG0078 : Hidden Field writes its value to post meta
     * @Test_PFG0079 : File Upload rejects a file over the size limit (@Pro)
     * @Test_PFG0080 : Checkbox / Radio / Multi Select values round-trip
     * @Test_PFG0081 : Repeat Field renders on the frontend (known bug)
     *
     */

    const formName = `FB Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `fb-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const sectionTitle = `Section ${ faker.string.alphanumeric( 5 ) }`;
    const sectionDescription = `Describes ${ faker.string.alphanumeric( 5 ) }`;
    const htmlMarker = `QA_HTML_${ faker.string.alphanumeric( 5 ) }`;
    const hiddenKey = `qa_hidden_${ faker.string.alphanumeric( 4 ) }`.toLowerCase();
    const hiddenValue = `hidden-${ faker.string.alphanumeric( 6 ) }`;

    let formId: string;

    test( 'PFG0070 : Section Break prints its title and description', { tag: [ '@Lite', '@Test_PFG0070' ] }, async () => {
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doAddField( 'Section Break' );
        await editor.doEditField( 'section_break' );
        await editor.doSetFieldOptionInput( 'Title', sectionTitle );
        await editor.doSetFieldOptionTextarea( 'Description', sectionDescription );
        await editor.doSaveForm();

        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        await editor.openFrontendForm( pageSlug );
        await editor.validateFrontendText( sectionTitle );
        await editor.validateFrontendText( sectionDescription );
    } );

    test( 'PFG0071 : Custom HTML output reaches the rendered form', { tag: [ '@Lite', '@Test_PFG0071' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doAddField( 'Custom HTML' );
        await editor.doEditField( 'custom_html' );
        await editor.doSetFieldOptionTextarea( 'Html Codes', `<div id="qa-html-probe">${ htmlMarker }</div>` );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await expect( page.locator( '#qa-html-probe' ) ).toHaveText( htmlMarker );
    } );

    test( 'PFG0072 : A Shortcode field is executed, not printed', { tag: [ '@Lite', '@Test_PFG0072' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doAddField( 'Shortcode' );
        await editor.doEditField( 'shortcode' );
        // [wpuf-login] is always registered, so it needs no test-only plugin.
        await editor.doSetFieldOptionInput( 'Shortcode', '[wpuf-login]' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        // Executed → its own markup/notice shows and the raw tag never does.
        await editor.validateProbeRendered( 'logged in', '[wpuf-login]' );
    } );

    test( 'PFG0073 : An Action Hook field fires its hook', { tag: [ '@Lite', '@Test_PFG0073' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        // A hook only proves itself when something is listening, so the test drops
        // its own mu-plugin listener instead of depending on the environment.
        const hookName = `wpuf_qa_hook_${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
        const marker = `QA-HOOK-${ faker.string.alphanumeric( 8 ) }`;
        const probed = installHookProbe( hookName, marker );

        try {
            await editor.openFormEditor( formId );
            await editor.doAddField( 'Action Hook' );
            await editor.doEditField( 'action_hook' );
            await editor.doSetFieldOptionInput( 'Hook Name', hookName );
            await editor.doSaveForm();

            await editor.openFrontendForm( pageSlug );
            await expect( page.locator( 'body' ) ).not.toContainText( hookName );

            if ( probed ) {
                await editor.validateProbeRendered( marker );
            } else {
                // No wp-env CLI (e.g. a QA_BASE_URL run against a remote site), so
                // only the "hook name is not leaked to visitors" half is assertable.
                console.log( 'no wp-cli available — skipped the hook-fires assertion' );
            }
        } finally {
            removeHookProbe();
        }
    } );

    test( 'PFG0074 : Columns render as a multi-column row', { tag: [ '@Pro', '@Test_PFG0074' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Columns' ), 'Columns requires wpuf-pro' );

        await editor.doAddField( 'Columns' );
        await editor.validateFieldOnStage( 'column_field' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        // The template wraps each column in its own container.
        expect(
            await editor.countFrontendMatches( '//form[contains(@class,"wpuf-form")]//*[contains(@class,"wpuf-column")]' )
        ).toBeGreaterThan( 1 );
    } );

    test( 'PFG0075 : A required Terms & Conditions blocks submit', { tag: [ '@Pro', '@Test_PFG0075' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Terms & Conditions' ), 'Terms & Conditions requires wpuf-pro' );

        await editor.doAddField( 'Terms & Conditions' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        const tocTitle = `TOC ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( tocTitle );
        await editor.doFillPostContent( faker.lorem.paragraph() );

        // Field_Toc renders a native `required` checkbox, so the browser blocks
        // the submit itself — there is no WPUF-rendered error string to read.
        const consent = page.locator( '//form[contains(@class,"wpuf-form")]//input[@type="checkbox"][@required]' ).first();
        await expect( consent, 'the Terms & Conditions checkbox is not required' ).toHaveCount( 1 );
        expect( await consent.evaluate( ( el: HTMLInputElement ) => el.checkValidity() ) ).toBeFalsy();

        await editor.doSubmitFrontendForm();

        // Nothing was submitted: the form is still on screen and no post exists.
        await expect( page.locator( '//form[contains(@class,"wpuf-form")]' ) ).toBeVisible();

        if ( wpCliAvailable() ) {
            expect( getPostIdByTitle( tocTitle ) ).toBe( 0 );
        }

        // Accepting it clears the block.
        await consent.check();
        expect( await consent.evaluate( ( el: HTMLInputElement ) => el.checkValidity() ) ).toBeTruthy();
    } );

    test( 'PFG0076 : A Ratings value is stored on the post', { tag: [ '@Pro', '@Test_PFG0076' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        test.skip( ! wpCliAvailable(), 'Reading the stored rating needs wp-cli' );

        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Ratings' ), 'Ratings requires wpuf-pro' );

        // Terms & Conditions from the previous test would block every submit below.
        await editor.doRemoveField( 'toc' ).catch( () => {} );
        await editor.doAddField( 'Ratings' );
        await editor.doEditField( 'ratings' );
        const ratingKey = await page.locator( '(//label[normalize-space()="Meta Key"]/following::input)[1]' ).inputValue();
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        const title = `RATING ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );

        // Field_Rating renders a <select class="wpuf-ratings"> that jQuery Bar
        // Rating hides behind `.br-widget a[data-rating-value]` stars. Click the
        // star the visitor would; fall back to the hidden select when the widget
        // did not initialise (force, since barrating hides the select).
        const star = page.locator(
            `${ editor.frontendRow( 'Ratings' ) }//div[contains(@class,"br-widget")]//a[@data-rating-value="4"]`
        ).first();

        if ( await star.isVisible().catch( () => false ) ) {
            await star.click();
        } else {
            await page.locator( `${ editor.frontendRow( 'Ratings' ) }//select[contains(@class,"wpuf-ratings")]` )
                .first()
                .selectOption( '4', { force: true } );
        }

        await editor.doSubmitFrontendForm();

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );
        expect( wpCli( `post meta get ${ postId } ${ ratingKey }` ).trim() ).toBe( '4' );
    } );

    test( 'PFG0077 : An Embed value is stored on the post', { tag: [ '@Pro', '@Test_PFG0077' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        test.skip( ! wpCliAvailable(), 'Reading the stored embed needs wp-cli' );

        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Embed' ), 'Embed requires wpuf-pro' );

        await editor.doAddField( 'Embed' );
        await editor.doEditField( 'embed' );
        const embedKey = await page.locator( '(//label[normalize-space()="Meta Key"]/following::input)[1]' ).inputValue();
        await editor.doSaveForm();

        const embedUrl = 'https://wordpress.tv/2023/01/01/example/';
        const title = `EMBED ${ faker.string.alphanumeric( 5 ) }`;

        await editor.openFrontendForm( pageSlug );
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );
        await page.locator( `${ editor.frontendRow( 'Embed' ) }//input | ${ editor.frontendRow( 'Embed' ) }//textarea` ).first().fill( embedUrl );
        await editor.doSubmitFrontendForm();

        const postId = getPostIdByTitle( title );
        expect( wpCli( `post meta get ${ postId } ${ embedKey }` ) ).toContain( 'wordpress.tv' );
    } );

    test( 'PFG0078 : Hidden Field writes its value to post meta', { tag: [ '@Lite', '@Test_PFG0078' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        test.skip( ! wpCliAvailable(), 'Reading the hidden value needs wp-cli' );

        await editor.openFormEditor( formId );
        await editor.doAddField( 'Hidden Field' );
        await editor.doEditField( 'custom_hidden_field' );
        await editor.doSetFieldOptionInput( 'Meta Key', hiddenKey );
        await editor.doSetFieldOptionInput( 'Meta Value', hiddenValue );
        await editor.doSaveForm();

        const title = `HIDDEN ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        expect( wpCli( `post meta get ${ postId } ${ hiddenKey }` ).trim() ).toBe( hiddenValue );
    } );

    test( 'PFG0079 : File Upload rejects a file over the size limit', { tag: [ '@Pro', '@Test_PFG0079' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'File Upload' ), 'File Upload requires wpuf-pro' );

        await editor.doAddField( 'File Upload' );
        await editor.doEditField( 'file_upload' );
        await editor.doExpandAdvancedOptions();
        // The size cap is optional per build; without the control there is nothing
        // to assert here.
        test.skip( ! await editor.isFieldOptionAvailable( 'Max. file size' ), 'This build has no Max. file size option' );

        await editor.doSetFieldOptionInput( 'Max. file size', '1' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await page.setInputFiles( `${ editor.frontendRow( 'File Upload' ) }//input[@type="file"]`, PostForm.imageUpload );

        await expect
            .poll( async () => ( await editor.getVisibleValidationErrors() ).join( ' ' ), { timeout: 30000 } )
            .toMatch( /size|large|exceed/i );
    } );

    test( 'PFG0080 : Checkbox / Radio / Multi Select values round-trip', { tag: [ '@Lite', '@Test_PFG0080' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        test.skip( ! wpCliAvailable(), 'Reading the stored choices needs wp-cli' );

        await editor.openFormEditor( formId );
        await editor.doRemoveField( 'file_upload' ).catch( () => {} );

        for ( const label of [ 'Checkbox', 'Radio', 'Multi Select' ] ) {
            await editor.doAddField( label );
        }

        await editor.doSaveForm();
        await editor.openFrontendForm( pageSlug );

        // Every one of the three renders its own control type with options.
        await editor.validateFrontendRow( 'Checkbox', 'input[type="checkbox"]' );
        await editor.validateFrontendRow( 'Radio', 'input[type="radio"]' );
        await editor.validateFrontendRow( 'Multi Select', 'select[multiple], .custom-multiselect' );

        const title = `CHOICE ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );
        await page.locator( `${ editor.frontendRow( 'Checkbox' ) }//input[@type="checkbox"]` ).first().check( { force: true } );
        await page.locator( `${ editor.frontendRow( 'Radio' ) }//input[@type="radio"]` ).first().check( { force: true } );

        const checkboxName = ( await page.locator( `${ editor.frontendRow( 'Checkbox' ) }//input[@type="checkbox"]` ).first().getAttribute( 'name' ) ) || '';
        const radioName = ( await page.locator( `${ editor.frontendRow( 'Radio' ) }//input[@type="radio"]` ).first().getAttribute( 'name' ) ) || '';

        await editor.doSubmitFrontendForm();

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );

        for ( const name of [ checkboxName, radioName ] ) {
            const key = name.replace( /\[\]$/, '' );
            expect( wpCli( `post meta get ${ postId } ${ key }` ).trim(), `no value stored for ${ key }` ).not.toBe( '' );
        }
    } );

    // KNOWN BUG (BUGS-FOUND.md): the builder saves the Repeat Field with
    // input_type "repeat" while the renderer is registered as "repeat_field", so
    // the field never reaches the frontend. test.fail() keeps this honest — it
    // turns red the day the mismatch is fixed.
    test( 'PFG0081 : Repeat Field renders on the frontend', { tag: [ '@Pro', '@Test_PFG0081' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Repeat Field' ), 'Repeat Field requires wpuf-pro' );

        test.fail();
        await editor.doAddField( 'Repeat Field' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await editor.validateFrontendRow( 'Repeat Field', 'input' );
    } );
} );
