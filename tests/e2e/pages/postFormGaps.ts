import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { FormEditorPage } from './formEditor';
import { Urls } from '../utils/testData';

/**
 * Page object for the post-form coverage gaps listed in docs/post-form.md §7.
 *
 * Extends FormEditorPage because every one of these areas is reached through the
 * same builder chrome (Settings sidebar → panel → Save) — only the controls and
 * the frontend consequence differ. What is added here:
 *
 *   - selectize-aware select / multi-select setting helpers (the native <select>
 *     is hidden, so `selectOption()` cannot be used on these panels)
 *   - "run this as another user / as a guest" contexts, for view-control and
 *     edit-authorization assertions
 *   - the Post Forms list screen (search, row menu, bulk actions, status tabs)
 *   - the frontend messages the settings produce (restricted content, schedule
 *     notices, [wpuf_edit] refusals)
 */
export class PostFormGapsPage extends FormEditorPage {
    constructor( page: Page ) {
        super( page );
    }

    private gaps = Selectors.postFormGaps;

    /*********************************/
    /***** Settings: selectize *******/
    /*********************************/

    // Settings selects are selectize-enhanced: the real <select> is hidden and
    // the dropdown is a sibling div, so pick the option through the widget.
    async doSelectSetting( id: string, value: string ) {
        await this.validateAndClick( this.gaps.settings.selectizeInput( id ) );
        await this.validateAndClick( this.gaps.settings.selectizeOption( id, value ) );
        expect( await this.getSettingValue( id ) ).toBe( value );
    }

    async getSettingValue( id: string ): Promise<string> {
        return this.page.locator( `//select[@id="${ id }"] | //input[@id="${ id }"]` ).first().inputValue();
    }

    async validateSettingSelected( id: string, value: string ) {
        expect( await this.getSettingValue( id ) ).toBe( value );
    }

    // Multi-selects keep every pick as an .item chip inside the selectize input.
    async doPickMultiSelectSetting( id: string, optionText: string ) {
        await this.validateAndClick( this.gaps.settings.selectizeInput( id ) );
        await this.validateAndClick( this.gaps.settings.selectizeOptionByText( id, optionText ) );
        await expect( this.page.locator( this.gaps.settings.selectizeItem( id, optionText ) ).first() ).toBeVisible();
    }

    async validateMultiSelectHas( id: string, optionText: string ) {
        await expect( this.page.locator( this.gaps.settings.selectizeItem( id, optionText ) ).first() ).toBeVisible();
    }

    // Textareas/inputs in the settings panels reuse the generic id accessor.
    async validateSettingText( id: string, value: string ) {
        await expect( this.page.locator( this.fe.settings.controlById( id ) ).first() ).toHaveValue( value );
    }

    /*********************************/
    /******** Field options **********/
    /*********************************/

    // Structural fields (Section Break, Custom HTML, Shortcode, Action Hook,
    // Hidden Field, Terms & Conditions) render option inputs with no name or id,
    // so they are filled through their label.
    async doSetFieldOptionInput( label: string, value: string ) {
        await this.validateAndFillStrings( this.gaps.fieldOptions.inputByLabel( label ), value );
    }

    async doSetFieldOptionTextarea( label: string, value: string ) {
        await this.validateAndFillStrings( this.gaps.fieldOptions.textareaByLabel( label ), value );
    }

    /**
     * Show the selected field only when `watchField` satisfies `operator`
     * (`!=empty` = has any value, `==empty` = has no value). The rule row only
     * exists once Conditional Logic is switched to Yes.
     */
    async doSetConditionalLogic( watchField: string, operator: '!=empty' | '==empty' = '!=empty' ) {
        await this.doExpandAdvancedOptions();
        await this.page.locator( this.gaps.fieldOptions.conditionalYes ).first().check( { force: true } );
        await this.page.locator( this.gaps.fieldOptions.conditionalField ).first().selectOption( watchField );
        await this.page.locator( this.gaps.fieldOptions.conditionalOperator ).first().selectOption( operator );
    }

    /*********************************/
    /***** Other-user sessions *******/
    /*********************************/

    /**
     * Runs `work` in a throwaway context logged in as the given user, so the
     * admin session on the shared page survives. Returns whatever `work` returns.
     */
    async withUser<T>( username: string, password: string, work: ( page: Page ) => Promise<T> ): Promise<T> {
        const context = await this.page.context().browser().newContext( { ignoreHTTPSErrors: true } );
        const userPage = await context.newPage();

        try {
            await userPage.goto( `${ Urls.baseUrl }/wp-login.php` );
            await userPage.locator( '#user_login' ).fill( username );
            await userPage.locator( '#user_pass' ).fill( password );
            await userPage.locator( '#wp-submit' ).click();
            await userPage.waitForLoadState( 'domcontentloaded' );

            return await work( userPage );
        } finally {
            await context.close();
        }
    }

