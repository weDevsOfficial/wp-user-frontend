require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


//import { TestData } from '../tests/testdata';

 

export class fieldOptionsCommon {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }





/***********************************/
/********** @PostForms ***********/
/*********************************/ 

/********* PostFields *********************/
    //PostFields
    async addPostFields_PF() {
        //PostFields
        await this.page.click(selectors.postForms.addPostFields_PF.postTitleBlock);
        await this.page.click(selectors.postForms.addPostFields_PF.postContentBlock);
        await this.page.click(selectors.postForms.addPostFields_PF.postExcerptBlock);
        await this.page.click(selectors.postForms.addPostFields_PF.featuredImageBlock);

        await this.page.waitForLoadState('domcontentloaded');
    };

    //Validate > PostFields
    async validatePostFields_PF() {
        //Validate
        //Post Title
        await expect(await this.page.isVisible(selectors.postForms.validatePostFields_PF.validatePostTitle)).toBeTruthy();
        //Post Description
        await expect(await this.page.isVisible(selectors.postForms.validatePostFields_PF.validatePostContent)).toBeTruthy();
        //Excerpt
        await expect(await this.page.isVisible(selectors.postForms.validatePostFields_PF.validateExcerpt)).toBeTruthy();
        //Featured Image
        await expect(await this.page.isVisible(selectors.postForms.validatePostFields_PF.validateFeaturedImage)).toBeTruthy();
        
    };



/********************* Taxonomies *********************/
    //Taxonomies
    async addTaxonomies_PF() {
        //Taxonomies
        await this.page.click(selectors.postForms.addTaxonomies_PF.categoryBlock);
        await this.page.click(selectors.postForms.addTaxonomies_PF.tagsBlock);

        await this.page.waitForLoadState('domcontentloaded');
    };

    //Validate > Taxonomies
    async validateTaxonomies_PF() {
        //Validate
        //Category
        expect(await this.page.isVisible(selectors.postForms.validateTaxonomies_PF.validateCategory)).toBeTruthy();
        //Tags
         expect(await this.page.isVisible(selectors.postForms.validateTaxonomies_PF.validateTags)).toBeTruthy();
    };

    async validateTaxonomiesPreset_PF() {
        //Validate
        //Category
        await expect(await this.page.isVisible(selectors.postForms.validateTaxonomiesPreset_PF.validateCategory)).toBeTruthy();
        //Tags
        await expect(await this.page.isVisible(selectors.postForms.validateTaxonomiesPreset_PF.validateTags)).toBeTruthy();
    };


/********************* Validate *********************/
    //Admin checks if Created form is displayed in Post Forms - Table/List
    async validateBlankFormCreated_PF(validateNewPostName_PF) {
        //Return HOME
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.waitForLoadState('domcontentloaded');
        
        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_PF = await this.page.isVisible(selectors.postForms.navigatePage_PF.checkAddButton_PF);
        if (checkNewBlankFormCreatedValid_PF == true) {  
            const checkNewFormCreated_PF = await this.page.innerText(selectors.postForms.navigatePage_PF.postFormsPageFormsTitleCheck_PF);
            await expect(checkNewFormCreated_PF).toContain(validateNewPostName_PF);
            console.log(checkNewFormCreated_PF);
            console.log(validateNewPostName_PF);
        }
    };




