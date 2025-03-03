require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';

export class SettingsSetupPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;

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
            //await this.page.click(SelectorsPage.settingsSetup.clickWPUFSetupSkip);
            await this.page.click(Selectors.settingsSetup.wpufSetup.clickWPUFSetupLetsGo);
            await this.page.click(Selectors.settingsSetup.wpufSetup.clickWPUFSetupContinue);
            await this.page.click(Selectors.settingsSetup.wpufSetup.clickWPUFSetupEnd);
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

        await this.page.click(Selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
        if (availableText == true) {
            const checkText = await this.page.innerText(Selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton);
            await expect(checkText).toContain("Add Form");
        }

    };


    //Plugin Activate - Lite
    async activateWPUFLite() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);

        //Activate Plugin
        const activateWPUFLite = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);

        if (activateWPUFLite == true) {
            //Plugins is getting activated here
            await this.page.click(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);

            await this.page.reload();
            await this.page.goBack();

            await this.page.goto(Urls.baseUrl + '/wp-admin/');
            await this.page.isVisible(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.page.click(Selectors.login.basicNavigation.clickWPUFSidebar);
        }

        else {
            await this.page.isVisible(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.page.click(Selectors.login.basicNavigation.clickWPUFSidebar);
        }
        console.log("WPUF-Lite Status: Activated");
    };

    //Plugin Activate - Pro
    async activateWPUFPro() {
        //Go to Plugins page
        const pluginsPage = Urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);

        //Activate Plugin
        const activateWPUFPro = await this.page.isVisible(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);

        if (activateWPUFPro == true) {
            //Plugins were DeActive
            await this.page.click(Selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);

            await this.page.isVisible(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.page.click(Selectors.login.basicNavigation.clickWPUFSidebar);
        }
        else {
            await this.page.isVisible(Selectors.login.basicNavigation.clickWPUFSidebar);
            await this.page.click(Selectors.login.basicNavigation.clickWPUFSidebar);
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
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Login/Registration
        expect(await this.page.isVisible(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1)).toBeTruthy();
        console.log(await this.page.isVisible(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1));
        //Click Login/Registration
        await this.page.waitForSelector(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Login Page to default
        expect(await this.page.waitForSelector(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage)).toBeTruthy();
        //Again - Click Login/Registration
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage, { label: '— Select —' });
        //Save Login/Registration
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);

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
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Frontend Posting
        await expect(await this.page.isVisible(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting)).toBeTruthy();
        //Click Frontend Posting
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Set Default Post Form 
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.setDefaultPostForm, { label: 'FE PostForm' });
        //Save FrontEnd Posting
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsFrontendPostingSave);

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
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTab);
        await this.page.reload();
        //Validate Login/Registration
        await expect(await this.page.isVisible(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1)).toBeTruthy();
        //Click Login/Registration
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Registration Page Form
        await this.page.selectOption(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileRegistrationPage, { label: registrationFormPageTitle });
        //Save Login/Registration
        await this.page.click(Selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);

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
        await this.page.fill(Selectors.settingsSetup.setPermalink.fillCustomStructure, '');
        //Set Post Name Permalink
        await this.page.click(Selectors.settingsSetup.setPermalink.clickCustomStructurePostName);
        //Validate Permalink - Postname select
        const validatePermalinkPostname = await this.page.innerText(Selectors.settingsSetup.setPermalink.validatePermalinkPostname);
        //Save Permalink Settings
        await this.page.click(Selectors.settingsSetup.setPermalink.savePermalinkSettings);
        await this.page.reload();
        //Save Permalink again
        await this.page.click(Selectors.settingsSetup.setPermalink.savePermalinkSettings);


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
        await this.page.click(Selectors.settingsSetup.createNewUser.clickUserMenuAdmin);
        //Add New User
        await this.page.click(Selectors.settingsSetup.createNewUser.clickAddNewUserAdmin);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');

        //New User creation flow
        //Enter Username
        await this.page.fill(Selectors.settingsSetup.createNewUser.newUserName, userName);
        //Enter Email
        await this.page.fill(Selectors.settingsSetup.createNewUser.newUserEmail, email);
        //Enter First Name
        await this.page.fill(Selectors.settingsSetup.createNewUser.newUserFirstName, firstName);
        //Enter Last Name
        await this.page.fill(Selectors.settingsSetup.createNewUser.newUserLastName, lastName);
        //Enter Password
        await this.page.fill(Selectors.settingsSetup.createNewUser.newUserPassword, password);
        //Allow weak Password
        await this.page.check(Selectors.settingsSetup.createNewUser.newUserWeakPasswordAllow);
        //Select Role
        await this.page.waitForLoadState('domcontentloaded');
        await expect(await this.page.isVisible(Selectors.settingsSetup.createNewUser.newUserSelectRole)).toBeTruthy();
        await this.page.selectOption(Selectors.settingsSetup.createNewUser.newUserSelectRole, { label: 'Subscriber' });

        //Create User
        await this.page.click(Selectors.settingsSetup.createNewUser.newUserSubmit);
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
        await this.page.fill(Selectors.resetWordpreseSite.wpResetInputBox, 'reset');
        await this.page.click(Selectors.resetWordpreseSite.wpResetSubmitButton);
        await this.page.click(Selectors.resetWordpreseSite.wpResetConfirmWordpressReset);
        await this.page.waitForLoadState('networkidle');
    };

}
