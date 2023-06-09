require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { registrationForms } from '../pages/registrationForms';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon'
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie






export default function RegistrationFormTests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------REGISTRATIONFORMS----------------------------------**
     * 
     * 
     * @TestScenario : [Reg-Forms]
     * @Test0014 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test0015 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0016 : Admin is creating Blank Form with > PF + CustomFields...
     * 
     * 
     *  
     */ 
    test.skip('0014:[Reg-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')

        const BasicLogin = new basicLoginPage(page);
        const RegistrationForms = new registrationForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegistrationForms.createBlankFormRF(testData.registrationForms.rfPostName1);
        //PostFields
        await FieldOptionsCommon.addProfileFieldsRF();
        //Save
        await FieldOptionsCommon.saveFormCommon(testData.registrationForms.rfPostName1);
        //Validate
        await FieldOptionsCommon.validateBlankFormCreatedRF(testData.registrationForms.rfPostName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0015:[Reg-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const BasicLogin = new basicLoginPage(page);
        const RegistrationForms = new registrationForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegistrationForms.createBlankFormRF(testData.registrationForms.rfPostName2);
        //PostFields
        await FieldOptionsCommon.addProfileFieldsRF();
        //CustomFields
        await FieldOptionsCommon.addCustomFieldsCommon();
        //Save
        await FieldOptionsCommon.saveFormCommon(testData.registrationForms.rfPostName2);
        //Validate
        await FieldOptionsCommon.validateBlankFormCreatedRF(testData.registrationForms.rfPostName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0016:[Reg-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const BasicLogin = new basicLoginPage(page);
        const RegistrationForms = new registrationForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegistrationForms.createBlankFormRF(testData.registrationForms.rfPostName3);
        //PostFields
        await FieldOptionsCommon.addProfileFieldsRF();
        //Others
        await FieldOptionsCommon.addOthersCommon();
        //Save
        await FieldOptionsCommon.saveFormCommon(testData.registrationForms.rfPostName3);
        //Validate
        await FieldOptionsCommon.validateBlankFormCreatedRF(testData.registrationForms.rfPostName3);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


});

}