/*************************************/
/********** @_CommonFields ***********/
/***********************************/ 

    /********************* CustomFields *********************/
    //CustomFields
    async addCustomFields_Common() {
        //CustomFields
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsText);
            if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                await this.page.click(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
            }         
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsTextarea);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsDropdown);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsMultiSelect);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsRadio);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsCheckBox);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsWebsiteUrl);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsEmailAddress);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsHiddenField);
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsImageUpload);
        
        //FromPRO
        //RepeatField
        await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsRepeatField);
        const checkProPopUp = await this.page.isVisible(selectors.postForms.addCustomFields_Common.checkProPopUp);
            if (checkProPopUp === true) {
                await this.page.click(selectors.postForms.addCustomFields_Common.checkProPopUp);
                console.log("WPUF Pro is requred...")
            }
            else {
                //DateTime
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsDateTime);
                //TimeField
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsTimeField);
                //FileUpload    
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsFileUpload);
                //CountryList
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsCountryList);
                //NumericField
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsNumericField);
                //PhoneField
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsPhoneField); 
                //AddressField  
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsAddressField);
                //GoogleMaps
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsGoogleMaps);
                    if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
                        await this.page.click(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
                    } 
                //StepStart
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsStepStart);
                    if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                        await this.page.click(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
                    }
                //Embed
                await this.page.click(selectors.postForms.addCustomFields_Common.customFieldsEmbed); //TODO: This is an Error as position changes in Lite and Pro

                await this.page.waitForLoadState('domcontentloaded');

            }
        
    };

    //Validate > CustomFields
    async validateCustomFields_Common() {
        //Validate
        //Text
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateText)).toBeTruthy();
        //Textarea
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateTextarea)).toBeTruthy();
        //Dropdown
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateDropdown)).toBeTruthy();
        //MultiSelect
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateMultiSelect)).toBeTruthy();
        //Radio
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateRadio)).toBeTruthy();
        //CheckBox
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateCheckBox)).toBeTruthy();
        //WebsiteUrl
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateWebsiteUrl)).toBeTruthy();
        //EmailAddress
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateEmailAddress)).toBeTruthy();
        //HiddenField
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateHiddenField)).toBeTruthy();
        //ImageUpload
        await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateImageUpload)).toBeTruthy();
        
        //From PRO
        //RepeatField
        const proCustomFields_Common = await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateRepeatField);
        if (proCustomFields_Common === true) {
            //RepeatField
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateRepeatField)).toBeTruthy();
            //DateTime
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateDateTime)).toBeTruthy();
            //TimeField
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateTimeField)).toBeTruthy();
            //FileUpload
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateFileUpload)).toBeTruthy();
            //CountryList
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateCountryList)).toBeTruthy();
            //NumericField
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateNumericField)).toBeTruthy();
            //PhoneField
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validatePhoneField)).toBeTruthy();
            //AddressField
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateAddressField)).toBeTruthy();
            
            //GoogleMaps        //TODO: Setup required
                // if(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateGoogleMaps) == true){
                //     await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateGoogleMaps)).toBeTruthy();
                // }

            //StepStart
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateStepStart)).toBeTruthy();
            //Embed
            await expect(await this.page.isVisible(selectors.postForms.validateCustomFields_Common.validateEmbed)).toBeTruthy();

        }
        
        
    
    };


