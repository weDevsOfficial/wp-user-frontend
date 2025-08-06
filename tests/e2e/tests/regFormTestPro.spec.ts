import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { RegFormPage } from '../pages/regForm';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Urls, Users } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';
import { configureSpecFailFast } from '../utils/specFailFast';
import * as fs from 'fs'; //Clear Cookie

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
     * 
     */

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



});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});