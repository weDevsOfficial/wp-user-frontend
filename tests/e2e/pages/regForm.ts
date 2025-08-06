import * as dotenv from 'dotenv';
dotenv.config();
import { expect, request, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { RegistrationForm, Urls } from '../utils/testData';

//Store data
//First Name
const firstName = RegistrationForm.rfFirstName;
//Last Name
const lastName = RegistrationForm.rfLastName;
//Email
const email = RegistrationForm.rfEmail;
//Username
const userName = RegistrationForm.rfUsername;
//Display Name
const displayName = RegistrationForm.rfDisplayName;
//Nickname
const nickname = RegistrationForm.rfNickname;
//Website
const website = RegistrationForm.rfWebsite;
//Biographical Info
const biographicalInfo = RegistrationForm.rfBiographicalInfo;
//Avatar
const avatar = RegistrationForm.rfAvatar;
//Profile Photo
const profilePhoto = RegistrationForm.rfProfilePhoto;
//X (Twitter)
const xTwitter = RegistrationForm.rfXtwitter;
//Facebook
const facebook = RegistrationForm.rfFacebook;
//LinkedIn
const linkedIn = RegistrationForm.rfLinkedIn;
//Instagram
const instagram = RegistrationForm.rfInstagram;
// new email
let newEmail = '';

export class RegFormPage extends Base {

    constructor(page: Page) {
        super(page);
    }

    /************************************************* LITE *************************************************/
    /******* @Registration Forms - Lite *******/
    /************************************************/

    //Registration forms page - only WPUF-Lite activated
    async validateRegistrationFormsProFeature() {
        // Visit Registration forms page
        await this.navigateToURL(this.wpufRegistrationFormPage);

        const validateWPUFProActivate = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log('WPUF Pro is Activated');
        }
        else {
            //Check Pro Features Header
            await this.checkElementText(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText, 'Unlock PRO Features');

            //Check Setup
            const checkUpgradeToProOption = this.page.locator(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkUpgradeToProOption);
            expect(checkUpgradeToProOption).toBeTruthy();
        }
    }


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(regFormName: string, registrationFormPageTitle: string) {
        // Visit Registration forms page
        await this.navigateToURL(this.wpufRegistrationFormPage);

        let storeShortcode: string = '';

        //Copy Shortcode
        storeShortcode = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode(regFormName));
        console.log(storeShortcode);


        // Get nonce for REST API authentication
        let nonce = await this.page.evaluate(() => {
            return (window as any).wpApiSettings?.nonce || '';
        });

        // If nonce not found, try to get it from the admin area
        if (!nonce) {
            // Navigate to admin dashboard to get nonce
            await this.navigateToURL(this.wpAdminPage);
            nonce = await this.page.evaluate(() => {
                return (window as any).wpApiSettings?.nonce || '';
            });
        }

        //console.log('REST API Nonce:', nonce);

        const storageState = await this.page.context().storageState();
        // Create a new request context with auth cookies and nonce
        const apiContext = await request.newContext({
            baseURL: Urls.baseUrl,
            storageState: storageState,
            extraHTTPHeaders: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
            ignoreHTTPSErrors: true,
        });

        // Create page using REST API with auth session cookie and nonce
        const res = await apiContext.post('/wp-json/wp/v2/pages', {
            data: {
                title: registrationFormPageTitle,
                content: storeShortcode,
                status: 'publish',
            },
        });

        // Debug: Log response details
        console.log('API Response Status:', res.status());
        //console.log('API Response Headers:', await res.headersArray());

        if (!res.ok()) {
            const errorBody = await res.text();
            console.log('API Error Response Body:', errorBody);
            throw new Error(`API request failed with status ${res.status()}: ${errorBody}`);
        }

        const pageData = await res.json();
        console.log('Page created:', pageData.link);

    }

    /************************************************* PRO *************************************************/
    /******* @Create Registration Forms - Pro *******/
    /***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegFormName: string, newRegFormPage: string) {

        let flag = true;

        while (flag == true) {

            //Visit Post Form Page
            await this.navigateToURL(this.wpufRegistrationFormPage);
            //CreateNewRegistrationForm

            try {
                await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
            } catch (error) {
                await this.navigateToURL(this.wpufRegistrationFormPage);
                await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
            }

            //ClickBlankForm
            //Templates 
            //await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.hoverBlankForm);
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickBlankForm);

            //EnterName
            await this.page.waitForLoadState('domcontentloaded');
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.editNewFormName);
            await this.validateAndFillStrings(Selectors.registrationForms.createBlankForm_RF.enterNewFormName, newRegFormName);
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.confirmNewNameTickButton);

            await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldFirstName);
            await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldDisplayName);
            await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldEmail);
            await this.validateAndClick(Selectors.registrationForms.addProfileFields_RF.profileFieldPassword);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await this.createRegistrationPageUsingShortcodeLite(newRegFormName, newRegFormPage);
        }



    }

    async addFieldsToRegistrationForm(newRegFormName: string) {

        let flag = true;

        while (flag == true) {

            //Visit Post Form Page
            await this.navigateToURL(this.wpufRegistrationFormPage);
            //CreateNewRegistrationForm
            //EnterName
            await this.validateAndClick(Selectors.registrationForms.addFields.clickForm(newRegFormName));
            await this.validateAndClick(Selectors.registrationForms.addFields.clickFormEditor);

            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Username'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('First Name'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Last Name'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Display Name'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Nickname Name'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Website'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Biographical Info'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Avatar'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Profile Photo'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('X (Twitter)'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Facebook'));
            //await this.validateAndClick(Selectors.registrationForms.addFields.useField('LinkedIn'));
            //await this.validateAndClick(Selectors.registrationForms.addFields.useField('instagram'));
            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

    }

    async validateFieldsToRegistrationForm(newRegFormName: string) {
        await this.navigateToURL(this.wpufRegistrationFormPage);
        try {
            await this.validateAndClick(Selectors.registrationForms.addFields.clickForm(newRegFormName));
        } catch (error) {
            await this.navigateToURL(this.wpufRegistrationFormPage);
            await this.validateAndClick(Selectors.registrationForms.addFields.clickForm(newRegFormName));
        }
        await this.validateAndClick(Selectors.registrationForms.addFields.clickFormEditor);
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('user_email'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('user_login'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('first_name'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('last_name'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('display_name'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('nickname'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('user_url'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('description'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('avatar'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_profile_photo'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_twitter'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_facebook'));
        //await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_linkedin'));
        //await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_instagram'));

    }

    /************************************************* LITE *************************************************/
    /******* @Create Registration Forms - Lite > FrontEnd **********/
    /**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async completeUserRegistrationFormFrontend() {
        newEmail = email;
        //Go to Registration page - FrontEnd
        await this.navigateToURL(this.wpufRegistrationPage);

        //Validate Registration page
        await this.checkElementText(Selectors.registrationForms.completeUserRegistrationFormFrontend.validateRegistrationPage, 'Registration Page');
        try {
            //Enter Email
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfEmail, newEmail);
        } catch (error) {
            console.log('Email field is not present');
        }
        try {
            //Enter Password
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfPassword, newEmail);
        } catch (error) {
            console.log('Password field is not present');
        }
        try {
            //Confirm Password
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfConfirmPassword, newEmail);
        } catch (error) {
            console.log('Confirm Password field is not present');
        }
        try {
            //Enter Username
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfUserName, userName);
        } catch (error) {
            console.log("Username field is not present");
        }
        try {
            // Enter First Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFirstName, firstName);
        } catch (error) {
            console.log("First Name field is not present");
        }

        try {
            // Enter Last Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLastName, lastName);
        } catch (error) {
            console.log("Last Name field is not present");
        }

        try {
            // Enter Display Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfDisplayName, displayName);
        } catch (error) {
            console.log("Display Name field is not present");
        }

        try {
            // Enter Nickname
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfNickname, nickname);
        } catch (error) {
            console.log("Nickname field is not present");
        }

        try {
            // Enter Website
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfWebsite, website);
        } catch (error) {
            console.log("Website field is not present");
        }

        try {
            // Enter Biographical Info
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfBiographicalInfo, biographicalInfo);
        } catch (error) {
            console.log("Biographical Info field is not present");
        }


        try {
            // Enter Avatar
            await this.page.setInputFiles(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfAvatar, avatar);
        } catch (error) {
            console.log("Avatar field is not present");
        }

        try {
            // Enter Profile Photo
            await this.page.setInputFiles(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfProfilePhoto, profilePhoto);
        } catch (error) {
            console.log("Profile Photo field is not present");
        }

        try {
            // Enter X (Twitter)
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfXtwitter, xTwitter);
        } catch (error) {
            console.log("X (Twitter) field is not present");
        }

        try {
            // Enter Facebook
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFacebook, facebook);
        } catch (error) {
            console.log("Facebook field is not present");
        }


        // try {
        //     // Enter LinkedIn
        //     await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLinkedIn, linkedIn);
        // } catch (error) {
        //     console.log("LinkedIn field is not present");
        // }

        // try {
        //     // Enter Instagram
        //     await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfInstagram, instagram);
        // } catch (error) {
        //     console.log("Instagram field is not present");
        // }

        //Click Register
        await this.validateAndClick(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfRegisterButton);
        await this.page.waitForTimeout(2000);
        await this.assertionValidate(Selectors.regFormSettings.checkSuccessMessage);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }





    /***********************************************/
    /******* @Validate Admin End - Users **********/
    /*********************************************/

    //Validate in Admin - Registered Form Submitted
    async validateUserRegisteredAdminEnd() {
        await this.navigateToURL(this.wpufRegistrationFormPage);

        //Validate Registered User
        //Go to Users List
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserEmail(newEmail));

    }


}