/********************* Others *********************/
    //Others
    async addOthers_Common() {
        //Others
        await this.page.click(selectors.postForms.addOthers_Common.othersColumns);
        await this.page.click(selectors.postForms.addOthers_Common.othersSectionBreak);
        await this.page.click(selectors.postForms.addOthers_Common.othersCustomHTML);
        await this.page.click(selectors.postForms.addOthers_Common.othersReCaptcha);
            if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
                await this.page.click(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
            } 


        //FromPRO
        await this.page.click(selectors.postForms.addOthers_Common.othersShortCode);
        const checkProPopUp = await this.page.isVisible(selectors.postForms.addCustomFields_Common.checkProPopUp);
            if (checkProPopUp === true) {
                await this.page.click(selectors.postForms.addCustomFields_Common.checkProPopUp);
                console.log("WPUF Pro is requred...")
            }

            else {
                //ShortCode
                if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                    await this.page.click(selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
                } 
                //ActionHook
                await this.page.click(selectors.postForms.addOthers_Common.othersActionHook);
                //TermsAndConditions
                await this.page.click(selectors.postForms.addOthers_Common.othersTermsAndConditions);
                //Ratings
                await this.page.click(selectors.postForms.addOthers_Common.othersRatings);
                //ReallySimpleCaptcha
                await this.page.click(selectors.postForms.addOthers_Common.othersReallySimpleCaptcha);
                    if (await this.page.isVisible(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
                        await this.page.click(selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
                    } 
                //MathCaptcha
                await this.page.click(selectors.postForms.addOthers_Common.othersMathCaptcha);
                await this.page.waitForLoadState('domcontentloaded');
            }

    };

    //Validate > Others
    async validateOthers_Common() {
        //Validate
        //Columns
        await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateColumns)).toBeTruthy();
        //SectionBreak
        await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateSectionBreak)).toBeTruthy();
        //CustomHTML
        await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateCustomHTML)).toBeTruthy();
        
        //From PRO
        //ReCaptcha
            //Not visible
        //Shortcode
        const proOthers_Common = await this.page.isVisible(selectors.postForms.validateOthers_Common.validateShortcode);
        if (proOthers_Common === true) {
            //Shortcode
            await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateShortcode)).toBeTruthy();
            //ActionHook
            await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateActionHook)).toBeTruthy();
            //TermsAndConditions
            await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateTermsAndConditions)).toBeTruthy();
            //Ratings
            await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateRatings)).toBeTruthy();
            //ReallySimpletCaptcha
                //Not visible
            //MathCaptcha
            await expect(await this.page.isVisible(selectors.postForms.validateOthers_Common.validateMathCaptcha)).toBeTruthy();
        }
    };


    //Settings > MultiStep Check
    async setMultiStepSettings_Common() {
        await this.page.waitForLoadState('domcontentloaded');
        //Add Multi-Step-Check
        await this.page.click(selectors.postForms.addOthers_Common.formEditorSettings);
        const proTextAlertInSettings = await this.page.isVisible(selectors.postForms.addCustomFields_Common.proTextAlertInSettings);
            if (proTextAlertInSettings === true) {  
                console.log("WPUF Pro is requred...");
            }
            else {
                await this.page.click(selectors.postForms.addOthers_Common.checkMultiStepOption);
                expect(await this.page.isChecked(selectors.postForms.addOthers_Common.checkMultiStepOption)).toBeTruthy();
            }
        

        await this.page.waitForLoadState('domcontentloaded');
    };



/********************* SaveForm *********************/
    //SaveForm
    async saveForm_Common(validateNewPostName_Common) {
        //Finish
        await this.page.waitForLoadState('domcontentloaded');
            const checkNewFormName_Common = await this.page.innerText(selectors.postForms.saveForm_Common.formNameReCheck);
            await expect(checkNewFormName_Common).toContain(validateNewPostName_Common);
        expect(await this.page.isVisible(selectors.postForms.saveForm_Common.saveFormButton)).toBeTruthy();
        await this.page.click(selectors.postForms.saveForm_Common.saveFormButton);
    };






/******************************************/
/********** @RegistrationForms ***********/
/****************************************/    

/********************* Profile Fields *********************/
    //ProfileFields
    async addProfileFields_RF() {
        //PostFields
        await this.page.waitForLoadState('domcontentloaded')
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldUsername);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldFirstName);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldLastName);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldDisplayName);        
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldNickName);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldEmail);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldWebsiteUrl);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFielBioInfo);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldPassword);
        await this.page.click(selectors.registrationForms.addProfileFields_RF.profileFieldAvatar);
    };

/********************* Custom Fields *********************/
    //Same as Post Forms

/********************* Others *********************/
    //Same as Post Forms

/********************* Save *********************/
    //Same as Post Forms


/********************* Validate *********************/
    //Admin checks if Created form is displayed in Post Forms - Table/List
    async validateBlankFormCreated_RF(validateNewPostName_RF) {
        //Return HOME
        await this.page.click(selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);
        await this.page.waitForLoadState('domcontentloaded');
        
        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_RF = await this.page.isVisible(selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (checkNewBlankFormCreatedValid_RF == true) {  
            const checkNewFormCreated_RF = await this.page.innerText(selectors.registrationForms.navigatePage_RF.postFormsPageFormTitleCheck_RF);
            await expect(checkNewFormCreated_RF).toContain(validateNewPostName_RF);
            console.log(checkNewFormCreated_RF);
            console.log(validateNewPostName_RF);
        }
    };



}