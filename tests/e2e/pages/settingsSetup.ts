import dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page, type Dialog } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls, Users } from '../utils/testData';
import { Base } from './base';
import { OnboardingPage } from './onboarding';
import { waitForSiteReady } from '../utils/siteReady';
import { clearSavedSession } from '../utils/authSession';
export class SettingsSetupPage extends Base {

    constructor(page: Page) {
        super(page);

    }

    // Settings are written through the React screen's REST route (wpuf/v1/settings),
    // with wp.apiFetch on the admin page (its nonce, the same sanitising store as the
    // screen's Save). The classic settings screen is gone.
    private settingsCache: any = null;

    async settingsPayload(fresh = false): Promise<any> {
        if (!fresh && this.settingsCache) {
            return this.settingsCache;
        }
        if (!this.page.url().includes('/wp-admin/')) {
            await this.navigateToURL(this.wpufSettingsPage);
        }
        await this.page.waitForFunction(() => !!(window as any).wp?.apiFetch);
        const res = await this.page.evaluate(() => (window as any).wp.apiFetch({ path: '/wpuf/v1/settings' }));
        this.settingsCache = res.data;
        return this.settingsCache;
    }

    // A field by its label (or description, for checkboxes labelled by their desc).
    async settingsField(label: string, section?: string): Promise<{ section: string; name: string; field: any }> {
        const data = await this.settingsPayload();
        const clean = (t: any) => String(t || '').replace(/<[^>]*>/g, '').trim();
        for (const sid of Object.keys(data.fields || {})) {
            if (section && sid !== section) {
                continue;
            }
            for (const field of data.fields[sid] || []) {
                if (clean(field.label) === label || clean(field.desc) === label) {
                    return { section: sid, name: field.name, field };
                }
            }
        }
        throw new Error(`Settings field not found: ${label}`);
    }

    // The stored key of an option by its label (select / radio options).
    optionKey(field: any, label: string): string {
        const options = field.options || {};
        const entries = Array.isArray(options) ? options.map((o: any) => [o.value, o.label]) : Object.entries(options).map(([k, v]: any) => [k, typeof v === 'object' ? v.label : v]);
        const hit = entries.find(([, l]: any) => String(l).trim() === label);
        if (!hit) {
            throw new Error(`Option "${label}" not found in ${field.name}`);
        }
        return String(hit[0]);
    }

    async saveSettings(settings: Record<string, Record<string, any>>) {
        await this.settingsPayload();
        const res = await this.page.evaluate((body) => (window as any).wp.apiFetch({ path: '/wpuf/v1/settings', method: 'POST', data: { settings: body } }), settings);
        expect(res.success).toBeTruthy();
        this.settingsCache = null;
        return res;
    }

    async settingValue(section: string, name: string): Promise<any> {
        const data = await this.settingsPayload(true);
        return (data.values[section] || {})[name];
    }

    // Set fields by label: { 'Login Page': value } with option labels resolved.
    async setSettingsByLabel(values: Record<string, { value: any; option?: boolean; section?: string }>) {
        const body: Record<string, Record<string, any>> = {};
        for (const [label, spec] of Object.entries(values)) {
            const { section, name, field } = await this.settingsField(label, spec.section);
            body[section] = body[section] || {};
            body[section][name] = spec.option ? this.optionKey(field, String(spec.value)) : spec.value;
        }
        return this.saveSettings(body);
    }



    /**************************************************/
    /*************** @WPUF Setup *********************/
    /************************************************/

