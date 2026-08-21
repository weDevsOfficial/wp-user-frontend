import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { FormEditorPage } from '../pages/formEditor';
import { Users, PostForm } from '../utils/testData';
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

    // The builder arms a beforeunload guard once a form is edited; Playwright
    // dismisses dialogs by default, which cancels the navigation and hangs the
    // next step. Always accept so navigation goes through.
    page.on( 'dialog', async ( dialog ) => {
        await dialog.accept().catch( () => {} );
    } );
} );

test.describe( 'Form-Editor', () => {
    configureSpecFailFast();

    /**--------------------------- NEW FORM EDITOR ---------------------------**
     *
     * @TestScenario : [New post form editor — builder chrome + frontend output]
     *
     * Ordered/stateful spec: one form is built up across the run, so the tests
     * below are listed in execution order. Tests tagged @Pro self-skip when the
     * Pro feature is missing (unlicensed wpuf-pro strips those fields/settings
     * through `Feature_Lock`).
     *
     * Setup
     * @Test_FE0000 : Admin creates a blank post form to exercise the editor
     *
     * Editor shell
     * @Test_FE0001 : Editor shell renders (header, tabs, stage, side panel)
     * @Test_FE0005 : Form id chip matches the id in the URL
     *
     * Add Fields panel
     * @Test_FE0010 : All field groups render in the Add Fields panel
     * @Test_FE0011 : Field search filters the panel, clearing restores it
     * @Test_FE0012 : Search with no match shows no field buttons
     * @Test_FE0013 : Clicking a field button adds it to the stage
     * @Test_FE0016 : Single-instance post fields cannot be added twice
     *
     * Field stage
     * @Test_FE0017 : Hover reveals Edit / Copy / Remove on a stage field
     * @Test_FE0018 : Edit switches the side panel to Field Options
     * @Test_FE0019 : Copy duplicates a field on the stage
     * @Test_FE0020 : Remove deletes a field from the stage
     * @Test_FE0022 : Required field shows the asterisk on the stage
     * @Test_FE0023 : Step Start renders as a multistep divider (@Pro)
     * @Test_FE0021 : Drag reorder persists after save
     * @Test_FE0007 : Save persists the stage field set
     *
     * Header, tabs and settings
     * @Test_FE0002 : Inline form title rename persists after reload
     * @Test_FE0008 : Form Editor / Settings tabs toggle their panels
     * @Test_FE0032 : Settings sidebar lists every section
     * @Test_FE0033 : Clicking a settings section renders its panel
     * @Test_FE0034 : Post settings (submit text) persist after save
     * @Test_FE0004 : Form switcher navigates to another form editor
     * @Test_FE0006 : Preview link renders the form on the preview page
     *
     * Frontend baseline
     * @Test_FE0040 : Frontend form renders with the selected layout class
     * @Test_FE0047 : Required validation blocks an empty frontend submit
     * @Test_FE0046 : Frontend submit creates the post (end-to-end)
     *
     * Field Options panel
     * @Test_FE0024 : Field Options shows an empty state on a form with no fields
     * @Test_FE0025 : Label edit updates the stage label live
     * @Test_FE0027 : Help text edit shows under the field on the stage
     * @Test_FE0028 : Required toggle adds and removes the asterisk
     * @Test_FE0029 : Field size option applies the size class on the stage
     * @Test_FE0026 : Meta key is locked for post fields
     * @Test_FE0031 : Conditional logic is offered with more than one field (@Pro)
     * @Test_FE0030 : Dropdown options render on the stage
     *
     * Panel & header extras
     * @Test_FE0014 : Dragging a panel field onto the stage adds it
     * @Test_FE0015 : Pro-only fields are shown gated with the pro badge
     * @Test_FE0009 : Leaving a dirty editor raises the unsaved-changes guard
     *
     * Settings persistence
     * @Test_FE0035 : Multistep toggle reveals the progress bar options (@Pro)
     * @Test_FE0036 : Form style (layout) selection persists (@Pro)
     * @Test_FE0037 : Payment settings persist after save
     * @Test_FE0038 : Notification settings persist after save
     * @Test_FE0039 : Form title and description toggles reach the frontend
     *
     * Frontend templates
     * @Test_FE0041 : Form title and description render on the template
     * @Test_FE0042 : Field help text renders under the field
     * @Test_FE0044 : Native selects use the template chevron
     * @Test_FE0045 : Image field with a single-file limit blocks a second upload
     * @Test_FE0043 : Multistep navigation works on the frontend (@Pro)
     *
     * Negative / security
     * @Test_FE0049 : An unknown form id does not break the editor page
     * @Test_FE0050 : A script payload in the form title is escaped on the frontend
     * @Test_FE0003 : Empty form title is not persisted (known bug — test.fail)
     * @Test_FE0048 : A subscriber cannot open the form builder
     *
     */

    const formName = `FE Editor ${ faker.string.alphanumeric( 5 ) }`;
    const secondFormName = `FE Switch ${ faker.string.alphanumeric( 5 ) }`;
    const renamedForm = `${ formName } Renamed`;
    const pageSlug = `fe-editor-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const postTitle = `FE Post ${ faker.string.alphanumeric( 5 ) }`;
    const submitText = 'Publish It';
    const fieldLabel = `Label ${ faker.string.alphanumeric( 5 ) }`;
    const helpText = `Help ${ faker.string.alphanumeric( 5 ) }`;
    const formDescription = `Description ${ faker.string.alphanumeric( 5 ) }`;
    const notificationSubject = `Subject ${ faker.string.alphanumeric( 5 ) }`;
    const xssTitle = `<script>alert(1)</script>XSS ${ faker.string.alphanumeric( 4 ) }`;
    const subscriberName = `fesub${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const subscriberPass = faker.internet.password( { length: 14 } );

    let formId: string;
    let savedOrder: string[];

    test( 'FE0000 : Admin creates a blank post form to exercise the editor', { tag: [ '@Lite', '@Test_FE0000' ] }, async () => {
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        // A second form is needed so the header form switcher has something to switch to.
        await postForm.createBlankFormPostForm( secondFormName );
        await postForm.createBlankFormPostForm( formName );
        formId = await new FormEditorPage( page ).getFormId();
    } );

    test( 'FE0001 : Editor shell renders header, tabs, stage and side panel', { tag: [ '@Lite', '@Test_FE0001' ] }, async () => {
        await new FormEditorPage( page ).validateEditorShell();
    } );

    test( 'FE0005 : Form id chip matches the id in the URL', { tag: [ '@Lite', '@Test_FE0005' ] }, async () => {
        await new FormEditorPage( page ).validateFormIdChip();
    } );

    test( 'FE0010 : Add Fields panel lists every field group', { tag: [ '@Lite', '@Test_FE0010' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.validateFieldGroups( [ 'Post Fields', 'Taxonomies', 'Custom Fields', 'Pricing Fields', 'Others' ] );
        await editor.validateFieldButtons( [ 'Post Title', 'Post Content', 'Category', 'Text', 'Dropdown', 'Section Break' ] );
    } );

    test( 'FE0011 : Field search filters the panel and clearing restores it', { tag: [ '@Lite', '@Test_FE0011' ] }, async () => {
        await new FormEditorPage( page ).validateSearchFiltersFields( 'Dropdown', 'Dropdown', 'Post Title' );
    } );

    test( 'FE0012 : Search with no match shows no field buttons', { tag: [ '@Lite', '@Test_FE0012' ] }, async () => {
        await new FormEditorPage( page ).validateSearchNoMatch( 'zzzznotafield' );
    } );

    test( 'FE0013 : Clicking a field button adds it to the stage', { tag: [ '@Lite', '@Test_FE0013' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doAddField( 'Post Title' );
        await editor.validateFieldOnStage( 'post_title' );
        await editor.doAddField( 'Post Content' );
        await editor.validateFieldOnStage( 'post_content' );
        await editor.doAddField( 'Text' );
        await editor.validateFieldOnStage( 'text_field' );
    } );

    test( 'FE0016 : Single-instance post fields cannot be added twice', { tag: [ '@Lite', '@Test_FE0016' ] }, async () => {
        await new FormEditorPage( page ).validateSingleInstanceField( 'Post Title', 'post_title' );
    } );

    test( 'FE0017 : Hover reveals Edit / Copy / Remove on a stage field', { tag: [ '@Lite', '@Test_FE0017' ] }, async () => {
        await new FormEditorPage( page ).validateFieldActions( 'text_field' );
    } );

    test( 'FE0018 : Edit switches the side panel to Field Options', { tag: [ '@Lite', '@Test_FE0018' ] }, async () => {
        await new FormEditorPage( page ).validateEditOpensFieldOptions( 'text_field' );
    } );

    test( 'FE0019 : Copy duplicates a field on the stage', { tag: [ '@Lite', '@Test_FE0019' ] }, async () => {
        await new FormEditorPage( page ).validateCopyDuplicatesField( 'text_field' );
    } );

    test( 'FE0020 : Remove deletes a field from the stage', { tag: [ '@Lite', '@Test_FE0020' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doRemoveField( 'text_field' );
        await editor.validateFieldRemoved( 'text_field', 1 );
    } );

    test( 'FE0022 : Required field shows the asterisk on the stage', { tag: [ '@Lite', '@Test_FE0022' ] }, async () => {
        await new FormEditorPage( page ).validateRequiredMark( 'post_title' );
    } );

    test( 'FE0023 : Step Start renders as a multistep divider', { tag: [ '@Pro', '@Test_FE0023' ] }, async () => {
        const editor = new FormEditorPage( page );
        // Step Start is a Pro field — skip instead of failing on Lite-only runs.
        test.skip( ! await editor.isFieldAvailable( 'Step Start' ), 'Step Start requires wpuf-pro' );
        await editor.doAddField( 'Step Start' );
        await editor.validateStepStartOnStage();
        // Leaving it behind turns the shared form multistep, which parks the
        // submit button in a hidden step for every later frontend test.
        await editor.doRemoveField( 'step_start' );
        await editor.validateFieldRemoved( 'step_start', 0 );
    } );

    test( 'FE0021 : Drag reorder persists after save', { tag: [ '@Lite', '@Test_FE0021' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doReorderFieldUp( 'post_content' );
        savedOrder = await editor.getStageFieldOrder();
        await editor.doSaveForm();
        await editor.validateStageOrderPersisted( savedOrder );
    } );

    test( 'FE0007 : Save persists the stage field set', { tag: [ '@Lite', '@Test_FE0007' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.validateFieldOnStage( 'post_title' );
        await editor.validateFieldOnStage( 'post_content' );
    } );

    test( 'FE0002 : Inline form title rename persists after reload', { tag: [ '@Lite', '@Test_FE0002' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doRenameForm( renamedForm );
        await editor.validateFormNamePersisted( renamedForm );
    } );

    test( 'FE0008 : Form Editor / Settings tabs toggle their panels', { tag: [ '@Lite', '@Test_FE0008' ] }, async () => {
        await new FormEditorPage( page ).validateTabSwitching();
    } );

    test( 'FE0032 : Settings sidebar lists every section', { tag: [ '@Lite', '@Test_FE0032' ] }, async () => {
        await new FormEditorPage( page ).validateSettingsSections(
            [ 'General', 'Payment Settings', 'Notification Settings', 'Display Settings', 'Advanced' ],
            [ 'Post Settings', 'Modules' ]
        );
    } );

    test( 'FE0033 : Clicking a settings section renders its panel', { tag: [ '@Lite', '@Test_FE0033' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.validateSettingsSectionOpens( 'General', '//select[@id="post_status"]' );
        await editor.validateSettingsSectionOpens( 'Display Settings', '//input[@id="show_form_title"]' );
    } );

    test( 'FE0034 : Post settings (submit text) persist after save', { tag: [ '@Lite', '@Test_FE0034' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doSetSubmitText( submitText );
        await editor.validateSubmitTextPersisted( submitText );
    } );

    test( 'FE0004 : Form switcher navigates to another form editor', { tag: [ '@Lite', '@Test_FE0004' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.validateSwitcherNavigatesTo();
        await editor.openFormEditor( formId );
    } );

    test( 'FE0006 : Preview link renders the form on the preview page', { tag: [ '@Lite', '@Test_FE0006' ] }, async () => {
        await new FormEditorPage( page ).validatePreviewOpensForm();
    } );

    test( 'FE0040 : Frontend form renders with the selected layout class', { tag: [ '@Lite', '@Test_FE0040' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await new PostFormPage( page ).createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        await editor.openFrontendForm( pageSlug );
        await editor.validateFormLayout( 'layout1' );
    } );

    test( 'FE0047 : Required validation blocks an empty frontend submit', { tag: [ '@Lite', '@Test_FE0047' ] }, async () => {
        await new FormEditorPage( page ).validateRequiredValidation();
    } );

    test( 'FE0046 : Frontend submit creates the post', { tag: [ '@Lite', '@Test_FE0046' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( postTitle, faker.lorem.paragraph() );
        await editor.validatePostCreated( postTitle );
    } );

    /**------------------------ FIELD OPTIONS PANEL ------------------------**/

    test( 'FE0024 : Field Options shows an empty state on a form with no fields', { tag: [ '@Lite', '@Test_FE0024' ] }, async () => {
        const editor = new FormEditorPage( page );
        // A form with fields auto-selects the first one, so the empty state needs
        // a fresh blank form.
        await new PostFormPage( page ).createBlankFormPostForm( `FE Empty ${ faker.string.alphanumeric( 5 ) }` );
        await editor.validateFieldOptionsEmptyState();
        await editor.openFormEditor( formId );
    } );

    test( 'FE0025 : Label edit updates the stage label live', { tag: [ '@Lite', '@Test_FE0025' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doEditField( 'text_field' );
        await editor.validateLabelUpdatesStage( 'text_field', fieldLabel );
    } );

    test( 'FE0027 : Help text edit shows under the field on the stage', { tag: [ '@Lite', '@Test_FE0027' ] }, async () => {
        await new FormEditorPage( page ).validateHelpTextUpdatesStage( 'text_field', helpText );
    } );

    test( 'FE0028 : Required toggle adds and removes the asterisk', { tag: [ '@Lite', '@Test_FE0028' ] }, async () => {
        await new FormEditorPage( page ).validateRequiredTogglesAsterisk( 'text_field' );
    } );

    test( 'FE0029 : Field size option applies the size class on the stage', { tag: [ '@Lite', '@Test_FE0029' ] }, async () => {
        await new FormEditorPage( page ).validateFieldSizeAppliesClass( 'text_field', 'small' );
    } );

    test( 'FE0026 : Meta key is locked for post fields', { tag: [ '@Lite', '@Test_FE0026' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doEditField( 'post_title' );
        await editor.validateMetaKeyLocked();
    } );

    test( 'FE0031 : Conditional logic is offered once more than one field exists', { tag: [ '@Pro', '@Test_FE0031' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doEditField( 'text_field' );
        // Fields_Manager::add_conditional_field() registers it in the `advanced`
        // section, which is collapsed until expanded — without this the option is
        // never visible and the pro gate below misfires on a Pro site too.
        await editor.doExpandAdvancedOptions();
        // Conditional logic ships with wpuf-pro.
        test.skip( ! await editor.isFieldOptionAvailable( 'Conditional Logic' ), 'Conditional Logic requires wpuf-pro' );
        expect( await editor.isFieldOptionAvailable( 'Conditional Logic' ) ).toBeTruthy();
    } );

    test( 'FE0030 : Dropdown options render on the stage', { tag: [ '@Lite', '@Test_FE0030' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doAddField( 'Dropdown' );
        await editor.validateFieldOnStage( 'dropdown_field' );
        // Default preset: the "- select -" placeholder plus one empty option.
        await editor.validateStageDropdownOptions( 'dropdown_field', 2 );
    } );

    /**------------------------ PANEL & HEADER EXTRAS ----------------------**/

    test( 'FE0014 : Dragging a panel field onto the stage adds it', { tag: [ '@Lite', '@Test_FE0014' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doDragFieldToStage( 'Textarea' );
        await editor.validateFieldOnStage( 'textarea_field' );
    } );

    test( 'FE0015 : Pro-only fields are shown gated with the pro badge', { tag: [ '@Lite', '@Test_FE0015' ] }, async () => {
        const editor = new FormEditorPage( page );
        // With wpuf-pro active there is nothing to gate.
        test.skip( ! await editor.hasProPreviewFields(), 'No pro-preview fields (wpuf-pro is active)' );
        await editor.validateProPreviewFieldsAreGated();
    } );

    test( 'FE0009 : Leaving a dirty editor raises the unsaved-changes guard', { tag: [ '@Lite', '@Test_FE0009' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await editor.validateUnsavedChangesGuard();
    } );

    /**--------------------------- SETTINGS TAB ----------------------------**/

    test( 'FE0035 : Multistep toggle reveals the progress bar options', { tag: [ '@Pro', '@Test_FE0035' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        // Multistep is a Pro form setting.
        test.skip( ! await editor.isSettingAvailable( 'enable_multistep' ), 'Multistep requires wpuf-pro' );
        await editor.validateMultistepRevealsOptions();
    } );

    test( 'FE0036 : Form style (layout) selection persists', { tag: [ '@Pro', '@Test_FE0036' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doOpenSettingsSection( 'Display Settings' );
        // The layout picker ships with wpuf-pro.
        test.skip( ! await editor.isSettingAvailable( 'form_layout' ), 'Form style picker requires wpuf-pro' );
        await editor.doPickRadioSetting( 'form_layout', 'layout2' );
        await editor.doSaveForm();
        await page.reload();
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.validateRadioSettingPicked( 'form_layout', 'layout2' );
        // Later frontend tests assert the default layout, so hand the shared
        // form back the way it was found.
        await editor.doPickRadioSetting( 'form_layout', 'layout1' );
        await editor.doSaveForm();
    } );

    test( 'FE0037 : Payment settings persist after save', { tag: [ '@Lite', '@Test_FE0037' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doOpenSettingsSection( 'Payment Settings' );
        await editor.doToggleSetting( 'payment_options', true );

        // The per-post cost row only exists when pay-per-post is switched on
        // globally, so treat it as optional here.
        const hasPerPostCost = await editor.isSettingVisible( 'pay_per_post_cost' );

        if ( hasPerPostCost ) {
            await editor.doFillSetting( 'pay_per_post_cost', '25' );
        }

        await editor.doSaveForm();
        await page.reload();
        await editor.doOpenSettingsSection( 'Payment Settings' );
        await editor.validateSettingChecked( 'payment_options', true );

        if ( hasPerPostCost ) {
            await editor.validateSettingValue( 'pay_per_post_cost', '25' );
        }
    } );

    test( 'FE0038 : Notification settings persist after save', { tag: [ '@Lite', '@Test_FE0038' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doOpenSettingsSection( 'Notification Settings' );
        await editor.doFillSetting( 'new_subject', notificationSubject );
        await editor.doSaveForm();
        await page.reload();
        await editor.doOpenSettingsSection( 'Notification Settings' );
        await editor.validateSettingValue( 'new_subject', notificationSubject );
    } );

    test( 'FE0039 : Form title and description toggles reach the frontend', { tag: [ '@Lite', '@Test_FE0039' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doToggleSetting( 'show_form_title', true );
        await editor.doFillSetting( 'form_description', formDescription );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await editor.validateFormTitleAndDescription( renamedForm, formDescription );
    } );

    /**------------------------- FRONTEND TEMPLATES ------------------------**/

    test( 'FE0041 : Form title and description render on the template', { tag: [ '@Lite', '@Test_FE0041' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFrontendForm( pageSlug );
        await editor.validateFormLayout( 'layout1' );
        await editor.validateFormTitleAndDescription( renamedForm, formDescription );
    } );

    test( 'FE0042 : Field help text renders under the field', { tag: [ '@Lite', '@Test_FE0042' ] }, async () => {
        const editor = new FormEditorPage( page );
        // FE0027 only proved the live stage preview; persist it before checking
        // the rendered form.
        await editor.openFormEditor( formId );
        await editor.doEditField( 'text_field' );
        await editor.validateHelpTextUpdatesStage( 'text_field', helpText );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await editor.validateHelpTextRendered( helpText );
    } );

    test( 'FE0044 : Native selects use the template chevron', { tag: [ '@Lite', '@Test_FE0044' ] }, async () => {
        const editor = new FormEditorPage( page );
        // Needs a saved select on the form — FE0030 only checked the stage.
        await editor.openFormEditor( formId );
        await editor.doAddField( 'Dropdown' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await editor.validateSelectStyling();
    } );

    test( 'FE0045 : Image field with a single-file limit blocks a second upload', { tag: [ '@Lite', '@Test_FE0045' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await editor.doAddField( 'Image Upload' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await editor.validateSingleImageUploadEnforced( PostForm.imageUpload );
    } );

    test( 'FE0043 : Multistep navigation works on the frontend', { tag: [ '@Pro', '@Test_FE0043' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        // Needs the Pro Step Start field on the stage.
        test.skip( ! await editor.isFieldAvailable( 'Step Start' ), 'Multistep requires wpuf-pro' );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doToggleSetting( 'enable_multistep', true );
        await editor.doOpenTab( 'Form Editor' );
        // Step Start opens a step, so two of them are needed for a form with
        // something to navigate to: one above the fields, one before submit.
        await editor.doAddField( 'Step Start' );
        await editor.doReorderFieldUp( 'step_start' );
        await editor.doAddField( 'Step Start' );
        await editor.doSaveForm();
        await editor.openFrontendForm( pageSlug );
        await editor.validateMultistepNavigation();
    } );

    /**--------------------------- NEGATIVE CASES --------------------------**/

    test( 'FE0049 : An unknown form id does not break the editor page', { tag: [ '@Lite', '@Test_FE0049' ] }, async () => {
        await new FormEditorPage( page ).validateInvalidFormIdHandled( '99999999' );
    } );

    test( 'FE0050 : A script payload in the form title is escaped on the frontend', { tag: [ '@Lite', '@Test_FE0050' ] }, async () => {
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await editor.validateTitleXssEscaped( xssTitle, pageSlug );
    } );

    // KNOWN BUG (see BUGS-FOUND.md #2): the builder saves an empty form title, so
    // the form ends up nameless in the forms list. Marked test.fail() so the suite
    // stays honest — it turns red the day the guard lands. Runs late because it
    // blanks the form name.
    test( 'FE0003 : Empty form title is not persisted', { tag: [ '@Lite', '@Test_FE0003' ] }, async () => {
        test.fail();
        const editor = new FormEditorPage( page );
        await editor.openFormEditor( formId );
        await editor.validateEmptyTitleRejected( xssTitle );
    } );

    test( 'FE0048 : A subscriber cannot open the form builder', { tag: [ '@Lite', '@Test_FE0048' ] }, async () => {
        await new FormEditorPage( page ).validateBuilderBlockedForSubscriber( formId, subscriberName, subscriberPass );
    } );
} );
