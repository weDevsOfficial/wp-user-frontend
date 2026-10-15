import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { aiWp, AiFormBuilderPage } from '../pages/aiFormBuilder';
import { Users, Urls } from '../utils/testData';
import { BUILDER_URL, builderFormId } from '../utils/builderUrl';

let browser: Browser;
let context: BrowserContext;
let page: Page;

/**
 * Release smoke: every React admin screen mounts from the built bundles with no
 * JavaScript error and no failed plugin asset. Run it against a site that has the
 * RELEASE packages installed (free from deploy-org.yml's rsync, Pro from a plan zip)
 * to prove the release build carries every React part.
 *
 * @TestScenario : [Release build]
 * @Test_RS0001 : Post forms list mounts (React) with no JS error or missing plugin asset
 * @Test_RS0002 : New post form opens the React builder
 * @Test_RS0003 : Subscriptions screen mounts
 * @Test_RS0004 : Settings screen mounts
 * @Test_RS0005 : Registration forms list and a new registration form open in React (Pro)
 * @Test_RS0006 : AI form builder page mounts
 * @Test_RS0007 : Admin app: every in-app menu row opens its screen with no page load and no JS error
 * @Test_RS0008 : Admin app: a reload on each route (deep link) opens that screen
 *
 * Every screen also fails when it loads Vue: a Vue runtime or app, or a bundle / sheet of the
 * removed Vue admin (old forms list, old builder, old AI builder, old subscriptions admin).
 */

/** Assets of the removed Vue admin; React screens must not load them. */
const VUE_ASSETS = [
    /\/vue(\.runtime)?(\.global)?(\.prod)?(\.min)?\.js/,
    /\/js\/forms-list\.min\.js/,
    /\/js\/subscriptions\.min\.js/,
    /\/js\/ai-form-builder\.min\.js/,
    /\/css\/ai-form-builder(\.min)?\.css/,
    /\/js\/wpuf-form-builder(-mixins|-components|-wpuf-forms)?(-pro)?\.js/,
    /\/js\/wpuf-form-builder-(field-option-data-pro|wpuf-profile)\.js/,
    /\/(wp-user-frontend-pro|wpuf-pro)\/assets\/css\/forms-list\.min\.css/,
];

const problems: string[] = [];
// REST requests a page load cut off (a reload during a fetch): the screen logs a
// fetch_error for them, which is not a problem of the screen.
let aborted = 0;

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext({ ignoreHTTPSErrors: true });
    page = await context.newPage();

    page.on('pageerror', (error) => problems.push(`pageerror: ${error.message}`));
    page.on('console', (message) => {
        // Google rejecting the test site's placeholder Maps key (a form with a Google
        // Map field) is the external service, not the screen.
        if ('error' === message.type() && !message.text().includes('Google Maps JavaScript API error')) {
            problems.push(`console: ${message.text()}`);
        }
    });
    page.on('requestfailed', (request) => {
        if ((request.failure()?.errorText || '').includes('ERR_ABORTED') && /wp-json|rest_route/.test(request.url())) {
            aborted++;
        }
    });
    page.on('response', (response) => {
        const url = response.url();
        if (response.status() >= 400 && /\/wp-content\/plugins\/(wp-user-frontend|wpuf-pro|wp-user-frontend-pro)\//.test(url)) {
            problems.push(`${response.status()}: ${url}`);
        }
    });

    await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
});

test.afterAll(async () => {
    await browser.close();
});

/** Open an admin page, wait for it to settle and fail on any collected problem. */
async function visit(path: string) {
    problems.length = 0;
    aborted = 0;
    await page.goto(`${Urls.baseUrl}/wp-admin/${path}`);
    await page.waitForLoadState('networkidle');
}

async function expectClean(label: string) {
    const real = aborted ? problems.filter((problem) => !problem.includes('fetch_error')) : problems;
    expect(real, `${label}: JS errors / failed plugin assets`).toEqual([]);

    const loaded = await page.evaluate(() => ({
        vue: 'undefined' !== typeof (window as unknown as { Vue?: unknown }).Vue || !!document.querySelector('[data-v-app]'),
        urls: [
            ...Array.from(document.querySelectorAll('script[src]')).map((el) => (el as HTMLScriptElement).src),
            ...Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map((el) => (el as HTMLLinkElement).href),
        ],
    }));
    expect(loaded.vue, `${label}: Vue runtime or app on a React screen`).toBe(false);
    const vueAssets = loaded.urls.filter((url) => VUE_ASSETS.some((pattern) => pattern.test(url.split('?')[0])));
    expect(vueAssets, `${label}: old Vue admin assets on a React screen`).toEqual([]);
}

