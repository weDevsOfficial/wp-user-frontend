export const SelectorsPage = {



    //0001
        login:{
            //Login-1
            loginEmailField: '//input[@id="user_login"]',
            loginPasswordField: '//input[@id="user_pass"]',
            loginButton: '//input[@id="wp-submit"]',
            //Login-2
            loginEmailField2: '//input[@id="wpuf-user_login"]',
            loginPasswordField2: '//input[@id="wpuf-user_pass"]',
            loginButton2: '///input[@type="submit"]',
                //Admin BackEnd
                adminDashboard:'//li[@id="wp-admin-bar-site-name"]/a[1]',
    
            //Validate LOGIN
            logingSuccessDashboard: '//div[text()="Dashboard"]',
            //clickWPUFSidebar: '//div[text()="User Frontend"]/.',
            clickWPUFSidebar: '#toplevel_page_wp-user-frontend > a',
            
            //Plugin Activate/Deactivate
            clickPluginsSidebar: '//li[@id="menu-plugins"]',
            clickWPUF_LitePlugin: '//a[@aria-label="Activate WP User Frontend"]',
            clickWPUF_ProPlugin: '//a[@id="activate-wpuf-pro"]',
    
            //WPUF Setup 
            //Skip Setup
            clickWPUFSetupSkip: '//a[@class="button button-large" and contains(text(), "Not right now")]',
            //Continue Setup
            clickWPUFSetupLetsGo: '//a[contains(@class,"button-primary button")]',
            clickWPUFSetupContinue: '//input[@type="submit"]',
            clickWPUFSetupEnd: '//a[contains(@class,"button button-primary")]',
    
            //WPUF > Pages > Navigation
            wpufPostForm_CheckAddButton: '#new-wpuf-post-form',
            wpufRegistrationForm_CheckAddButton: '//a[@id="new-wpuf-profile-form"]',
            postFormsPageFormTitleCheck: '(//a[@class="row-title"])[1]',
    
    
        },
    
    //0002
        logout:{
            logoutHoverUsername: '//a[@class="ab-item" and contains(text(), "Howdy, ")]',
            logoutButton: '//a[@class="ab-item" and contains(text(), "Log Out")]',
    
            //Validate LOGOUT
            logoutSucces: '//div[@class="wpuf-message"]',
        },
    
    //0003
        createPostForm: {
            //Create_New_Post_Form
            clickPostFormMenuOption: '//a[contains(text(), "Post Forms")]',
    
            //Start
            clickPostAddForm: '//a[@class="page-title-action add-form" and contains(text(), "Add Form")]',
            hoverBlankForm: '.blank-form',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',
    
            //Enter_NAME
            editNewFormName: '//span[text()="Sample Form"]',
            enterNewFormName: '//header[@class="clearfix"]/span/input',  //TODO: Catch with Child
            confirmNewNameTickButton: '//button[@class="button button-small"]',
    
            //Post_Fields
            postTitleBlock: '//li[@data-form-field="post_title"]',
            postContentBlock: '//li[@data-form-field="post_content"]',
            postExcerptBlock: '//li[@data-form-field="post_excerpt"]',
            featuredImageBlock: '//li[@data-form-field="featured_image"]',
    
            //Taxonomies
            categoryBlock: '//li[@data-form-field="category"]',
            tagsBlock: '//li[@data-form-field="post_tag"]',
    
            //Custom _Fields
            customFieldsText: '//li[@data-form-field="text_field"]',
            customFieldsTextarea: '//li[@data-form-field="textarea_field"]',
            customFieldsDropdown: '//li[@data-form-field="textarea_field"]',
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
                customFieldsGoogleMape: '//li[@data-form-field="google_map"]',      
                customFieldsStepStart: '//li[@data-form-field="step_start"]',
                customFieldsEmbed: '//li[@data-form-field="embed"]',
    
                //prompt1
                prompt1PopUpModalClose: "//button[contains(@class,'swal2-confirm btn')]",
                //prompt2
                prompt2PopUpModalOk:'//button[contains(@class,"swal2-confirm swal2-styled")]',
            
            
            //Others
            othersColumns: '//li[@data-form-field="column_field"]',
            othersSectionBreak: '//li[@data-form-field="section_break"]',
            othersCustomHTML: '//li[@data-form-field="custom_html"]',
                //From___PRO
                othersQrCode: '//li[@data-form-field="qr_code"]', 
                othersReCaptcha: '//li[@data-form-field="recaptcha"]',
                othersShortCode: '//li[@data-form-field="shortcode"]',
                othersActionHook: '//li[@data-form-field="action_hook"]',
                othersTermsAndConditions: '//li[@data-form-field="toc"]',
                othersRatings: '//li[@data-form-field="ratings"]',
                othersReallySimpleCaptcha: '//li[@data-form-field="really_simple_captcha"]',
                othersMathCaptcha: '//li[@data-form-field="math_captcha"]',
    
            //Add Multi-Step-Check
            formEditorSettings: '(//h2[@class="nav-tab-wrapper"]//a)[2]',
            checkMultiStepOption: '//input[@name="wpuf_settings[enable_multistep]"]',
    
            //FINISH
            saveFormButton: '//button[@class="button button-primary" and contains(text(), "Save Form")]',
    
            //New_Created_NAME_Checker
            newPostCreatedName: '(//a[@class="row-title"])[1]',
        },
    
    //0004
        frontEndCheckBlankForm: {
            //Save_ShortCODE
            clickShortCode: '(//td[@data-colname="Shortcode"]//code)[1]',
    
            //Click Pages
            clickLeftNavPages: '//li[@id="menu-pages"]',
                //Add New Page
                clickAddNewPageButton: '.page-title-action',
                //Add New Page > Add Title
                newPageBlockEditorPopup1: '.components-modal__content', 
                newPageBlockEditorPopup2: '.components-guide__container',
                newPageBlockEditorPopupClose: '//button[@aria-label="Close dialog"]',
                newPageBlockEditorPopupNextButton: '//button[text()="Next"]',
    
                newPageAddTitle: '//h1[@aria-label="Add title"]',
                //Add New Page > Add Shortcode
                newPageAddBlockIcon: '//button[@aria-label="Add block"]',
                newPageAddBlockSearch: '.components-search-control__input',
                newPageAddBlockClickShortcode: '//button[@role="option"]',
    
            //Click on New Page > From Pages
            clickFormsPageFrontEndEdit: '//a[contains (text(),"Post Form Page")]',
    
                //Edit Shortcode
                shortCodeBlock: '//label[text()="Shortcode"]',
                editShortCodeBlock1: '#blocks-shortcode-input-1',
                editShortCodeBlock2: '//textarea[@class="block-editor-plain-text blocks-shortcode__textarea"]',
            //Save
            clickEditFormsPageUpdate: '//button[text()="Update"]',
    
            //Go to FRONT-END
                //Dashboard Return
                clickReturnToDashboard: '//a[@aria-label="View Pages"]',
                //Access Visit Site
                hoverSiteName: '//li[@id="wp-admin-bar-site-name"]',
                clickVisitSite: '//li[@id="wp-admin-bar-view-site"]//a[1]',
    
                    //FRONT-END
                    frontEndClickAccount: '//a[contains(text(),"Account")]',
                    frontEndClickSubmitPost: '//li[@class="wpuf-menu-item submit-post"]//a[1]',
                        //Locators > BLANK Form ITEMS
                        frontEndPostTitle: '//input[@name="post_title"]',
                        frontEndPostContent: '//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]',
                            frontEndPostContentfill: '//body[@id="tinymce"]',
                        frontEndPostExcerpt: '//textarea[@name="post_excerpt"]',
                        
                        frontEndFeaturedImageUpload: '(//input[@type="file"])[1]',
                        frontEndcategorySelection: 'select[name="category"]',
                        frontEndTags: 'input[name="tags"]',
                        frontEndTextBox: 'input[name="text"]',
                        frontEndTextAreaBox: 'textarea[name="textarea"]',
                        frontEndMultiSelect: '//option[@value="Option"]',
                        frontEndRadioOption: 'input[name="radio"]',
                        frontEndCheckbox: '(//input[@type="checkbox"])[1]',
                        frontEndWebsiteURL: 'input[name="website_url"]',
                        frontEndEmailAddress: 'input[name="email_address"]',
                        frontEndImageUpload: '(//input[@type="file"])[2]',
                        frontEndRepeatField: 'input[name="repeat_field[]"]',
                        frontEndDateTimeSet: '//input[@id="wpuf-date-date___time"]',
                        frontEndTimeField: 'select[name="time_field"]',
                        frontEndFileUpload: '(//input[@type="file"])[3]',
                        frontEndCountrySelect: 'select[name="country_list"]',
                        frontEndNumericField: 'input[name="numeric_field"]',
                        frontEndPhoneNumber: '//input[@data-label="Phone Field"]',
                        frontEndAddressFieldLine1: '//input[@data-label="Address Line 1"]',
                            frontEndAddressFieldCity: '//input[@data-label="City"]',
                            frontEndAddressFieldSelectCountry: '//select[@name="address_field[country_select]"]',
                            frontEndAddressFieldZipCode: '//input[@data-label="Zip Code"]', 
                            frontEndAddressFieldSelectState: '//select[@name="address_field[state]"]',
                        frontEndEmbedField: 'input[name="embed"]',
                        frontEndSectionBreak: '//h2[text()="Section Break"]',
                        frontEndHTMLSection: '//div[text()[normalize-space()="HTML Section"]]',
                        frontEndShortCode: '//div[text()[normalize-space()="[your_shortcode]"]]',
                            frontEndCheckTermsAndConditions: '(//input[@name="terms_and_conditions"])',
                        frontEndRating5: '//a[@data-rating-text="5"]',
                        frontEndCaptchaValue1: '//span[@id="operand_one"]',
                        frontEndCaptchaValue2: '//span[@id="operand_two"]',
                        frontEndCaptchaOperator:'//span[@id="operator"]',
                        frontEndCaptchaInputBox: '//input[contains(@class,"textfield wpuf-captcha-input")]',
                        
    
                        //SUBMIT FORM
                        frontEndSubmitForm: '//input[@value="Submit"]',
    
                    //VALIDATE FORM SUBMISSION
                    frontEndClickHomePage: '//h1[@class="wp-block-site-title"]//a[1]',
                    frontEndValiteFormSubmitted: '//h2[@class="wp-block-post-title"])[1]',
    
                    frontEndClickPost: '//li[@class="wpuf-menu-item post"]//a[1]',
                    frontEndPostTableItem1: '(//td[@data-label="Title: "]//a)[1]',
    
        },
    
    
    
    //0005
        createRegistrationForm: {
            //Create_New_Post_Form
            clickRegistrationFormMenuOption: '//a[contains(text(), "Registration Forms")]',
    
            //Start
            clickRegistraionAddForm: '#new-wpuf-profile-form',
            hoverBlankForm: '.blank-form',
            clickBlankForm: '//a[@title="Blank Form" and contains(text(), "Create Form")]',
    
            //Enter_NAME
            editNewFormName: '//span[text()="Sample Registration Form"]',
            enterNewFormName: '//header[@class="clearfix"]//span//input',  //TODO: Catch with Child
            confirmNewNameTickButton: '//header[@class="clearfix"]//button',
    
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
    
            //Custom Fields > Same as POST_Forms
    
            //Others > Same as POST_Forms
    
        },
    
    //0006
        editPostForm: {
    
        }
        
    };
    
    