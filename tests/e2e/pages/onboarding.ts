import dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';

/**
 * Guided onboarding wizard.
 *
 * The wizard renders its own document rather than sitting inside the WP admin
 * chrome, so there is no admin sidebar on these screens. Anything that needs the
 * sidebar (menu visibility) is asserted from a normal admin page instead.
 */
export class OnboardingPage extends Base {

    readonly wizardUrl = `${Urls.baseUrl}/wp-admin/index.php?page=wpuf-onboarding`;
    readonly toolsUrl = `${Urls.baseUrl}/wp-admin/admin.php?page=wpuf_tools&tab=tools`;
    readonly settingsUrl = `${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-settings`;
    readonly adminHomeUrl = `${Urls.baseUrl}/wp-admin/index.php`;

    constructor(page: Page) {
        super(page);
    }

    /**************************************************/
    /*************** @Navigation *********************/
    /************************************************/

    async gotoWizard(step: string = '') {
        const url = step ? `${this.wizardUrl}&step=${step}` : this.wizardUrl;
        await this.navigateToURL(url);
    }

    async gotoTools() {
        await this.navigateToURL(this.toolsUrl);
    }

    /**
     * Whether Pro is active, decided from what the site actually renders rather
     * than from an env flag, so one spec covers both builds.
     *
     * The signal is the Premium menu, which free registers and Pro does not. The
     * registration forms menu looks like a Pro marker but is not: free registers
     * that one as well.
     */
    async isProActive(): Promise<boolean> {
        await this.navigateToURL(this.adminHomeUrl);

        return await this.page.locator(Selectors.onboardingMenus.premiumMenu).count() === 0;
    }

    /**************************************************/
    /*************** @Wizard chrome ******************/
    /************************************************/

    async wizardIsOpen(): Promise<boolean> {
        return await this.page.locator(Selectors.onboarding.chrome.stepRail).count() > 0;
    }

    async getRailLabels(): Promise<string[]> {
        const labels = await this.page.locator(Selectors.onboarding.chrome.railLabels).allTextContents();

        return labels.map( label => label.trim() );
    }

    async getActiveStepLabel(): Promise<string> {
        return ( await this.page.locator(Selectors.onboarding.chrome.activeStep).first().textContent() || '' ).trim();
    }

    /**
     * What the rail markers actually contain.
     *
     * Every marker should draw a check icon and none should print a digit, matching
     * the User Directory wizard. Reported as counts rather than a bare boolean so a
     * failure says which half broke.
     */
    async getMarkerReport(): Promise<{ markers: number; icons: number; digits: number; digitText: string[] }> {
        return await this.page.evaluate( () => {
            const markers = Array.from( document.querySelectorAll( '.wpuf-onboarding-steps .wpuf-step-marker' ) );
            const digitText = markers
                .map( marker => ( marker.textContent || '' ).trim() )
                .filter( text => /\d/.test( text ) );

            return {
                markers: markers.length,
                icons: markers.filter( marker => !! marker.querySelector( 'svg' ) ).length,
                digits: digitText.length,
                digitText,
            };
        } );
    }

    async continueStep() {
        await this.validateAndClick(Selectors.onboarding.chrome.continueButton);
        await this.waitForLoading();
    }

    async skipStep() {
        await this.validateAndClick(Selectors.onboarding.chrome.skipLink);
        await this.waitForLoading();
    }

    /**************************************************/
    /*************** @Step: features *****************/
    /************************************************/

    /**
     * Tick exactly the features named and untick the rest, so a caller states the
     * whole intended state rather than a delta.
     */
    async setFeatures( wanted: string[] ) {
        const boxes: Record<string, string> = {
            post_form: Selectors.onboarding.features.postFormCheckbox,
            registration: Selectors.onboarding.features.registrationCheckbox,
            user_directory: Selectors.onboarding.features.userDirectoryCheckbox,
            payments: Selectors.onboarding.features.paymentsCheckbox,
        };

        for ( const [ feature, locator ] of Object.entries( boxes ) ) {
            const box = this.page.locator( locator );

            if ( await box.count() === 0 ) {
                continue;
            }

            const shouldBeOn = wanted.includes( feature );

            if ( await box.isChecked() !== shouldBeOn ) {
                await box.setChecked( shouldBeOn );
            }
        }
    }

