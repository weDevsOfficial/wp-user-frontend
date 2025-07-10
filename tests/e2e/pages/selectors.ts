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
            settingsTabProfileSave: '//div[@id="wpuf_profile"]//form[@method="post"]//div//input[@id="submit"]',

            settingsTabAccount: '//a[@id="wpuf_my_account-tab"]',
            settingsTabAccountPage: '//select[@name="wpuf_my_account[account_page]"]',
            settingsTabAccountSave: '//div[@id="wpuf_my_account"]//form[@method="post"]//div//input[@id="submit"]',

            settingsTabAccountActiveTab: '//select[@name="wpuf_my_account[account_page_active_tab]"]',

            settingsTabEditProfile: '//select[@name="wpuf_my_account[edit_profile_form]"]',

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
            postFormsPageFormsTitleCheck_PF: (formName:string)=> `//span[normalize-space()="${formName}"]`,
            postFormShortCode: (formName:string)=> `//span[normalize-space()="${formName}"]//..//..//code`,
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

        addPostFieldButton: '//a[normalize-space()="Add Fields"]',
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
            customFieldsGoogleMapsEdit: '//div[@class="wpuf-form-google-map"]//..//..//..//..//..//span[normalize-space(text())="Edit"]',
            googleMapsSearchbox: '//label[normalize-space()="Show address search box"]',
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
            reCaptchaEdit: '//label[@for="recaptcha"]//..//..//..//span[normalize-space()="Edit"]',
            invisibleReCaptcha: '//input[@value="invisible_recaptcha"]',
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
            enterGuestDetails: '//input[@id="guest_details"]',
            //Enter Name Label
            enterNameLabel: '//input[@id="name_label"]',
            //Enter Email Label
            enterEmailLabel: '//input[@id="email_label"]',

            // Save Form Settings
            saveFormSettings: '//button[normalize-space(text())="Save"]',
            // Validate Form Settings Saved
            validateFormSettingsSaved: '//div[normalize-space(text())="Saved form data"]',
        },

        validateOthers_Common: {
            validateColumns: '//li[contains(@class,"form-field-column_field")]',
            validateSectionBreak: '//li[contains(@class,"section_break")]',
            validateCustomHTML: '//div[text()="HTML Section"]/..//div[@class="wpuf-fields"]',
            validateReCaptcha: '//label[@for="recaptcha"]',

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
            categorySelectionFormsFE: '//select[@name="category"]',
            // Post Description
            postDescriptionFormsFE1: '//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]',
            postDescriptionFormsFE2: '//body[@id="tinymce"]',
            // Featured Photo
            featuredPhotoFormsFE: '//li[@data-label="Featured Image"]//input[@type="file"]',
            uploads: (upload:string)=> `(//div[@class='attachment-name']//img)[${upload}]`,
            // Excerpt
            postExcerptFormsFE: '//textarea[@name="post_excerpt"]',
            // Tags
            postTagsFormsFE: '//input[@name="tags"]',
            // Text
            postTextFormsFE: '//input[@name="text"]',
            // Textarea
            postTextareaFormsFE: '//textarea[@name="textarea"]',
            // Dropdown
            postDropdownFormsFE: '//select[@name="dropdown"]',
            // Multi Select
            postMultiSelectFormsFE: '//select[@name="multi_select[]"]',
            // Radio
            postRadioFormsFE: '//input[@name="radio"]',
            // Checkbox
            postCheckboxFormsFE: '//input[@name="checkbox[]"]',
            // Website URL
            postWebsiteUrlFormsFE: '//input[@name="website_url"]',
            // Email Address
            postEmailAddressFormsFE: '//input[@name="email_address"]',
            // Image Upload
            postImageUploadFormsFE: '//li[@data-label="Image Upload"]//input[@type="file"]',
            // Repeat Field
            postRepeatFieldFormsFE: '//input[@name="repeat_field[]"]',
            // Date / Time
            postDateTimeFormsFE: {
                dateTimeSelect: '//input[@name="date___time"]',
                selectYear: '//select[@data-handler="selectYear"]',
                selectMonth: '//select[@data-handler="selectMonth"]',
                selectDay: '//a[@data-date="20"]',
            },
            // Time Field
            postTimeFieldFormsFE: '//select[@name="time_field"]',
            // File Upload
            postFileUploadFormsFE: '//li[@data-label="File Upload"]//input[@type="file"]',
            // Country List
            postCountryListFormsFE: '//select[@name="country_list"]',
            // Numeric Field
            postNumericFieldFormsFE: '//input[@name="numeric_field"]',
            // Phone Field
            postPhoneFieldFormsFE: {
                countryContainer: '(//div[@class="iti__flag-container"]//div)[1]',
                countrySelect: '//li[@data-country-code="bd"]',
                phoneNumber: '//input[@name="phone_field"]',
            },
            // Address Field
            postAddressFieldFormsFE: {
                addressLine1: '//input[@name="address_field[street_address]"]',
                addressLine2: '//input[@name="address_field[street_address2]"]',
                city: '//input[@name="address_field[city_name]"]',
                state: '//select[@name="address_field[state]"]',
                country: '//select[@name="address_field[country_select]"]',
                zip: '//input[@name="address_field[zip]"]',
            },
            // Google Maps
            postGoogleMapsFormsFE: '//input[@placeholder="Search address"]',
            // Embed
            postEmbedFormsFE: '//input[@name="embed"]',
            // Terms and Conditions
            postTermsAndConditionsFormsFE: '//input[@name="terms_and_conditions"]',
            // Ratings
            postRatingsFormsFE: '//select[@name="ratings"]',
            // Math Captcha
            postMathCaptchaFormsFE: {
                operand1: '//span[@id="operand_one"]',
                operand2: '//span[@id="operand_two"]',
                operator: '//span[@id="operator"]',
                mathCaptcha: '(//label[contains(.,"Math Captcha *")]/following::input)[1]',
            },
            // Guest name
            guestName: '//input[@name="guest_name"]',
            // Guest Email
            guestEmail: '//input[@name="guest_email"]',
            // Create Post
            submitPostFormsFE: '//input[@name="submit"]',
            // Validate Post Submitted
            validatePostSubmitted: (postFormTitle:string)=> `//h1[normalize-space(text())='${postFormTitle}']`,
        },
        postFormData: {
            title: (title:string)=> `//h1[normalize-space(text())='${title}']`,
            description: (description:string)=> `//div[contains(@class,"entry-content")]//p[normalize-space(text())="${description}"]`,
            featuredImage: '//figure[@class="wp-block-post-featured-image"]',
            category: '//div[contains(@class,"taxonomy-category")]',
            tags: '//div[contains(@class,"taxonomy-post_tag")]//a',
            text: '//li[contains(@class,"wpuf-field-data-text_field")]',
            textarea: '//li[contains(@class,"wpuf-field-data-textarea_field")]',
            dropdown: '//li[contains(@class,"wpuf-field-data-dropdown_field")]',
            multiSelect: '//li[contains(@class,"wpuf-field-data-multiple_select")]',
            radio: '//li[contains(@class,"wpuf-field-data-radio_field")]',
            checkbox: '//li[contains(@class,"wpuf-field-data-checkbox_field")]',
            websiteUrl: '//li[contains(@class,"wpuf-field-data-website_url")]',
            emailAddress: '//li[contains(@class,"wpuf-field-data-email_address")]',
            imageUpload: '//label[text()="Image Upload:"]/following-sibling::a',
            repeatField: (repeatField:string)=> `//li[contains(.,"Repeat Field: ${repeatField}")]`,
            dateTime: (dateTime:string)=> `//li[contains(.,"Date / Time: ${dateTime}")]`,
            timeField: (timeField:string)=> `//li[contains(.,"Time Field: ${timeField}")]`,
            fileUpload: '//label[text()="File Upload:"]/following-sibling::a',
            countryList: (countryList:string)=> `//li[contains(.,"Country List: ${countryList}")]`,
            numericField: '//li[contains(@class,"wpuf-field-data-numeric_text_field")]',
            phoneField: (phoneNumber:string)=> `//li[contains(.,"Phone Field: ${phoneNumber}")]`,
            addressLine1: (addressLine1:string)=> `//li[normalize-space(text())="${addressLine1}"]`,
            addressLine2: (addressLine2:string)=> `//li[normalize-space(text())="${addressLine2}"]`,
            city: (city:string)=> `//li[normalize-space(text())="${city}"]`,
            zip: (zip:string)=> `//li[normalize-space(text())="${zip}"]`,
            country: (country:string)=> `//li[normalize-space(text())="${country}"][2]`,
            state: (state:string)=> `//li[normalize-space(text())="${state}"]`,
            embed: '//div[@class="wpuf-embed-preview"]//a',
            ratings: '//li[contains(@class,"wpuf-field-data-ratings")]',
        },
        createPageWithShortcode: {
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
            // Enter Shortcode
            enterShortcode: '//textarea[@aria-label="Shortcode text"]',
            // Click Publish Page
            clickPublishPage: '//button[text()="Publish"]',
            // Confirm Publish
            confirmPublish: '//button[contains(@class,"components-button editor-post-publish-button")]',
            // Validate Page Created
            validatePageCreated: '//div[@class="post-publish-panel__postpublish-buttons"]//a[normalize-space(text())="View Page"]',
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
            clickRegistrationFormMenuOption: '//li//a[contains(text(), "Registration Forms")]',

            // Profile_Name
            validateRegistrationFormPageName: '//h2[contains(text(), "Profile Forms")]',

            // Start
            clickRegistraionAddForm: '//a[contains(@class,"new-wpuf-form")]',
            //hoverBlankForm: '(//a[contains(@class,"new-wpuf-form")])[1]',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',

            // Enter_NAME
            editNewFormName: '//input[@name="post_title"]',
            enterNewFormName: '//input[@name="post_title"]',  // TODO: Catch with Child
            confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::i[1]',
        },

        // Create Registration Forms - Add Profile Fields
        addProfileFields_RF: {
            // Profile Fields
            profileFieldUsername: '//p[normalize-space()="Username"]',
            profileFieldFirstName: '//p[normalize-space()="First Name"]',
            profileFieldLastName: '//p[normalize-space()="Last Name"]',
            profileFieldDisplayName: '//p[normalize-space()="Display Name"]',
            profileFieldNickName: '//p[normalize-space()="Nickname"]',
            profileFieldEmail: '//p[normalize-space(text())="E-mail"]',
            profileFieldWebsiteUrl: '//p[normalize-space()="Website"]',
            profileFielBioInfo: '//p[normalize-space()="Biographical Info"]',
            profileFieldPassword: '//p[normalize-space(text())="Password"]',
            profileFieldAvatar: '//p[normalize-space()="Avatar"]',
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
            //closePatternModal: '(//div[@class="components-modal__header"]//button)[1]',
            closePatternModal: '(//div[@data-wp-component="Spacer"]/following-sibling::button)[1]',
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
            // Validate User Role
            validateUserRole: '//td[@class="role column-role"]',
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
        addNewButton: '//a[contains(@class,"new-wpuf-form")]',
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
        wpufMessage: '//div[@class="wpuf-message"]',
        clickPost: (postTitle: string) => `//a[normalize-space(text())="${postTitle}"]`,

        showFormTitle: (formName: string) => `//h2[normalize-space()="${formName}"]`,
        showFormDescription: '//div[@class="wpuf-form-description"]',
        

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

            postPermissionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[10]',
            postPermissionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[10]',
            postPermissionOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,

            roleSelectionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[11]',
            roleSelectionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[11]',
            roleSelectionOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,

            paymentSettingsTab: '//li[@data-settings="payment_settings"]',
            paymentEnableToggle: '//input[@id="payment_options"]/following-sibling::span[1]',
            paymentOptionsContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[12]',
            paymentOptionsDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[12]',
            payPerPostOption: (value: string) => `(//div[contains(@class,"selectize-dropdown-content")])//div[@data-value="${value}"]`,
            
            payPerPostCostContainer: '//input[@id="pay_per_post_cost"]',
            paymentSuccessPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[13]',
            paymentSuccessPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[13]',
            paymentSuccessPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,


            formTitleToggle: '//input[@id="show_form_title"]/following-sibling::span[1]',
            formDescriptionBox: '//textarea[@id="form_description"]',

            unAuthMsg: '//textarea[@id="message_restrict"]'
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

    regFormSettings: {

        clickForm: (formName: string) => `//span[normalize-space()="${formName}"]`,
        saveButton: '//button[normalize-space(text())="Save"]',
        formSaved: '//div[normalize-space(text())="Saved form data"]',
        clickFormEditor: '//a[contains(text(),"Form Editor")]',
        clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2]',
        inputEmail: '//input[@name="user_email"]',
        inputPassword: '//input[@name="pass1"]',
        inputConfirmPassword: '//input[@name="pass2"]',
        submitRegisterButton: '//input[@name="submit"]',
        submitRegisterButtonText:(value: string) => `//input[@value="${value}"]`,
        checkPostTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        checkSuccessMessage: '//div[@class="wpuf-success"]',
        saveDraftButton: '//a[normalize-space(text())="Save Draft"]',
        draftSavedAlert: '//span[@class="wpuf-draft-saved"]',
        confirmDelete: '//button[normalize-space()="Yes, delete it"]',
        editPostButton: '(//td[@data-label="Options: "]//a)[1]',
        quickEditButtonContainer: '//tbody[@id="the-list"]//tr[1]',
        quickEditButton: '(//button[@class="button-link editinline"])[1]',
        wpufInfo: '//div[@class="wpuf-info"]',
        successMessage: '//div[@class="wpuf-success"]',
        wpufMessage: '//div[@class="wpuf-message"]',
        wpLoginErrorMessage: '//div[@id="login_error"]',

        regSettingsSection: {
            regSettingsHeader: '//h2[normalize-space()="General"]',

            userRoleContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            userRoleDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[1]',
            userRoleOption: (role: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${role}"]`,

            approvalToggle: '//input[@id="user_status"]/following-sibling::span[1]',
            approveUser: '//a[normalize-space()="Approve"]',
        },

        afterSignUpSettingsSection: {
            afterSignUpSettingsHeader: '//label[contains(text(),"After Registration Successful Redirection")]',
            
            // After Registration Successful Redirection (looking for actual form field structure)
            afterRegistrationRedirectionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[2]',
            afterRegistrationRedirectionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[2]',
            afterRegistrationRedirectionOption: (value: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"]`,
            
            afterRegistrationRedirectionPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[3]',
            afterRegistrationRedirectionPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[3]',
            afterRegistrationRedirectionPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,
            
            afterRegistrationRedirectionUrlInput: '//input[@id="registration_url"]',
            
            // Registration Success Message  
            registrationSuccessMessageInput: '//textarea[@id="message"]',
            
            // Submit Button Text
            submitButtonTextInput: '//input[@id="submit_text"]',
            
            // After Profile Update Successful Redirection
            afterProfileUpdateRedirectionContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[4]',
            afterProfileUpdateRedirectionDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[4]',
            afterProfileUpdateRedirectionOption: (value: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"]`,
            
            afterProfileUpdateRedirectionPageContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[5]',
            afterProfileUpdateRedirectionPageDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[5]',
            afterProfileUpdateRedirectionPageOption: (text: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")]`,
            
            afterProfileUpdateRedirectionUrlInput: '//input[@id="profile_url"]',
            
            // Update Profile Message
            updateProfileMessageInput: '//textarea[@id="update_message"]',
            
            // Update Button Text
            updateButtonTextInput: '//input[@id="update_text"]',
        },

        // Frontend validation selectors
        frontendValidation: {
            registrationForm: '//form[@id="wpuf-registration-form"]',
            registrationSubmitButton: '//input[@type="submit"]',
            registrationSuccessMessage: '//div[@class="wpuf-success"]',
            afterRegPageTitle: (pageTitle: string) => `//h1[normalize-space(text())='${pageTitle}']`,
            
            editProfileForm: '//form[@id="wpuf-edit-profile-form"]',
            firstNameField: '//input[@name="first_name"]',
            displayNameField: '//input[@name="display_name"]',
            emailField: '//input[@name="user_email"]',
            currentPasswordField: '//input[@name="current_password"]',
            newPasswordField: '//input[@name="pass1"]',
            confirmPasswordField: '//input[@name="pass2"]',
            updateProfileSubmitButton: '//input[@name="submit"]',
            updateProfileSuccessMessage: '//div[@class="wpuf-success"]',
        },

        // Notification Settings Section  
        notificationSettingsSection: {
            notificationSettingsTab: '//span[normalize-space()="Notification Settings"]',
            notificationSettingsHeader: '//h2[normalize-space()="Notification Settings"]',
            
            // User Notification
            userNotificationHeader: '//p[normalize-space()="User Notification"]',
            enableUserNotificationToggle: '//input[@id="user_notification"]/following-sibling::span[1]',
            
            // User Notification Type
            emailVerificationRadio: '//input[@id="email_verification"]',
            welcomeEmailRadio: '//input[@id="welcome_email"]',
            
            // Email Verification Settings
            confirmationEmailSubjectInput: '//input[@id="verification_subject"]',
            confirmationEmailBodyTextarea: '(//div[contains(@class,"mce-edit-area mce-container")]//iframe[1])[1]',
            
            // Welcome Email Settings
            welcomeEmailSubjectInput: '//input[@id="welcome_email_subject"]',
            welcomeEmailBodyTextarea: '(//div[contains(@class,"mce-edit-area mce-container")]//iframe[1])[2]',

            textareaBody: '//body[@id="tinymce"]',

            templateTagPointer: (tag: string, point: string) => `(//span[@data-clipboard-text="${tag}"])[${point}]`,
            tagClickTooltip: '//span[@data-original-title="Copied!"]',
            
            // Admin Notification
            adminNotificationHeader: '//h3[normalize-space()="Admin Notification"]',
            enableAdminNotificationToggle: '//input[@id="admin_notification"]/following-sibling::span[1]',
            adminNotificationSubjectInput: '//input[@id="admin_email_subject"]',
            adminNotificationMessageTextarea: '//textarea[@id="admin_email_body"]',
        },

        // WP Mail Log validation selectors
        wpMailLogValidation: {
            wpMailLogPage: '//h2[normalize-space()="WP Mail Log"]',
            
            // First email row selectors
            sentEmailAddress: '(//tbody/tr[2]/td[3]/div[1])[1]',
            sentEmailSubject: '(//tbody/tr[2]/td[4]/div[1])[1]',
            viewEmailContent: '(//tbody/tr[2]/td[3]/div[1])[1]',
            previewEmailContentBody: '(//div[@class="wml-body-wrapper"])[1]',
            grabActivationLink: '//a[normalize-space()="Activation Link"]',

            modalCloseButton: '//button[@class="el-button el-button--danger"]',

            sentLatestEmailAddress: '(//tbody/tr[1]/td[3]/div[1])[1]',
            sentLatestEmailSubject: '(//tbody/tr[1]/td[4]/div[1])[1]',
            viewLatestEmailContent: '(//tbody/tr[1]/td[3]/div[1])[1]',
            
            // Search and filter
            emailSearchInput: '//input[@id="post-search-input"]',
            emailSearchButton: '//input[@id="search-submit"]',
        },

        // Multi-Step Settings Section
        advancedSettingsSection: {
            advancedSettingsHeader: '//h2[normalize-space()="Advanced Settings"]',
            advancedSettingsTab: '//span[normalize-space()="Advanced Settings"]',
            multiStepSettingsHeader: '//p[normalize-space()="Multistep Form"]',
            enableMultiStepToggle: '//input[@id="enable_multistep"]/following-sibling::span[1]',
            multiStepTypeContainer: '(//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[8]',
            multiStepTypeDropdown: '(//div[contains(@class,"selectize-dropdown-content")])[8]',
            multiStepTypeOption: (value: string) => `//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"]`,
            multiStepProgressbar: '//div[normalize-space(text())="Step Start (100%)"]',
            multiStepByStep: '//li[normalize-space(text())="Step Start"]',
        },

        // Custom Fields Section
        addCustomFields_Common: {
            customFieldsStepStart: '//p[normalize-space(text())="Step Start"]',
            customFieldsText: '//p[normalize-space(text())="Text"]',
            customFieldsUrl: '//p[normalize-space(text())="Website URL"]',
        },
    },
};