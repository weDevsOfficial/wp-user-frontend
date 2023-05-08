export const Selectors_LoginPage = {


    /** @Here Admin is doing a Basic Login and Validating the login
     * 
     * 
     * 
    */
    basicLogin:{
        //Login-1
        loginEmailField: '//input[@id="user_login"]',
        loginPasswordField: '//input[@id="user_pass"]',
        loginButton: '//input[@id="wp-submit"]',
        //Login-2
        loginEmailField2: '//input[@id="wpuf-user_login"]',
        loginPasswordField2: '//input[@id="wpuf-user_pass"]',
        loginButton2: '//input[@type="submit"]',
    },

    validateBasicLogin: {
        //Validate LOGIN
        logingSuccessDashboard: '//div[text()="Dashboard"]',
        //clickWPUFSidebar: '//div[text()="User Frontend"]/.',
        clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
    },



    /** @Here Admin is doing Basic Navigation
     * 
     * 
     * 
    */
    basicNavigation: {
        //Sidebar
        clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
    },


    
    /** @Here Admin is checking WPUF plugin-status and visiting plugin-page
     *
     * 
     * 
    */
    pluginStatusCheck: {
        //Plugin Activate/Deactivate
        clickPluginsSidebar: '//li[@id="menu-plugins"]',
        clickWPUF_LitePlugin: '//a[@id="activate-wp-user-frontend"]',
        clickWPUF_ProPlugin: '//a[@id="activate-wp-user-frontend-pro"]',
    },

    pluginVisit: {
        //WPUF > Pages > Navigation
        //Sidebar
        //PostFormPage
        clickPostFormMenuOption: '//a[contains(text(), "Post Forms")]',
        wpufPostForm_CheckAddButton: '#new-wpuf-post-form',
        wpufRegistrationForm_CheckAddButton: '//a[@id="new-wpuf-profile-form"]',
        postFormsPageFormTitleCheck: '(//a[@class="row-title"])[1]',
    },



    /** @Here Admin is doing a WPUF Setup and WPUF Settings Page Update
     *  
     * 
     * 
    */
    wpufSetup: {
        //WPUF Setup 
        //Skip Setup
        clickWPUFSetupSkip: '//a[@class="button button-large" and contains(text(), "Not right now")]',
        //Continue Setup
        clickWPUFSetupLetsGo: '//a[contains(@class,"button-primary button")]',
        clickWPUFSetupContinue: '//input[@type="submit"]',
        clickWPUFSetupEnd: '//a[contains(@class,"button button-primary")]',
    },


    wpuf_SettingsPage: {
        settingsTab: '//a[@href="admin.php?page=wpuf-settings"]',
        settingsTab_Profile1: '//a[@href="#wpuf_profile"]',
        settingsTab_Profile2: '#wpuf_profile-tab',
        settingsTab_Profile_LoginPage: '//select[@id="wpuf_profile[login_page]"]',
        settingsTab_Profile_Submit: '//div[@id="wpuf_profile"]//form[@method="post"]//div//input[@id="submit"]'
      } 


};