    async getFeatureStates(): Promise<Record<string, boolean>> {
        const boxes: Record<string, string> = {
            post_form: Selectors.onboarding.features.postFormCheckbox,
            registration: Selectors.onboarding.features.registrationCheckbox,
            user_directory: Selectors.onboarding.features.userDirectoryCheckbox,
            payments: Selectors.onboarding.features.paymentsCheckbox,
        };
        const state: Record<string, boolean> = {};

        for ( const [ feature, locator ] of Object.entries( boxes ) ) {
            const box = this.page.locator( locator );
            state[ feature ] = await box.count() > 0 ? await box.isChecked() : false;
        }

        return state;
    }

    /**************************************************/
    /*************** @Step: post form ****************/
    /************************************************/

    async setPostFormOptions( allowEdit: boolean, allowDelete: boolean ) {
        await this.setCheckboxIfPresent( Selectors.onboarding.postForm.enablePostEdit, allowEdit );
        await this.setCheckboxIfPresent( Selectors.onboarding.postForm.enablePostDelete, allowDelete );
    }

    /**************************************************/
    /*************** @Step: registration *************/
    /************************************************/

    async setAutologin( on: boolean ) {
        await this.setCheckboxIfPresent( Selectors.onboarding.registration.autologinCheckbox, on );
    }

    /**
     * State of the login layout picker.
     *
     * Layouts are a Pro feature, so without Pro every radio must be disabled and the
     * basic layout is what stays selected, rather than a locked preview of whatever
     * a previously-active Pro build had stored.
     */
    async getLayoutPickerState(): Promise<{ total: number; disabled: number; checked: string | null; previewLabel: string }> {
        return await this.page.evaluate( () => {
            const radios = Array.from(
                document.querySelectorAll( 'input[name="wpuf_login_form_layout"]' )
            ) as HTMLInputElement[];

            return {
                total: radios.length,
                disabled: radios.filter( radio => radio.disabled ).length,
                checked: radios.find( radio => radio.checked )?.value ?? null,
                previewLabel: ( document.querySelector( '#wpuf-onboarding-layout-name' )?.textContent || '' ).trim(),
            };
        } );
    }

    /**
     * Page pickers are only useful when the site already has a page to pick, which
     * is exactly what the first-install page creation is meant to guarantee.
     */
    async getPageSelectOptionCount( locator: string ): Promise<number> {
        const select = this.page.locator( locator );

        if ( await select.count() === 0 ) {
            return 0;
        }

        return await select.locator( 'option' ).count();
    }

    /**************************************************/
    /*************** @Step: settings *****************/
    /************************************************/

    async setCommonOptions( opts: {
        installPages?: boolean;
        hideAdminBar?: boolean;
        addLogoutMenu?: boolean;
        enablePayments?: boolean;
    } ) {
        if ( opts.installPages !== undefined ) {
            await this.setCheckboxIfPresent( Selectors.onboarding.common.installPages, opts.installPages );
        }

        if ( opts.hideAdminBar !== undefined ) {
            await this.setCheckboxIfPresent( Selectors.onboarding.common.hideAdminBar, opts.hideAdminBar );
        }

        if ( opts.addLogoutMenu !== undefined ) {
            await this.setCheckboxIfPresent( Selectors.onboarding.common.addLogoutMenu, opts.addLogoutMenu );
        }

        if ( opts.enablePayments !== undefined ) {
            await this.setCheckboxIfPresent( Selectors.onboarding.common.enablePayments, opts.enablePayments );
        }
    }

    /**
     * Gateway cards, described the way the assertions need them: every card should
     * carry a logo, and the PRO badge should appear only while Pro is inactive.
     */
    async getGatewayCards(): Promise<Array<{ label: string; hasIcon: boolean; isPro: boolean }>> {
        return await this.page.evaluate( () => {
            const grid = document.querySelector( '.wpuf-onboarding-grid.is-thirds' );

            if ( ! grid ) {
                return [];
            }

            return Array.from( grid.children ).map( card => ( {
                label: ( card.querySelector( 'strong' )?.textContent || '' ).trim().replace( /\s+/g, ' ' ),
                hasIcon: !! card.querySelector( '.wpuf-onboarding-card-icon img, .wpuf-onboarding-card-icon svg' ),
                isPro: card.classList.contains( 'is-pro' ),
            } ) );
        } );
    }

