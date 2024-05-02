require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { Urls } from '../utils/testData';


//import { TestData } from '../tests/testdata';



export class postForms {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


    /**********************************/
    /******* @Post Forms *************/
    /********************************/

    //BlankForm
    async createBlankFormPostForm(newPostName) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        expect(await this.page.isVisible(selectors.postForms.createBlankForm_PF.clickPostAddForm)).toBeTruthy();
        await this.page.click(selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates
        //Hover over - Blank Form
        await this.page.waitForSelector(selectors.postForms.createBlankForm_PF.hoverBlankForm);
        await this.page.hover(selectors.postForms.createBlankForm_PF.hoverBlankForm);
        //Click Blank Form
        await this.page.waitForSelector(selectors.postForms.createBlankForm_PF.clickBlankForm);
        await this.page.click(selectors.postForms.createBlankForm_PF.clickBlankForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.page.click(selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    };


    //PresetForm
    async createPresetPostForm(newPostName) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        await this.page.click(selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates 
        //Hover over - Preset Form
        await this.page.waitForSelector(selectors.postForms.createPreset_PF.hoverPresetForm);
        await this.page.hover(selectors.postForms.createPreset_PF.hoverPresetForm);
        //Click Preset Form  
        await this.page.click(selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.page.click(selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    };


    //PresetForm
    async createPresetPostFormWithGuestEnabled(newPostName) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        await this.page.click(selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates 
        //Hover over - Blank Form
        await this.page.waitForSelector(selectors.postForms.createPreset_PF.hoverPresetForm);
        await this.page.hover(selectors.postForms.createPreset_PF.hoverPresetForm);
        //Click Preset Form  
        await this.page.click(selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        //Click Form Name Box
        await this.page.click(selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);
        //Click Form Editor again - to handle Shortcode tooltip
        await this.page.click(selectors.postForms.formSettings.clickFormEditor);

        //Enabled Guest Post Submission
        //Click Form Settings
        await this.page.click(selectors.postForms.formSettings.clickFormEditorSettings);
        //Click Submission Restrictions
        await this.page.click(selectors.postForms.formSettings.clickSubmissionRestriction);
        //Enable Guest Post Submission
        await this.page.click(selectors.postForms.formSettings.enableGuestPostCheckBox);
        //Save Form Settings
        await this.page.click(selectors.postForms.formSettings.saveFormSettings);

        //Return
        //Form Editor Page
        await this.page.click(selectors.postForms.formSettings.clickFormEditor);

        //Save Form Settings
        await this.page.click(selectors.postForms.formSettings.saveFormSettings);

    };




}