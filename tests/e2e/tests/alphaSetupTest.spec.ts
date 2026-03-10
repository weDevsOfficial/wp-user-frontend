import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';
import { PostFormPage } from '../pages/postForm';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll(async () => {
    // Launch browser
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();
});

test.describe('Login and Setup', () => {
    // Configure fail-fast behavior for this spec file
    configureSpecFailFast();
    
    /**----------------------------------LOGIN----------------------------------**
     *
     * @Test_Scenarios : [LOGIN & SETUP] 
     * @Test_LS0001 : Admin is logging into Admin-Dashboard
     * @Test_LS0002 : Admin is checking Dashboard page reached
     * @Test_LS0003 : Admin is checking Plugin Status - Lite Activation
     * @Test_LS0004 : Admin is checking Plugin Status - Pro Activation
     * @Test_LS0005 : Admin is activating license - Pro
     * @Test_LS0006 : Admin is Completing WPUF setup
     * @Test_LS0007 : Admin is setting Permalink
     * @Test_LS0008 : Admin is visiting WPUF Post Form List
     * @Test_LS0009 : Admin is visiting WPUF Registration Form List
     * @Test_LS0010 : Admin is validating WPUF Pages
     * @Test_LS0011 : Admin is validating WPUF Pages from frontend
     * @Test_LS0012 : Admin is validating account page tabs from frontend
     * @Test_LS0013 : Admin is changing WPUF Settings
     * @Test_LS0014 : Admin is allowing anyone to register
     * @Test_LS0015 : Admin is creating a New User
     * @Test_LS0016 : Admin is adding post categories
     * @Test_LS0017 : Admin is adding post tags
     * @Test_LS0018 : Admin is adding credentils for Google Map
     * @Test_LS0019 : Admin is adding credentils for ReCaptcha
     * @Test_LS0020 : Admin is adding credentils for Cloudflare Turnstile
     * @Test_LS0021 : Admin is enabling payment gateway bank
     * @Test_LS0022 : Admin is activating dokan lite
     * @Test_LS0023 : Admin is making user directory page
     * @Test_LS0024 : Admin is logging out succesfully
     *  
     */
    if (process.env.CI !== 'true') {
        test('RS0001 : Admin is resetting Site', { tag: ['@Basic'] }, async () => {
            const BasicLogin = new BasicLoginPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();
            const BasicLogout = new BasicLogoutPage(page);
            await BasicLogout.logOut();

        });
    }

    test('LS0001 : Admin is logging into Admin-Dashboard', { tag: ['@Basic'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });

    test('LS0002 : Admin is checking Dashboard page reached', { tag: ['@Basic'] }, async () => {
        const BasicLogin = new BasicLoginPage(page);
        await BasicLogin.validateBasicLogin();
    });

    test('LS0003 : Admin is checking Plugin Status - Lite Activation', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckLite();
    });

    test('LS0004 : Admin is checking Plugin Status - Pro Activation', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.pluginStatusCheckPro();
    });

    test('LS0005 : Admin is activating license - Pro', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.licenseActivateWPUFPro();
    });

    test('LS0006 : Admin is Completing WPUF setup', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.wpufSetup();
    });

    test('LS0007 : Admin is setting Permalink', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.setPermalink();
    });

    test('LS0008 : Admin is visiting WPUF Post Form List', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.postFormListVisit();
    });

    test('LS0009 : Admin is visiting WPUF Registration Form List', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.regFormListVisit();
    });

    test('LS0010 : Admin is validating WPUF Pages', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateWPUFpages();
    });

    test('LS0011 : Admin is validating WPUF Pages from frontend', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateWPUFpagesFE();
    });

    test('LS0012 : Admin is validating account page tabs from frontend', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.validateAccountPageTabs();
    });

    test('LS0013 : Admin is changing WPUF Settings', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.changeSettingsSetLoginPageDefault();
    });

    test('LS0014 : Admin is allowing anyone to register', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.allowRegistration();
    });

    test('LS0015 : Admin is creating a New User', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.createNewUser(
            Users.userName,
            Users.userEmail,
            Users.userFirstName,
            Users.userLastName,
            Users.userPassword
        );
    });

    test('LS0016 : Admin is adding post categories', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.createPostCategories();
    });

    test('LS0017 : Admin is adding post tags', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.createPostTags();
    });

    test('LS0018 : Admin is adding credentils for Google Map', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const googleMapAPIKey = process.env.GOOGLE_MAP_API_KEY;
        await SettingsSetup.addGoogleMapAPIKey(googleMapAPIKey?.toString() || '');
    });

    test('LS0019 : Admin is adding credentils for ReCaptcha', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const reCaptchaSiteKey = process.env.RECAPTCHA_SITE_KEY;
        const reCaptchaSecretKey = process.env.RECAPTCHA_SECRET_KEY;
        await SettingsSetup.addReCaptchaKeys(reCaptchaSiteKey?.toString() || '', reCaptchaSecretKey?.toString() || '');
    });

    test('LS0020 : Admin is adding credentils for Cloudflare Turnstile', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        const cloudflareTurnstileSiteKey = process.env.CLOUDFLARE_TURNSTILE_SITE_KEY;
        const cloudflareTurnstileSecretKey = process.env.CLOUDFLARE_TURNSTILE_SECRET_KEY;
        await SettingsSetup.addCloudflareTurnstileKeys(cloudflareTurnstileSiteKey?.toString() || '', cloudflareTurnstileSecretKey?.toString() || '');
    });

    test('LS0021 : Admin is enabling payment gateway bank', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.enablePaymentGatewayBank();
    });

    test('LS0022 : Admin is activating dokan lite', { tag: ['@Basic'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);
        await SettingsSetup.dokanLiteStatusCheck();
    });

    test('LS0023 : Admin is making user directory page', { tag: ['@Basic'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcode('[wpuf_user_listing]', 'Users');
    });

    test('LS0024 : Admin is logging out successfully', { tag: ['@Basic'] }, async () => {
        const BasicLogout = new BasicLogoutPage(page);
        await BasicLogout.logOut();
    });
});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});