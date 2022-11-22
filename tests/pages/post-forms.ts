require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';


//import { TestData } from '../tests/testdata';

 

export class PostForms {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

//2.0: CREATE_NEW_BLANK_FORM
    async createNewPostBlankForm(newPostName) {
        console.log("0002 > Running POST FORM Create");
        //Create_New_Post_Form
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);
        //Start
        console.log("2.0: START > Create New BLANK Form");                        //TODO: Make a COMMON FUNCTION
        await this.page.click(SelectorsPage.createPostForm.clickPostAddForm); 
  
        //Click_Blank_Form
        await this.page.waitForSelector(SelectorsPage.createPostForm.hoverBlankForm);   
        await this.page.hover(SelectorsPage.createPostForm.hoverBlankForm);   
        await this.page.waitForSelector(SelectorsPage.createPostForm.clickBlankForm);   
        await this.page.click(SelectorsPage.createPostForm.clickBlankForm);   

        //Enter_Name
        await this.page.click(SelectorsPage.createPostForm.editNewFormName); 
        await this.page.fill(SelectorsPage.createPostForm.enterNewFormName, newPostName);   
        await this.page.click(SelectorsPage.createPostForm.confirmNewNameTickButton);  
        

        
        //ACTION_Start
        //Post_Fields
        await this.page.click(SelectorsPage.createPostForm.postTitleBlock);
        await this.page.click(SelectorsPage.createPostForm.postContentBlock);
        await this.page.click(SelectorsPage.createPostForm.postExcerptBlock);
        await this.page.click(SelectorsPage.createPostForm.featuredImageBlock);

        //Taxonomies
        await this.page.click(SelectorsPage.createPostForm.categoryBlock);
        await this.page.click(SelectorsPage.createPostForm.tagsBlock);

        //Custom_Fields
        await this.page.click(SelectorsPage.createPostForm.customFieldsText);
            //br-start
            var textBlockAddPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            if (textBlockAddPopUp) {
                console.log("2.1: Click TextBlock PopUp > 'Dont show again' Button1")
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
                console.log("2.2: Click GoogleMap PopUP > 'OK1'")
                await this.page.click(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.customFieldsStepStart);
            //br-start
            var stepStartBlockAddPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            if (stepStartBlockAddPopUp) {
                console.log("2.3: Click StepStart POPUP > 'Dont show again' Button2")
                await this.page.click(SelectorsPage.createPostForm.prompt1PopUpModalClose);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.customFieldsEmbed);


        //Others
        await this.page.click(SelectorsPage.createPostForm.othersColumns);
        await this.page.click(SelectorsPage.createPostForm.othersSectionBreak);
        await this.page.click(SelectorsPage.createPostForm.othersCustomHTML);
        //From_PRO
        //await this.page.click(SelectorsPage.createPostForm.qrCode);            
        await this.page.click(SelectorsPage.createPostForm.othersReCaptcha);
            //br-start
            var reCaptchaBlockPopUp = await this.page.isVisible(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            if (reCaptchaBlockPopUp) {
                console.log("2.4: Click reCaptcha PopUP > 'OK2'")
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
                console.log("2.5: Click reCaptcha PopUP > 'OK3'")
                await this.page.click(SelectorsPage.createPostForm.prompt2PopUpModalOk);
            } 
            //br-end
        await this.page.click(SelectorsPage.createPostForm.othersMathCaptcha);

        //Add Multi-Step-Check
        await this.page.click(SelectorsPage.createPostForm.formEditorSettings);
        await this.page.click(SelectorsPage.createPostForm.checkMultiStepOption);
        
        //Finish
        await this.page.click(SelectorsPage.createPostForm.saveFormButton);
        console.log("2.6: END > Create New BLANK Form")

        //Return HOME
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);
        await this.page.waitForLoadState('domcontentloaded')
        //ASSERTION > Check if-VALID
        const checkNew_BlankForm_CreatedValid = await this.page.isVisible(SelectorsPage.login.wpufPostForm_CheckAddButton);
        console.log("2.7: " + checkNew_BlankForm_CreatedValid) //Check STATUS
        if (checkNew_BlankForm_CreatedValid == true) {  
            const checkNewFormCreated = await this.page.innerText(SelectorsPage.login.postFormsPageFormTitleCheck);
            console.log("2.8: Text is -> " + checkNewFormCreated)
            await expect(checkNewFormCreated).toContain(process.env.NEW_POST_BLANK_NAME);
        
        }

        console.log("2.9: Testing Check");

    }







}
