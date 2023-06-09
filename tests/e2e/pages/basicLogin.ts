require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'

export class basicLoginPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;

    }


    


    
/**************************************************/
/*************** @Login **************************/
/************************************************/

    //Basic Login
    async basicLogin(email, password) {
        const adminEmail = email;
        const adminPassword = password;

        console.log(testData.urls.baseUrl);
        
        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });
        
        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (emailStateCheck == true) {
                await this.backendLogin(adminEmail, adminPassword);
            }
            else {
                await this.frontendLogin(adminEmail, adminPassword);
            }
        
        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
    };

    //Login and Plugin Visit
    async basicLoginAndPluginVisit(email, password) {
        const adminEmail = email;
        const adminPassword = password;

        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });

        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (emailStateCheck == true) {
                await this.backendLogin(adminEmail, adminPassword);
            }
            else {
                await this.frontendLogin(adminEmail, adminPassword);
            }

            
        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
        
        //Redirection to WPUF Home Page
        await this.pluginVisit();
    };

    //Validate Login
    async validateBasicLogin() {
        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });
        //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const dashboardLanded = await this.page.isVisible(selectors.login.validateBasicLogin.logingSuccessDashboard);
        await expect(dashboardLanded).toBeTruthy;    
    };




/**************************************************/
/*************** @WPUF Setup *********************/
/************************************************/

    //WPUF Setup
    async wpufSetup() {
        //WPUF Setup
        const wpufSetupPage = testData.urls.baseUrl + '/wp-admin/index.php?page=wpuf-setup';
        
        await this.page.goto(wpufSetupPage, { waitUntil: 'networkidle' }); 
        const wpufSetup = await this.page.isVisible(selectors.login.wpufSetup.clickWPUFSetupSkip);
            if (wpufSetup == true) {
                //await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupLetsGo);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupContinue);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupEnd);
            }
    };
    




/**************************************************/
/*************** @Plugin Activate ****************/
/************************************************/
    
    //Plugin Activate - Lite
    async pluginStatusCheckLite() {
        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });
        
        //Activate Lite
        await this.activateWPUFLite();
    };

    //Plugin Activate - Pro
    async pluginStatusCheckPro() {
        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });
        
        //Activate Pro
        await this.activateWPUFPro();
    };





/************************************************************/
/*************** @Plugin Activate Functions ****************/
/**********************************************************/

    //Plugin Page - Visit
    async pluginVisit() {
        await this.page.goto(testData.urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' });
        await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(selectors.login.pluginVisit.clickPostFormMenuOption);
            if (availableText == true) {    
                const checkText = await this.page.innerText(selectors.login.pluginVisit.wpufPostFormCheckAddButton);
                await expect(checkText).toContain("Add Form");
            }

    };

    //Plugin Activate - Lite
    async activateWPUFLite() {
        const pluginsPage = testData.urls.baseUrl + '/wp-admin/plugins.php';

        await this.page.goto(pluginsPage, { waitUntil: 'networkidle' });
        //Activate Plugin
        const activateWPUFLite = await this.page.isVisible(selectors.login.pluginStatusCheck.clickWPUFPluginLite);

            if ( activateWPUFLite == true) {
                //Plugins were DeActive
                await this.page.click(selectors.login.pluginStatusCheck.clickWPUFPluginLite);
    
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
        const pluginsPage = testData.urls.baseUrl + '/wp-admin/plugins.php';

        await this.page.goto(pluginsPage, { waitUntil: 'networkidle' });
        //Activate Plugin
        const activateWPUFPro = await this.page.isVisible(selectors.login.pluginStatusCheck.clickWPUFPluginPro);
        
            if ( activateWPUFPro== true) {
                //Plugins were DeActive
                await this.page.click(selectors.login.pluginStatusCheck.clickWPUFPluginPro);
    
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Pro Status: Activated");

    };

    



/*******************************************************/
/*************** @Change WPUF Settings ****************/
/*****************************************************/

    //Change Settings - Login Page
    async changeSettingsSetLoginPageDefault() {
        const wpufpostformpage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';

        await this.page.goto(wpufpostformpage, { waitUntil: 'networkidle' });
        //Change Settings
        await this.page.click(selectors.login.wpufSettingsPage.settingsTab);
        await expect (await this.page.isVisible(selectors.login.wpufSettingsPage.settingsTabProfile1)).toBeTruthy();
        await this.page.click(selectors.login.wpufSettingsPage.settingsTabProfile2);
        await this.page.selectOption(selectors.login.wpufSettingsPage.settingsTabProfileLoginPage, {label: '— Select —'});
        await this.page.click(selectors.login.wpufSettingsPage.settingsTabProfileSubmit);
        
        await this.page.waitForLoadState('domcontentloaded');
    };

    
    





/**************************************************/
/**************** @Login Page ********************/
/************************************************/
    
    //BackEnd Login
    async backendLogin(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField, password);

        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton);
    }

    //FrontEnd Login
    async frontendLogin(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField2, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField2);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField2, password);

        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton2);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton2);
    }




}
