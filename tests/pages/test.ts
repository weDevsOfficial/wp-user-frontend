require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';


//import { TestData } from '../tests/testdata';

 

export class Practice {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

//1.1: FrontEND_Check_Blank_Form
    async checkNewPostBlankFormFrontEnd(newPostName) {
            //Visit Site
            await this.page.hover(SelectorsPage.frontEndCheckBlankForm.hoverSiteName);
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickVisitSite);
            console.log("Site Visited")
                
            
            //FRONT-END Post Form Page
            const FrontEndPostPage = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndclickPostFormPage);
            await expect(FrontEndPostPage).toEqual("Post Form Page");
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndclickPostFormPage);
            console.log("> Clicked on Page > VISITED")
            

           
            //28.0
            // //TODO: Integrate MATH Captcha
            // const Value1  = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaValue1);
            // const Value2 = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaValue2);    
            // const Operator = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaOperator);
            //     if (Operator == '+') {
            //         const InputValue = (+Value1) + (+Value2);               
            //         const FillValue = InputValue.toString();
            //         await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);
            //     }
            //     else if (Operator == '-') {
            //         const InputValue = (+Value1) - (+Value2);
            //         const FillValue = InputValue.toString();
            //         await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);             }
            //     else {
            //         const InputValue = (+Value1) * (+Value2);
            //         const FillValue = InputValue.toString();
            //         await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);
            //     }
            // await this.page.pause();

            // await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent);
            // const FrameLocator1 = await this.page.frameLocator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent)
            //     FrameLocator1.locator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContentfill).fill("Test > Post Content");

           
            await this.page.frameLocator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent)
                .locator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContentfill).fill("Test > Post Content");
                

            //await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent)







        }





//3.0: EDIT
    async editPostForm(editPostForm: string) {

    }
}
