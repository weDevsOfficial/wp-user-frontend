import dotenv from 'dotenv';
dotenv.config();
import { expect, type Page, type Dialog } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';
export class SettingsSetupPage extends Base {

    constructor(page: Page) {
        super(page);

    }



    /**************************************************/
    /*************** @WPUF Setup *********************/
    /************************************************/

    //WPUF Setup
    async wpufSetup() {
        //WPUF Setup
        const wpufSetupPage = Urls.baseUrl + '/wp-admin/index.php?page=wpuf-setup';
        await Promise.all([
            this.page.goto(wpufSetupPage, { waitUntil: 'domcontentloaded' }),
        ]);

        const wpufSetup = await this.page.isVisible(Selectors.settingsSetup.wpufSetup.validateWPUFSetupPage);
        if (wpufSetup == true) {
            //await this.validateAndClick(SelectorsPage.settingsSetup.clickWPUFSetupSkip);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupLetsGo);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupContinue);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupEnd);
        }

    }





    /**************************************************/
    /*************** @Plugin Activate ****************/
    /************************************************/

    //Plugin Activate - Lite
    async pluginStatusCheckLite() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' }),
        ]);

        //Activate Lite
        await this.activateWPUFLite();
    }


    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' }),
        ]);

        //Activate Pro
        await this.activateWPUFPro();
    }

    // Plugin Activate - License
    //Plugin Activate - Pro
    async licenseActivateWPUFPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'domcontentloaded' }),
        ]);

        //Activate Pro
        await this.activateLicenseWPUFPro();
    }



    /************************************************************/
    /*************** @Plugin Activate Functions ****************/
    /**********************************************************/

    //Plugin Page - Visit
    async pluginVisitWPUF() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'domcontentloaded' }),
        ]);

        await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            const checkText = await this.page.innerText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton);
            await expect(checkText).toContain('Add New');
        }

    }

    async validateWPUFpages() {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit.php?post_type=page', { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');
        //Validate WPUF Pages
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.accountPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufAccountPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.dashboardPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufDashboardPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.editPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufEditPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.loginPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufLoginPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.subscriptionPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufSubscriptionPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.orderReceivedPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.paymentPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.thankYouPage);
        console.log('WPUF Pages are validated. all pages created successfully');
    }

    async validateWPUFpagesFE() {
        //Go to FrontEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl, { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');
        //Validate WPUF Pages
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.accountPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.dashboardPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.editPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.loginPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.subscriptionPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.orderReceivedPageFE);
        await this.validateAndClick(Selectors.settingsSetup.wpufPagesFE.thankYouPageFE);
    }

    async validateAccountPageTabs(){

        await Promise.all([
            this.page.goto(Urls.baseUrl , { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');
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
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        const ifWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginLite);
        await this.page.waitForTimeout(200);
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow1);
        await this.page.waitForTimeout(500);
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow2);
        await this.page.waitForTimeout(500);
        if (ifWPUFLite == true) {
            //Activate Plugin
            const activateWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
            console.log(activateWPUFLite);
            if (activateWPUFLite === true) {
                //Plugins is getting activated here
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
                await this.page.waitForTimeout(3000);
                await Promise.all([
                    this.page.goto(pluginsPage, { waitUntil: 'domcontentloaded' }),
                ]);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginDeactivate);

                await this.page.goto(Urls.baseUrl + '/wp-admin/');
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
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        const ifWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginPro);
        if (ifWPUFPro == true) {
            //Activate Plugin
            const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
            console.log(activateWPUFPro);
            if (activateWPUFPro == true) {
                //Plugins were DeActive
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
                await this.page.waitForTimeout(3000);
                await Promise.all([
                    this.page.goto(pluginsPage, { waitUntil: 'domcontentloaded' }),
                ]);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginProDeactivate);

                await this.page.goto(Urls.baseUrl + '/wp-admin/');
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
                await this.page.goto(Urls.baseUrl + '/wp-admin/');
                const dialogHandler = async (dialog: Dialog) => {
                    if (dialog.type() === 'confirm') {
                        await dialog.accept();
                    }
                };
                this.page.on('dialog', dialogHandler);
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickRunUpdater);
                this.page.off('dialog', dialogHandler);
                console.log('WPUF-Pro Status: was Active');
            }
        } else {
            console.log('WPUF-Pro not available');
        }

    }

    //Plugin Activate - Pro
    async activateLicenseWPUFPro() {
        const ifWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginPro);
        if (ifWPUFPro == true) {
            //Go to Plugins page
            const pluginsPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
            await Promise.all([
                this.page.goto(pluginsPage, { waitUntil: 'domcontentloaded' }),
            ]);

            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.validateAndClick(Selectors.login.basicNavigation.licenseTab);
            //Activate Plugin
            const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickActivateLicense);
            console.log(activateWPUFPro);
            if (activateWPUFPro == true) {
                await this.validateAndFillStrings(Selectors.settingsSetup.pluginStatusCheck.fillLicenseKey, process.env.WPUF_PRO_LICENSE_KEY?.toString() || '');
                await this.page.waitForTimeout(500);
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.submitLicenseKey);
                await this.page.waitForTimeout(500);
                await this.page.reload();
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.activationRemaining);
                console.log('WPUF-Pro Status: License is Activated');
            }
            else {
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                await this.validateAndClick(Selectors.login.basicNavigation.licenseTab);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.deactivateLicenseKey);
                console.log('WPUF-Pro Status: License was Active');
            }
        } else {
            console.log('WPUF-Pro not available');
        }
    }





    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetLoginPageDefault() {
        //Go to WPUF
        const wpufpostformpage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'domcontentloaded' }),
        ]);

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Login/Registration
        await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1);
        console.log(await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1));
        //Click Login/Registration
        await this.page.waitForSelector(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Login Page to default
        expect(await this.page.waitForSelector(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage)).toBeTruthy();
        //Again - Click Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage, { label: '— Select —' });
        //Save Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);

        await this.page.waitForTimeout(1000);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.showCustomFields);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);

        await this.page.waitForLoadState('domcontentloaded');
    }


    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle: string) {
        //Go to WPUF
        const wpufpostformpage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'domcontentloaded' }),
        ]);

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Click Frontend Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Set Default Post Form 
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.setDefaultPostForm, { label: postFormPresetFrontEndTitle });
        //Save FrontEnd Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);

        await this.page.waitForLoadState('domcontentloaded');
    }




    /*********************************************************/
    /******* @Change WPUF-Settings > Registration ***********/
    /*******************************************************/

    //Change Settings - Registration Page
    async changeSettingsSetRegistrationPage(registrationFormPageTitle: string) {
        //Go to WPUF
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'domcontentloaded' }),
        ]);

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Login/Registration
        await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1);
        //Click Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Registration Page Form
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileRegistrationPage, { label: registrationFormPageTitle });
        //Save Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);

        await this.page.waitForLoadState('domcontentloaded');

    }





    /********************************************/
    /************ @Set Permalink ***************/
    /******************************************/

    //Set Permalink
    async setPermalink() {
        //Go to Settings - Permalink page
        const settingsPermalinkPage = Urls.baseUrl + '/wp-admin/options-permalink.php';
        await Promise.all([
            this.page.goto(settingsPermalinkPage, { waitUntil: 'domcontentloaded' }),
        ]);

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
        const settingsGeneralPage = Urls.baseUrl + '/wp-admin/options-general.php';
        await Promise.all([
            this.page.goto(settingsGeneralPage, { waitUntil: 'domcontentloaded' }),
        ]);

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
    async createNewUserAdmin(userName:string, email:string, firstName:string, lastName:string, password:string) {
        const pluginsPage = Urls.baseUrl + '/wp-admin/';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'domcontentloaded' }),
        ]);

        //Go to Admin-Users
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.clickUserMenuAdmin);
        //Add New User
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.clickAddNewUserAdmin);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');

        //New User creation flow
        //Enter Username
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserName, userName);
        //Enter Email
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserEmail, email);
        //Enter First Name
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserFirstName, firstName);
        //Enter Last Name
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserLastName, lastName);
        //Enter Password
        await this.validateAndFillStrings(Selectors.settingsSetup.createNewUser.newUserPassword, password);
        //Allow weak Password
        await this.page.check(Selectors.settingsSetup.createNewUser.newUserWeakPasswordAllow);
        //Select Role
        await this.page.waitForLoadState('domcontentloaded');
        await this.assertionValidate(Selectors.settingsSetup.createNewUser.newUserSelectRole);
        await this.page.selectOption(Selectors.settingsSetup.createNewUser.newUserSelectRole, { label: 'Subscriber' });

        //Create User
        await this.validateAndClick(Selectors.settingsSetup.createNewUser.newUserSubmit);
    }

    async createPostCategories() {
        //Go to Admin-Users
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=category', { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');
        //Add New Category
        await this.validateAndClick(Selectors.settingsSetup.categories.clickCategoryMenu);
        await this.page.waitForLoadState('domcontentloaded');
        const categoryNames: string[] = ['Science', 'Music', 'Politics', 'Sports', 'Technology'];
        for (let i = 0; i < categoryNames.length; i++) {
            await this.validateAndFillStrings(Selectors.settingsSetup.categories.addNewCategory, categoryNames[i]);
            await this.validateAndClick(Selectors.settingsSetup.categories.submitCategory);
            await this.page.waitForTimeout(1000);
            await this.page.waitForLoadState('domcontentloaded');
            const validatedCategory = await this.page.innerText(Selectors.settingsSetup.categories.validateCategory);
            await expect(validatedCategory).toContain(categoryNames[i]);
        }
    }
    async createPostTags() {
        //Go to Admin-Users
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=post_tag', { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');
        //Add New Category
        await this.validateAndClick(Selectors.settingsSetup.tags.clickTagsMenu);
        await this.page.waitForLoadState('domcontentloaded');
        const tagNames: string[] = ['Physics', 'Rock', 'Democracy', 'Football', 'AI'];
        for (let i = 0; i < tagNames.length; i++) {
            await this.validateAndFillStrings(Selectors.settingsSetup.tags.addNewTag, tagNames[i]);
            await this.validateAndClick(Selectors.settingsSetup.tags.submitTag);
            await this.page.waitForTimeout(1000);
            await this.page.waitForLoadState('domcontentloaded');
            const validatedtag = await this.page.innerText(Selectors.settingsSetup.tags.validateTag);
            await expect(validatedtag).toContain(tagNames[i]);
        }
    }

    async addGoogleMapAPIKey(googleMapAPIKey: string) {
        //Go to Settings - General page
        const settingsGeneralPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-settings';
        await Promise.all([
            this.page.goto(settingsGeneralPage, { waitUntil: 'domcontentloaded' }),
        ]);

        await this.page.reload();

        await this.validateAndClick(Selectors.settingsSetup.keys.clickSettingsTabGeneral);
        //Google Map API Key
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillGoogleMapAPIKey, googleMapAPIKey);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.keys.settingsTabGeneralSave);
    }

    async addReCaptchaKeys(recaptchaSiteKey: string, recaptchaSecretKey: string) {
        //Go to Settings - General page
        const settingsGeneralPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-settings';
        await Promise.all([
            this.page.goto(settingsGeneralPage, { waitUntil: 'domcontentloaded' }),
        ]);

        await this.page.reload();

        await this.validateAndClick(Selectors.settingsSetup.keys.clickSettingsTabGeneral);
        //ReCaptcha site & secret Key
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillReCaptchaSiteKey, recaptchaSiteKey);
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillReCaptchaSecretKey, recaptchaSecretKey);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.keys.settingsTabGeneralSave);
    }

    async addCloudflareTurnstileKeys(turnstileSiteKey: string, turnstileSecretKey: string) {
        //Go to Settings - General page
        const settingsGeneralPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-settings';
        await Promise.all([
            this.page.goto(settingsGeneralPage, { waitUntil: 'domcontentloaded' }),
        ]);

        await this.page.reload();

        await this.validateAndClick(Selectors.settingsSetup.keys.clickSettingsTabGeneral);
        // Cloudflare Turnstile enable
        await this.validateAndClick(Selectors.settingsSetup.keys.enableCloudflareTurnstile);
        // Cloudflare Turnstile site & secret Key
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillCloudflareTurnstileSiteKey, turnstileSiteKey);
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillCloudflareTurnstileSecretKey, turnstileSecretKey);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.keys.settingsTabGeneralSave);
        // Go to Settings - Login/Registration page
        await this.validateAndClick(Selectors.settingsSetup.keys.clickLoginOrRegistration);
        // Cloudflare Turnstile enable
        await this.validateAndClick(Selectors.settingsSetup.keys.enableCloudflareTurnstileLogin);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);
    }



    /***********************************************/
    /********** @Rest WorPress Site ***************/
    /*********************************************/

    async resetWordpressSite() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/tools.php?page=wp-reset', { waitUntil: 'domcontentloaded' }),
        ]);
        await this.page.reload();
        await this.validateAndClick(Selectors.resetWordpreseSite.reActivateTheme);
        await this.validateAndClick(Selectors.resetWordpreseSite.reActivatePlugins);
        await this.validateAndFillStrings(Selectors.resetWordpreseSite.wpResetInputBox, 'reset');
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetSubmitButton);
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetConfirmWordpressReset);
        await this.page.waitForTimeout(2000);
    }

}
