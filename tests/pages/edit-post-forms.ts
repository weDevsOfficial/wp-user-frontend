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
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);        //Basic Settings
        
        //Edit_Post_Title > Item1
        await expect(this.page.isVisible(SelectorsPage.editPostForm.postFormListItem1)).toBeTruthy();
        await this.page.click(SelectorsPage.editPostForm.postFormListItem1);
        console.log("5.0: Edit Start");

        //Edit > Post Title
        await this.page.hover(SelectorsPage.editPostForm.editPost_PostTitle_Hover);
        await this.page.click(SelectorsPage.editPostForm.editPost_PostTitle_EditButton);
            //Input Label
            await this.page.fill(SelectorsPage.editPostForm.editPost_PostTitle_InputLabel, "Post Label - Test001");
            console.log("5.0: Post Title > Input Label");
            //Input Help Text
            await this.page.fill(SelectorsPage.editPostForm.editPost_PostTitle_InputHelpText, "Post Help Text - Test001");
            console.log("5.0: Post Title > Input Help Text");
            
            //Required Field
            await this.page.click(SelectorsPage.editPostForm.editPost_PostTitle_InputHelpText);
            console.log("5.0: Post Title > Input Help Text");
            

            
        //Advanced Settings

        //Conditional Settings

    }

    //5.2: Edit_Taxonomies
    async edit_PostBlankForm_Taxonomies() {
        
    }

    //5.3: Edit_Taxonomies
    async edit_PostBlankForm_Custom_Fields() {
        
        
    }

    //5.4: Edit_Taxonomies
    async edit_PostBlankForm_Others() {
        
        
    }


    async FieldOptionFunction() {

    }


}
