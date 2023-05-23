require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../../utils/testData'


//import { TestData } from '../tests/testdata';

 

export class postFormsCreate {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }



    /**
     * 
     * 
     * @BlankForm Admin creates a new Blank Post Form 
     * @PresetForm Admin creates a new Preset Post Form
     * 
     * 
     * 
     */
    //BlankForm
    async create_BlankForm_PF(newPostName) {
        //Visit Post Form Page
        const wpuf_post_form_page = testData.urls.baseUrl + 'admin.php?page=wpuf-post-forms';
        
        await this.page.goto(wpuf_post_form_page, { waitUntil: 'networkidle' }); 
        
        //Create_New_Post_Form
        await this.page.click(selectors.postForms.create_BlankForm_PF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.create_BlankForm_PF.clickPostAddForm); 
  
        //Click_Blank_Form
        //Templates 
        await this.page.waitForSelector(selectors.postForms.create_BlankForm_PF.hover_Blank_Form);   
        await this.page.hover(selectors.postForms.create_BlankForm_PF.hover_Blank_Form);   
        await this.page.waitForSelector(selectors.postForms.create_BlankForm_PF.click_Blank_Form);   
        await this.page.click(selectors.postForms.create_BlankForm_PF.click_Blank_Form);   

        //Enter_Name
        await this.page.click(selectors.postForms.create_BlankForm_PF.editNewFormName); 
        await this.page.fill(selectors.postForms.create_BlankForm_PF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.create_BlankForm_PF.confirmNewNameTickButton);  
    };


    //PresetForm
    async create_Preset_PF(newPostName) {
        //Visit Post Form Page
        const wpuf_post_form_page = testData.urls.baseUrl + 'admin.php?page=wpuf-post-forms';
        
        await this.page.goto(wpuf_post_form_page, { waitUntil: 'networkidle' }); 

        //Create_New_Post_Form
        await this.page.click(selectors.postForms.create_BlankForm_PF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.create_BlankForm_PF.clickPostAddForm); 

        //Click_Blank_Form
        //Templates 
        //await this.page.waitForSelector(selectors.postForms.create_Preset_PR.hover_Preset_Form);   
        await this.page.hover(selectors.postForms.create_Preset_PR.hover_Preset_Form);   
        //await this.page.waitForSelector(selectors.postForms.create_Preset_PR.click_Preset_Form);   
        await this.page.click(selectors.postForms.create_Preset_PR.click_Preset_Form);   

        //Enter_Name
        await this.page.click(selectors.postForms.create_Preset_PR.editNewFormName); 
        await this.page.fill(selectors.postForms.create_BlankForm_PF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.create_BlankForm_PF.confirmNewNameTickButton); 

    };




















    /**
     * 
     * @Action1 Admin adds Post Fields to Form
     * @Action2 Admin adds Taxonomies to Form
     * @Action3 Admin adds Custom Fields to Form
     * @Action4 Admin adds Others to Form
     * @Action5 Admin saves Form Creation
     * 
     * 
     */
/********************* PostFields *********************/
    //PostFields
    async add_PostFields_PF() {
        //Post_Fields
        await this.page.click(selectors.postForms.add_PostFields_PF.postTitleBlock);
        await this.page.click(selectors.postForms.add_PostFields_PF.postContentBlock);
        await this.page.click(selectors.postForms.add_PostFields_PF.postExcerptBlock);
        await this.page.click(selectors.postForms.add_PostFields_PF.featuredImageBlock);

        await this.page.waitForLoadState('domcontentloaded');
    };

    //Validate > PostFields
    async validate_PostFields_PF() {
        //Validate
        //Post Title
        await expect(await this.page.isVisible(selectors.postForms.validate_PostFields_PF.val_PostTitle)).toBeTruthy();
        //Post Description
        await expect(await this.page.isVisible(selectors.postForms.validate_PostFields_PF.val_PostContent)).toBeTruthy();
        //Excerpt
        await expect(await this.page.isVisible(selectors.postForms.validate_PostFields_PF.val_Excerpt)).toBeTruthy();
        //Featured Image
        await expect(await this.page.isVisible(selectors.postForms.validate_PostFields_PF.val_FeaturedImage)).toBeTruthy();
        
    };



