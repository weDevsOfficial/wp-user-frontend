import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { RegFormPage } from '../pages/regForm';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Urls, Users, VendorRegistrationForm } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll(async () => {
    // Launch browser
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();
});


test.describe('Registration-Forms', () => {
    // Configure fail-fast behavior for this spec file
    configureSpecFailFast();
    
    /**----------------------------------REGISTRATIONFORMS----------------------------------**
     * 
     * @TestScenario : [Reg-Forms]
     * @Test_RF0001 : Admin is checking Registration Forms - Pro Feature Page
     * @Test_RF0002 : Admin is adding fields to Registration Forms
     * @Test_RF0003 : Admin is validating fields to Registration Forms
     * @Test_RF0004 : Admin is creating Registration Forms - using shortcode
     * @Test_RF0005 : User is registering using - Registration Form
     * @Test_RF0006 : Admin is validating - Registered user
     * @Test_RF0007 : Admin is creating Dokan Vendor Registration Form
     * @Test_RF0008 : Admin is creating dokan vendor registration page using shortcode
     * @Test_RF0009 : User registering as Dokan Vendor FE
     * @Test_RF0010 : Admin validating Dokan Vendor registration as default
     * @Test_RF0011 : Admin validating Dokan Vendor registration in dokan
     * @Test_RF0012 : Admin is creating WC Vendors Registration Form
     * @Test_RF0013 : Admin is creating WC Vendors registration page using shortcode
     * @Test_RF0014 : User registering as WC Vendor and validates email verification
     * @Test_RF0015 : User clicks on activation link and logging in as WC Vendor
     * @Test_RF0016 : Admin validating WC Vendor registration as default
     * @Test_RF0017 : Admin validating WC Vendor registration in WC
     * @Test_RF0018 : Admin is creating WCFM Membership Registration Form
     * @Test_RF0019 : Admin is enabling multi-step for WCFM Membership Registration Form
     * @Test_RF0020 : Admin is creating WCFM member registration page using shortcode
     * @Test_RF0021 : User registering as WCFM Member and validates email verification
     * @Test_RF0022 : User clicks on activation link and logging in as WCFM member
     * @Test_RF0023 : Admin validating WCFM Membership registration as default
     */

    let activationLink: string = '';
    test('RF0001 : Admin is checking Registration Forms - Pro Feature Page', { tag: ['@Pro'] }, async () => {
        await page.waitForTimeout(15000);
        const BasicLogin = new BasicLoginPage(page);
        const RegForm = new RegFormPage(page);
        //Basic login
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        await RegForm.validateRegistrationFormsProFeature();

    });

    test('RF0002 : Admin is adding fields to Registration Forms ', { tag: ['@Pro'] }, async () => {
        const RegForm = new RegFormPage(page);
        const regFormName = 'Registration';
        //FrontEnd
        //Complete FrontEnd Registration
        await RegForm.addFieldsToRegistrationForm(regFormName);
    });

    test('RF0003 : Admin is validating fields to Registration Forms ', { tag: ['@Pro'] }, async () => {
        const RegForm = new RegFormPage(page);
        const regFormName = 'Registration';
        //FrontEnd
        //Complete FrontEnd Registration
        await RegForm.validateFieldsToRegistrationForm(regFormName);
    });

    test('RF0004 : Admin is creating Registration Forms Page - using shortcode', { tag: ['@Pro'] }, async () => {
        const RegForm = new RegFormPage(page);
        const SettingsSetup = new SettingsSetupPage(page);
        //Registration Forms page - Title
        const regFormPageTitle = 'Registration Page';
        const regFormName = 'Registration';
        //Create Registration Forms page
        await RegForm.createRegistrationPageUsingShortcodeLite(regFormName, regFormPageTitle);
        //Change Registration settings
        await SettingsSetup.changeSettingsSetRegistrationPage(regFormPageTitle);

        await new BasicLogoutPage(page).logOut();
    });


    test('RF0005 : User is registering using - Registration Form', { tag: ['@Pro'] }, async () => {
        const RegForm = new RegFormPage(page);
        //FrontEnd
        //Complete FrontEnd Registration
        await RegForm.completeUserRegistrationFormFrontend();
    });


    test('RF0006 : Admin is validating - Registered user', { tag: ['@Pro'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        const RegForm = new RegFormPage(page);
        //Basic Login
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        //Validate FrontEnd Registered
        await RegForm.validateUserRegisteredAdminEnd();
    });

    test('RF0007 : Admin is creating Dokan Vendor Registration Form', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Create Dokan Vendor Registration Form
        await RegForm.createDokanVendorRegistrationForm(VendorRegistrationForm.dokanVendorFormName);
    });

    test('RF0008 : Admin is creating dokan vendor registration page using shortcode', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Change Registration settings
        await RegForm.createRegistrationPageUsingShortcodeLite(VendorRegistrationForm.dokanVendorFormName, VendorRegistrationForm.dokanVendorPageTitle);
        
        await new BasicLogoutPage(page).logOut();
    });

    test('RF0009 : User registering as Dokan Vendor FE', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Complete Dokan Vendor Registration Frontend
        await RegForm.completeDokanVendorRegistrationFrontend();
    });

    test('RF0010 : Admin validating Dokan Vendor registration as default', { tag: ['@Pro', '@Vendor'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        const RegForm = new RegFormPage(page);
        
        // Basic Login
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        
        // Validate Dokan Vendor Registration Admin
        await RegForm.validateDokanVendorRegistrationAdmin();
    });

    test('RF0011 : Admin validating Dokan Vendor registration in dokan', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Validate Dokan Vendor Registration Admin
        await RegForm.validateDokanVendorRegistrationDokan();
    });

    test('RF0012 : Admin is creating WC Vendors Registration Form', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Create WC Vendors Registration Form
        await RegForm.createWcVendorRegistrationForm(VendorRegistrationForm.wcVendorFormName);
    });

    test('RF0013 : Admin is creating WC Vendors registration page using shortcode', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Change Registration settings
        await RegForm.createRegistrationPageUsingShortcodeLite(VendorRegistrationForm.wcVendorFormName, VendorRegistrationForm.wcVendorPageTitle);
        
        await new BasicLogoutPage(page).logOut();
    });

    test('RF0014 : User registering as WC Vendor and validates email verification', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Complete WC Vendor Registration Frontend
        activationLink = await RegForm.completeWcVendorRegistrationFrontend();
    });

    test('RF0015 : User clicks on activation link and logging in as WC Vendor', { tag: ['@Pro'] }, async () => {
        const regForm = new RegFormPage(page);
        await regForm.validateEmailVerification(activationLink, VendorRegistrationForm.wcVendorEmail, VendorRegistrationForm.wcVendorPassword);
    });

    test('RF0016 : Admin validating WC Vendor registration as default', { tag: ['@Pro', '@Vendor'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        const RegForm = new RegFormPage(page);
        
        // Basic Login
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        
        // Validate WC Vendor Registration Admin
        await RegForm.validateWcVendorRegistrationAdmin();
    });

    test('RF0017 : Admin validating WC Vendor registration in WC', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Validate WC Vendor Registration Admin
        await RegForm.validateWcVendorRegistrationWC();
    });

    test('RF0018 : Admin is creating WCFM Membership Registration Form', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Create WCFM Membership Registration Form
        await RegForm.createWcfmMemberRegistrationForm();
    });

    test('RF0019 : Admin is enabling multi-step for WCFM Membership Registration Form', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Enable multi-step for WCFM Membership Registration Form
        await RegForm.enableMultiStepForWcfmMemberRegistrationForm(VendorRegistrationForm.wcfmMemberFormName);
    });

    test('RF0020 : Admin is creating WCFM member registration page using shortcode', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Create WCFM Membership Registration Page
        await RegForm.createWcfmMemberRegistrationPage(VendorRegistrationForm.wcfmMemberFormName, VendorRegistrationForm.wcfmMemberPageTitle);

        await new BasicLogoutPage(page).logOut();
    });

    test.skip('RF0021 : User registering as WCFM Member and validates email verification', { tag: ['@Pro', '@Vendor'] }, async () => {
        const RegForm = new RegFormPage(page);
        
        // Complete WCFM Membership Registration Frontend
        activationLink = await RegForm.completeWcfmMemberRegistrationFrontend();
    });

    test.skip('RF0022 : User clicks on activation link and logging in as WCFM member', { tag: ['@Pro'] }, async () => {
        const regForm = new RegFormPage(page);
        await regForm.validateEmailVerification(activationLink, VendorRegistrationForm.wcfmMemberEmail, VendorRegistrationForm.wcfmMemberPassword);
    });

    test.skip('RF0023 : Admin validating WCFM Membership registration as default', { tag: ['@Pro', '@Vendor'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        const RegForm = new RegFormPage(page);
        
        // Basic Login
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        
        // Validate WCFM Membership Registration Admin
        await RegForm.validateWcfmMemberRegistrationAdmin();
    });



});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});