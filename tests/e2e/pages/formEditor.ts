import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, request, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { Urls } from '../utils/testData';

/**
 * Page object for the revamped post form editor (admin builder chrome).
 *
 * Covers: header (inline title, form switcher, Preview, Save), the
 * Form Editor / Settings tab pair, the Add Fields / Field Options side
 * panel and the sortable field stage — plus the frontend rendering of a
 * form built with it.
 */
export class FormEditorPage extends Base {
    constructor( page: Page ) {
        super( page );
    }

    // protected so PostFormGapsPage (pages/postFormGaps.ts) can reuse the same
    // builder/frontend selectors instead of re-declaring them.
    protected fe = Selectors.formEditor;

    /*********************************/
    /********* Header (shell) ********/
    /*********************************/

    async openFormEditor( formId: string ) {
        await this.navigateToURL( this.accessFormWithId + formId );
        // A blank form renders an empty <ul> stage (zero height), so wait for it
        // to be attached rather than visible.
        await this.page.waitForSelector( this.fe.tabs.editorStage, { state: 'attached' } );
    }

    async validateEditorShell() {
        await this.assertionValidate( this.fe.header.logo );
        await this.assertionValidate( this.fe.header.titleInput );
        await this.assertionValidate( this.fe.header.formIdChip );
        await this.assertionValidate( this.fe.header.previewLink );
        await this.assertionValidate( this.fe.header.saveButton );
        await this.assertionValidate( this.fe.tabs.navTab( 'Form Editor' ) );
        await this.assertionValidate( this.fe.tabs.navTab( 'Settings' ) );
        await this.validateAttached( this.fe.tabs.editorStage );
        await this.assertionValidate( this.fe.panel.panelTab( 'Add Fields' ) );
        await this.assertionValidate( this.fe.panel.panelTab( 'Field Options' ) );
    }

    // The "#123" chip must match the id the editor was opened with.
    async validateFormIdChip() {
        const formId = await this.getFormId();
        const chip = ( await this.page.locator( this.fe.header.formIdChip ).first().innerText() ).trim();
        expect( chip.replace( '#', '' ) ).toBe( formId );
    }

    async doRenameForm( newName: string ) {
        await this.validateAndFillStrings( this.fe.header.titleInput, newName );
        await this.page.locator( this.fe.header.titleInput ).press( 'Enter' );
        await this.doSaveForm();
    }

    async validateFormNamePersisted( expectedName: string ) {
        await this.page.reload();
        await this.page.waitForSelector( this.fe.header.titleInput );
        await expect( this.page.locator( this.fe.header.titleInput ) ).toHaveValue( expectedName );
    }

    async doSaveForm() {
        // Wait on the save ajax itself — wp-admin keeps polling (heartbeat), so
        // "networkidle" never settles here.
        const saved = this.page.waitForResponse( ( response ) =>
            response.url().includes( 'admin-ajax.php' )
            && ( response.request().postData() || '' ).includes( 'wpuf_form_builder_save_form' )
        );

        await this.validateAndClick( this.fe.header.saveButton );
        await saved;
    }

    // Switches to whichever other form the dropdown offers — form names in the
    // list are whatever the site already has, so they are not assumed.
    async validateSwitcherNavigatesTo() {
        const currentId = await this.getFormId();
        await this.validateAndClick( this.fe.header.switcherArrow );

        const target = this.page.locator( this.fe.header.switcherOtherForm( currentId ) );
        const targetName = ( await target.innerText() ).trim();
        await target.click();

        await this.page.waitForSelector( this.fe.tabs.editorStage, { state: 'attached' } );
        expect( await this.getFormId() ).not.toBe( currentId );
        await expect( this.page.locator( this.fe.header.titleInput ) ).toHaveValue( targetName );
    }

    async validatePreviewOpensForm() {
        const formId = await this.getFormId();
        const href = await this.page.locator( this.fe.header.previewLink ).first().getAttribute( 'href' );
        expect( href ).toContain( `form_id=${ formId }` );

        await this.navigateToURL( this.accessFormPreview + formId );
        await this.assertionValidate( this.fe.frontend.form );
    }

    /*********************************/
    /************ Tabs ***************/
    /*********************************/

    async doOpenTab( name: 'Form Editor' | 'Settings' ) {
        await this.validateAndClick( this.fe.tabs.navTab( name ) );
    }

    async validateTabSwitching() {
        await this.doOpenTab( 'Settings' );
        await expect( this.page.locator( this.fe.tabs.settingsPanel ) ).toBeVisible();
        await expect( this.page.locator( this.fe.tabs.editorStage ) ).toBeHidden();

        await this.doOpenTab( 'Form Editor' );
        await expect( this.page.locator( this.fe.tabs.editorStage ) ).toBeVisible();
        await expect( this.page.locator( this.fe.tabs.settingsPanel ) ).toBeHidden();
    }

    /*********************************/
    /******** Add Fields panel *******/
    /*********************************/

    async doOpenPanelTab( name: 'Add Fields' | 'Field Options' ) {
        await this.validateAndClick( this.fe.panel.panelTab( name ) );
    }

    async validateFieldGroups( groups: string[] ) {
        await this.doOpenPanelTab( 'Add Fields' );
        for ( const group of groups ) {
            await this.assertionValidate( this.fe.panel.groupHeading( group ) );
        }
    }

    async validateFieldButtons( labels: string[] ) {
        for ( const label of labels ) {
            await this.assertionValidate( this.fe.panel.fieldButton( label ) );
        }
    }

    async validateSearchFiltersFields( term: string, expectedLabel: string, hiddenLabel: string ) {
        await this.validateAndFillStrings( this.fe.panel.searchField, term );
        await expect( this.page.locator( this.fe.panel.fieldButton( expectedLabel ) ) ).toBeVisible();
        await expect( this.page.locator( this.fe.panel.fieldButton( hiddenLabel ) ) ).toBeHidden();

        // Clearing the search restores the full list.
        await this.page.locator( this.fe.panel.searchField ).fill( '' );
        await expect( this.page.locator( this.fe.panel.fieldButton( hiddenLabel ) ) ).toBeVisible();
    }

