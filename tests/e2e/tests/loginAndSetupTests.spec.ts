require('dotenv').config();
import { test, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SettingsSetupPage } from '../pages/settingsSetup'
import { Users } from '../utils/testData'


export default function loginAndSetupTests() {


test.describe('Login and Setup :-->', () => {
/**----------------------------------LOGIN----------------------------------**
 *
 * @Test_Scenarios : [LOGIN & SETUP] 
 * @Test_0001 : Admin is logging into Admin-Dashboard
 * @Test_0002 : Admin is checking Dashboard page reached
 * @Test_0003 : Admin is checking Plugin Status - Lite Activation
 * @Test_0004 : Admin is checking Plugin Status - Pro Activation
 * @Test_0005 : Admin is activating license - Pro
 * @Test_0006 : Admin is Completing WPUF setup
 * @Test_0007 : Admin is visiting WPUF Page
 * @Test_0008 : Admin is changing WPUF Settings
 * @Test_0009 : Admin is setting Permalink
 * @Test_0010 : Admin is allowing anyone to register
 * @Test_0011 : Admin is creating a New User
 * @Test_0012 : Admin is adding credentils for Google Map
 * @Test_0013 : Admin is adding credentils for ReCaptcha
 * @Test_0014 : Admin is adding credentils for Cloudflare Turnstile
 * @Test_0015 : Admin is logging out succesfully
 *  
 */

    test('0001: Admin is logging into Admin-Dashboard', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });


    test('0002: Admin is checking Dashboard page reached', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.validateBasicLogin();
    });


    test('0003: Admin is checking Plugin Status - Lite Activation', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckLite();
    });

    test('0004: Admin is checking Plugin Status - Pro Activation', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckPro();
    });

    test('0005: Admin is activating license - Pro', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.licenseActivateWPUFPro();
    });


    test('0006: Admin is Completing WPUF setup', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.wpufSetup();
    });


    test('0007: Admin is visiting WPUF Page', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginVisitWPUF();
    });


    test('0008: Admin is changing WPUF Settings', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.changeSettingsSetLoginPageDefault();
    });


    test('0009: Admin is setting Permalink', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.setPermalink();
    });

    test('0010: Admin is allowing anyone to register', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.allowRegistration();
    });


    test('0011: Admin is creating a New User', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //New User Credentials
        const userName = Users.userName;
        const email = Users.userEmail;
        const firstName = Users.userFirstName;
        const lastName = Users.userLastName;
        const password = Users.userPassword;

        await SettingsSetup.createNewUserAdmin(userName, email, firstName, lastName, password);
    });

    test('0012: Admin is adding credentils for Google Map', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const googleMapAPIKey = process.env.GOOGLE_MAP_API_KEY;

        await SettingsSetup.addGoogleMapAPIKey(googleMapAPIKey?.toString() || '');
    });

    test('0013: Admin is adding credentils for ReCaptcha', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const reCaptchaSiteKey = process.env.RECAPTCHA_SITE_KEY;
        const reCaptchaSecretKey = process.env.RECAPTCHA_SECRET_KEY;
        await SettingsSetup.addReCaptchaKeys(reCaptchaSiteKey?.toString() || '', reCaptchaSecretKey?.toString() || '');
    });

    test('0014: Admin is adding credentils for Cloudflare Turnstile', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const cloudflareTurnstileSiteKey = process.env.CLOUDFLARE_TURNSTILE_SITE_KEY;
        const cloudflareTurnstileSecretKey = process.env.CLOUDFLARE_TURNSTILE_SECRET_KEY;
        await SettingsSetup.addCloudflareTurnstileKeys(cloudflareTurnstileSiteKey?.toString() || '', cloudflareTurnstileSecretKey?.toString() || '');
    });


    test('0015: Admin is logging out succesfully', async ({ page }) => {
        const BasicLogout = new BasicLogoutPage(page);

        //Logout
        await BasicLogout.logOut();
    });



    //--------------_END_--------------/
});


};