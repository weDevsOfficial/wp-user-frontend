require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


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
        const wpufPostFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        
        await this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }); 
        
        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.createBlankForm_PF.clickPostAddForm); 
  
        //ClickBlankForm
        //Templates 
        await this.page.waitForSelector(selectors.postForms.createBlankForm_PF.hoverBlankForm);   
        await this.page.hover(selectors.postForms.createBlankForm_PF.hoverBlankForm);   
        await this.page.waitForSelector(selectors.postForms.createBlankForm_PF.clickBlankForm);   
        await this.page.click(selectors.postForms.createBlankForm_PF.clickBlankForm);   

        //EnterName
        await this.page.click(selectors.postForms.createBlankForm_PF.editNewFormName); 
        await this.page.fill(selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);  
    };


    //PresetForm
    async createPresetPostForm(newPostName) {
        //Visit Post Form Page
        const wpufPostFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        
        await this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }); 

        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.createBlankForm_PF.clickPostAddForm); 

        //ClickBlankForm
        //Templates 
        //await this.page.waitForSelector(selectors.postForms.createPresetPR.hoverPresetForm);   
        await this.page.hover(selectors.postForms.createPreset_PF.hoverPresetForm);   
        //await this.page.waitForSelector(selectors.postForms.createPresetPR.clickPresetForm);   
        await this.page.click(selectors.postForms.createPreset_PF.clickPresetForm);   

        //EnterName
        await this.page.click(selectors.postForms.createPreset_PF.editNewFormName); 
        await this.page.fill(selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.createBlankForm_PF.confirmNewNameTickButton); 

    };



}