/********************* Taxonomies *********************/
    //Taxonomies
    async add_Taxonomies_PF() {
        //Taxonomies
        await this.page.click(selectors.postForms.add_Taxonomies_PF.categoryBlock);
        await this.page.click(selectors.postForms.add_Taxonomies_PF.tagsBlock);

        await this.page.waitForLoadState('domcontentloaded');
    };

    //Validate > Taxonomies
    async validate_Taxonomies_PF() {
        //Validate
        //Category
        await expect(await this.page.isVisible(selectors.postForms.validate_Taxonomies_PF.val_Category)).toBeTruthy();
        //Tags
        await expect(await this.page.isVisible(selectors.postForms.validate_Taxonomies_PF.val_Tags)).toBeTruthy();
    };

    async validate_Taxonomies_Preset_PF() {
        //Validate
        //Category
        await expect(await this.page.isVisible(selectors.postForms.validate_Taxonomies_Preset_PF.val_Category)).toBeTruthy();
        //Tags
        await expect(await this.page.isVisible(selectors.postForms.validate_Taxonomies_Preset_PF.val_Tags)).toBeTruthy();
    };


/********************* CustomFields *********************/
    //CustomFields
    async add_CustomFields_PF() {
        //Custom_Fields
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsText);
            if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose)) {
                await this.page.click(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose);
            }         
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsTextarea);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsDropdown);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsMultiSelect);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsRadio);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsCheckBox);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsWebsiteUrl);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsEmailAddress);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsHiddenField);
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsImageUpload);
        
        //From_PRO
        //RepeatField
        await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsRepeatField);
        const check_Pro_Popup = await this.page.isVisible(selectors.postForms.add_CustomFields_PF.check_Pro_Pop_UP);
            if (check_Pro_Popup === true) {
                await this.page.click(selectors.postForms.add_CustomFields_PF.check_Pro_Pop_UP);
                console.log("WPUF Pro is requred...")
            }
            else {
                //DateTime
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsDateTime);
                //TimeField
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsTimeField);
                //FileUpload    
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsFileUpload);
                //CountryList
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsCountryList);
                //NumericField
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsNumericField);
                //PhoneField
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsPhoneField); 
                //AddressField  
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsAddressField);
                //GoogleMaps
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsGoogleMaps);
                    if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk)) {
                        await this.page.click(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk);
                    } 
                //StepStart
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsStepStart);
                    if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose)) {
                        await this.page.click(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose);
                    }
                //Embed
                await this.page.click(selectors.postForms.add_CustomFields_PF.customFieldsEmbed); //TODO: This is an Error as position changes in Lite and Pro

                await this.page.waitForLoadState('domcontentloaded');

            }
        
    };

    //Validate > CustomFields
    async validate_CustomFields_PF() {
        //Validate
        //Text
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_Text)).toBeTruthy();
        //Textarea
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_Textarea)).toBeTruthy();
        //Dropdown
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_Dropdown)).toBeTruthy();
        //MultiSelect
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_MultiSelect)).toBeTruthy();
        //Radio
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_Radio)).toBeTruthy();
        //CheckBox
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_CheckBox)).toBeTruthy();
        //WebsiteUrl
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_WebsiteUrl)).toBeTruthy();
        //EmailAddress
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_EmailAddress)).toBeTruthy();
        //HiddenField
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_HiddenField)).toBeTruthy();
        //ImageUpload
        await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_ImageUpload)).toBeTruthy();
        
        //From PRO
        //RepeatField
        const pro_CustomFields_PF = await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_RepeatField);
        if (pro_CustomFields_PF === true) {
            //RepeatField
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_RepeatField)).toBeTruthy();
            //DateTime
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_DateTime)).toBeTruthy();
            //TimeField
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_TimeField)).toBeTruthy();
            //FileUpload
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_FileUpload)).toBeTruthy();
            //CountryList
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_CountryList)).toBeTruthy();
            //NumericField
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_NumericField)).toBeTruthy();
            //PhoneField
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_PhoneField)).toBeTruthy();
            //AddressField
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_AddressField)).toBeTruthy();
            
            //GoogleMaps        //TODO: Setup required
                // if(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_GoogleMaps) == true){
                //     await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_GoogleMaps)).toBeTruthy();
                // }

            //StepStart
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_StepStart)).toBeTruthy();
            //Embed
            await expect(await this.page.isVisible(selectors.postForms.validate_CustomFields_PF.val_Embed)).toBeTruthy();

        }
        
        
    
    };


