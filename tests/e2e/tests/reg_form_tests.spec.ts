require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/01_Basic/basicLogin';
import { BasicLogoutPage } from '../pages/01_Basic/basicLogout';
import { PostForms_Create } from '../pages/02_PostForms/postForms_Create';
import { RegistrationForms_Create } from '../pages/03_RegistrationForms/registrationForms_Create';

import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie






//Faker
//Registration-Forms-Faker
const RF_postName1 = faker.lorem.word();
const RF_postName2 = faker.lorem.word();
const RF_postName3 = faker.lorem.word();
const RF_postName4 = faker.lorem.word();









export default function Registration_Form_Tests() {


test.describe('TEST :-->', () => {
    


/**----------------------------------REGISTRATION_FORMS----------------------------------**
     * 
     * 
     * @Test_Scenario : [Reg-Forms]
     * @Test0015 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test0016 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0017 : Admin is creating Blank Form with > PF + CustomFields...
     * 
     * 
     *  
     */ 
    test.skip('0015:[Reg-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')

        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
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


    test.skip('0016:[Reg-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
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


    test.skip('0017:[Reg-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        test.fail(!!process.env.CI, 'Issue after Add Form Button > Script not loading')
        
        const regForms_Create = new RegistrationForms_Create(page);
        const postForms_Create = new PostForms_Create(page);
        const basicLogin = new BasicLoginPage(page);

        await basicLogin.basiclogin2('admin', 'password');
        
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

}