    async validateSearchNoMatch( term: string ) {
        await this.validateAndFillStrings( this.fe.panel.searchField, term );
        await expect( this.page.locator( this.fe.panel.visibleFieldButtons ).locator( 'visible=true' ) ).toHaveCount( 0 );
        await this.page.locator( this.fe.panel.searchField ).fill( '' );
    }

    async doAddField( label: string ) {
        await this.doOpenPanelTab( 'Add Fields' );
        await this.validateAndClick( this.fe.panel.fieldButton( label ) );
        await this.dismissBuilderPopup();
    }

    // The builder pops SweetAlerts (e.g. "show custom field data inside your
    // post?") that block every later click until they are closed.
    async dismissBuilderPopup() {
        const popup = this.page.locator( this.fe.panel.builderPopup );

        if ( ! await popup.isVisible().catch( () => false ) ) {
            return;
        }

        const cancel = this.page.locator( this.fe.panel.builderPopupCancel );
        const dismiss = await cancel.isVisible().catch( () => false )
            ? cancel
            : this.page.locator( this.fe.panel.builderPopupConfirm );

        await dismiss.click();
        await expect( popup ).toBeHidden();
    }

    async validateFieldOnStage( type: string ) {
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ) ).toHaveCount( 1 );
    }

    // Post Title / Content / Excerpt / Featured Image are single-instance: once
    // used, the panel button warns instead of adding a second copy.
    async validateSingleInstanceField( label: string, type: string ) {
        await this.doOpenPanelTab( 'Add Fields' );
        await this.page.locator( this.fe.panel.fieldButton( label ) ).click();
        await expect( this.page.locator( this.fe.panel.builderPopupText ) ).toContainText( 'already have this field' );
        await this.dismissBuilderPopup();
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ) ).toHaveCount( 1 );
    }

    /*********************************/
    /********* Field stage ***********/
    /*********************************/

    // Copy leaves more than one item of the same type on the stage, so every
    // stage action targets the first match.
    async validateFieldActions( type: string ) {
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover();
        await expect( this.page.locator( this.fe.stage.actionEdit( type ) ).first() ).toBeVisible();
        await expect( this.page.locator( this.fe.stage.actionCopy( type ) ).first() ).toBeVisible();
        await expect( this.page.locator( this.fe.stage.actionRemove( type ) ).first() ).toBeVisible();
    }

    async validateEditOpensFieldOptions( type: string ) {
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover();
        await this.page.locator( this.fe.stage.actionEdit( type ) ).first().click();
        await expect( this.page.locator( this.fe.panel.panelTab( 'Field Options' ) ) ).toHaveClass( /wpuf-bg-white/ );
    }

    async validateCopyDuplicatesField( type: string ) {
        const before = await this.page.locator( this.fe.stage.fieldByType( type ) ).count();
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover();
        await this.page.locator( this.fe.stage.actionCopy( type ) ).first().click();
        await this.dismissBuilderPopup();
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ) ).toHaveCount( before + 1 );
    }

    // Remove asks for confirmation ("Yes, delete it") before dropping the field.
    async doRemoveField( type: string ) {
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover();
        await this.page.locator( this.fe.stage.actionRemove( type ) ).first().click();

        const popup = this.page.locator( this.fe.panel.builderPopup );
        await expect( popup ).toBeVisible();
        await this.page.locator( this.fe.panel.builderPopupConfirm ).click();
        await expect( popup ).toBeHidden();
    }

    async validateFieldRemoved( type: string, expectedCount: number ) {
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ) ).toHaveCount( expectedCount );
    }

    async validateRequiredMark( type: string ) {
        await this.assertionValidate( this.fe.stage.requiredMark( type ) );
    }

    // Step Start ships with wpuf-pro, so Lite-only runs will not offer it.
    async isFieldAvailable( label: string ): Promise<boolean> {
        await this.doOpenPanelTab( 'Add Fields' );

        return this.page.locator( this.fe.panel.fieldButton( label ) ).isVisible().catch( () => false );
    }

    async validateStepStartOnStage() {
        await this.assertionValidate( this.fe.stage.stepStart );
    }

    // Drag the given field to the top of the stage with the sortable handle.
    // jQuery UI needs a small move past its distance threshold before it starts
    // sorting, hence the nudge between mouse down and the real move.
    async doReorderFieldUp( type: string ) {
        const handle = this.page.locator( this.fe.stage.dragHandle( type ) ).first();
        const target = this.page.locator( this.fe.stage.allFields ).first();

        // The handle lives in the hover-revealed action bar (opacity only), so a
        // forced hover is enough to place the cursor on it.
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover( { force: true } );
        await handle.hover( { force: true } );

        const start = await handle.boundingBox();
        const box = await target.boundingBox();

        await this.page.mouse.down();
        await this.page.mouse.move( start.x + start.width / 2, start.y + start.height / 2 - 15, { steps: 5 } );
        await this.page.mouse.move( box.x + box.width / 2, box.y + 5, { steps: 20 } );
        await this.page.mouse.up();
        await this.waitForLoading();
    }

    async getStageFieldOrder(): Promise<string[]> {
        return this.page.locator( this.fe.stage.allFields ).evaluateAll(
            ( items ) => items.map( ( item ) => ( item.className.match( /form-field-([a-z_]+)/ ) || [] )[ 1 ] )
        );
    }

    async validateStageOrderPersisted( expectedOrder: string[] ) {
        await this.page.reload();
        await this.page.waitForSelector( this.fe.tabs.editorStage );
        expect( await this.getStageFieldOrder() ).toEqual( expectedOrder );
    }

    /*********************************/
    /********** Settings tab *********/
    /*********************************/

    async validateSettingsSections( sections: string[], groups: string[] = [] ) {
        await this.doOpenTab( 'Settings' );
        for ( const group of groups ) {
            await this.assertionValidate( this.fe.settings.sidebarGroup( group ) );
        }
        for ( const section of sections ) {
            await this.assertionValidate( this.fe.settings.sidebarItem( section ) );
        }
    }

    // Panel controls are selectize-driven (the native <select> stays hidden), so
    // the panel is proven by its heading plus the control being attached.
    async validateSettingsSectionOpens( section: string, panelLocator: string ) {
        await this.validateAndClick( this.fe.settings.sidebarItem( section ) );
        await expect( this.page.locator( this.fe.settings.sectionHeading( section ) ).first() ).toBeVisible();
        await this.validateAttached( panelLocator );
    }

    async doSetSubmitText( text: string ) {
        await this.doOpenTab( 'Settings' );
        await this.validateAndClick( this.fe.settings.sidebarItem( 'General' ) );
        await this.validateAndFillStrings( this.fe.settings.submitTextField, text );
        await this.doSaveForm();
    }

    async validateSubmitTextPersisted( text: string ) {
        await this.page.reload();
        await this.doOpenTab( 'Settings' );
        await this.validateAndClick( this.fe.settings.sidebarItem( 'General' ) );
        await expect( this.page.locator( this.fe.settings.submitTextField ) ).toHaveValue( text );
    }

    /*********************************/
    /*********** Frontend ************/
    /*********************************/

    async openFrontendForm( pageSlug: string ) {
        await this.navigateToURL( `${ Urls.baseUrl }/${ pageSlug }/` );
        await this.page.waitForSelector( this.fe.frontend.form );
    }

    // The revamped templates render as wpuf-form-<layout> on the <form> tag.
    async validateFormLayout( layout: string ) {
        await expect( this.page.locator( this.fe.frontend.form ).first() ).toHaveClass( new RegExp( `wpuf-form-${ layout }` ) );
    }

    async validateRequiredValidation() {
        await this.validateAndClick( this.fe.frontend.submitButton );
        await expect( this.page.locator( this.fe.frontend.errorMessage ).first() ).toBeVisible();
    }

    async doSubmitFrontendPost( title: string, content: string ) {
        await this.validateAndFillStrings( this.fe.frontend.titleField, title );
        await this.doFillPostContent( content );
        await this.validateAndClick( this.fe.frontend.submitButton );
        // On success WPUF either replaces the form with .wpuf-success or redirects;
        // both end with the form gone. Waiting for that avoids racing the redirect.
        await expect( this.page.locator( this.fe.frontend.form ) ).toBeHidden( { timeout: 60000 } );
    }

    // Post Content renders as a TinyMCE iframe when rich editing is on and as a
    // plain textarea otherwise.
    async doFillPostContent( content: string ) {
        const textarea = this.page.locator( this.fe.frontend.contentField );

        if ( await textarea.isVisible().catch( () => false ) ) {
            await textarea.fill( content );

            return;
        }

        await this.page.frameLocator( this.fe.frontend.contentEditorFrame ).locator( 'body' ).fill( content );
    }

    async validatePostCreated( title: string ) {
        await this.navigateToURL( this.postsPage );
        await this.assertionValidate( `//a[normalize-space()="${ title }"]` );
    }

    /*********************************/
    /******** Field Options tab ******/
    /*********************************/

    private fos = Selectors.fieldOptionsSettings.fieldOptionsPanel;

    async validateFieldOptionsEmptyState() {
        await this.doOpenPanelTab( 'Field Options' );
        await this.assertionValidate( this.fe.panel.fieldOptionsEmptyState );
    }

    async doEditField( type: string ) {
        await this.doOpenTab( 'Form Editor' );
        await this.page.locator( this.fe.stage.fieldByType( type ) ).first().hover();
        await this.page.locator( this.fe.stage.actionEdit( type ) ).first().click();
        await expect( this.page.locator( this.fe.panel.fieldOptionsPanel ) ).toBeVisible();
    }

    // Advanced Options is collapsed by default; Field Size / CSS class live there.
    async doExpandAdvancedOptions() {
        const advanced = this.page.locator( Selectors.fieldOptionsSettings.advancedSettings ).first();

        if ( ! await advanced.isVisible().catch( () => false ) ) {
            return;
        }

        const expanded = await advanced.locator( 'i.fa-angle-down' ).count();

        if ( ! expanded ) {
            await advanced.click();
        }
    }

    async isFieldOptionAvailable( title: string ): Promise<boolean> {
        return this.page.locator( this.fe.panel.fieldOptionByTitle( title ) ).first().isVisible().catch( () => false );
    }

    // Editing an option updates the stage preview live, without saving.
    async validateLabelUpdatesStage( type: string, label: string ) {
        await this.validateAndFillStrings( this.fos.fieldLabel, label );
        await expect( this.page.locator( this.fe.stage.fieldLabel( type ) ).first() ).toContainText( label );
    }

    async validateHelpTextUpdatesStage( type: string, helpText: string ) {
        await this.validateAndFillStrings( this.fos.helpText, helpText );
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ).first() ).toContainText( helpText );
    }

    async validateRequiredTogglesAsterisk( type: string ) {
        await this.validateAndClick( this.fos.requiredToggle.yes );
        await expect( this.page.locator( this.fe.stage.requiredMark( type ) ).first() ).toBeVisible();

        await this.validateAndClick( this.fos.requiredToggle.no );
        await expect( this.page.locator( this.fe.stage.requiredMark( type ) ) ).toHaveCount( 0 );
    }

    async validateFieldSizeAppliesClass( type: string, size: 'small' | 'medium' | 'large' ) {
        await this.doExpandAdvancedOptions();
        await this.validateAndClick( this.fos.advancedOptions.fieldSize( size ) );
        await expect( this.page.locator( this.fe.stage.fieldByType( type ) ).first() )
            .toHaveClass( new RegExp( `field-size-${ size }` ) );
    }

    // Post fields map to core post columns, so their meta key is not editable.
    async validateMetaKeyLocked() {
        const metaKey = this.page.locator( this.fos.metaKey );

        if ( ! await metaKey.isVisible().catch( () => false ) ) {
            return;
        }

        const locked = await metaKey.evaluate( ( el: HTMLInputElement ) => el.readOnly || el.disabled );
        expect( locked ).toBeTruthy();
    }

    async validateStageDropdownOptions( type: string, expectedCount: number ) {
        await expect( this.page.locator( this.fe.stage.selectOptions( type ) ) ).toHaveCount( expectedCount );
    }

    /*********************************/
    /***** Panel: drag & pro gate ****/
    /*********************************/

    // Panel buttons are jQuery UI draggables dropped onto the sortable stage.
    async doDragFieldToStage( label: string ) {
        await this.doOpenPanelTab( 'Add Fields' );

        const source = this.page.locator( this.fe.panel.fieldButton( label ) ).first();
        const stage = this.page.locator( this.fe.tabs.editorStage ).first();

        const from = await source.boundingBox();
        const to = await stage.boundingBox();

        const dropX = to.x + to.width / 2;
        const dropY = to.y + Math.min( to.height - 20, 200 );

        await source.hover();
        await this.page.mouse.down();
        // jQuery UI needs the distance threshold, then movement *inside* the
        // sortable before it accepts the drop.
        await this.page.mouse.move( from.x + from.width / 2, from.y + from.height / 2 + 15, { steps: 5 } );
        await this.page.mouse.move( dropX, dropY, { steps: 25 } );
        await this.page.mouse.move( dropX, dropY + 10, { steps: 5 } );
        await this.page.mouse.move( dropX, dropY, { steps: 5 } );
        await this.page.mouse.up();
        await this.dismissBuilderPopup();
    }

    async hasProPreviewFields(): Promise<boolean> {
        await this.doOpenPanelTab( 'Add Fields' );

        return ( await this.page.locator( this.fe.panel.proPreviewBadge ).count() ) > 0;
    }

    async validateProPreviewFieldsAreGated() {
        const badge = this.page.locator( this.fe.panel.proPreviewBadge ).first();
        await expect( badge ).toBeAttached();
    }

    /*********************************/
    /**** Header: title & guards *****/
    /*********************************/

    // An empty title must not be persisted — WPUF falls back to the old name.
    async validateEmptyTitleRejected( previousName: string ) {
        await this.page.locator( this.fe.header.titleInput ).fill( '' );
        await this.doSaveForm();
        await this.page.reload();
        await this.page.waitForSelector( this.fe.header.titleInput );

        const title = await this.page.locator( this.fe.header.titleInput ).inputValue();
        expect( title ).toBe( previousName );
    }

    // Dirty editor + navigation away must raise the browser's unsaved-changes guard.
    async validateUnsavedChangesGuard() {
        let guarded = false;
        const onDialog = async ( dialog ) => {
            guarded = guarded || dialog.type() === 'beforeunload';
            await dialog.accept().catch( () => {} );
        };

        await this.doAddField( 'Text' );
        this.page.on( 'dialog', onDialog );
        await this.page.goto( this.wpufPostFormPage ).catch( () => {} );
        this.page.off( 'dialog', onDialog );

        expect( guarded ).toBeTruthy();
    }

    /*********************************/
    /***** Settings (generic) ********/
    /*********************************/

    // Toggles render as sr-only checkboxes behind a styled label, so presence is
    // measured by attachment rather than visibility.
    async isSettingAvailable( id: string ): Promise<boolean> {
        return ( await this.page.locator( this.fe.settings.controlById( id ) ).count() ) > 0;
    }

    // Rows hidden behind a dependency (e.g. per-post cost) are attached but not
    // shown — fillable controls must be checked for visibility.
    async isSettingVisible( id: string ): Promise<boolean> {
        return this.page.locator( this.fe.settings.controlById( id ) ).first().isVisible().catch( () => false );
    }

    async doOpenSettingsSection( section: string ) {
        await this.doOpenTab( 'Settings' );
        await this.validateAndClick( this.fe.settings.sidebarItem( section ) );
    }

    async doFillSetting( id: string, value: string ) {
        await this.validateAndFillStrings( this.fe.settings.controlById( id ), value );
    }

    // pic-radio settings (form style) are opacity-0 radios behind a preview
    // image, so they need a forced check rather than a fill.
    async doPickRadioSetting( id: string, value: string ) {
        await this.page.locator( this.fe.settings.radioOptionById( id, value ) ).first().check( { force: true } );
    }

    async validateRadioSettingPicked( id: string, value: string ) {
        await expect( this.page.locator( this.fe.settings.radioOptionById( id, value ) ).first() ).toBeChecked();
    }

    // The checkbox itself is sr-only — flip it through its styled label.
    async doToggleSetting( id: string, on: boolean ) {
        const toggle = this.page.locator( this.fe.settings.controlById( id ) ).first();

        if ( await toggle.isChecked() === on ) {
            return;
        }

        await this.page.locator( `//div[@id="wpuf-form-builder-settings"]//label[@for="${ id }"]` ).first().click();
        expect( await toggle.isChecked() ).toBe( on );
    }

    async validateSettingValue( id: string, value: string ) {
        await expect( this.page.locator( this.fe.settings.controlById( id ) ).first() ).toHaveValue( value );
    }

    async validateSettingChecked( id: string, checked: boolean ) {
        const toggle = this.page.locator( this.fe.settings.controlById( id ) ).first();
        expect( await toggle.isChecked() ).toBe( checked );
    }

    async validateMultistepRevealsOptions() {
        await this.doToggleSetting( 'enable_multistep', true );
        await this.validateAttached( this.fe.settings.multistepProgressbar );
    }

    /*********************************/
    /***** Frontend (extended) *******/
    /*********************************/

    async validateFormTitleAndDescription( title: string, description: string ) {
        await expect( this.page.locator( this.fe.frontend.formTitle ) ).toContainText( title );
        await expect( this.page.locator( this.fe.frontend.formDescription ) ).toContainText( description );
    }

    async validateHelpTextRendered( helpText: string ) {
        await this.assertionValidate( this.fe.frontend.helpText( helpText ) );
    }

    // The revamped templates paint their own chevron on native selects.
    async validateSelectStyling() {
        const styles = await this.page.locator( this.fe.frontend.nativeSelect ).first().evaluate( ( el ) => {
            const computed = window.getComputedStyle( el );

            return {
                appearance: computed.appearance || computed.webkitAppearance,
                backgroundImage: computed.backgroundImage,
            };
        } );

        expect( styles.appearance ).toBe( 'none' );
        expect( styles.backgroundImage ).toContain( 'url(' );
    }

    // An image field with "Max. files = 1" must not accept a second upload.
    async validateSingleImageUploadEnforced( file: string ) {
        await this.page.setInputFiles( this.fe.frontend.imageUploadInput, file );
        await expect( this.page.locator( this.fe.frontend.uploadedItem ).first() ).toBeVisible();
        await expect( this.page.locator( this.fe.frontend.imageUploadButton ).first() ).toBeHidden();
    }

    // Only the active fieldset is on screen; Next moves the field-active class
    // to the following one.
    async validateMultistepNavigation() {
        await this.assertionValidate( this.fe.frontend.multistepProgress );

        const steps = this.page.locator( this.fe.frontend.multistepFieldset );
        expect( await steps.count() ).toBeGreaterThan( 1 );
        await expect( steps.nth( 0 ) ).toHaveClass( /field-active/ );

        // Next runs the step's required-field check first, so the step has to be
        // valid before it will move.
        await this.fillStringIfAvailable( this.fe.frontend.titleField, 'Multistep step one' );
        await this.page.locator( this.fe.frontend.multistepNextButton ).first().click();
        await expect( steps.nth( 1 ) ).toHaveClass( /field-active/ );
    }

    /*********************************/
    /******** All field types ********/
    /*********************************/

    // Panel buttons carry the field slug in data-form-field, which is the stable
    // handle for "add every field type" runs.
    async isFieldTypeAvailable( slug: string ): Promise<boolean> {
        await this.doOpenPanelTab( 'Add Fields' );

        return ( await this.page.locator( `//div[@data-form-field="${ slug }"]` ).count() ) > 0;
    }

    async doAddFieldType( slug: string ) {
        await this.doOpenPanelTab( 'Add Fields' );
        await this.page.locator( `//div[@data-form-field="${ slug }"]` ).first().click();
        await this.dismissBuilderPopup();
    }

    /**
     * Adds every available type. A click is only counted as "added" when the
     * stage actually grew — fields gated on configuration (Google Map API key,
     * captcha keys, Really Simple Captcha plugin) answer with a SweetAlert and
     * add nothing, and are reported as refused with the builder's own reason.
     */
    async doAddAllFieldTypes( slugs: string[] ): Promise<{ added: string[]; unavailable: string[]; refused: Record<string, string> }> {
        const added: string[] = [];
        const unavailable: string[] = [];
        const refused: Record<string, string> = {};

        for ( const slug of slugs ) {
            if ( ! await this.isFieldTypeAvailable( slug ) ) {
                unavailable.push( slug );
                continue;
            }

            const before = await this.page.locator( this.fe.stage.allFields ).count();
            await this.doOpenPanelTab( 'Add Fields' );
            await this.page.locator( `//div[@data-form-field="${ slug }"]` ).first().click();
            await this.page.waitForTimeout( 400 );

            const popup = this.page.locator( this.fe.panel.builderPopup );
            const popupText = await popup.isVisible().catch( () => false )
                ? ( await popup.innerText() ).replace( /\s+/g, ' ' ).trim().slice( 0, 120 )
                : '';
            await this.dismissBuilderPopup();

            const after = await this.page.locator( this.fe.stage.allFields ).count();

            if ( after > before ) {
                added.push( slug );
            } else {
                refused[ slug ] = popupText || 'no field added and no reason given';
            }
        }

        return { added, unavailable, refused };
    }

    async validateStageHasFieldTypes( slugs: string[] ) {
        for ( const slug of slugs ) {
            await expect(
                this.page.locator( this.fe.stage.fieldByType( slug ) ),
                `field "${ slug }" is missing from the stage`
            ).not.toHaveCount( 0 );
        }
    }

    async getStageFieldTypes(): Promise<string[]> {
        return this.page.locator( this.fe.stage.allFields ).evaluateAll( ( items ) =>
            items.map( ( item ) => ( item.className.match( /form-field-([a-z_]+)/ ) || [] )[ 1 ] ).filter( Boolean )
        );
    }

    // Frontend row for a field, matched on the label WPUF prints as data-label.
    frontendRow( label: string ): string {
        return `//form[contains(@class,"wpuf-form")]//li[contains(@class,"wpuf-el")][@data-label="${ label }"]`;
    }

    async validateFrontendRow( label: string, control?: string ) {
        const row = this.page.locator( this.frontendRow( label ) ).first();
        await expect( row, `no frontend row rendered for "${ label }"` ).toBeAttached();

        if ( control ) {
            await expect(
                row.locator( control ).first(),
                `field "${ label }" rendered no ${ control } control`
            ).toBeAttached();
        }
    }

    // Env-dependent fields (captchas, maps, cart total) may legitimately render
    // nothing without keys/payments, so report instead of failing.
    async checkFrontendRow( label: string ): Promise<boolean> {
        return ( await this.page.locator( this.frontendRow( label ) ).count() ) > 0;
    }

    async getFrontendFieldLabels(): Promise<string[]> {
        return this.page
            .locator( '//form[contains(@class,"wpuf-form")]//li[contains(@class,"wpuf-el")]' )
            .evaluateAll( ( items ) => items.map( ( item ) => item.getAttribute( 'data-label' ) || '' ) );
    }

    /**
     * Fills every field the rendered form offers and returns what was typed,
     * keyed by the field row label, so an edit-form round-trip can verify it.
     */
    async doFillEveryField( uploadFile: string ): Promise<Record<string, string>> {
        const entered: Record<string, string> = {};
        const rows = this.page.locator( '//form[contains(@class,"wpuf-form")]//li[contains(@class,"wpuf-el")]' );

        for ( let i = 0; i < await rows.count(); i++ ) {
            const row = rows.nth( i );
            const label = ( await row.getAttribute( 'data-label' ) ) || `row-${ i }`;

            if ( ! await row.isVisible().catch( () => false ) ) {
                continue;
            }

            // uploads first — plupload swaps the row markup once a file lands
            const file = row.locator( 'input[type="file"]' );
            if ( await file.count() ) {
                await file.first().setInputFiles( uploadFile ).catch( () => {} );
                await this.page.waitForTimeout( 2500 );
                entered[ label ] = '[file]';
                continue;
            }

            // A row can hold several controls (address sub-fields, date ranges),
            // so fill every one of them, not just the first.
            const textish = row.locator( 'input[type="text"]:visible, input[type="email"]:visible, input[type="url"]:visible, input[type="number"]:visible, input[type="tel"]:visible, textarea:visible' );
            const textCount = await textish.count();

            for ( let t = 0; t < textCount; t++ ) {
                const input = textish.nth( t );
                const type = await input.getAttribute( 'type' );
                const name = ( ( await input.getAttribute( 'name' ) ) || '' ).toLowerCase();
                const placeholder = ( ( await input.getAttribute( 'placeholder' ) ) || '' ).toLowerCase();
                let value = `QA ${ label }`.slice( 0, 40 );

                if ( type === 'email' || name.includes( 'email' ) ) {
                    value = `qa.${ Date.now() % 100000 }@example.test`;
                } else if ( type === 'url' || name.includes( 'url' ) ) {
                    value = 'https://example.test/qa';
                } else if ( type === 'tel' || name.includes( 'phone' ) ) {
                    // intl-tel-input validates the number, so use a real E.164 one.
                    value = '+8801711111111';
                } else if ( type === 'number' || name.includes( 'numeric' ) || name.includes( 'price' ) ) {
                    value = '12';
                } else if ( name.includes( 'zip' ) || placeholder.includes( 'zip' ) ) {
                    value = '1207';
                } else if ( name.includes( 'city' ) ) {
                    value = 'Dhaka';
                } else if ( await input.evaluate( ( el: HTMLInputElement ) => el.classList.contains( 'wpuf-date-field' ) || ( el.dataset.type || '' ).includes( 'date' ) ) ) {
                    // the server validates dd/mm/yy on date fields
                    value = '01/01/2027';
                } else if ( name.includes( 'time' ) ) {
                    value = '10:30';
                }

                await input.fill( value ).catch( () => {} );
                entered[ label ] = value;
            }

            if ( textCount ) {
                // rows with text controls can still hold selects (address country/state)
                const rowSelects = row.locator( 'select:visible:not([multiple])' );

                for ( let sIndex = 0; sIndex < await rowSelects.count(); sIndex++ ) {
                    const select = rowSelects.nth( sIndex );
                    const options = await select.locator( 'option' ).evaluateAll( ( opts ) =>
                        opts.map( ( o ) => ( o as HTMLOptionElement ).value ).filter( ( v ) => v && v !== '-1' )
                    );

                    if ( options.length ) {
                        await select.selectOption( options[ 0 ] ).catch( () => {} );
                        await this.page.waitForTimeout( 300 );
                    }
                }

                continue;
            }

            const multiselect = row.locator( '.custom-multiselect .multiselect-dropdown label' );
            if ( await multiselect.count() ) {
                await row.locator( '.custom-multiselect .multiselect-input' ).first().click().catch( () => {} );
                await multiselect.first().click().catch( () => {} );
                await this.page.keyboard.press( 'Escape' );
                entered[ label ] = '[multiselect]';
                continue;
            }

            const select = row.locator( 'select:visible:not([multiple])' );
            const selectCount = await select.count();

            if ( selectCount ) {
                for ( let sIndex = 0; sIndex < selectCount; sIndex++ ) {
                    const current = select.nth( sIndex );
                    const options = await current.locator( 'option' ).evaluateAll( ( opts ) =>
                        opts.map( ( o ) => ( o as HTMLOptionElement ).value ).filter( ( v ) => v && v !== '-1' )
                    );

                    if ( options.length ) {
                        await current.selectOption( options[ 0 ] ).catch( () => {} );
                        entered[ label ] = options[ 0 ];
                        await this.page.waitForTimeout( 200 );
                    }
                }

                continue;
            }

            const radio = row.locator( 'input[type="radio"]:visible' );
            if ( await radio.count() ) {
                await radio.first().check( { force: true } ).catch( () => {} );
                entered[ label ] = '[radio]';
                continue;
            }

            const checkbox = row.locator( 'input[type="checkbox"]:visible' );
            if ( await checkbox.count() ) {
                await checkbox.first().check( { force: true } ).catch( () => {} );
                entered[ label ] = '[checkbox]';
                continue;
            }
        }

        // TinyMCE-backed post content
        await this.page.evaluate( () => {
            const tinymce = ( window as any ).tinymce;

            if ( tinymce ) {
                tinymce.editors.forEach( ( editor: any ) => {
                    if ( ! editor.getContent() ) {
                        editor.setContent( 'QA post content body' );
                    }
                } );
            }
        } );

        return entered;
    }

    // Reads the rendered equation (e.g. "6 - 3 =") and types the answer. The
    // captcha handler blocks submit and refreshes the equation on every attempt.
    async doSolveMathCaptcha() {
        const row = this.page.locator( this.frontendRow( 'Math Captcha' ) ).first();

        if ( ! await row.count() ) {
            return;
        }

        const answer = await row.evaluate( ( node ) => {
            const match = ( node.textContent || '' ).replace( /\s+/g, ' ' ).match( /(\d+)\s*([x+\-\/])\s*(\d+)/i );

            if ( ! match ) {
                return '';
            }

            const left = Number( match[ 1 ] );
            const right = Number( match[ 3 ] );

            switch ( match[ 2 ].toLowerCase() ) {
                case '+': return String( left + right );
                case '-': return String( left - right );
                case 'x': return String( left * right );
                default: return String( left / right );
            }
        } );

        if ( answer ) {
            await row.locator( 'input' ).first().fill( answer );
        }
    }

    // Walks any multistep wizard to the last step, filling as it goes.
    async doAdvanceAllSteps( uploadFile: string ): Promise<Record<string, string>> {
        let entered: Record<string, string> = {};

        for ( let guard = 0; guard < 8; guard++ ) {
            entered = { ...entered, ...await this.doFillEveryField( uploadFile ) };
            const next = this.page.locator( '.wpuf-multistep-next-btn:visible' ).first();

            if ( ! await next.count() ) {
                break;
            }

            await next.click();
            await this.page.waitForTimeout( 900 );
        }

        return entered;
    }

    async validateFrontendValidationErrors(): Promise<string[]> {
        return this.page
            .locator( '//form[contains(@class,"wpuf-form")]//div[contains(@class,"wpuf-error-msg") or contains(@class,"wpuf-errors")]' )
            .evaluateAll( ( nodes ) => nodes.filter( ( n ) => ( n as HTMLElement ).offsetParent !== null ).map( ( n ) => ( n.textContent || '' ).trim() ) );
    }

    /**
     * Round-trips the submitted post through WPUF's own edit form: opens the
     * dashboard, follows the Edit link (which carries the required nonce) and
     * reads back what each field row holds.
     */
    async doOpenDashboardEditForm( dashboardSlug: string, postTitle: string ) {
        await this.navigateToURL( `${ Urls.baseUrl }/${ dashboardSlug }/` );
        const editLink = this.page
            .locator( `//tr[.//*[normalize-space()="${ postTitle }"]]//a[contains(@href,"pid=") or normalize-space()="Edit"]` )
            .first();

        // The dashboard builds this href from get_permalink( edit_page_id ). With
        // that option unset the link degrades to the post's own permalink, which
        // renders no form at all — say so instead of waiting out the timeout.
        const href = await editLink.getAttribute( 'href' ) || '';
        await editLink.click();
        await this.page
            .waitForSelector( this.fe.frontend.form, { state: 'attached', timeout: 30000 } )
            .catch( () => {
                throw new Error(
                    `the dashboard Edit link did not open a WPUF edit form (href: ${ href }) — set WPUF Settings → Frontend Posting → Edit Page to a page holding [wpuf_edit]`
                );
            } );
    }

    /**
     * Points WPUF Settings → Frontend Posting → Edit Page at the given page.
     * The dashboard Edit link is built from this option, so the round-trip
     * tests need it set before they can reach the edit form.
     */
    async doSetGlobalEditPage( pageTitle: string ) {
        const settings = Selectors.settingsSetup.wpufSettingsPage;

        await this.navigateToURL( `${ Urls.baseUrl }/wp-admin/admin.php?page=wpuf-settings` );
        await this.validateAndClick( settings.settingsFrontendPosting );
        await this.selectOptionWithLabel( settings.setEditPage, pageTitle );
        await this.validateAndClick( settings.settingsFrontendPostingSave );
        await this.page.waitForLoadState( 'domcontentloaded' );
    }

    async readEditFormValues(): Promise<{ filled: string[]; empty: string[]; title: string }> {
        return this.page.evaluate( () => {
            const form = document.querySelector( 'form[class*="wpuf-form"]' );
            const filled: string[] = [];
            const empty: string[] = [];
            let title = '';

            for ( const row of Array.from( form?.querySelectorAll( 'li.wpuf-el' ) || [] ) ) {
                const label = row.getAttribute( 'data-label' ) || '';
                const controls = Array.from(
                    row.querySelectorAll< HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement >( 'input, select, textarea' )
                ).filter( ( el ) => el.type !== 'hidden' && el.type !== 'file' );

                const hasValue = controls.some( ( el ) => {
                    if ( el instanceof HTMLInputElement && ( el.type === 'checkbox' || el.type === 'radio' ) ) {
                        return el.checked;
                    }

                    return !! ( el.value && el.value !== '-1' );
                } );

                const uploaded = row.querySelectorAll( '.wpuf-image-wrap, .wpuf-attachment-list li' ).length > 0;
                const richText = ( row.querySelector( 'iframe' ) as HTMLIFrameElement | null )?.contentDocument?.body?.innerText?.trim();

                if ( row.querySelector( 'input[name="post_title"]' ) ) {
                    title = ( row.querySelector( 'input[name="post_title"]' ) as HTMLInputElement ).value;
                }

                if ( hasValue || uploaded || richText ) {
                    filled.push( label );
                } else {
                    empty.push( label );
                }
            }

            return { filled, empty, title };
        } );
    }

    /*********************************/
    /***** Field validation rules ****/
    /*********************************/

    private frontendInput( label: string ): string {
        return `${ this.frontendRow( label ) }//input | ${ this.frontendRow( label ) }//textarea`;
    }

    async doSubmitFrontendForm() {
        const submit = this.page.locator( this.fe.frontend.submitButton ).first();
        await submit.scrollIntoViewIfNeeded().catch( () => {} );
        await submit.click( { force: true } );
        await this.page.waitForTimeout( 2500 );
    }

    // WPUF reports problems in three places: per-field messages, the captcha's
    // own container, and a SweetAlert for server-side rejections.
    async getVisibleValidationErrors(): Promise<string[]> {
        return this.page.evaluate( () =>
            Array.from(
                document.querySelectorAll( '.wpuf-error-msg, .wpuf-errors, .wpuf-error, .wpuf-captcha-error, .swal2-popup' )
            )
                .filter( ( node ) => ( node as HTMLElement ).offsetParent !== null && ( node.textContent || '' ).trim() )
                .map( ( node ) => ( node.textContent || '' ).replace( /\s+/g, ' ' ).trim().slice( 0, 120 ) )
        );
    }

    // Submitting an untouched form must stop and say why.
    async validateRequiredFieldsBlockSubmit(): Promise<string[]> {
        await this.doSubmitFrontendForm();

        await expect
            .poll( async () => ( await this.getVisibleValidationErrors() ).length, {
                message: 'an empty form submitted without any validation error',
                timeout: 15000,
            } )
            .toBeGreaterThan( 0 );

        await expect( this.page.locator( this.fe.frontend.form ) ).toBeVisible();

        return this.getVisibleValidationErrors();
    }

    // Types a value into a field row and submits, returning the errors raised.
    async validateFieldRejects( label: string, value: string ): Promise<string[]> {
        const input = this.page.locator( this.frontendInput( label ) ).first();
        await input.fill( value );
        await input.blur().catch( () => {} );
        await this.page.waitForTimeout( 400 );
        await this.doSubmitFrontendForm();

        return this.getVisibleValidationErrors();
    }

    async validateHtml5Validity( label: string, value: string ): Promise<boolean> {
        const input = this.page.locator( this.frontendInput( label ) ).first();
        await input.fill( value );

        return input.evaluate( ( el: HTMLInputElement ) => el.checkValidity() );
    }

    async doAnswerMathCaptcha( answer: string ) {
        await this.page.locator( Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.mathCaptcha ).first().fill( answer );
    }

    async countFrontendRows(): Promise<{ total: number; visible: number; hidden: number }> {
        return this.page.evaluate( () => {
            const rows = Array.from( document.querySelectorAll( 'form[class*="wpuf-form"] li.wpuf-el' ) );
            const visible = rows.filter( ( row ) => ( row as HTMLElement ).offsetParent !== null ).length;

            return { total: rows.length, visible, hidden: rows.length - visible };
        } );
    }

    async validateMultistepRenderedOnFrontend() {
        await this.validateAttached( '//form[contains(@class,"wpuf-form")]//*[contains(@class,"wpuf-multistep-fieldset")]' );
    }

    /*********************************/
    /********** Negative *************/
    /*********************************/

    // Creates a subscriber over REST (admin session) and checks the builder page
    // is refused for that role. Runs in its own context so the admin session in
    // the shared page survives.
    async validateBuilderBlockedForSubscriber( formId: string, username: string, password: string ) {
        const nonce = await this.page.evaluate( () => ( window as any ).wpApiSettings?.nonce || '' );
        const api = await request.newContext( {
            baseURL: Urls.baseUrl,
            storageState: await this.page.context().storageState(),
            extraHTTPHeaders: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            ignoreHTTPSErrors: true,
        } );

        const created = await api.post( '/wp-json/wp/v2/users', {
            data: {
                username,
                email: `${ username }@example.test`,
                password,
                roles: [ 'subscriber' ],
            },
        } );
        expect( created.ok() ).toBeTruthy();

        const context = await this.page.context().browser().newContext();
        const subscriberPage = await context.newPage();

        try {
            await subscriberPage.goto( `${ Urls.baseUrl }/wp-login.php` );
            await subscriberPage.locator( '#user_login' ).fill( username );
            await subscriberPage.locator( '#user_pass' ).fill( password );
            // Wait for the login POST to land — navigating straight away races the
            // submit and can cancel it, leaving the context logged out so the
            // builder URL just bounces back to wp-login.
            // WPUF can redirect a subscriber anywhere after login, so wait for the
            // login screen itself to go away rather than for a specific URL.
            await Promise.all( [
                subscriberPage.waitForURL( ( url ) => ! url.pathname.includes( 'wp-login.php' ), { timeout: 30000 } ),
                subscriberPage.locator( '#wp-submit' ).click(),
            ] );

            await subscriberPage.goto( this.accessFormWithId + formId );

            await expect( subscriberPage.locator( 'body' ) ).toContainText( /not allowed|do not have permission|Sorry/i );
            await expect( subscriberPage.locator( this.fe.tabs.editorStage ) ).toHaveCount( 0 );
        } finally {
            await context.close();
        }
    }

    // An id that matches no form must not open an editable builder — WPUF falls
    // back to the forms list without a PHP notice/fatal.
    async validateInvalidFormIdHandled( formId: string ) {
        await this.navigateToURL( this.accessFormWithId + formId );
        await this.validateAttached( '//div[@id="wpbody-content"]' );
        await expect( this.page.locator( 'body' ) ).not.toContainText( 'Fatal error' );
        await expect( this.page.locator( 'body' ) ).not.toContainText( 'There has been a critical error' );
        await expect( this.page.locator( '//form[@id="wpuf-form-builder"]' ) ).toHaveCount( 0 );
    }

    // The form title is stored raw but must never execute when rendered.
    async validateTitleXssEscaped( payload: string, pageSlug: string ) {
        await this.validateAndFillStrings( this.fe.header.titleInput, payload );
        await this.doSaveForm();

        await this.openFrontendForm( pageSlug );
        const scripts = await this.page.locator( `//h2[contains(@class,"wpuf-form-title")]//script` ).count();
        expect( scripts ).toBe( 0 );
        await expect( this.page.locator( this.fe.frontend.formTitle ) ).toContainText( 'XSS' );
    }
}
