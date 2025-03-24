import dotenv from "dotenv";
dotenv.config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';


//import { TestData } from '../tests/testdata';



export class PostFormsPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
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
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        expect(await this.page.isVisible(Selectors.postForms.createBlankForm_PF.clickPostAddForm)).toBeTruthy();
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates
        //Click Blank Form
        await this.page.waitForSelector(Selectors.postForms.createBlankForm_PF.clickBlankForm);
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickBlankForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.page.click(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

        const formName = await this.page.innerText(Selectors.postForms.createBlankForm_PF.enterNewFormName);
        expect(formName).toBe(newPostName);
    };


    //PresetForm
    async createPresetPostForm(newPostName: string) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickPostForm
        //Templates 
        //Click Preset Form  
        await this.page.click(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.page.click(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

        const formName = await this.page.innerText(Selectors.postForms.createBlankForm_PF.enterNewFormName);
        expect(formName).toBe(newPostName);
    };


    //PresetForm
    async createPresetPostFormWithGuestEnabled(newPostName: string) {
        //Visit Post Form Page
        const wpufPostFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        await Promise.all([
            this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewPostForm
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        //Click Add Form
        await this.page.click(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        //ClickBlankForm
        //Templates 
        //Click Preset Form  
        await this.page.click(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        //Click Form Name Box
        await this.page.click(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.page.fill(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.page.click(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

        const formName = await this.page.innerText(Selectors.postForms.createBlankForm_PF.enterNewFormName);
        expect(formName).toBe(newPostName);
        
        //Click Form Editor again - to handle Shortcode tooltip
        await this.page.click(Selectors.postForms.formSettings.clickFormEditor);

        //Enabled Guest Post Submission
        //Click Form Settings
        await this.page.click(Selectors.postForms.formSettings.clickFormEditorSettings);
        //Click Submission Restrictions
        await this.page.click(Selectors.postForms.formSettings.clickSubmissionRestriction);
        //Enable Guest Post Submission
        await this.page.click(Selectors.postForms.formSettings.enableGuestPostCheckBox);
        //Save Form Settings
        await this.page.click(Selectors.postForms.formSettings.saveFormSettings);

        //Return
        //Form Editor Page
        await this.page.click(Selectors.postForms.formSettings.clickFormEditor);

        //Save Form Settings
        await this.page.click(Selectors.postForms.formSettings.saveFormSettings);

    };




}