import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls, PostForm, RegistrationForm } from '../utils/testData';
import { Base } from './base';



//Store data
//Post Title
// const postTitle = Urls.postForms.pfTitle;
//Post Description
const postDescription = PostForm.pfPostDescription;
//Excerp
const postExcerpt = RegistrationForm.rfEmail;
//Tags
const postTags = RegistrationForm.rfUsername;



export class PostFormsFrontendPage extends Base{

    constructor(page: Page) {
        super(page);
    }




    /************************************************* LITE *************************************************/
    /******* @Create Registration Forms - Lite > FrontEnd **********/
    /**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async createPostFormFrontend(postFormTitle: string) {
        //Go to Accounts page - FrontEnd
        const wpufRegistrationFormFage = Urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage ),
        ]);

        //Go to Submit Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.submitPostSideMenu);

        //Post Form process
        //Enter Post Title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postFormTitle);
        //Select Category
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, { label: 'Uncategorized' });
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postDescription);

        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, 'uploadeditems/sample_image.jpeg');
        //Enter Excerpt
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        //Enter Tags
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, postTags);
        //Create Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
        //Validate Post Submitted
        const validatePostSubmitted = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(postFormTitle));
        expect(validatePostSubmitted).toContain(postFormTitle);
    }





    /********************************************************/
    /******* @Validate PostForm Create - Frontend **********/
    /******************************************************/

    //Validate in Admin - Registered Form Submitted
    async validatePostFormCreatedFrontend(postFormTitle: string) {
        //Go to FrontEnd
        //Click Accounts
        await this.validateAndClick(Selectors.postForms.postFormsFrontendValidate.clickAccountsTopMenu);
        //Click Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendValidate.clickPostsSideMenu);
        //Validate First Item in List
        const validatePostCreated: string | null = await this.page.innerText(Selectors.postForms.postFormsFrontendValidate.validatePostSubmittedFE);
        //Validate created Post
        expect(validatePostCreated).toContain(postFormTitle);

    }
}