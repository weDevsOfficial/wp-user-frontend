require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { basicLogoutPage } from '../pages/basicLogout';
import { testData } from '../utils/testData'

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
     * @Test_005 : Here, Admin is visiting WPUF Page
     * @Test_006 : Admin is changing WPUF Settings...
     * @Test_007 : Admin is able to Log out succesfully...
     * 
     * 
     *  
     */ 
    
    test('001:[Login] Here, Admin is logging into Admin-Dashboard', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);

        console.log(testData.users.adminUsername, testData.users.adminPassword);
        await BasicLogin.basic_login(testData.users.adminUsername, testData.users.adminPassword);
    });

    test('002:[Login] Here, Admin is skipping WPUF setup', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        await BasicLogin.wpufSetup();
    });

    test('003:[Login] Here, Admin is checking Dashboard page reached', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        await BasicLogin.validateBasicLogin();
    });

    test('004:[Login] Here, Admin is checking Plugin Status - Lite Activation', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        await BasicLogin.pluginStatusCheck_Lite_Activate();
    });


    test('005:[Login] Here, Admin is visiting WPUF Page', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        await BasicLogin.pluginVisit();
    });

    test('006:[Login] Here, Admin is changing WPUF Settings', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        await BasicLogin.change_WPUF_Settings();
    });

    test('007 Here, Admin is able to Log out succesfully', async ({page}) => {
        const BasicLogout = new basicLogoutPage(page);
        await BasicLogout.logOut();
    })




});


};