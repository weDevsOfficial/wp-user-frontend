require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { Urls, PostForm, RegistrationForm } from '../utils/testData';



//Store data
//Post Title
// const postTitle = Urls.postForms.pfTitle;
//Post Description
const postDescription = PostForm.pfPostDescription;
//Excerp
const postExcerpt = RegistrationForm.rfEmail;
//Tags
const postTags = RegistrationForm.rfUsername;



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
        const wpufRegistrationFormFage = Urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
        ]);

        //Go to Submit Post
        await this.page.click(selectors.postForms.postFormsFrontendCreate.submitPostSideMenu);

        //Post Form process
        //Enter Post Title
        await this.page.fill(selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postFormTitle);
        //Select Category
        await this.page.selectOption(selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, { label: 'Uncategorized' });
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
        //Validate Post Submitted
        const validatePostSubmitted = await this.page.innerText(selectors.postForms.postFormsFrontendCreate.validatePostSubmitted);
        expect(validatePostSubmitted).toContain(postFormTitle);
    };





    /********************************************************/
    /******* @Validate PostForm Create - Frontend **********/
    /******************************************************/

    //Validate in Admin - Registered Form Submitted
    async validatePostFormCreatedFrontend(postFormTitle) {
        //Go to FrontEnd
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