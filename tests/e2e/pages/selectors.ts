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
            // React [wpuf-login] submits with a button; theme-override forms keep the input.
            loginButton2: '(//button[@type="submit" and @name="wp-submit"] | //input[@type="submit"])[1]',
        },

        // Validate Basic Login
        validateBasicLogin: {
            // Validate LOGIN
            logingSuccessDashboard: '//div[text()="Dashboard"]',
        },

        // Basic Navigation
        basicNavigation: {
            // Sidebar
            clickWPUFSidebar: '//div[normalize-space(text())="User Frontend"]',
            clickDokanSidebar: '//div[normalize-space(text())="Dokan"]',
            licenseTab: '//li[@id="toplevel_page_wp-user-frontend"]//ul//li[normalize-space()="License"]',
        },
        // WPUF frontend [wpuf-login] shortcode: the React login app (src/frontend/login, root .wpuf-login-root;
        // the classic templates/login-form.php,
        // lost-pass-form.php, logged-in.php — Lite Simple_Login)
        frontendLogin: {
            loginForm: '.wpuf-login-root form#loginform',
            usernameField: '.wpuf-login-root input#wpuf-user_login',
            passwordField: '.wpuf-login-root input#wpuf-user_pass',
            rememberMeCheckbox: '.wpuf-login-root input#wpuf-rememberme',
            // The app renders a <button id="wp-submit"> (a classic template an <input>).
            submitButton: '.wpuf-login-root #wp-submit',
            fieldError: '.wpuf-login-root .wpuf-error-msg',
            lostPasswordLink: '.wpuf-login-root .wpuf-lost-password a',
            // The server's errors and messages sit inside the form card (.wpuf-login-notices).
            errorNotice: '.wpuf-login-notices .wpuf-error',
            messageNotice: '.wpuf-login-notices .wpuf-message',
            loggedInView: 'div.wpuf-user-loggedin',
            lostPasswordForm: '.wpuf-login-root form#lostpasswordform',
            lostPasswordUserField: '.wpuf-login-root form#lostpasswordform input#wpuf-user_login',
            lostPasswordSubmit: '.wpuf-login-root form#lostpasswordform #wp-submit',
            resetPasswordForm: '.wpuf-login-root form#resetpasswordform',
            resetPass1: '.wpuf-login-root input#wpuf-pass1',
            resetPass2: '.wpuf-login-root input#wpuf-pass2',
            resetSubmit: '.wpuf-login-root form#resetpasswordform #wp-submit',
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
            clickEDD: '//a[@id="activate-easy-digital-downloads"]',
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
            // React License page (Pro #/license): status pill, key input, Activate button.
            licenseStatus: '[data-license-status]',
            fillLicenseKey: '#wpuf-license-key',
            submitLicenseKey: '//button[normalize-space()="Activate"]',
            activationRemaining: '[data-license-status="active"]',
            
        },

        // Plugin Visit
        pluginVisit: {
            // WPUF > Pages > Navigation
            // Sidebar
            // PostFormPage
            clickPostFormMenuOption: '//h3[normalize-space(text())="Post Forms"]',
            clickRegFormMenuOption: '//h3[normalize-space()="Registration Forms"]',
            wpufPostFormCheckAddButton: '(//*[self::button or self::a][contains(@class,"new-wpuf-form")])[1]',
            wpufRegFormCheckAddButton: '(//*[self::button or self::a][contains(@class,"new-wpuf-form")])[1]',
            noFormMsg: '//*[self::h2 or self::h3 or self::p][normalize-space()="No Post Forms Created Yet"]',
            // The React list prints the form name as a link in the first data cell.
            formTitleCheck: (formName: string) => `(//td//a[normalize-space()='${formName}'] | //span[normalize-space(text())='${formName}'])[1]`,
            clickRegFormListPage: '//a[normalize-space()="Registration Forms"]'

        },

        wpufPages: {
            wpufAccountPage: '//a[normalize-space()="Account"]//..//span[normalize-space()="WPUF Account Page"]',
            wpufDashboardPage: '//a[normalize-space()="Dashboard"]//..//span[normalize-space()="WPUF Dashboard Page"]',
            wpufEditPage: '//a[normalize-space()="Edit"]//..//span[normalize-space()="WPUF Post Edit Page"]',
            wpufSubscriptionPage: '//a[normalize-space()="Subscription"]//..//span[normalize-space()="WPUF Subscription Page"]',
            wpufLoginPage: '//a[normalize-space()="Login"]//..//span[normalize-space()="WPUF Login Page"]',
            orderReceivedPage: '//strong//a[normalize-space()="Order Received"]',
            thankYouPage: '//strong//a[normalize-space()="Thank You"]',
            paymentPage: '//strong//a[normalize-space()="Payment"]',
            clickNextPage: '(//span[text()="Next page"]/following-sibling::span)[2]',
        },

        wpufPagesFE: {
            accountPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Account"]',
            dashboardPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Dashboard"]',
            editPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Edit"]',
            subscriptionPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Subscription"]',
            loginPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Login"]',
            orderReceivedPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Order Received"]',
            thankYouPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Thank You"]',
            paymentPageFE: '//ul[@class="wp-block-page-list"]//li//a[normalize-space()="Payment"]',
        },

        accountPageTabs: {
            // React account: rail links (the Dashboard stat cards repeat some labels).
            dashboardTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Dashboard"]',
            viewDashboardPara: '//p[contains(text(),"From your account dashboard you can view your dash")]',
            postsTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Posts"]',
            postsTableHeader: '//thead//tr[1]//th[text()="Title"]',
            editProfileTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Edit Profile"]',
            updateProfileButton: '//input[@value="Update Profile"]',
            subscriptionTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Subscription"]',
            noSubscriptionPara: '//p[normalize-space()="You have not subscribed to any package yet."]',
            billingAddessTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Billing Address"]',
            updateBillingAddressButton: '//button[@id="wpuf-account-update-billing_address"]',
            submitPostTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Submit Post"]',
            submitPostButton: '//div[contains(@class,"wpuf-account-content")]//input[contains(@class,"wpuf-submit-button")]',
            invoiceTab: '//nav[contains(@class,"wpuf-account-nav")]//a[normalize-space()="Invoices"]',
            invoiceTableHeader: '//h2[normalize-space()="My Invoices"]'
        },

        // WPUF Setup
        wpufSetup: {
            // WPUF Setup 
            // Skip Setup
            validateWPUFSetupPage: '//h1[text()="Welcome to the world of WPUF!"]',
            // Continue Setup
            clickWPUFSetupLetsGo: '//a[contains(@class,"button-primary button")]',
            clickWPUFSetupContinue: '//input[@type="submit"]',
            clickWPUFSetupEnd: '//a[contains(@class,"button button-primary")]',
        },

        // WPUF Settings Page
        wpufSettingsPage: {
            // Main Settings Tab
            // The settings menu row: the page link, or its admin app route.
            settingsTab: '//a[@href="admin.php?page=wpuf-settings" or @href="admin.php?page=wp-user-frontend#/settings"]',

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
            // Custom Structure fillup box
            fillCustomStructure: '//input[@id="permalink_structure"]',
            // Click Permalink-Postname
            clickCustomStructurePostName: '//button[@data-added="postname added to permalink structure"]',
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
            // Select Role
            newUserSelectRole: '//select[@id="role"]',
            // Create User
            newUserSubmit: '//input[@type="submit"]',
            validateCreationMsg: '//p[contains(., "New user created. Edit user")]',
            validateUserInList: '//a[normalize-space()="Testuser0001"]',
        },

        categories: {
            clickCategoryMenu: '//a[normalize-space()="Categories"]',
            addNewCategory: '//input[@id="tag-name"]',
            submitCategory: '//input[@id="submit"]',
            // No //td segment on purpose: WordPress renders the term name in the list's primary
            // column as a <th scope="row">, not a <td>. //td//strong//a never matched, so the
            // add succeeded but validation hung until the test timeout. Do not re-add //td.
            validateCategory: (categoryName: string) => `//tbody[@id="the-list"]//tr//strong//a[normalize-space()="${categoryName}"]`,
        },

        tags: {
            clickTagsMenu: '//a[normalize-space()="Tags"]',
            addNewTag: '//input[@id="tag-name"]',
            submitTag: '//input[@id="submit"]',
            // No //td segment on purpose: WordPress renders the term name in the list's primary
            // column as a <th scope="row">, not a <td>. //td//strong//a never matched, so the
            // add succeeded but validation hung until the test timeout. Do not re-add //td.
            validateTag: (tagName: string) => `//tbody[@id="the-list"]//tr//strong//a[normalize-space()="${tagName}"]`,
        },

        keys: {
            // Keys
            // SETTINGS > GENERAL
            clickSettingsTabGeneral: '//a[@id="wpuf_general-tab"]',
            fillGoogleMapAPIKey: '(//input[@id="wpuf_general[gmap_api_key]"])[1]',
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
            clickPaymentGatewayBank: '//div[@data-gateway="bank"]',
            clickPaymentGatewayPaypal: '//div[@data-gateway="paypal"]',
            clickPaymentGatewayStripe: '//div[@data-gateway="stripe"]',
            enablePaymentGatewayBank: '//label[@for="wpuf-wpuf_payment[active_gateways][bank]"]',
            enablePaymentGatewayPaypal: '//label[@for="wpuf-wpuf_payment[active_gateways][paypal]"]',
            enablePaymentGatewayStripe: '//label[@for="wpuf-wpuf_payment[active_gateways][stripe]"]',
            /* 
            enablePaymentGatewayBank: '//input[@data-gateway="bank"]/following-sibling::span[1]',
            enablePaymentGatewayPaypal: '//input[@data-gateway="paypal"]/following-sibling::span[1]',
            enablePaymentGatewayStripe: '//input[@data-gateway="stripe"]/following-sibling::span[1]',
            */
            fillStripePublishableKey: '(//*[self::label or self::div or self::span][normalize-space(text())="Stripe Publishable Key"]/following::input)[1]',
            fillStripeSecretKey: '(//label[normalize-space(text())="Stripe Secret Key"]/following::input)[1]',
            fillStripeSigningKey: '(//label[normalize-space(text())="Stripe Signing Secret"]/following::input)[1]',
            fillPaypalEmail: '(//label[normalize-space(text())="PayPal Email"]/following::input)[1]',
            fillPaypalClientId: '(//label[normalize-space(text())="PayPal Client ID"]/following::input)[1]',
            fillPaypalClientSecret: '(//label[normalize-space(text())="PayPal Client Secret"]/following::input)[1]',
            fillPaypalWebhookId: '(//label[normalize-space(text())="PayPal Webhook ID"]/following::input)[1]',
            fillPaypalApiUsername: '(//label[normalize-space(text())="PayPal API username"]/following::input)[1]',
            fillPaypalApiPassword: '(//label[normalize-space(text())="PayPal API password"]/following::input)[1]',
            fillPaypalApiSignature: '(//label[normalize-space(text())="PayPal API signature"]/following::input)[1]',
            selectPaypalTest: '//label[normalize-space()="Test Mode (Sandbox)"]',
            settingsTabPaymentSave: '//div[@id="wpuf_payment"]//form[@method="post"]//div//input[@id="submit"]',
        },

        modules: {
            // React Modules page (Pro #/modules): header buttons, one card per module
            // (`li[data-module="<module file>"]`) with a plugin-ui switch.
            clickActivateAll: '//header//button[normalize-space()="Activate All"]',
            clickDeactivateAll: '//header//button[normalize-space()="Deactivate All"]',
            confirmDeactivateAll: '//*[@role="alertdialog"]//button[normalize-space()="Deactivate All"]',
            checkModule: (moduleName: string) => `//li[@data-module='${moduleName}']//*[@role='switch']`,
        },

        AI: {
            clickAITab: '//a[@id="wpuf_ai-tab"]',
            openAIButton: '//span[normalize-space()="OpenAI"]',
            anthropicButton: '//span[normalize-space()="Anthropic"]',
            googleButton: '//span[normalize-space()="Google"]',
            inputAPIKey: '//input[@id="wpuf_ai_api_key_field"]',
            settingsTabAISave: '//div[@id="wpuf_ai"]//form[@method="post"]//div//input[@id="submit"]',
        },

        // Settings-persistence assertion locators (detected via Playwright MCP
        // snapshot/evaluate). Target the real <input> by its bracketed id — WPUF
        // renders a hidden input + a visible checkbox that share the same name,
        // so a name-based selector would be ambiguous; the id (prefixed "wpuf-"
        // for checkboxes/radios) is unique.
        persistence: {
            // General tab — Turnstile enable checkbox
            turnstileEnableCheckbox: '//input[@id="wpuf-wpuf_general[enable_turnstile]"]',
            // Payments tab — master enable + gateway toggles + PayPal sandbox mode
            enablePaymentCheckbox: '//input[@id="wpuf-wpuf_payment[enable_payment]"]',
            gatewayBankCheckbox: '//input[@id="wpuf-wpuf_payment[active_gateways][bank]"]',
            gatewayStripeCheckbox: '//input[@id="wpuf-wpuf_payment[active_gateways][stripe]"]',
            gatewayPaypalCheckbox: '//input[@id="wpuf-wpuf_payment[active_gateways][paypal]"]',
            paypalSandboxCheckbox: '//input[@id="wpuf-wpuf_payment[sandbox_mode]"]',
            // AI tab — active provider radio, keyed by provider slug (openai|google|anthropic)
            aiProviderRadio: (provider: string) => `//input[@id="wpuf-wpuf_ai[ai_provider][${provider}]"]`,
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
            logoutButtonFE: '//span[normalize-space()="Logout"]',
            confirmLogoutFE: '//a[normalize-space()="log out"]',

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
            checkAddButton_PF: '(//*[self::a or self::button][contains(@class,"new-wpuf-form")])[1]',
            postFormsPageFormsTitleCheck_PF: (formName: string) => `(//td//a[normalize-space()="${formName}"] | //span[normalize-space()="${formName}"])[1]`,
            // React list: the form name is a link in its cell, the shortcode a <code> in the same row.
            postFormShortCode: (formName: string) => `(//tr[.//td//a[normalize-space()="${formName}"]]//code | //span[normalize-space()="${formName}"]//..//..//code)[1]`,
        },

        /* Locators creating Post > Blank Form */
        createBlankForm_PF: {
            // Create_New_Post_Form
            clickpostFormsMenuOption: '//a[contains(text(), "Post Forms")]',

            // Add Form
            clickPostAddForm: '(//*[self::a or self::button][contains(@class,"new-wpuf-form")])[1]',

            // Start > Blank Form
            clickBlankForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Blank Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Blank Form"])]',

            // Enter_NAME
            editNewFormName: '//input[@name="post_title"]',
            enterNewFormName: '//input[@name="post_title"]',  // TODO: Catch with Child
            confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::*[self::i or self::button][1]',
        },

        createPreset_PF: {
            // Start > Preset Form
            clickPresetForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Post Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Post Form"])]',
        },

        createProduct_PF: {
            // Start > Preset Form
            clickProductForm: '//a[contains(normalize-space(.), "Create Form") and (@title="WooCommerce Product" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="WooCommerce Product"])]',
        },

        createDownloads_PF: {
            // Start > Preset Form
            clickDownloadsForm: '//a[contains(normalize-space(.), "Create Form") and (@title="EDD Download" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="EDD Download"])]',
        },

        /* Locators for All Fields Options + Save */
        /********************* PostFields *********************/

        addPostFieldButton: '(//a[normalize-space()="Add Fields"] | //h2[normalize-space()="Add Fields"])[1]',
        addPostFields_PF: {
            // Post_Fields
            postTitleBlock: '//p[normalize-space(text())="Post Title"]',
            postContentBlock: '//p[normalize-space(text())="Post Content"]',
            postExcerptBlock: '//p[normalize-space(text())="Post Excerpt"]',
            featuredImageBlock: '//p[normalize-space(text())="Featured Image"]',
        },

        validatePostFields_PF: {      // TODO: Inconsistent with Blank form
            validatePostTitle: '//label[@for="post_title" or @for="wpuf-post_title"]/../..//div[@class="wpuf-fields"]',
            validatePostContent: '//label[@for="post_content" or @for="wpuf-post_content"]/../..//div[@class="wpuf-fields"]',
            validateExcerpt: '//label[@for="post_excerpt" or @for="wpuf-post_excerpt"]/../..//div[@class="wpuf-fields"]',
            validateFeaturedImage: '//label[@for="featured_image" or @for="wpuf-featured_image"]/../..//div[@class="wpuf-fields"]',
        },

        validateProductPostFields_PF: {
            validateProductTitle: '//label[@for="post_title" or @for="wpuf-post_title"]/../..//div[@class="wpuf-fields"]',
            validateProductCategory: '//label[@for="product_cat" or @for="wpuf-product_cat"]/../..//div[@class="wpuf-fields"]',
            validateProductDescription: '//label[@for="post_content" or @for="wpuf-post_content"]/../..//div[@class="wpuf-fields"]',
            validateProductShDescription: '//label[@for="post_excerpt" or @for="wpuf-post_excerpt"]/../..//div[@class="wpuf-fields"]',
            validateRegularPrice: '//label[@for="_regular_price" or @for="wpuf-_regular_price"]/../..//div[@class="wpuf-fields"]',
            validateSalePrice: '//label[@for="_sale_price" or @for="wpuf-_sale_price"]/../..//div[@class="wpuf-fields"]',
            validateProductImage: '//label[@for="featured_image" or @for="wpuf-featured_image"]/../..//div[@class="wpuf-fields"]',
            validateImageGallery: '//label[@for="_product_image" or @for="wpuf-_product_image"]/../..//div[@class="wpuf-fields"]',
            validateCatalogVisibility: '//label[@for="_visibility" or @for="wpuf-_visibility"]/../..//div[@class="wpuf-fields"]',
            validatepurchaseNote: '//label[@for="_purchase_note" or @for="wpuf-_purchase_note"]/../..//div[@class="wpuf-fields"]',
            validateProductReviews: '//label[@for="product_reviews" or @for="wpuf-product_reviews"]/../..//div[@class="wpuf-fields"]',
            validateDownloadableProduct: '//label[@for="_downloadable" or @for="wpuf-_downloadable"]/../..//div[@class="wpuf-fields"]',
            validateDownloadableFiles: '//label[@for="_woo_files" or @for="wpuf-_woo_files"]/../..//div[@class="wpuf-fields"]',
            validateProductBrand: '//label[@for="product_brand" or @for="wpuf-product_brand"]/../..//div[@class="wpuf-fields"]',
            validateProductType: '//label[@for="product_type" or @for="wpuf-product_type"]/../..//div[@class="wpuf-fields"]',
            validateProductVisibility: '//label[@for="product_visibility" or @for="wpuf-product_visibility"]/../..//div[@class="wpuf-fields"]',
            validateProductTag: '//label[@for="product_tag" or @for="wpuf-product_tag"]/../..//div[@class="wpuf-fields"]',
            validateProductShippingClass: '//label[@for="product_shipping_class" or @for="wpuf-product_shipping_class"]/../..//div[@class="wpuf-fields"]',
            validateProductAttribute: '//label[@for="pa_color" or @for="wpuf-pa_color"]/../..//div[@class="wpuf-fields"]',
        },

        validateDownloadsPostFields_PF: {
            validateDownloadsTitle: '//label[@for="post_title" or @for="wpuf-post_title"]/../..//div[@class="wpuf-fields"]',
            validateDownloadsCategory: '(//div[@class="wpuf-fields"]//select)[1]',
            validateDownloadsDescription: '//label[@for="post_content" or @for="wpuf-post_content"]/../..//div[@class="wpuf-fields"]',
            validateDownloadsShDescription: '//label[@for="post_excerpt" or @for="wpuf-post_excerpt"]/../..//div[@class="wpuf-fields"]',
            validateRegularPrice: '//input[@placeholder="Regular price of your download"]',
            validateDownloadsImage: '//a[normalize-space()="Select Image"]',
            validatepurchaseNote: '(//textarea[@default="default"] | //li[.//*[normalize-space(text())="Product Notes"]]//textarea)[1]',
            validateDownloadableFiles: '//a[normalize-space()="Select Files"]',
            validateProductTag: '(//div[@class="wpuf-fields"]//select)[2]',
        },

        /********************* Taxonomies *********************/
        addTaxonomies_PF: {
            // Taxonomies
            categoryBlock: '//p[normalize-space(text())="Category"]',
            tagsBlock: '//p[normalize-space(text())="Tags"]',
        },

        validateTaxonomies_PF: {
            validateCategory: '//label[@for="category" or @for="wpuf-category"]/../..//div[@class="wpuf-fields"]',
            validateTags: '//label[@for="tags" or @for="wpuf-tags"]/../..//div[@class="wpuf-fields"]',
        },

        validateTaxonomiesPreset_PF: {
            validateCategory: '//label[@for="category" or @for="wpuf-category"]/../..//div[@class="wpuf-fields"]',
            validateTags: '//label[@for="tags" or @for="wpuf-tags"]/../..//div[@class="wpuf-fields"]',
        },

        addProductTaxo_PF: {
            brandBlock: '//p[normalize-space()="Product_brand"]',
            typeblock: '//p[normalize-space()="Product_type"]',
            visibilityBlock: '//p[normalize-space()="Product_visibility"]',
            categoryBlock: '//p[normalize-space()="Product_cat"]',
            tagBlock: '//p[normalize-space()="Product_tag"]',
            shippingBlock: '//p[normalize-space()="Product_shipping_class"]',
            attributeBlock: '//p[normalize-space()="Pa_color"]'
        },

        addDownloadsTaxo_PF: {
            tagBlock: '//p[normalize-space()="Download_tag"]',
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
            customFieldsGoogleMapsEdit: '(//li[contains(@class,"form-field-google_map")]//span[normalize-space()="Edit"] | //div[@class="wpuf-form-google-map"]/ancestor::li[1]//span[normalize-space()="Edit"] | //div[@class="wpuf-form-google-map"]//..//..//..//..//..//span[normalize-space(text())="Edit"])[1]',
            googleMapsSearchbox: '//label[normalize-space()="Show address search box"]',
            customFieldsStepStart: '//p[normalize-space(text())="Step Start"]',
            customFieldsEmbed: '//p[normalize-space(text())="Embed"]',

            // prompt1
            // "Don't show again" of the custom field tooltip (develop: Swal confirm; React: the dialog's cancel slot).
            prompt1PopUpModalClose: '//div[@class="swal2-loader"]/following-sibling::button[1] | //*[@data-wpuf-dialog]//*[@data-slot="alert-dialog-cancel"]',
            // prompt2
            prompt2PopUpModalOk: '//button[@class="swal2-deny swal2-styled"]/following-sibling::button[1] | //*[@data-wpuf-dialog]//*[@data-slot="alert-dialog-action"]',
            // Pro Check Pop Up
            checkProPopUp: '//button[text()="Get the Pro version"]',
            checkProPopUpCloseButton: '//button[@aria-label="Close this dialog"]',
            // Pro Text Alert in Settings
            proTextAlertInSettings: '(//h3[@class="wpuf-pro-text-alert"])[1]'
        },

        // Validate Custom Fields
        validateCustomFields_Common: {
            validateText: '//label[@for="text" or @for="wpuf-text"]/../..//div[@class="wpuf-fields"]',
            validateTextarea: '//label[@for="textarea" or @for="wpuf-textarea"]/../..//div[@class="wpuf-fields"]',
            validateDropdown: '//label[@for="dropdown" or @for="wpuf-dropdown"]/../..//div[@class="wpuf-fields"]',
            validateMultiSelect: '//label[@for="multi_select" or @for="wpuf-multi_select"]/../..//div[@class="wpuf-fields"]',
            validateRadio: '//label[@for="radio" or @for="wpuf-radio"]/../..//div[@class="wpuf-fields"]',
            validateCheckBox: '//label[@for="checkbox" or @for="wpuf-checkbox"]/../..//div[@class="wpuf-fields"]',
            validateWebsiteUrl: '//label[@for="website_url" or @for="wpuf-website_url"]/../..//div[@class="wpuf-fields"]',
            validateEmailAddress: '//label[@for="email_address" or @for="wpuf-email_address"]/../..//div[@class="wpuf-fields"]',
            validateHiddenField: '(//li[contains(@class,"field-items") and (contains(@class,"group/hidden-fields") or contains(@class,"wpuf-group/hidden-fields"))]//div)[1]',
            validateImageUpload: '//label[@for="image_upload" or @for="wpuf-image_upload"]/../..//div[@class="wpuf-fields"]',
            // From___PRO
            validateRepeatField: '//label[@for="repeat_field" or @for="wpuf-repeat_field"]/../..//div[@class="wpuf-fields"]',
            validateDateTime: '//label[@for="date___time" or @for="wpuf-date___time"]/../..//div[@class="wpuf-fields"]',  // TODO: Date - Time has large underscore
            validateTimeField: '//label[@for="time_field" or @for="wpuf-time_field"]/../..//div[@class="wpuf-fields"]',
            validateFileUpload: '//label[@for="file_upload" or @for="wpuf-file_upload"]/../..//div[@class="wpuf-fields"]',
            validateCountryList: '//label[@for="country_list" or @for="wpuf-country_list"]/../..//div[@class="wpuf-fields"]',
            validateNumericField: '//label[@for="numeric_field" or @for="wpuf-numeric_field"]/../..//div[@class="wpuf-fields"]',
            validatePhoneField: '//label[@for="phone_field" or @for="wpuf-phone_field"]/../..//div[@class="wpuf-fields"]',
            validateAddressField: '//label[@for="address_field" or @for="wpuf-address_field"]',
            validateGoogleMaps: '//div[@class="wpuf-form-google-map"]',
            validateStepStart: '//div[@class="step-start-indicator"]/../..',
            validateEmbed: '//label[@for="embed" or @for="wpuf-embed"]/../..//div[@class="wpuf-fields"]',
        },

        // Others - Field options for Forms
        addOthers_Common: {
            // Others
            othersColumns: '//p[normalize-space(text())="Columns"]',
            othersSectionBreak: '//p[normalize-space(text())="Section Break"]',
            othersCustomHTML: '//p[normalize-space(text())="Custom HTML"]',
            othersReCaptcha: '//p[normalize-space(text())="reCaptcha"]',
            reCaptchaEdit: '(//label[@for="recaptcha" or @for="wpuf-recaptcha"]/ancestor::li[1]//span[normalize-space()="Edit"] | //label[@for="recaptcha" or @for="wpuf-recaptcha"]//..//..//..//span[normalize-space()="Edit"])[1]',
            invisibleReCaptcha: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="invisible_recaptcha"]] | //input[@value="invisible_recaptcha"] >> visible=true >> nth=0',
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
            clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab")][normalize-space()="Settings"] | (//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2])[1]',

            // Click Form Editor
            clickFormEditor: '//a[contains(text(),"Form Editor")]',
            // Add Multi-Step-Check
            checkMultiStepOption: '//input[@name="wpuf_settings[enable_multistep]"]',

            // Submission Restriction
            clickSubmissionRestriction: '//a[contains(text(),"Submission Restriction")]',
            // set post permission
            setPostPermission: '(//*[self::label or self::div or self::span][@for="post_permission"]/following::*[@role="combobox"][1] | //select[@name="wpuf_settings[post_permission]"]/following-sibling::div[1])[1]',
            // Check Guest Enable
            enableGuestPost: 'xpath=//*[@role="option"][@data-value="guest_post"] | //div[@data-value="guest_post"] >> visible=true >> nth=0',
            enterGuestDetails: 'xpath=//*[@id="guest_details"][@role="checkbox"] | //input[@id="guest_details"]/preceding-sibling::*[@role="checkbox"][1] | //input[@id="guest_details"] >> visible=true >> nth=0',
            //Enter Name Label
            enterNameLabel: '//input[@id="name_label"]',
            //Enter Email Label
            enterEmailLabel: '//input[@id="email_label"]',

            // Save Form Settings
            saveFormSettings: '//button[normalize-space(text())="Save"]',
            // Validate Form Settings Saved
            validateFormSettingsSaved: '//li[@data-sonner-toast]//div[@data-title][normalize-space()="Saved form data" or normalize-space()="Form data saved."]',
        },

        validateOthers_Common: {
            validateColumns: '//li[contains(@class,"form-field-column_field")]',
            validateSectionBreak: '//li[contains(@class,"section_break")]',
            validateCustomHTML: '(//div[text()="HTML Section"]/..//div[@class="wpuf-fields"] | //div[contains(@class,"wpuf-fields")][.//*[normalize-space(text())="HTML Section"]])[1]',
            validateReCaptcha: '//label[@for="recaptcha" or @for="wpuf-recaptcha"]',

            // validateReCaptcha: '',            // TODO: Setup required
            validateShortcode: '//label[@for="shortcode" or @for="wpuf-shortcode"]/../..//div[@class="wpuf-fields"]',
            validateActionHook: '//span[normalize-space()="YOUR_CUSTOM_HOOK_NAME"]',
            validateTermsAndConditions: '//div[contains(@class,"wpuf-toc-container wpuf-fields")]',
            validateRatings: '//label[@for="ratings" or @for="wpuf-ratings"]/../..//div[@class="wpuf-fields"]',
            // validateReallySimpletCaptcha: '',  // TODO: Setup required
            validateMathCaptcha: '//label[@for="math_captcha" or @for="wpuf-math_captcha"]/../..//div[@class="wpuf-fields"]',
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
            uploads: (upload: string) => `(//div[@class='attachment-name']//img)[${upload}]`,
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
                // jQuery UI (classic forms) or the React calendar: same data attributes.
                selectYear: '//*[@data-handler="selectYear"]',
                selectMonth: '//*[@data-handler="selectMonth"]',
                selectDay: '//*[@data-date="20"][self::a or self::button]',
                selectHour: '//*[@data-unit="hour"]',
                selectMinute: '//*[@data-unit="minute"]'
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
            // Rating Stars
            postRatingStarsFormsFE: '//a[@data-rating-value="5"]',
            // Math Captcha
            postMathCaptchaFormsFE: {
                // Classic ids, or the React field's `p.captcha-number` spans (a, operator, b).
                operand1: '//span[@id="operand_one"] | (//p[contains(@class,"captcha-number")]/span)[1]',
                operand2: '//span[@id="operand_two"] | (//p[contains(@class,"captcha-number")]/span)[3]',
                operator: '//span[@id="operator"] | (//p[contains(@class,"captcha-number")]/span)[2]',
                mathCaptcha: '(//label[contains(.,"Math Captcha *")]/following::input)[1]',
                // Error container the WPUF submit handler fills when the captcha is
                // unanswered/wrong (jQuery `.wpuf-captcha-error`). Used to prove enforcement.
                error: '//*[contains(@class,"wpuf-captcha-error")]',
            },
            // Guest name
            guestName: '//input[@name="guest_name"]',
            // Guest Email
            guestEmail: '//input[@name="guest_email"]',
            // Create Post
            submitPostFormsFE: '//input[@name="submit"]',
            // Validate Post Submitted
            validatePostSubmitted: (postFormTitle: string) => `//h1[normalize-space(text())='${postFormTitle}']`,
        },

        // Frontend dashboard post management — account "Posts" tab
        // (/account/?section=post). Locators detected via Playwright MCP. Rows
        // are scoped by post title so a user owning multiple posts doesn't trip
        // Playwright strict mode; the Options cell holds the Edit + Delete links.
        dashboardManage: {
            // React account Posts section: View / Edit / Delete are buttons on the row
            // (no "⋮" menu any more); Delete asks in the WPUF dialog.
            allPostTitles: '//td[@data-label="Title"]//a',
            postTitleCell: (title: string) => `//td[@data-label="Title"]//a[normalize-space()="${title}"]`,
            editLinkForPost: (title: string) => `//tr[.//td[@data-label="Title"]//a[normalize-space()="${title}"]]//td[@data-label="Options"]//a[@title="Edit"]`,
            deleteLinkForPost: (title: string) => `//tr[.//td[@data-label="Title"]//a[normalize-space()="${title}"]]//td[@data-label="Options"]//button[@title="Delete"]`,
            deleteConfirm: '[data-wpuf-dialog] [data-slot="alert-dialog-action"], .wpuf-dialog__footer .wpuf-dialog__btn:last-child',
        },

        productFrontendCreate: {
            // Product Create

            // Start Form Submission
            // Post Tile
            productTitleFE: '//input[@name="post_title"]',
            // Post Description
            productDescription1: '//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]',
            productDescription2: '//body[@id="tinymce"]',
            // Featured Photo
            productImage: '//li[@data-label="Product Image"]//input[@type="file"]',
            uploads: (upload: string) => `(//div[@class='attachment-name']//img)[${upload}]`,
            // Excerpt
            productExcerpt: '//textarea[@name="post_excerpt"]',
            //Regular Price
            productRegularPrice: '//input[@name="_regular_price"]',
            // sale preice
            productSalePrice: '//input[@name="_sale_price"]',
            // image Gallery
            productImageGallery: '//li[@data-label="Product Image Gallery"]//input[@type="file"]',
            //visibility
            catalogVisibility: '//select[@name="_visibility"]',
            // purchase Note
            purchaseNote: '//textarea[@name="_purchase_note"]',
            // reviews
            enableReviews: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="_enable_reviews"]] | //input[@value="_enable_reviews"] >> visible=true >> nth=0',
            // downloadable
            downloadable: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="no"]] | //input[@value="no"] >> visible=true >> nth=0',
            // Taxonomy selects by name: the React select keeps the native <select>
            // (name only, no id) hidden beside its list button; a multiselect posts name[].
            // brand
            selectBrand: '//select[@name="product_brand" or @name="product_brand[]"]',
            // category
            selectCategory: '//select[@name="product_cat" or @name="product_cat[]"]',
            // tag
            selectTag: '//select[@name="product_tag" or @name="product_tag[]"]',
            // attribute
            selectShippingClass: '//select[@name="product_shipping_class" or @name="product_shipping_class[]"]',
            // type
            selectType: '//select[@name="product_type" or @name="product_type[]"]',
            // visibility
            selectVisibility: '//select[@name="product_visibility" or @name="product_visibility[]"]',
            // color
            selectColor: '//select[@name="pa_color" or @name="pa_color[]"]',
            //create
            createProduct: '//input[@name="submit"]'

        },

        downloadsFrontendCreate: {
            // Downloads Create
            // Start Form Submission
            // Post Tile
            downloadsTitleFE: '//input[@name="post_title"]',
            // Post Description
            downloadsDescription1: '//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]',
            downloadsDescription2: '//body[@id="tinymce"]',
            // Featured Photo
            downloadsImage: '//li[@data-label="Download Image"]//input[@type="file"]',
            uploads: (upload: string) => `(//div[@class='attachment-name']//img)[${upload}]`,
            // Excerpt
            downloadsExcerpt: '//textarea[@name="post_excerpt"]',
            // Regular Price
            downloadsRegularPrice: '//input[@name="edd_price"]',
            // Purchase Note
            purchaseNote: '//textarea[@name="edd_product_notes"]',
            // Downloadable files
            downloadableFiles: '//li[@data-label="Downloadable Files"]//input[@type="file"]',
            // Category
            downloadCategory: '//select[@name="download_category" or @name="download_category[]"]',
            // Tag
            downloadsTag: '//select[@name="download_tag" or @name="download_tag[]"]',
            // Create
            createDownloads: '//input[@name="submit"]'
        },

        postFormData: {
            title: (title: string) => `//h1[normalize-space(text())='${title}']`,
            description: (description: string) => `//div[contains(@class,"entry-content")]//p[normalize-space(text())="${description}"]`,
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
            repeatField: (repeatField: string) => `//li[contains(.,"Repeat Field: ${repeatField}")]`,
            dateTime: (dateTime: string) => `//li[contains(.,"Date / Time: ${dateTime}")]`,
            timeField: (timeField: string) => `//li[contains(.,"Time Field: ${timeField}")]`,
            fileUpload: '//label[text()="File Upload:"]/following-sibling::a',
            countryList: (countryList: string) => `//li[contains(.,"Country List: ${countryList}")]`,
            numericField: '//li[contains(@class,"wpuf-field-data-numeric_text_field")]',
            phoneField: (phoneNumber: string) => `//li[contains(.,"Phone Field: ${phoneNumber}")]`,
            addressLine1: (addressLine1: string) => `//li[contains(text(),"${addressLine1}")]`,
            addressLine2: (addressLine2: string) => `//li[contains(text(),"${addressLine2}")]`,
            city: (city: string) => `//li[contains(text(),"${city}")]`,
            zip: (zip: string) => `//li[contains(text(),"${zip}")]`,
            country: (country: string) => `//li[contains(text(),"${country}")][2]`,
            state: (state: string) => `//li[contains(text(),"${state}")][2]`,
            embed: '//div[@class="wpuf-embed-preview"]//a',
            ratings: '//li[contains(@class,"wpuf-field-data-ratings")]',
        },

        productFormData: {
            title: (title: string) => `//h1[normalize-space(text())='${title}']`,
            description: (description: string) => `//div[contains(@class,"entry-content")]//p[normalize-space(text())="${description}"]`,
            excerpt: '//div[@class="wp-block-post-excerpt"]//p[1]',
            regularPrice: '(//span[@class="woocommerce-Price-amount amount"]//bdi)[1]',
            salePrice: '(//span[@class="woocommerce-Price-amount amount"]//bdi)[2]',
            featuredImage: '//div[@class="wp-block-woocommerce-product-image-gallery "]//div[3]',
            galleryImage: (number: string) => `(//ol[@class="flex-control-nav flex-control-thumbs"]//img)[${number}]`,
            category: '//span[text()="Category: "]/following-sibling::a',
            tags: '//span[text()="Tags: "]/following-sibling::a',
            brand: '//span[text()="Brands: "]/following-sibling::a',
            reviews: '//a[normalize-space()="Reviews (0)"]',
            productTitle: (title: string) => `//a[normalize-space()='${title}']`,
            type: (type: string) => `//select[@name="product-type"]//option[@value="${type}"]`,
        },

        downloadsFormData: {
            title: (title: string) => `//h1[normalize-space(text())='${title}']`,
            description: (description: string) => `//div[contains(@class,"entry-content")]//p[normalize-space(text())="${description}"]`,
            purchaseButton: '//span[@class="edd-add-to-cart-label"]',
            downloadsImage: '//figure[@class="wp-block-post-featured-image"]',
            titleBE: (title: string) => `//a[normalize-space()='${title}']`,
            // Scope to EDD's native price field container: the page also carries a hidden
            // WPUF-rendered #edd_price twin, so a bare positional index can land on it.
            price: (price: string) => `//div[@id="edd_regular_price_field"]//input[@id="edd_price" and @value="${price}.00"]`,
            clickDownload: '//div[@class="interface-complementary-area editor-sidebar"]//div//button//span[normalize-space()="Download"]',
            clickCategory: '//button[normalize-space()="Categories"]',
            categoryBE: (category: string) => `//label[normalize-space()='${category}']`,
            clickTag: '//button[normalize-space()="Tags"]',
            tagBE: (tag: string) => `//span[normalize-space()='${tag}']`,
            excerpt: (excerpt: string) => `//span[normalize-space(text())='${excerpt}']`,
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
        },

        productPostForm: {
            addBrand: '//input[@id="tag-name"]',
            addCategory: '//input[@id="tag-name"]',
            addTag: '//input[@id="tag-name"]',
            addAttribute: '//input[@id="attribute_label"]',
            saveSubmit: '//input[@id="submit"]',
            saveAttribute: '//button[@id="submit"]',
            configureAttributeTerms: '//a[normalize-space()="Configure terms"]',
            addAttributeTerms: '//input[@id="tag-name"]',
        },

        eddPostForm: {
            addCategory: '//input[@id="tag-name"]',
            addTag: '//input[@id="tag-name"]',
            saveSubmit: '//input[@id="submit"]',
        },
    },

    /****************************************************/
    /********** @RegistrationForms Selectors ***********/
    /****************************************************/

    registrationForms: {
        // Navigate Registration Forms Page
        navigatePage_RF: {
            // WPUF > Pages > Navigation
            checkAddButton_RF: '(//*[self::a or self::button][contains(@class,"new-wpuf-form")])[1]',
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
            clickRegistraionAddForm: '(//*[self::a or self::button][contains(@class,"new-wpuf-form")])[1]',
            //hoverBlankForm: '(//a[contains(@class,"new-wpuf-form wpuf-rounded-md")])',
            clickBlankForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Blank Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Blank Form"])]',

            // Enter_NAME
            editNewFormName: '//input[@name="post_title"]',
            enterNewFormName: '//input[@name="post_title"]',  // TODO: Catch with Child
            confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::*[self::i or self::button][1]',
        },

        addFields: {
            clickForm: (formName: string) => `(//td//a[normalize-space()="${formName}"] | //span[normalize-space()="${formName}"])[1]`,
            clickFormEditor: '//a[contains(text(),"Form Editor")]',
            clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab")][normalize-space()="Settings"] | (//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2])[1]',
            clickBlankForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Blank Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Blank Form"])]',
            useField: (field: string) => `//p[normalize-space()="${field}"]`,
            // OK of the "Oops... You already have this field in the form" alert (SweetAlert on develop).
            alreadyAddedOk: '.swal2-container .swal2-confirm, [data-slot="alert-dialog-action"], .wpuf-dialog__footer .wpuf-dialog__btn:last-child',
            clickAddFieldButton: '//a[contains(text(),"Add Field")]',
            validateField: (field: string) => `(//label[@for="${field}" or @for="wpuf-${field}"]/../..//div[@class="wpuf-fields"])[1]`,
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
            storeShortcode: (formName: string) => `(//tr[.//td//a[normalize-space()='${formName}']]//code | //span[normalize-space()='${formName}']//..//..//code)[1]`,
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
            rfUserName: '//input[@name="user_login"]',
            rfFirstName: '//input[@name="first_name"]',
            // Last Name
            rfLastName: '//input[@name="last_name"]',
            // Email
            rfEmail: '//input[@name="user_email"]',
            // Password
            rfPassword: '//input[@name="pass1"]',
            // Confirm Password
            rfConfirmPassword: '//input[@name="pass2"]',
            rfDisplayName: '//input[@name="display_name"]',
            rfNickname: '//input[@name="nickname"]',
            rfWebsite: '//input[@name="user_url"]',
            rfBiographicalInfo: '//textarea[@name="description"]',
            rfAvatar: '//li[@data-label="Avatar"]//input[@type="file"]',
            rfProfilePhoto: '//li[@data-label="Profile Photo"]//input[@type="file"]',
            rfXtwitter: '//input[@name="wpuf_social_twitter"]',
            rfFacebook: '//input[@name="wpuf_social_facebook"]',
            rfLinkedIn: '//input[@name="wpuf_social_linkedin"]',
            rfInstagram: '//input[@name="wpuf_social_instagram"]',
            // Register button
            rfRegisterButton: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="Register"]] | //input[@value="Register"] >> visible=true >> nth=0',

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

            validateUserEmail: (email: string) => `//a[normalize-space()='${email}']`,
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
        // Reactivate Theme
        reActivateTheme: '//input[@id="reactivate-theme"]',
        // Reactivate Plugins
        reActivatePlugins: '//input[@id="reactivate-plugins"]',
    },

    postFormSettings: {
        // Navigation and Basic Elements
        formNameInput: '//input[@name="post_title"]',
        addNewButton: '(//*[self::a or self::button][contains(@class,"new-wpuf-form")])[1]',
        saveButton: '//button[normalize-space(text())="Save"]',
        postTypeColumn: (formName: string, postType: string) => `(//tr[.//td//a[normalize-space()="${formName}"]]//td[normalize-space()="${postType}"] | //span[normalize-space()="${formName}"]//..//..//td[normalize-space()="${postType}"])[1]`,
        postSubmissionStatusColumn: (formName: string, status: string) => `(//tr[.//td//a[normalize-space()="${formName}"]]//td[normalize-space()="${status}"] | //span[normalize-space()="${formName}"]//..//..//td[normalize-space()="${status}"])[1]`,
        clickFormEditor: '//a[contains(text(),"Form Editor")]',
        clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab")][normalize-space()="Settings"] | (//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2])[1]',
        clickBlankForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Blank Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Blank Form"])]',
        confirmNewNameTickButton: '//input[@name="post_title"]/following-sibling::*[self::i or self::button][1]',
        clickForm: (formName: string) => `(//td//a[normalize-space()="${formName}"] | //span[normalize-space()="${formName}"])[1]`,
        postTypePage: (type: string) => `//a[normalize-space()="${type}"]`,
        postCategory: (category: string) => `//a[normalize-space()="${category}"]`,
        submitPostButton: '//input[@name="submit"]',
        updatePostButton: '//input[@name="submit"]',
        submitPostButtonText: (value: string) => `//input[@value="${value}"]`,
        checkPostTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        checkSuccessMessage: '//div[@class="wpuf-success"]',
        checkPageTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        postTitleColumn: (title: string, a: string) => `//td${a}[normalize-space(text())="${title}"]`,
        postStatusColumn: (title: string, status: string, a: string, b: string) => `//td${a}[normalize-space(text())="${title}"]//..${b}//span[normalize-space(text())="${status}"]`,
        saveDraftButton: '//a[normalize-space(text())="Save Draft"]',
        draftSavedAlert: '//span[@class="wpuf-draft-saved"]',
        // Progressbar ("progressive") type — the default. The frontend renders a header
        // (.wpuf-progressbar-header) with "Step N of M" step text plus a percent, not the old
        // "Step Start (100%)" label. Match the new step-text span.
        multiStepProgressbar: '//div[contains(@class,"wpuf-multistep-progressbar")]//span[contains(@class,"wpuf-progressbar-step-text") and starts-with(normalize-space(.),"Step 1 of")]',
        // Step-by-step type — renders a .wpuf-step-wizard of .wpuf-step-item circles, each with
        // a .wpuf-step-label carrying the step legend ("Step Start"), not the old <li> markup.
        multiStepByStep: '//div[contains(@class,"wpuf-step-wizard")]//div[contains(@class,"wpuf-step-label") and normalize-space(text())="Step Start"]',
        removeStepStart: '//div[@class="step-start-indicator"]/../../../..//span[4]',
        confirmDelete: '//button[normalize-space()="Yes, delete it"]',
        threeDotButton: '(//div[contains(@class,"wpuf-relative wpuf-inline-block")]//button)[1]',
        // React account Posts list: the row's Edit link (no options menu any more).
        editPostButton: '(//div[contains(@class,"wpuf-row-actions")]//a[@title="Edit"] | //td[@data-label="Options: "]//a)[1]',
        quickEditButtonContainer: '//tbody[@id="the-list"]//tr[1]',
        quickEditButton: '(//button[@class="button-link editinline"])[1]',
        statusDropdown: '(//select[@name="_status"])[1]',
        updateStatus: '(//input[@id="_inline_edit"]/following-sibling::button)[1]',
        wpufInfo: '//div[@class="wpuf-info"]',
        paymentPageTitle: '//h1[normalize-space(text())="Payment"]',
        validatePayPerPostCost: '//span[@id="wpuf_pay_page_cost"]',
        checkBankButton: '//li[@class="wpuf-gateway-bank"]//input[1]',
        proceedPaymentButton: '//button[@name="wpuf_payment_submit"]',
        afterPaymentPageTitle: (successPage: string) => `//h1[normalize-space(text())="${successPage}"]`,
        transactionTableRow: '//tbody//tr[1]',
        // React Transactions route: Pending tab, first row's Actions menu, Accept item, confirm.
        transactionsPendingTab: '(//*[@role="tab" or self::button or self::a][starts-with(normalize-space(),"Pending")])[1]',
        transactionRowActions: '(//button[starts-with(@aria-label,"Actions for payment")])[1]',
        acceptPayment: '//*[@role="menuitem"][normalize-space()="Accept"]',
        acceptPaymentConfirm: '//*[@role="alertdialog"]//button[normalize-space()="Accept"]',
        successMessage: '//div[@class="wpuf-success"]',
        wpufMessage: '//div[@class="wpuf-message"]',
        clickPost: (postTitle: string) => `//a[normalize-space(text())="${postTitle}"]`,

        showFormTitle: (formName: string) => `//h2[normalize-space()="${formName}"]`,
        showFormDescription: '//div[@class="wpuf-form-description"]',
        pendingMessage: '//div[normalize-space(text())="You can’t edit a post while in pending mode."]',


        // Post Settings Section
        postSettingsSection: {
            afterPostSettingsHeader: '//p[contains(text(),"After Post Settings")]',
            beforePostSettingsHeader: '//p[contains(text(),"Before Post Settings")]',

            // Post Type Selectize Dropdown
            postTypeContainer: '(//label[normalize-space(text())="Post Type"]/following::*[@role="combobox"][1] | //label[normalize-space(text())="Post Type"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postTypeDropdown: 'xpath=//*[@role="listbox"] | //label[normalize-space(text())="Post Type"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postTypeOption: (type: string) => `xpath=//*[@role="option"][@data-value="${type}"] | //label[normalize-space(text())="Post Type"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${type}"] >> visible=true >> nth=0`,

            defaultCategoryContainer: '(//*[self::label or self::div or self::span][normalize-space()="Default Categories"]/following::*[@role="combobox"][1] | //label[normalize-space()="Default Categories"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            defaultCategoryDropdown: 'xpath=//*[@role="listbox"] | //label[normalize-space()="Default Categories"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            defaultCategoryOption: (type: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${type}")] | //label[normalize-space()="Default Categories"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${type}")] >> visible=true >> nth=0`,

            postRedirectionContainer: '(//*[self::label or self::div or self::span][(@for="redirect_to" or @for="redirect_to-selectized")]/following::*[@role="combobox"][1] | //label[@for="redirect_to-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postRedirectionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postRedirectionOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            postRedirectionMessage: '//textarea[@id="message"]',

            postRedirectionPageContainer: '(//*[self::label or self::div or self::span][(@for="page_id" or @for="page_id-selectized")]/following::*[@role="combobox"][1] | //label[@for="page_id-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postRedirectionPageDropdown: 'xpath=//*[@role="listbox"] | //label[@for="page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postRedirectionPageOption: (text: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${text}")] | //label[@for="page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")] >> visible=true >> nth=0`,

            postRedirectionUrlInput: '//input[@id="url"]',

            postSubmissionStatusContainer: '(//*[self::label or self::div or self::span][(@for="post_status" or @for="post_status-selectized")]/following::*[@role="combobox"][1] | //label[@for="post_status-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postSubmissionStatusDropdown: 'xpath=//*[@role="listbox"] | //label[@for="post_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postSubmissionStatusOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="post_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            savingAsDraftToggleOn: 'xpath=//input[@id="draft_post"]/preceding-sibling::*[@role="switch"][1] | //input[@id="draft_post"]/following-sibling::span[1] >> visible=true >> nth=0',

            submitButtonContainer: '(//*[self::label or self::div or self::span][normalize-space(text())="Submit Post Button Text"]/following::input)[1]',

            // Multi-Step Settings
            enableMultiStepToggle: 'xpath=//input[@id="enable_multistep"]/preceding-sibling::*[@role="switch"][1] | //input[@id="enable_multistep"]/following-sibling::span[1] >> visible=true >> nth=0',
            enableMultiStepCheckbox: '//input[@id="enable_multistep"]',

            progressbarTypeContainer: '(//label[(@for="multistep_progressbar_type" or @for="multistep_progressbar_type-selectized")]/following::*[@role="combobox"][1] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            progressbarTypeDropdown: 'xpath=//*[@role="listbox"] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            progressbarTypeOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,


            // After Post Settings
            postUpdateStatusContainer: '(//*[self::label or self::div or self::span][(@for="edit_post_status" or @for="edit_post_status-selectized")]/following::*[@role="combobox"][1] | //label[@for="edit_post_status-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postUpdateStatusDropdown: 'xpath=//*[@role="listbox"] | //label[@for="edit_post_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postUpdateStatusOption: (status: string) => `xpath=//*[@role="option"][@data-value="${status}"] | //label[@for="edit_post_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${status}"] >> visible=true >> nth=0`,

            postUpdateMessageContainer: '//textarea[@id="update_message"]',

            lockUserEditingAfterInput: '//input[@id="lock_edit_post"]',

            updatePostButtonTextInput: '//input[@id="update_text"]',

            // Successful Redirection Settings (Update Post scenarios)
            updatePostRedirectionContainer: '(//*[self::label or self::div or self::span][(@for="edit_redirect_to" or @for="edit_redirect_to-selectized")]/following::*[@role="combobox"][1] | //label[@for="edit_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            updatePostRedirectionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="edit_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            updatePostRedirectionOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="edit_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            successfulRedirectionMessage: '//textarea[@id="update_message"]',

            updatePostRedirectionPageContainer: '(//*[self::label or self::div or self::span][(@for="edit_page_id" or @for="edit_page_id-selectized")]/following::*[@role="combobox"][1] | //label[@for="edit_page_id-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            updatePostRedirectionPageDropdown: 'xpath=//*[@role="listbox"] | //label[@for="edit_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            updatePostRedirectionPageOption: (text: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${text}")] | //label[@for="edit_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")] >> visible=true >> nth=0`,

            updatePostRedirectionUrlInput: '//input[@id="edit_url"]',

            postPermissionContainer: '(//*[self::label or self::div or self::span][(@for="post_permission" or @for="post_permission-selectized")]/following::*[@role="combobox"][1] | //label[@for="post_permission-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            postPermissionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="post_permission-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            postPermissionOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="post_permission-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            roleSelectionContainer: '(//*[self::label or self::div or self::span][(@for="roles" or @for="roles-selectized")]/following::*[@role="combobox"][1] | //label[@for="roles-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            roleSelectionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="roles-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            // Roles is a multi-select: its options have no data-value, match the role name too.
            roleSelectionOption: (value: string) => {
                const label = value.replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase());
                return `xpath=//*[@role="option"][@data-value="${value}" or normalize-space()="${label}"] | //label[@for="roles-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`;
            },
            paymentSettingsTab: '//li[@data-settings="payment_settings"]',
            paymentEnableToggle: 'xpath=//input[@id="payment_options"]/preceding-sibling::*[@role="switch"][1] | //input[@id="payment_options"]/following-sibling::span[1] >> visible=true >> nth=0',
            paymentOptionsContainer: '(//*[self::label or self::div or self::span][(@for="choose_payment_option" or @for="choose_payment_option-selectized")]/following::*[@role="combobox"][1] | //label[@for="choose_payment_option-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            paymentOptionsDropdown: 'xpath=//*[@role="listbox"] | //label[@for="choose_payment_option-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            payPerPostOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="choose_payment_option-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            payPerPostCostContainer: '//input[@id="pay_per_post_cost"]',
            paymentSuccessPageContainer: '(//*[self::label or self::div or self::span][(@for="ppp_payment_success_page" or @for="ppp_payment_success_page-selectized")]/following::*[@role="combobox"][1] | //label[@for="ppp_payment_success_page-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            paymentSuccessPageDropdown: 'xpath=//*[@role="listbox"] | //label[@for="ppp_payment_success_page-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            paymentSuccessPageOption: (text: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${text}")] | //label[@for="ppp_payment_success_page-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")] >> visible=true >> nth=0`,


            formTitleToggle: 'xpath=//input[@id="show_form_title"]/preceding-sibling::*[@role="switch"][1] | //input[@id="show_form_title"]/following-sibling::span[1] >> visible=true >> nth=0',
            formDescriptionBox: '//textarea[@id="form_description"]',

            unAuthMsg: '//textarea[@id="message_restrict"]'
        },

        // Validation Messages
        messages: {
            formSaved: '(//li[@data-sonner-toast]//div[@data-title][normalize-space()="Saved form data" or normalize-space()="Form data saved."])[1]',
        },

        // Notification Settings Section
        notificationSettingsSection: {
            notificationSettingsHeader: '//p[contains(text(),"New Post Notification")]',
            updatedPostNotificationSettingsHeader: '//p[contains(text(),"Update Post Notification")]',

            // New Post Notification
            newPostNotificationToggle: 'xpath=(//p[normalize-space()="New Post Notification"]/following::*[@role="switch"][1] | //input[@name="wpuf_settings[notification][new]"]/following-sibling::span[1])[1]',
            newPostNotificationTo: 'xpath=(//p[normalize-space()="New Post Notification"]/following::*[normalize-space(text())="To"][1]/following::input[1] | //input[@name="wpuf_settings[notification][new_to]"])[1]',
            newPostNotificationSubject: 'xpath=(//p[normalize-space()="New Post Notification"]/following::*[normalize-space(text())="Subject"][1]/following::input[1] | //input[@name="wpuf_settings[notification][new_subject]"])[1]',
            newPostNotificationBody: 'xpath=(//p[normalize-space()="New Post Notification"]/following::*[normalize-space(text())="Email Body"][1]/following::textarea[1] | //textarea[@name="wpuf_settings[notification][new_body]"])[1]',

            // Update Post Notification (PRO)
            updatePostNotificationToggle: 'xpath=(//p[normalize-space()="Update Post Notification"]/following::*[@role="switch"][1] | //input[@name="wpuf_settings[notification_edit]"]/following-sibling::span[1])[1]',
            updatePostNotificationTo: 'xpath=(//p[normalize-space()="Update Post Notification"]/following::*[normalize-space(text())="To"][1]/following::input[1] | //input[@name="wpuf_settings[notification_edit_to]"])[1]',
            updatePostNotificationSubject: 'xpath=(//p[normalize-space()="Update Post Notification"]/following::*[normalize-space(text())="Subject"][1]/following::input[1] | //input[@name="wpuf_settings[notification_edit_subject]"])[1]',
            updatePostNotificationBody: 'xpath=(//p[normalize-space()="Update Post Notification"]/following::*[normalize-space(text())="Email Body"][1]/following::textarea[1] | //textarea[@name="wpuf_settings[notification_edit_body]"])[1]',

            // point 1 = New Post Notification tags, 2 = Update Post Notification tags. React
            // unmounts a switched-off section, so the update tags are found after their heading.
            templateTagPointer: (tag: string, point: string) => '2' === point
                ? `xpath=(//p[contains(normalize-space(),"Update Post Notification")]/following::span[@data-clipboard-text="${tag}"])[1]`
                : `(//span[@data-clipboard-text="${tag}"])[${point}]`,
            tagClickTooltip: '//span[@data-original-title="Copied!"]',
            sentEmailAddress: (emails: string) => `(//div[normalize-space()='${emails}'])[1]`,
            sentEmailSubjectSubmitted: '//div[normalize-space()="New post submitted"]',
            viewEmailContentSubmitted: '//div[normalize-space()="New post submitted"]',
            sentEmailSubjectUpdated: '//div[normalize-space()="Post updated"]',
            viewEmailContentUpdated: '//div[normalize-space()="Post updated"]',
            previewEmailContentBody: '(//div[@class="wml-body-wrapper"])[1]',


        },

        // Advanced Settings Section
        advancedSettingsSection: {
            advancedSettingsHeader: '//li[contains(concat(" ", normalize-space(@class), " "), " active_settings_tab ") and normalize-space()="Advanced"]',

            // Comment Status
            commentStatusContainer: '(//*[self::label or self::div or self::span][(@for="comment_status" or @for="comment_status-selectized")]/following::*[@role="combobox"][1] | //label[@for="comment_status-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            commentStatusDropdown: 'xpath=//*[@role="listbox"] | //label[@for="comment_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            commentStatusOption: (status: string) => `xpath=//*[@role="option"][@data-value="${status}"] | //label[@for="comment_status-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${status}"] >> visible=true >> nth=0`,

            commentBox: '//textarea[@id="comment"]',
            postCommentButton: '//input[@id="submit"]',
            validateComment: '//ol//li[1]//div[@class="wp-block-comment-content"]',

            limitFormEntriesToggle: 'xpath=//input[@id="limit_entries"]/preceding-sibling::*[@role="switch"][1] | //input[@id="limit_entries"]/following-sibling::span[1] >> visible=true >> nth=0',
            limitNumberInput: '//input[@id="limit_number"]',
            limitMessage: '//textarea[@id="limit_message"]',

            condtonalLogicOn: '(//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//*[@role="radio"][following-sibling::input[1][@value="yes"]] | (//*[self::label or self::div or self::span][normalize-space(text())="Conditional Logic on Submit Button"]/following::input)[1])[1]',
            condtonalLogicOff: '(//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//*[@role="radio"][following-sibling::input[1][@value="no"]] | (//label[normalize-space(text())="Conditional Logic on Submit Button"]/following::input)[2])[1]',
            meetRules: '(//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"wpuf-conditional-logic-settings")]//*[@role="combobox"] | //select[@name="wpuf_settings[submit_button_cond][cond_logic]"])[1]',

            selectField1:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-field")]//*[@role="combobox"])[1] | //select[@name="wpuf_settings[submit_button_cond][conditions][0][name]"])[1]',
            selectAction1:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-operator")]//*[@role="combobox"])[1] | //select[@name="wpuf_settings[submit_button_cond][conditions][0][operator]"])[1]',
            setValue1:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-option")]//input[(not(@type) or @type="text") and not(@aria-hidden="true")])[1] | //input[@name="wpuf_settings[submit_button_cond][conditions][0][option]"])[1]',
 
            selectField2:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-field")]//*[@role="combobox"])[2] | //select[@name="wpuf_settings[submit_button_cond][conditions][1][name]"])[1]',
            selectAction2:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-operator")]//*[@role="combobox"])[2] | //select[@name="wpuf_settings[submit_button_cond][conditions][1][operator]"])[1]',
            setValue2:'((//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//div[contains(@class,"cond-option")]//input[(not(@type) or @type="text") and not(@aria-hidden="true")])[2] | //input[@name="wpuf_settings[submit_button_cond][conditions][1][option]"])[1]',

            addConditionButton:'(//div[contains(@class,"wpuf-submit-button-conditional-logic-container")]//button[contains(@class,"wpuf-repeater-add")] | //button[@title="Add Condition"])[1]',

            submitButton:'//input[@name="submit"]',

            inputText:'//input[@name="text"]',
            inputTextarea:'//textarea[@name="textarea"]',
            clickTitle:'//input[@name="post_title"]',


        },

        // Post Expiration Settings Section
        postExpirationSettingsSection: {
            postExpirationSettingsHeader: '//li[contains(concat(" ", normalize-space(@class), " "), " active_settings_tab ") and normalize-space()="Post Expiration"]',
            postExpirationToggle: 'xpath=//*[@role="switch"][following-sibling::input[1][@id="enable_post_expiration" or contains(@id,"[enable_post_expiration]")]] | //label[normalize-space()="Enable Post Expiration"]/following::*[@role="switch"][1] | //input[@id="enable_post_expiration"]/following-sibling::span[1] >> visible=true >> nth=0',
            postExpirationTime: '//input[@id="expiration_time_value" or contains(@id,"[expiration_time_value]")]',
            enablePostExpirationMessage: 'xpath=//*[@role="checkbox"][following-sibling::input[1][@id="enable_mail_after_expired" or contains(@id,"[enable_mail_after_expired]")]] | //*[@role="checkbox"][@id="enable_mail_after_expired" or contains(@id,"[enable_mail_after_expired]")] | //input[@id="enable_mail_after_expired"] >> visible=true >> nth=0',
            postExpirationMessage: '//textarea[@id="post_expiration_message" or contains(@id,"[post_expiration_message]")]',
        },

        // Navigation tabs
        notificationSettingsTab: '//li[@data-settings="notification_settings"]',
        paymentSettingsTab: '//li[@data-settings="payment_settings"]',
        displaySettingsTab: '//li[@data-settings="display_settings"]',
        advancedSettingsTab: '//li[@data-settings="advanced"]',
        postExpirationSettingsTab: '//li[normalize-space()="Post Expiration"]',
    },

    regFormSettings: {

        clickForm: (formName: string) => `(//td//a[normalize-space()="${formName}"] | //span[normalize-space()="${formName}"])[1]`,
        saveButton: '//button[normalize-space(text())="Save"]',
        formSaved: '//li[@data-sonner-toast]//div[@data-title][normalize-space()="Saved form data" or normalize-space()="Form data saved."]',
        clickFormEditor: '//a[contains(text(),"Form Editor")]',
        clickFormEditorSettings: '(//a[contains(@class,"wpuf-nav-tab")][normalize-space()="Settings"] | (//a[contains(@class,"wpuf-nav-tab wpuf-nav-tab-active")])[2])[1]',
        inputEmail: '//input[@name="user_email"]',
        inputPassword: '//input[@name="pass1"]',
        inputConfirmPassword: '//input[@name="pass2"]',
        submitRegisterButton: '//input[@name="submit"]',
        submitRegisterButtonText: (value: string) => `//input[@value="${value}"]`,
        checkPostTitle: (title: string) => `//h1[normalize-space(text())='${title}']`,
        checkSuccessMessage: '//div[@class="wpuf-success"]',
        saveDraftButton: '//a[normalize-space(text())="Save Draft"]',
        draftSavedAlert: '//span[@class="wpuf-draft-saved"]',
        confirmDelete: '//button[normalize-space()="Yes, delete it"]',
        // React account Posts list: the row's Edit link (no options menu any more).
        editPostButton: '(//div[contains(@class,"wpuf-row-actions")]//a[@title="Edit"] | //td[@data-label="Options: "]//a)[1]',
        quickEditButtonContainer: '//tbody[@id="the-list"]//tr[1]',
        quickEditButton: '(//button[@class="button-link editinline"])[1]',
        wpufInfo: '//div[@class="wpuf-info"]',
        successMessage: '//div[@class="wpuf-success"]',
        wpufMessage: '//div[@class="wpuf-message"]',
        accountGuestText: '.wpuf-account-guest .wpuf-state-card__text',
        wpLoginErrorMessage: '//div[@id="login_error"]',
        // React [wpuf-login]: one line per server error inside the alert.
        wpufLoginErrorLines: '.wpuf-login-root .wpuf-error .wpuf-login-error-line',

        regSettingsSection: {
            regSettingsHeader: '//li[contains(concat(" ", normalize-space(@class), " "), " active_settings_tab ") and normalize-space()="General"]',

            userRoleContainer: '(//label[(@for="role" or @for="role-selectized")]/following::*[@role="combobox"][1] | //label[@for="role-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            userRoleDropdown: 'xpath=//*[@role="listbox"] | //label[@for="role-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            userRoleOption: (role: string) => `xpath=//*[@role="option"][@data-value="${role}"] | //label[@for="role-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${role}"] >> visible=true >> nth=0`,

            approvalToggle: 'xpath=//input[@id="user_status"]/preceding-sibling::*[@role="switch"][1] | //input[@id="user_status"]/following-sibling::span[1] >> visible=true >> nth=0',
            approveUser: '//a[normalize-space()="Approve"]',
        },

        afterSignUpSettingsSection: {
            afterSignUpSettingsHeader: '//label[contains(text(),"After Registration Successful Redirection")]',

            // After Registration Successful Redirection (looking for actual form field structure)
            afterRegistrationRedirectionContainer: '(//*[self::label or self::div or self::span][(@for="reg_redirect_to" or @for="reg_redirect_to-selectized")]/following::*[@role="combobox"][1] | //label[@for="reg_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            afterRegistrationRedirectionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="reg_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            afterRegistrationRedirectionOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="reg_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            afterRegistrationRedirectionPageContainer: '(//*[self::label or self::div or self::span][(@for="reg_page_id" or @for="reg_page_id-selectized")]/following::*[@role="combobox"][1] | //label[@for="reg_page_id-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            afterRegistrationRedirectionPageDropdown: 'xpath=//*[@role="listbox"] | //label[@for="reg_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            afterRegistrationRedirectionPageOption: (text: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${text}")] | //label[@for="reg_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")] >> visible=true >> nth=0`,

            afterRegistrationRedirectionUrlInput: '//input[@id="registration_url"]',

            // Registration Success Message  
            registrationSuccessMessageInput: '//textarea[@id="message"]',

            // Submit Button Text
            submitButtonTextInput: '//input[@id="submit_text"]',

            // After Profile Update Successful Redirection
            afterProfileUpdateRedirectionContainer: '(//*[self::label or self::div or self::span][(@for="profile_redirect_to" or @for="profile_redirect_to-selectized")]/following::*[@role="combobox"][1] | //label[@for="profile_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            afterProfileUpdateRedirectionDropdown: 'xpath=//*[@role="listbox"] | //label[@for="profile_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            afterProfileUpdateRedirectionOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="profile_redirect_to-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,

            afterProfileUpdateRedirectionPageContainer: '(//*[self::label or self::div or self::span][(@for="profile_page_id" or @for="profile_page_id-selectized")]/following::*[@role="combobox"][1] | //label[@for="profile_page_id-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            afterProfileUpdateRedirectionPageDropdown: 'xpath=//*[@role="listbox"] | //label[@for="profile_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            afterProfileUpdateRedirectionPageOption: (text: string) => `xpath=//*[@role="option"][contains(normalize-space(),"${text}")] | //label[@for="profile_page_id-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[contains(text(),"${text}")] >> visible=true >> nth=0`,

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
            notificationSettingsHeader: '//li[contains(concat(" ", normalize-space(@class), " "), " active_settings_tab ") and normalize-space()="Notification Settings"]',

            // User Notification
            userNotificationHeader: '//p[normalize-space()="User Notification"]',
            enableUserNotificationToggle: 'xpath=//input[@id="user_notification"]/preceding-sibling::*[@role="switch"][1] | //input[@id="user_notification"]/following-sibling::span[1] >> visible=true >> nth=0',

            // User Notification Type
            emailVerificationRadio: 'xpath=//*[@role="radio"][@data-value="email_verification"] | //input[@id="email_verification"] >> visible=true >> nth=0',
            welcomeEmailRadio: 'xpath=//*[@role="radio"][@data-value="welcome_email"] | //input[@id="welcome_email"] >> visible=true >> nth=0',

            // Email Verification Settings
            confirmationEmailSubjectInput: '//input[@id="verification_subject" or contains(@id,"[verification_subject]")]',
            confirmationEmailBodyTextarea: 'xpath=//div[contains(@class,"mce-edit-area mce-container")]//iframe >> visible=true >> nth=0',

            // Welcome Email Settings
            welcomeEmailSubjectInput: '//input[@id="welcome_email_subject" or contains(@id,"[welcome_email_subject]")]',
            welcomeEmailBodyTextarea: 'xpath=//div[contains(@class,"mce-edit-area mce-container")]//iframe >> visible=true >> nth=0',

            textareaBody: '//body[@id="tinymce"]',

            // point 1 = verification body, 2 = welcome body, 3 = admin message. React
            // renders only the selected user mail, so the tags are found after their label.
            templateTagPointer: (tag: string, point: string) => {
                const anchor: Record<string, string> = {
                    '1': '//*[normalize-space(text())="Confirmation Email Body"]',
                    '2': '//*[normalize-space(text())="Welcome Email Body"]',
                    '3': '//*[normalize-space(text())="Admin Notification"]',
                };
                return anchor[point]
                    ? `xpath=(${anchor[point]}/following::span[@data-clipboard-text="${tag}"])[1]`
                    : `(//span[@data-clipboard-text="${tag}"])[${point}]`;
            },
            tagClickTooltip: '//span[@data-original-title="Copied!"]',

            // Admin Notification
            adminNotificationHeader: '//h3[normalize-space()="Admin Notification"]',
            enableAdminNotificationToggle: 'xpath=//input[@id="admin_notification"]/preceding-sibling::*[@role="switch"][1] | //input[@id="admin_notification"]/following-sibling::span[1] >> visible=true >> nth=0',
            adminNotificationSubjectInput: '//input[@id="admin_email_subject" or contains(@id,"[admin_email_subject]")]',
            adminNotificationMessageTextarea: '//textarea[@id="admin_email_body" or contains(@id,"[admin_email_body]")]',
        },

        // WP Mail Log validation selectors
        wpMailLogValidation: {
            wpMailLogPage: '//h2[normalize-space()="WP Mail Log"]',

            // First email row selectors
            sentEmailAddress: (emails: string) => `(//div[normalize-space()='${emails}'])[1]`,
            sentEmailSubject: (subject: string) => `(//div[normalize-space()='${subject}'])[1]`,
            viewEmailContent: (subject: string) => `(//div[normalize-space()='${subject}'])[1]`,
            previewEmailContentBody: '(//div[@class="wml-body-wrapper"])[1]',
            grabActivationLink: '//a[normalize-space()="Activation Link"]',

            modalCloseButton: '//button[@class="el-button el-button--danger"]',

            sentLatestEmailSubject: (subject: string) => `(//div[normalize-space()='${subject}'])[1]`,
            viewLatestEmailContent: (subject: string) => `(//div[normalize-space()='${subject}'])[1]`,

            // Search and filter
            emailSearchInput: '//input[@id="post-search-input"]',
            emailSearchButton: '//input[@id="search-submit"]',
        },

        // Multi-Step Settings Section
        advancedSettingsSection: {
            advancedSettingsHeader: '//li[contains(concat(" ", normalize-space(@class), " "), " active_settings_tab ") and normalize-space()="Advanced Settings"]',
            advancedSettingsTab: '//span[normalize-space()="Advanced Settings"]',
            multiStepSettingsHeader: '//p[normalize-space()="Multistep Form"]',
            enableMultiStepToggle: 'xpath=//input[@id="enable_multistep"]/preceding-sibling::*[@role="switch"][1] | //input[@id="enable_multistep"]/following-sibling::span[1] >> visible=true >> nth=0',
            multiStepTypeContainer: '(//*[self::label or self::div or self::span][(@for="multistep_progressbar_type" or @for="multistep_progressbar_type-selectized")]/following::*[@role="combobox"][1] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-control")]//div[contains(@class,"selectize-input")])[1]',
            multiStepTypeDropdown: 'xpath=//*[@role="listbox"] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")] >> visible=true >> nth=0',
            multiStepTypeOption: (value: string) => `xpath=//*[@role="option"][@data-value="${value}"] | //label[@for="multistep_progressbar_type-selectized"]//..//..//div[contains(@class,"selectize-dropdown-content")]//div[@data-value="${value}"] >> visible=true >> nth=0`,
            multiStepProgressbar: '//div[contains(@class,"wpuf-multistep-progressbar")]//span[contains(@class,"wpuf-progressbar-step-text") and starts-with(normalize-space(.),"Step 1 of")]',
            multiStepByStep: '//div[contains(@class,"wpuf-step-wizard")]//div[contains(@class,"wpuf-step-label") and normalize-space(text())="Step Start"]',
        },

        // Custom Fields Section
        addCustomFields_Common: {
            customFieldsStepStart: '//p[normalize-space(text())="Step Start"]',
            customFieldsText: '//p[normalize-space(text())="Text"]',
            customFieldsUrl: '//p[normalize-space(text())="Website URL"]',
        },

        // MailPoet email-marketing module + per-form subscription settings
        mailPoet: {
            // WPUF > Modules : the "Mailpoet 3" module card + its enable toggle
            moduleCard: 'li[data-module="mailpoet3/wpuf-mailpoet-3.php"]',
            moduleToggle: 'li[data-module="mailpoet3/wpuf-mailpoet-3.php"] [role="switch"]',
            moduleCheckbox: 'li[data-module="mailpoet3/wpuf-mailpoet-3.php"] [role="switch"]',
            // Registration form builder > Settings > Modules > Mailpoet 3
            settingsMenuItem: '//li[normalize-space()="Mailpoet 3"]',
            // React: a plugin-ui switch; #enable_mailpoet_3 is the hidden input behind it.
            enableToggle: 'role=switch[name=/Enable Mailpoet 3/i]',
            enableCheckbox: '#enable_mailpoet_3',
            listSelect: '#mailpoet_3_list',
        },
    },

    /****************************************************/
    /********** @Vendor Registration Forms Selectors ***********/
    /****************************************************/

    vendorRegistrationForms: {
        // Dokan Vendor Registration Form Selectors
        dokanVendor: {
            // Form Creation
            createDokanVendorForm: '//a[contains(normalize-space(.), "Create Form") and (@title="Dokan Vendor Registration Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="Dokan Vendor Registration Form"])]',
            
            // Profile Fields
            validateField: (field: string) => `(//label[@for="${field}" or @for="wpuf-${field}"]/../..//div[@class="wpuf-fields"])[1]`,
            validateAddressField: '//label[@for="dokan_address" or @for="wpuf-dokan_address"]',
            validatePasswordField: '(//label[@for="password" or @for="wpuf-password"])[1]',
            validateConfirmPasswordField: '(//label[@for="password" or @for="wpuf-password"]/ancestor::li[1]//label)[2]',
            
            // Frontend Registration Form
            frontendForm: {
                firstNameField: '//input[@name="first_name"]',
                lastNameField: '//input[@name="last_name"]',
                emailField: '//input[@name="user_email"]',
                shopUrlField: '//input[@name="shopurl"]',
                shopNameField: '//input[@name="dokan_store_name"]',
                phoneField: '//input[@name="dokan_store_phone"]',
                addressLine1Field: '//input[@name="dokan_address[street_1]"]',
                addressLine2Field: '//input[@name="dokan_address[street_2]"]',
                cityField: '//input[@name="dokan_address[city]"]',
                stateField: '//select[@name="dokan_address[state]"]',
                zipField: '//input[@name="dokan_address[zip]"]',
                countryField: '//select[@name="dokan_address[country_select]"]',
                storeLogoField: '(//li[@data-label="Profile Picture"]//input[@type="file"])[1]',
                storeBannerField: '(//li[@data-label="Upload Banner"]//input[@type="file"])[1]',
                passwordField: '//input[@name="pass1"]',
                confirmPasswordField: '//input[@name="pass2"]',
                registerButton: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="Register"]] | //input[@value="Register"] >> visible=true >> nth=0',
                successMessage: '//div[@class="wpuf-success"]',
            },
            
            // Admin Validation
            adminValidation: {
                searchUserField: '//input[@type="search"]',
                searchSubmitButton: '//input[@id="search-submit"]',
                userEmailValidation: (email: string) => `//a[normalize-space()='${email}']`,
                dokanVendorRole: '//td[contains(text(),"Vendor")]',
            },
            dokanValidation: {
                searchVendorField: '//input[@id="post-search-input"]',
                vendorName: (name: string) => `//a[normalize-space()="${name}"]`,
                vendorEnable: (name: string) => `//a[normalize-space()="${name}"]//..//..//..//span[@class='slider round']`,
                vendorValidation: (name: string) => `//h2[normalize-space()='${name}']`,
                validateVendorPhone: (number:string)=> `//li[normalize-space(text())='+88${number}']`,
                validateAddress: (address:string)=> `//span[normalize-space(text())='${address},']`,
                validateStateZip: '//span[normalize-space(text())="BD-13 1216"]',
                validateVendorEnabled: '//button[normalize-space()="Enabled"]',
            }
        },

        // WC Vendors Registration Form Selectors
        wcVendor: {
            // Form Creation
            createWcVendorForm: '//a[contains(normalize-space(.), "Create Form") and (@title="WC Vendors Registration Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="WC Vendors Registration Form"])]',
            wcVendorFormName: '//input[@name="post_title"]',
            wcVendorFormEditor: '//a[contains(text(),"Form Editor")]',
            
            // Profile Fields
            validateField: (field: string) => `(//label[@for="${field}" or @for="wpuf-${field}"]/../..//div[@class="wpuf-fields"])[1]`,
            validatePasswordField: '(//label[@for="password" or @for="wpuf-password"])[1]',
            validateConfirmPasswordField: '(//label[@for="password" or @for="wpuf-password"]/ancestor::li[1]//label)[2]',
            
            // Frontend Registration Form
            frontendForm: {
                emailField: '//input[@name="user_email"]',
                paypalField: '//input[@name="pv_paypal"]',
                shopNameField: '//input[@name="pv_shop_name"]',
                sellerInfo: '//textarea[@name="pv_seller_info"]',
                shortDescription: '//textarea[@name="pv_shop_description"]',
                passwordField: '//input[@name="pass1"]',
                confirmPasswordField: '//input[@name="pass2"]',
            },
            
            // Admin Validation
            adminValidation: {
                searchUserField: '//input[@type="search"]',
                searchSubmitButton: '//input[@id="search-submit"]',
                userEmailValidation: (email: string) => `//a[normalize-space()='${email}']`,
                wcVendorRole: '//td[contains(text(),"Pending Vendor")]',
            },

            wcValidation: {
                vendorValidation: (name: string) => `//a[normalize-space()='${name}']`,
                // The status in this vendor's row (earlier runs leave other Active vendors).
                vendorStatusValidation: (name: string) => `//tr[.//a[normalize-space()='${name}']]//td//span[text()="Active"]`,
            }
        },

        // WCFM Membership Registration Form Selectors
        wcfmMember: {
            // Form Creation
            createWcfmMemberForm: '//a[contains(normalize-space(.), "Create Form") and (@title="WCFM Membership Registration Form" or ancestor::div[contains(@class,"wpuf-template-card")]//span[normalize-space()="WCFM Membership Registration Form"])]',
            
            // Profile Fields
            validateField: (field: string) => `(//label[@for="${field}" or @for="wpuf-${field}"]/../..//div[@class="wpuf-fields"])[1]`,
            validateAddressField: '//label[@for="_vendor_address" or @for="wpuf-_vendor_address"]',
            validatePasswordField: '(//label[@for="password" or @for="wpuf-password"])[1]',
            
            // Frontend Registration Form
            frontendForm: {
                storeNameField: '//input[@name="user_login"]',
                phoneField: '//input[@name="_vendor_phone"]',
                emailField: '//input[@name="user_email"]',
                passwordField: '//input[@name="pass1"]',
                confirmPasswordField: '//input[@name="pass2"]',
                websiteField: '//input[@name="user_url"]',
                descriptionField: '//textarea[@name="_vendor_description"]',
                nextButton: '//button[normalize-space()="Next"]',
                addressLine1Field: '//input[@name="_vendor_address[street_address]"]',
                addressLine2Field: '//input[@name="_vendor_address[street_address2]"]',
                cityField: '//input[@name="_vendor_address[city_name]"]',
                stateField: '//input[@name="_vendor_address[state]"]',
                zipField: '//input[@name="_vendor_address[zip]"]',
                countryField: '//select[@name="_vendor_address[country_select]"]',
                storeLogoField: '//li[@data-label="Store Logo"]//input[@type="file"]',
                storeBannerField: '//li[@data-label="Store Banner"]//input[@type="file"]',
                facebookField: '//input[@name="_vendor_fb_profile"]',
                twitterField: '//input[@name="_vendor_twitter_profile"]',
                googleField: '//input[@name="_vendor_google_plus_profile"]',
                linkedinField: '//input[@name="_vendor_linkdin_profile"]',
                youtubeField: '//input[@name="_vendor_youtube"]',
                instagramField: '//input[@name="_vendor_instagram"]',
                registerButton: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="Register"]] | //input[@value="Register"] >> visible=true >> nth=0',
                successMessage: '//div[@class="wpuf-success"]',
            },
            
            // Admin Validation
            adminValidation: {
                searchUserField: '//input[@type="search"]',
                searchSubmitButton: '//input[@id="search-submit"]',
                userEmailValidation: (email: string) => `//a[normalize-space()='${email}']`,
                wcfmMemberRole: '//td[contains(text(),"Store Vendor")]',
            },
        },
        wpMailLogValidation: {
            wpMailLogPage: '//h2[normalize-space()="WP Mail Log"]',
            sentEmailAddress: (emails: string) => `(//div[normalize-space()='${emails}'])[1]`,
            viewEmailContent: (emails: string) => `(//div[normalize-space()='${emails}'])[2]`,
            // The activation mail of that address, by subject (not by row position: other
            // mails to the same address may come before or after it).
            // Pro's "pending by an administrator" mail, which a sign-up that needs no approval must not get.
            pendingStatusEmail: (email: string) => `//tr[.//div[normalize-space()='${email}']][contains(normalize-space(.),'Status has been changed to pending')]`,
            viewActivationEmail: (email: string) => `(//tr[.//div[normalize-space()='${email}']][contains(normalize-space(.),'Account Activation')]//div[normalize-space()='${email}'])[1]`,
            previewEmailContentBody: '(//div[@class="wml-body-wrapper"])[1]',
            grabActivationLink: '//a[normalize-space()="Activation Link"]',

            modalCloseButton: '//button[@class="el-button el-button--danger"]',
        }
    },

    /****************************************************/
    /********** @Field Options Settings Selectors *****/
    /****************************************************/

    fieldOptionsSettings: {
        // Field Navigation and Selection
        addFieldsButton: '(//a[normalize-space()="Add Fields"] | //h2[normalize-space()="Add Fields"])[1]',
        formSelector: (formName: string) => `//span[normalize-space()="${formName}"]`,
        // React builder: the "Field Options" column (its tab before the three-column layout).
        fieldOptionHeader: '(//*[@role="tab" and normalize-space()="Field Options"] | //h2[normalize-space()="Field Options"] | //div[@class="option-fields-section wpuf-mt-6"]//h3)[1]',
        advancedSettings: '(//*[self::h3 or self::button][normalize-space()="Advanced Options"])[1]',
        previewButton: '//a[normalize-space()="Preview"]',
        // Field Edit Actions
        fieldActions: {
            editFieldButton: (fieldType: string) => {
                // Special handling for text field to avoid matching textarea
                if (fieldType === 'text') {
                    return `//li[contains(@class,"form-field-text")][not(contains(@class,"textarea"))]//span[normalize-space()="Edit"]`;
                }
                // Special handling for date field to avoid matching datetime
                if (fieldType === 'date') {
                    return `//li[contains(@class,"form-field-date")][not(contains(@class,"datetime"))]//span[normalize-space()="Edit"]`;
                }
                return `//li[contains(@class,"form-field-${fieldType}")]//span[normalize-space()="Edit"]`;
            },
            copyFieldButton: (fieldType: string) => `//li[contains(@class,"form-field-${fieldType}")]//span[normalize-space()="Copy"]`,
            removeFieldButton: (fieldType: string) => `//li[contains(@class,"form-field-${fieldType}")]//span[normalize-space()="Remove"]`,
            hoverField: (fieldType: string) => {
                // Special handling for text field to avoid matching textarea
                if (fieldType === 'text') {
                    return `//li[contains(@class,"form-field-text")][not(contains(@class,"textarea"))]`;
                }
                // Special handling for date field to avoid matching datetime
                if (fieldType === 'date') {
                    return `//li[contains(@class,"form-field-date")][not(contains(@class,"datetime"))]`;
                }
                return `//li[contains(@class,"form-field-${fieldType}")]`;
            },
        },

        // Field Options Panel
        fieldOptionsPanel: {
            panelTitle: '(//h3[normalize-space()="Field Options"] | //h2[normalize-space()="Field Options"])[1]',
            
            // Basic Options - Common to most fields
            fieldLabel: '(//*[self::label or self::div or self::span][normalize-space(text())="Field Label"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            metaKey: '(//*[self::label or self::div or self::span][normalize-space(text())="Meta Key"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            helpText: '(//*[self::label or self::div or self::span][normalize-space(text())="Help text"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            selectText: '(//*[self::label or self::div or self::span][normalize-space(text())="Select Text"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            readOnly: '(//*[self::label or self::div or self::span][normalize-space(text())="Read Only"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            openInSameWindow: '(//*[self::label or self::div or self::span][normalize-space(text())="Open in :"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            openInNewWindow: '(//*[self::label or self::div or self::span][normalize-space(text())="Open in :"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[2]',

            advancedOptions: {
                placeholderText: '(//*[self::label or self::div or self::span][normalize-space(text())="Placeholder text"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                defaultValue: '(//*[self::label or self::div or self::span][normalize-space(text())="Default value"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                fieldSize:(fieldSize:string)=> {
                    if(fieldSize === 'small'){
                        return '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]'
                    }else if(fieldSize === 'medium'){
                        return '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[2]'
                    }else if(fieldSize === 'large'){
                        return '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[3]'
                    }
                },
                fieldSizeSmall: '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                fieldSizeMedium: '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[2]', 
                fieldSizeLarge: '(//*[self::label or self::div or self::span][normalize-space(text())="Field Size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[3]',
                cssClassName: '(//*[self::label or self::div or self::span][normalize-space(text())="CSS Class Name"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            },
            // Required Field Toggle
            requiredToggle: {
                yes: '//*[self::label or self::div or self::span][normalize-space()="Required"]/following::label[normalize-space()="Yes"][1]',
                no: '//*[self::label or self::div or self::span][normalize-space()="Required"]/following::label[normalize-space()="No"][1]',
            },

            // Read Only Toggle
            readOnlyCheckbox: '(//*[self::label or self::div or self::span][normalize-space(text())="Read Only"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            
            // Content Restriction
            contentRestriction: {
                minimum: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="min"]] | //input[@value="min"] >> visible=true >> nth=0',
                maximum: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="max"]] | //input[@value="max"] >> visible=true >> nth=0',
                lengthInputBox: '(//*[self::label or self::div or self::span][normalize-space(text())="Content Restriction"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                character: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="character"]] | //input[@value="character"] >> visible=true >> nth=0',
                word: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="word"]] | //input[@value="word"] >> visible=true >> nth=0',

                minCharMsg: '//span[contains(.,"Minimum character required.This field requires minimum 10 characters. Please add some more character.")]',
                maxCharMsg: '//span[contains(.,"Maximum character limit reached. Please shorten your texts.This field supports a maximum of 10 characters, and the limit is reached. Remove a few characters to reach the acceptable limit of the field.")]',
                minWordMsg: '//span[contains(.,"Minimum word required.This field requires minimum 10 words. Please add some more text.")]',
                maxWordMsg: '//span[contains(.,"Maximum word limit reached. Please shorten your texts.This field supports a maximum of 10 words, and the limit is reached. Remove a few words to reach the acceptable limit of the field.")]',


            },

            // Show Data in Post
            showDataInPost: {
                yes: '(//*[self::label or self::div or self::span][normalize-space()="Show Data in Post"]//..//..//*[@role="radio"][following-sibling::input[1][@value="yes"] or preceding-sibling::input[1][@value="yes"]] | //*[self::label or self::div or self::span][normalize-space()="Show Data in Post"]//..//..//input[@value="yes"])[1]',
                no: '(//*[self::label or self::div or self::span][normalize-space()="Show Data in Post"]//..//..//*[@role="radio"][following-sibling::input[1][@value="no"] or preceding-sibling::input[1][@value="no"]] | //*[self::label or self::div or self::span][normalize-space()="Show Data in Post"]//..//..//input[@value="no"])[1]',
                showData: "//a[contains(text(),'www.google.com')]",
            },

            // Hide Field Label in Post
            hideFieldLabel: {
                yes: '(//*[self::label or self::div or self::span][normalize-space()="Hide Field Label in Post"]//..//..//*[@role="radio"][following-sibling::input[1][@value="yes"] or preceding-sibling::input[1][@value="yes"]] | //*[self::label or self::div or self::span][normalize-space()="Hide Field Label in Post"]//..//..//input[@value="yes"])[1]',
                no: '(//*[self::label or self::div or self::span][normalize-space()="Hide Field Label in Post"]//..//..//*[@role="radio"][following-sibling::input[1][@value="no"] or preceding-sibling::input[1][@value="no"]] | //*[self::label or self::div or self::span][normalize-space()="Hide Field Label in Post"]//..//..//input[@value="no"])[1]',
                fieldlabel: '//*[self::label or self::div or self::span][normalize-space()="Website URL:"]'
            },

            // Visibility Options
            visibility: {
                everyone: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="everyone"]] | //input[@value="everyone"] >> visible=true >> nth=0',
                hidden: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="hidden"]] | //input[@value="hidden"] >> visible=true >> nth=0',
                loggedInOnly: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="logged_in"]] | //input[@value="logged_in"] >> visible=true >> nth=0',
                subscriptionOnly: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="subscribed_users"]] | //input[@value="subscribed_users"] >> visible=true >> nth=0',
            },

            // Conditional Logic
            conditionalLogic: {
                yes: '//*[self::label or self::div or self::span][normalize-space()="Conditional Logic"]/following::label[normalize-space()="Yes"][1]',
                no: '//*[self::label or self::div or self::span][normalize-space()="Conditional Logic"]/following::label[normalize-space()="No"][1]',
                selectField1: 'xpath=(//div[contains(@class,"cond-field")]//*[self::select or @role="combobox"])[1]',
                selectAction1: 'xpath=(//div[contains(@class,"cond-operator")]//*[self::select or @role="combobox"])[1]',
                setValue1: 'xpath=(//div[contains(@class,"cond-option")]//input[not(@type) or @type="text"])[1]',
                selectField2: 'xpath=(//div[contains(@class,"cond-field")]//*[self::select or @role="combobox"])[2]',
                selectAction2: 'xpath=(//div[contains(@class,"cond-operator")]//*[self::select or @role="combobox"])[2]',
                setValue2: 'xpath=(//div[contains(@class,"cond-option")]//input[not(@type) or @type="text"])[2]',
                addConditionButton: 'xpath=(//button[contains(@class,"wpuf-repeater-add")][ancestor::*[contains(@class,"cond-action-btns")]] | //div[@class="cond-option"]/following-sibling::div[1]/span[1])[1]',
                textfield: '//input[@name="text"]',
                inputUrl: '//input[@name="website_url"]',
                inputTextarea: '//textarea[@name="textarea"]',
                clickTitle: '//input[@name="post_title"]'
            },

            richText:{
                normal: 'role=radio[name="Normal"s] >> visible=true >> nth=0',
                rich: 'role=radio[name="Rich textarea"s] >> visible=true >> nth=0',
                teenyRich: 'role=radio[name="Teeny Rich textarea"s] >> visible=true >> nth=0',
            },

            // Field-specific options for different field types
            dropdownOptions: {
                showValues: '(//*[self::label or self::div or self::span][normalize-space(text())="Options"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                addOption: '(//div[@class="action-buttons hover:wpuf-cursor-pointer"]/following-sibling::div | //*[contains(@class,"plus-buttons")][@role="button"])[1]',
                optionLabel1:'(//*[self::span or self::div][normalize-space(text())="Label & Values"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[2]',
                optionValue1:'(//*[self::span or self::div][normalize-space(text())="Label & Values"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[3]',
                optionLabel2:'(//*[self::span or self::div][normalize-space(text())="Label & Values"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[5]',
                optionValue2:'(//*[self::span or self::div][normalize-space(text())="Label & Values"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[6]',
                selectDropdownOption:'(//select[@name="dropdown"])',
            },

            categoryTypeOptions: (type: string)=>{
                if(type === 'text'){
                    return 'xpath=//*[@role="option"][@data-value="text"] | //li[@value="text"] >> visible=true >> nth=0'
                }else if(type === 'checkbox'){
                    return 'xpath=//*[@role="option"][@data-value="checkbox"] | //li[@value="checkbox"] >> visible=true >> nth=0'
                }else if(type === 'multiselect'){
                    return 'xpath=//*[@role="option"][@data-value="multiselect"] | //li[@value="multiselect"] >> visible=true >> nth=0'
                }
            },

            categoryTypeShow: 'role=combobox[name="Type"s] >> visible=true >> nth=0',

            validateCategoryType: (type: string)=>{
                if(type === 'text'){
                    return '//input[@name="category" and @type="text"]'
                }else if(type === 'checkbox'){
                    return '//div[@data-type="tax-checkbox"]'
                }else if(type === 'multiselect'){
                    return '//select[contains(@class,"category multiselect")]'
                }
            },

            showSelectionType: 'role=combobox[name="Selection Type"s] >> visible=true >> nth=0',
            showSelectionTerms: 'xpath=//*[self::label or self::div or self::span][normalize-space()="Selection Terms"]/following::*[@role="combobox"][1] | //*[self::label or self::div or self::span][normalize-space()="Selection Terms"]//..//..//div[contains(@class,"selectize-input items")] >> visible=true >> nth=0',

            selectionTypeOptions: (type: string)=>{
                if(type === 'include'){
                    return 'xpath=//*[@role="option"][@data-value="include"] | //li[@value="include"] >> visible=true >> nth=0'
                }else if(type === 'exclude'){
                    return 'xpath=//*[@role="option"][@data-value="exclude"] | //li[@value="exclude"] >> visible=true >> nth=0'
                }
            },

            selectionTermsUncategorized: 'xpath=//*[@role="option"][normalize-space()="Uncategorized"] | //div[normalize-space(text())="Uncategorized"] >> visible=true >> nth=0',
            selectionTermsMusic: 'xpath=//*[@role="option"][normalize-space()="Music"] | //div[normalize-space(text())="Music"] >> visible=true >> nth=0',
            selectionTermsScience: 'xpath=//*[@role="option"][normalize-space()="Science"] | //div[normalize-space(text())="Science"] >> visible=true >> nth=0',

            validateSelectionTerm: (type: string)=>{
                if(type === 'uncategorized'){
                    return '//option[normalize-space(text())="Uncategorized"]'
                }else if(type === 'music'){
                    return '//option[normalize-space(text())="Music"]'
                }else if(type === 'science'){
                    return '//option[normalize-space(text())="Science"]'
                }
            },

            inLineListOptions: {
                showInLineList: '(//*[self::label or self::div or self::span][normalize-space(text())="Show in inline list"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                validateInLineList: '//label[@class="wpuf-radio-inline"]'
            },

            checkboxOptions: {
                addOption: '//button[normalize-space()="Add Option"]',
                optionLabel: (index: number) => `//input[@placeholder="Checkbox Option ${index}"]`,
                optionValue: (index: number) => `//input[@placeholder="Checkbox Value ${index}"]`,
                selectedByDefault: (index: number) => `//input[@name="selected_default"][${index}]`,
            },

            // Numeric Field Options
            numericOptions: {
                step: '(//*[self::label or self::div or self::span][normalize-space(text())="Step"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                minValue: '(//*[self::label or self::div or self::span][normalize-space(text())="Min Value"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                maxValue: '(//*[self::label or self::div or self::span][normalize-space(text())="Max Value"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',

                validateMinValue: (expectedMin: string) => `//input[@min="${expectedMin}"]`,
                validateMaxValue: (expectedMax: string) => `//input[@max="${expectedMax}"]`,
                validateStep: (expectedStep: string) => `//input[@step="${expectedStep}"]`,
            },

            // Date/Time Field Options
            dateTimeOptions: {
                minDate: '(//*[self::label or self::div or self::span][normalize-space(text())="Enter minimum date"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                maxDate: '(//*[self::label or self::div or self::span][normalize-space(text())="Enter maximum date"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                format: '(//*[self::label or self::div or self::span][normalize-space(text())="Date Format"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                enableInput: '(//label[normalize-space()="Enable time input"])[1]',
                asPublishTime: '(//label[normalize-space()="Set this as publish time input"])[1]',
                validateFormat: (format: string) => `//input[@data-format="${format}"]`,
                validateTimeInput: '//div[@class="ui-timepicker-div"]',
                validatePostPublishTime: (postTitle: string) => `//a[normalize-space()='${postTitle}']//..//..//..//td[normalize-space()='Published1970/01/01 at 2:07 am']`,
                validateMinDate: (expectedMinDate: string) => `//input[@data-mintime="${expectedMinDate}"]`,
                validateMaxDate: (expectedMaxDate: string) => `//input[@data-maxtime="${expectedMaxDate}"]`,
            },

            // Date/Time Field Options
            timeFieldOptions: {
                format: 'xpath=//*[@role="radio"][following-sibling::input[1][@value="H:i:s"]] | //input[@value="H:i:s"] >> visible=true >> nth=0',
                interval: '(//*[self::label or self::div or self::span][normalize-space(text())="Time Intervals (in minutes)"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                validateInterval: '//select[contains(@class,"time_field")]',

                
            },

            // File Upload Options
            fileUploadOptions: {
                maxFiles: '(//*[self::label or self::div or self::span][normalize-space(text())="Max. files"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
            },

            // Image Upload Options
            imageUploadOptions: {
                maxFileSize: '(//*[self::label or self::div or self::span][normalize-space(text())="Max. file size"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                buttonText: '(//*[self::label or self::div or self::span][normalize-space(text())="Button Label"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                validateButtonText: (buttonText: string) => `//a[normalize-space(text())="${buttonText}"]`,
            },

            // Google Map Options
            googleMapOptions: {
                defaultLocation: '//*[self::label or self::div or self::span][normalize-space()="Default Location"]/following-sibling::input',
                zoom: '//*[self::label or self::div or self::span][normalize-space()="Zoom Level"]/following-sibling::input',
                showAddress: '//*[self::label or self::div or self::span][normalize-space()="Show address search box"]/following-sibling::input[@type="checkbox"]',
            },

            // Address Field Options
            addressOptions: {
                showAddressLine2: '(//div[contains(@class,"panel-field-opt-address")]//label[@class="ml-1"][normalize-space()="Address Line 2"] | //label[@class="wpuf-ml-1"][normalize-space()="Address Line 2"])[1]',
                makeRequired: '(//div[contains(@class,"panel-field-opt-address")]//label[normalize-space()="Required"]/preceding::*[@role="checkbox"][1] | //input[@name="street_address2_required"])[1]',
                defaultInput: '(//div[contains(@class,"panel-field-opt-address")]//label[normalize-space()="Default"]/following-sibling::div[1]//input | (//div[@class="wpuf-mt-2 wpuf-mr-2"]//input)[2])[1]',
                placeHolderInput: '(//div[contains(@class,"panel-field-opt-address")]//label[normalize-space()="Placeholder"]/following-sibling::div[1]//input | (//div[contains(@class,"default-item wpuf-mr-1")]/following-sibling::div)[2]//div[1]//input)[1]',
                validateRequired: '//*[self::label or self::div or self::span][normalize-space(text())="Address Line 2"]//span[@class="required"]',
                validateDefault: (defaultValue: string)=> `//input[@value="${defaultValue}"]`,
                validatePlaceHolder: (placeHolder: string)=> `//input[@placeholder='${placeHolder}']`,
            },

            // Country List Options
            countryOptions: {
                defaultCountry: 'xpath=//*[normalize-space(text())="Default Country"]/following::*[@role="combobox"][1] | //select[@class="default-country selectized"]/following-sibling::div[1] >> visible=true >> nth=0',
                // React: plugin-ui Select options carry data-value; the multi-select options only show the name.
                selectCountry: (country: string) => {
                    const names: Record<string, string> = { BD: 'Bangladesh', CA: 'Canada', GB: 'United Kingdom', US: 'United States' };
                    return `xpath=//*[@role="option"][@data-value="${country}"] | //*[@role="option"][normalize-space()="${names[country] || country}"] | //div[contains(@class,"selectize-dropdown-content")]//div[contains(@class,"option")][@data-value="${country}"] >> visible=true >> nth=0`;
                },
                selectedCountry: (country: string) => `//select[@name="country_list"]//option[@value="${country}"]`,
                hideThese: '//button[normalize-space(text())="Hide these"]',
                showThese: '//button[normalize-space(text())="Only show"]',
                openCountryList: '//select[@name="country_list"]',
                selectHiddenCountry: 'xpath=//*[contains(@class,"country-list-selector")]//*[@role="combobox"] >> visible=true >> nth=0',
                selectOnlyShowCountry: 'xpath=//*[contains(@class,"country-list-selector")]//*[@role="combobox"] >> visible=true >> nth=0',
            },

            // Phone Field Options
            phoneOptions: {
                format: '//*[self::label or self::div or self::span][normalize-space()="Phone Format"]/following-sibling::select',
            },

            // reCaptcha Options
            reCaptchaOptions: {
                type: '//*[self::label or self::div or self::span][normalize-space()="reCaptcha Type"]/following-sibling::select',
                theme: '//*[self::label or self::div or self::span][normalize-space()="Theme"]/following-sibling::select',
                size: '//*[self::label or self::div or self::span][normalize-space()="Size"]/following-sibling::select',
            },

            // Section Break Options
            sectionBreakOptions: {
                description: '//*[self::label or self::div or self::span][normalize-space()="Description"]/following-sibling::textarea',
            },

            // Custom HTML Options
            customHtmlOptions: {
                htmlContent: '//*[self::label or self::div or self::span][normalize-space()="HTML Content"]/following-sibling::textarea',
            },

            // Show Icons Options
            icons: {
                showIcons: '(//*[self::label or self::div or self::span][normalize-space(text())="Show Icon"]/following::*[@role="radio" or @role="checkbox" or (self::input and not(@type="radio") and not(@type="checkbox") and not(@type="hidden") and not(@aria-hidden="true"))])[1]',
                clickFieldIcon: '(//div[contains(@class,"panel-field-opt-icon-selector")]//div[contains(@class,"option-fields-section")]/div[@role="button"] | //*[self::label or self::div or self::span][normalize-space()="Field Icon"]//..//..//div[@class="option-fields-section wpuf-relative"])[1]',
                searchIcons: '//input[@placeholder="Search icons... (e.g., user, email, home)"]',
                // React icon grid: the <i> carries size classes too, so match the class token.
                envelope: '(//*[contains(@class,"wpuf-icon-grid-item")][.//i[contains(concat(" ",normalize-space(@class)," ")," fa-envelope ")]])[1]',
                validateEnvelope: '//i[contains(@class,"fas fa-envelope")]'
            },

            // Ratings Options
        },

        // Frontend Validation Selectors
        frontend: {
            fieldContainer: (fieldName: string) => `//li[@data-label="${fieldName}"]`,
            fieldInput: (fieldName: string) => `//input[@name="${fieldName}"]`,
            fieldLabel: (fieldName: string) => `//label[normalize-space()='${fieldName}']`,
            requiredIndicator: (fieldLabel: string) => `//li[@data-label="${fieldLabel}"]//span[contains(@class,"required")]`,
            placeHolderText: (placeHolder: string) => `//input[@placeholder="${placeHolder}"]`,
            defaultValue: (fieldLabel: string, defaultValue: string) => `//li[@data-label="${fieldLabel}"]//input[@value="${defaultValue}"]`,
        },
    },

    /*********************************************/
    /********** @Subscription Selectors **********/
    /*********************************************/

    subscription: {

        // Subscription List Page (Admin)
        listPage: {
            createNewPackButton: '//button[contains(text(),"Create New") or contains(text(),"Add Subscription")]',
            threeDotButton: (packName: string)=>`xpath=(//div[contains(text(),"${packName}")]/ancestor::div[.//button[@aria-label="Actions"]][1]//button[@aria-label="Actions"] | //div[contains(text(),"${packName}")]//..//..//..//div[contains(@class,"wpuf-cursor-pointer wpuf-flex")]/following-sibling::div[1])[1]`,
            threeDotButtonTrash: (packName: string)=>`xpath=(//div[contains(text(),'${packName}')]/ancestor::div[.//button[@aria-label='Actions']][1]//button[@aria-label='Actions'] | //div[contains(text(),'${packName}')]//..//..//..//div[contains(@class,'wpuf-flex wpuf-justify-between')]/following-sibling::div[1])[1]`,
            threeDotButtonDraft: (packName: string)=>`xpath=(//div[contains(text(),'${packName}')]/ancestor::div[.//button[@aria-label='Actions']][1]//button[@aria-label='Actions'] | //div[contains(text(),'${packName}')]//..//..//..//div[contains(@class,'wpuf-flex wpuf-justify-between')]/following-sibling::div[1])[1]`,
            editButton: 'xpath=//*[@role="menuitem"][normalize-space()="Edit"] >> visible=true >> nth=0',
            quickEditButton: 'xpath=//*[@role="menuitem"][normalize-space()="Quick Edit"] >> visible=true >> nth=0',
            darftButton: 'xpath=//*[@role="menuitem"][normalize-space()="Draft"] >> visible=true >> nth=0',
            trashButton: 'xpath=//*[@role="menuitem"][normalize-space()="Trash"] >> visible=true >> nth=0',
            trashTab1: 'role=button[name="Trash 1"s]',
            draftTab1: 'role=button[name="Drafts 1"s]',
            confirmTrashButton: 'xpath=(//*[@role="alertdialog" or @role="dialog"]//button[normalize-space()="Trash"] | //div[contains(@class,"swal2-popup")]//button[normalize-space()="Trash"])[1]',
            publishButton: 'xpath=//*[@role="menuitem"][normalize-space()="Publish"] >> visible=true >> nth=0',
            restoreButton: 'xpath=//*[@role="menuitem"][normalize-space()="Restore"] >> visible=true >> nth=0',
            deletePermanentlyButton: 'xpath=//*[@role="menuitem"][normalize-space()="Delete Permanently"] >> visible=true >> nth=0',
            confirmDeleteButton: 'xpath=(//*[@role="alertdialog" or @role="dialog"]//button[normalize-space()="Delete"] | //button[normalize-space()="Delete"])[1]',
        },

        // New Subscription Pack Page (React UI)
        newPackPage: {
            // Overview Section
            planNameInput: '//input[@id="plan-name" or @name="plan-name"]',
            planSummaryInput: '//textarea[@id="plan-summary" or @name="plan-summary"]',
            
            // Access & Visibility Section
            planSlugInput: '//input[@id="plan-slug" or @name="plan-slug"]',
            sortOrderInput: '//input[@id="sort-order" or @name="sort_order"]',

            // Post Expiration section
            enablePostExpirationToggle: 'xpath=//label[normalize-space()="Enable Post Expiration"]/following::*[@role="switch"][1] | //button[@id="post-expiration"] >> visible=true >> nth=0',
            postExpirationTimeInput: '//input[@id="post-expiration-value"]',
            postExpirationUnitSelect: 'xpath=//*[@role="combobox"][@id="post-expiration-unit"] | //select[@id="post-expiration-unit"] >> visible=true >> nth=0',
            expiredPostStatusSelect: 'xpath=//*[@role="combobox"][@id="post-status"] | //select[@id="post-status"] >> visible=true >> nth=0',
            sendExpirationMailToggle: 'xpath=//label[normalize-space()="Send Expiration Mail"]/following::*[@role="switch"][1] | //button[@id="is-send-mail"] >> visible=true >> nth=0',
            expirationMessageTextarea: '//textarea[@id="expiration-message"]',
            enablepostNumberRollback: 'xpath=//label[normalize-space()="Enable Post Number Rollback"]/following::*[@role="switch"][1] | //button[@id="post-number-rollback"] >> visible=true >> nth=0',
            
            // Payment Settings Section
            billingAmountInput: '//input[@id="billing-amount"]',
            expirationNumberInput: '//input[@id="wpuf-expiration-number"]',
            expirationPeriodSelect: 'xpath=//*[@role="combobox"][@id="subs-expiration-unit"] | //select[@id="subs-expiration-unit"] >> visible=true >> nth=0',
            enableRecurringPaymentToggle: 'xpath=//label[normalize-space()="Enable Recurring Payment"]/following::*[@role="switch"][1] | //button[@id="recurring_pay"] >> visible=true >> nth=0',
            enableRecurringToggle: 'xpath=//label[normalize-space()="Enable Recurring Payment"]/following::*[@role="switch"][1] | //button[@id="recurring_pay"] >> visible=true >> nth=0',
            billingCycleInput: '//input[@id="billing_cycle_number"]',
            cyclePeriodSelect: 'xpath=//*[@role="combobox"][@id="cycle_period"] | //select[@id="cycle_period"] >> visible=true >> nth=0',
            stopCycleToggle: 'xpath=//label[normalize-space()="Stop Billing Cycle"]/following::*[@role="switch"][1] | //button[@id="stop-cycle"] >> visible=true >> nth=0',
            billingLimitInput: '//input[@id="billing-limit"]',
            enableTrialToggle: 'xpath=//label[normalize-space()="Enable Trial"]/following::*[@role="switch"][1] | //button[@id="trial"] >> visible=true >> nth=0',
            trialPeriodInput: '//input[@id="trial-period-value"]',
            trialPeriodUnitSelect: 'xpath=//*[@role="combobox"][@id="trial-period-unit"] | //select[@id="trial-period-unit"] >> visible=true >> nth=0',
            featuredItemCheckbox: '//input[@name="is_featured_item"]',
            
            // Content Limit Section
            maxPostsInput: '//input[@id="number-of-posts"]',
            maxPagesInput: '//input[@id="number-of-pages"]',
            maxUserReqInput: '//input[@id="number-of-user-requests"]',

            // Design Elements
            maxReusableBlock: '//input[@id="number-of-blocks"]',
            maxTemplates: '//input[@id="number-of-templates"]',
            maxTemplateParts: '//input[@id="number-of-template-parts"]',
            maxNavigationMenus: '//input[@id="number-of-menus"]',

            //Additional options
            maxFeaturedItemsInput: '//input[@id="number-of-featured-items"]',
            removeFeaturedOnExpiryToggle: 'xpath=//label[normalize-space()="Remove Featured Item"]/following::*[@role="switch"][1] | //button[@id="remove-featured-item"] >> visible=true >> nth=0',
            postCategoriesSelect: 'xpath=//button[starts-with(normalize-space(),"Post Categories")]/following::*[@role="combobox"][1] | //div[@aria-controls="category-multiselect-options"] >> visible=true >> nth=0',
            postCategoriesDropdown: 'xpath=//*[@role="listbox"] | //div[@id="category-dropdown"] >> visible=true >> nth=0',
            selectCategory: 'xpath=//*[@role="option"] | //ul[@id="category-multiselect-options"]//li[1] >> visible=true >> nth=0',
            postViewCategoriesSelect: 'xpath=//button[starts-with(normalize-space(),"Post View Categories")]/following::*[@role="combobox"][1] | //div[@aria-controls="view_category-multiselect-options"] >> visible=true >> nth=0',
            postViewCategoriesDropdown: 'xpath=//*[@role="listbox"] | //div[@id="view_category-dropdown"] >> visible=true >> nth=0',
            selectViewCategory: 'xpath=//*[@role="option"] | //ul[@id="view_category-multiselect-options"]//li[1] >> visible=true >> nth=0',
            // Action Buttons
            savePackButton: '//button[normalize-space()="Save"]',
            updatePackButton: '//button[normalize-space()="Update"]',
            publishPackButton: 'xpath=//*[@role="menuitem"][normalize-space()="Publish"] | //span[normalize-space()="Publish"] >> visible=true >> nth=0',
            cancelButton: '//button[contains(text(),"Cancel")]',
            draftPackButton: 'xpath=//*[@role="menuitem"][normalize-space()="Save as Draft"] | //span[normalize-space(text())="Save as Draft"] >> visible=true >> nth=0',
            
            // Navigation Tabs
            subscriptionDetailsTab: '//button[normalize-space()="Subscription Details"]',
            paymentSettingsTab: '//button[normalize-space()="Payment Settings"]',
            advancedConfigTab: '//button[normalize-space()="Advanced Configuration"]',
            
            // Sub-Sections
            subscriptionDetailsSection: '//button[normalize-space()="Subscription Details"]',
            overviewSection: '(//span[contains(text(),"Overview")] | //button[@aria-controls][starts-with(normalize-space(),"Overview")])[1]',
            accessAndVisibilitySection: '(//span[contains(text(),"Access and Visibility")] | //button[@aria-controls][starts-with(normalize-space(),"Access and Visibility")])[1]',
            postExpirationSection: '(//span[contains(text(),"Post Expiration")] | //button[@aria-controls][starts-with(normalize-space(),"Post Expiration")])[1]',
            paymentDetailsSection: '//button[normalize-space()="Payment Settings"]',
            advanceConfigurationSection: '//button[normalize-space()="Advanced Configuration"]',
            contentLimitSection: '(//span[contains(text(),"Content Limit")] | //button[@aria-controls][starts-with(normalize-space(),"Content Limit")])[1]',
            designElementSection: '(//span[contains(text(),"Design Elements")] | //button[@aria-controls][starts-with(normalize-space(),"Design Elements")])[1]',
            additionalOptionsSection: '(//span[contains(text(),"Additional Options")] | //button[@aria-controls][starts-with(normalize-space(),"Additional Options")])[1]',
            postCategoriesSection: '(//span[contains(text(),"Post Categories")] | //button[@aria-controls][starts-with(normalize-space(),"Post Categories")])[1]',
            postViewCategoriesSection: '(//span[contains(text(),"Post View Categories")] | //button[@aria-controls][starts-with(normalize-space(),"Post View Categories")])[1]',

            // Validation
            validatePackCreated: (packName: string) => `//div[contains(text(),'${packName}')]`,
            validatePackFree: (packName: string) => `//div[contains(text(),'${packName}')]//..//..//..//p[normalize-space(text())="Free"]`,
            validatePackPaid: (packName: string, packPrice: string)=> `//div[contains(text(),'${packName}')]//..//..//..//p[normalize-space(text())='$${packPrice}']`,
            validateRecurringPackPaid: (packName: string, packPrice: string, packExpirationNumber:string, packExpirationPeriod:string)=> `//div[contains(text(),'${packName}')]//..//..//..//p[normalize-space(text())='$${packPrice} every ${packExpirationNumber} ${packExpirationPeriod}(s)']`,
            recurringPackIcon: (packName: string)=> `//div[contains(text(),'${packName}')]//..//..//..//div[3]//*[name()='svg']`,
            // The status is the kit Badge (a span since 5756aba2), so any tag.
            validatePackPublished: (packName)=> `//div[contains(text(),"${packName}")]//..//..//..//*[normalize-space(text())="Published"]`,
            validatePackDrafted: (packName)=> `//div[contains(text(),"${packName}")]//..//..//..//*[normalize-space(text())="Draft"]`,
            validatePackTrashed: (packName)=> `//div[contains(text(),"${packName}")]//..//..//..//*[normalize-space(text())="Trash"]`,
            // React tabs: a button whose name is the label plus the count (no count when 0).
            validateAllPackCount: (packCount: number)=> packCount ? `role=button[name="All Subscriptions ${packCount}"s]` : 'role=button[name="All Subscriptions"s]',
            validatePublishedPackCount: (packCount: number)=> packCount ? `role=button[name="Published ${packCount}"s]` : 'role=button[name="Published"s]',
            validateDraftPackCount: (packCount: number)=> packCount ? `role=button[name="Drafts ${packCount}"s]` : 'role=button[name="Drafts"s]',
            validateTrashPackCount: (packCount: number)=> packCount ? `role=button[name="Trash ${packCount}"s]` : 'role=button[name="Trash"s]',
            validateSubscriberscount: (packName: string, subscribersCount: number)=> `//div[contains(text(),"${packName}")]//..//..//..//p[normalize-space(text())="Total Subscribers"]//..//a[normalize-space(text())="${subscribersCount}"]`,
            packPreferences: 'role=button[name="Preferences"s]',
            inputColor: 'xpath=//*[normalize-space(text())="Button Color"]/following::input[not(@type) or @type="text"][1] | //input[@type="color"]/following-sibling::input[1] >> visible=true >> nth=0',
            savePreferencesButton: '//button[normalize-space()="Save Preferences"]',
            // React plan card: the saved colour is the card's --wpuf-plan-accent (the button paints with it).
            buyNowButtonColorFE: (packName:string, buttonColor: string)=> `//article[contains(@class,"wpuf-plan-card")][.//h3[normalize-space()='${packName}']][contains(@style,"--wpuf-plan-accent") and contains(@style,"${buttonColor}")]`,
        },

        // Frontend Subscription Page
        frontendPage: {
            subscriptionPageTitle: '//h1[contains(text(),"Subscription")]',
            packTitleFE: (packName: string) => `//h3[normalize-space(text())='${packName}']`,
            packPriceFE: (packName: string, packPrice: string) => `//h3[normalize-space(text())='${packName}']//..//..//span[normalize-space(text())='$${packPrice}.00']`,
            validateRecurringPackPaid: (packName: string, packPrice: string, packExpirationNumber:string, packExpirationPeriod:string, installments:string)=> `//h3[normalize-space(text())='${packName}']//..//..//span[normalize-space(text())='$${packPrice}.00']//..//span[2]//div[normalize-space(text())='Every ${packExpirationNumber} ${packExpirationPeriod.charAt(0).toUpperCase() + packExpirationPeriod.slice(1)}s, for ${installments} installments']`,
            packDescriptionFE: (packName: string, packDescription: string) => `//h3[normalize-space(text())='${packName}']//..//..//p[normalize-space(text())='${packDescription}']`,
            freePackButton: (packName: string)=>  `//h3[normalize-space(text())='${packName}']//..//..//span[normalize-space(text())='Free']`,
            buyNowButton: (packName:string) =>`//h3[normalize-space(text())='${packName}']//..//..//a[normalize-space(text())='Buy Now']`,
            expandFeaturesButton: (packName: string) => `//h3[normalize-space(text())='${packName}']//..//..//button[contains(text(),"more features")]`,
            lessFeaturesButton: (packName: string) => `//h3[normalize-space(text())='${packName}']//..//..//button[contains(text(),"See less")]`,
            signUpButton: (packName: string) => `//div[contains(@class,"wpuf-pricing-box")]//h3[contains(text(),"${packName}")]//..//..//a[contains(text(),"Sign Up")]`,
            freeButton: (packName: string) => `//div[contains(@class,"wpuf-pricing-box")]//h3[contains(text(),"${packName}")]//..//..//a[contains(text(),"Free")]`,
            packExpiration: (packName: string) => `//div[contains(@class,"wpuf-pricing-box")]//h3[contains(text(),"${packName}")]//..//..//span[contains(@class,"expiration") or contains(@class,"cycle")]`,
            currentPackIndicator: '//div[contains(@class,"current-pack") or contains(text(),"Active")]',
            cancelSubscriptionButton: '//input[@name="wpuf_user_subscription_cancel" or @value="Cancel"]',
            cancelSubscriptionForm: '//form[@id="wpuf_cancel_subscription"]',
            noSubscriptionMessage: '//p[contains(text(),"not subscribed")]',
            allPackCards: '//div[contains(@class,"wpuf-pricing-box") or contains(@class,"pack-card")]',
            // React plan card: amount and cycle side by side in the price row.
            oneTimePayment: (packName: string, packPrice: string) => `//article[contains(@class,"wpuf-plan-card")][.//h3[normalize-space()='${packName}']]//div[contains(@class,"wpuf-plan-card__price")][span[contains(@class,"wpuf-plan-card__amount")][normalize-space()='$${packPrice}.00']][span[contains(@class,"wpuf-plan-card__cycle")][normalize-space()="One time payment"]]`,

        },

        // Account Page - Subscription Tab
        accountPage: {
            packNotExistsMsg: '//p[normalize-space(text())="Your subscription pack is not exists. Please contact admin."]',
            subscriptionMsg: '//p[normalize-space(text())="View your current subscription status, billing details, and renewal information."]',
            // React plan card (kit plans.jsx): title in the h3, price / dates as facts,
            // "What is left" as value + label, the plan features behind "Show all (n)".
            detailsCard: '//section[contains(@class,"wpuf-plan-status")]',
            currentPackName: (packName: string)=> `//section[contains(@class,"wpuf-plan-status")]//h3[normalize-space()='${packName}']`,
            freePack: '//section[contains(@class,"wpuf-plan-status")]//span[contains(@class,"wpuf-plan-card__amount")][normalize-space()="Free"]',
            currentPackPrice: (packPrice: string)=> `//section[contains(@class,"wpuf-plan-status")]//span[contains(@class,"wpuf-plan-card__amount")][contains(normalize-space(), "$${packPrice}")]`,
            showDetailsButton: '//button[contains(@class,"wpuf-plan-status__more")][@aria-expanded="false"]',
            hideDetailsButton: '//button[contains(@class,"wpuf-plan-status__more")][@aria-expanded="true"]',
            remainingPosts: (postType: string) => `(//div[contains(@class,"wpuf-plan-status__limits")]//li[span[normalize-space()="${postType}"]]/strong)[1]`,
            showedLimits: (packName: string, featureName: string, count: string) => `(//section[contains(@class,"wpuf-plan-status")][.//h3[normalize-space()='${packName}']]//div[contains(@class,"wpuf-plan-status__limits")]//li[strong[normalize-space()="${count}"]][span[normalize-space()="${featureName}"]])[1]`,
            expirationDate: '//section[contains(@class,"wpuf-plan-status")]//div[contains(@class,"wpuf-plan-fact")][dt[contains(normalize-space(),"Expires")]]/dd',
            subscriptionStatus: '//section[contains(@class,"wpuf-plan-status")]//span[contains(@class,"wpuf-pill")]',
            noSubscriptionPara: '//p[normalize-space()="You have not subscribed to any package yet."]',
            packDetails: '//div[contains(@class,"pack-details") or contains(@class,"subscription-details")]',
            postCountTable: '//table[contains(@class,"post-count") or contains(@class,"remaining")]',
            cancelButton: '//input[@name="wpuf_user_subscription_cancel"]',
            confirmModal: '//button[normalize-space()="Yes"]',
            upgradeButton: '//a[contains(text(),"Upgrade") or contains(text(),"Change Plan")]',
        },

        // Payment Page
        paymentPage: {
            paymentPageTitle: '//h1[contains(text(),"Payment")]',
            freePackActivcateMsg: '//div[normalize-space(text())="Your Free package has been activated. Enjoy!"]',
            packSummary: '//div[contains(@class,"pack-summary") or contains(@class,"order-summary")]',
            totalAmount: '//span[contains(@class,"total") or contains(@class,"amount")]',
            bankPaymentOption: '//input[@value="bank" or @id="payment_method_bank"]',
            paypalPaymentOption: '//input[@value="paypal" or @id="payment_method_paypal"]',
            stripePaymentOption: '//input[@value="stripe" or @id="payment_method_stripe"]',
            
            cancelButton: '//a[contains(text(),"Cancel") or contains(@class,"cancel")]',
        },
    },

    // React settings screen (WPUF > Settings). Selectors are text/role/structure
    // based since the React app uses Tailwind classes (no test ids). The POM
    // (pages/settingsScreen.ts) builds dynamic tab/sub-tab/field locators on top.
    settingsScreen: {
        root: '#wpuf-settings-root',
        loadingSkeleton: '#wpuf-settings-root .wpuf-animate-pulse, #wpuf-settings-root .animate-pulse',
        nav: '#wpuf-settings-root nav.wpuf-space-y-1, #wpuf-settings-root nav',
        // Panel/tab title is the gray-900 h2. The other h2 is the app header
        // ("WP User Frontend [Pro]"), which has no gray-900 class.
        panelTitle: '#wpuf-settings-root h2.wpuf-text-gray-900, #wpuf-settings-root h2.text-gray-900',
        // The page title is fixed ("Settings", FlyHR); the open tab is the current nav item.
        activeNavItem: '#wpuf-settings-root nav button[aria-current="page"]',
        searchInput: '#wpuf-settings-root input[placeholder^="Search"]',
        searchClearButton: '#wpuf-settings-root button[aria-label="Clear search"]',
        noResults: 'text=No settings found',
        savedIndicator: 'text=Saved',
        // Modal h1 by id — `text=Unsaved Changes` is ambiguous (also matches the
        // footer "Unsaved changes" badge + the modal body copy).
        // React: the shared Modal (base-ui dialog named by its title).
        unsavedModalTitle: 'role=alertdialog[name="Unsaved Changes"]',
        unsavedDiscardButton: 'button:has-text("Discard Changes")',
        unsavedContinueButton: 'button:has-text("Continue Editing")',
        // The classic screen is gone: these must not exist any more (SR0021).
        classicViewLink: 'a:text-is("Classic view")',
        legacyScreenWrap: '.wpuf-settings-wrap',
        // Colour fields: develop's native colour input, or the shared ColorPicker's swatch button.
        colorPicker: '#wpuf-settings-root input[type="color"], #wpuf-settings-root button:has(> span.rounded-full + span.font-mono)',
    },

    /*************************************/
    /***** Onboarding Wizard Selectors ***/
    /*************************************/

    // Welcome page (#/welcome) and the one-time welcome over the admin app.
    welcome: {
        root: '//div[@id="wpuf-welcome-root"]',
        heading: '//div[@id="wpuf-welcome-root"]//h1',
        createFormButton: '//div[@id="wpuf-welcome-root"]//button[normalize-space()="Create Your First Form"]',
        setupButton: '//div[@id="wpuf-welcome-root"]//button[normalize-space()="Run the Setup Wizard"]',
        sections: '//div[@id="wpuf-welcome-root"]//section[contains(@class,"wpuf-welcome-section")]',
        sectionLinks: '//div[@id="wpuf-welcome-root"]//section[contains(@class,"wpuf-welcome-section")]//a',
        proCard: '//div[@id="wpuf-welcome-root"]//section[contains(@class,"wpuf-welcome-pro")]',
        videoButton: '//div[@id="wpuf-welcome-root"]//button[contains(@class,"wpuf-welcome-video")]',
        videoFrame: '//iframe[contains(@src,"youtube-nocookie.com/embed/")]',
        intro: '//div[contains(@class,"wpuf-intro")][@role="dialog"]',
        introHeading: '//h1[@id="wpuf-intro-title"]',
        introButton: '//button[contains(@class,"wpuf-intro-button")]',
    },

    onboarding: {
        // Entry point on User Frontend > Tools
        entry: {
            // React Tools route (#/tools): the "Onboarding" tool card, its button and its warning Notice.
            onboardingBox: '(//*[self::h2 or self::h3][normalize-space()="Onboarding"]/ancestor::div[.//button][1])',
            startButton: '(//*[self::h2 or self::h3][normalize-space()="Onboarding"]/ancestor::div[.//button][1])//button',
            rerunWarning: '(//*[self::h2 or self::h3][normalize-space()="Onboarding"]/ancestor::div[.//button][1])//*[@data-slot="alert"][@data-wpuf-tone="warning"]',
        },

        // Shared chrome
        chrome: {
            // The wizard is the admin app route #/onboarding/:step (full screen).
            wizardBody: '//body[contains(@class,"wpuf-onboarding-open")]',
            heading: '//div[contains(@class,"wpuf-onboarding-head")]//h1',
            stepRail: '//ol[contains(@class,"wpuf-onboarding-steps")]',
            railItems: '//ol[contains(@class,"wpuf-onboarding-steps")]/li',
            railLabels: '//ol[contains(@class,"wpuf-onboarding-steps")]//span[contains(@class,"wpuf-step-label")]',
            activeStep: '//ol[contains(@class,"wpuf-onboarding-steps")]/li[contains(@class,"is-active")]//span[contains(@class,"wpuf-step-label")]',
            doneMarkers: '//ol[contains(@class,"wpuf-onboarding-steps")]/li[contains(@class,"is-done")]',
            markerCheckIcon: '//ol[contains(@class,"wpuf-onboarding-steps")]//*[contains(@class,"wpuf-step-marker")]/*[name()="svg"]',
            exitLink: '//a[contains(@class,"wpuf-onboarding-exit")]',
            // The step's own submit in the fixed action bar.
            continueButton: '//div[contains(@class,"wpuf-onboarding-footer")]//button[@data-action="next"]',
            skipLink: '//div[contains(@class,"wpuf-onboarding-footer")]//button[@data-action="skip"]',
            previousButton: '//div[contains(@class,"wpuf-onboarding-footer")]//button[@data-action="previous"]',
        },

        // Step 1: what you need
        features: {
            cards: '//div[contains(@class,"wpuf-onboarding-grid")]//*[contains(@class,"wpuf-onboarding-card")]',
            postFormCheckbox: '//*[@role="checkbox"][@data-name="features"][@data-value="post_form"]',
            registrationCheckbox: '//*[@role="checkbox"][@data-name="features"][@data-value="registration"]',
            userDirectoryCheckbox: '//*[@role="checkbox"][@data-name="features"][@data-value="user_directory"]',
            paymentsCheckbox: '//*[@role="checkbox"][@data-name="features"][@data-value="payments"]',
        },

        // Step 2: post form
        postForm: {
            templateSelect: '//*[@id="wpuf-onboarding-template"]',
            enablePostEdit: '//*[@data-name="enable_post_edit"]//*[@role="switch"]',
            enablePostDelete: '//*[@data-name="enable_post_del"]//*[@role="switch"]',
        },

        // Step 3: login and registration
        registration: {
            loginPageSelect: '//*[@id="wpuf-onboarding-login-page"]',
            regPageSelect: '//*[@id="wpuf-onboarding-reg-page"]',
            accountPageSelect: '//*[@id="wpuf-onboarding-account-page"]',
            autologinCheckbox: '//*[@data-name="autologin_after_registration"]//*[@role="switch"]',
            layoutChange: '//button[@data-action="change-layout"]',
            loginLayoutRadio: '//*[contains(@class,"wpuf-onboarding-layouts")]//*[@role="radio"]',
            proBadge: '//img[contains(@class,"wpuf-onboarding-pro-badge")]',
        },

        // Step 4: settings
        common: {
            installPages: '//*[@data-name="install_wpuf_pages"]//*[@role="switch"]',
            hideAdminBar: '//*[@data-name="hide_admin_bar"]//*[@role="switch"]',
            addLogoutMenu: '//*[@data-name="add_logout_menu"]//*[@role="switch"]',
            enablePayments: '//*[@data-name="enable_payment"]//*[@role="switch"]',
            gatewayGrid: '//div[contains(@class,"wpuf-onboarding-grid") and contains(@class,"is-thirds")]',
            gatewayCards: '//div[contains(@class,"is-thirds")]/*[contains(@class,"wpuf-onboarding-card")]',
            gatewayIcons: '//div[contains(@class,"is-thirds")]//span[contains(@class,"wpuf-onboarding-card-icon")]',
            gatewayProCard: '//div[contains(@class,"is-thirds")]//a[contains(@class,"is-pro")]',
            gatewayProBadge: '//div[contains(@class,"is-thirds")]//img[contains(@class,"wpuf-onboarding-pro-badge")]',
            bankGateway: '//*[@role="checkbox"][@data-name="active_gateways"][@data-value="bank"]',
            paypalGateway: '//*[@role="checkbox"][@data-name="active_gateways"][@data-value="paypal"]',
        },

        // Step 5: plugins
        plugins: {
            pluginCards: '//*[@role="checkbox"][@data-name="plugins"]',
            errorBadge: '//span[contains(@class,"wpuf-onboarding-badge") and contains(@class,"is-error")]',
        },

        // Step 6: ready
        ready: {
            checklist: '//ul[contains(@class,"wpuf-onboarding-checklist")]',
            checklistRows: '//ul[contains(@class,"wpuf-onboarding-checklist")]/li',
            shareCheckbox: '//*[@data-name="share_essentials"]//*[@role="switch"]',
            finishButton: '//div[contains(@class,"wpuf-onboarding-footer")]//button[@data-action="next"]',
        },
    },

    // WPUF admin menus, used to assert a switched-off feature hides its menu
    onboardingMenus: {
        parentMenu: '//li[@id="toplevel_page_wp-user-frontend"]',
        submenuLinks: '//li[@id="toplevel_page_wp-user-frontend"]//ul[contains(@class,"wp-submenu")]//a',
        postFormsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf-post-forms") or contains(@href,"#/post-forms")]',
        registrationFormsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf-profile-forms") or contains(@href,"#/registration-forms")]',
        userDirectoryMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf_userlisting")]',
        // The page link, or its admin app route.
        subscriptionsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf_subscription") or contains(@href,"#/subscriptions")]',
        transactionsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf_transaction") or contains(@href,"#/transactions")]',
        settingsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf-settings") or contains(@href,"#/settings")]',
        // Registered only when Pro is inactive, so its absence is what identifies a
        // Pro build. The registration forms menu cannot be used: free registers it too.
        premiumMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf_premium")]',
        toolsMenu: '//li[@id="toplevel_page_wp-user-frontend"]//a[contains(@href,"page=wpuf_tools") or contains(@href,"#/tools")]',
    },

    // The settings screen fields the wizard writes, so a wizard choice can be
    // asserted where the admin would actually see it afterwards.
    // The settings API renders checkboxes with a wpuf- prefixed id and selects
    // without one, so the two are not interchangeable here.
    onboardingSettingsCheck: {
        autologin: '//input[@id="wpuf-wpuf_profile[autologin_after_registration]"]',
        enablePayment: '//input[@id="wpuf-wpuf_payment[enable_payment]"]',
        showAdminBar: '//input[@id="wpuf-wpuf_general[show_admin_bar][administrator]"]',
        // Selects: yes/no rather than a checkbox, and no wpuf- prefix on the id.
        loginPage: '//select[@id="wpuf_profile[login_page]"]',
        regOverridePage: '//select[@id="wpuf_profile[reg_override_page]"]',
        enablePostEdit: '//select[@id="wpuf_dashboard[enable_post_edit]"]',
        enablePostDelete: '//select[@id="wpuf_dashboard[enable_post_del]"]',
    },

    /*****************************************************/
    /************** @Parity (develop vs branch) *********/
    /*****************************************************/
    parity: {
        // Builder (same markup on the Vue and the React builder)
        builderSaveButton: '//button[normalize-space(text())="Save"]',
        // Palette buttons that add a field on click (develop: .wpuf-field-button[data-form-field])
        paletteFieldButtons: '.wpuf-field-button[data-form-field]',
        paletteFieldButton: (type: string) => `.wpuf-field-button[data-form-field="${type}"]`,
        // Fields on the builder stage
        stageFields: 'li[class*="form-field-"]',
        // Builder alert (develop: SweetAlert; branch: shared dialogs) and its confirm button
        alertPopup: '.swal2-container .swal2-popup, [data-wpuf-dialog], dialog.wpuf-dialog[open]',
        alertConfirm: '.swal2-container .swal2-confirm, [data-wpuf-dialog] [data-slot="alert-dialog-action"], .wpuf-dialog__footer .wpuf-dialog__btn:last-child',
        // Field settings panel (same classes on the Vue and the React builder)
        stageFieldEdit: 'Edit',
        fieldOptionsPanel: '.wpuf-form-builder-field-options',
        fieldOptionsSectionHeads: '.wpuf-form-builder-field-options .option-fields-section > h3',
        // Custom dropdown (React) inside an option row, and its options
        // Vue: div with a chevron icon; React: div[role=button]
        customSelectButton: 'div[role="button"][tabindex="0"], .option-fields-section > div:has(> i.fa-angle-down)',
        customSelectOption: 'li[role="option"], .option-fields-section ul li',
        fieldOptionsSectionBody: '.option-field-section-fields',
        fieldOptionRows: '.wpuf-form-builder-field-options .panel-field-opt',
        // React settings screen: text inputs carry the field name as id
        settingsTextInputs: '#wpuf-settings-root input[type="text"][id]',
        settingsSaveButton: '#wpuf-settings-root button:has-text("Save")',
    },

    /*****************************************************/
    /************** @AI Form Builder (React) *************/
    /*****************************************************/
    aiFormBuilder: {
        root: '#wpuf-ai-form-builder',
        // Forms list entry points
        listButton: 'button:has-text("AI Form Builder")',
        configModal: '[data-wpuf-dialog="card"]',
        // Stage 1
        inputHeading: 'h2:has-text("Create Form with AI")',
        description: 'textarea[aria-label="Describe your form"]',
        counter: 'text=/\\d+\\/300 Characters/',
        promptButtons: '#wpuf-ai-form-builder button[aria-pressed]',
        activePrompt: '#wpuf-ai-form-builder button[aria-pressed="true"]',
        integrationSelect: '#wpuf-ai-integration',
        selectItem: '[data-slot="select-item"]',
        generateButton: 'button:has-text("Generate Form")',
        // Stage 2
        processingHeading: 'text=Generating your form...',
        // Stage 3
        successHeading: 'h1:has-text("AI Form Builder")',
        previewTitle: '.wpuf-form-title',
        previewFields: '.wpuf-form-fields .wpuf-form-field',
        previewField: (template: string) => `.wpuf-form-fields [data-field-template="${template}"]`,
        chatInput: 'textarea[aria-label="Message"]',
        sendButton: 'button[aria-label="Send"]',
        userMessages: '.wpuf-message-user',
        aiMessages: '.wpuf-message-ai',
        acceptButton: '.wpuf-btn-accept',
        rejectButton: '.wpuf-btn-reject',
        acceptedStatus: '.wpuf-accepted-status',
        restoreButton: '.wpuf-btn-restore',
        regenerateButton: '.wpuf-btn-regenerate',
        editInBuilderButton: '.wpuf-btn-edit-builder',
        // Dialogs (shared ConfirmDialog)
        dialog: '[data-wpuf-dialog]',
        dialogAction: '[data-wpuf-dialog] [data-slot="alert-dialog-action"]',
        dialogCancel: '[data-wpuf-dialog] [data-slot="alert-dialog-cancel"]',
        regenerateDialog: '.wpuf-ai-regenerate-dialog',
        proFieldsDialog: '.wpuf-ai-pro-fields-dialog',
        proFieldItems: '.wpuf-ai-pro-fields-dialog .wpuf-pro-field-item',
        errorDialog: '.wpuf-ai-error-dialog',
        // Settings > Integrations > AI Settings
        testConnectionButton: 'button:has-text("Test Connection")',
        fetchModelsButton: 'button:has-text("Fetch latest models")',
        // Builder: "AI Generate Options" on a dropdown / radio / checkbox field
        builderField: (template: string) => `li.form-field-${template}`,
        builderFieldEdit: (template: string) => `li.form-field-${template} >> text=Edit`,
        optionsAiButton: 'button[title="AI Generate Options"]',
        optionsAiModal: '.wpuf-ai-modal',
        optionsAiPrompt: '.wpuf-ai-modal textarea',
        optionsAiGenerate: '.wpuf-ai-modal button:has-text("Generate")',
        optionsAiList: '.wpuf-ai-modal .wpuf-ai-options-list',
        optionsAiImport: '.wpuf-ai-modal button:has-text("Import Selected")',
    },
};
