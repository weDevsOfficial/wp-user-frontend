import dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';

/**
 * Guided onboarding wizard.
 *
 * The wizard is the admin app route #/onboarding/:step (React), full screen:
 * the WordPress chrome is hidden there. Anything that needs the sidebar (menu
 * visibility) is asserted from a normal admin page instead. Steps save over
 * wpuf/v1/onboarding/{step} and move on without a page load.
 */
export class OnboardingPage extends Base {

    readonly wizardUrl = `${Urls.baseUrl}/wp-admin/index.php?page=wpuf-onboarding`;
    readonly toolsUrl = `${Urls.baseUrl}/wp-admin/admin.php?page=wpuf_tools&tab=tools`;
    // Read-backs use the classic settings screen (its field ids); the React
    // screen is the default and has its own spec.
    readonly settingsUrl = `${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-settings`;
    readonly adminHomeUrl = `${Urls.baseUrl}/wp-admin/index.php`;

    constructor(page: Page) {
        super(page);
    }

    /**************************************************/
    /*************** @Navigation *********************/
    /************************************************/

    /** A wpuf/v1/onboarding request (pretty or ?rest_route= permalinks). */
    private isWizardCall( url: string, visit: boolean ): boolean {
        const decoded = decodeURIComponent( url );

        if ( ! decoded.includes( 'wpuf/v1/onboarding/' ) ) {
            return false;
        }

        return /\/onboarding\/[a-z_]+\/visit/.test( decoded ) === visit;
    }

    /**
     * Open a step and wait until the app has recorded the visit (the ready step
     * finishes the run on that visit).
     */
    async gotoWizard(step: string = '') {
        const url = step ? `${this.wizardUrl}&step=${step}` : this.wizardUrl;
        const visited = this.page.waitForResponse(
            response => this.isWizardCall( response.url(), true ) && response.request().method() === 'POST',
            { timeout: 30000 }
        ).catch( () => null );

        await this.navigateToURL(url);
        await this.page.locator(Selectors.onboarding.chrome.stepRail).waitFor( { timeout: 30000 } );
        await visited;
        await this.settle();
    }

    /** Let the step's entrance (text and panel reveals) finish before measuring. */
    async settle() {
        await this.page.evaluate( () => Promise.all(
            document.getAnimations()
                .filter( animation => Number.isFinite( Number( animation.effect?.getComputedTiming().endTime ) ) )
                .map( animation => animation.finished.catch( () => null ) )
        ) ).catch( () => {} );
    }

