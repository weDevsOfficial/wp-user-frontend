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

        //Post Title
        await this.Function1(SelectorsPage.editPostForm.editPost_PostTitle_Hover, 
                        SelectorsPage.editPostForm.editPost_PostTitle_EditButton);
        //Post Content
        await this.Function1(SelectorsPage.editPostForm.editPost_PostContent_Hover, 
                        SelectorsPage.editPostForm.editPost_PostContent_EditButton);
        //Post Excerpt
        await this.Function1(SelectorsPage.editPostForm.editPost_PostExcerpt_Hover, 
                        SelectorsPage.editPostForm.editPost_PostExcerpt_EditButton);
        //Featured Image
        await this.Function1(SelectorsPage.editPostForm.editPost_FeaturedImage_Hover, 
                        SelectorsPage.editPostForm.editPost_FeaturedImage_EditButton);

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


















    /**
     * 
     *
     * Function1 - Common for All
     *  
     */

    async Function1(hoverItem, clickItem){
        await this.page.hover(SelectorsPage.editPostForm.editPost_PostTitle_Hover);
        await this.page.click(SelectorsPage.editPostForm.editPost_PostTitle_EditButton);
        
        //Post Title
        //Input Label
        await this.page.fill(SelectorsPage.editPostForm.editPost_PostTitle_InputLabel, "Post Label - Test001");
        //TODO; CHeck if filled
        console.log("5.0: Post Title > Input Label");
        //Input Help Text
        await this.page.fill(SelectorsPage.editPostForm.editPost_PostTitle_InputHelpText, "Post Help Text - Test001");
        console.log("5.0: Post Title > Input Help Text");
        //Required Field
        await this.page.click(SelectorsPage.editPostForm.editPost_PostTitle_InputHelpText);
        console.log("5.0: Post Title > Input Help Text");


        //Advanced Options
        //PlaceHolder
        const PaceHolderText = await this.page.isVisible('');
            if (PaceHolderText == true) {

            }
        //DefaulValue
        await this.page.fill('', '');
        //TextArea
        const TextArea = await this.page.isVisible('');
            if (PaceHolderText == true) {

            }
        

    }



}
