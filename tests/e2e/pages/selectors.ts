export const selectors = {



/*********************************/
/******* Login Selectors ********/
/*******************************/

    login: {
        
        //Basic Login
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

        //Validate Basic Login
        validateBasicLogin: {
            //Validate LOGIN
            logingSuccessDashboard: '//div[text()="Dashboard"]',
            //clickWPUFSidebar: '//div[text()="User Frontend"]/.',
            clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
        },


        //Basic Navigation
        basicNavigation: {
            //Sidebar
            clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
        },


        
        //Plugin Status Check
        pluginStatusCheck: {
            //Plugin Activate/Deactivate
            clickPluginsSidebar: '//li[@id="menu-plugins"]',
            clickWPUFPluginLite: '//a[@id="activate-wp-user-frontend"]',
            clickWPUFPluginPro: '//a[@id="activate-wp-user-frontend-pro"]',
        },

        //Plugin Visit
        pluginVisit: {
            //WPUF > Pages > Navigation
            //Sidebar
            //PostFormPage
            clickPostFormMenuOption: '//a[contains(text(), "Post Forms")]',
            wpufPostFormCheckAddButton: '#new-wpuf-post-form',
            wpufRegistrationFormCheckAddButton: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck: '(//a[@class="row-title"])[1]',
        },


        //WPUF Setup
        wpufSetup: {
            //WPUF Setup 
            //Skip Setup
            clickWPUFSetupSkip: '//a[@class="button button-large" and contains(text(), "Not right now")]',
            //Continue Setup
            clickWPUFSetupLetsGo: '//a[contains(@class,"button-primary button")]',
            clickWPUFSetupContinue: '//input[@type="submit"]',
            clickWPUFSetupEnd: '//a[contains(@class,"button button-primary")]',
        },

        //WPUF Settings Page
        wpufSettingsPage: {
            settingsTab: '//a[@href="admin.php?page=wpuf-settings"]',
            settingsTabProfile1: '//a[@href="#wpuf_profile"]',
            settingsTabProfile2: '#wpuf_profile-tab',
            settingsTabProfileLoginPage: '//select[@id="wpuf_profile[login_page]"]',
            settingsTabProfileRegistrationPage: '//select[@id="wpuf_profile[reg_override_page]"]',
            settingsTabProfileSubmit: '//div[@id="wpuf_profile"]//form[@method="post"]//div//input[@id="submit"]'
        } 
    },


/*********************************/
/******* Logout Selectors *******/
/*******************************/

    logout: {

        /** @Here Admin is doing a Basic Logout and Validating the logout success
         * 
         * 
         * 
        */
        basicLogout:{
            logoutHoverUsername: '//a[@class="ab-item" and contains(text(), "Howdy, ")]',
            logoutButton: '//a[@class="ab-item" and contains(text(), "Log Out")]',

            //Validate LOGOUT
            logoutSuccess: '//div[@class="wpuf-message"]',
        
        },

        validateBasicLogout: {
            
        },

    },


/*********************************************/
/********** @Post_Forms Selectors ***********/
/*******************************************/ 

    postForms: {

        /**@Here Locators creating Navigating Post Forms Page
         * 
         * 
         * 
         */
        navigatePage_PF : {
            //WPUF > Pages > Navigation
            checkAddButton_PF: '#new-wpuf-post-form',
            postFormsPageFormsTitleCheck_PF: '(//a[@class="row-title"])[1]',

            //New_Created_NAME_Checker
            newPostCreatedName_PF: '(//a[@class="row-title"])[1]',
        },


        /**@Here Locators creating Post > Blank Form
         * 
         */
        createBlankForm_PF: {
            //Create_New_Post_Form
            clickpostFormsMenuOption: '//a[contains(text(), "Post Forms")]',
        
            //Add Form
            clickPostAddForm: '//a[@class="page-title-action add-form" and contains(text(), "Add Form")]',
            
            //Start > Blank Form
            hoverBlankForm: '.blank-form',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',

            //Enter_NAME
            editNewFormName: '//span[text()="Sample Form"]',
            enterNewFormName: '//header[@class="clearfix"]/span/input',  //TODO: Catch with Child
            confirmNewNameTickButton: '//button[@class="button button-small"]',
        },


        createPreset_PF: {
            //Start > Preset Form
            hoverPresetForm: '(//div[@class="content"]//li)[2]',
            clickPresetForm: '//a[@title="Post Form" and contains(text(), "Create Form")]',

            //Enter_NAME
            editNewFormName: '//span[text()="Post Form"]',
        },


        /**@Here Locators for All Fields Options + Save
         * 
         */
        /********************* PostFields *********************/
        addPostFields_PF: {
            //Post_Fields
            postTitleBlock: '//li[@data-form-field="post_title"]',
            postContentBlock: '//li[@data-form-field="post_content"]',
            postExcerptBlock: '//li[@data-form-field="post_excerpt"]',
            featuredImageBlock: '//li[@data-form-field="featured_image"]',
        },

        validatePostFields_PF: {      //TODO: Inconsistent with Blank form
            validatePostTitle: '//label[@for="post_title"]/../..//div[@class="wpuf-fields"]',
            validatePostContent: '//label[@for="post_content"]/../..//div[@class="wpuf-fields"]',
            validateExcerpt: '//label[@for="post_excerpt"]/../..//div[@class="wpuf-fields"]',
            validateFeaturedImage: '//label[@for="featured_image"]/../..//div[@class="wpuf-fields"]',
        },


        /********************* Taxonomies *********************/
        addTaxonomies_PF: {
            //Taxonomies
            categoryBlock: '//li[@data-form-field="category"]',
            tagsBlock: '//li[@data-form-field="post_tag"]',
        },

        validateTaxonomies_PF: {
            validateCategory: '//li[@class="field-items wpuf-el category form-field-taxonomy field-size-small"]',
            validateTags: '//li[@class="field-items wpuf-el tags form-field-post_tags field-size-large"]',
        },

        validateTaxonomiesPreset_PF: {
            validateCategory: '//label[@for="category"]/../..//div[@class="wpuf-fields"]',
            validateTags: '//label[@for="tags"]/../..//div[@class="wpuf-fields"]',
        },


/***********************************************/
/********** @CommonFields Selectors ***********/
/*********************************************/ 

        //Custom - Field options for Forms
        addCustomFields_Common: {
            //Custom _Fields
            customFieldsText: '//li[@data-form-field="text_field"]',
            customFieldsTextarea: '//li[@data-form-field="textarea_field"]',
            customFieldsDropdown: '//li[@data-form-field="dropdown_field"]',
            customFieldsMultiSelect: '//li[@data-form-field="multiple_select"]',
            customFieldsRadio: '//li[@data-form-field="radio_field"]',
            customFieldsCheckBox: '//li[@data-form-field="checkbox_field"]',
            customFieldsWebsiteUrl: '//li[@data-form-field="website_url"]',
            customFieldsEmailAddress: '//li[@data-form-field="email_address"]',
            customFieldsHiddenField: '//li[@data-form-field="custom_hidden_field"]',
            customFieldsImageUpload: '//li[@data-form-field="image_upload"]',
            
            //From___PRO
            customFieldsRepeatField: '//li[@data-form-field="repeat_field"]',
            customFieldsDateTime: '//li[@data-form-field="date_field"]',
            customFieldsTimeField: '//li[@data-form-field="time_field"]', 
            customFieldsFileUpload: '//li[@data-form-field="file_upload"]',
            customFieldsCountryList: '//li[@data-form-field="country_list_field"]',
            customFieldsNumericField: '//li[@data-form-field="numeric_text_field"]',
            customFieldsPhoneField: '//li[@data-form-field="phone_field"]', 
            customFieldsAddressField: '//li[@data-form-field="address_field"]',
            customFieldsGoogleMaps: '//li[@data-form-field="google_map"]',      
            customFieldsStepStart: '//li[@data-form-field="step_start"]',
            customFieldsEmbed: '//li[@data-form-field="embed"]',

            //prompt1
            prompt1PopUpModalClose: "//button[contains(@class,'swal2-confirm btn')]",
            //prompt2
            prompt2PopUpModalOk:'//button[contains(@class,"swal2-confirm swal2-styled")]',
            //Pro Check Pop Up
            checkProPopUp: '//button[text()="Get the Pro version"]',
            //Pro Text Alert in Settings
            proTextAlertInSettings: '(//h3[@class="wpuf-pro-text-alert"])[1]'
        },

        //Validate Custom Fields
        validateCustomFields_Common: {
            validateText: '//label[@for="text"]/../..//div[@class="wpuf-fields"]',
            validateTextarea: '//label[@for="textarea"]/../..//div[@class="wpuf-fields"]',
            validateDropdown: '//label[@for="dropdown"]/../..//div[@class="wpuf-fields"]',  
            validateMultiSelect: '//label[@for="multi_select"]/../..//div[@class="wpuf-fields"]',
            validateRadio: '//label[@for="radio"]/../..//div[@class="wpuf-fields"]',
            validateCheckBox: '//label[@for="checkbox"]/../..//div[@class="wpuf-fields"]',
            validateWebsiteUrl: '//label[@for="website_url"]/../..//div[@class="wpuf-fields"]',
            validateEmailAddress: '//label[@for="email_address"]/../..//div[@class="wpuf-fields"]',
            validateHiddenField: '//div[@class="hidden-field-list"]//li[@class="field-items"]',
            validateImageUpload: '//label[@for="image_upload"]/../..//div[@class="wpuf-fields"]',
            
            //From___PRO
            validateRepeatField: '//label[@for="repeat_field"]/../..//div[@class="wpuf-fields"]',
            validateDateTime: '//label[@for="date___time"]/../..//div[@class="wpuf-fields"]',  //TODO: Date - Time has large underscore
            validateTimeField: '//label[@for="time_field"]/../..//div[@class="wpuf-fields"]', 
            validateFileUpload: '//label[@for="file_upload"]/../..//div[@class="wpuf-fields"]',
            validateCountryList: '//label[@for="country_list"]/../..//div[@class="wpuf-fields"]',
            validateNumericField: '//label[@for="numeric_field"]/../..//div[@class="wpuf-fields"]',
            validatePhoneField: '//label[@for="phone_field"]/../..//div[@class="wpuf-fields"]', 
            validateAddressField: '//label[@for="addr_field_label"]/../..//div[@class="wpuf-fields"]',
            //validateGoogleMaps: '',           //TODO: Setup required
            validateStepStart: '//div[@class="step-start-indicator"]/../..',
            validateEmbed: '//label[@for="embed"]/../..//div[@class="wpuf-fields"]',
        },


        //Others - Field options for Forms
        addOthers_Common: {
            //Others
            othersColumns: '//li[@data-form-field="column_field"]',
            othersSectionBreak: '//li[@data-form-field="section_break"]',
            othersCustomHTML: '//li[@data-form-field="custom_html"]',
            othersQrCode: '//li[@data-form-field="qr_code"]', 
            othersReCaptcha: '//li[@data-form-field="recaptcha"]',
            
            
            //From___PRO
            //Pro Check Pop Up
            //check_Pro_Pop_UP: '//button[text()="Get the Pro version"]',
            //Pro Field Options
            othersShortCode: '//li[@data-form-field="shortcode"]',
            othersActionHook: '//li[@data-form-field="action_hook"]',
            othersTermsAndConditions: '//li[@data-form-field="toc"]',
            othersRatings: '//li[@data-form-field="ratings"]',
            othersReallySimpleCaptcha: '//li[@data-form-field="really_simple_captcha"]',
            othersMathCaptcha: '//li[@data-form-field="math_captcha"]',

            //Add Multi-Step-Check
            formEditorSettings: '(//h2[@class="nav-tab-wrapper"]//a)[2]',
            checkMultiStepOption: '//input[@name="wpuf_settings[enable_multistep]"]',
        },

        validateOthers_Common: {
            validateColumns: '//div[@class="wpuf-field-columns has-columns-3"]',
            validateSectionBreak: '//h2[text()="Section Break"]/../..//div[@class="wpuf-section-details"]',
            validateCustomHTML: '//div[text()="HTML Section"]/..//div[@class="wpuf-fields"]',
            
            //validate ReCaptcha: '',            //TODO: Setup required
            validateShortcode: '//label[@for="shortcode"]/../..//div[@class="wpuf-fields"]',
            validateActionHook: '//div[text()="Action Hook"]/../..//div[@class="wpuf-fields"]',
            validateTermsAndConditions: '//div[@class="wpuf-toc-container"]/..//div[@class="wpuf-fields clearfix has-toc-checkbox"]',
            validateRatings: '//label[@for="ratings"]/../..//div[@class="wpuf-fields"]',
            //validate ReallySimpletCaptcha: '',            //TODO: Setup required
            validateMathCaptcha: '//label[@for="math_captcha"]/../..//div[@class="wpuf-fields"]',

        },


        //Save Forms
        saveForm_Common: {
            //Validate Name
            formNameReCheck: '.form-title',
            //FINISH
            saveFormButton: '//button[@class="button button-primary" and contains(text(), "Save Form")]',
        },


    },




/****************************************************/
/********** @RegistrationForms Selectors ***********/
/**************************************************/    

    registrationForms: {
        
        //Navigate Registration Forms Page
        navigatePage_RF : {
            //WPUF > Pages > Navigation
            checkAddButton_RF: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck_RF: '(//a[@class="row-title"])[1]',

            //New_Created_NAME_Checker
            newPostCreatedName_RF: '(//a[@class="row-title"])[1]',
        },
            

        //Create Registration Forms - Blank
        createBlankForm_RF: {
            //Create_New_Post_Form
            clickRegistrationFormMenuOption: '//a[contains(text(), "Registration Forms")]',

            //Profile_Name
            validateRegistrationFormPageName: '//h2[contains(text(), "Profile Forms")]',

            //Start
            clickRegistraionAddForm: '//a[@id="new-wpuf-profile-form" and contains(text(), "Add Form")]',
            //clickRegistraionAddForm: '//a[@id="new-wpuf-profile-form"]',
            hoverBlankForm: '.blank-form',
            //hoverBlankForm: '(//div[@class="form-create-overlay"])[1]/..',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',

            //Enter_NAME
            editNewFormName: '//span[text()="Sample Registration Form"]',
            enterNewFormName: '//header[@class="clearfix"]//span//input',  //TODO: Catch with Child
            confirmNewNameTickButton: '//header[@class="clearfix"]//button',
        },



        //Create Registration Forms - Add Profile Fields
        addProfileFields_RF: {
            //Post_Fields
            profileFieldUsername: '//li[@data-form-field="user_login"]',
            profileFieldFirstName: '//li[@data-form-field="first_name"]',
            profileFieldLastName: '//li[@data-form-field="last_name"]',
            profileFieldDisplayName: '//li[@data-form-field="display_name"]',
            profileFieldNickName: '//li[@data-form-field="nickname"]',
            profileFieldEmail: '//li[@data-form-field="user_email"]',
            profileFieldWebsiteUrl: '//li[@data-form-field="user_url"]',
            profileFielBioInfo: '//li[@data-form-field="user_bio"]',
            profileFieldPassword: '//li[@data-form-field="password"]',
            profileFieldAvatar: '//li[@data-form-field="avatar"]',        
        },
    

    }

};