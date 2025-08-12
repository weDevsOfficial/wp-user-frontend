import dotenv from 'dotenv';
dotenv.config();
import { expect, test, type Page, type Dialog } from '@playwright/test';
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
        await this.navigateToURL(this.wpufSetupPage);

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
            await this.checkElementText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton, 'Add New');
            expect(this.page.locator(Selectors.settingsSetup.pluginVisit.noFormMsg)).not.toBeVisible();
            await this.assertionValidate(Selectors.settingsSetup.pluginVisit.formTitleCheck('Registration'));
        }
    }

    async validateWPUFpages() {
        await this.navigateToURL(this.pagesPage);

        //Validate WPUF Pages
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufAccountPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufDashboardPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufEditPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufLoginPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.orderReceivedPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.paymentPage);
        await this.validateAndClick(Selectors.settingsSetup.wpufPages.clickNextPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.wpufSubscriptionPage);
        await this.assertionValidate(Selectors.settingsSetup.wpufPages.thankYouPage);
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
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickRunUpdater);
        this.page.off('dialog', dialogHandler);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow1);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickSkipSetup);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickSwitchCart);

        // await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickDismiss);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickEDDnoticeCross);

        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickPayPalCross);

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
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickDokanLiteDeactivate);

                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow);


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
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        }
        await this.page.reload();
        //Validate Login/Registration
        await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1);
        //Click Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Login Page to default
        expect(await this.page.waitForSelector(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage)).toBeTruthy();
        //Again - Click Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        await this.page.locator(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage).selectOption({ index: 0 });
        //Save Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);


        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.showCustomFields);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);


        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccount);
        await this.page.waitForTimeout(200);
        await this.selectOptionWithLabel(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccountPage, 'Account');

        await this.selectOptionWithLabel(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccountActiveTab, 'Dashboard');
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccountSave);


    }

    async changeSettingsSetEditProfilePageDefault(label: string) {
        await this.navigateToURL(this.wpufRegFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);


        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccount);
        await this.selectOptionWithLabel(Selectors.settingsSetup.wpufSettingsPage.settingsTabEditProfile, `${label}`);
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabAccountSave);
    }


    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle: string) {
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        }

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Click Frontend Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Set Default Post Form 
        await this.selectOptionWithLabel(Selectors.settingsSetup.wpufSettingsPage.setDefaultPostForm, postFormPresetFrontEndTitle);
        //Save FrontEnd Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);

        await this.page.waitForLoadState('domcontentloaded');
    }




    /*********************************************************/
    /******* @Change WPUF-Settings > Registration ***********/
    /*******************************************************/

    //Change Settings - Registration Page
    async changeSettingsSetRegistrationPage(registrationFormPageTitle: string) {
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            //Change Settings
            await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        }

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Login/Registration
        await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1);
        //Click Login/Registration
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Registration Page Form
        await this.selectOptionWithLabel(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileRegistrationPage, registrationFormPageTitle);
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
        await this.page.waitForTimeout(200);
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
    }

    async createPostCategories() {
        //Go to Admin-Users
        await this.navigateToURL(this.categoriesPage);
        //Add New Category
        //await this.validateAndClick(Selectors.settingsSetup.categories.clickCategoryMenu);
        await this.page.waitForLoadState('domcontentloaded');
        const categoryNames: string[] = ['Science', 'Music'];
        for (let i = 0; i < categoryNames.length; i++) {
            await this.validateAndFillStrings(Selectors.settingsSetup.categories.addNewCategory, categoryNames[i]);
            await this.validateAndClick(Selectors.settingsSetup.categories.submitCategory);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.settingsSetup.categories.validateCategory(categoryNames[i]));
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
        //Go to Settings - General page
        await this.navigateToURL(this.wpufSettingsPage);

        await this.page.reload();

        await this.validateAndClick(Selectors.settingsSetup.keys.clickSettingsTabGeneral);
        //Google Map API Key
        await this.validateAndFillStrings(Selectors.settingsSetup.keys.fillGoogleMapAPIKey, googleMapAPIKey);
        //Save Settings
        await this.validateAndClick(Selectors.settingsSetup.keys.settingsTabGeneralSave);
    }

    async addReCaptchaKeys(recaptchaSiteKey: string, recaptchaSecretKey: string) {
        //Go to Settings - General page
        await this.navigateToURL(this.wpufSettingsPage);

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
        await this.navigateToURL(this.wpufSettingsPage);

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
        //await this.validateAndClick(Selectors.settingsSetup.keys.clickLoginOrRegistration);
        // Cloudflare Turnstile enable
        //await this.validateAndClick(Selectors.settingsSetup.keys.enableCloudflareTurnstileLogin);
        //Save Settings
        //await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);
    }

    async enablePaymentGatewayBank() {
        //Go to Settings - General page
        await this.navigateToURL(this.wpufSettingsPage);
        await this.page.reload();
        await this.validateAndClick(Selectors.settingsSetup.payment.clickPaymentTab);
        await this.validateAndClick(Selectors.settingsSetup.payment.clickPaymentGatewayBank);
        await this.validateAndClick(Selectors.settingsSetup.payment.settingsTabPaymentSave);
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
        await this.page.waitForTimeout(30000);
        await this.navigateToURL(this.pluginsPage);
        await this.page.reload();
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWCvendors);


        await this.navigateToURL(this.pluginsPage);
        await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickDoNotAllow);

    }

}
