require('dotenv').config();
import { expect, Page, selectors } from '@playwright/test';
import { Selectors_LoginPage } from './selectors_Login';


export class BasicLoginPage {
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
    async basiclogin(email, password) {
        const AdminEmail = email;
        const AdminPassword = password;


        await this.page.goto('http://localhost:8889/wp-admin/', { waitUntil: 'networkidle' });
        const Email_State_Check = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (Email_State_Check == true) {
                await this.backend_Login(AdminEmail, AdminPassword);
            }
            else {
                await this.frontend_Login(AdminEmail, AdminPassword);
            }
        
        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
    };

    async basiclogin2(email, password) {
        const AdminEmail = email;
        const AdminPassword = password;
        
        await this.page.goto('http://localhost:8889/wp-admin/', { waitUntil: 'networkidle' });
        const Email_State_Check = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (Email_State_Check == true) {
                await this.backend_Login(AdminEmail, AdminPassword);
            }
            else {
                await this.frontend_Login(AdminEmail, AdminPassword);
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
        await this.page.goto('http://localhost:8889/wp-admin/index.php?page=wpuf-setup', { waitUntil: 'networkidle' }); 
        const WPUFSetup = await this.page.isVisible(Selectors_LoginPage.wpufSetup.clickWPUFSetupSkip);
            if (WPUFSetup == true) {
                //await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                await this.page.click(Selectors_LoginPage.wpufSetup.clickWPUFSetupLetsGo);
                await this.page.click(Selectors_LoginPage.wpufSetup.clickWPUFSetupContinue);
                await this.page.click(Selectors_LoginPage.wpufSetup.clickWPUFSetupEnd);
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

        await this.page.goto('http://localhost:8889/wp-admin/');
        //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(Selectors_LoginPage.validateBasicLogin.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;    
    };



    /**----------------------------------PLUGIN STATUS + PLUGIN Activate + VISIT----------------------------------**
     * 
     * 
     * @step_01 Admin is checking plugin-status + plugin-visit
     * 
     *  
     */
    async pluginStatusCheck_Lite_Activate() {
        await this.page.goto('http://localhost:8889/wp-admin/');
        
        //Activate Lite
        await this.activate_WPUF_Lite();
    };


    async pluginStatusCheck_Pro_Activate() {
        await this.page.goto('http://localhost:8889/wp-admin/');
        
        //Activate Pro
        await this.activate_WPUF_Pro();
    };



    async pluginVisit() {
        await this.page.goto('http://localhost:8889/wp-admin/');
        await this.page.click(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const availableText = await this.page.isVisible(Selectors_LoginPage.pluginVisit.clickPostFormMenuOption);
            if (availableText == true) {    
                const checkText = await this.page.innerText(Selectors_LoginPage.pluginVisit.wpufPostForm_CheckAddButton);
                await expect(checkText).toContain("Add Form");
            }

    };


    //Plugin Activation Check
    async activate_WPUF_Lite() {
        await this.page.goto('http://localhost:8889/wp-admin/plugins.php');
        const ActivateWPUF = await this.page.isVisible(Selectors_LoginPage.pluginStatusCheck.clickWPUF_LitePlugin);

            if ( ActivateWPUF == true) {
                //Plugins were DeActive
                await this.page.click(Selectors_LoginPage.pluginStatusCheck.clickWPUF_LitePlugin);
    
                await this.page.isVisible(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
                await this.page.click(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
                await this.page.click(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
            }
        console.log("WPUF-Lite Status: Activated");
    };

    async activate_WPUF_Pro() {
        await this.page.goto('http://localhost:8889/wp-admin/plugins.php');
        //Activate Plugin
        const ActivateWPUF_Pro = await this.page.isVisible(Selectors_LoginPage.pluginStatusCheck.clickWPUF_ProPlugin);
        
            if ( ActivateWPUF_Pro== true) {
                //Plugins were DeActive
                await this.page.click(Selectors_LoginPage.pluginStatusCheck.clickWPUF_ProPlugin);
    
                await this.page.isVisible(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
                await this.page.click(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
            }
            else {
                await this.page.isVisible(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
                await this.page.click(Selectors_LoginPage.basicNavigation.clickWPUFSidebar);
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
        await this.page.goto('http://localhost:8889/wp-admin/admin.php?page=wpuf-post-forms');
        //Change Settings
        await this.page.click(Selectors_LoginPage.wpuf_SettingsPage.settingsTab);
        await expect (await this.page.isVisible(Selectors_LoginPage.wpuf_SettingsPage.settingsTab_Profile1)).toBeTruthy();
        await this.page.click(Selectors_LoginPage.wpuf_SettingsPage.settingsTab_Profile2);
        await this.page.selectOption(Selectors_LoginPage.wpuf_SettingsPage.settingsTab_Profile_LoginPage, {label: '— Select —'});
        await this.page.click(Selectors_LoginPage.wpuf_SettingsPage.settingsTab_Profile_Submit);
        
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
        await this.page.fill(Selectors_LoginPage.basicLogin.loginEmailField, email);

        const PasswordCheck = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginPasswordField);
        await expect(PasswordCheck).toBeTruthy();
        await this.page.fill(Selectors_LoginPage.basicLogin.loginPasswordField, password);

        const LoginButtonCheck = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginButton);
        await expect(LoginButtonCheck).toBeTruthy();
        await this.page.click(Selectors_LoginPage.basicLogin.loginButton);
    }

    async frontend_Login(email, password) {
        await this.page.fill(Selectors_LoginPage.basicLogin.loginEmailField2, email);

        const PasswordCheck = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginPasswordField2);
        await expect(PasswordCheck).toBeTruthy();
        await this.page.fill(Selectors_LoginPage.basicLogin.loginPasswordField2, password);

        const LoginButtonCheck = await this.page.isVisible(Selectors_LoginPage.basicLogin.loginButton2);
        await expect(LoginButtonCheck).toBeTruthy();
        await this.page.click(Selectors_LoginPage.basicLogin.loginButton2);
    }




}