/********************* Others *********************/
    //Others
    async add_Others_PF() {
        //Others
        await this.page.click(selectors.postForms.add_Others_PF.othersColumns);
        await this.page.click(selectors.postForms.add_Others_PF.othersSectionBreak);
        await this.page.click(selectors.postForms.add_Others_PF.othersCustomHTML);
        await this.page.click(selectors.postForms.add_Others_PF.othersReCaptcha);
            if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk)) {
                await this.page.click(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk);
            } 


        //From_PRO
        await this.page.click(selectors.postForms.add_Others_PF.othersShortCode);
        const check_Pro_Popup = await this.page.isVisible(selectors.postForms.add_CustomFields_PF.check_Pro_Pop_UP);
            if (check_Pro_Popup === true) {
                await this.page.click(selectors.postForms.add_CustomFields_PF.check_Pro_Pop_UP);
                console.log("WPUF Pro is requred...")
            }

            else {
                //ShortCode
                if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose)) {
                    await this.page.click(selectors.postForms.add_CustomFields_PF.prompt1PopUpModalClose);
                } 
                //ActionHook
                await this.page.click(selectors.postForms.add_Others_PF.othersActionHook);
                //TermsAndConditions
                await this.page.click(selectors.postForms.add_Others_PF.othersTermsAndConditions);
                //Ratings
                await this.page.click(selectors.postForms.add_Others_PF.othersRatings);
                //ReallySimpleCaptcha
                await this.page.click(selectors.postForms.add_Others_PF.othersReallySimpleCaptcha);
                    if (await this.page.isVisible(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk)) {
                        await this.page.click(selectors.postForms.add_CustomFields_PF.prompt2PopUpModalOk);
                    } 
                //MathCaptcha
                await this.page.click(selectors.postForms.add_Others_PF.othersMathCaptcha);
                await this.page.waitForLoadState('domcontentloaded');
            }

    };

    //Validate > Others
    async validate_Others_PF() {
        //Validate
        //Columns
        await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_Columns)).toBeTruthy();
        //SectionBreak
        await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_SectionBreak)).toBeTruthy();
        //CustomHTML
        await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_CustomHTML)).toBeTruthy();
        
        //From PRO
        //ReCaptcha
            //Not visible
        //Shortcode
        const pro_Others_PF = await this.page.isVisible(selectors.postForms.validate_Others_PF.val_Shortcode);
        if (pro_Others_PF === true) {
            //Shortcode
            await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_Shortcode)).toBeTruthy();
            //ActionHook
            await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_ActionHook)).toBeTruthy();
            //TermsAndConditions
            await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_TermsAndConditions)).toBeTruthy();
            //Ratings
            await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_Ratings)).toBeTruthy();
            //ReallySimpletCaptcha
                //Not visible
            //MathCaptcha
            await expect(await this.page.isVisible(selectors.postForms.validate_Others_PF.val_MathCaptcha)).toBeTruthy();
        }
    };


    //Settings > MultiStep Check
    async set_MultiStep_Settings_PF() {
        await this.page.waitForLoadState('domcontentloaded');
        //Add Multi-Step-Check
        await this.page.click(selectors.postForms.add_Others_PF.formEditorSettings);
        const pro_Text_Alert_In_Settings = await this.page.isVisible(selectors.postForms.add_CustomFields_PF.pro_Text_Alert_In_Settings);
            if (pro_Text_Alert_In_Settings === true) {  
                console.log("WPUF Pro is requred...");
            }
            else {
                await this.page.click(selectors.postForms.add_Others_PF.checkMultiStepOption);
                expect(await this.page.isChecked(selectors.postForms.add_Others_PF.checkMultiStepOption)).toBeTruthy();
            }
        

        await this.page.waitForLoadState('domcontentloaded');
    };



/********************* SaveForm *********************/
    //SaveForm
    async save_Form_PF(validateNewPostName_PF) {
        //Finish
        await this.page.waitForLoadState('domcontentloaded');
            const checkNewFormName_PF = await this.page.innerText(selectors.postForms.save_Form_PF.formName_ReCheck);
            await expect(checkNewFormName_PF).toContain(validateNewPostName_PF);
        expect(await this.page.isVisible(selectors.postForms.save_Form_PF.saveFormButton)).toBeTruthy();
        await this.page.click(selectors.postForms.save_Form_PF.saveFormButton);
    };












    /**
     * 
     * @Here Admin checks if Created form is displayed in Post Forms - Table/List
     * 
     * 
     */

    async validate_BlankForm_Created_PF(validateNewPostName_PF) {
        //Return HOME
        await this.page.click(selectors.postForms.create_BlankForm_PF.clickpostFormsMenuOption);
        await this.page.waitForLoadState('domcontentloaded');
        
        //ASSERTION > Check if-VALID
        const checkNew_BlankForm_CreatedValid_PF = await this.page.isVisible(selectors.postForms.navigate_PF_Page.checkAddButton_PF);
        if (checkNew_BlankForm_CreatedValid_PF == true) {  
            const checkNewFormCreated_PF = await this.page.innerText(selectors.postForms.navigate_PF_Page.postFormsPageFormsTitleCheck_PF);
            await expect(checkNewFormCreated_PF).toContain(validateNewPostName_PF);
            console.log(checkNewFormCreated_PF);
            console.log(validateNewPostName_PF);
        }
    };






}