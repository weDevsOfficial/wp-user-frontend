export const Selectors_RF_Create = {


    /**@Here Locators creating Navigating Post Forms Page
     * 
     * 
     * 
     */
    navigate_RF_Page : {
        //WPUF > Pages > Navigation
        checkAddButton_RF: '//a[@id="new-wpuf-profile-form"]',
        postFormsPageFormTitleCheck_RF: '(//a[@class="row-title"])[1]',

        //New_Created_NAME_Checker
        newPostCreatedName_RF: '(//a[@class="row-title"])[1]',
    },
        

    /**@Here Locators creating Registration > Blank Form
     * 
     * 
     * 
     */
    create_BlankForm_RF: {
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

    


    /**@Here Locators for All Fields Options + Save
     * 
     * 
     * 
     */
    add_ProfileFields_RF: {
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
    

    // save_BlankForm_RF: { //TODO: Remove Later
    //     //Validate Name
    //     formName_ReCheck: '.form-title',
    //     //FINISH
    //     saveFormButton: '//button[text()[normalize-space()="Save Form"]]',
    // },





    };
        
    
    