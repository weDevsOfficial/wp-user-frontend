import dotenv from "dotenv";
dotenv.config();
import { Dialog, expect, Page } from '@playwright/test';
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
            this.page.goto(wpufSetupPage, { waitUntil: 'networkidle' }),
        ]);

        const wpufSetup = await this.page.isVisible(Selectors.settingsSetup.wpufSetup.validateWPUFSetupPage);
        if (wpufSetup == true) {
            //await this.validateAndClick(SelectorsPage.settingsSetup.clickWPUFSetupSkip);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupLetsGo);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupContinue);
            await this.validateAndClick(Selectors.settingsSetup.wpufSetup.clickWPUFSetupEnd);
        }

    };





    /**************************************************/
    /*************** @Plugin Activate ****************/
    /************************************************/

    //Plugin Activate - Lite
    async pluginStatusCheckLite() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'networkidle' }),
        ]);

        //Activate Lite
        await this.activateWPUFLite();
    };


    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'networkidle' }),
        ]);

        //Activate Pro
        await this.activateWPUFPro();
    };

    // Plugin Activate - License
    //Plugin Activate - Pro
    async licenseActivateWPUFPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/plugins.php', { waitUntil: 'networkidle' }),
        ]);

        //Activate Pro
        await this.activateLicenseWPUFPro();
    };



    /************************************************************/
    /*************** @Plugin Activate Functions ****************/
    /**********************************************************/

    //Plugin Page - Visit
    async pluginVisitWPUF() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            const checkText = await this.page.innerText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton);
            await expect(checkText).toContain("Add New");
        }

    };


    //Plugin Activate - Lite
    async activateWPUFLite() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        try {
            await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginLite);
            //Activate Plugin
            const activateWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
            console.log(activateWPUFLite);
            if (activateWPUFLite === true) {
                //Plugins is getting activated here
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
                await this.page.waitForTimeout(3000);
                await Promise.all([
                    this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
                ]);
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginDeactivate);

                await this.page.goto(Urls.baseUrl + '/wp-admin/');
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow);
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log("WPUF-Lite Status: is Activated");
            }

            else {
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log("WPUF-Lite Status: was Active");
            }
        } catch (e) {
            console.log("WPUF-Lite not available")
        }

    };

    //Plugin Activate - Pro
    async activateWPUFPro() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginPro);
        //Activate Plugin
        const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
        console.log(activateWPUFPro);
        if (activateWPUFPro == true) {
            //Plugins were DeActive
            await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
            await this.page.waitForTimeout(3000);
            await Promise.all([
                this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
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
            console.log("WPUF-Pro Status: is Activated");
        }
        else {
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.assertionValidate(Selectors.login.basicNavigation.licenseTab);
            console.log("WPUF-Pro Status: was Active");
        }

    };

    //Plugin Activate - Pro
    async activateLicenseWPUFPro() {
        const ifWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.availableWPUFPluginPro);
        if (ifWPUFPro == true) {
            //Go to Plugins page
            const pluginsPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
            await Promise.all([
                this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
                console.log("WPUF-Pro License Activation Line 1 passed"),
            ]);

            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.validateAndClick(Selectors.login.basicNavigation.licenseTab);
            //Activate Plugin
            const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickActivateLicense);
            console.log(activateWPUFPro);
            if (activateWPUFPro == true) {
                console.log("WPUF-Pro License Activation Line 3 passed"),
                    await this.validateAndFillStrings(Selectors.settingsSetup.pluginStatusCheck.fillLicenseKey, process.env.WPUF_PRO_LICENSE_KEY?.toString() || '');
                console.log("WPUF-Pro License Activation Line 5 passed");
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.submitLicenseKey);
                console.log("WPUF-Pro License Activation Line 6 passed");
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.deactivateLicenseKey);
                console.log("WPUF-Pro License Activation Line 7 passed");
                await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickAllow);
                console.log("WPUF-Pro License Activation Line 8 passed");
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log("WPUF-Pro Status: License is Activated");
            }
            else {
                console.log("WPUF-Pro License Activation Line 10 passed");
                await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
                console.log("WPUF-Pro License Activation Line 11 passed");
                await this.validateAndClick(Selectors.login.basicNavigation.licenseTab);
                console.log("WPUF-Pro License Activation Line 12 passed");
                await this.assertionValidate(Selectors.settingsSetup.pluginStatusCheck.deactivateLicenseKey);
                console.log("WPUF-Pro Status: License was Active");
            }
        }else {
            console.log("WPUF-Pro not available");
        }
    };





    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetLoginPageDefault() {
        //Go to WPUF
        const wpufpostformpage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'networkidle' }),
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

        await this.page.waitForLoadState('domcontentloaded');
    };


    /*********************************************************/
    /******* @Change WPUF-Settings > Reset page *************/
    /*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle) {
        //Go to WPUF
        const wpufpostformpage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'networkidle' }),
        ]);

        //Change Settings
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Frontend Posting
        await this.assertionValidate(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Click Frontend Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Turn On Custom Fields
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.showCustomFields);
        //Set Default Post Form 
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.setDefaultPostForm, { label: 'FE PostForm' });
        //Save FrontEnd Posting
        await this.validateAndClick(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);

        await this.page.waitForLoadState('domcontentloaded');
    };




    /*********************************************************/
    /******* @Change WPUF-Settings > Registration ***********/
    /*******************************************************/

    //Change Settings - Registration Page
    async changeSettingsSetRegistrationPage(registrationFormPageTitle) {
        //Go to WPUF
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
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

    };





    /********************************************/
    /************ @Set Permalink ***************/
    /******************************************/

    //Set Permalink
    async setPermalink() {
        //Go to Settings - Permalink page
        const settingsPermalinkPage = Urls.baseUrl + '/wp-admin/options-permalink.php';
        await Promise.all([
            this.page.goto(settingsPermalinkPage, { waitUntil: 'networkidle' }),
        ]);

        await this.page.reload();
        //Custom structure - fill with empty
        await this.validateAndFillStrings(Selectors.settingsSetup.setPermalink.fillCustomStructure, '');
        //Set Post Name Permalink
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.clickCustomStructurePostName);
        //Validate Permalink - Postname select
        const validatePermalinkPostname = await this.page.innerText(Selectors.settingsSetup.setPermalink.validatePermalinkPostname);
        //Save Permalink Settings
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);
        await this.page.reload();
        //Save Permalink again
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);


    };


    async allowRegistration() {
        //Go to Settings - General page
        const settingsGeneralPage = Urls.baseUrl + '/wp-admin/options-general.php';
        await Promise.all([
            this.page.goto(settingsGeneralPage, { waitUntil: 'networkidle' }),
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
    async createNewUserAdmin(userName, email, firstName, lastName, password) {
        const pluginsPage = Urls.baseUrl + '/wp-admin/';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
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
    };



    /***********************************************/
    /********** @Rest WorPress Site ***************/
    /*********************************************/

    async resetWordpressSite() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/tools.php?page=wp-reset', { waitUntil: 'networkidle' }),
        ]);
        await this.page.reload();
        await this.validateAndClick(Selectors.resetWordpreseSite.reActivateTheme);
        // await this.validateAndClick(Selectors.resetWordpreseSite.reActivatePlugins);
        await this.validateAndFillStrings(Selectors.resetWordpreseSite.wpResetInputBox, 'reset');
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetSubmitButton);
        await this.validateAndClick(Selectors.resetWordpreseSite.wpResetConfirmWordpressReset);
        await this.page.waitForTimeout(4000);
    };

}
