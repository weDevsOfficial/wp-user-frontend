require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


 
    //Store data
    //First Name
    const firstName = testData.registrationForms.rfFirstName;
    //Last Name
    const lastName = testData.registrationForms.rfLastName;
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
        const wpufRegistrationFormPage = testData.urls.baseUrl + '/registration-page/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);

        
        //Validate Registration page
        const validateRegistrationPage = await this.page.innerText(selectors.registrationForms.completeUserRegistrationFormFrontEnd.validateRegistrationPage);
        await expect(validateRegistrationPage).toContain('Registration Page');

        //Enter First Name
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfFirstName, firstName);

        //Enter Last Name
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfLastName, lastName);

        //Enter Email
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfEmail, email);

        //Enter Username
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfUserName, userName);

        //Enter Password
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfPassword, password);

        //Confirm Password
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfConfirmPassword, password);

        //Click Register
        await this.page.click(selectors.registrationForms.completeUserRegistrationFormFrontEnd.rfRegisterButton);

        //Validate User logged in
        await expect(await this.page.locator(selectors.registrationForms.completeUserRegistrationFormFrontEnd.validateRegisteredLogoutButton)).toBeTruthy();

    };





/***********************************************/
/******* @Validate Admin End - Users **********/
/*********************************************/

    //Validate in Admin - Registered Form Submitted
    async validateUserRegisteredAdminEnd() {
        //Go to Admin End/Back End
        const wpufRegistrationFormPage = testData.urls.baseUrl + '/wp-admin/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);
    
        
        //Validate Registered User
        //Go to Users List
        await this.page.click(selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);

        //Search Username
        await this.page.fill(selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchBox, email);

        //Click Search
        await this.page.click(selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchButton);

        //Validate Email present
        const validateUserCreated = await this.page.innerText(selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserCreated);
        
        if (validateUserCreated == email){
            console.log("User is registered and present");
        }
        else {
            console.log("User could not be found");
        }
    };







}