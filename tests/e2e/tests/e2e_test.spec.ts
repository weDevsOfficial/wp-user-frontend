require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/01_Basic/basicLogin';
import { PostForms_Create } from '../pages/02_PostForms/postForms_Create';
import { RegistrationForms_Create } from '../pages/03_RegistrationForms/registrationForms_Create';

import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie





//Clear Cookie
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

//Faker
//Post-Forms-Faker
const PF_postName1 = faker.lorem.word();
const PF_postName2 = faker.lorem.word();
const PF_postName3 = faker.lorem.word();
const PF_postName4 = faker.lorem.word();
//Registration-Forms-Faker
const RF_postName1 = faker.lorem.word();
const RF_postName2 = faker.lorem.word();
const RF_postName3 = faker.lorem.word();
const RF_postName4 = faker.lorem.word();










test.describe('TEST :-->', () => {
    

/**----------------------------------LOGIN----------------------------------**
     * 
     * 
     * @Test_Scenario : [LOGIN] 
     * @Test_001 : Admin is logging in...
     * @Test_002 : Admin is skipping WPUF setup...
     * @Test_003 : Admin is checking Dashboard page reached...
     * @Test_004 : Admin is checking Plugin Status...
     * @Test_005 : Here, Admin is visiting WPUF Page
     * @Test_006 : Admin is changing WPUF Settings...
     * 
     * 
     *  
     */ 
    
    test('001:[Login] Here, Admin is logging into Admin-Dashboard', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        
        
        await basicLogin.basiclogin(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
    });

    test('002:[Login] Here, Admin is skipping WPUF setup', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.wpufSetup();
    });

    test('003:[Login] Here, Admin is checking Dashboard page reached', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.validateBasicLogin();
    });

    test('004:[Login] Here, Admin is checking Plugin Status + activating Plugin if found deactivated', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.pluginStatusCheck();
    });

    test('005:[Login] Here, Admin is visiting WPUF Page', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.pluginVisit();
    });

    test('006:[Login] Here, Admin is changing WPUF Settings', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.change_WPUF_Settings();

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });










/**----------------------------------POST_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Post-Forms]
     * @Test007 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test008 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test009 : Admin is creating Blank Form with > PF + CustomFields...
     * @Test0010 : Admin is creating Blank Form with > PF + Others...
     * @Test0011 : Admin is creating Blank Form with all Fields...
     * @Test0012 : Admin is creating a Preset Post Form...
     * 
     * 
     *  
     */ 
    test('007:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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


    test('008:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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


    test('009:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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


    test('0010:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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


    test('0011:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({page}) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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


    test('0012:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({page}) => {
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
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










/**----------------------------------REGISTRATION_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Reg-Forms]
     * @Test0013 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test0014 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0015 : Admin is creating Blank Form with > PF + CustomFields...
     * 
     * 
     *  
     */ 
    test.skip('0013:[Reg-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')

        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
        //Post Blank Form
        await regForms_Create.create_BlankForm_RF(RF_postName1);
        //PostFields
        await regForms_Create.add_ProfileFields_RF();
        //Save
        await postForms_Create.save_Form_PF(RF_postName1);
        //Validate
        await regForms_Create.validate_BlankForm_Created_RF(RF_postName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0014:[Reg-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
        //Post Blank Form
        await regForms_Create.create_BlankForm_RF(RF_postName2);
        //PostFields
        await regForms_Create.add_ProfileFields_RF();
        //CustomFields
        await postForms_Create.add_CustomFields_PF();
        //Save
        await postForms_Create.save_Form_PF(RF_postName2);
        //Validate
        await regForms_Create.validate_BlankForm_Created_RF(RF_postName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test.skip('0015:[Reg-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        
        //Post Blank Form
        await regForms_Create.create_BlankForm_RF(RF_postName3);
        //PostFields
        await regForms_Create.add_ProfileFields_RF();
        //Others
        await postForms_Create.add_Others_PF();
        //Save
        await postForms_Create.save_Form_PF(RF_postName3);
        //Validate
        await regForms_Create.validate_BlankForm_Created_RF(RF_postName3);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


});