    async gotoTools() {
        await this.navigateToURL(this.toolsUrl);
        // React Tools route: wait for the Onboarding card before reading it.
        await this.page.locator(Selectors.onboarding.entry.onboardingBox).waitFor({ timeout: 30000 }).catch(() => {});
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
        return await this.page.locator(Selectors.onboarding.chrome.stepRail).waitFor( { timeout: 30000 } ).then( () => true ).catch( () => false );
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

    /** Save the step: wait for its save call, then for the next step to open. */
    async continueStep() {
        const saved = this.page.waitForResponse(
            response => this.isWizardCall( response.url(), false ) && response.request().method() === 'POST',
            { timeout: 120000 }
        );

        await this.validateAndClick(Selectors.onboarding.chrome.continueButton);
        await saved;
        await this.page.waitForLoadState( 'networkidle' ).catch( () => {} );
    }

    async skipStep() {
        await this.validateAndClick(Selectors.onboarding.chrome.skipLink);
        await this.page.waitForLoadState( 'networkidle' ).catch( () => {} );
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
                await box.click();

                // Unticking a User Directory that is running asks first (owner
                // decision, 4.7): confirm, or the step keeps it on.
                if ( ! shouldBeOn ) {
                    const confirmOff = this.page.getByRole( 'button', { name: 'Turn it off' } );

                    if ( await confirmOff.waitFor( { timeout: 1500 } ).then( () => true ).catch( () => false ) ) {
                        await confirmOff.click();
                        await confirmOff.waitFor( { state: 'hidden' } );
                    }
                }
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

    /**
     * Make sure the payments feature is on, so the settings step renders its
     * gateway picker. Saves the picker step, since the choice only takes effect
     * once it is submitted.
     */
    async enablePaymentsFeature() {
        await this.gotoWizard( 'features' );

        const current = await this.getFeatureStates();

        if ( current.payments ) {
            return;
        }

        const wanted = Object.keys( current ).filter( key => current[ key ] );

        wanted.push( 'payments' );

        await this.setFeatures( wanted );
        await this.continueStep();
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
        const change = this.page.locator( Selectors.onboarding.registration.layoutChange );

        // The picker folds away; "Change" opens it.
        if ( await change.count() > 0 && await change.getAttribute( 'aria-expanded' ) !== 'true' ) {
            await change.click();
        }

        return await this.page.evaluate( () => {
            const radios = Array.from(
                document.querySelectorAll( '.wpuf-onboarding-layouts [role="radio"]' )
            ) as HTMLButtonElement[];

            return {
                total: radios.length,
                disabled: radios.filter( radio => radio.disabled ).length,
                checked: radios.find( radio => radio.getAttribute( 'aria-checked' ) === 'true' )?.dataset.value ?? null,
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

        // A plugin-ui select: its options exist while the list is open.
        await select.click();

        const options = this.page.locator( '[role="option"]' ).filter( { visible: true } );

        await options.first().waitFor( { timeout: 5000 } ).catch( () => {} );

        const count = await options.count();

        await this.page.keyboard.press( 'Escape' );

        return count;
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
    async getGatewayCards(): Promise<Array<{ label: string; hasIcon: boolean; isPro: boolean; isUnavailable: boolean; hint: string; height: number }>> {
        return await this.page.evaluate( () => {
            const grid = document.querySelector( '.wpuf-onboarding-grid.is-thirds' );

            if ( ! grid ) {
                return [];
            }

            return Array.from( grid.children ).map( card => ( {
                label: ( card.querySelector( 'strong' )?.textContent || '' ).trim().replace( /\s+/g, ' ' ),
                hasIcon: !! card.querySelector( '.wpuf-onboarding-card-icon img, .wpuf-onboarding-card-icon svg' ),
                // The badge is the claim being made, so read that rather than a class
                // that also covers a gateway this site owns but has not switched on.
                isPro: !! card.querySelector( '.wpuf-onboarding-pro-badge' ),
                isUnavailable: card.classList.contains( 'is-unavailable' ),
                hint: ( card.querySelector( '.wpuf-onboarding-card-hint' )?.textContent || '' ).trim(),
                height: Math.round( card.getBoundingClientRect().height ),
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

    /**
     * Every image the wizard renders, described well enough to prove it is a
     * vector that actually loaded rather than a broken or raster asset.
     */
    async getImageReport(): Promise<Array<{ file: string; loaded: boolean; isSvg: boolean; naturalW: number }>> {
        return await this.page.evaluate( () => {
            const imgs = Array.from( document.querySelectorAll( 'img' ) ) as HTMLImageElement[];

            return imgs
                .filter( img => ( img.getAttribute( 'src' ) || '' ).includes( '/images/' ) )
                .map( img => {
                    const src = img.getAttribute( 'src' ) || '';

                    return {
                        file: src.split( '/' ).pop()!.split( '?' )[0],
                        // A broken image reports a natural width of 0 once it has settled.
                        loaded: img.complete && img.naturalWidth > 0,
                        isSvg: /\.svg$/i.test( src.split( '?' )[0] ),
                        naturalW: img.naturalWidth,
                    };
                } );
        } );
    }

    /**
     * The plugin logos on the companion plugins step, in card order.
     */
    async getPluginLogos(): Promise<Array<{ name: string; file: string; loaded: boolean }>> {
        return await this.page.evaluate( () =>
            Array.from( document.querySelectorAll( '.wpuf-onboarding-card' ) )
                .filter( card => card.querySelector( '.wpuf-onboarding-logo' ) )
                .map( card => {
                    const img = card.querySelector( '.wpuf-onboarding-logo' ) as HTMLImageElement;

                    return {
                        name: ( card.querySelector( 'strong' )?.textContent || '' ).trim(),
                        file: ( img.getAttribute( 'src' ) || '' ).split( '/' ).pop() || '',
                        loaded: img.complete && img.naturalWidth > 0,
                    };
                } )
        );
    }

    /**
     * How the required controls on the current step are marked.
     */
    async getRequiredMarkers(): Promise<{ marks: number; srWords: number; requiredControls: number }> {
        return await this.page.evaluate( () => {
            const marks = Array.from( document.querySelectorAll( '.wpuf-onboarding-required' ) );

            return {
                marks: marks.length,
                srWords: document.querySelectorAll( '.wpuf-onboarding-required + .screen-reader-text' ).length,
                // Each marked label names the control it belongs to.
                requiredControls: marks.filter( mark => {
                    const target = mark.closest( 'label' )?.getAttribute( 'for' );

                    return !! target && !! document.getElementById( target );
                } ).length,
            };
        } );
    }

    /** The wizard's enqueued script files. */
    async getScriptReport(): Promise<{ externalFiles: string[] }> {
        return await this.page.evaluate( () => ( {
            externalFiles: Array.from( document.querySelectorAll( 'script[src]' ) )
                .map( tag => ( tag as HTMLScriptElement ).src.split( '/' ).pop()!.split( '?' )[0] )
                .filter( name => name.indexOf( 'onboarding' ) !== -1 ),
        } ) );
    }

    /**
     * Horizontal overflow and rail-label collisions at the current viewport.
     */
    async getLayoutHealth(): Promise<{ overflow: number; labelOverlaps: number }> {
        return await this.page.evaluate( () => {
            const root = document.documentElement;
            const boxes = Array.from( document.querySelectorAll( '.wpuf-step-label' ) )
                .filter( label => ( label as HTMLElement ).offsetParent !== null )
                .map( label => label.getBoundingClientRect() )
                .sort( ( a, b ) => a.left - b.left );

            let overlaps = 0;

            for ( let i = 1; i < boxes.length; i++ ) {
                if ( boxes[ i ].left < boxes[ i - 1 ].right - 1 ) {
                    overlaps++;
                }
            }

            return { overflow: root.scrollWidth - root.clientWidth, labelOverlaps: overlaps };
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
     * The stored settings value a classic field locator points at (`wpuf_section[key]`
     * in its id / name), read through the React screen's REST route on the admin page.
     */
    async settingsValueOf( locator: string ): Promise<unknown> {
        const match = locator.match( /(wpuf_[a-z_]+)\[([a-z0-9_]+)\]/ );

        if ( ! match ) {
            throw new Error( `No settings field in locator ${ locator }` );
        }

        await this.navigateToURL( this.settingsUrl );
        await this.page.waitForFunction( () => !! ( window as any ).wp?.apiFetch );
        const res = await this.page.evaluate( () => ( window as any ).wp.apiFetch( { path: '/wpuf/v1/settings' } ) );

        return ( ( res.data.values || {} )[ match[ 1 ] ] || {} )[ match[ 2 ] ];
    }

    /** Whether a settings checkbox / toggle the wizard set is on (stored value). */
    async settingsCheckboxIsOn( locator: string, tab: string = '' ): Promise<boolean> { // eslint-disable-line @typescript-eslint/no-unused-vars
        return [ 'on', 'yes', 'true', '1', true ].includes( await this.settingsValueOf( locator ) as any );
    }

    /** The stored value of a settings select the wizard set. */
    async settingsSelectValue( locator: string ): Promise<string> {
        const value = await this.settingsValueOf( locator );

        return null === value || undefined === value ? '' : String( value );
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
