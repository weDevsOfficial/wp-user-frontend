require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { SelectorsPage } from './selectors';
 

export class PostFormsFrontEnd {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


//3.0: FrontEND_Check_Blank_Form
    async checkNewPostBlankFormFrontEnd(newPostTitleName) {
        console.log("0003 > Running FRONT-END Check > POST FORM");
        await this.page.click(SelectorsPage.createPostForm.clickPostFormMenuOption);

        //Validate Post Form Created
        await this.page.waitForLoadState('domcontentloaded');
        //ASSERTION > Check if-VALID
        const checkNew_BlankForm_CreatedValid = await this.page.isVisible(SelectorsPage.login.wpufPostForm_CheckAddButton);
        console.log("2.7: " + checkNew_BlankForm_CreatedValid) //Check STATUS
        if (checkNew_BlankForm_CreatedValid == true) {  
            const checkNewFormCreated = await this.page.innerText(SelectorsPage.login.postFormsPageFormTitleCheck);
            console.log("2.8: Text is -> " + checkNewFormCreated)
            await expect(checkNewFormCreated).toContain(process.env.NEW_POST_BLANK_FORMNAME);
        }
        const StorePostFormName = await this.page.innerText(SelectorsPage.login.postFormsPageFormTitleCheck);
        
        //Go to Settings
        await this.page.isVisible('//a[contains(text(), "Settings")]');
        await this.page.click('//a[contains(text(), "Settings")]');
        console.log("2.9: Settings Page Visited");

        //Go to Front-End Posting
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.isVisible('//a[@id="wpuf_frontend_posting-tab"]');
        await this.page.click('//a[@id="wpuf_frontend_posting-tab"]');
        console.log("3.0: FrontEnd Posting Selected < Settings");

        //Dropdown check
        await this.page.isVisible('//select[@id="wpuf_frontend_posting[default_post_form]"]');
        //Select > Dropdown
        await this.page.selectOption('//select[@id="wpuf_frontend_posting[default_post_form]"]', {label: StorePostFormName});
        const CheckPostFormSelected = await this.page.innerText('//select[@id="wpuf_frontend_posting[default_post_form]"]');
        console.log("3.1: Selected Value from Dropdown");
        //Click Save
        await this.page.click("(//p[@class='submit']//input)[2]");
        console.log("3.2: Save FrontEnd Posting");


        //Go to Dashboard > Visist Site > FRONT-END
        //await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickReturnToDashboard);
        //FRONT-END Click
        await this.page.hover(SelectorsPage.frontEndCheckBlankForm.hoverSiteName);
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.clickVisitSite);
        console.log("3.3: Site Visited");
            
        
        //FRONT-END Post Form Page
        await expect(this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndClickAccount)).toBeTruthy();
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndClickAccount);
        await expect(this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndClickSubmitPost)).toBeTruthy();
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndClickSubmitPost);
        await this.page.waitForLoadState('domcontentloaded');
        console.log("3.4: Clicked on Account Page");
        

        //Check BLANK FORM Items
        console.log("3.5: Started > Blank Form Item Check");
        //1.0
        await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostTitle);
        await this.page.fill(SelectorsPage.frontEndCheckBlankForm.frontEndPostTitle, newPostTitleName);
        //2.0
        // const CheckPostContentBlock = await this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndPostContent);
        // await expect(CheckPostContentBlock).toBeTruthy();
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
        console.log("3.6: Ended > Blank Form Item Check")


        //VALIDATE Form Submission
        await this.page.waitForLoadState('domcontentloaded');
        await expect(this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndClickAccount)).toBeTruthy();
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndClickAccount);
        await expect(this.page.isVisible(SelectorsPage.frontEndCheckBlankForm.frontEndClickPost)).toBeTruthy();
        await this.page.click(SelectorsPage.frontEndCheckBlankForm.frontEndClickPost);

        //Check ITEM
        const ValidateFormSubmitted = await this.page.innerText(SelectorsPage.frontEndCheckBlankForm.frontEndPostTableItem1);
            await expect(ValidateFormSubmitted).toContain(newPostTitleName);
            console.log("3.7: Form Submission: VALIDATED");

        }




}
