require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basic_01/basic_login';
import { BasicLogoutPage } from '../pages/basic_01/basic_logout';
import { PostForms_Create } from '../pages/post_forms_02/post_forms_create';
import { RegistrationForms_Create } from '../pages/registration_forms_03/registration_forms_create';

import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie







export default function Login_Tests() {


test.describe('TEST :-->', () => {
    

/**----------------------------------LOGIN----------------------------------**
     * 
     * 
     * @Test_Scenario : [LOGIN] 
     * @Test_001 : Admin is logging in...
     * @Test_002 : Admin is skipping WPUF setup...
     * @Test_003 : Admin is checking Dashboard page reached...
     * @Test_004 : Here, Admin is checking Plugin Status - Lite Activation...
     * @Test_005 : Here, Admin is checking Plugin Status - Pro Activation
     * @Test_006 : Here, Admin is visiting WPUF Page
     * @Test_007 : Admin is changing WPUF Settings...
     * @Test_008 : Admin is able to Log out succesfully...
     * 
     * 
     *  
     */ 
    
    test('001:[Login] Here, Admin is logging into Admin-Dashboard', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);



        await basicLogin.basic_login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
    });

    test('002:[Login] Here, Admin is skipping WPUF setup', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.wpufSetup();
    });

    test('003:[Login] Here, Admin is checking Dashboard page reached', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.validateBasicLogin();
    });

    test('004:[Login] Here, Admin is checking Plugin Status - Lite Activation', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.pluginStatusCheck_Lite_Activate();
    });

    test.skip('005:[Login] Here, Admin is checking Plugin Status - Pro Activation', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.pluginStatusCheck_Pro_Activate();
    });

    test('006:[Login] Here, Admin is visiting WPUF Page', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.pluginVisit();
    });

    test('007:[Login] Here, Admin is changing WPUF Settings', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.change_WPUF_Settings();
    });

    test('008 Here, Admin is able to Log out succesfully', async ({page}) => {
        const basicLogoutPage = new BasicLogoutPage(page);
        const basicLogin = new BasicLoginPage(page);
        await basicLogin.basic_login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        await basicLogoutPage.logOut();
    })



});


};