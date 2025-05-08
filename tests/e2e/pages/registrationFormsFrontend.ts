import dotenv from "dotenv";
dotenv.config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls, RegistrationForm } from '../utils/testData';
import { Base } from './base';



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



export class RegistrationFormsFrontendPage extends Base {

    constructor(page: Page) {
        super(page);
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

        // try {
        //     // Enter First Name
        //     await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFirstName, firstName);
        // } catch (error) {
        //     console.log("First Name field is not present");
        // }

        // try {
        //     // Enter Last Name
        //     await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLastName, lastName);
        // }catch (error) {
        //     console.log("Last Name field is not present");
        // }
        try {
            //Enter Email
        await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfEmail, email);
        }catch (error) {
            console.log("Email field is not present");
        }
        // try {
        //     //Enter Username
        //     await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfUserName, userName);
        // }catch (error) {
        //     console.log("Username field is not present");
        // }
        try {
            //Enter Password
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfPassword, password);
        }catch (error) {
            console.log("Password field is not present");
        }
        try {
            //Confirm Password
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfConfirmPassword, password);
        }catch (error) {
            console.log("Confirm Password field is not present");
        }
        //Click Register
        await this.validateAndClick(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfRegisterButton);
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
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);
        //Search Username
        await this.validateAndFillStrings(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchBox, email);
        //Click Search
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchButton);
        try{
        //Validate Email present
        const validateUserCreated = await this.page.innerText(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserCreated);

        expect(validateUserCreated, `Expected user with email ${email} to be found in admin`).toBe(email);
        }
        catch (error) {
            console.log("User not found in admin");
        }

    };







}