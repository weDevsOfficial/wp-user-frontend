require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'

export class basicLoginPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;

    }


    


    
    /**----------------------------------BASIC_LOGIN(1,2)----------------------------------**
     * 
     * 
     * @step_01 Admin is logging in
     * 
     *  
     */
    async basic_login(email, password) {
        const adminEmail = email;
        const adminPassword = password;

        console.log(testData.urls.baseUrl);
        
        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });
        
        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (emailStateCheck == true) {
                await this.backend_Login(adminEmail, adminPassword);
            }
            else {
                await this.frontend_Login(adminEmail, adminPassword);
            }
        
        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
    };


    async basic_login_plugin_visit(email, password) {
        const adminEmail = email;
        const adminPassword = password;

        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });

        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (emailStateCheck == true) {
                await this.backend_Login(adminEmail, adminPassword);
            }
            else {
                await this.frontend_Login(adminEmail, adminPassword);
            }

            
        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
        
        //Redirection to WPUF Home Page
        await this.pluginVisit();
    };



    /**----------------------------------WPUF_Setup----------------------------------**
     * 
     * 
     * @step_02 Admin is completing WPUF Setup
     *  
     * 
     */
    async wpufSetup() {
        //WPUF Setup
        const wpuf_setup_page = testData.urls.baseUrl + 'index.php?page=wpuf-setup';
        
        await this.page.goto(wpuf_setup_page, { waitUntil: 'networkidle' }); 
        const wpuf_Setup = await this.page.isVisible(selectors.login.wpufSetup.clickWPUFSetupSkip);
            if (wpuf_Setup == true) {
                //await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupLetsGo);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupContinue);
                await this.page.click(selectors.login.wpufSetup.clickWPUFSetupEnd);
            }
    };



    /**----------------------------------VALIDATE_LOGIN----------------------------------**
     * 
     * 
     * @step_03 Admin is validating Basic Login
     * 
     *  
     */
    async validateBasicLogin() {
        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });
        //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const dashboard_Landed = await this.page.isVisible(selectors.login.validateBasicLogin.logingSuccessDashboard);
        await expect(dashboard_Landed).toBeTruthy;    
    };



    /**----------------------------------PLUGIN STATUS + PLUGIN Activate + VISIT----------------------------------**
     * 
     * 
     * @step_01 Admin is checking plugin-status + plugin-visit
     * 
     *  
     */
    async pluginStatusCheck_Lite_Activate() {
        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });
        
        //Activate Lite
        await this.activate_WPUF_Lite();
    };


    async pluginStatusCheck_Pro_Activate() {
        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });
        
        //Activate Pro
        await this.activate_WPUF_Pro();
    };



    async pluginVisit() {
        await this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' });
        await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(selectors.login.pluginVisit.clickPostFormMenuOption);
            if (availableText == true) {    
                const checkText = await this.page.innerText(selectors.login.pluginVisit.wpufPostForm_CheckAddButton);
                await expect(checkText).toContain("Add Form");
            }

    };


    //Plugin Activation Check
    async activate_WPUF_Lite() {
        const plugins_page = testData.urls.baseUrl + 'plugins.php';

        await this.page.goto(plugins_page, { waitUntil: 'networkidle' });
        //Activate Plugin
        const activate_WPUF_Lite = await this.page.isVisible(selectors.login.pluginStatusCheck.clickWPUF_LitePlugin);

            if ( activate_WPUF_Lite == true) {
                //Plugins were DeActive
                await this.page.click(selectors.login.pluginStatusCheck.clickWPUF_LitePlugin);
    
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Lite Status: Activated");
    };

    async activate_WPUF_Pro() {
        const plugins_page = testData.urls.baseUrl + 'plugins.php';

        await this.page.goto(plugins_page, { waitUntil: 'networkidle' });
        //Activate Plugin
        const activate_WPUF_Pro = await this.page.isVisible(selectors.login.pluginStatusCheck.clickWPUF_ProPlugin);
        
            if ( activate_WPUF_Pro== true) {
                //Plugins were DeActive
                await this.page.click(selectors.login.pluginStatusCheck.clickWPUF_ProPlugin);
    
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(selectors.login.basicNavigation.clickWPUFSidebar);
                await this.page.click(selectors.login.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Pro Status: Activated");

    };



    /**----------------------------------CHANGE_Settings----------------------------------**
      * 
      * 
      * @step_01 Admin is changing Settings
      * 
      *  
      */
    async change_WPUF_Settings() {
        const wpuf_post_form_page = testData.urls.baseUrl + 'admin.php?page=wpuf-post-forms';

        await this.page.goto(wpuf_post_form_page, { waitUntil: 'networkidle' });
        //Change Settings
        await this.page.click(selectors.login.wpuf_SettingsPage.settingsTab);
        await expect (await this.page.isVisible(selectors.login.wpuf_SettingsPage.settingsTab_Profile1)).toBeTruthy();
        await this.page.click(selectors.login.wpuf_SettingsPage.settingsTab_Profile2);
        await this.page.selectOption(selectors.login.wpuf_SettingsPage.settingsTab_Profile_LoginPage, {label: '— Select —'});
        await this.page.click(selectors.login.wpuf_SettingsPage.settingsTab_Profile_Submit);
        
        await this.page.waitForLoadState('domcontentloaded');
    };




    /**----------------------------------FRONTEND + BACKEND Login----------------------------------**
     * 
     * 
     * @Here BackEnd and FrontEnd Login Functions are present
     * 
     *  
     */
    async backend_Login(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField, password);

        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton);
    }

    async frontend_Login(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField2, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField2);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField2, password);

        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton2);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton2);
    }




}
