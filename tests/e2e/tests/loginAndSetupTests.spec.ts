require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SettingsSetupPage } from '../pages/settingsSetup'
import { Users } from '../utils/testData'

import * as fs from "fs"; //Clear Cookie







export default function loginAndSetupTests() {


test.describe('Login and Setup :-->', () => {
/**----------------------------------LOGIN----------------------------------**
 * 
 * 
 * @Test_Scenario : [LOGIN] 
 * @Test_001 : Admin is logging into Admin-Dashboard...
 * @Test_002 : Admin is checking Dashboard page reached...
 * @Test_003 : Admin is checking Plugin Status - Lite Activation...
 * @Test_004 : Here, Admin is Completing WPUF setup...
 * @Test_005 : Here, Admin is visiting WPUF Page...
 * @Test_006 : Admin is changing WPUF Settings...
 * @Test_007 : Admin is setting Permalink...
 * @Test_008 : Admin is creating a New User...
 * @Test_009 : Admin is able to Log out succesfully...
 * 
 * 
 *  
 */

    test('001:[Login] Here, Admin is logging into Admin-Dashboard', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });


    test('002:[Login] Here, Admin is checking Dashboard page reached', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.validateBasicLogin();
    });


    test('003:[Login] Here, Admin is checking Plugin Status - Lite Activation', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckLite();
    });


    test('004:[Login] Here, Admin is Completing WPUF setup', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.wpufSetup();
    });


    test('005:[Login] Here, Admin is visiting WPUF Page', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginVisitWPUF();
    });


    test('006:[Login] Here, Admin is changing WPUF Settings', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.changeSettingsSetLoginPageDefault();
    });


    test('007: Here, Admin is setting Permalink', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.setPermalink();
    });


    test('008: Here, Admin is creating a New User', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //New User Credentials
        const userName = Users.userName;
        const email = Users.userEmail;
        const firstName = Users.userFirstName;
        const lastName = Users.userLastName;
        const password = Users.userPassword;

        await SettingsSetup.createNewUserAdmin(userName, email, firstName, lastName, password);
    });


    test('009: Here, Admin is logging out succesfully', async ({ page }) => {
        const BasicLogout = new BasicLogoutPage(page);

        //Logout
        await BasicLogout.logOut();
    });



    //--------------_END_--------------/
});


};