    /** Same, but logged out — for "what does a visitor see" assertions. */
    async withGuest<T>( work: ( page: Page ) => Promise<T> ): Promise<T> {
        const context = await this.page.context().browser().newContext( { ignoreHTTPSErrors: true } );
        const guestPage = await context.newPage();

        try {
            return await work( guestPage );
        } finally {
            await context.close();
        }
    }

    /*********************************/
    /********* View control **********/
    /*********************************/

    async doEnableRoleViewControl( role: string, message: string ) {
        await this.doOpenSettingsSection( 'General' );
        await this.doToggleSetting( 'view_control_role_enabled', true );
        await this.doPickMultiSelectSetting( 'view_control_roles', role );
        await this.doFillSetting( 'view_control_role_message', message );
        await this.doSaveForm();
    }

    async doEnableSubscriptionViewControl( packName: string, message: string ) {
        await this.doOpenSettingsSection( 'General' );
        await this.doToggleSetting( 'view_control_subscription_enabled', true );
        await this.doPickMultiSelectSetting( 'view_control_subscriptions', packName );
        await this.doFillSetting( 'view_control_subscription_message', message );
        await this.doSaveForm();
    }

    async doDisableViewControl() {
        await this.doOpenSettingsSection( 'General' );
        await this.doToggleSetting( 'view_control_role_enabled', false );
        await this.doToggleSetting( 'view_control_subscription_enabled', false );
        await this.doSaveForm();
    }

    /** The post shows its own content — i.e. the viewer passed view control. */
    async validatePostContentVisible( page: Page, postUrl: string, content: string ) {
        await page.goto( postUrl );
        await expect( page.locator( this.gaps.frontend.restrictedContent ) ).toHaveCount( 0 );
        await expect( page.locator( 'body' ) ).toContainText( content );
    }

    /** The post is replaced by the configured unauthorized message. */
    async validatePostRestricted( page: Page, postUrl: string, message: string, hiddenContent: string ) {
        await page.goto( postUrl );
        await expect( page.locator( this.gaps.frontend.restrictedContent ).first() ).toBeVisible();
        await expect( page.locator( this.gaps.frontend.restrictedContent ).first() ).toContainText( message );
        await expect( page.locator( 'body' ) ).not.toContainText( hiddenContent );
    }

    /*********************************/
    /**** Display / advanced *********/
    /*********************************/

    /**
     * "Use Theme CSS" on a layout1 form swaps WPUF's own styling for the theme's:
     * Frontend_Render_Form adds `wpuf-theme-style` to the <form> and stops
     * enqueuing the layout stylesheet (includes/Frontend_Render_Form.php:288).
     */
    async validateThemeCssUsed( pageSlug: string, expected: boolean ) {
        await this.openFrontendForm( pageSlug );
        const form = this.page.locator( this.fe.frontend.form ).first();

        if ( expected ) {
            await expect( form ).toHaveClass( /wpuf-theme-style/ );
        } else {
            await expect( form ).not.toHaveClass( /wpuf-theme-style/ );
        }
    }

    // Label position paints form-label-<position> on the form's <ul>.
    async validateLabelPosition( pageSlug: string, position: string ) {
        await this.openFrontendForm( pageSlug );
        await expect(
            this.page.locator( `//ul[contains(@class,"wpuf-form")][contains(@class,"form-label-${ position }")]` ).first()
        ).toBeAttached();
    }

    async doScheduleForm( start: string, end: string, pendingMessage: string, expiredMessage: string ) {
        await this.doOpenSettingsSection( 'Advanced' );
        await this.doToggleSetting( 'schedule_form', true );
        await this.doFillSetting( 'schedule_start', start );
        await this.doFillSetting( 'schedule_end', end );
        // The date inputs carry a picker overlay that swallows the next click.
        await this.page.keyboard.press( 'Escape' );
        await this.doFillSetting( 'form_pending_message', pendingMessage );
        await this.doFillSetting( 'form_expired_message', expiredMessage );
        await this.doSaveForm();
    }

    async doUnscheduleForm() {
        await this.doOpenSettingsSection( 'Advanced' );
        await this.doToggleSetting( 'schedule_form', false );
        await this.doSaveForm();
    }