    //WPUF Setup
    async wpufSetup() {
        //WPUF Setup
        await this.navigateToURL(this.wpufSetupPage);

        const wpufSetup = await this.page.isVisible(Selectors.settingsSetup.wpufSetup.validateWPUFSetupPage);
        if (wpufSetup == true) {
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupLetsGo);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupContinue);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupEnd);
            return;
        }

        // The old setup wizard is retired: its URL opens the onboarding wizard,
        // which installs the pages and the sample forms the suite relies on.
        await this.completeOnboarding();
    }

    // Walk the onboarding wizard (React app route #/onboarding/:step): every
    // feature, no post form template (the page installer then builds the
    // "Sample Form" the suite uses), pages installed, no extra plugins.
    async completeOnboarding() {
        const onboarding = new OnboardingPage(this.page);
        const stepOf = () => (this.page.url().match(/#\/onboarding\/([a-z_]+)/) || [])[1] || '';

        await onboarding.gotoWizard();

        for (let i = 0; i < 10; i++) {
            const step = stepOf();
            if (!step || 'ready' === step) {
                break;
            }
            if ('plugins' === step) {
                // Other weDevs plugins are not part of this suite.
                await onboarding.skipStep();
            } else {
                if ('post_form' === step) {
                    await this.page.locator('#wpuf-onboarding-template').click();
                    await this.page.getByRole('option', { name: 'Do not create a form' }).click();
                }
                if ('common' === step) {
                    await onboarding.setCommonOptions({ installPages: true });
                }
                await onboarding.continueStep();
            }
            await expect.poll(stepOf, { timeout: 30000 }).not.toBe(step);
        }
    }





    /**************************************************/
    /*************** @Plugin Activate ****************/
    /************************************************/

    //Plugin Activate - Lite
    async pluginStatusCheckLite() {
        //Go to AdminEnd
        await this.navigateToURL(this.pluginsPage);

        //Activate Lite
        await this.activateWPUFLite();
    }


    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        //Go to AdminEnd
        await this.navigateToURL(this.pluginsPage);

        //Activate Pro
        await this.activateWPUFPro();
    }

    // Plugin Activate - License
    //Plugin Activate - Pro
    async licenseActivateWPUFPro() {
        //Go to AdminEnd
        await this.navigateToURL(this.pluginsPage);

        //Activate Pro
        await this.activateLicenseWPUFPro();
    }

    //Plugin Activate - Dokan Lite
    async dokanLiteStatusCheck() {
        //Go to AdminEnd
        await this.navigateToURL(this.pluginsPage);

        //Activate Dokan Lite
        await this.activateDokanLite();
    }

    /************************************************************/
    /*************** @Plugin Activate Functions ****************/
    /**********************************************************/

    //Plugin Page - Visit
    async pluginVisitWPUF() {
        //Go to AdminEnd
        await this.navigateToURL(this.wpAdminPage);

        await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);


        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            await this.checkElementText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton, 'Add New');
        }
    }

    async postFormListVisit() {
        //Go to AdminEnd
        await this.navigateToURL(this.wpAdminPage);

        await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);

        await this.validateAndClick(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);


        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            await this.checkElementText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton, 'Add New');
            expect(this.page.locator(Selectors.settingsSetup.pluginVisit.noFormMsg)).not.toBeVisible();
            await this.assertionValidate(Selectors.settingsSetup.pluginVisit.formTitleCheck('Sample Form'));
        }
    }

    async regFormListVisit() {
        //Go to AdminEnd
        await this.navigateToURL(this.wpAdminPage);

        await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        await this.validateAndClick(Selectors.settingsSetup.pluginVisit.clickRegFormListPage);

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickRegFormMenuOption);
        if (availableText == true) {
            await this.checkElementText(Selectors.settingsSetup.pluginVisit.wpufRegFormCheckAddButton, 'Add New');
            expect(this.page.locator(Selectors.settingsSetup.pluginVisit.noFormMsg)).not.toBeVisible();
            await this.assertionValidate(Selectors.settingsSetup.pluginVisit.formTitleCheck('Registration'));
        }
    }

    async validateWPUFpages() {
        // Validate each WPUF page independently of pagination. The pages list is sorted
        // alphabetically and other plugins (WooCommerce, MailPoet, Dokan, EDD) add their own
        // pages, so which pagination page a WPUF row lands on is not stable — relying on a
        // fixed "page 1 then Next" split makes this flaky (a shifted row times out). Instead
        // search the list for each page (WP admin `s=` param) so its row is always on screen.
        const wpufPages: Array<[string, string]> = [
            [ 'Account', Selectors.settingsSetup.wpufPages.wpufAccountPage ],
            [ 'Dashboard', Selectors.settingsSetup.wpufPages.wpufDashboardPage ],
            [ 'Edit', Selectors.settingsSetup.wpufPages.wpufEditPage ],
            [ 'Login', Selectors.settingsSetup.wpufPages.wpufLoginPage ],
            [ 'Order Received', Selectors.settingsSetup.wpufPages.orderReceivedPage ],
            [ 'Payment', Selectors.settingsSetup.wpufPages.paymentPage ],
            [ 'Subscription', Selectors.settingsSetup.wpufPages.wpufSubscriptionPage ],
            [ 'Thank You', Selectors.settingsSetup.wpufPages.thankYouPage ],
        ];

        for (const [ pageName, selector ] of wpufPages) {
            await this.navigateToURL(`${this.pagesPage}&s=${encodeURIComponent(pageName)}`);
            await this.page.waitForLoadState('domcontentloaded');
            await this.assertionValidate(selector);
        }
        console.log('WPUF Pages are validated. all pages created successfully');
    }

    async validateWPUFpagesFE() {
        //Go to FrontEnd
        await this.navigateToURL(Urls.baseUrl);

        //Validate WPUF Pages
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.accountPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.dashboardPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.editPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.loginPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.subscriptionPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.orderReceivedPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.thankYouPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.paymentPageFE);
    }

    async validateAccountPageTabs() {

        await this.navigateToURL(Urls.baseUrl);

        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.accountPageFE);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.dashboardTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.viewDashboardPara);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.postsTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.postsTableHeader);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.updateProfileButton);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.subscriptionTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.noSubscriptionPara);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.billingAddessTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.updateBillingAddressButton);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.submitPostTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.submitPostButton);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.invoiceTab);
        await this.assertionValidate(Selectors.settingsSetup.accountPageTabs.invoiceTableHeader);
    }

    //Plugin Activate - Lite
    async activateWPUFLite() {
        
        const ifWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginLite);
        console.log(ifWPUFLite);
        const dialogHandler = async (dialog: Dialog) => {
            if (dialog.type() === 'confirm') {
                await dialog.accept();
            }
        };
        this.page.on('dialog', dialogHandler);
        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickRunUpdater);
        this.page.off('dialog', dialogHandler);

        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickAllow1);

        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickAllow);

        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickSkipSetup);

        // try {
        //     await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickSwitchCart);
        // } catch (error) {
        //     console.log('Failed to click Switch Cart:', error);
        // }

        // try {
        //     await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickDismiss);
        // } catch (error) {
        //     console.log('Failed to click Dismiss:', error);
        // }

        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickEDDnoticeCross);

        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickPayPalCross);

        if (ifWPUFLite == true) {
            //Activate Plugin
            const activateWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
            console.log(activateWPUFLite);
            if (activateWPUFLite === true) {
                //Plugins is getting activated here
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);

                await this.navigateToURL(this.pluginsPage);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginDeactivate);

                await this.navigateToURL(this.wpAdminPage);
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log('WPUF-Lite Status: is Activated');
            }

            else {
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log('WPUF-Lite Status: was Active');
            }
        } else {
            console.log('WPUF-Lite not available');
        }

    }

    //Plugin Activate - Pro
    async activateWPUFPro() {
        // Take screenshot and attach to test reportAdd commentMore actions
        // const screenshot = await this.page.screenshot({ 
        //     fullPage: true,
        //     path: 'plugins-page-wpuf-lite-check.png'
        // });
        // await test.info().attach('Plugins Page - WPUF Lite Check', {
        //     body: screenshot,
        //     contentType: 'image/png'
        // });
        const ifWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginPro);
        console.log(ifWPUFPro);
        if (ifWPUFPro == true) {
            //Activate Plugin
            const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
            console.log(activateWPUFPro);
            if (activateWPUFPro == true) {
                //Plugins were DeActive
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);

                await this.navigateToURL(this.pluginsPage);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginProDeactivate);

                await this.navigateToURL(this.wpAdminPage);
                const dialogHandler = async (dialog: Dialog) => {
                    if (dialog.type() === 'confirm') {
                        await dialog.accept();
                    }
                };
                this.page.on('dialog', dialogHandler);
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickRunUpdater);
                this.page.off('dialog', dialogHandler);
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                await this.assertionValidate(Selectors.login.basicNavigation.licenseTab);
                console.log('WPUF-Pro Status: is Activated');
            }
            else {
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                await this.assertionValidate(Selectors.login.basicNavigation.licenseTab);
                console.log('WPUF-Pro Status: was Active');
            }
        } else {
            console.log('WPUF-Pro not available');
        }

    }

    //Plugin Activate - Pro
    async activateLicenseWPUFPro() {
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        }
        await this.validateAndClick(Selectors.login.basicNavigation.licenseTab);

        // Already activated? The React License page shows the "Active" status pill
        // and no key input, so filling would block until the test timeout:
        // wait for the page to render, then short-circuit.
        await this.page.locator(Selectors.settingsSetup.pluginStatusCheck.licenseStatus).first().waitFor({ timeout: 30000 });
        const alreadyActivated = await this.page
            .locator(Selectors.settingsSetup.pluginStatusCheck.activationRemaining)
            .first()
            .isVisible()
            .catch(() => false);
        if (alreadyActivated) {
            console.log('WPUF-Pro Status: License was already Activated');
            return;
        }

        //Activate Plugin
        await this.validateAndFillStrings(Selectors.settingsSetup.pluginStatusCheck.fillLicenseKey, process.env.WPUF_PRO_LICENSE_KEY?.toString() || '');
        await this.page.waitForTimeout(200);
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.submitLicenseKey);
        await this.page.waitForTimeout(200);
        await this.page.reload();
        await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.activationRemaining);
        console.log('WPUF-Pro Status: License is Activated');
    }

    async activateDokanLite() {
        //Go to Plugins page

        const ifDokanLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableDokanLite);
        console.log(ifDokanLite);

        if (ifDokanLite == true) {
            //Activate Plugin
            const activateDokanLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickDokanLite);
            console.log(activateDokanLite);
            if (activateDokanLite === true) {
                //Plugins is getting activated here
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickDokanLite);

                await this.navigateToURL(this.pluginsPage);
                // Activation is confirmed by the Deactivate control appearing.
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickDokanLiteDeactivate);

                // Some Dokan versions surface an Appsero opt-in ("Allow") after activation and
                // some do not. Dismiss it when present, but never block on it — it is optional
                // UI, and activation is already verified above. Blocking here hung LS0030 for
                // the full test timeout on builds where the notice never appears.
                const dokanAllow = this.page.locator(Selectors.settingsSetup.pluginStatusCheck.clickAllow).first();
                try {
                    await dokanAllow.waitFor({ timeout: 5000 });
                    await dokanAllow.click();
                } catch (e) {
                    console.log('Dokan Appsero opt-in not shown; continuing');
                }

                await this.navigateToURL(this.wpAdminPage);
                await this.validateAndClick(Selectors.login.basicNavigation.clickDokanSidebar);
                console.log('Dokan-Lite Status: is Activated');
            }
            else {
                await this.validateAndClick(Selectors.login.basicNavigation.clickDokanSidebar);
                console.log('Dokan-Lite Status: was Active');
            }
        } else {
            console.log('Dokan-Lite not available');
        }

    }





    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetLoginPageDefault() {
        // Login page back to "-- Select --", custom fields shown on the post, the
        // account page on "Account" with the Dashboard tab active.
        await this.navigateToURL(this.wpufSettingsPage);
        const login = await this.settingsField('Login Page', 'wpuf_profile');
        // "-- Select --" is the '' key; JS lists integer keys (page ids) before it.
        const loginOptions = login.field.options || {};
        const firstLogin = '' in loginOptions ? '' : (Object.keys(loginOptions)[0] ?? '');
        const custom = await this.settingsField('Show custom fields on post content area');
        await this.saveSettings({
            wpuf_profile: { [login.name]: firstLogin },
            [custom.section]: { [custom.name]: 'on' },
        });
        await this.setSettingsByLabel({
            'Account Page': { value: 'Account', option: true, section: 'wpuf_my_account' },
            'Active Tab': { value: 'Dashboard', option: true, section: 'wpuf_my_account' },
        });
    }


    async changeSettingsSetEditProfilePageDefault(label: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        const field = await this.settingsField('Profile Form', 'wpuf_my_account').catch(() => this.settingsField('Edit Profile Form', 'wpuf_my_account'));
        await this.saveSettings({ wpuf_my_account: { [field.name]: this.optionKey(field.field, label) } });
    }


    async changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.setSettingsByLabel({ 'Default Post Form': { value: postFormPresetFrontEndTitle, option: true, section: 'wpuf_frontend_posting' } });
    }


    async changeSettingsSetRegistrationPage(registrationFormPageTitle: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.setSettingsByLabel({ 'Registration Page': { value: registrationFormPageTitle, option: true, section: 'wpuf_profile' } });
    }


    async setPermalink() {
        //Go to Settings - Permalink page
        await this.navigateToURL(this.settingsPermalinkPage);

        await this.page.reload();
        //Custom structure - fill with empty
        await this.validateAndFillStrings(Selectors.settingsSetup.setPermalink.fillCustomStructure, '');
        //Set Post Name Permalink
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.clickCustomStructurePostName);
        //Save Permalink Settings
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);
        await this.page.reload();
        //Save Permalink again
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);


    }


    async allowRegistration() {
        //Go to Settings - General page
        await this.navigateToURL(this.settingsPage);

        await this.page.reload();
        //Allow anyone to register
        await this.validateAndClick(Selectors.settingsSetup.allowRegistration.clickAnyoneRegister);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.allowRegistration.saveSettings);
    }


    /********************************************/
    /********** @Create New User ***************/
    /******************************************/

    //Main Admin
    //New User Create
    async createNewUser(userName: string, email: string, firstName: string, lastName: string, password: string) {
        await this.navigateToURL(this.wpAdminPage);
        //Go to Admin-Users
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.clickUserMenuAdmin);
        //Add New User
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.clickAddNewUserAdmin);
        //await this.page.reload();


        //New User creation flow
        //Enter Username
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserName, userName);
        await this.page.waitForTimeout(1000);
        //Enter Email
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserEmail, email);
        await this.page.waitForTimeout(200);
        //Enter First Name
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserFirstName, firstName);
        await this.page.waitForTimeout(200);
        //Enter Last Name
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserLastName, lastName);
        await this.page.waitForTimeout(200);
        //Enter Password
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserPassword, password);
        await this.page.waitForTimeout(200);
        //Select Role
        await this.assertionValidate(Selectors.settingsSetup.createNewUser.newUserSelectRole);
        await this.selectOptionWithLabel(Selectors.settingsSetup.createNewUser.newUserSelectRole, 'Subscriber');
        await this.page.waitForTimeout(200);
        //Create User
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.newUserSubmit);
        await this.page.waitForTimeout(200);
        //Validate User Creation
        await this.assertionValidate(Selectors.settingsSetup.createNewUser.validateCreationMsg);
        await this.assertionValidate(Selectors.settingsSetup.createNewUser.validateUserInList);

    }

    async createPostCategories() {
        const categoryNames: string[] = ['Science', 'Music'];
        for (let i = 0; i < categoryNames.length; i++) {
            // Reload the categories screen for every term. WordPress's "Add New Category"
            // form falls back to a full-page POST when its AJAX handler isn't bound, so filling
            // the next term into a form left over from the previous submit races that reload and
            // posts an empty name. A fresh page guarantees a stable form for each term.
            await this.navigateToURL(this.categoriesPage);
            await this.page.waitForLoadState('domcontentloaded');

            const categoryRow = this.page
                .locator(Selectors.settingsSetup.categories.validateCategory(categoryNames[i]))
                .first();

            // Idempotent: skip terms that already exist (e.g. a re-run against a used site).
            if ( await categoryRow.count() > 0 ) {
                continue;
            }

            await this.validateAndFillStrings(Selectors.settingsSetup.categories.addNewCategory, categoryNames[i]);
            await this.validateAndClick(Selectors.settingsSetup.categories.submitCategory);
            await this.page.waitForTimeout(500);

            // The add completes either via AJAX (row injected in place) or a full-page POST (the
            // submit navigates and the row is present after the redirect). Settle the page, then
            // wait on the row; .first() tolerates any duplicate left on a re-used site.
            await this.page.waitForLoadState('domcontentloaded').catch(() => {});
            await this.page
                .locator(Selectors.settingsSetup.categories.validateCategory(categoryNames[i]))
                .first()
                .waitFor({ timeout: 20000 });
        }
    }
    async createPostTags() {
        //Go to Admin-Users
        await this.navigateToURL(this.tagsPage);
        //Add New Category
        //await this.validateAndClick(Selectors.settingsSetup.tags.clickTagsMenu);
        await this.page.waitForLoadState('domcontentloaded');
        const tagNames: string[] = ['Physics', 'AI'];
        for (let i = 0; i < tagNames.length; i++) {
            await this.validateAndFillStrings(Selectors.settingsSetup.tags.addNewTag, tagNames[i]);
            await this.validateAndClick(Selectors.settingsSetup.tags.submitTag);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.settingsSetup.tags.validateTag(tagNames[i]));
        }
    }

    async addGoogleMapAPIKey(googleMapAPIKey: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.saveSettings({ wpuf_general: { gmap_api_key: googleMapAPIKey } });
    }


    async addReCaptchaKeys(recaptchaSiteKey: string, recaptchaSecretKey: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.saveSettings({ wpuf_general: { recaptcha_public: recaptchaSiteKey, recaptcha_private: recaptchaSecretKey } });
    }


    async addCloudflareTurnstileKeys(turnstileSiteKey: string, turnstileSecretKey: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.saveSettings({ wpuf_general: { enable_turnstile: 'on', turnstile_site_key: turnstileSiteKey, turnstile_secret_key: turnstileSecretKey } });
    }


    // Adds a gateway to the active ones (the onboarding wizard may have turned some on already).
    async turnOnGateway(gateway: string, extra: Record<string, any> = {}) {
        await this.navigateToURL(this.wpufSettingsPage);
        const active = (await this.settingValue('wpuf_payment', 'active_gateways')) || {};
        const gateways = Array.isArray(active) ? Object.fromEntries(active.map((g: string) => [g, g])) : { ...active };
        gateways[gateway] = gateway;
        await this.saveSettings({ wpuf_payment: { enable_payment: 'on', active_gateways: gateways, ...extra } });
    }


    async enablePaymentGatewayBank() {
        await this.turnOnGateway('bank');
    }


    async enablePaymentGatewayStripe() {
        const keys: Record<string, any> = {};
        for (const [label, env] of [['Stripe Publishable Key', 'STRIPE_PUBLISHABLE_KEY'], ['Stripe Secret Key', 'STRIPE_SECRET_KEY'], ['Stripe Signing Secret', 'STRIPE_SIGNING_KEY']]) {
            const f = await this.settingsField(label, 'wpuf_payment').catch(() => null);
            if (f) {
                keys[f.name] = process.env[env] || '';
            }
        }
        await this.turnOnGateway('stripe', keys);
    }


    async enablePaymentGatewayPaypal() {
        const keys: Record<string, any> = {};
        for (const [label, env] of [['PayPal Email', 'PAYPAL_EMAIL'], ['PayPal Client ID', 'PAYPAL_CLIENT_ID'], ['PayPal Client Secret', 'PAYPAL_CLIENT_SECRET'], ['PayPal Webhook ID', 'PAYPAL_WEBHOOK_ID'], ['PayPal API username', 'PAYPAL_API_USERNAME'], ['PayPal API password', 'PAYPAL_API_PASSWORD'], ['PayPal API signature', 'PAYPAL_API_SIGNATURE']]) {
            const f = await this.settingsField(label, 'wpuf_payment').catch(() => null);
            if (f) {
                keys[f.name] = process.env[env] || '';
            }
        }
        keys.sandbox_mode = 'on';
        await this.turnOnGateway('paypal', keys);
    }


    async enableModules(moduleName: string[]) {
        //Go to Settings - General page
        await this.navigateToURL(this.wpufModulesPage);
        await this.validateAndClick(Selectors.settingsSetup.modules.clickActivateAll);
        await this.page.waitForTimeout(2000);
        for (let i = 0; i < moduleName.length; i++) {
            if (moduleName[i] != '') {
                await expect(this.page.locator(Selectors.settingsSetup.modules.checkModule(moduleName[i]))).toBeChecked();
            }
        }
    }

    async disableModules(moduleName: string[]) {
        //Go to Settings - General page
        await this.navigateToURL(this.wpufModulesPage);
        await this.validateAndClick(Selectors.settingsSetup.modules.clickDeactivateAll);
        // The React page asks first (shared ConfirmDialog).
        await this.validateAndClick(Selectors.settingsSetup.modules.confirmDeactivateAll);
        await this.page.waitForTimeout(2000);
        for (let i = 0; i < moduleName.length; i++) {
            if (moduleName[i] != '') {
                await expect(this.page.locator(Selectors.settingsSetup.modules.checkModule(moduleName[i]))).not.toBeChecked();
            }
        }
    }

    async enableStripeModule(moduleName: string) {
        //Go to Settings - General page
        await this.navigateToURL(this.wpufModulesPage);
        await this.validateAndClick(Selectors.settingsSetup.modules.checkModule(moduleName));
        await this.page.waitForTimeout(2000);
        await expect(this.page.locator(Selectors.settingsSetup.modules.checkModule(moduleName))).toBeChecked();
    }

    async enableGoogleAI() {
        await this.navigateToURL(this.wpufSettingsPage);
        const key = await this.settingsField('API Key', 'wpuf_ai').catch(() => ({ name: 'ai_api_key' }) as any);
        await this.saveSettings({ wpuf_ai: { ai_provider: 'google', [key.name]: process.env.GEMINI_AI_API_KEY || '' } });
    }


    async enableOpenAI() {
        await this.navigateToURL(this.wpufSettingsPage);
        const key = await this.settingsField('API Key', 'wpuf_ai').catch(() => ({ name: 'ai_api_key' }) as any);
        await this.saveSettings({ wpuf_ai: { ai_provider: 'openai', [key.name]: process.env.OPEN_AI_API_KEY || '' } });
    }


    async enableAnthropicAI() {
        await this.navigateToURL(this.wpufSettingsPage);
        const key = await this.settingsField('API Key', 'wpuf_ai').catch(() => ({ name: 'ai_api_key' }) as any);
        await this.saveSettings({ wpuf_ai: { ai_provider: 'anthropic', [key.name]: process.env.ANTHROPIC_API_KEY || '' } });
    }


    async validatePermalinkPersistence() {
        await this.navigateToURL(this.settingsPermalinkPage);
        await this.page.reload();
        await expect(this.page.locator(Selectors.settingsSetup.setPermalink.fillCustomStructure))
            .toHaveValue('/%postname%/');
        console.log('\x1b[32m%s\x1b[0m', '✅ Permalink structure persisted: /%postname%/');
    }

    // Reload WP General settings and assert "anyone can register" stuck.
    async validateAllowRegistrationPersistence() {
        await this.navigateToURL(this.settingsPage);
        await this.page.reload();
        await expect(this.page.locator(Selectors.settingsSetup.allowRegistration.clickAnyoneRegister))
            .toBeChecked();
        console.log('\x1b[32m%s\x1b[0m', '✅ Anyone-can-register persisted');
    }

    // Reload WPUF > Settings > Payments and assert payments + the bank/stripe/paypal
    // gateways enabled during setup persisted (incl. PayPal sandbox/test mode).
    async validatePaymentGatewayPersistence() {
        await this.navigateToURL(this.wpufSettingsPage);
        const on = (v: any) => ['on', 'yes', 'true', '1', true].includes(v);
        expect(on(await this.settingValue('wpuf_payment', 'enable_payment'))).toBeTruthy();
        const active = (await this.settingValue('wpuf_payment', 'active_gateways')) || {};
        const list = Array.isArray(active) ? active : Object.keys(active);
        for (const gateway of ['bank', 'stripe', 'paypal']) {
            expect(list).toContain(gateway);
        }
        expect(on(await this.settingValue('wpuf_payment', 'sandbox_mode'))).toBeTruthy();
        console.log('\x1b[32m%s\x1b[0m', '✅ Payment gateways (bank/stripe/paypal) + sandbox persisted');
    }


    async validateAIProviderPersistence(provider: string) {
        await this.navigateToURL(this.wpufSettingsPage);
        expect(await this.settingValue('wpuf_ai', 'ai_provider')).toBe(provider);
        console.log('\x1b[32m%s\x1b[0m', `✅ AI provider persisted: ${provider}`);
    }


    async validateGeneralSettingsPersistenceRoundTrip() {
        const sentinel = 'wpuf-qa-gmap-persist-check';
        await this.navigateToURL(this.wpufSettingsPage);
        const on = (v: any) => ['on', 'yes', 'true', '1', true].includes(v);

        // Turnstile enable toggle from setup persisted.
        expect(on(await this.settingValue('wpuf_general', 'enable_turnstile'))).toBeTruthy();

        // Round-trip a text setting: capture original -> write sentinel -> save -> read back.
        const original = (await this.settingValue('wpuf_general', 'gmap_api_key')) || '';
        await this.saveSettings({ wpuf_general: { gmap_api_key: sentinel } });
        expect(await this.settingValue('wpuf_general', 'gmap_api_key')).toBe(sentinel);
        console.log('\x1b[32m%s\x1b[0m', '✅ WPUF general setting round-trip persisted (Google Map API key)');

        // Restore the original value so downstream tests see the pre-existing state.
        await this.saveSettings({ wpuf_general: { gmap_api_key: original } });
    }

    /***********************************************/
    /********** @Rest WorPress Site ***************/
    /*********************************************/

    async resetWordpressSite() {
        //Go to AdminEnd
        await this.navigateToURL(this.wpResetPage);
        await this.page.reload();
        await this.validateAndClick(Selectors.resetWordpreseSite.reActivateTheme);
        await this.validateAndClick(Selectors.resetWordpreseSite.reActivatePlugins);
        await this.validateAndFillStrings(Selectors.resetWordpreseSite.wpResetInputBox, 'reset');
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetSubmitButton);
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetConfirmWordpressReset);
        // WP Reset wipes the DB then reloads; poll until the site answers again
        // instead of a fixed 20s sleep (same worst-case ceiling, faster when done).
        await this.page.waitForTimeout(3000);
        await waitForSiteReady(this.page, 60000);

        // The reset wipes the users/usermeta tables, so the current cookie (and any
        // cached .auth state) is dead — every admin page below would render wp-login.
        // Re-authenticate before touching plugins.php.
        clearSavedSession(Users.adminUsername);
        const { BasicLoginPage } = await import('./basicLogin'); // dynamic: basicLogin imports this module
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        await this.navigateToURL(this.pluginsPage);
        await this.page.reload();
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWCvendors);
        // WP Reset only restores the plugins that were active when it ran, so a site
        // whose EDD had drifted inactive comes back without the `download_*` taxonomies
        // and PF0018 dies on "Invalid taxonomy". Optional click: no-op when already active.
        await this.navigateToURL(this.pluginsPage);
        await this.clickIfAvailable(Selectors.settingsSetup.pluginStatusCheck.clickEDD);


        await this.navigateToURL(this.pluginsPage);
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickDoNotAllow);

    }

}
