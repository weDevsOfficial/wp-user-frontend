import * as dotenv from 'dotenv';
dotenv.config();
import { test } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';


export default function loginAndSetupTests() {


test.describe('Login and Setup @Both :-->', () => {
/**----------------------------------LOGIN----------------------------------**
 *
 * @Test_Scenarios : [LOGIN & SETUP] 
 * @Test_LS0001 : Admin is logging into Admin-Dashboard
 * @Test_LS0002 : Admin is checking Dashboard page reached
 * @Test_LS0003 : Admin is checking Plugin Status - Lite Activation
 * @Test_LS0004 : Admin is checking Plugin Status - Pro Activation
 * @Test_LS0005 : Admin is activating license - Pro
 * @Test_LS0006 : Admin is Completing WPUF setup
 * @Test_LS0007 : Admin is visiting WPUF Page
 * @Test_LS0008 : Admin is validating WPUF Pages
 * @Test_LS0009 : Admin is validating WPUF Pages from frontend
 * @Test_LS0010 : Admin is validating account page tabs from frontend
 * @Test_LS0011 : Admin is changing WPUF Settings
 * @Test_LS0012 : Admin is setting Permalink
 * @Test_LS0013 : Admin is allowing anyone to register
 * @Test_LS0014 : Admin is creating a New User
 * @Test_LS0015 : Admin is adding post categories
 * @Test_LS0016 : Admin is adding post tags
 * @Test_LS0017 : Admin is adding credentils for Google Map
 * @Test_LS0018 : Admin is adding credentils for ReCaptcha
 * @Test_LS0019 : Admin is adding credentils for Cloudflare Turnstile
 * @Test_LS0020 : Admin is logging out succesfully
 *  
 */

    test('LS0001 : Admin is logging into Admin-Dashboard', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });


    test('LS0002 : Admin is checking Dashboard page reached', async ({ page }) => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.validateBasicLogin();
    });


    test('LS0003 : Admin is checking Plugin Status - Lite Activation', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckLite();
    });

    test('LS0004 : Admin is checking Plugin Status - Pro Activation', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckPro();
    });

    test('LS0005 : Admin is activating license - Pro', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.licenseActivateWPUFPro();
    });


    test('LS0006 : Admin is Completing WPUF setup', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.wpufSetup();
    });


    test('LS0007 : Admin is visiting WPUF Page', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginVisitWPUF();
    });

    test('LS0008 : Admin is validating WPUF Pages', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateWPUFpages();
    });

    test('LS0009 : Admin is validating WPUF Pages from frontend', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateWPUFpagesFE();
    });

    test('LS0010 : Admin is validating account page tabs from frontend', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateAccountPageTabs();
    });


    test('LS0011 : Admin is changing WPUF Settings', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.changeSettingsSetLoginPageDefault();
    });


    test('LS0012 : Admin is setting Permalink', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.setPermalink();
    });

    test('LS0013 : Admin is allowing anyone to register', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.allowRegistration();
    });


    test('LS0014 : Admin is creating a New User', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //New User Credentials
        const userName = Users.userName;
        const email = Users.userEmail;
        const firstName = Users.userFirstName;
        const lastName = Users.userLastName;
        const password = Users.userPassword;

        await SettingsSetup.createNewUserAdmin(userName, email, firstName, lastName, password);
    });

    test('LS0015 : Admin is adding post categories', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        await SettingsSetup.createPostCategories();
    });

    test('LS0016 : Admin is adding post tags', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        await SettingsSetup.createPostTags();
    });

    test('LS0017 : Admin is adding credentils for Google Map', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const googleMapAPIKey = process.env.GOOGLE_MAP_API_KEY;

        await SettingsSetup.addGoogleMapAPIKey(googleMapAPIKey?.toString() || '');
    });

    test('LS0018 : Admin is adding credentils for ReCaptcha', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const reCaptchaSiteKey = process.env.RECAPTCHA_SITE_KEY;
        const reCaptchaSecretKey = process.env.RECAPTCHA_SECRET_KEY;
        await SettingsSetup.addReCaptchaKeys(reCaptchaSiteKey?.toString() || '', reCaptchaSecretKey?.toString() || '');
    });

    test('LS0019 : Admin is adding credentils for Cloudflare Turnstile', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        //Google Map API Key
        const cloudflareTurnstileSiteKey = process.env.CLOUDFLARE_TURNSTILE_SITE_KEY;
        const cloudflareTurnstileSecretKey = process.env.CLOUDFLARE_TURNSTILE_SECRET_KEY;
        await SettingsSetup.addCloudflareTurnstileKeys(cloudflareTurnstileSiteKey?.toString() || '', cloudflareTurnstileSecretKey?.toString() || '');
    });


    test('LS0020 : Admin is logging out succesfully', async ({ page }) => {
        const BasicLogout = new BasicLogoutPage(page);

        //Logout
        await BasicLogout.logOut();
    });



    //--------------_END_--------------/
});


}