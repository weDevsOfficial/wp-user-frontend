require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'

export class settingsSetup {
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
        const wpufSetupPage = testData.urls.baseUrl + '/wp-admin/index.php?page=wpuf-setup';
        await Promise.all([
            this.page.goto(wpufSetupPage, { waitUntil: 'networkidle' }),
        ]);

        const wpufSetup = await this.page.isVisible(selectors.settingsSetup.wpufSetup.clickWPUFSetupSkip);
        if (wpufSetup == true) {
            //await this.page.click(SelectorsPage.settingsSetup.clickWPUFSetupSkip);
            await this.page.click(selectors.settingsSetup.wpufSetup.clickWPUFSetupLetsGo);
            await this.page.click(selectors.settingsSetup.wpufSetup.clickWPUFSetupContinue);
            await this.page.click(selectors.settingsSetup.wpufSetup.clickWPUFSetupEnd);
        }
        
    };
    




/**************************************************/
/*************** @Plugin Activate ****************/
/************************************************/
    
    //Plugin Activate - Lite
    async pluginStatusCheckLite() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        //Activate Lite
        await this.activateWPUFLite();
    };


    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        //Activate Pro
        await this.activateWPUFPro();
    };





/************************************************************/
/*************** @Plugin Activate Functions ****************/
/**********************************************************/

    //Plugin Page - Visit
    async pluginVisit() {
        //Go to AdminEnd
        await Promise.all([
            this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(selectors.settingsSetup.pluginVisit.clickPostFormMenuOption);
            if (availableText == true) {    
                const checkText = await this.page.innerText(selectors.settingsSetup.pluginVisit.wpufPostFormCheckAddButton);
                await expect(checkText).toContain("Add Form");
            }

    };


    //Plugin Activate - Lite
    async activateWPUFLite() {
        //Go to Plugins page
        const pluginsPage = testData.urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);

        //Activate Plugin
        const activateWPUFLite = await this.page.isVisible(selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);

            if ( activateWPUFLite == true) {
                //Plugins were DeActive
                await this.page.click(selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginLite);
    
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Lite Status: Activated");
    };

    //Plugin Activate - Pro
    async activateWPUFPro() {
        //Go to Plugins page
        const pluginsPage = testData.urls.baseUrl + '/wp-admin/plugins.php';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);

        //Activate Plugin
        const activateWPUFPro = await this.page.isVisible(selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
        
            if ( activateWPUFPro== true) {
                //Plugins were DeActive
                await this.page.click(selectors.settingsSetup.pluginStatusCheck.clickWPUFPluginPro);
    
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Pro Status: Activated");

    };

    



/*********************************************************/
/******* @Change WPUF-Settings > Reset page *************/
/*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetLoginPageDefault() {
        //Go to WPUF
        const wpufpostformpage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'networkidle' }),
        ]);

        //Change Settings
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTab);
        //Validate Login/Registration
        await expect (await this.page.isVisible(selectors.settingsSetup.wpufSettingsPage.settingsTabProfile1)).toBeTruthy();
        //Click Login/Registration
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Login Page to default
        await this.page.selectOption(selectors.settingsSetup.wpufSettingsPage.settingsTabProfileLoginPage, {label: '— Select —'});
        //Save Login/Registration
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);
        
        await this.page.waitForLoadState('domcontentloaded');
    };


/*********************************************************/
/******* @Change WPUF-Settings > Reset page *************/
/*******************************************************/

    //Change Settings - Login Page
    async changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle) {
        //Go to WPUF
        const wpufpostformpage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufpostformpage, { waitUntil: 'networkidle' }),
        ]);

        //Change Settings
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTab);
        //Validate Frontend Posting
        await expect (await this.page.isVisible(selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting)).toBeTruthy();
        //Click Frontend Posting
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsFrontendPosting);
        //Set Default Post Form 
        await this.page.selectOption(selectors.settingsSetup.wpufSettingsPage.setDefaultPostForm, {label: 'FE PostForm'});
        //Save FrontEnd Posting
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsFrontEndPostingSave);
        
        await this.page.waitForLoadState('domcontentloaded');
    };