    /**
     * The badge asset is a 39x22 pill. Rendering it square means it is squashed,
     * so the ratio is what this checks, not the pixel size.
     */
    async proBadgeKeepsAspectRatio(): Promise<boolean> {
        return await this.page.evaluate( () => {
            const badge = document.querySelector( '.wpuf-onboarding-pro-badge' ) as HTMLImageElement | null;

            if ( ! badge || ! badge.naturalWidth ) {
                return false;
            }

            const box = badge.getBoundingClientRect();

            return Math.abs( ( badge.naturalWidth / badge.naturalHeight ) - ( box.width / box.height ) ) < 0.1;
        } );
    }

    /**
     * Logo and title should sit on the card's centre line, the way the settings
     * screen draws its gateway cards.
     */
    async gatewayCardsAreCentred(): Promise<boolean> {
        return await this.page.evaluate( () => {
            const grid = document.querySelector( '.wpuf-onboarding-grid.is-thirds' );

            if ( ! grid ) {
                return false;
            }

            return Array.from( grid.children ).every( card => {
                const cardBox = card.getBoundingClientRect();
                const icon = card.querySelector( '.wpuf-onboarding-card-icon' );
                const title = card.querySelector( 'strong' );

                if ( ! icon || ! title ) {
                    return false;
                }

                const centre = cardBox.left + cardBox.width / 2;
                const iconBox = icon.getBoundingClientRect();
                const titleBox = title.getBoundingClientRect();

                return Math.abs( ( iconBox.left + iconBox.width / 2 ) - centre ) < 2
                    && Math.abs( ( titleBox.left + titleBox.width / 2 ) - centre ) < 2;
            } );
        } );
    }

    /**************************************************/
    /*************** @Step: ready ********************/
    /************************************************/

    async getChecklistRows(): Promise<string[]> {
        const rows = await this.page.locator(Selectors.onboarding.ready.checklistRows).allTextContents();

        return rows.map( row => row.trim().replace( /\s+/g, ' ' ) );
    }

    /**************************************************/
    /*************** @Tools entry point **************/
    /************************************************/

    async getEntryButtonLabel(): Promise<string> {
        await this.gotoTools();

        return ( await this.page.locator(Selectors.onboarding.entry.startButton).first().textContent() || '' ).trim();
    }

    async rerunWarningIsVisible(): Promise<boolean> {
        await this.gotoTools();

        return await this.page.locator(Selectors.onboarding.entry.rerunWarning).count() > 0;
    }

    async startFromTools() {
        await this.gotoTools();
        await this.validateAndClick(Selectors.onboarding.entry.startButton);
        await this.waitForLoading();
    }

    /**************************************************/
    /*************** @Admin menu assertions **********/
    /************************************************/

    async getWpufSubmenuLabels(): Promise<string[]> {
        await this.navigateToURL(this.adminHomeUrl);

        const labels = await this.page.locator(Selectors.onboardingMenus.submenuLinks).allTextContents();

        return labels.map( label => label.trim() );
    }

    async menuIsVisible( locator: string ): Promise<boolean> {
        await this.navigateToURL(this.adminHomeUrl);

        return await this.page.locator( locator ).count() > 0;
    }

    /**************************************************/
    /*************** @Settings assertions ************/
    /************************************************/

    /**
     * Read a settings checkbox back on the screen the admin would actually visit,
     * so a wizard choice is proven where it is meant to show up.
     */
    async settingsCheckboxIsOn( locator: string, tab: string = '' ): Promise<boolean> {
        const url = tab ? `${this.settingsUrl}#${tab}` : this.settingsUrl;
        await this.navigateToURL( url );

        const box = this.page.locator( locator );

        if ( await box.count() === 0 ) {
            return false;
        }

        return await box.isChecked();
    }

    /**
     * Read a settings dropdown back on the settings screen. Some of what the wizard
     * writes lands in a yes/no select rather than a checkbox.
     */
    async settingsSelectValue( locator: string ): Promise<string> {
        await this.navigateToURL( this.settingsUrl );

        const select = this.page.locator( locator );

        if ( await select.count() === 0 ) {
            return '';
        }

        return await select.inputValue();
    }

    /**************************************************/
    /*************** @Helpers ************************/
    /************************************************/

    /**
     * Set a checkbox only when the step actually renders it. Steps drop controls
     * for features that were switched off, so a missing control is a valid state
     * rather than a failure.
     */
    async setCheckboxIfPresent( locator: string, checked: boolean ): Promise<boolean> {
        const box = this.page.locator( locator );

        if ( await box.count() === 0 ) {
            return false;
        }

        if ( await box.isChecked() !== checked ) {
            await box.setChecked( checked );
        }

        return true;
    }
}
