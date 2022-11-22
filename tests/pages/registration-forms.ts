require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';


//import { TestData } from '../tests/testdata';

 

export class RegistrationForms {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


//4.0: FrontEND_Check_Blank_Form
    async createNewRegistrationBlankForm(newRegistrationName) {
        console.log("0004 > Running REGISTRATION FORM Create");
        //Create_New_Post_Form
        await this.page.click(SelectorsPage.createRegistrationForm.clickRegistrationFormMenuOption);
        //Start
        console.log("4.0: START > Create New REGISTRATION Form");                        //TODO: Make a COMMON FUNCTION
        await this.page.click(SelectorsPage.createRegistrationForm.clickRegistraionAddForm); 
  
        //Click_Blank_Form
        await this.page.waitForSelector(SelectorsPage.createRegistrationForm.hoverBlankForm);   
        await this.page.hover(SelectorsPage.createRegistrationForm.hoverBlankForm);   
        await this.page.waitForSelector(SelectorsPage.createRegistrationForm.clickBlankForm);   
        await this.page.click(SelectorsPage.createRegistrationForm.clickBlankForm);   

        //Enter_Name
        await this.page.click(SelectorsPage.createRegistrationForm.editNewFormName); 
        await this.page.fill(SelectorsPage.createRegistrationForm.enterNewFormName, newRegistrationName);   
        await this.page.click(SelectorsPage.createRegistrationForm.confirmNewNameTickButton);  
        


        //ACTION_Start
        //Post_Fields
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldUsername);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldFirstName);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldLastName);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldDisplayName);        
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldNickName);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldEmail);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldWebsiteUrl);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFielBioInfo);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldPassword);
        await this.page.click(SelectorsPage.createRegistrationForm.profileFieldAvatar);
                

        
        //Custom_Fields
        await this.page.click(SelectorsPage.createPostForm.customFieldsText);
            //br-start
            var textBlockAddPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            if (textBlockAddPopUp) {
                console.log("4.1: Click TextBlock PopUp > 'Dont show again' Button1")
                await this.page.click(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.customFieldsTextarea);
        await this.page.click(SelectorsPage.createPostForm.customFieldsDropdown);
        await this.page.click(SelectorsPage.createPostForm.customFieldsMultiSelect);
        await this.page.click(SelectorsPage.createPostForm.customFieldsRadio);
        await this.page.click(SelectorsPage.createPostForm.customFieldsCheckBox);
        await this.page.click(SelectorsPage.createPostForm.customFieldsWebsiteUrl);
        await this.page.click(SelectorsPage.createPostForm.customFieldsEmailAddress);
        await this.page.click(SelectorsPage.createPostForm.customFieldsHiddenField);
        await this.page.click(SelectorsPage.createPostForm.customFieldsImageUpload);
        //From_PRO
        await this.page.click(SelectorsPage.createPostForm.customFieldsRepeatField);
        await this.page.click(SelectorsPage.createPostForm.customFieldsDateTime);
        await this.page.click(SelectorsPage.createPostForm.customFieldsTimeField);     
        await this.page.click(SelectorsPage.createPostForm.customFieldsFileUpload);
        await this.page.click(SelectorsPage.createPostForm.customFieldsCountryList);
        await this.page.click(SelectorsPage.createPostForm.customFieldsNumericField);
        await this.page.click(SelectorsPage.createPostForm.customFieldsPhoneField);   
        await this.page.click(SelectorsPage.createPostForm.customFieldsAddressField);
        await this.page.click(SelectorsPage.createPostForm.customFieldsGoogleMape);
            //br-start
            var googleMapBlockPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            if (googleMapBlockPopUp) {
                console.log("4.2: Click GoogleMap PopUP > 'OK1'")
                await this.page.click(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.customFieldsStepStart);
            //br-start
            var stepStartBlockAddPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            if (stepStartBlockAddPopUp) {
                console.log('4.3: Click StepStart POPUP > "Dont show again" Button2')
                await this.page.click(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.customFieldsEmbed);

        //Others
        await this.page.click(SelectorsPage.createPostForm.othersColumns);
        await this.page.click(SelectorsPage.createPostForm.othersSectionBreak);
        await this.page.click(SelectorsPage.createPostForm.othersCustomHTML);
        //From_PRO
        await this.page.click(SelectorsPage.createPostForm.othersReCaptcha);
            //br-start
            var reCaptchaBlockPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            if (reCaptchaBlockPopUp) {
            console.log('4.4: Click reCaptcha PopUP > "OK2')
            await this.page.click(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.othersShortCode);
        await this.page.click(SelectorsPage.createPostForm.othersActionHook);
        await this.page.click(SelectorsPage.createPostForm.othersTermsAndConditions);
        await this.page.click(SelectorsPage.createPostForm.othersRatings);
        await this.page.click(SelectorsPage.createPostForm.othersReallySimpleCaptcha);
            //br-start
            var reallySimpleCaptchaPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            if (reallySimpleCaptchaPopUp) {
                console.log('4.5: Click reCaptcha PopUP > "OK3')
                await this.page.click(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.othersMathCaptcha);

        //Add Multi-Step-Check
        await this.page.click(SelectorsPage.createPostForm.formEditorSettings);
        await this.page.click(SelectorsPage.createPostForm.checkMultiStepOption);
        await this.page.waitForLoadState('domcontentloaded')


        //Finish
        await this.page.click(SelectorsPage.createPostForm.saveFormButton);
        console.log("4.6: END > Create New REGISTRATION Form")

        //Return HOME
        await this.page.click(SelectorsPage.createRegistrationForm.clickRegistrationFormMenuOption);
        await this.page.waitForLoadState('domcontentloaded')
        //ASSERTION > Check if-VALID
        const checkNew_RegistrationForm_CreatedValid = await this.page.isVisible(SelectorsPage.createRegistrationForm.clickRegistrationFormMenuOption);
        console.log("4.7: " + checkNew_RegistrationForm_CreatedValid) //Check STATUS
            if (checkNew_RegistrationForm_CreatedValid == true) {  
                const checkNewFormCreated = await this.page.innerText(SelectorsPage.login.postFormsPageFormTitleCheck);
                console.log("4.8 Text is -> " + checkNewFormCreated)
                await expect(checkNewFormCreated).toContain(process.env.NEW_REGISTRAION_BLOG_NAME);
                console.log("4.9: Validation PASSED");

            }
            else {
                await this.page.pause();
                console.log("4.10: Validation FAILED");

            }

        }




}
