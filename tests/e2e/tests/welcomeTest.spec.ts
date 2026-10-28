import { Browser, BrowserContext, Page, test, chromium, expect } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { Selectors } from '../pages/selectors';
import { Users, Urls } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';
import { aiWp } from '../pages/aiFormBuilder';

let browser: Browser;
let context: BrowserContext;
let page: Page;

const welcomeUrl = `${Urls.baseUrl}/wp-admin/index.php?page=wpuf-welcome`;
// A full page load (the old menu URL opens the app), not a hash change.
const appUrl = `${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-post-forms`;

const setWelcomeSeen = (seen: boolean) => {
    try {
        // WP-CLI on the site under test (wp-env, or WPUF_E2E_WP_PATH).
        aiWp(seen
            ? [ 'user', 'meta', 'update', Users.adminUsername, 'wpuf_welcome_seen', '1' ]
            : [ 'user', 'meta', 'delete', Users.adminUsername, 'wpuf_welcome_seen' ]);
    } catch {
        // Nothing stored yet.
    }
};

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext();
    page = await context.newPage();

    setWelcomeSeen(true);

    await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
});

test.afterAll(async () => {
    setWelcomeSeen(true);

    await context?.close();
    await browser?.close();
});

test.describe('Welcome Tests', () => {

    configureSpecFailFast();

    /**----------------------------------WELCOME----------------------------------**
     *
     * @TestScenario : [Welcome page and the one-time welcome, free and Pro]
     * @Test_WEL0001 : Admin is opening the welcome page from its old URL
     * @Test_WEL0002 : Admin is validating the upgrade card follows Pro
     * @Test_WEL0003 : Admin is watching the getting started video
     * @Test_WEL0004 : Admin is seeing the one-time welcome once
     *
     ***--------------------------------------------------------------------------**/

    test('WEL0001 : Admin is opening the welcome page from its old URL', { tag: ['@Basic'] }, async () => {
        await page.goto(welcomeUrl);

        // The dashboard page opens the admin app route.
        await expect(page).toHaveURL(/page=wp-user-frontend#\/welcome/);
        await expect(page.locator(Selectors.welcome.heading)).toContainText('Welcome to WP User Frontend');
        await expect(page.locator(Selectors.welcome.createFormButton)).toBeVisible();
        await expect(page.locator(Selectors.welcome.setupButton)).toBeVisible();

        // Three groups of four features, each opening its guide in a new tab.
        await expect(page.locator(Selectors.welcome.sections)).toHaveCount(3);

        const links = page.locator(Selectors.welcome.sectionLinks);

        expect(await links.count()).toBe(12);

        for (const link of await links.all()) {
            await expect(link).toHaveAttribute('href', /^https:\/\/wedevs\.com\/docs\//);
            await expect(link).toHaveAttribute('target', '_blank');
        }
    });

    test('WEL0002 : Admin is validating the upgrade card follows Pro', { tag: ['@Basic'] }, async () => {
        await page.goto(welcomeUrl);
        await expect(page.locator(Selectors.welcome.heading)).toBeVisible();

        const isPro = await page.evaluate(() => !!(window as any).wpufWelcome?.isPro);

        await expect(page.locator(Selectors.welcome.proCard)).toHaveCount(isPro ? 0 : 1);
    });

    test('WEL0003 : Admin is watching the getting started video', { tag: ['@Basic'] }, async () => {
        await page.goto(welcomeUrl);
        await page.locator(Selectors.welcome.videoButton).click();

        await expect(page.locator(Selectors.welcome.videoFrame)).toBeVisible();

        await page.keyboard.press('Escape');
        await expect(page.locator(Selectors.welcome.videoFrame)).toHaveCount(0);
    });

    test('WEL0004 : Admin is seeing the one-time welcome once', { tag: ['@Basic'] }, async () => {
        setWelcomeSeen(false);

        await page.goto(appUrl);

        const intro = page.locator(Selectors.welcome.intro);

        await expect(intro).toBeVisible();
        await expect(page.locator(Selectors.welcome.introHeading)).toContainText('Welcome to WP User Frontend');
        await expect(page.locator(Selectors.welcome.introButton)).toBeFocused({ timeout: 5000 });

        // Esc closes it, and it does not come back.
        await page.keyboard.press('Escape');
        await expect(intro).toHaveCount(0);

        await page.reload();
        await expect(page.locator('#wpuf-admin-app')).toBeVisible();
        await page.waitForTimeout(1000);
        await expect(intro).toHaveCount(0);
    });
});
