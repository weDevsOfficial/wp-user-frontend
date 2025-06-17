import {Urls, Users, PostForm, RegistrationForm, SubscriptionPack} from '../utils/testData';
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
            clickWPUFSidebar: '//div[normalize-space(text())="User Frontend"]',
            clickDokanSidebar: '//div[normalize-space(text())="Dokan"]',
            // Hover Settings Menu
            hoverSettings: '//div[text()="Settings"]',
            licenseTab: '//li[@id="toplevel_page_wp-user-frontend"]//ul//li[normalize-space()="License"]',
        },
    },

    /*******************************************/
    /******* Settings Setup Selectors *********/
    /*******************************************/

    settingsSetup: {
        // Plugin Status Check
        pluginStatusCheck: {
            // Plugin Activate/Deactivate
            availableWPUFPluginLite: '//tr[@data-slug="wp-user-frontend"]//strong[contains(text(),"WP User Frontend")]',
            availableWPUFPluginPro: '//tr[@data-slug="wp-user-frontend-pro"]//strong[contains(text(),"WP User Frontend Pro")]',
            availableDokanLite: '//tr[@data-slug="dokan-lite"]//strong[contains(text(),"Dokan")]',
            clickPluginsSidebar: '//li[@id="menu-plugins"]',
            clickWPUFPluginLite: '//a[@id="activate-wp-user-frontend"]',
            clickWPUFPluginPro: '//a[@id="activate-wp-user-frontend-pro"]',
            clickWCvendors: '//a[@id="activate-wc-vendors"]',
            clickDokanLite: '//a[@id="activate-dokan-lite"]',
            clickWPUFPluginDeactivate: '//a[@id="deactivate-wp-user-frontend"]',
            clickWPUFPluginProDeactivate: '//a[@id="deactivate-wp-user-frontend-pro"]',
            clickDokanLiteDeactivate: '//a[@id="deactivate-dokan-lite"]',
            clickAllow1: '(//a[normalize-space()="Allow"])[1]',
            clickAllow: '//a[normalize-space()="Allow"]',
            clickSkipSetup: '//a[normalize-space()="Skip setup"]',
            clickDoNotAllow: '//a[normalize-space()="Do not allow"]',
            clickSwitchCart: '//button[@id="wcv-switch-to-classic-cart-checkout"]',
            clickDismiss: '//a[normalize-space()="Dismiss"]',
            clickEDDnoticeCross: '//div[@id="edds-edd-stripe-core-notice"]//button[@type="button"]',
            clickPayPalCross: '//div[@id="wpuf-paypal-settings-notice"]//button[@type="button"]',
            clickRunUpdater: '//a[normalize-space()="Run the updater"]',
            clickActivateLicense: '//button[normalize-space()="Activate License"]',
            fillLicenseKey: '//div[@class="license-input-key"]//input[1]',
            submitLicenseKey: '//div[@class="license-input-key"]/following-sibling::button[1]',
            deactivateLicenseKey: '//div[@class="license-input-key"]/following-sibling::button[1]',
            activationRemaining: '//h3[normalize-space()="Activations Remaining"]',
            
        },

        // Plugin Visit
        pluginVisit: {
            // WPUF > Pages > Navigation
            // Sidebar
            // PostFormPage
            clickPostFormMenuOption: '//h3[normalize-space(text())="Post Forms"]',
            wpufPostFormCheckAddButton: ' //a[contains(text(),"Add New")]',
            wpufRegistrationFormCheckAddButton: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck: '//a[@class="row-title"][1]',
        },

        wpufPages: {
            wpufAccountPage: '//a[normalize-space()="Account"]//..//span[normalize-space()="WPUF Account Page"]',
            wpufDashboardPage: '//a[normalize-space()="Dashboard"]//..//span[normalize-space()="WPUF Dashboard Page"]',
            wpufEditPage: '//a[normalize-space()="Edit"]//..//span[normalize-space()="WPUF Post Edit Page"]',
            wpufSubscriptionPage: '//a[normalize-space()="Subscription"]//..//span[normalize-space()="WPUF Subscription Page"]',
            wpufLoginPage: '//a[normalize-space()="Login"]//..//span[normalize-space()="WPUF Login Page"]',
            orderReceivedPage: '//strong//a[normalize-space()="Order Received"]',
            wpufRegistrationPage: '//a[normalize-space()="Registration"]//..//span[normalize-space()="WPUF Registration Page"]',
            thankYouPage: '//strong//a[normalize-space()="Thank You"]',
            paymentPage: '//strong//a[normalize-space()="Payment"]',
            clickNextPage: '(//span[text()="Next page"]/following-sibling::span)[2]',
        },

        wpufPagesFE:{
            accountPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Account"]',
            dashboardPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Dashboard"]',
            editPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Edit"]',
            subscriptionPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Subscription"]',
            loginPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Login"]',
            orderReceivedPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Order Received"]',
            thankYouPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Thank You"]',
            paymentPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Payment"]',
        },

        accountPageTabs:{
            dashboardTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Dashboard"]',
            viewDashboardPara: '//p[contains(text(),"From your account dashboard you can view your dash")]',
            postsTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Posts"]',
            postsTableHeader: '//thead//tr[1]//th[text()="Title"]',
            editProfileTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Edit Profile"]',
            updateProfileButton: '//button[normalize-space(text())="Update Profile"]',
            subscriptionTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Subscription"]',
            noSubscriptionPara: '//p[normalize-space()="You have not subscribed to any package yet."]',
            billingAddessTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Billing Address"]',
            updateBillingAddressButton: '//input[@value="Update Billing Address"]',
            submitPostTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Submit Post"]',
            submitPostButton: '//input[@value="wpuf_submit_post"]/following-sibling::input[1]',
            invoiceTab: '//nav[@class="wpuf-dashboard-navigation"]//li//a[normalize-space()="Invoices"]',
            invoiceTableHeader: '//thead[contains(.,"Invoice")]'
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

        // Allow User Registration
        allowRegistration: {
            // Settings > General
            clickAnyoneRegister: '//input[@id="users_can_register"]',
            // Save Settings
            saveSettings: '//input[@id="submit"]',
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

        categories: {
            clickCategoryMenu: '//a[normalize-space()="Categories"]',
            addNewCategory: '//input[@id="tag-name"]',
            addCategorySlug: '//input[@id="tag-slug"]',
            submitCategory: '//input[@id="submit"]',
            validateCategory: (categoryName: string) => `//tbody[@id="the-list"]//tr//td//strong//a[normalize-space()="${categoryName}"]`,
        },

        tags: {
            clickTagsMenu: '//a[normalize-space()="Tags"]',
            addNewTag: '//input[@id="tag-name"]',
            addTagSlug: '//input[@id="tag-slug"]',
            submitTag: '//input[@id="submit"]',
            validateTag: (tagName: string) => `//tbody[@id="the-list"]//tr//td//strong//a[normalize-space()="${tagName}"]`,
        },

        keys: {
            // Keys
            // SETTINGS > GENERAL
            clickSettingsTabGeneral: '//a[@id="wpuf_general-tab"]',
            fillGoogleMapAPIKey:'(//input[@id="wpuf_general[gmap_api_key]"])[1]',
            fillReCaptchaSiteKey: '(//input[@id="wpuf_general[recaptcha_public]"])[1]',
            fillReCaptchaSecretKey: '(//input[@id="wpuf_general[recaptcha_private]"])[1]',
            enableCloudflareTurnstile: '//label[@for="wpuf-wpuf_general[enable_turnstile]"]//span[1]',
            fillCloudflareTurnstileSiteKey: '(//input[@id="wpuf_general[turnstile_site_key]"])[1]',
            fillCloudflareTurnstileSecretKey: '(//input[@id="wpuf_general[turnstile_secret_key]"])[1]',
            settingsTabGeneralSave: '//div[@id="wpuf_general"]//form[@method="post"]//div//input[@id="submit"]',
            clickLoginOrRegistration: '//a[normalize-space(text())="Login / Registration"]',
            enableCloudflareTurnstileLogin: '//label[@for="wpuf-wpuf_profile[login_form_turnstile]"]//span[1]'
        },
        payment: {
            clickPaymentTab: '//a[@id="wpuf_payment-tab"]',
            clickPaymentGatewayBank: '//input[@id="wpuf-wpuf_payment[active_gateways][bank]"]',
            clickPaymentGatewayPaypal: '//input[@id="wpuf-wpuf_payment[active_gateways][paypal]"]',
            clickPaymentGatewayStripe: '//input[@id="wpuf-wpuf_payment[active_gateways][stripe]"]',
            settingsTabPaymentSave: '//div[@id="wpuf_payment"]//form[@method="post"]//div//input[@id="submit"]',
        },

        pluginInstall: {
            clickPluginInstall: (pluginName: string, pluginSlug: string) => `//a[contains(text(),"${pluginName}")]/../../..//a[@data-slug="${pluginSlug}"]`,
            validatePluginInstalled: (pluginName: string) => `//strong[normalize-space()='${pluginName}']`,
            validatePluginActived: (pluginSlug: string) => `//a[@id='deactivate-${pluginSlug}']`,
        }
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
            logoutSuccess: '//p[normalize-space(text())="You are now logged out."]',
            signOutButton: '//a[normalize-space()="Sign out"]',

        },
    },

    /*********************************************/
    /********** @Post_Forms Selectors ***********/
    /*********************************************/

    postForms: {
        /* Locators creating Navigating Post Forms Page */
        navigatePage_PF: {
            // WPUF > Pages > Navigation
            checkAddButton_PF: '//a[contains(text(),"Add New")]',
            postFormsPageFormsTitleCheck_PF: '(//input[@type="checkbox"]/following-sibling::span)[1]',

            // New_Created_NAME_Checker
            newPostCreatedName_PF: '(//input[@type="checkbox"]/following-sibling::span)[1]',
        },

        /* Locators creating Post > Blank Form */
        createBlankForm_PF: {
            // Create_New_Post_Form
            clickpostFormsMenuOption: '//a[contains(text(), "Post Forms")]',

            // Add Form
            clickPostAddForm: ' (//a[contains(@class,"new-wpuf-form")])[1]',

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
            prompt1PopUpModalClose: '//div[@class="swal2-loader"]/following-sibling::button[1]',
            // prompt2
            prompt2PopUpModalOk: '//button[@class="swal2-deny swal2-styled"]/following-sibling::button[1]',
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
            // From___PRO
            validateRepeatField: '//label[@for="repeat_field"]/../..//div[@class="wpuf-fields"]',
            validateDateTime: '//label[@for="date___time"]/../..//div[@class="wpuf-fields"]',  // TODO: Date - Time has large underscore
            validateTimeField: '//label[@for="time_field"]/../..//div[@class="wpuf-fields"]',
            validateFileUpload: '//label[@for="file_upload"]/../..//div[@class="wpuf-fields"]',
            validateCountryList: '//label[@for="country_list"]/../..//div[@class="wpuf-fields"]',
            validateNumericField: '//label[@for="numeric_field"]/../..//div[@class="wpuf-fields"]',
            validatePhoneField: '//label[@for="phone_field"]/../..//div[@class="wpuf-fields"]',
            validateAddressField: '//label[@for="address_field"]',
            validateGoogleMaps: '//div[@class="wpuf-form-google-map"]',
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
            othersRatings: '//p[normalize-space(text())="Ratings"]',
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
            validateActionHook: '//span[normalize-space()="YOUR_CUSTOM_HOOK_NAME"]',
            validateTermsAndConditions: '//div[contains(@class,"wpuf-toc-container wpuf-fields")]',
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
            submitPostSideMenu: '//li[@class="wpuf-menu-item submit-post"]//a[1]',

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
            validatePostSubmitted: (postFormTitle:string)=> `//h1[normalize-space(text())='${postFormTitle}']`,
        },

        postFormsFrontendValidate: {
            // Accounts - Top Menu
            clickAccountsTopMenu: '//a[contains(text(), "Account")]',
            // Post
            clickPostsSideMenu: '//li[@class="wpuf-menu-item post"]//a[1]',
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
            checkAddButton_RF: '//a[contains(text(),"Add New")]',
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

            // Check Core Updates
            checkUpdateToLatest: '//a[normalize-space()="Update to Latest"]',
        },

        // Create Registration page using Shortcode
        createRegistrationPageUsingShortcodeLite: {
            // Validate Shortcode
            validateShortcode: '//code[text()="[wpuf-registration]"]',
            // Shortcode
            storeShortcode: '(//div[@class="wpuf-mb-4 wpuf-flex"]//code)[1]',
            // Add New Page
            addNewPage: '//a[@class="page-title-action"]',
            // Close Pattern Modal
            closePatternModal: '(//div[@class="components-modal__header"]//button)[1]',
            // Close Welcome Modal
            closeWelcomeModal: '(//div[@class="components-modal__header"]//button)[1]',
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
            rfFirstName: '//input[@id="wpuf-user_fname"]',
            // Last Name
            rfLastName: '//input[@id="wpuf-user_lname"]',
            // Email
            rfEmail: '//input[@type="email"]',
            // Username
            rfUserName: '//input[@id="wpuf-user_login"]',
            // Password
            rfPassword: '(//input[@type="password"])[1]',
            // Confirm Password
            rfConfirmPassword: '(//input[@type="password"])[2]',
            // Register button
            rfRegisterButton: '//input[@value="Register"]',

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

    postFormSettings: {
        // Navigation and Basic Elements
        formNameInput: '//input[@name="post_title"]',
        addNewButton: '//a[contains(text(),"Add New")]',
        saveButton: '//button[normalize-space(text())="Save"]',
        postTypeColumn: '//tbody/tr[1]/td[2]',
        postSubmissionStatusColumn: '//tbody/tr[1]/td[3]',
        clickFormEditor: '//a[contains(text(),"Form Editor")]',
        clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2]',
        clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',
        confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::i[1]',
        clickForm: (formName: string) => `//span[normalize-space()="${formName}"]`,
        postTypePage: (type: string) => `//a[normalize-space()="${type}"]`,
        postCategory: (category: string) => `//a[normalize-space()="${category}"]`,
        submitPostButton: '//input[@name="submit"]',
        updatePostButton: '//input[@name="submit"]',
        submitPostButtonText:(value: string) => `//input[@value="${value}"]`,
        checkPostTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        checkSuccessMessage: '//div[@class="wpuf-success"]',
        checkPageTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        postTitleColumn: '//tbody//tr[1]//td[1]',
        postStatusColumn: '//tbody//tr[1]//td[2]//span[1]',
        saveDraftButton: '//a[normalize-space(text())="Save Draft"]',
        draftSavedAlert: '//span[@class="wpuf-draft-saved"]',
        multiStepProgressbar: '//div[normalize-space(text())="Step Start (100%)"]',
        multiStepByStep: '//li[normalize-space(text())="Step Start"]',
        removeStepStart: '//div[@class="step-start-indicator"]/../../../..//span[4]',
        confirmDelete: '//button[normalize-space()="Yes, delete it"]',
        editPostButton: '(//td[@data-label="Options: "]//a)[1]',
        quickEditButtonContainer: '//tbody[@id="the-list"]//tr[1]',
        quickEditButton: '(//button[@class="button-link editinline"])[1]',
        statusDropdown: '(//select[@name="_status"])[1]',
        updateStatus: '(//input[@id="_inline_edit"]/following-sibling::button)[1]',
        wpufInfo: '//div[@class="wpuf-info"]',
        paymentPageTitle: '//h1[normalize-space(text())="Payment"]',
        validatePayPerPostCost: '//span[@id="wpuf_pay_page_cost"]',
        checkBankButton: '//li[@class="wpuf-gateway-bank"]//input[1]',
        proceedPaymentButton: '//input[@value="Proceed"]',
        afterPaymentPageTitle: (successPage: string) => `//h1[normalize-space(text())="${successPage}"]`,
        transactionTableRow: '//tbody//tr[1]',
        acceptPayment: '//a[normalize-space()="Accept"]',
        successMessage: '//div[@class="wpuf-success"]',
        clickPost: (postTitle: string) => `//a[normalize-space(text())="${postTitle}"]`,
        

        // Post Settings Section
        postSettingsSection: {
            afterPostSettingsHeader: '//p[contains(text(),"After Post Settings")]',
            beforePostSettingsHeader: '//p[contains(text(),"Before Post Settings")]',
            
            // Post Type Selectize Dropdown
            postTypeContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postTypeDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[1]',
            postTypeOption: (type: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${type}"]`,

            defaultCategoryContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[2]',
            defaultCategoryDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[2]',
            defaultCategoryOption: (type: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${type}")]`,

            postRedirectionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[3]',
            postRedirectionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[3]',
            postRedirectionOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,

            postRedirectionMessage: '//textarea[@id="message"]',

            postRedirectionPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[4]',
            postRedirectionPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[4]',
            postRedirectionPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,

            postRedirectionUrlInput: '//input[@id="url"]',

            postSubmissionStatusContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[5]',
            postSubmissionStatusDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[5]',
            postSubmissionStatusOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,

            savingAsDraftToggleOn: '//input[@id="draft_post"]/following-sibling::span[1]',

            submitButtonContainer: '(//label[normalize-space(text())="Submit Post Button Text"]/following::input)[1]',

            // Multi-Step Settings
            enableMultiStepToggle: '//input[@id="enable_multistep"]/following-sibling::span[1]',
            enableMultiStepCheckbox: '//input[@id="enable_multistep"]',

            progressbarTypeContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[6]',
            progressbarTypeDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[6]',
            progressbarTypeOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,


            // After Post Settings
            postUpdateStatusContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[7]',
            postUpdateStatusDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[7]',
            postUpdateStatusOption: (status: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${status}"]`,
            
            postUpdateMessageContainer: '//textarea[@id="update_message"]',
            
            lockUserEditingAfterInput: '//input[@id="lock_edit_post"]',
            
            updatePostButtonTextInput: '//input[@id="update_text"]',

            // Successful Redirection Settings (Update Post scenarios)
            updatePostRedirectionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[8]',
            updatePostRedirectionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[8]',
            updatePostRedirectionOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,
            
            successfulRedirectionMessage: '//textarea[@id="update_message"]',
            
            updatePostRedirectionPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[9]',
            updatePostRedirectionPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[9]',
            updatePostRedirectionPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,
            
            updatePostRedirectionUrlInput: '//input[@id="edit_url"]',

            paymentSettingsTab: '//li[@data-settings="payment_settings"]',
            paymentEnableToggle: '//input[@id="payment_options"]/following-sibling::span[1]',
            paymentOptionsContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[12]',
            paymentOptionsDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[12]',
            payPerPostOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,
            payPerPostCostContainer: '//input[@id="pay_per_post_cost"]',
            paymentSuccessPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[13]',
            paymentSuccessPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[13]',
            paymentSuccessPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,


        },

        // Validation Messages
        messages: {
            formSaved: '//div[normalize-space(text())="Saved form data"]',
        },

        // Notification Settings Section
        notificationSettingsSection: {
            notificationSettingsHeader: '//p[contains(text(),"New Post Notification")]',
            updatedPostNotificationSettingsHeader: '//p[contains(text(),"Update Post Notification")]',
            
            // New Post Notification
            newPostNotificationToggle: '//input[@name="wpuf_settings[notification][new]"]/following-sibling::span[1]',
            newPostNotificationTo: '//input[@name="wpuf_settings[notification][new_to]"]',
            newPostNotificationSubject: '//input[@name="wpuf_settings[notification][new_subject]"]',
            newPostNotificationBody: '//textarea[@name="wpuf_settings[notification][new_body]"]',
            
            // Update Post Notification (PRO)
            updatePostNotificationToggle: '//input[@name="wpuf_settings[notification_edit]"]/following-sibling::span[1]',
            updatePostNotificationTo: '//input[@name="wpuf_settings[notification_edit_to]"]',
            updatePostNotificationSubject: '//input[@name="wpuf_settings[notification_edit_subject]"]',
            updatePostNotificationBody: '//textarea[@name="wpuf_settings[notification_edit_body]"]',

            templateTagPointer: (tag: string, point: string) => `(//span[@data-clipboard-text="${tag}"])[${point}]`,
            tagClickTooltip: '//span[@data-original-title="Copied!"]',
            sentEmailAddress: '//tbody/tr[1]/td[3]/div[1]',
            sentEmailSubject: '//tbody/tr[1]/td[4]/div[1]',
            viewEmailContent: '//tbody/tr[1]/td[3]/div[1]',
            previewEmailContentBody: '(//div[@class="wml-body-wrapper"])[1]',


        },

        // Advanced Settings Section
        advancedSettingsSection: {
            advancedSettingsHeader: '//h2[normalize-space()="Advanced"]',
            
            // Comment Status
            commentStatusContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[15]',
            commentStatusDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[15]',
            commentStatusOption: (status: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${status}"]`,
            
            commentBox: '//textarea[@id="comment"]',
            postCommentButton: '//input[@id="submit"]',
            validateComment: '//ol//li[1]//div[@class="wp-block-comment-content"]',

            limitFormEntriesToggle: '//input[@id="limit_entries"]/following-sibling::span[1]',
            limitNumberInput: '//input[@id="limit_number"]',
            limitMessage: '//textarea[@id="limit_message"]',


        },

        // Post Expiration Settings Section
        postExpirationSettingsSection: {
            postExpirationSettingsHeader: '//h2[normalize-space()="Post Expiration"]',
            postExpirationToggle: '//input[@id="enable_post_expiration"]/following-sibling::span[1]',
            postExpirationTime: '//input[@id="expiration_time_value"]',
            enablePostExpirationMessage: '//input[@id="enable_mail_after_expired"]',
            postExpirationMessage: '//textarea[@id="post_expiration_message"]',
        },

        // Navigation tabs
        notificationSettingsTab: '//li[@data-settings="notification_settings"]',
        paymentSettingsTab: '//li[@data-settings="payment_settings"]',
        displaySettingsTab: '//li[@data-settings="display_settings"]',
        advancedSettingsTab: '//li[@data-settings="advanced"]',
        postExpirationSettingsTab: '//li[normalize-space()="Post Expiration"]',
    },

    registrationFormSettings: {
        
    },
};