import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { RegFormPage } from '../pages/regForm';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Urls, Users } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';
import * as fs from 'fs'; //Clear Cookie


export default function regFormTestPro() {

    let browser: Browser;
    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));

        // Launch browser
        browser = await chromium.launch();

        // Create a single context
        context = await browser.newContext();

        // Create a single page
        page = await context.newPage();
    });


    test.describe('Registration-Forms', () => {
        /**----------------------------------REGISTRATIONFORMS----------------------------------**
         * 
         * @TestScenario : [Reg-Forms]
         * @Test_RF0001 : Admin is checking Registration Forms - Pro Feature Page
         * @Test_RF0002 : Admin is creating Registration Forms - using shortcode
         * @Test_RF0003 : User is registering using - Registration Form
         * @Test_RF0004 : Admin is validating - Registered user
         * 
         */

        test('RF0001 : Here, Admin is checking Registration Forms - Pro Feature Page', { tag: ['@Pro'] }, async () => {
            const BasicLogin = new BasicLoginPage(page);
            const RegForm = new RegFormPage(page);
            //Basic login
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            await RegForm.validateRegistrationFormsProFeature();

        });


        test('RF0002 : Here, Admin is creating Registration Forms Page - using shortcode', { tag: ['@Pro'] }, async () => {
            const RegForm = new RegFormPage(page);
            const SettingsSetup = new SettingsSetupPage(page);
            //Registration Forms page - Title
            const regFormPageTitle = 'Registration Page';
            //Create Registration Forms page
            await RegForm.createRegistrationPageUsingShortcodeLite(regFormPageTitle);
            //Change Registration settings
            await SettingsSetup.changeSettingsSetRegistrationPage(regFormPageTitle);

            await new BasicLogoutPage(page).logOut();
        });


        test('RF0003 : Here, User is registering using - Registration Form', { tag: ['@Pro'] }, async () => {
            const RegForm = new RegFormPage(page);
            //FrontEnd
            //Complete FrontEnd Registration
            await RegForm.completeUserRegistrationFormFrontend();
        });


        test('RF0004 : Here, Admin is validating - Registered user', { tag: ['@Pro'] }, async () => {
            const BasicLogin = new BasicLoginPage(page);
            const RegForm = new RegFormPage(page);
            //Basic Login
            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            //Validate FrontEnd Registered
            await RegForm.validateUserRegisteredAdminEnd();
        });

    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });

}