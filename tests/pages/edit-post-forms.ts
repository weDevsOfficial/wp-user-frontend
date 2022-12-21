require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';


//import { TestData } from '../tests/testdata';

 

export class EditPostForms {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

//5.0: Edit_BLANK_FORM
    //5.1: Edit_PostFields
    async edit_PostBlankForm_PostFields() {
        //Edit_Post_Title
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);        //Basic Settings
        
        //Advanced Settings

        //Conditional Settings

    }

    //5.2: Edit_Taxonomies
    async edit_PostBlankForm_Taxonomies() {
        
        
    }







}
