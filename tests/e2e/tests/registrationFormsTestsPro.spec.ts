import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { RegistrationFormsPage } from '../pages/registrationForms';
import { RegistrationFormsFrontendPage } from '../pages/registrationFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';
import * as fs from 'fs'; //Clear Cookie


export default function registrationFormsTestsPro() {

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


    test.describe('Registration-Forms @Pro :-->', () => {
        /**----------------------------------REGISTRATIONFORMS----------------------------------**
         * 
         * @TestScenario : [Reg-Forms]
         * @Test_RF0001_PRO : Admin is checking Registration Forms - Pro Feature Page
         * @Test_RF0002_PRO : Admin is creating Registration Forms - using shortcode
         * @Test_RF0003_PRO : User is registering using - Registration Form
         * @Test_RF0004_PRO : Admin is validating - Registered user
         * 
         */

        test('RF0001_PRO : Here, Admin is checking Registration Forms - Pro Feature Page', async () => {
            const BasicLogin = new BasicLoginPage(page);
            const RegistrationFormsLite = new RegistrationFormsPage(page);
            //Basic login
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            await RegistrationFormsLite.validateRegistrationFormsProFeature();

        });


        test('RF0002_PRO : Here, Admin is creating Registration Forms Page - using shortcode', async () => {
            const RegistrationFormsLite = new RegistrationFormsPage(page);
            const SettingsSetup = new SettingsSetupPage(page);
            //Registration Forms page - Title
            const registrationFormPageTitle = 'Registration Page';
            //Create Registration Forms page
            await RegistrationFormsLite.createRegistrationPageUsingShortcodeLite(registrationFormPageTitle);
            //Change Registration settings
            await SettingsSetup.changeSettingsSetRegistrationPage(registrationFormPageTitle);

            await new BasicLogoutPage(page).logOut();
        });


        test('RF0003_PRO : Here, User is registering using - Registration Form', async () => {
            const RegistrationFormsFrontend = new RegistrationFormsFrontendPage(page);
            //FrontEnd
            //Complete FrontEnd Registration
            await RegistrationFormsFrontend.completeUserRegistrationFormFrontend();
        });


        test('RF0004_PRO : Here, Admin is validating - Registered user', async () => {
            const BasicLogin = new BasicLoginPage(page);
            const RegistrationFormsFrontend = new RegistrationFormsFrontendPage(page);
            //Basic Login
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            //Validate FrontEnd Registered
            await RegistrationFormsFrontend.validateUserRegisteredAdminEnd();

            await new BasicLogoutPage(page).logOut();
        });

    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });

}