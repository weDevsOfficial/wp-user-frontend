import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Urls, Users } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';
import * as fs from "fs";

/**----------------------------------Reset Site----------------------------------**
 *
 * @Test_Scenarios : [Reset Site] 
 * @Test_RLS0001 : Admin is resetting Local Site
 * 
 */

export default function resetWordpressSite() {

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
    test.describe('Reset Site', () => {

        test('RS0001 : Admin is resetting Site', { tag: ['@Basic'] }, async () => {
            const BasicLogin = new BasicLoginPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();
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