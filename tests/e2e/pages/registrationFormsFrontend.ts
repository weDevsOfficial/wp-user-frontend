require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls, RegistrationForm } from '../utils/testData';



//Store data
//First Name
const firstName = RegistrationForm.rfFirstName;
//Last Name
const lastName = RegistrationForm.rfLastName;
//Email
const email = RegistrationForm.rfEmail;
//Username
const userName = RegistrationForm.rfUsername;
//Password
const password = RegistrationForm.rfPassword;



export class RegistrationFormsFrontendPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }




    /************************************************* LITE *************************************************/
    /******* @Create Registration Forms - Lite > FrontEnd **********/
    /**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async completeUserRegistrationFormFrontend() {
        //Go to Registration page - FrontEnd
        const wpufRegistrationFormPage = Urls.baseUrl + '/registration-page/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);


        //Validate Registration page
        const validateRegistrationPage = await this.page.innerText(Selectors.registrationForms.completeUserRegistrationFormFrontend.validateRegistrationPage);
        expect(validateRegistrationPage).toContain('Registration Page');

        //Enter First Name
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFirstName, firstName);
        //Enter Last Name
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLastName, lastName);
        //Enter Email
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfEmail, email);
        //Enter Username
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfUserName, userName);
        //Enter Password
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfPassword, password);
        //Confirm Password
        await this.page.fill(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfConfirmPassword, password);
        //Click Register
        await this.page.click(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfRegisterButton);
        //Validate User logged in
        await expect(await this.page.locator(Selectors.registrationForms.completeUserRegistrationFormFrontend.validateRegisteredLogoutButton)).toBeTruthy();

    };





    /***********************************************/
    /******* @Validate Admin End - Users **********/
    /*********************************************/

    //Validate in Admin - Registered Form Submitted
    async validateUserRegisteredAdminEnd() {
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);

        //Validate Registered User
        //Go to Users List
        await this.page.click(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);
        //Search Username
        await this.page.fill(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchBox, email);
        //Click Search
        await this.page.click(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchButton);
        //Validate Email present
        const validateUserCreated = await this.page.innerText(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserCreated);

        expect(validateUserCreated, `Expected user with email ${email} to be found in admin`).toBe(email);

    };







}