require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basic_01/basic_login';
import { BasicLogoutPage } from '../pages/basic_01/basic_logout';
import { PostForms_Create } from '../pages/post_forms_02/post_forms_create';
import { RegistrationForms_Create } from '../pages/registration_forms_03/registration_forms_create';

import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie







//Faker
//Post-Forms-Faker
const PF_postName1 = faker.lorem.word();
const PF_postName2 = faker.lorem.word();
const PF_postName3 = faker.lorem.word();
const PF_postName4 = faker.lorem.word();







export default function Post_Form_Tests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------POST_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Post-Forms]
     * @Test009 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test0010 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0011 : Admin is creating Blank Form with > PF + CustomFields...
     * @Test0012 : Admin is creating Blank Form with > PF + Others...
     * @Test0013 : Admin is creating Blank Form with all Fields...
     * @Test0014 : Admin is creating a Preset Post Form...
     * 
     * 
     *  
     */ 
    test('009:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Blank Form
        await postForms_Create.create_BlankForm_PF(PF_postName1);
        //PostFields + Validate
        await postForms_Create.add_PostFields_PF();
        await postForms_Create.validate_PostFields_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName1);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0010:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Blank Form
        await postForms_Create.create_BlankForm_PF(PF_postName2);
        //PostFields
        await postForms_Create.add_PostFields_PF();
        //Taxonomies + Validate
        await postForms_Create.add_Taxonomies_PF();
        await postForms_Create.validate_Taxonomies_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName2);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0011:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Blank Form
        await postForms_Create.create_BlankForm_PF(PF_postName3);
        //PostFields
        await postForms_Create.add_PostFields_PF();
        //CustomFields + Validate
        await postForms_Create.add_CustomFields_PF();
        await postForms_Create.validate_CustomFields_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName3);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName3);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0012:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Blank Form
        await postForms_Create.create_BlankForm_PF(PF_postName4);
        //PostFields
        await postForms_Create.add_PostFields_PF();
        //Others + Validate
        await postForms_Create.add_Others_PF();
        await postForms_Create.validate_Others_PF();
        await postForms_Create.set_MultiStep_Settings_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName4);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName4);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0013:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({page}) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Blank Form
        await postForms_Create.create_BlankForm_PF(PF_postName1);
        //PostFields + Validate
        await postForms_Create.add_PostFields_PF();
        await postForms_Create.validate_PostFields_PF();
        //Taxonomies + Validate
        await postForms_Create.add_Taxonomies_PF();
        await postForms_Create.validate_Taxonomies_PF();
        //CustomFields + Validate
        await postForms_Create.add_CustomFields_PF();
        await postForms_Create.validate_CustomFields_PF();
        //Others + Validate
        await postForms_Create.add_Others_PF();
        await postForms_Create.validate_Others_PF();
        await postForms_Create.set_MultiStep_Settings_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName1);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });


    test('0014:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({page}) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
        //Post Preset Form
        await postForms_Create.create_Preset_PF(PF_postName1);
        //Validate
        await postForms_Create.validate_PostFields_PF();
        await postForms_Create.validate_Taxonomies_Preset_PF();

        //Save
        await postForms_Create.save_Form_PF(PF_postName1);
        //Validate
        await postForms_Create.validate_BlankForm_Created_PF(PF_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });



});

};