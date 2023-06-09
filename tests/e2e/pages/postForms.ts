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
        await this.page.click(selectors.postForms.createBlankFormPF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.createBlankFormPF.clickPostAddForm); 
  
        //ClickBlankForm
        //Templates 
        await this.page.waitForSelector(selectors.postForms.createBlankFormPF.hoverBlankForm);   
        await this.page.hover(selectors.postForms.createBlankFormPF.hoverBlankForm);   
        await this.page.waitForSelector(selectors.postForms.createBlankFormPF.clickBlankForm);   
        await this.page.click(selectors.postForms.createBlankFormPF.clickBlankForm);   

        //EnterName
        await this.page.click(selectors.postForms.createBlankFormPF.editNewFormName); 
        await this.page.fill(selectors.postForms.createBlankFormPF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.createBlankFormPF.confirmNewNameTickButton);  
    };


    //PresetForm
    async createPresetPostForm(newPostName) {
        //Visit Post Form Page
        const wpufPostFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
        
        await this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' }); 

        //CreateNewPostForm
        await this.page.click(selectors.postForms.createBlankFormPF.clickpostFormsMenuOption);
        //Start
        await this.page.click(selectors.postForms.createBlankFormPF.clickPostAddForm); 

        //ClickBlankForm
        //Templates 
        //await this.page.waitForSelector(selectors.postForms.createPresetPR.hoverPresetForm);   
        await this.page.hover(selectors.postForms.createPresetPR.hoverPresetForm);   
        //await this.page.waitForSelector(selectors.postForms.createPresetPR.clickPresetForm);   
        await this.page.click(selectors.postForms.createPresetPR.clickPresetForm);   

        //EnterName
        await this.page.click(selectors.postForms.createPresetPR.editNewFormName); 
        await this.page.fill(selectors.postForms.createBlankFormPF.enterNewFormName, newPostName);   
        await this.page.click(selectors.postForms.createBlankFormPF.confirmNewNameTickButton); 

    };



}