    /**
     * Outside its schedule WPUF does NOT remove the form — it prints the
     * pending/expired notice and disables the submit button
     * (wpuf_show_form_schedule_message in wpuf-functions.php), so assert both.
     */
    async validateFormScheduleNotice( pageSlug: string, message: string ) {
        await this.navigateToURL( `${ Urls.baseUrl }/${ pageSlug }/` );
        await expect( this.page.locator( this.gaps.frontend.formMessage ).first() ).toContainText( message );
        await expect( this.page.locator( this.fe.frontend.submitButton ).first() ).toBeDisabled();
    }

    async validateFormSubmittable( pageSlug: string ) {
        await this.navigateToURL( `${ Urls.baseUrl }/${ pageSlug }/` );
        await expect( this.page.locator( this.fe.frontend.form ).first() ).toBeVisible();
        await expect( this.page.locator( this.fe.frontend.submitButton ).first() ).toBeEnabled();
    }

    /*********************************/
    /********* Edit access ***********/
    /*********************************/

    /**
     * Reads the dashboard's Edit href for a post. It carries the `wpuf_edit`
     * nonce, which the edit shortcode demands before it looks at `pid` at all —
     * so authorization tests reuse the viewer's own link and swap the pid.
     */
    async getDashboardEditHref( page: Page, dashboardSlug: string, postTitle: string ): Promise<string> {
        await page.goto( `${ Urls.baseUrl }/${ dashboardSlug }/` );

        // A published row wraps its title in an <a>, but a draft row prints the
        // title as bare text next to a `.post-edit-icon` span, so no single element
        // normalizes to exactly the title. Match on the row's text instead.
        const link = page
            .locator( `//tr[contains(., "${ postTitle }")]//a[contains(@href,"pid=")]` )
            .first();

        return ( await link.getAttribute( 'href' ) ) || '';
    }

    static withPid( href: string, pid: number | string ): string {
        return href.replace( /pid=\d+/, `pid=${ pid }` );
    }

    async validateEditRefused( page: Page, url: string, message: RegExp ) {
        await page.goto( url );
        await expect( page.locator( this.gaps.frontend.infoMessage ).first() ).toContainText( message );
        await expect( page.locator( this.fe.frontend.form ) ).toHaveCount( 0 );
    }

    async validateEditFormOpens( page: Page, url: string ) {
        await page.goto( url );
        await expect( page.locator( this.fe.frontend.form ).first() ).toBeVisible();
    }

    /*********************************/
    /******** Forms list page ********/
    /*********************************/

    async openFormsList() {
        await this.navigateToURL( this.wpufPostFormPage );
        await this.page.waitForSelector( this.gaps.formList.searchInput );
    }

    async doSearchForms( term: string ) {
        await this.validateAndFillStrings( this.gaps.formList.searchInput, term );
        // The table filters as you type; give the debounce a beat.
        await this.page.waitForTimeout( 800 );
    }

    async getListedFormNames(): Promise<string[]> {
        return this.page
            .locator( `${ this.gaps.formList.allRows }//span[1]` )
            .evaluateAll( ( nodes ) => nodes.map( ( node ) => ( node.textContent || '' ).trim() ) );
    }

    async doRowMenuAction( formName: string, action: 'Edit' | 'Duplicate' | 'Trash' ) {
        await this.validateAndClick( this.gaps.formList.rowMenuToggle( formName ) );
        await this.validateAndClick( this.gaps.formList.rowMenuItem( action ) );
        // Trash/Duplicate re-render the table through the REST list call.
        await this.page.waitForTimeout( 1500 );
        await this.dismissBuilderPopup();
    }

    async validateFormListed( formName: string, listed: boolean ) {
        await expect( this.page.locator( this.gaps.formList.row( formName ) ) )
            .toHaveCount( listed ? 1 : 0 );
    }

    async getFormIdFromRow( formName: string ): Promise<string> {
        return ( await this.page.locator( this.gaps.formList.rowCheckbox( formName ) ).first().inputValue() ).trim();
    }

    /*********************************/
    /********* Field output **********/
    /*********************************/

    /** Field rows are matched by their printed label (data-label). */
    async validateFrontendText( text: string ) {
        await expect( this.page.locator( this.fe.frontend.form ).first() ).toContainText( text );
    }

    async countFrontendMatches( selector: string ): Promise<number> {
        return this.page.locator( selector ).count();
    }

    /**
     * True when the rendered form executed the given shortcode/hook probe —
     * both are proven by the marker text the probe prints, never by the raw tag.
     */
    async validateProbeRendered( marker: string, rawTag?: string ) {
        await expect( this.page.locator( 'body' ) ).toContainText( marker );

        if ( rawTag ) {
            await expect( this.page.locator( 'body' ) ).not.toContainText( rawTag );
        }
    }
}
