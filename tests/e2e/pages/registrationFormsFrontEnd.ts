require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


//Store data
//First Name
const firstName = testData.registrationForms.rfFirstName;
//Last Name
const lastName = testData.registrationForms.rfFirstName;
//Email
const email = testData.registrationForms.rfEmail;
//Username
const userName = testData.registrationForms.rfUsername;
//Password
const password = testData.registrationForms.rfPassword;



export class registrationFormsFrontEnd {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }




/************************************************* LITE *************************************************/
/******* @Create Registration Forms - Lite > FrontEnd **********/
/**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async completeUserRegistrationFormFrontEnd() {
        //Go to Registration page - FrontEnd
        const wpufRegistrationFormFage = testData.urls.baseUrl + '/registration-page/';
        try {
            await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
            // Add more promises here if needed
            ]);
        } catch (error) {
            // Handle the error here
            console.error('An error occurred during Promise.all():', error);
        }
        
        //Validate Registration page
        const validateRegistrationPage = await this.page.innerText('//h1[text()="Registration Page"]');
        await expect(validateRegistrationPage).toContain('Registration Page');

        //Enter First Name
        await this.page.fill('//input[@name="reg_fname"]', firstName);

        //Enter Last Name
        await this.page.fill('//input[@name="reg_lname"]', lastName);

        //Enter Email
        await this.page.fill('//input[@name="reg_email"]', email);

        //Enter Username
        await this.page.fill('//input[@id="wpuf-user_login"]', userName);

        //Enter Password
        await this.page.fill('//input[@id="wpuf-user_pass1"]', password);

        //Confirm Password
        await this.page.fill('//input[@id="wpuf-user_pass2"]', password);

        //Click Register
        await this.page.click('//input[@id="wp-submit"]');

        //Validate User logged in
        await expect(await this.page.locator('//a[contains(text(),"Log out")]')).toBeTruthy();

    };





/********************************************/
/******* @Validate Admin End - Users **********/
/******************************************/

    //Validate in Admin - Registered Form Submitted
    async validateUserRegisteredAdminEnd() {
        //Go to Admin End/Back End
        const wpufRegistrationFormFage = testData.urls.baseUrl + '/wp-admin/';
        try {
            await Promise.all([
            this.page.goto(wpufRegistrationFormFage, { waitUntil: 'networkidle' }),
            // Add more promises here if needed
            ]);
        } catch (error) {
            // Handle the error here
            console.error('An error occurred during Promise.all():', error);
        }
        
        //Validate Registered User
        //Go to Users List
        await this.page.click('//div[text()="Users"]');

        //Search Username
        await this.page.fill('//input[@type="search"]', email);

        //Click Search
        await this.page.click('//input[@id="search-submit"]');

        //Validate Email present
        const validateUserCreated = await this.page.innerText('//td[@class="email column-email"]');
        
        if (validateUserCreated == email){
            console.log("User is registered and present");
        }
        else {
            console.log("User could not be found");
        }
    };







}