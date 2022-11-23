require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';


//import { TestData } from '../tests/testdata';

 

export class PostFormsFrontEnd {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


//3.0: FrontEND_Check_Blank_Form
    async checkNewPostBlankFormFrontEnd(newPostTitleName) {
        console.log("0003 > Running FRONT-END Check > POST FORM");
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);

        //Copy_Shortcode
        const storeShortCode = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.clickShortCode);
        console.log("3.0" + storeShortCode);

        //Go To Pages
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickLeftNavPages);

        //Add New Page
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.clickAddNewPageButton);
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickAddNewPageButton);
        //Add New Page > Add Title
        const BlockEditorPopup = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.newPageBlockEditorPopup1);
        if (BlockEditorPopup == true){
            await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.newPageBlockEditorPopupClose);
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.newPageBlockEditorPopupClose);
        }
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.newPageAddTitle);
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.newPageAddTitle);
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.newPageAddTitle, 'New: Post Form Page');
        //Add New Page > Add Shortcode
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.newPageAddBlockIcon);
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.newPageAddBlockSearch)
            await this.page.fill(SelectorsPage.frontEndCheckBlankForm.newPageAddBlockSearch, 'Shortcode')
            await this.page.click(SelectorsPage.frontEndCheckBlankForm.newPageAddBlockClickShortcode);

        // //Click on New Page > From Pages
        // await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickFormsPageFrontEndEdit);
        // await this.page.waitForLoadState('networkidle');

        //Wait for ShortCode Block
        const WaitforShortcodeBlock = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.shortCodeBlock)
        await expect(WaitforShortcodeBlock).toEqual("Shortcode");
        //Edit Shortcode
        //await this.page.waitForNavigation();
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.editShortCodeBlock);
        await this.page.waitForSelector(SelectorsPage.frontEndCheckBlankForm.editShortCodeBlock);
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.editShortCodeBlock, "");
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.editShortCodeBlock, storeShortCode);
        const CheckExpectedShortcodeBlock = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.editShortCodeBlock);
        await expect(CheckExpectedShortcodeBlock).toEqual(storeShortCode);
        await this.page.waitForLoadState('domcontentloaded')            

        //Update Edited Page
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickEditFormsPageUpdate);
        await this.page.waitForLoadState('domcontentloaded')
        console.log("3.1: Updating Page Done")

        //Go to Dashboard > Visist Site > FRONT-END
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickReturnToDashboard);
        //FRONT-END Click
        await this.page.hover(SelectorsPage.frontEndCheckBlankForm.hoverSiteName);
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickVisitSite);
        console.log("3.2: Site Visited")
            
        
        //FRONT-END Post Form Page
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndclickPostFormPage);
        const FrontEndPostPage = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndclickPostFormPage);
        await expect(FrontEndPostPage).toEqual("Post Form Page");
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndclickPostFormPage);
        console.log("3.3: Clicked on Page > VISITED")
        

        //Check BLANK FORM Items
        console.log("3.4: Started > Blank Form Item Check")
        //1.0
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostTitle);
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndPostTitle, newPostTitleName);
        //2.0
        const CheckPostContentBlock = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent);
        await expect(CheckPostContentBlock).toBeTruthy();
        //const IFramePostContent = 
        await this.page.frameLocator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent)
            .locator(SelectorsPage.frontEndCheckBlankForm.frontEndPostContentfill).fill("Test > Post Content");
        
        //3.0
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostExcerpt);
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndPostExcerpt);
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndPostExcerpt, "Test > Post Excerpt");
        //4.0 - Upload
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndFeaturedImageUpload);
        await this.page.setInputFiles(SelectorsPage.frontEndCheckBlankForm.frontEndFeaturedImageUpload, 'uploadeditems/Baby-Frog.png');
        
        //5.0
        await this.page.selectOption(SelectorsPage.frontEndCheckBlankForm.frontEndcategorySelection, '1')
        //.selectOption('1');
        //6.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndTags, 'Testing > Tags');
        //7.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndTextBox, 'Testing > Text box');
        //8.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndTextAreaBox, 'Sample Value > Text Area');
        //9.0
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndMultiSelect);
        //10.0
        await this.page.check(SelectorsPage.frontEndCheckBlankForm.frontEndRadioOption);
        //11.0
        await this.page.locator(SelectorsPage.frontEndCheckBlankForm.frontEndCheckbox);
        //12.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndWebsiteURL, 'https://www.google.com/');
        //13.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndEmailAddress, 'abc@gmail.com');
        //14.0 - Upload
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndImageUpload);
        await this.page.setInputFiles(SelectorsPage.frontEndCheckBlankForm.frontEndImageUpload, 'uploadeditems/PEPE.png');
        //15.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndRepeatField, 'Sample > Repeat Text');
        //16.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndDateTimeSet, '10/10/2022');
        //17.0
        await this.page.selectOption(SelectorsPage.frontEndCheckBlankForm.frontEndTimeField, '11:00 am');
        //18.0 - Upload
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndFileUpload);
        await this.page.setInputFiles(SelectorsPage.frontEndCheckBlankForm.frontEndFileUpload, ['uploadeditems/Macbook-Pro.jpeg', 'uploadeditems/Wolf-Icon.png']);
        //19.0
        await this.page.selectOption(SelectorsPage.frontEndCheckBlankForm.frontEndCountrySelect, 'US');
        //20.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndNumericField, '10');
        //21.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndPhoneNumber, '(201) 555-0123');
        //22.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndAddressFieldLine1, '20 Cooper St.');
            await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndAddressFieldCity, 'New York');
            await this.page.selectOption(SelectorsPage.frontEndCheckBlankForm.frontEndAddressFieldSelectCountry, 'US');
            await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndAddressFieldZipCode, '1060');     //TODO: No Input Field [ISSUE]
            await this.page.selectOption(SelectorsPage.frontEndCheckBlankForm.frontEndAddressFieldSelectState, 'NY');
        //23.0
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndEmbedField, 'Testing > Embed Field');
        //24.0 - Section Break
        const ValidateSectionBreak = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndSectionBreak);
        await expect(ValidateSectionBreak).toBeTruthy();
        //24.0
        const ValidateHTMLSection = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndHTMLSection);
        await expect(ValidateHTMLSection).toBeTruthy();
        //25.0
        const ValidateShortcode = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndShortCode);
        await expect(ValidateShortcode).toBeTruthy(); //TODO: Has issue > no type space [ISSUE]
        //26.0
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndCheckTermsAndConditions); 
        //27.0
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndRating5);
        //28.0
        //TODO: Integrate MATH Captcha
        const Value1  = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaValue1);
        const Value2 = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaValue2);    
        const Operator = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaOperator);
            if (Operator == '+') {
                const InputValue = (+Value1) + (+Value2);               
                const FillValue = InputValue.toString();
                await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);
            }
            else if (Operator == '-') {
                const InputValue = (+Value1) - (+Value2);
                const FillValue = InputValue.toString();
                await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);             }
            else {
                const InputValue = (+Value1) * (+Value2);
                const FillValue = InputValue.toString();
                await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndCaptchaInputBox, FillValue);
            }

        //SUBMIT
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndSubmitForm);
        await this.page.waitForLoadState('domcontentloaded')
        console.log("3.5: Ended > Blank Form Item Check")


        //VALIDATE Form Submission
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndClickHomePage);
        const FrontEndHomePage = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndClickHomePage);
            await expect(FrontEndHomePage).toEqual("Home");

        //Check ITEM
        const ValidateFormSubmitted = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndValiteFormSubmitted);
            await expect(ValidateFormSubmitted).toContain(newPostTitleName);
            console.log("3.6: Form Submission: VALIDATED");

        }




}