/*********************************************************/
/******* @Change WPUF-Settings > Registration ***********/
/*******************************************************/
    
    //Change Settings - Registration Page
    async changeSettingsSetRegistrationPage(registrationFormPageTitle) {
        //Go to WPUF
        const wpufPostFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);

        //Change Settings
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTab);
        //Validate Login/Registration
        await expect (await this.page.isVisible(selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2)).toBeTruthy();
        //Click Login/Registration
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTabProfile2);
        //Set Registration Page Form
        await this.page.selectOption(selectors.settingsSetup.wpufSettingsPage.settingsTabProfileRegistrationPage, {label: registrationFormPageTitle});
        //Save Login/Registration
        await this.page.click(selectors.settingsSetup.wpufSettingsPage.settingsTabProfileSave);
        
        await this.page.waitForLoadState('domcontentloaded');
    
    };





/********************************************/
/************ @Set Permalink ***************/
/******************************************/

    //Set Permalink
    async setPermalink() {
        //Go to Settings - Permalink page
        const settingsPermalinkPage = testData.urls.baseUrl + '/wp-admin/options-permalink.php';
        await Promise.all([
            this.page.goto(settingsPermalinkPage, { waitUntil: 'networkidle' }),
        ]);
        
        await this.page.reload();
        //Set Post Name Permalink
        await this.page.click(selectors.settingsSetup.setPermalink.clickCustomStructurePostName);
        //Validate Permalink - Postname select
        const validatePermalinkPostname = await this.page.innerText(selectors.settingsSetup.setPermalink.validatePermalinkPostname);
        //Save Permalink Settings
        await this.page.click(selectors.settingsSetup.setPermalink.savePermalinkSettings);
        await this.page.reload();
        //Save Permalink again
        await this.page.click(selectors.settingsSetup.setPermalink.savePermalinkSettings);
        

    };



/********************************************/
/********** @Create New User ***************/
/******************************************/

    //Main Admin
    //New User Create
    async createNewUserAdmin(username, email, fname, lname, password) {
        const pluginsPage = testData.urls.baseUrl + '/wp-admin/';
        await Promise.all([
            this.page.goto(pluginsPage, { waitUntil: 'networkidle' }),
        ]);
        
        //Go to Admin-Users
        await this.page.click(selectors.settingsSetup.createNewUser.clickUserMenuAdmin);
        //Add New User
        await this.page.click(selectors.settingsSetup.createNewUser.clickAddNewUserAdmin);
        await this.page.waitForLoadState('domcontentloaded');

        //New User creation flow
        //Enter Username
        await this.page.fill(selectors.settingsSetup.createNewUser.newUserName, username);
        //Enter Email
        await this.page.fill(selectors.settingsSetup.createNewUser.newUserEmail, email);
        //Enter First Name
        await this.page.fill(selectors.settingsSetup.createNewUser.newUserFirstName, fname);
        //Enter Last Name
        await this.page.fill(selectors.settingsSetup.createNewUser.newUserLastName, lname);
        //Enter Password
        await this.page.fill(selectors.settingsSetup.createNewUser.newUserPassword, password);
        //Allow weak Password
        await this.page.check(selectors.settingsSetup.createNewUser.newUserWeakPasswordAllow);
        //Select Role
        await this.page.waitForLoadState('domcontentloaded');
        await expect(await this.page.isVisible(selectors.settingsSetup.createNewUser.newUserSelectRole)).toBeTruthy();
        await this.page.selectOption(selectors.settingsSetup.createNewUser.newUserSelectRole, {label: 'Customer'});

        //Create User
        await this.page.click(selectors.settingsSetup.createNewUser.newUserSubmit);
    };



}
