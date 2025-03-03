require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
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



export class PostFormsFrontendPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }




    /************************************************* LITE *************************************************/
    /******* @Create Registration Forms - Lite > FrontEnd **********/
    /**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async createPostFormFrontend(postFormTitle: string) {
        //Go to Accounts page - FrontEnd
        const wpufRegistrationFormFage = Urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
        ]);

        //Go to Submit Post
        await this.page.click(Selectors.postForms.postFormsFrontendCreate.submitPostSideMenu);

        //Post Form process
        //Enter Post Title
        await this.page.fill(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postFormTitle);
        //Select Category
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, { label: 'Uncategorized' });
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postDescription);

        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, 'uploadeditems/sample_image.jpeg');
        //Enter Excerpt
        await this.page.fill(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        //Enter Tags
        await this.page.fill(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, postTags);
        //Create Post
        await this.page.click(Selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
        //Validate Post Submitted
        const validatePostSubmitted = await this.page.innerText(`//h1[normalize-space(text())='${postFormTitle}']`);
        expect(validatePostSubmitted).toContain(postFormTitle);
    };





    /********************************************************/
    /******* @Validate PostForm Create - Frontend **********/
    /******************************************************/

    //Validate in Admin - Registered Form Submitted
    async validatePostFormCreatedFrontend(postFormTitle: string) {
        //Go to FrontEnd
        //Click Accounts
        await this.page.click(Selectors.postForms.postFormsFrontendValidate.clickAccountsTopMenu);
        //Click Post
        await this.page.click(Selectors.postForms.postFormsFrontendValidate.clickPostsSideMenu);
        //Validate First Item in List
        const validatePostCreated: string = await this.page.innerText(Selectors.postForms.postFormsFrontendValidate.validatePostSubmittedFE);
        //Validate created Post
        expect(validatePostCreated).toContain(postFormTitle);

    };







}