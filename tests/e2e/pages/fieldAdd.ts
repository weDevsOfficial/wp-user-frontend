import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';


export class FieldAddPage extends Base {

    constructor(page: Page) {
        super(page);
    }


    /*****************************************/
    /********** @PostForms Fields ***********/
    /***************************************/

    /********* PostFields *********************/
    //PostFields
    async addPostFields_PF() {
        //PostFields
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postTitleBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postContentBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postExcerptBlock);
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.featuredImageBlock);
         
    }

    //Validate > PostFields
    async validatePostFields_PF() {
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

    async validateProductPostFields_PF(){
        // Validate
        // Product Title
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductTitle);
        //Product Category
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductCategory);
        // Product Description
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductDescription);
        // Product Price
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductShDescription);
        // Regular Price
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateRegularPrice);
        // Sale Price
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateSalePrice);
        // Product Image
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductImage);
        // Image Gallery
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateImageGallery);
        // Catalog Visibility
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateCatalogVisibility);
        // Purchase Note
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validatepurchaseNote);
        // Product Reviews
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductReviews);
        // Downloadable Product
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateDownloadableProduct);
        // Downloadable Files
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateDownloadableFiles);
        // Product Brand
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductBrand);
        // Product Type
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductType);
        // Product Visibility
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductVisibility);
        // Product Tag
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductTag);
        // Product Shipping Class
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductShippingClass);
        // Product Attribute
        await this.assertionValidate(Selectors.postForms.validateProductPostFields_PF.validateProductAttribute);
    }

    async validateDownloadsPostFields_PF(){
        // Validate
        // Downloads Title
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadsTitle);
        //Downloads Category
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadsCategory);
        // Downloads Description
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadsDescription);
        // Downloads Price
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadsShDescription);
        // Regular Price
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateRegularPrice);
        // Downloads Image
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadsImage);
        // Purchase Note
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validatepurchaseNote);
        // Downloadable Files
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateDownloadableFiles);
        // Product Tag
        await this.assertionValidate(Selectors.postForms.validateDownloadsPostFields_PF.validateProductTag);
    }



    /********************* Taxonomies *********************/
    //Taxonomies
    async addTaxonomies_PF() {
        //Taxonomies
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.categoryBlock);
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.tagsBlock);

         
    }

    //Validate > Taxonomies
    async validateTaxonomies_PF() {
        //Validate
        //Category
        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateCategory);
        //Tags
        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateTags);
    }

    async validateTaxonomiesPreset_PF() {
        //Validate
        //Category
        await this.assertionValidate(Selectors.postForms.validateTaxonomiesPreset_PF.validateCategory);
        //Tags
        await this.assertionValidate(Selectors.postForms.validateTaxonomiesPreset_PF.validateTags);
    }

    async addProductTaxoFields_PF(){
        //Taxonomies
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.brandBlock);
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.typeblock);
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.visibilityBlock);
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.tagBlock);
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.shippingBlock);
        await this.validateAndClick(Selectors.postForms.addProductTaxo_PF.attributeBlock);
    }

    async addDownloadsTaxoFields_PF(){
        //Taxonomies
        await this.validateAndClick(Selectors.postForms.addDownloadsTaxo_PF.tagBlock);
    }





    /**************************************/
    /********** @Common Fields ***********/
    /************************************/

    /********************* CustomFields *********************/
    //CustomFields
    async addCustomFields_Common() {
        //CustomFields
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsText);
        
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsTextarea);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsDropdown);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsMultiSelect);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsRadio);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsCheckBox);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsWebsiteUrl);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsEmailAddress);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsHiddenField);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsImageUpload);

        //FromPRO
        //RepeatField
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsRepeatField);
        const checkProPopUpCloseButton = await this.page.isVisible(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
        if (checkProPopUpCloseButton === true) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.checkProPopUpCloseButton);
            console.log('Pro: WPUF Pro is requred...');
        }
        else {
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
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsGoogleMapsEdit);
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.googleMapsSearchbox);
            await this.validateAndClick(Selectors.postForms.addPostFieldButton);

            //StepStart
            // await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsStepStart);
            // if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            //     await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
            // }

            //Embed
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsEmbed); //TODO: This is an Error as position changes in Lite and Pro

             

        }

    }

    async addTextRelatedFields() {
        //CustomFields
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsText);
        
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsTextarea);
    }

    //Validate > CustomFields
    async validateCustomFields_Common() {
        //Validate
        //Text
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateText);
        //Textarea
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateTextarea);
        //Dropdown
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateDropdown);
        //MultiSelect
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateMultiSelect);
        //Radio
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateRadio);
        //CheckBox
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateCheckBox);
        //WebsiteUrl
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateWebsiteUrl);
        //EmailAddress
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateEmailAddress);
        //HiddenField
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateHiddenField);
        //ImageUpload
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateImageUpload);

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
            // if(await this.page.isVisible(Selectors.postForms.validateCustomFields_Common.validateGoogleMaps) === true){
            //     await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateGoogleMaps)).toBeTruthy();
            // }

            //StepStart
            //await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateStepStart);
            //Embed
            await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateEmbed);

        }



    }


    /********************* Others *********************/
    //Others
    async addOthers_Common() {
        //Others
        await this.validateAndClick(Selectors.postForms.addOthers_Common.othersColumns);
        await this.validateAndClick(Selectors.postForms.addOthers_Common.othersSectionBreak);
        await this.validateAndClick(Selectors.postForms.addOthers_Common.othersCustomHTML);
        // await this.validateAndClick(Selectors.postForms.addOthers_Common.othersReCaptcha);
        // if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
        //     await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
        // }
        // await this.validateAndClick(Selectors.postForms.addOthers_Common.reCaptchaEdit);
        // await this.validateAndClick(Selectors.postForms.addOthers_Common.invisibleReCaptcha);
        // await this.validateAndClick(Selectors.postForms.addPostFieldButton);
        // await this.validateAndClick(Selectors.postForms.addOthers_Common.othersCloudflareTurnstile);
        // if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk)) {
        //     await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt2PopUpModalOk);
        // }


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
             
        }

    }

    //Validate > Others
    async validateOthers_Common() {
        //Validate
        //Columns
        await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateColumns);
        //SectionBreak
        await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateSectionBreak);
        //CustomHTML
        await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateCustomHTML);
        //ReCaptcha
        //await this.assertionValidate(Selectors.postForms.validateOthers_Common.validateReCaptcha);
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

    async addFOS() {
        //FOS
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postTitleBlock);
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.categoryBlock);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsText);
        
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsTextarea);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsDropdown);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsRadio);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsImageUpload);
        //TimeField
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsTimeField);
        //FileUpload    
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsFileUpload);
        //CountryList
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsCountryList);

        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsEmbed);

        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsEmailAddress);

        //AddressField  
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsAddressField);

        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsWebsiteUrl);

        //NumericField
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsNumericField);

    }
    
    async addFOS_more() {
        //FOS
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postTitleBlock);
        //DateTime
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsDateTime);
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }

    }

    async addFOS_again() {
        //FOS
        await this.validateAndClick(Selectors.postForms.addPostFields_PF.postTitleBlock);
        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsWebsiteUrl);
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }
    }


    //Settings > MultiStep Check
    async setMultiStepSettings_Common() {
         
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
         
    }



    /********************* SaveForm *********************/
    //SaveForm
    async saveForm_Common() {
        //Save Form
        await this.validateAndClick(Selectors.postForms.saveForm_Common.saveFormButton);

        await this.waitForFormSaved(Selectors.postFormSettings.messages.formSaved, Selectors.postForms.saveForm_Common.saveFormButton);

    }

    /********************* Validate *********************/
    //Admin checks if Created form is displayed in Post Forms - Table/List
    async validatePostFormCreated(validateNewPostName_PF: string) {
        //Return HOME
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
         

        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_PF = await this.page.isVisible(Selectors.postForms.navigatePage_PF.checkAddButton_PF);
        if (checkNewBlankFormCreatedValid_PF === true) {
            await this.checkElementText(Selectors.postForms.navigatePage_PF.postFormsPageFormsTitleCheck_PF(validateNewPostName_PF), validateNewPostName_PF);
            return await this.page.textContent(Selectors.postForms.navigatePage_PF.postFormShortCode(validateNewPostName_PF));
        }
    }

    async validateProductPostFormCreated(validateNewPostName_PF: string) {
        //Return HOME
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
         

        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_PF = await this.page.isVisible(Selectors.postForms.navigatePage_PF.checkAddButton_PF);
        if (checkNewBlankFormCreatedValid_PF === true) {
            await this.checkElementText(Selectors.postForms.navigatePage_PF.postFormsPageFormsTitleCheck_PF(validateNewPostName_PF), validateNewPostName_PF);
            return await this.page.textContent(Selectors.postForms.navigatePage_PF.postFormShortCode(validateNewPostName_PF));
        }
    }


    /******************************************/
    /********** @RegistrationForms ***********/
    /****************************************/

    /********************* Profile Fields *********************/
    //ProfileFields
    async addProfileFields_RF() {
        //PostFields
         
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
         

        //ASSERTION > Check if-VALID
        const checkNewBlankFormCreatedValid_RF = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (checkNewBlankFormCreatedValid_RF === true) {
            await this.checkElementText(Selectors.registrationForms.navigatePage_RF.postFormsPageFormTitleCheck_RF, validateNewPostName_RF);
        }
    }
}