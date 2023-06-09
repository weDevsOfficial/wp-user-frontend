require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { registrationForms } from '../pages/registrationForms';
import { registrationFormsFrontEnd } from '../pages/registrationFormsFrontEnd';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon';
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie






export default function RegistrationFormTests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------REGISTRATIONFORMS----------------------------------**
     * 
     * @TestScenario : [Reg-Forms]
     * @Test0014 : Admin is checking Registration Forms - Pro Feature Page
     * @Test0015 : Admin is creating Registration Forms - using shortcode
     * @Test0016 : User is registering using - Registration Form
     * @Test0017 : Admin is validating - Registered user
     * 
     *  
     */ 
    test('0014:[Reg-Forms] Here, Admin is checking Registration Forms - Pro Feature Page', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        const RegistrationFormsLite = new registrationForms(page);
        
        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);

        await RegistrationFormsLite.validateRegistrationFormsProFeatureLite();

    });

    
    test('0015:[Reg-Forms] Here, Admin is creating Registration Forms Page - using shortcode', async ({ page }) => {
        const RegistrationFormsLite = new registrationForms(page);
        
        //Registration Forms page - Title
        const registrationFormPageTitle = 'Registration Page';
        
        //Create Registration Forms page
        await RegistrationFormsLite.createRegistrationPageUsingShortcodeLite(registrationFormPageTitle);
        //Change Registration settings
        await RegistrationFormsLite.changeSettingsSetRegistrationPage(registrationFormPageTitle);
        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });

    test('0016:[Reg-Forms] Here, User is registering using - Registration Form', async ({ page }) => {
        const RegistrationFormsFrontEnd = new registrationFormsFrontEnd(page);
        
        //FrontEnd
        //Complete FrontEnd Registration
        await RegistrationFormsFrontEnd.completeUserRegistrationFormFrontEnd();
        
        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });


    test('0017:[Reg-Forms] Here, Admin is validating - Registered user', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        const RegistrationFormsFrontEnd = new registrationFormsFrontEnd(page);
        
        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);

        //Validate FrontEnd Registered
        await RegistrationFormsFrontEnd.validateUserRegisteredAdminEnd();

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });

    


});

}