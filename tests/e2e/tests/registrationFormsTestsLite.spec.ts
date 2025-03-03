require('dotenv').config();
import { test } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { RegistrationFormsPage } from '../pages/registrationForms';
import { RegistrationFormsFrontendPage } from '../pages/registrationFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie




export default function registrationFormsTests() {


test.describe('Registration-Forms @Lite :-->', () => {
/**----------------------------------REGISTRATIONFORMS----------------------------------**
 * 
 * @TestScenario : [Reg-Forms]
 * @Test0019 : Admin is checking Registration Forms - Pro Feature Page
 * @Test0020 : Admin is creating Registration Forms - using shortcode
 * @Test0021 : User is registering using - Registration Form
 * @Test0022 : Admin is validating - Registered user
 * 
 *  
 */


    test('0019:[Reg-Forms] Here, Admin is checking Registration Forms - Pro Feature Page', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        const RegistrationFormsLite = new RegistrationFormsPage(page);
        //Basic login
        await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        await RegistrationFormsLite.validateRegistrationFormsProFeatureLite();

    });


    test('0020:[Reg-Forms] Here, Admin is creating Registration Forms Page - using shortcode', async ({ page }) => {
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


    test('0021:[Reg-Forms] Here, User is registering using - Registration Form', async ({ page }) => {
        const RegistrationFormsFrontend = new RegistrationFormsFrontendPage(page);
        //FrontEnd
        //Complete FrontEnd Registration
        await RegistrationFormsFrontend.completeUserRegistrationFormFrontend();

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0022:[Reg-Forms] Here, Admin is validating - Registered user', async ({ page }) => {
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