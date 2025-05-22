import * as dotenv from 'dotenv';
import { test } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { RegistrationFormsPage } from '../pages/registrationForms';
import { RegistrationFormsFrontendPage } from '../pages/registrationFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';

import * as fs from 'fs'; //Clear Cookie




export default function registrationFormsTestsPro() {


test.describe('Registration-Forms @Pro :-->', () => {
/**----------------------------------REGISTRATIONFORMS----------------------------------**
 * 
 * @TestScenario : [Reg-Forms]
 * @Test_RF0001_PRO : Admin is checking Registration Forms - Pro Feature Page
 * @Test_RF0002_PRO : Admin is creating Registration Forms - using shortcode
 * @Test_RF0003_PRO : User is registering using - Registration Form
 * @Test_RF0004_PRO : Admin is validating - Registered user
 * 
 *  
 */

    test('RF0001 : Here, Admin is checking Registration Forms - Pro Feature Page', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        const RegistrationFormsLite = new RegistrationFormsPage(page);
        //Basic login
        await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        await RegistrationFormsLite.validateRegistrationFormsProFeature();

    });


    test('RF0002 : Here, Admin is creating Registration Forms Page - using shortcode', async ({ page }) => {
        const RegistrationFormsLite = new RegistrationFormsPage(page);
        const SettingsSetup = new SettingsSetupPage(page);
        //Registration Forms page - Title
        const registrationFormPageTitle = 'Registration Page';
        //Create Registration Forms page
        await RegistrationFormsLite.createRegistrationPageUsingShortcodeLite(registrationFormPageTitle);
        //Change Registration settings
        await SettingsSetup.changeSettingsSetRegistrationPage(registrationFormPageTitle);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('RF0003 : Here, User is registering using - Registration Form', async ({ page }) => {
        const RegistrationFormsFrontend = new RegistrationFormsFrontendPage(page);
        //FrontEnd
        //Complete FrontEnd Registration
        await RegistrationFormsFrontend.completeUserRegistrationFormFrontend();

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('RF0004 : Here, Admin is validating - Registered user', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        const RegistrationFormsFrontend = new RegistrationFormsFrontendPage(page);
        //Basic Login
        await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        //Validate FrontEnd Registered
        await RegistrationFormsFrontend.validateUserRegisteredAdminEnd();

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });

});

}