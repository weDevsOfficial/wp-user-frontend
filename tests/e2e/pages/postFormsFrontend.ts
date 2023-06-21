require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData';


 
    //Store data
    //Post Title
    // const postTitle = testData.postForms.pfTitle;
    //Post Description
    const postDescription = testData.postForms.pfPostDescription;
    //Excerp
    const postExcerpt = testData.registrationForms.rfEmail;
    //Tags
    const postTags = testData.registrationForms.rfUsername;



export class postFormsFrontend {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }




/************************************************* LITE *************************************************/
/******* @Create Registration Forms - Lite > FrontEnd **********/
/**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async createPostFormFrontend(postFormTitle) {
        //Go to Accounts page - FrontEnd
        const wpufRegistrationFormFage = testData.urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
        ]);

        //Go to Submit Post
        await this.page.click(selectors.postForms.postFormsFrontendCreate.submitPostSideMenu);

        //Post Form process
        //Enter Post Title
        await this.page.fill(selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postFormTitle);
        //Select Category
        await this.page.selectOption(selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, {label: 'Uncategorized'});
        //Enter Post Description
        await this.page.frameLocator(selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postDescription);

        //Add Featured Photo
        await this.page.setInputFiles(selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, 'uploadeditems/sample_image.jpeg');
        //Enter Excerpt
        await this.page.fill(selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        //Enter Tags
        await this.page.fill(selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, postTags);
        //Create Post
        await this.page.click(selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
        await this.page.waitForLoadState('domcontentloaded');
        // //Validate Post Submitted
        // const validatePostSubmitted = await this.page.innerText(selectors.postForms.postFormsFrontendCreate.validatePostSubmitted);
        // expect(validatePostSubmitted).toContain(postFormTitle);
    };





/********************************************************/
/******* @Validate PostForm Create - Frontend **********/
/******************************************************/

    //Validate in Admin - Registered Form Submitted
    async validatePostFormCreatedFrontend(postFormTitle) {
        //Go to FrontEnd
        // await Promise.all([
        //     this.page.goto(testData.urls.baseUrl, { waitUntil: 'networkidle' }),
        // ]);
        //Click Accounts
        await this.page.click(selectors.postForms.postFormsFrontendValidate.clickAccountsTopMenu);
        //Click Post
        await this.page.click(selectors.postForms.postFormsFrontendValidate.clickPostsSideMenu);
        //Validate First Item in List
        const validatePostCreated = await this.page.innerText(selectors.postForms.postFormsFrontendValidate.validatePostSubmittedFE);
        //Validate created Post
        await expect(validatePostCreated).toContain(postFormTitle);
        
    };







}