test.describe('Release smoke: React admin screens', () => {
    test('RS0001 : Post forms list mounts with no JS error or missing plugin asset', { tag: ['@Lite', '@Test_RS0001'] }, async () => {
        await visit('admin.php?page=wpuf-post-forms');
        await expect(page.locator('button:has-text("AI Form Builder")').first()).toBeVisible({ timeout: 30000 });
        await expectClean('post forms list');
    });

    test('RS0002 : New post form opens the React builder', { tag: ['@Lite', '@Test_RS0002'] }, async () => {
        await visit('admin.php?page=wpuf-post-forms&action=add-new');
        await expect(page).toHaveURL(BUILDER_URL);
        await expect(page.locator('body.wpuf-admin-screen')).toHaveCount(1);
        await expect(page.getByRole('tab', { name: 'Form Editor' }).first()).toBeVisible({ timeout: 30000 });
        await expectClean('post form builder');
        // The new form route creates an empty "Sample Form" (develop's add-new did too): delete it, or
        // later specs that open "Sample Form" by name (SB0037) find this empty one first.
        const created = builderFormId(page.url());
        if (created) aiWp(['post', 'delete', created, '--force']);
    });

    test('RS0003 : Subscriptions screen mounts', { tag: ['@Lite', '@Test_RS0003'] }, async () => {
        await visit('admin.php?page=wpuf_subscription');
        await expect(page.locator('body.wpuf-admin-screen')).toHaveCount(1);
        await expect(page.getByText('Subscriptions', { exact: false }).first()).toBeVisible({ timeout: 30000 });
        await expectClean('subscriptions');
    });

    test('RS0004 : Settings screen mounts', { tag: ['@Lite', '@Test_RS0004'] }, async () => {
        await visit('admin.php?page=wpuf-settings');
        await expect(page.locator('#wpuf-settings-root h2', { hasText: /WP User Frontend/ }).first()).toBeVisible({ timeout: 30000 });
        await expectClean('settings');
    });

    test('RS0005 : Registration forms list and a new registration form open in React (Pro)', { tag: ['@Pro', '@Test_RS0005'] }, async () => {
        test.skip(!new AiFormBuilderPage(page).proActive(), 'registration forms need Pro');
        await visit('admin.php?page=wpuf-profile-forms');
        await expect(page.locator('button:has-text("AI Form Builder")').first()).toBeVisible({ timeout: 30000 });
        await expectClean('registration forms list');

        await visit('admin.php?page=wpuf-profile-forms&action=add-new');
        await expect(page).toHaveURL(BUILDER_URL);
        await expect(page.getByRole('tab', { name: 'Form Editor' }).first()).toBeVisible({ timeout: 30000 });
        await expectClean('registration form builder');
        const created = builderFormId(page.url());
        if (created) aiWp(['post', 'delete', created, '--force']);
    });

    test('RS0006 : AI form builder page mounts', { tag: ['@Lite', '@Test_RS0006'] }, async () => {
        const ai = new AiFormBuilderPage(page);
        ai.configureMock();

        try {
            await visit('admin.php?page=wpuf-post-forms');
            await page.locator('button:has-text("AI Form Builder")').first().click();
            await expect(page.locator('h2:has-text("Create Form with AI")')).toBeVisible({ timeout: 30000 });
            await page.waitForLoadState('networkidle');
            await expectClean('AI form builder');
        } finally {
            ai.restore();
        }
    });

    test('RS0007 : Admin app: menu rows open their screens without a page load', { tag: ['@Lite', '@Test_RS0007'] }, async () => {
        await visit('admin.php?page=wp-user-frontend#/post-forms');
        test.skip(!(await page.evaluate(() => !!(window as unknown as { wpuf?: { app?: unknown } }).wpuf?.app)), 'admin app is off');
        await expect(page.locator('#wpuf-post-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });

        const rows = [
            { menu: 'Registration Forms', ready: '#wpuf-profile-forms-list-table-view', pro: true },
            { menu: 'Subscriptions', ready: '#wpuf-subscription-page button' },
            { menu: 'Settings', ready: '#wpuf-settings-root nav button' },
            { menu: 'Post Forms', ready: '#wpuf-post-forms-list-table-view table' },
        ];

        for (const row of rows) {
            const link = page.locator('#toplevel_page_wp-user-frontend a', { hasText: row.menu }).first();

            if (row.pro && !((await link.getAttribute('href')) || '').includes('#/')) {
                continue; // registration forms need Pro
            }

            await link.click();
            await expect(page.locator(row.ready).first(), `${row.menu} opens`).toBeVisible({ timeout: 30000 });
        }

        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'no page load across menu rows').toBe(true);
        await expectClean('admin app menu rows');
    });

    test('RS0008 : Admin app: a reload on each route opens that screen', { tag: ['@Lite', '@Test_RS0008'] }, async () => {
        await visit('admin.php?page=wp-user-frontend#/post-forms');
        test.skip(!(await page.evaluate(() => !!(window as unknown as { wpuf?: { app?: unknown } }).wpuf?.app)), 'admin app is off');
        await expect(page.locator('#wpuf-post-forms-list-table-view td a').first()).toBeVisible({ timeout: 30000 });
        const edit = await page.locator('#wpuf-post-forms-list-table-view td a').first().getAttribute('href');
        const pro = await page.evaluate(() => !!(window as unknown as { wpufAdmin: { app: { routes: { id: string; mode: string }[] } } }).wpufAdmin.app.routes.find((r) => 'registration-forms' === r.id && 'app' === r.mode));
        const ai = new AiFormBuilderPage(page);
        ai.configureMock();

        const routes = [
            { hash: '#/post-forms', ready: '#wpuf-post-forms-list-table-view table' },
            { hash: (edit || '').replace(/^.*(#\/post-forms\/\d+\/edit).*$/, '$1'), ready: '#wpuf-form-builder input[name="post_title"]' },
            { hash: '#/post-forms/ai', ready: 'h2:has-text("Create Form with AI")' },
            { hash: '#/subscriptions', ready: '#wpuf-subscription-page button' },
            { hash: '#/settings?tab=general', ready: '#wpuf-settings-root nav button' },
            ...(pro ? [{ hash: '#/registration-forms', ready: '#wpuf-profile-forms-list-table-view' }] : []),
        ];

        try {
            for (const route of routes) {
                expect(route.hash, 'route hash').toMatch(/^#\//);
                await visit(`admin.php?page=wp-user-frontend${route.hash}`);
                await page.reload();
                await page.waitForLoadState('networkidle');
                await expect(page.locator(route.ready).first(), `${route.hash} after a reload`).toBeVisible({ timeout: 30000 });
                await expectClean(`route ${route.hash}`);
            }
        } finally {
            ai.restore();
        }
    });
});
