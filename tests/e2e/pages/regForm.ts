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
//Password
const password = RegistrationForm.rfPassword;
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


    // //Visit Pages
    // await this.navigateToURL(this.newPagePage);
    // await this.page.reload();
    // // // Check if the Welcome Modal is visible
    // // try {
    // //     await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closeWelcomeModal);
    // // } catch (error) {
    // //     console.log('Welcome Modal not visible!');
    // // }

    // // Check if the Choose Pattern Modal is visible
    // let patternModalVisible = false;
    // while (patternModalVisible == false) {
    //     try {
    //         await this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closePatternModal).click({ timeout: 10000 });
    //         patternModalVisible = true;
    //     } catch (error) {
    //         console.log('Pattern Modal not visible!, trying again');
    //         await this.page.reload();
    //     }
    // }

    // //Add Page Title
    // await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addPageTitle, registrationFormPageTitle);

    // //Click Add Block Button
    // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockAddButton);

    // //Search and Add Shortcode block
    // await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockSearchBox, 'Shortcode');
    // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addShortCodeBlock);

    // //Enter Registration Shortcode
    // await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.enterRegistrationShortcode, storeShortcode?.toString() ?? '');

    // //Click Publish Page
    // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.clickPublishPage);
    // // //Allow Permission
    // // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
    // //Confirm Publish
    // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.confirmPublish);

}

    /************************************************* PRO *************************************************/
    /******* @Create Registration Forms - Pro *******/
    /***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegFormName: string, newRegFormPage: string) {
    //Visit Post Form Page
    await this.navigateToURL(this.wpufRegistrationFormPage);
    //CreateNewRegistrationForm

    await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);

    //Start
    await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);

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
    await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

    await this.createRegistrationPageUsingShortcodeLite(newRegFormName, newRegFormPage);



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
        await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfEmail, newEmail);
    } catch (error) {
        console.log('Email field is not present');
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
    } catch (error) {
        console.log('Password field is not present');
    }
    try {
        //Confirm Password
        await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfConfirmPassword, password);
    } catch (error) {
        console.log('Confirm Password field is not present');
    }
    //Click Register
    await this.validateAndClick(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfRegisterButton);
    await this.page.waitForTimeout(2000);
    await this.assertionValidate(Selectors.regFormSettings.checkSuccessMessage);
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