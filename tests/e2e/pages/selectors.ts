export const Selectors = {

    /*********************************/
    /******* Login Selectors *********/
    /*********************************/

    login: {
        // Basic Login
        basicLogin: {
            // Login-1
            loginEmailField: '//input[@id="user_login"]',
            loginPasswordField: '//input[@id="user_pass"]',
            rememberMeField: '//input[@id="rememberme"]',
            loginButton: '//input[@id="wp-submit"]',
            // Login-2
            loginEmailField2: '//input[@id="wpuf-user_login"]',
            loginPasswordField2: '//input[@id="wpuf-user_pass"]',
            loginButton2: '//input[@type="submit"]',
        },

        // Validate Basic Login
        validateBasicLogin: {
            // Validate LOGIN
            logingSuccessDashboard: '//div[text()="Dashboard"]',
            clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
        },

        // Basic Navigation
        basicNavigation: {
            // Sidebar
            clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
            // Hover Settings Menu
            hoverSettings: '//div[text()="Settings"]',
        },
    },

    /*******************************************/
    /******* Settings Setup Selectors *********/
    /*******************************************/

    settingsSetup: {
        // Plugin Status Check
        pluginStatusCheck: {
            // Plugin Activate/Deactivate
            clickPluginsSidebar: '//li[@id="menu-plugins"]',
            clickWPUFPluginLite: '//a[@id="activate-wp-user-frontend"]',
            clickWPUFPluginPro: '//a[@id="activate-wp-user-frontend-pro"]',
            clickWPUFPluginDeactivate: '//a[@id="deactivate-wp-user-frontend"]',
            clickWPUFPluginProDeactivate: '//a[@id="deactivate-wp-user-frontend-pro"]',
        },

        // Plugin Visit
        pluginVisit: {
            // WPUF > Pages > Navigation
            // Sidebar
            // PostFormPage
            clickPostFormMenuOption: '//a[contains(text(), "Post Forms")]',
            wpufPostFormCheckAddButton: '#new-wpuf-post-form',
            wpufRegistrationFormCheckAddButton: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck: '(//a[@class="row-title"])[1]',
        },

        // WPUF Setup
        wpufSetup: {
            // WPUF Setup 
            // Skip Setup
            validateWPUFSetupPage: '//h1[text()="Welcome to the world of WPUF!"]',
            // Continue Setup
            clickWPUFSetupLetsGo: '//a[contains(@class,"button-primary button")]',
            checkWPUFInstallPages: '//input[@name="install_wpuf_pages"]',
            clickWPUFSetupContinue: '//input[@type="submit"]',
            clickWPUFSetupEnd: '//a[contains(@class,"button button-primary")]',
        },

        // WPUF Settings Page
        wpufSettingsPage: {
            // Main Settings Tab
            settingsTab: '//a[@href="admin.php?page=wpuf-settings"]',

            // Menu-2nd Option
            // FrontEnd Posting
            settingsFrontendPosting: '//a[@id="wpuf_frontend_posting-tab"]',
            //Turn on custom field
            showCustomFields: '//label[normalize-space()="Show custom fields on post content area"]',
            // Set Default Post Form
            setDefaultPostForm: '//select[@id="wpuf_frontend_posting[default_post_form]"]',
            // Save Changes
            settingsFrontendPostingSave: '//div[@id="wpuf_frontend_posting"]//form[@method="post"]//div//input[@id="submit"]',

            // Menu-5th Option
            // Login/Registration
            settingsTabProfile1: '//a[@href="#wpuf_profile"]',
            settingsTabProfile2: '#wpuf_profile-tab',
            // Login Page
            settingsTabProfileLoginPage: '//select[@id="wpuf_profile[login_page]"]',
            // Registration Page
            settingsTabProfileRegistrationPage: '//select[@id="wpuf_profile[reg_override_page]"]',
            // Login Registration Submit button
            settingsTabProfileSave: '//div[@id="wpuf_profile"]//form[@method="post"]//div//input[@id="submit"]'
        },

        // Set Permalink
        setPermalink: {
            // Permalink Side Menu
            clickPermalinksSideMenu: '//a[text()="Permalinks"]',

            // Custom Structure fillup box
            fillCustomStructure: '//input[@id="permalink_structure"]',
            // Click Permalink-Postname
            clickCustomStructurePostName: '//button[@data-added="postname added to permalink structure"]',
            // Validate Permalink-Postname 
            validatePermalinkPostname: '//input[@id="permalink_structure"]',

            // Save Permalink Settings
            savePermalinkSettings: '//input[@id="submit"]',
        },

        // Admin Create New User
        createNewUser: {
            // Admin Create New User
            clickUserMenuAdmin: '//div[text()="Users"]',
            // Add New User
            clickAddNewUserAdmin: '//a[@class="page-title-action"]',

            // Enter Username
            newUserName: '//input[@id="user_login"]',
            // Enter Email
            newUserEmail: '//input[@id="email"]',
            // Enter First Name
            newUserFirstName: '//input[@id="first_name"]',
            // Enter Last Name
            newUserLastName: '//input[@id="last_name"]',
            // Enter Password
            newUserPassword: '//input[@id="pass1"]',
            // Allow weak Password        
            newUserWeakPasswordAllow: '//input[@class="pw-checkbox"]',
            // Select Role
            newUserSelectRole: '//select[@id="role"]',
            newUserSelectRoleCustomer: '//option[@value="customer"]',
            // Create User
            newUserSubmit: '//input[@type="submit"]',
        },
    },

    /*********************************/
    /******* Logout Selectors ********/
    /*********************************/

    logout: {
        /* Admin is doing a Basic Logout and Validating the logout success */
        basicLogout: {
            logoutHoverUsername: '//a[@class="ab-item" and contains(text(), "Howdy, ")]',
            logoutButton: '//a[@class="ab-item" and contains(text(), "Log Out")]',

            // Validate LOGOUT
            logoutSuccess: '//div[@class="wpuf-message"]',
        },

        validateBasicLogout: {
            // Empty section
        },
    },

    /*********************************************/
    /********** @Post_Forms Selectors ***********/
    /*********************************************/

    postForms: {
        /* Locators creating Navigating Post Forms Page */
        navigatePage_PF: {
            // WPUF > Pages > Navigation
            checkAddButton_PF: '#new-wpuf-post-form',
            postFormsPageFormsTitleCheck_PF: '(//a[@class="row-title"])[1]',

            // New_Created_NAME_Checker
            newPostCreatedName_PF: '(//a[@class="row-title"])[1]',
        },

        /* Locators creating Post > Blank Form */
        createBlankForm_PF: {
            // Create_New_Post_Form
            clickpostFormsMenuOption: '//a[contains(text(), "Post Forms")]',

            // Add Form
            clickPostAddForm: '//a[@class="page-title-action add-form" and contains(text(), "Add Form")]',

            // Start > Blank Form
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',

            // Enter_NAME
            editNewFormName: '//input[@name="post_title"]',
            enterNewFormName: '//input[@name="post_title"]',  // TODO: Catch with Child
            confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::i[1]',
        },

        createPreset_PF: {
            // Start > Preset Form
            clickPresetForm: '//a[@title="Post Form" and contains(text(), "Create Form")]',

            // Enter_NAME
            editNewFormName: '//input[@name="post_title"]',
        },

        /* Locators for All Fields Options + Save */
        /********************* PostFields *********************/
        addPostFields_PF: {
            // Post_Fields
            postTitleBlock: '//p[normalize-space(text())="Post Title"]',
            postContentBlock: '//p[normalize-space(text())="Post Content"]',
            postExcerptBlock: '//p[normalize-space(text())="Post Excerpt"]',
            featuredImageBlock: '//p[normalize-space(text())="Featured Image"]',
        },

        validatePostFields_PF: {      // TODO: Inconsistent with Blank form
            validatePostTitle: '//label[@for="post_title"]/../..//div[@class="wpuf-fields"]',
            validatePostContent: '//label[@for="post_content"]/../..//div[@class="wpuf-fields"]',
            validateExcerpt: '//label[@for="post_excerpt"]/../..//div[@class="wpuf-fields"]',
            validateFeaturedImage: '//label[@for="featured_image"]/../..//div[@class="wpuf-fields"]',
        },

        /********************* Taxonomies *********************/
        addTaxonomies_PF: {
            // Taxonomies
            categoryBlock: '//p[normalize-space(text())="Category"]',
            tagsBlock: '//p[normalize-space(text())="Tags"]',
        },

        validateTaxonomies_PF: {
            validateCategory: '//label[@for="category"]/../..//div[@class="wpuf-fields"]',
            validateTags: '//label[@for="tags"]/../..//div[@class="wpuf-fields"]',
        },

        validateTaxonomiesPreset_PF: {
            validateCategory: '//label[@for="category"]/../..//div[@class="wpuf-fields"]',
            validateTags: '//label[@for="tags"]/../..//div[@class="wpuf-fields"]',
        },

        /***********************************************/
        /********** @CommonFields Selectors ***********/
        /***********************************************/

        // Custom - Field options for Forms
        addCustomFields_Common: {
            // Custom _Fields
            customFieldsText: '//p[normalize-space(text())="Text"]',
            customFieldsTextarea: '//p[normalize-space(text())="Textarea"]',
            customFieldsDropdown: '//p[normalize-space(text())="Dropdown"]',
            customFieldsMultiSelect: '//p[normalize-space(text())="Multi Select"]',
            customFieldsRadio: '//p[normalize-space(text())="Radio"]',
            customFieldsCheckBox: '//p[normalize-space(text())="Checkbox"]',
            customFieldsWebsiteUrl: '//p[normalize-space(text())="Website URL"]',
            customFieldsEmailAddress: '//p[normalize-space(text())="Email Address"]',
            customFieldsHiddenField: '//p[normalize-space(text())="Hidden Field"]',
            customFieldsImageUpload: '//p[normalize-space(text())="Image Upload"]',

            // From___PRO
            customFieldsRepeatField: '//p[normalize-space(text())="Repeat Field"]',
            customFieldsDateTime: '//p[normalize-space(text())="Date / Time"]',
            customFieldsTimeField: '//p[normalize-space(text())="Time Field"]',
            customFieldsFileUpload: '//p[normalize-space(text())="File Upload"]',
            customFieldsCountryList: '//p[normalize-space(text())="Country List"]',
            customFieldsNumericField: '//p[normalize-space(text())="Numeric Field"]',
            customFieldsPhoneField: '//p[normalize-space(text())="Phone Field"]',
            customFieldsAddressField: '//p[normalize-space(text())="Address Field"]',
            customFieldsGoogleMaps: '//p[normalize-space(text())="Google Map"]',
            customFieldsStepStart: '//p[normalize-space(text())="Step Start"]',
            customFieldsEmbed: '//p[normalize-space(text())="Embed"]',

            // prompt1
            prompt1PopUpModalClose: "//div[@class='swal2-loader']/following-sibling::button[1]",
            // prompt2
            prompt2PopUpModalOk: "//button[@class='swal2-deny swal2-styled']/following-sibling::button[1]",
            // Pro Check Pop Up
            checkProPopUp: '//button[text()="Get the Pro version"]',
            checkProPopUpCloseButton: '//button[@aria-label="Close this dialog"]',
            // Pro Text Alert in Settings
            proTextAlertInSettings: '(//h3[@class="wpuf-pro-text-alert"])[1]'
        },

        // Validate Custom Fields
        validateCustomFields_Common: {
            validateText: '//label[@for="text"]/../..//div[@class="wpuf-fields"]',
            validateTextarea: '//label[@for="textarea"]/../..//div[@class="wpuf-fields"]',
            validateDropdown: '//label[@for="dropdown"]/../..//div[@class="wpuf-fields"]',
            validateMultiSelect: '//label[@for="multi_select"]/../..//div[@class="wpuf-fields"]',
            validateRadio: '//label[@for="radio"]/../..//div[@class="wpuf-fields"]',
            validateCheckBox: '//label[@for="checkbox"]/../..//div[@class="wpuf-fields"]',
            validateWebsiteUrl: '//label[@for="website_url"]/../..//div[@class="wpuf-fields"]',
            validateEmailAddress: '//label[@for="email_address"]/../..//div[@class="wpuf-fields"]',
            validateHiddenField: '(//li[contains(@class,"field-items wpuf-group/hidden-fields")]//div)[1]',
            validateImageUpload: '//label[@for="image_upload"]/../..//div[@class="wpuf-fields"]',
            //div[contains(@class,'wpuf-flex wpuf-rounded-t-lg')]//div[1]
            // From___PRO
            validateRepeatField: '//label[@for="repeat_field"]/../..//div[@class="wpuf-fields"]',
            validateDateTime: '//label[@for="date___time"]/../..//div[@class="wpuf-fields"]',  // TODO: Date - Time has large underscore
            validateTimeField: '//label[@for="time_field"]/../..//div[@class="wpuf-fields"]',
            validateFileUpload: '//label[@for="file_upload"]/../..//div[@class="wpuf-fields"]',
            validateCountryList: '//label[@for="country_list"]/../..//div[@class="wpuf-fields"]',
            validateNumericField: '//label[@for="numeric_field"]/../..//div[@class="wpuf-fields"]',
            validatePhoneField: '//label[@for="phone_field"]/../..//div[@class="wpuf-fields"]',
            validateAddressField: '//label[@for="addr_field_label"]/../..//div[@class="wpuf-fields"]',
            // validateGoogleMaps: '',           // TODO: Setup required
            validateStepStart: '//div[@class="step-start-indicator"]/../..',
            validateEmbed: '//label[@for="embed"]/../..//div[@class="wpuf-fields"]',
        },

        // Others - Field options for Forms
        addOthers_Common: {
            // Others
            othersColumns: '//p[normalize-space(text())="Columns"]',
            othersSectionBreak: '//p[normalize-space(text())="Section Break"]',
            othersCustomHTML: '//p[normalize-space(text())="Custom HTML"]',
            othersQrCode: '//p[normalize-space(text())="QR Code"]',
            othersReCaptcha: '//p[normalize-space(text())="reCaptcha"]',
            othersCloudflareTurnstile: '//p[normalize-space(text())="Cloudflare Turnstile"]',

            // From___PRO
            othersShortCode: '//p[normalize-space(text())="Shortcode"]',
            othersActionHook: '//p[normalize-space(text())="Action Hook"]',
            othersTermsAndConditions: '//p[normalize-space(text())="Terms & Conditions"]',
            othersRatings: '//p[normalize-space(text())="Action Hook"]',
            othersReallySimpleCaptcha: '//p[normalize-space(text())="Really Simple Captcha"]',
            othersMathCaptcha: '//p[normalize-space(text())="Math Captcha"]',
        },

        // Form Settings
        formSettings: {
            // Post Settings
            // Click Form Edit Settings
            clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2]',

            // Click Form Editor
            clickFormEditor: '//a[contains(text(),"Form Editor")]',
            // Add Multi-Step-Check
            checkMultiStepOption: '//input[@name="wpuf_settings[enable_multistep]"]',

            // Submission Restriction
            clickSubmissionRestriction: '//a[contains(text(),"Submission Restriction")]',
            // set post permission
            setPostPermission: '//select[@name="wpuf_settings[post_permission]"]/following-sibling::div[1]',
            // Check Guest Enable
            enableGuestPost: '//div[@data-value="guest_post"]',

            // Save Form Settings
            saveFormSettings: '//button[normalize-space(text())="Save"]',
            // Validate Form Settings Saved
            validateFormSettingsSaved: '//div[normalize-space(text())="Saved form data"]',
        },

        validateOthers_Common: {
            validateColumns: '//li[contains(@class,"form-field-column_field")]',
            validateSectionBreak: '//li[contains(@class,"section_break")]',
            validateCustomHTML: '//div[text()="HTML Section"]/..//div[@class="wpuf-fields"]',

            // validateReCaptcha: '',            // TODO: Setup required
            validateShortcode: '//label[@for="shortcode"]/../..//div[@class="wpuf-fields"]',
            validateActionHook: '//div[text()="Action Hook"]/../..//div[@class="wpuf-fields"]',
            validateTermsAndConditions: '//div[@class="wpuf-toc-container"]/..//div[@class="wpuf-fields clearfix has-toc-checkbox"]',
            validateRatings: '//label[@for="ratings"]/../..//div[@class="wpuf-fields"]',
            // validateReallySimpletCaptcha: '',  // TODO: Setup required
            validateMathCaptcha: '//label[@for="math_captcha"]/../..//div[@class="wpuf-fields"]',
        },

        // Save Forms
        saveForm_Common: {
            // Validate Name
            formNameReCheck: '//input[@name="post_title"]',
            // FINISH
            saveFormButton: '//button[normalize-space(text())="Save"]',
        },

        /*****************************************************/
        /********** @PostForm FrontEnd Selectors ************/
        /************* + FrontEnd Validation ***************/
        /*****************************************************/
        postFormsFrontendCreate: {
            // Post Forms Create
            // Account
            // Submit Post
            submitPostSideMenu: '//a[contains(text(),"Submit Post")]',

            // Start Form Submission
            // Post Tile
            postTitleFormsFE: '//input[@name="post_title"]',
            // Category
            categorySelectionFormsFE: '//select[@data-type="select"]',
            // Post Description
            postDescriptionFormsFE1: '//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]',
            postDescriptionFormsFE2: '//body[@id="tinymce"]',
            // Featured Photo
            featuredPhotoFormsFE: '(//input[@type="file"])[2]',
            // Excerpt
            postExcerptFormsFE: '//textarea[@name="post_excerpt"]',
            // Tags
            postTagsFormsFE: '//input[@name="tags"]',
            // Create Post
            submitPostFormsFE: '//input[@value="Create Post"]',
            // Validate Post Submitted
            validatePostSubmitted: '//h1[@class="wp-block-post-title"]',
        },

        postFormsFrontendValidate: {
            // Accounts - Top Menu
            clickAccountsTopMenu: '//a[contains(text(), "Account")]',
            // Post
            clickPostsSideMenu: '//a[contains(text(),"Posts")]',
            // Validate Title of Post Created
            validatePostSubmittedFE: '(//td[@data-label="Title: "])[1]'
        }
    },

    /****************************************************/
    /********** @RegistrationForms Selectors ***********/
    /****************************************************/

    registrationForms: {
        // Navigate Registration Forms Page
        navigatePage_RF: {
            // WPUF > Pages > Navigation
            checkAddButton_RF: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck_RF: '(//a[@class="row-title"])[1]',

            // New_Created_NAME_Checker
            newPostCreatedName_RF: '(//a[@class="row-title"])[1]',
        },

        // Create Registration Forms - Blank
        createBlankForm_RF: {
            // Create_New_Post_Form
            clickRegistrationFormMenuOption: '//a[contains(text(), "Registration Forms")]',

            // Profile_Name
            validateRegistrationFormPageName: '//h2[contains(text(), "Profile Forms")]',

            // Start
            clickRegistraionAddForm: '//a[@id="new-wpuf-profile-form" and contains(text(), "Add Form")]',
            hoverBlankForm: '.blank-form',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',

            // Enter_NAME
            editNewFormName: '//span[text()="Sample Registration Form"]',
            enterNewFormName: '//header[@class="clearfix"]//span//input',  // TODO: Catch with Child
            confirmNewNameTickButton: '//header[@class="clearfix"]//button',
        },

        // Create Registration Forms - Add Profile Fields
        addProfileFields_RF: {
            // Profile Fields
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

        /******************************************************/
        /********** @Registration Setup Selectors ************/
        /******************************************************/

        // Registration forms page - only WPUF-Lite activated
        validateRegistrationFormsProFeatureLite: {
            // Check Pro Features Header
            checkProFeaturesText: '//h2[text()="Unlock PRO Features"]',
            // Check Setup
            checkUpgradeToProOption: '//a[contains(text(),"Upgrade to PRO")]',
        },

        // Create Registration page using Shortcode
        createRegistrationPageUsingShortcodeLite: {
            // Validate Shortcode
            validateShortcode: '//code[text()="[wpuf-registration]"]',
            // Shortcode
            storeShortcode: '//code[text()="[wpuf-registration]"]',
            // Add New Page
            addNewPage: '//a[@class="page-title-action"]',
            // Close Pattern Modal
            closePatternModal: '//h1[normalize-space(text())="Choose a pattern"]/../../button[@aria-label="Close"]',
            // Close Welcome Modal
            closeWelcomeModal: '//button[@aria-label="Close"]',
            // Add Page Title
            addPageTitle: '//h1[@aria-label="Add title"]',
            // Block Add Button
            blockAddButton: '//button[@aria-label="Add block"]', 
            // Block Search box
            blockSearchBox: '//input[@placeholder="Search"]',
            // Block Add ShortCode Block
            addShortCodeBlock: '//span[text()="Shortcode"]',
            // Enter Registration Shortcode
            enterRegistrationShortcode: '//textarea[@aria-label="Shortcode text"]',

            // Click Publish Page
            clickPublishPage: '//button[text()="Publish"]',
            // Allow Permission
            allowShortcodePermission: '//button[text()="Proceed with Update"]',
            // Confirm Publish 
            confirmPublish: '//button[contains(@class,"components-button editor-post-publish-button")]',

            // Validation
            // Search Page
            pagesSearchBox: '//input[@type="search"]',
            // Search Page Submit
            pagesSearchBoxSubmit: '//input[@id="search-submit"]',
            // Validate Page Created
            validatePageCreated: '//a[@class="row-title"]',
        },

        /*********************************************************/
        /********** @Registration FrontEnd Selectors ************/
        /*********** + BackEnd/AdminEnd Validation *************/
        /*********************************************************/

        // Registration forms page - only WPUF-Lite activated
        completeUserRegistrationFormFrontend: {
            // Validate Registration page
            validateRegistrationPage: '//h1[text()="Registration Page"]',

            // Registration Form
            // First Name
            rfFirstName: '//input[@name="reg_fname"]',
            // Last Name
            rfLastName: '//input[@name="reg_lname"]',
            // Email
            rfEmail: '//input[@name="reg_email"]',
            // Username
            rfUserName: '//input[@id="wpuf-user_login"]',
            // Password
            rfPassword: '//input[@id="wpuf-user_pass1"]',
            // Confirm Password
            rfConfirmPassword: '//input[@id="wpuf-user_pass2"]',
            // Register button
            rfRegisterButton: '//input[@id="wp-submit"]',

            // Validate Registered
            // Logout button
            validateRegisteredLogoutButton: '//a[contains(text(),"Log out")]'
        },

        // Validate in Admin - Registered Form Submitted
        // Validate Registered User
        validateUserRegisteredAdminEnd: {
            // Go to Users List
            adminUsersList: '//div[text()="Users"]',
            // Search Username
            adminUsersSearchBox: '//input[@type="search"]',
            // Click Search
            adminUsersSearchButton: '//input[@id="search-submit"]',
            // Validate Email present
            validateUserCreated: '//td[@class="email column-email"]',
        },
    },

    /************************************************/
    /********** @Rest WordPress Site ***************/
    /********** @Plugin Required: WP Reset *********/
    /************************************************/

    resetWordpreseSite: {
        // Reset Input box
        wpResetInputBox: '//input[@name="wp_reset_confirm"]',
        // Submit Reset Button
        wpResetSubmitButton: '//a[@id="wp_reset_submit"]',
        // Confirm WordPress Reset
        wpResetConfirmWordpressReset: '//button[text()="Reset WordPress"]',
        // Validate Reset
        notRightNowButton: '//div[@class="wpuf-setup-content"]',
        // Reactivate Theme
        reActivateTheme: '//input[@id="reactivate-theme"]',
        // Reactivate Plugins
        reActivatePlugins: '//input[@id="reactivate-plugins"]',
        // Allow analytics
        allowAnalytics: '//a[normalize-space()="Allow"]',
    },
};