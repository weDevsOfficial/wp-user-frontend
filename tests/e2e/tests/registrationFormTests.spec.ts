require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { postFormsCreate } from '../pages/postFormsCreate';
import { registrationFormsCreate } from '../pages/registrationFormsCreate';
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie






export default function Registration_Form_Tests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------REGISTRATION_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Reg-Forms]
     * @Test0014 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test0015 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0016 : Admin is creating Blank Form with > PF + CustomFields...
     * 
     * 
     *  
     */ 
    test.skip('0014:[Reg-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')

        const RegForms_Create = new registrationFormsCreate(page);
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegForms_Create.create_BlankForm_RF(testData.registrationForms.rf_postName1);
        //PostFields
        await RegForms_Create.add_ProfileFields_RF();
        //Save
        await PostFormsCreate.save_Form_PF(testData.registrationForms.rf_postName1);
        //Validate
        await RegForms_Create.validate_BlankForm_Created_RF(testData.registrationForms.rf_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0015:[Reg-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const RegForms_Create = new registrationFormsCreate(page);
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegForms_Create.create_BlankForm_RF(testData.registrationForms.rf_postName2);
        //PostFields
        await RegForms_Create.add_ProfileFields_RF();
        //CustomFields
        await PostFormsCreate.add_CustomFields_PF();
        //Save
        await PostFormsCreate.save_Form_PF(testData.registrationForms.rf_postName2);
        //Validate
        await RegForms_Create.validate_BlankForm_Created_RF(testData.registrationForms.rf_postName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0016:[Reg-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const RegForms_Create = new registrationFormsCreate(page);
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await RegForms_Create.create_BlankForm_RF(testData.registrationForms.rf_postName3);
        //PostFields
        await RegForms_Create.add_ProfileFields_RF();
        //Others
        await PostFormsCreate.add_Others_PF();
        //Save
        await PostFormsCreate.save_Form_PF(testData.registrationForms.rf_postName3);
        //Validate
        await RegForms_Create.validate_BlankForm_Created_RF(testData.registrationForms.rf_postName3);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


});

}