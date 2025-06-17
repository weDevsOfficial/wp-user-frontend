import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';


export class FieldOptionsCommonProPage extends Base {

    constructor(page: Page) {
        super(page);
    }


    /*****************************************/
    /********** @PostForms Fields ***********/
    /***************************************/

    /********* PostFields *********************/
    //PostFields
    async addPostFields_PF_pro() {
        //PostFields
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postTitleBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postContentBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postExcerptBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.featuredImageBlock);
        await this.page.waitForLoadState('domcontentloaded');
    }

    //Validate > PostFields
    async validatePostFields_PF_pro() {
        //Validate
        //Post Title
        await this.assertionValidate(Selectors.postForms.validatePostFields_PF.validatePostTitle);
        //Post Description
        await this.assertionValidate(Selectors.postForms.validatePostFields_PF.validatePostContent);
        //Excerpt
        await this.assertionValidate(Selectors.postForms.validatePostFields_PF.validateExcerpt);
        //Featured Image
        await this.assertionValidate(Selectors.postForms.validatePostFields_PF.validateFeaturedImage);

    }



    /********************* Taxonomies *********************/
    //Taxonomies
    async addTaxonomies_PF_pro() {
        //Taxonomies
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.categoryBlock);
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.tagsBlock);

        await this.page.waitForLoadState('domcontentloaded');
    }

    //Validate > Taxonomies
    async validateTaxonomies_PF_pro() {
        //Validate
        //Category
        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateCategory);
        //Tags
        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateTags);
    }

    async validateTaxonomiesPreset_PF_pro() {
        //Validate
        //Category
        await this.assertionValidate(Selectors.postForms.validateTaxonomiesPreset_PF.validateCategory);
        //Tags
        await this.assertionValidate(Selectors.postForms.validateTaxonomiesPreset_PF.validateTags);
    }






    /**************************************/
    /********** @Common Fields ***********/
    /************************************/

    /********************* CustomFields *********************/
    //CustomFields
    async addCustomFields_Common_pro() {

        //FromPRO
        //RepeatField
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsRepeatField);
        const checkProPopUpCloseButton = await this.page.isVisible(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
        if (checkProPopUpCloseButton === true) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
            console.log('Pro: WPUF Pro is requred...');
        }
        else {
            if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
            }
            //DateTime
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsDateTime);
            //TimeField
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsTimeField);
            //FileUpload    
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsFileUpload);
            //CountryList
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsCountryList);
            //NumericField
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsNumericField);
            //PhoneField
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsPhoneField);
            //AddressField  
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsAddressField);
            //GoogleMaps
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsGoogleMaps);
            if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
                await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
            }
            //StepStart
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsStepStart);
            if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
            }
            //Embed
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsEmbed); //TODO: This is an Error as position changes in Lite and Pro

            await this.page.waitForLoadState('domcontentloaded');

        }

    }

    //Validate > CustomFields
    async validateCustomFields_Common_pro() {
        //Validate
        //From PRO
        //RepeatField
        const proCustomFields_Common = await this.page.isVisible(Selectors.postForms.validateCustomFields_Common.validateRepeatField);
        if (proCustomFields_Common === true) {
            //RepeatField
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateRepeatField);
            //DateTime
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateDateTime);
            //TimeField
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateTimeField);
            //FileUpload
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateFileUpload);
            //CountryList
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateCountryList);
            //NumericField
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateNumericField);
            //PhoneField
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validatePhoneField);
            //AddressField
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateAddressField);

            //GoogleMaps
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateGoogleMaps);

            //StepStart
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateStepStart);
            //Embed
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateEmbed);

        }
    }
    /********************* Others *********************/
    //Others
    async addOthers_Common_pro() {
        //Others
        //FromPRO
        await this.validateAndClick(Selectors.postForms.addOthers_Common.othersShortCode);
        const checkProPopUpCloseButton = await this.page.isVisible(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
        if (checkProPopUpCloseButton === true) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
            console.log('Pro: WPUF Pro is requred...');
        }

        else {
            //ShortCode
            if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
                await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
            }
            //ActionHook
            await this.validateAndClick(Selectors.postForms.addOthers_Common.othersActionHook);
            //TermsAndConditions
            await this.validateAndClick(Selectors.postForms.addOthers_Common.othersTermsAndConditions);
            //Ratings
            await this.validateAndClick(Selectors.postForms.addOthers_Common.othersRatings);
            //ReallySimpleCaptcha
            await this.validateAndClick(Selectors.postForms.addOthers_Common.othersReallySimpleCaptcha);
            if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
                await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
            }
            //MathCaptcha
            await this.validateAndClick(Selectors.postForms.addOthers_Common.othersMathCaptcha);
            await this.page.waitForLoadState('domcontentloaded');
        }

    }

    //Validate > Others
    async validateOthers_Common_pro() {
        //Validate
        //From PRO
        //ReCaptcha
        //Not visible
        //Shortcode
        const proOthers_Common = await this.page.isVisible(Selectors.postForms.validateOthers_Common.validateShortcode);
        if (proOthers_Common === true) {
            //Shortcode
            await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateShortcode);
            //ActionHook
            await this.validateAny(Selectors.postForms.validateOthers_Common.validateActionHook);
            //TermsAndConditions
            await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateTermsAndConditions);
            //Ratings
            await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateRatings);
            //ReallySimpletCaptcha
            //Not visible
            //MathCaptcha
            await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateMathCaptcha);
        }
    }


    //Settings > MultiStep Check
    async setMultiStepSettings_Common_pro() {
        await this.page.waitForLoadState('domcontentloaded');
        //Add Multi-Step-Check
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditorSettings);
        const proTextAlertInSettings = await this.page.isVisible(Selectors.postForms.addCustomFields_Common.proTextAlertInSettings);
        if (proTextAlertInSettings === true) {
            console.log('Pro: WPUF Pro is requred...');
        }
        else {
            await this.validateAndClick(Selectors.postForms.formSettings.checkMultiStepOption);
            await expect(this.page.isChecked(Selectors.postForms.formSettings.checkMultiStepOption)).toBeTruthy();
        }

        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditor);
        await this.page.waitForLoadState('domcontentloaded');
    }



    /********************* SaveForm *********************/
    //SaveForm
    async saveForm_Common_pro(validateNewPostName_Common: string) {

        try {
            await this.page.waitForSelector(Selectors.postForms.saveForm_Common.formNameReCheck, { state: 'visible' });
            const checkNewFormName_Common = await this.page.textContent(Selectors.postForms.saveForm_Common.formNameReCheck);
            expect(checkNewFormName_Common?.trim()).toContain(validateNewPostName_Common);

            console.log('Before Save-Form Name: ' + checkNewFormName_Common);
        } catch (e) {
            console.log('not matched ');
        }
        await this.page.waitForLoadState('domcontentloaded');

        //Save Form
        await this.validateAndClick(Selectors.postForms.saveForm_Common.saveFormButton);
        await this.page.reload();
    }





    /********************* Validate *********************/
    //Admin checks if Created form is displayed in Post Forms - Table/List
    async validatePostFormCreatedPro(validateNewPostName_PF: string) {
        //Return HOME
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_PF = await this.page.isVisible(Selectors.postForms.navigatePage_PF.checkAddButton_PF);
        if (checkNewBlankFormCreatedValid_PF === true) {
            const checkNewFormCreated_PF = await this.page.innerText(Selectors.postForms.navigatePage_PF.postFormsPageFormsTitleCheck_PF);
            await expect(checkNewFormCreated_PF).toContain(validateNewPostName_PF);
            console.log('PF Name: ' + checkNewFormCreated_PF);
            console.log('PF List: ' + validateNewPostName_PF);
        }
    }























    /******************************************/
    /********** @RegistrationForms ***********/
    /****************************************/

    /********************* Profile Fields *********************/
    //ProfileFields
    async addProfileFields_RF() {
        //PostFields
        await this.page.waitForLoadState('domcontentloaded');
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldUsername);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldFirstName);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldLastName);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldDisplayName);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldNickName);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldEmail);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldWebsiteUrl);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFielBioInfo);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldPassword);
        await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldAvatar);
    }

    /********************* Custom Fields *********************/
    //Same as Post Forms

    /********************* Others *********************/
    //Same as Post Forms

    /********************* Save *********************/
    //Same as Post Forms


    /********************* Validate *********************/
    //Admin checks if Created form is displayed in Post Forms - Table/List
    async validateRegistrtionFormCreated(validateNewPostName_RF) {
        //Return HOME
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);
        await this.page.waitForLoadState('domcontentloaded');

        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_RF = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (checkNewBlankFormCreatedValid_RF === true) {
            const checkNewFormCreated_RF = await this.page.innerText(Selectors.registrationForms.navigatePage_RF.postFormsPageFormTitleCheck_RF);
            await expect(checkNewFormCreated_RF).toContain(validateNewPostName_RF);
            console.log('RF Name: ' + checkNewFormCreated_RF);
            console.log('PF List: ' + validateNewPostName_RF);
        }
    }



}