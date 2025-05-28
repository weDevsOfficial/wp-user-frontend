import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';
import * as fs from "fs";

export default function loginAndSetupTests() {
    let browser: Browser;
    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Launch browser
        browser = await chromium.launch();
        
        // Create a single context
        context = await browser.newContext();
        
        // Create a single page
        page = await context.newPage();
    });

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
        test('LS0001 : Admin is logging into Admin-Dashboard', async () => {
            const BasicLogin = new BasicLoginPage(page);
            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
        });

        test('LS0002 : Admin is checking Dashboard page reached', async () => {
            const BasicLogin = new BasicLoginPage(page);
            await BasicLogin.validateBasicLogin();
        });

        test('LS0003 : Admin is checking Plugin Status - Lite Activation', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.pluginStatusCheckLite();
        });

        test('LS0004 : Admin is checking Plugin Status - Pro Activation', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.pluginStatusCheckPro();
        });

        test('LS0005 : Admin is activating license - Pro', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.licenseActivateWPUFPro();
        });

        test('LS0006 : Admin is Completing WPUF setup', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.wpufSetup();
        });

        test('LS0007 : Admin is visiting WPUF Page', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.pluginVisitWPUF();
        });

        test('LS0008 : Admin is validating WPUF Pages', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.validateWPUFpages();
        });

        test('LS0009 : Admin is validating WPUF Pages from frontend', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.validateWPUFpagesFE();
        });

        test('LS0010 : Admin is validating account page tabs from frontend', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.validateAccountPageTabs();
        });

        test('LS0011 : Admin is changing WPUF Settings', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.changeSettingsSetLoginPageDefault();
        });

        test('LS0012 : Admin is setting Permalink', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.setPermalink();
        });

        test('LS0013 : Admin is allowing anyone to register', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.allowRegistration();
        });

        test('LS0014 : Admin is creating a New User', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.createNewUserAdmin(
                Users.userName, 
                Users.userEmail, 
                Users.userFirstName, 
                Users.userLastName, 
                Users.userPassword
            );
        });

        test('LS0015 : Admin is adding post categories', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.createPostCategories();
        });

        test('LS0016 : Admin is adding post tags', async () => {
            const SettingsSetup = new SettingsSetupPage(page);
            await SettingsSetup.createPostTags();
        });

    test('LS0017 : Admin is adding credentils for Google Map', async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const googleMapAPIKey = process.env.GOOGLE_MAP_API_KEY;
        await SettingsSetup.addGoogleMapAPIKey(googleMapAPIKey?.toString() || '');
    });

    test('LS0018 : Admin is adding credentils for ReCaptcha', async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const reCaptchaSiteKey = process.env.RECAPTCHA_SITE_KEY;
        const reCaptchaSecretKey = process.env.RECAPTCHA_SECRET_KEY;
        await SettingsSetup.addReCaptchaKeys(reCaptchaSiteKey?.toString() || '', reCaptchaSecretKey?.toString() || '');
    });

    test('LS0019 : Admin is adding credentils for Cloudflare Turnstile', async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const cloudflareTurnstileSiteKey = process.env.CLOUDFLARE_TURNSTILE_SITE_KEY;
        const cloudflareTurnstileSecretKey = process.env.CLOUDFLARE_TURNSTILE_SECRET_KEY;
        await SettingsSetup.addCloudflareTurnstileKeys(cloudflareTurnstileSiteKey?.toString() || '', cloudflareTurnstileSecretKey?.toString() || '');
    });

        test('LS0020 : Admin is logging out successfully', async () => {
            const BasicLogout = new BasicLogoutPage(page);
            await BasicLogout.logOut();
        });
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });
}