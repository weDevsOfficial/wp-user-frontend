import dotenv from "dotenv";
dotenv.config();
import { expect, Page } from '@playwright/test';
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

        const wpufSetup = await this.validateAndReturn(Selectors.settingsSetup.wpufSetup.validateWPUFSetupPage);
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
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        //Activate Lite
        await this.activateWPUFLite();
    };


    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        //Activate Pro
        await this.activateWPUFPro();
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
        const availableText = await this.validateAndReturn(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            const checkText = await this.validateAndGetText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton);
            await expect(checkText).toContain("Add New");
        }

    };


    //Plugin Activate - Lite
    async activateWPUFLite() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);
        // try {
        //     expect(await this.page.locator('//a[@id="activate-wpuf-pro"]')).toBeVisible( {timeout: 3000} );
        //     console.log("WPUF-pro Status: Installed");
        // } catch (error) {
        //     console.log("WPUF-pro Status: Not Installed");
        // }
        //Activate Plugin
        const activateWPUFLite = await this.validateAndReturn(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
        console.log("installed");
        if (activateWPUFLite === true) {
            console.log("being activated");
            //Plugins is getting activated here
            await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
            console.log("activating");
            await this.page.reload();
            console.log("Plugins activated")
            // await this.page.goBack();

            await this.page.goto(Urls.baseUrl + '/wp-admin/');
            console.log("navigated to dashboard");
            await this.assertionValidate(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
            console.log("WPUF-Lite Status: Activated");
        }

        else {
            console.log("already activated");
            await this.assertionValidate(Selectors.login.basicNavigation.clickWPUFSidebar);
            console.log("navigated to dashboard");
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
            console.log("WPUF-Lite Status: Activated");
        }
    };

    //Plugin Activate - Pro
    async activateWPUFPro() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);

        //Activate Plugin
        const activateWPUFPro = await this.validateAndReturn(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);

        if (activateWPUFPro == true) {
            //Plugins were DeActive
            await this.validateAndClick(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);

            await this.assertionValidate(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        }
        else {
            await this.assertionValidate(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.validateAndClick(Selectors.login.basicNavigation.clickWPUFSidebar);
        }
        console.log("WPUF-Pro Status: Activated");

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
        const validatePermalinkPostname = await this.validateAndGetText(Selectors.settingsSetup.setPermalink.validatePermalinkPostname);
        //Save Permalink Settings
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);
        await this.page.reload();
        //Save Permalink again
        await this.validateAndClick(Selectors.settingsSetup.setPermalink.savePermalinkSettings);


    };



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
