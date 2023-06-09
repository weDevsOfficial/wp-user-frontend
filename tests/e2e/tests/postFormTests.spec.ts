require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { postFormsCreate } from '../pages/postFormsCreate';
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie



fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });




export default function Post_Form_Tests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------POST_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Post-Forms]
     * @Test008 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test009 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0010 : Admin is creating Blank Form with > PF + CustomFields...
     * @Test0011 : Admin is creating Blank Form with > PF + Others...
     * @Test0012 : Admin is creating Blank Form with all Fields...
     * @Test0013 : Admin is creating a Preset Post Form...
     * 
     * 
     *  
     */ 
    test('008:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await PostFormsCreate.create_BlankForm_PF(testData.postForms.pf_postName1);
        //PostFields + Validate
        await PostFormsCreate.add_PostFields_PF();
        await PostFormsCreate.validate_PostFields_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName1);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('009:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await PostFormsCreate.create_BlankForm_PF(testData.postForms.pf_postName2);
        //PostFields
        await PostFormsCreate.add_PostFields_PF();
        //Taxonomies + Validate
        await PostFormsCreate.add_Taxonomies_PF();
        await PostFormsCreate.validate_Taxonomies_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName2);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0010:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await PostFormsCreate.create_BlankForm_PF(testData.postForms.pf_postName3);
        //PostFields
        await PostFormsCreate.add_PostFields_PF();
        //CustomFields + Validate
        await PostFormsCreate.add_CustomFields_PF();
        await PostFormsCreate.validate_CustomFields_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName3);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName3);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0011:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await PostFormsCreate.create_BlankForm_PF(testData.postForms.pf_postName4);
        //PostFields
        await PostFormsCreate.add_PostFields_PF();
        //Others + Validate
        await PostFormsCreate.add_Others_PF();
        await PostFormsCreate.validate_Others_PF();
        await PostFormsCreate.set_MultiStep_Settings_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName4);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName4);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0012:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({page}) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Blank Form
        await PostFormsCreate.create_BlankForm_PF(testData.postForms.pf_postName1);
        //PostFields + Validate
        await PostFormsCreate.add_PostFields_PF();
        await PostFormsCreate.validate_PostFields_PF();
        //Taxonomies + Validate
        await PostFormsCreate.add_Taxonomies_PF();
        await PostFormsCreate.validate_Taxonomies_PF();
        //CustomFields + Validate
        await PostFormsCreate.add_CustomFields_PF();
        await PostFormsCreate.validate_CustomFields_PF();
        //Others + Validate
        await PostFormsCreate.add_Others_PF();
        await PostFormsCreate.validate_Others_PF();
        await PostFormsCreate.set_MultiStep_Settings_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName1);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });


    test('0013:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({page}) => {
        const PostFormsCreate = new postFormsCreate(page);
        const BasicLogin = new basicLoginPage(page);

        await BasicLogin.basic_login_plugin_visit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Preset Form
        await PostFormsCreate.create_Preset_PF(testData.postForms.pf_postName2);
        //Validate
        await PostFormsCreate.validate_PostFields_PF();
        await PostFormsCreate.validate_Taxonomies_Preset_PF();

        //Save
        await PostFormsCreate.save_Form_PF(testData.postForms.pf_postName2);
        //Validate
        await PostFormsCreate.validate_BlankForm_Created_PF(testData.postForms.pf_postName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });



});

};