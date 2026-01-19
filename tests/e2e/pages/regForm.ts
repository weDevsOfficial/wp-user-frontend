import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, request, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { RegistrationForm, VendorRegistrationForm, Urls, PostForm, Users } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { BasicLoginPage } from './basicLogin';
import { BasicLogoutPage } from './basicLogout';

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
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('LinkedIn'));
            await this.validateAndClick(Selectors.registrationForms.addFields.useField('Instagram'));
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
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_linkedin'));
        await this.assertionValidate(Selectors.registrationForms.addFields.validateField('wpuf_social_instagram'));

    }

    /************************************************* LITE *************************************************/
    /******* @Create Registration Forms - Lite > FrontEnd **********/
    /**************************************************************/


    //Registration forms page - only WPUF-Lite activated
    async completeUserRegistrationFormFrontend() {
        newEmail = RegistrationForm.rfEmail;
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
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfUserName, RegistrationForm.rfUsername);
        } catch (error) {
            console.log("Username field is not present");
        }
        try {
            // Enter First Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFirstName, RegistrationForm.rfFirstName);
        } catch (error) {
            console.log("First Name field is not present");
        }

        try {
            // Enter Last Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLastName, RegistrationForm.rfLastName);
        } catch (error) {
            console.log("Last Name field is not present");
        }

        try {
            // Enter Display Name
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfDisplayName, RegistrationForm.rfDisplayName);
        } catch (error) {
            console.log("Display Name field is not present");
        }

        try {
            // Enter Nickname
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfNickname, RegistrationForm.rfNickname);
        } catch (error) {
            console.log("Nickname field is not present");
        }

        try {
            // Enter Website
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfWebsite, RegistrationForm.rfWebsite);
        } catch (error) {
            console.log("Website field is not present");
        }

        try {
            // Enter Biographical Info
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfBiographicalInfo, RegistrationForm.rfBiographicalInfo);
        } catch (error) {
            console.log("Biographical Info field is not present");
        }


        try {
            // Enter Avatar
            await this.page.setInputFiles(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfAvatar, RegistrationForm.rfAvatar);
            await this.page.waitForTimeout(2000);
        } catch (error) {
            console.log("Avatar field is not present");
        }

        try {
            // Enter Profile Photo
            await this.page.setInputFiles(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfProfilePhoto, RegistrationForm.rfProfilePhoto);
            await this.page.waitForTimeout(2000)
        } catch (error) {
            console.log("Profile Photo field is not present");
        }

        try {
            // Enter X (Twitter)
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfXtwitter, RegistrationForm.rfXtwitter);
        } catch (error) {
            console.log("X (Twitter) field is not present");
        }

        try {
            // Enter Facebook
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfFacebook, RegistrationForm.rfFacebook);
        } catch (error) {
            console.log("Facebook field is not present");
        }

        try {
            // Enter X (Twitter)
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLinkedIn, RegistrationForm.rfLinkedIn = this.generateWordWithMinLength(5));
        } catch (error) {
            console.log("Linkedin field is not present");
        }

        try {
            // Enter Facebook
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfInstagram, RegistrationForm.rfInstagram = this.generateWordWithMinLength(5));
        } catch (error) {
            console.log("Instagram field is not present");
        }

        try {
            // Enter LinkedIn
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfLinkedIn, RegistrationForm.rfLinkedIn = this.generateWordWithMinLength(5));
        } catch (error) {
            console.log("LinkedIn field is not present");
        }

        try {
            // Enter Instagram
            await this.validateAndFillStrings(Selectors.registrationForms.completeUserRegistrationFormFrontend.rfInstagram, RegistrationForm.rfInstagram = this.generateWordWithMinLength(5));
        } catch (error) {
            console.log("Instagram field is not present");
        }

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

    /****************************************************/
    /********** @Vendor Registration Forms - Pro ********/
    /***************************************************/

    // Dokan Vendor Registration Form Methods
    async createDokanVendorRegistrationForm(formName: string) {

        await this.navigateToURL(this.wpufRegistrationFormPage);

        try {
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        } catch (error) {
            await this.navigateToURL(this.wpufRegistrationFormPage);
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        }

        // Click Blank Form (since vendor templates don't exist)
        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.createDokanVendorForm);

        // Add Profile Fields
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('first_name'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('last_name'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('user_email'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('dokan_store_name'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('shopurl'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('dokan_profile_picture'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('dokan_banner'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('dokan_store_phone'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateAddressField);
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateField('location'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validatePasswordField);
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.validateConfirmPasswordField);
    }



    async completeDokanVendorRegistrationFrontend() {
        // Navigate to Dokan Vendor Registration Page
        await this.navigateToURL(this.dokanVendorRegistrationPage);

        // Fill Profile Information (only the fields that exist in the basic form)
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.firstNameField, VendorRegistrationForm.dokanVendorFirstName = faker.person.firstName());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.lastNameField, VendorRegistrationForm.dokanVendorLastName = faker.person.lastName());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.emailField, VendorRegistrationForm.dokanVendorEmail = faker.internet.email());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.shopNameField, VendorRegistrationForm.dokanShopName = faker.lorem.words(2));
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.shopUrlField, VendorRegistrationForm.dokanShopUrl = faker.internet.url());
        await this.page.setInputFiles(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.storeLogoField, VendorRegistrationForm.dokanVendorStoreLogo);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.page.setInputFiles(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.storeBannerField, VendorRegistrationForm.dokanVendorStoreBanner);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.phoneField, VendorRegistrationForm.dokanVendorPhone = `016${faker.string.numeric(8)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.addressLine1Field, VendorRegistrationForm.dokanVendorStreet1Address);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.addressLine2Field, VendorRegistrationForm.dokanVendorStreet2Address);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.cityField, VendorRegistrationForm.dokanVendorCity);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.zipField, VendorRegistrationForm.dokanVendorZip);
        await this.selectOptionWithLabel(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.countryField, VendorRegistrationForm.dokanVendorCountry);
        await this.selectOptionWithLabel(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.stateField, VendorRegistrationForm.dokanVendorState);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postGoogleMapsFormsFE, VendorRegistrationForm.dokanVendorGoogleMaps = 'Dhaka, Bangladesh');
        await this.page.keyboard.press('Enter');
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.passwordField, VendorRegistrationForm.dokanVendorPassword = VendorRegistrationForm.dokanVendorEmail);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.confirmPasswordField, VendorRegistrationForm.dokanVendorPassword = VendorRegistrationForm.dokanVendorEmail);

        // Submit Registration
        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.registerButton);
        await this.page.waitForTimeout(2000);
        // await this.page.pause();
        // await this.navigateToURL(this.accountPage);
        // await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateDokanVendorRegistrationAdmin() {
        await this.navigateToURL(this.usersPage);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.adminValidation.searchUserField, VendorRegistrationForm.dokanVendorEmail);
        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.adminValidation.searchSubmitButton);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.adminValidation.userEmailValidation(VendorRegistrationForm.dokanVendorEmail));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.adminValidation.dokanVendorRole);
    }

    async validateDokanVendorRegistrationDokan() {
        await this.navigateToURL(this.dokanVendorStorePage);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.searchVendorField, VendorRegistrationForm.dokanShopName);
        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.vendorName(VendorRegistrationForm.dokanShopName));
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.vendorValidation(VendorRegistrationForm.dokanShopName));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateVendorPhone(VendorRegistrationForm.dokanVendorPhone));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateAddress(VendorRegistrationForm.dokanVendorStreet1Address));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateAddress(VendorRegistrationForm.dokanVendorStreet2Address));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateAddress(VendorRegistrationForm.dokanVendorCity));
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateStateZip);
        await this.assertionValidate(Selectors.vendorRegistrationForms.dokanVendor.dokanValidation.validateVendorEnabled);

    }

    // WC Vendors Registration Form Methods
    async createWcVendorRegistrationForm(formName: string) {

        // Visit Registration Forms Page
        await this.navigateToURL(this.wpufRegistrationFormPage);

        try {
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        } catch (error) {
            await this.navigateToURL(this.wpufRegistrationFormPage);
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        }

        // Click Blank Form (since vendor templates don't exist)
        await this.validateAndClick(Selectors.vendorRegistrationForms.wcVendor.createWcVendorForm);

        // Add Profile Fields
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateField('user_email'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateField('pv_paypal'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateField('pv_shop_name'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateField('pv_seller_info'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateField('pv_shop_description'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validatePasswordField);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.validateConfirmPasswordField);
    }

    async completeWcVendorRegistrationFrontend() {
        // Navigate to WC Vendors Registration Page
        await this.navigateToURL(this.wcVendorRegistrationPage);

        // Fill Profile Information (only the fields that exist in the basic form)
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.emailField, VendorRegistrationForm.wcVendorEmail = faker.internet.email());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.paypalField, VendorRegistrationForm.wcVendorpaypalName = faker.internet.email());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.shopNameField, VendorRegistrationForm.wcVendorShopName = faker.lorem.words(2));
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.sellerInfo, VendorRegistrationForm.wcVendorSellerInfo = faker.lorem.paragraph());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.shortDescription, VendorRegistrationForm.wcVendorShortDescription = faker.lorem.paragraph());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.passwordField, VendorRegistrationForm.wcVendorPassword = VendorRegistrationForm.wcVendorEmail);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.frontendForm.confirmPasswordField, VendorRegistrationForm.wcVendorConfirmPassword = VendorRegistrationForm.wcVendorEmail);

        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.registerButton);
        await this.page.waitForTimeout(2000);
        const successMessage = await this.page.innerText(Selectors.regFormSettings.successMessage);
        expect(successMessage).toContain('Please check your email for activation link');

        // Login as admin to check WP Mail Log
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        // Navigate to WP Mail Log
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wpMailLogValidation.wpMailLogPage);

        // Check the latest email

        // Validate email recipient
        const emailTo = await this.page.innerText(Selectors.vendorRegistrationForms.wpMailLogValidation.sentEmailAddress(VendorRegistrationForm.wcVendorEmail));
        expect(emailTo).toContain(VendorRegistrationForm.wcVendorEmail);

        // View email content to validate body
        await this.validateAndClick(Selectors.vendorRegistrationForms.wpMailLogValidation.viewEmailContent(VendorRegistrationForm.wcVendorEmail));

        const activationLink = await this.page.locator(Selectors.vendorRegistrationForms.wpMailLogValidation.grabActivationLink).getAttribute('href');
        //expect(emailBody).toContain(expectedBodyContent);

        await this.validateAndClick(Selectors.vendorRegistrationForms.wpMailLogValidation.modalCloseButton);



        await new BasicLogoutPage(this.page).logOut();

        return activationLink;
    }

    async validateEmailVerification(activationLink: string, email: string, password: string) {
        await this.navigateToURL(activationLink);
        await new BasicLoginPage(this.page).backendLogin(email, password);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateWcVendorRegistrationAdmin() {
        await this.navigateToURL(this.usersPage);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcVendor.adminValidation.searchUserField, VendorRegistrationForm.wcVendorEmail);
        await this.validateAndClick(Selectors.vendorRegistrationForms.wcVendor.adminValidation.searchSubmitButton);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.adminValidation.userEmailValidation(VendorRegistrationForm.wcVendorEmail));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.adminValidation.wcVendorRole);
        await this.page.hover(Selectors.vendorRegistrationForms.wcVendor.adminValidation.wcVendorRole);
        await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.approveUser);
        await this.page.waitForTimeout(500);
    }

    async validateWcVendorRegistrationWC() {
        await this.page.waitForTimeout(5000)
        await this.navigateToURL(this.wcVendorsPage);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.wcValidation.vendorValidation(VendorRegistrationForm.wcVendorShopName));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcVendor.wcValidation.vendorStatusValidation);

    }

    // WCFM Membership Registration Form Methods
    async createWcfmMemberRegistrationForm() {

        // Visit Registration Forms Page
        await this.navigateToURL(this.wpufRegistrationFormPage);

        try {
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        } catch (error) {
            await this.navigateToURL(this.wpufRegistrationFormPage);
            await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        }

        // Click Blank Form (since vendor templates don't exist)
        await this.validateAndClick(Selectors.vendorRegistrationForms.wcfmMember.createWcfmMemberForm);

        // Add Profile Fields
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('user_email'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('user_login'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_phone'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_image'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_banner'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_description'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateAddressField);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_fb_profile'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_twitter_profile'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_google_plus_profile'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_linkdin_profile'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_youtube'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validateField('_vendor_instagram'));
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.validatePasswordField);
    }

    async enableMultiStepForWcfmMemberRegistrationForm(formName: string) {
        let flag = true;

        if (flag == true) {
            while (flag == true) {

                await this.navigateToURL(this.wpufRegistrationFormPage);

                // Click Blank Form (since vendor templates don't exist)
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));

                await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
                await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

                // Navigate to Multi-Step Settings tab
                await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsTab);
                await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsHeader);

                await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepSettingsHeader);

                // Check if multi-step is already enabled
                const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle).isChecked();
                if (!isChecked) {
                    await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
                }

                await this.validateAndClick(Selectors.regFormSettings.saveButton);
                flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
            }
        }

    }

    async createWcfmMemberRegistrationPage(formName: string, pageTitle: string) {
        // Visit Registration Forms Page
        await this.navigateToURL(this.wpufRegistrationFormPage);

        let storeShortcode: string = '';

        // Copy Shortcode
        storeShortcode = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode(formName));
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
                title: pageTitle,
                content: storeShortcode,
                status: 'publish',
            },
        });

        if (!res.ok()) {
            const errorBody = await res.text();
            console.log('API Error Response Body:', errorBody);
            throw new Error(`API request failed with status ${res.status()}: ${errorBody}`);
        }

        const pageData = await res.json();
        console.log('Page created:', pageData.link);
    }

    async completeWcfmMemberRegistrationFrontend() {
        // Navigate to WCFM Membership Registration Page
        await this.navigateToURL(this.wcfmMemberRegistrationPage);

        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.emailField, VendorRegistrationForm.wcfmMemberEmail = faker.internet.email());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.storeNameField, VendorRegistrationForm.wcfmMemberStoreName = faker.word.words(2));
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.phoneField, VendorRegistrationForm.wcfmMemberPhone = `+88016${faker.string.numeric(8)}`);
        await this.page.setInputFiles(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.storeLogoField, VendorRegistrationForm.wcfmMemberStoreLogo);
        await this.page.waitForTimeout(2000);
        await this.page.setInputFiles(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.storeBannerField, VendorRegistrationForm.wcfmMemberStoreBanner);
        await this.page.waitForTimeout(2000);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.descriptionField, VendorRegistrationForm.description = faker.lorem.paragraph());
        await this.validateAndClick(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.nextButton);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.addressLine1Field, VendorRegistrationForm.wcfmMemberAddress = faker.location.streetAddress());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.addressLine2Field, VendorRegistrationForm.wcfmMemberAddress2 = faker.location.streetAddress());
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.cityField, VendorRegistrationForm.wcfmMemberCity);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.zipField, VendorRegistrationForm.wcfmMemberZip);
        await this.selectOptionWithLabel(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.countryField, VendorRegistrationForm.wcfmMemberCountry);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.facebookField, VendorRegistrationForm.wcfmMemberFacebook = `https://www.facebook.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.twitterField, VendorRegistrationForm.wcfmMemberTwitter = `https://www.x.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.googleField, VendorRegistrationForm.wcfmMemberGoogle = `https://www.google.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.linkedinField, VendorRegistrationForm.wcfmMemberLiknkedin = `https://www.linkedin.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.youtubeField, VendorRegistrationForm.wcfmMemberYoutube = `https://www.youtube.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.instagramField, VendorRegistrationForm.wcfmMemberInstagram = `https://www.instagram.com/${this.generateWordWithMinLength(5)}`);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.frontendForm.passwordField, VendorRegistrationForm.wcfmMemberPassword = VendorRegistrationForm.wcfmMemberEmail);

        await this.validateAndClick(Selectors.vendorRegistrationForms.dokanVendor.frontendForm.registerButton);
        await this.page.waitForTimeout(2000);
        const successMessage = await this.page.innerText(Selectors.regFormSettings.successMessage);
        expect(successMessage).toContain('Please check your email for activation link');

        // Login as admin to check WP Mail Log
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        // Navigate to WP Mail Log
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.regFormSettings.wpMailLogValidation.wpMailLogPage);

        // Check the latest email

        // Validate email recipient
        const emailTo = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentEmailAddress(VendorRegistrationForm.wcfmMemberEmail));
        expect(emailTo).toContain(VendorRegistrationForm.wcfmMemberEmail);

        // View email content to validate body
        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.viewEmailContent(VendorRegistrationForm.wcfmMemberEmail));

        const activationLink = await this.page.locator(Selectors.regFormSettings.wpMailLogValidation.grabActivationLink).getAttribute('href');
        //expect(emailBody).toContain(expectedBodyContent);

        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.modalCloseButton);



        await new BasicLogoutPage(this.page).logOut();

        return activationLink;
    }

    async validateWcfmMemberRegistrationAdmin() {
        await this.navigateToURL(this.usersPage);
        await this.validateAndFillStrings(Selectors.vendorRegistrationForms.wcfmMember.adminValidation.searchUserField, VendorRegistrationForm.wcfmMemberEmail);
        await this.validateAndClick(Selectors.vendorRegistrationForms.wcfmMember.adminValidation.searchSubmitButton);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.adminValidation.userEmailValidation(VendorRegistrationForm.wcfmMemberEmail));
        //await this.assertionValidate(Selectors.vendorRegistrationForms.wcfmMember.adminValidation.wcfmMemberRole);
    }


}