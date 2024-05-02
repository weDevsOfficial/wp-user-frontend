require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
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



export class registrationFormsFrontend {
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
        const validateRegistrationPage = await this.page.innerText(selectors.registrationForms.completeUserRegistrationFormFrontend.validateRegistrationPage);
        await expect(validateRegistrationPage).toContain('Registration Page');

        //Enter First Name
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfFirstName, firstName);
        //Enter Last Name
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfLastName, lastName);
        //Enter Email
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfEmail, email);
        //Enter Username
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfUserName, userName);
        //Enter Password
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfPassword, password);
        //Confirm Password
        await this.page.fill(selectors.registrationForms.completeUserRegistrationFormFrontend.rfConfirmPassword, password);
        //Click Register
        await this.page.click(selectors.registrationForms.completeUserRegistrationFormFrontend.rfRegisterButton);
        //Validate User logged in
        await expect(await this.page.locator(selectors.registrationForms.completeUserRegistrationFormFrontend.validateRegisteredLogoutButton)).toBeTruthy();

    };





    /***********************************************/
    /******* @Validate Admin End - Users **********/
    /*********************************************/

    //Validate in Admin - Registered Form Submitted
    async validateUserRegisteredAdminEnd() {
        //Go to Admin End/Back End
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/';
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

        if (validateUserCreated == email) {
            console.log("User is registered and present");
        }
        else {
            console.log("User could not be found");
        }
    };







}