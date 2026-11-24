import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { FormEditorPage } from '../pages/formEditor';
import { Users, PostForm } from '../utils/testData';
import { FieldTypes, fieldTypesByGroup } from '../utils/fieldTypes';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll( async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    // The builder guards navigation once the form is dirty.
    page.on( 'dialog', async ( dialog ) => {
        await dialog.accept().catch( () => {} );
    } );
} );

test.describe( 'All-Field-Types', () => {
    configureSpecFailFast();

    /**------------------------- ALL FIELD TYPES ---------------------------**
     *
     * @TestScenario : [One post form holding every field type the builder offers]
     *
     * Builds a single form with all 44 field types, then validates them in the
     * builder (stage) and on the rendered frontend form. Types the install does
     * not offer (Pro fields on a Lite/unlicensed site) are reported and skipped
     * rather than failing the run.
     *
     * @Test_AF0001 : Every field type in the catalogue is added to one form
     * @Test_AF0002 : Post Fields render on the stage
     * @Test_AF0003 : Taxonomy fields render on the stage
     * @Test_AF0004 : Custom fields render on the stage
     * @Test_AF0005 : Pricing fields render on the stage
     * @Test_AF0006 : "Others" fields render on the stage
     * @Test_AF0007 : The field set survives a save + reload
     * @Test_AF0008 : Frontend renders a row and control for every core field type
     * @Test_AF0009 : Frontend renders the multistep wrapper for Step Start
     * @Test_AF0010 : Environment-dependent fields are reported (captchas, map, cart total)
     *
     */

    // Reuse an existing all-fields fixture when AF_FORM_ID / AF_PAGE_SLUG are set —
    // rebuilding 40 fields through the builder takes minutes.
    const existingFormId = process.env.AF_FORM_ID || '';
    const existingPageSlug = process.env.AF_PAGE_SLUG || '';

    const formName = `AF All Fields ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = existingPageSlug || `af-all-fields-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const dashboardSlug = `af-dashboard-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const editPageSlug = `af-edit-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const postTitle = `AF Post ${ faker.string.alphanumeric( 5 ) }`;

    let entered: Record<string, string> = {};
    let formId: string;
    let added: string[] = [];
    let unavailable: string[] = [];
    let refused: Record<string, string> = {};

    const availableInGroup = ( group: Parameters<typeof fieldTypesByGroup>[ 0 ] ) =>
        fieldTypesByGroup( group ).filter( ( field ) => added.includes( field.slug ) );

    // Taxonomy buttons save under a different template name than their slug.
    const stageSlugs = ( group: Parameters<typeof fieldTypesByGroup>[ 0 ] ) =>
        availableInGroup( group ).map( ( field ) => field.stage || field.slug );

    test( 'AF0001 : Every field type in the catalogue is added to one form', { tag: [ '@Lite', '@Test_AF0001' ] }, async () => {
        test.setTimeout( 300000 );
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const editor = new FormEditorPage( page );

        if ( existingFormId ) {
            // Fixture reuse: take the field set from the form that already exists.
            formId = existingFormId;
            await editor.openFormEditor( formId );

            const onStage = await editor.getStageFieldTypes();
            added = FieldTypes
                .filter( ( field ) => onStage.includes( field.stage || field.slug ) )
                .map( ( field ) => field.slug );
            unavailable = [];
            refused = {};
            console.log( `reusing form ${ formId } with ${ added.length } field types` );
            expect( added.length, `form ${ formId } holds no known field types` ).toBeGreaterThan( 0 );

            return;
        }

        await new PostFormPage( page ).createBlankFormPostForm( formName );
        formId = await editor.getFormId();

        ( { added, unavailable, refused } = await editor.doAddAllFieldTypes( FieldTypes.map( ( field ) => field.slug ) ) );
        console.log( `added ${ added.length }/${ FieldTypes.length } field types` );

        if ( unavailable.length ) {
            const proOnly = unavailable.every( ( slug ) => FieldTypes.find( ( f ) => f.slug === slug )?.pro );
            console.log( `not offered by this install: ${ unavailable.join( ', ' ) }` );
            // Only Pro-gated fields may be missing; a missing Lite field is a bug.
            expect( proOnly, `Lite field types missing from the builder: ${ unavailable.join( ', ' ) }` ).toBeTruthy();
        }

        for ( const [ slug, reason ] of Object.entries( refused ) ) {
            console.log( `refused by the builder: ${ slug } — ${ reason }` );
        }

        // A refusal is only acceptable for fields that need external configuration.
        const unexpectedRefusals = Object.keys( refused ).filter(
            ( slug ) => ! FieldTypes.find( ( f ) => f.slug === slug )?.envDep
        );
        expect( unexpectedRefusals, `field types the builder refused to add: ${ unexpectedRefusals.join( ', ' ) }` ).toEqual( [] );

        expect( added.length, 'no field types could be added' ).toBeGreaterThan( 0 );
        await editor.doSaveForm();
    } );

    test( 'AF0002 : Post Fields render on the stage', { tag: [ '@Lite', '@Test_AF0002' ] }, async () => {
        await new FormEditorPage( page ).validateStageHasFieldTypes( stageSlugs( 'Post Fields' ) );
    } );

    test( 'AF0003 : Taxonomy fields render on the stage', { tag: [ '@Lite', '@Test_AF0003' ] }, async () => {
        await new FormEditorPage( page ).validateStageHasFieldTypes( stageSlugs( 'Taxonomies' ) );
    } );

    test( 'AF0004 : Custom fields render on the stage', { tag: [ '@Lite', '@Test_AF0004' ] }, async () => {
        await new FormEditorPage( page ).validateStageHasFieldTypes( stageSlugs( 'Custom Fields' ) );
    } );

    test( 'AF0005 : Pricing fields render on the stage', { tag: [ '@Pro', '@Test_AF0005' ] }, async () => {
        const pricing = stageSlugs( 'Pricing Fields' );
        test.skip( ! pricing.length, 'Pricing fields require wpuf-pro' );
        await new FormEditorPage( page ).validateStageHasFieldTypes( pricing );
    } );

    test( 'AF0006 : "Others" fields render on the stage', { tag: [ '@Lite', '@Test_AF0006' ] }, async () => {
        await new FormEditorPage( page ).validateStageHasFieldTypes( stageSlugs( 'Others' ) );
    } );

    test( 'AF0007 : The field set survives a save + reload', { tag: [ '@Lite', '@Test_AF0007' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );

        const onStage = await editor.getStageFieldTypes();
        const expectedOnStage = FieldTypes
            .filter( ( field ) => added.includes( field.slug ) )
            .map( ( field ) => field.stage || field.slug );
        const missing = expectedOnStage.filter( ( slug ) => ! onStage.includes( slug ) );
        expect( missing, `field types lost after save: ${ missing.join( ', ' ) }` ).toEqual( [] );
    } );

    test( 'AF0008 : Frontend renders a row and control for every core field type', { tag: [ '@Lite', '@Test_AF0008' ] }, async () => {
        test.setTimeout( 180000 );
        const editor = new FormEditorPage( page );

        if ( ! existingPageSlug ) {
            await new PostFormPage( page ).createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        }

        await editor.openFrontendForm( pageSlug );

        const core = FieldTypes.filter(
            ( field ) => added.includes( field.slug ) && ! field.envDep && ! field.noRow && ! field.frontendSkip && ! field.knownBug
        );

        for ( const field of core ) {
            await editor.validateFrontendRow( field.label, field.control );
        }

        console.log( `validated ${ core.length } core field rows on the frontend` );
    } );

    test( 'AF0009 : Frontend renders the multistep wrapper for Step Start', { tag: [ '@Pro', '@Test_AF0009' ] }, async () => {
        test.skip( ! added.includes( 'step_start' ), 'Step Start requires wpuf-pro' );
        await new FormEditorPage( page ).validateMultistepRenderedOnFrontend();
    } );

    test( 'AF0010 : Environment-dependent fields are reported', { tag: [ '@Lite', '@Test_AF0010' ] }, async () => {
        const editor = new FormEditorPage( page );
        const envDep = FieldTypes.filter( ( field ) => added.includes( field.slug ) && field.envDep );
        const notRendered: string[] = [];

        for ( const field of envDep ) {
            if ( ! await editor.checkFrontendRow( field.label ) ) {
                notRendered.push( field.label );
            }
        }

        // These need API keys / extra plugins / payments, so a miss is a config
        // gap, not a product failure — surface it instead of failing the suite.
        console.log(
            notRendered.length
                ? `env-dependent fields not rendered (needs configuration): ${ notRendered.join( ', ' ) }`
                : 'all env-dependent fields rendered'
        );
        expect( envDep.length, 'no env-dependent fields were added' ).toBeGreaterThan( 0 );
    } );

    /**------------------- END TO END: SUBMIT + ROUND TRIP -------------------**/

    // BUG (see BUGS-FOUND.md): a Step Start field on a form whose multistep
    // setting is off renders a permanently hidden .wpuf-multistep-fieldset, so
    // every field after it — including Submit — is unreachable.
    test( 'AF0021 : Step Start without multistep enabled keeps the form usable', { tag: [ '@Pro', '@Test_AF0021' ] }, async () => {
        test.setTimeout( 240000 );
        test.skip( ! added.includes( 'step_start' ), 'Step Start requires wpuf-pro' );
        test.fail();
        const editor = new FormEditorPage( page );

        // Precondition: the form has a Step Start but multistep switched off.
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        const hasMultistep = await editor.isSettingAvailable( 'enable_multistep' );

        if ( hasMultistep ) {
            await editor.doToggleSetting( 'enable_multistep', false );
            await editor.doSaveForm();
        }

        try {
            await editor.openFrontendForm( pageSlug );
            const rows = await editor.countFrontendRows();
            expect( rows.hidden, `${ rows.hidden } field rows are hidden with no way to reach them` ).toBe( 0 );
            await expect( page.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).first() ).toBeVisible();
        } finally {
            // Put multistep back so the submit flow below can reach the button.
            if ( hasMultistep ) {
                await editor.openFormEditor( formId );
                await editor.doOpenSettingsSection( 'Display Settings' );
                await editor.doToggleSetting( 'enable_multistep', true );
                await editor.doSaveForm();
            }
        }
    } );

    test( 'AF0011 : Every rendered field accepts input and the form submits', { tag: [ '@Lite', '@Test_AF0011' ] }, async () => {
        test.setTimeout( 420000 );
        const editor = new FormEditorPage( page );

        // The form carries a Step Start, so multistep has to be switched on for
        // the wizard (and the submit button) to be reachable at all.
        if ( added.includes( 'step_start' ) ) {
            await editor.openFormEditor( formId );
            await editor.doOpenSettingsSection( 'Display Settings' );

            if ( await editor.isSettingAvailable( 'enable_multistep' ) ) {
                await editor.doToggleSetting( 'enable_multistep', true );
                await editor.doSaveForm();
            }
        }

        await editor.openFrontendForm( pageSlug );

        entered = await editor.doAdvanceAllSteps( PostForm.imageUpload );

        // Post Title drives the post we look for afterwards.
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).first().fill( postTitle );

        // The captcha refreshes its equation on every blocked attempt, so answer it last.
        await editor.doSolveMathCaptcha();

        const submit = page.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).first();
        await expect( submit, 'submit button is not reachable after filling every field' ).toBeVisible();
        await submit.click();

        // Success = the form goes away (redirect or success message).
        await expect(
            page.locator( '//form[contains(@class,"wpuf-form")]' ),
            `submit was blocked: ${ ( await editor.validateFrontendValidationErrors() ).join( '; ' ) }`
        ).toBeHidden( { timeout: 60000 } );
    } );

    test( 'AF0012 : The submitted post exists with the entered title', { tag: [ '@Lite', '@Test_AF0012' ] }, async () => {
        await new FormEditorPage( page ).validatePostCreated( postTitle );
    } );

    test( 'AF0013 : Field values round-trip through the WPUF edit form', { tag: [ '@Lite', '@Test_AF0013' ] }, async () => {
        test.setTimeout( 240000 );
        const editor = new FormEditorPage( page );
        const postForm = new PostFormPage( page );

        // The dashboard's Edit link is built from the global Edit Page setting;
        // without it the link falls back to the post permalink and no form loads.
        await postForm.createPageWithShortcode( '[wpuf_edit]', editPageSlug );
        await editor.doSetGlobalEditPage( editPageSlug );

        await postForm.createPageWithShortcode( '[wpuf_dashboard]', dashboardSlug );
        await editor.doOpenDashboardEditForm( dashboardSlug, postTitle );

        const values = await editor.readEditFormValues();
        expect( values.title, 'post title did not round-trip into the edit form' ).toBe( postTitle );

        // Display-only / one-shot fields legitimately hold no stored value.
        const noStoredValue = [ 'Math Captcha', 'Total', 'Section Break', 'Custom HTML', 'Shortcode', 'wpuf-column-field', 'reCaptcha', 'Cloudflare Turnstile', 'Really Simple Captcha' ];

        // Every other row that was filled on submit must come back filled on edit.
        const lost = Object.keys( entered )
            .filter( ( label ) => label && entered[ label ] && ! noStoredValue.includes( label ) )
            .filter( ( label ) => values.empty.includes( label ) );

        console.log( `filled on edit: ${ values.filled.join( ', ' ) }` );
        console.log( `empty on edit: ${ values.empty.join( ', ' ) }` );
        expect( lost, `fields that lost their value on edit: ${ lost.join( ', ' ) }` ).toEqual( [] );
    } );

    /**--------------------- KNOWN PRODUCT DEFECTS -------------------------**/

    // BUG: Field_Repeat::get_field_props() stores input_type "repeat" while the
    // field is registered as "repeat_field", so the renderer finds no handler and
    // drops the field. test.fail() keeps the suite honest until it is fixed.
    test( 'AF0014 : Repeat Field renders on the frontend', { tag: [ '@Pro', '@Test_AF0014' ] }, async () => {
        test.skip( ! added.includes( 'repeat_field' ), 'Repeat Field requires wpuf-pro' );
        test.fail();
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );
        await editor.validateFrontendRow( 'Repeat Field', 'input' );
    } );

    // BUG: the Shortcode field prints its own <li> on top of the generic field
    // wrapper, so every shortcode field renders twice (the first row is empty).
    test( 'AF0015 : No field type renders more than one row', { tag: [ '@Lite', '@Test_AF0015' ] }, async () => {
        test.fail();
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );

        const labels = ( await editor.getFrontendFieldLabels() ).filter( Boolean );
        const duplicated = labels.filter( ( label, index ) => labels.indexOf( label ) !== index );

        expect( [ ...new Set( duplicated ) ], `field labels rendered more than once: ${ duplicated.join( ', ' ) }` ).toEqual( [] );
    } );

    /**------------------ VALIDATION RULES PER FIELD TYPE ------------------**/

    // BUG (see BUGS-FOUND.md): on a form holding Math Captcha, the captcha submit
    // handler calls stopImmediatePropagation() before WPUF's own validation runs,
    // so clicking Submit on an empty form renders no message at all — not even the
    // captcha's own. WPUF's validateForm() does produce the right errors when it is
    // reached, which is why this is a wiring defect rather than missing validation.
    test( 'AF0016 : An empty submit is blocked by the required-field rules', { tag: [ '@Lite', '@Test_AF0016' ] }, async () => {
        test.setTimeout( 180000 );
        test.fail( added.includes( 'math_captcha' ), 'Math Captcha suppresses all submit feedback' );
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );

        const errors = await editor.validateRequiredFieldsBlockSubmit();
        console.log( `empty-submit errors: ${ errors.join( ' | ' ) }` );
        expect( errors.length, 'the empty form was submitted with no complaint' ).toBeGreaterThan( 0 );
    } );

    test( 'AF0017 : Email Address rejects a malformed address', { tag: [ '@Lite', '@Test_AF0017' ] }, async () => {
        const editor = new FormEditorPage( page );
        const valid = await editor.validateHtml5Validity( 'Email Address', 'not-an-email' );
        expect( valid, 'the email field accepted "not-an-email"' ).toBeFalsy();
    } );

    test( 'AF0018 : Website URL rejects a malformed URL', { tag: [ '@Lite', '@Test_AF0018' ] }, async () => {
        const editor = new FormEditorPage( page );
        const valid = await editor.validateHtml5Validity( 'Website URL', 'not a url' );
        expect( valid, 'the URL field accepted "not a url"' ).toBeFalsy();
    } );

    test( 'AF0019 : Numeric Field only accepts numbers', { tag: [ '@Pro', '@Test_AF0019' ] }, async () => {
        test.skip( ! added.includes( 'numeric_text_field' ), 'Numeric Field requires wpuf-pro' );
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );

        const input = page.locator( `${ editor.frontendRow( 'Numeric Field' ) }//input` ).first();
        await expect( input, 'the numeric field is not a number input' ).toHaveAttribute( 'type', 'number' );

        // Typing letters into a number input leaves it empty in every browser.
        await input.click();
        await page.keyboard.type( 'abc' );
        await expect( input, 'letters were accepted by the numeric field' ).toHaveValue( '' );

        await input.fill( '42' );
        await expect( input ).toHaveValue( '42' );
    } );

    test( 'AF0020 : Math Captcha rejects a wrong answer', { tag: [ '@Pro', '@Test_AF0020' ] }, async () => {
        test.setTimeout( 180000 );
        test.skip( ! added.includes( 'math_captcha' ), 'Math Captcha requires wpuf-pro' );
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );

        await editor.doAdvanceAllSteps( PostForm.imageUpload );
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).first().fill( `${ postTitle } captcha` );
        await editor.doAnswerMathCaptcha( '99999' );
        await editor.doSubmitFrontendForm();

        await expect( page.locator( '//form[contains(@class,"wpuf-form")]' ), 'a wrong captcha answer still submitted the form' ).toBeVisible();
        const errors = await editor.getVisibleValidationErrors();
        console.log( `captcha errors: ${ errors.join( ' | ' ) }` );
    } );
} );
