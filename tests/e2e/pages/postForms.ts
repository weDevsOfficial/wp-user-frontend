import dotenv from "dotenv";
dotenv.config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';


//import { TestData } from '../tests/testdata';



export class PostFormsPage extends Base {

    constructor(page: Page) {
        super(page);
    }


    /**********************************/
    /******* @Post Forms *************/
    /********************************/

    //BlankForm
    async createBlankFormPostForm(newPostName: string) {

        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.assertionValidate(Selectors.postForms.createBlankForm_PF.clickPostAddForm);
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        
        //Click Blank Form
        await this.page.waitForSelector(Selectors.postForms.createBlankForm_PF.clickBlankForm);
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickBlankForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    };


    //PresetForm
    async createPresetPostForm(newPostName: string) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickPostForm
        //Templates 
        //Hover over - Post Form
        // await this.page.waitForSelector(Selectors.postForms.createPreset_PF.hoverPresetForm);
        // await this.page.hover(Selectors.postForms.createPreset_PF.hoverPresetForm);
        //Click Preset Form  
        await this.validateAndClick(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    };


    //PresetForm
    async createPresetPostFormWithGuestEnabled(newPostName: string) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates 
        //Hover over - Blank Form
        // await this.page.waitForSelector(Selectors.postForms.createPreset_PF.hoverPresetForm);
        // await this.page.hover(Selectors.postForms.createPreset_PF.hoverPresetForm);
        //Click Preset Form  
        await this.validateAndClick(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);
        //Click Form Editor again - to handle Shortcode tooltip
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditor);

        //Enabled Guest Post Submission
        //Click Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditorSettings);
        //Click Submission Restrictions
        //await this.validateAndClick(Selectors.postForms.formSettings.clickSubmissionRestriction);
        //Enable Guest Post Submission
        await this.validateAndClick(Selectors.postForms.formSettings.setPostPermission);
        await this.validateAndClick(Selectors.postForms.formSettings.enableGuestPost);
        //Save Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);

        //Return
        //Form Editor Page
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditor);

        //Save Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);

    };




}