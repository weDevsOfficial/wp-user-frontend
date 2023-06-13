require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


 
    //Store data
    //Post Title
    const postTitle = testData.postForms.pfTitle;
    //Post Description
    const postDescription = testData.postForms.pfPostDescription;
    //Excerp
    const postExcerpt = testData.registrationForms.rfEmail;
    //Tags
    const postTags = testData.registrationForms.rfUsername;



export class postFormsFrontEnd {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }




/************************************************* LITE *************************************************/
/******* @Create Registration Forms - Lite > FrontEnd **********/
/**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async createPostFormFrontEnd() {
        //Go to Accounts page - FrontEnd
        const wpufRegistrationFormFage = testData.urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
        ]);

        //Go to Submit Post
        await this.page.click('//a[contains(text(),"Submit Post")]');

        //Post Form process
        //Enter Post Title
        await this.page.fill('//input[@name="post_title"]', postTitle);
        //Select Category
        await this.page.selectOption('//select[@data-type="select"]', {label: 'Uncategorized'});
        //Enter Post Description
        await this.page.frameLocator('//div[contains(@class,"mce-edit-area mce-container")]//iframe[1]')
            .locator('//body[@id="tinymce"]').fill(postDescription);

        //Add Featured Photo
        await this.page.setInputFiles('(//input[@type="file"])[2]', 'uploadeditems/sample_image.jpeg');
        //Enter Excerpt
        await this.page.fill('//textarea[@name="post_excerpt"]', postExcerpt);
        //Enter Tags
        await this.page.fill('//input[@name="tags"]', postTags);
        //Create Post
        await this.page.click('//input[@value="Create Post"]');

    };





/********************************************************/
/******* @Validate PostForm Create - Frontend **********/
/******************************************************/

    //Validate in Admin - Registered Form Submitted
    async validatePostFormCreatedFrontend() {
        //Go to Accounts page - FrontEnd
        const wpufRegistrationFormFage = testData.urls.baseUrl + '/account/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
        ]);

        //Click Post
        await this.page.click('//a[contains(text(),"Posts")]');
        //Validate First Item in List
        const validatePostCreated = await this.page.innerText('(//td[@data-label="Title: "])[1]');
        //Validate created Post
        await expect(validatePostCreated).toContain(postTitle);
        
    };







}