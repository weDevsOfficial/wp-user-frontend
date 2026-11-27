import { existsSync, mkdirSync, writeFileSync } from 'fs';
import { join } from 'path';
import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { faker } from '@faker-js/faker';
import { BasicLoginPage } from '../pages/basicLogin';
import { SettingsScreenPage } from '../pages/settingsScreen';
import { aiWp, AiFormBuilderPage } from '../pages/aiFormBuilder';

/** WP-CLI reachable: a local WordPress root (WPUF_E2E_WP_PATH) or a wp-env project (WPUF_E2E_WP_ENV_DIR, e.g. the QA sites). */
// WP-CLI reaches the site through a local WordPress path, a wp-env project dir, or the
// wp-env project in this directory (CI and the worktree run): without this last case
// the WP-CLI tests skipped silently under wp-env.
const HAS_WP_CLI = !!(process.env.WPUF_E2E_WP_PATH || process.env.WPUF_E2E_WP_ENV_DIR || existsSync(join(process.cwd(), '.wp-env.json')));
import { Users, Urls } from '../utils/testData';
import { builderFormId } from '../utils/builderUrl';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let settings: SettingsScreenPage;

const APP = `${Urls.baseUrl}/wp-admin/admin.php?page=wp-user-frontend`;

/**
 * The single React admin app (task 5d): one admin page, hash routes. These
 * tests check what only the app does (menu links, redirects of old URLs,
 * route guards); the screens' own specs run inside the app too.
 *
 * @TestScenario : [Admin app]
 * @Test_APP0001 : The Settings menu row opens the app's settings route
 * @Test_APP0002 : Old settings URLs (?tab=&sub=, #section) land on that tab inside the app
 * @Test_APP0003 : There is no classic settings screen: ?wpuf_settings_ui=legacy opens the app's settings route
 * @Test_APP0004 : Unsaved settings ask before leaving the route; Continue stays, Discard leaves
 * @Test_APP0005 : Settings <-> Subscriptions through the menu without a page load; each route has only its own stylesheet on
 * @Test_APP0006 : Subscriptions list -> new form -> back / forward inside the route; a deep link reload opens the form
 * @Test_APP0007 : Post forms list <-> registration forms list (Pro) through the menu without a page load, each list with its own data
 * @Test_APP0008 : A list row action (trash) runs on the server and comes back to the list in the app with its notice
 * @Test_APP0009 : Post list -> builder -> list without a page load; the builder's stylesheet and body class only on its route
 * @Test_APP0010 : One builder after another: no stale form data, rootInit once per opened builder, old builder URL lands on the route
 * @Test_APP0011 : Unsaved builder changes ask before leaving the route; Continue stays, Discard leaves; save works in the app
 * @Test_APP0012 : The new form route creates a form and opens its builder; registration builder (Pro) opens in the app
 * @Test_APP0013 : Add New in the app: the template picker's Blank Form and a template each create a form and open its builder in the app without a page load
 * @Test_APP0014 : AI form builder in the app: list button -> AI route -> generate -> Edit with Builder -> builder route, no page load; old AI URLs (with nonce) land on the route
 * @Test_APP0015 : List row action buttons in the app: Edit (builder route), Duplicate (confirm, toast View form), Trash (confirm), Restore, Delete Permanently (confirm), bulk Move to trash (confirm), all without a page load, counts follow; registration Edit (Pro)
 * @Test_APP0016 : Pro without a valid license: Registration Forms is the license-required card in the app (no list, no builder, Activate License goes to the license route); the free routes still run
 * @Test_APP0017 : License (Pro) is the app route #/license: old URL lands there, the key is masked (never in the page), a site without an active key gets the key form
 * @Test_APP0018 : Without Pro, Registration Forms is the app route #/registration-forms on the new components (free shortcode with Copy, Pro features, modules icons) and shows admin notices
 * @Test_APP0019 : Help, Tools, Transactions and Coupons: old URLs land on their app routes with the menu row lit, and those routes show admin notices as develop's pages did
 * @Test_APP0020 : Post forms list Submissions count opens the form's Submissions page (status tabs + counts, WP columns, search, empty state, reload, back)
 * @Test_APP0021 : Builder seam "+": between two fields and after the last one it opens the field list and adds the picked field at that position
 * @Test_APP0022 : Column cell "+": adds a field into that column; the list leaves out types a column refuses
 * @Test_APP0024 : Post form builder Submissions tab; Settings / Form Editor tabs from the Submissions page; none on registration forms
 * @Test_APP0026 : Forms list search with no match shows the empty state; clearing it brings the list back
 * @Test_APP0027 : Builder refuses a second single-instance field; Remove asks first, Cancel keeps the field
 * @Test_APP0028 : Tools import refuses a file with a script block and imports a clean export
 * @Test_APP0025 : Pro modules switch on / off through the React page; without a valid license the license-required card shows and REST refuses switch-on
 * @Test_APP0023 : A column stored the develop way (inner fields without ids, as after an update) opens each inner field for editing
 */

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext({ ignoreHTTPSErrors: true });
    page = await context.newPage();
    await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
    settings = new SettingsScreenPage(page);
});

test.afterAll(async () => {
    await browser.close();
});

/** Whether Pro's registration list is an app route (without Pro the route is the free page). */
async function proRegistrationList(): Promise<boolean> {
    return page.evaluate(() => !!((window as unknown as { wpufAdmin?: { app?: { routes: { id: string; app: string; mode: string }[] } } }).wpufAdmin?.app?.routes || []).find((r) => 'registration-forms' === r.id && 'forms-list' === r.app && 'app' === r.mode));
}

/** Whether the admin app is on (its page carries the app boot data). */
async function appOn(): Promise<boolean> {
    await page.goto(`${APP}#/settings`);
    await page.waitForLoadState('domcontentloaded');

    return page.evaluate(() => !!(window as unknown as { wpuf?: { app?: unknown } }).wpuf?.app);
}

test.describe('Admin app', () => {
    test('APP0001 : The Settings menu row opens the app settings route', { tag: ['@Lite', '@Test_APP0001'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const href = await page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Settings' }).first().getAttribute('href');
        expect(href).toContain('page=wp-user-frontend#/settings');
        await expect(page).toHaveURL(/page=wp-user-frontend#\/settings/);
        await expect(page.locator('#wpuf-settings-root h2', { hasText: /WP User Frontend/ }).first()).toBeVisible({ timeout: 30000 });
    });

    test('APP0002 : Old settings URLs land on that tab inside the app', { tag: ['@Lite', '@Test_APP0002'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-settings&tab=payments&sub=wpuf_payment`);
        await expect(page).toHaveURL(/page=wp-user-frontend#\/settings\?tab=payments&sub=wpuf_payment$/);

        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-settings#wpuf_profile`);
        await expect(page).toHaveURL(/page=wp-user-frontend#\/settings\?tab=login_registration&sub=wpuf_profile$/);
    });

    test('APP0003 : The old classic settings URL opens the React settings route', { tag: ['@Lite', '@Test_APP0003'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-settings&wpuf_settings_ui=legacy`);
        await expect(page).toHaveURL(/page=wp-user-frontend#\/settings/);
        await expect(page.locator('.wpuf-settings-wrap')).toHaveCount(0);
        await expect(page.locator('a:text-is("Classic view")')).toHaveCount(0);
    });

    test('APP0004 : Unsaved settings ask before leaving the route', { tag: ['@Lite', '@Test_APP0004'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        await page.goto(`${APP}#/settings?tab=general`);
        await settings.root.waitFor({ state: 'visible' });
        await settings.openTab('General');
        await settings.fillByLabel('Custom CSS codes', `/* ${faker.string.uuid()} */`);
        await expect(page.getByText('Unsaved changes', { exact: false })).toBeVisible();

        // Leave to another route: the screen asks, Continue keeps the route.
        await page.evaluate(() => { window.location.hash = '#/nowhere'; });
        await settings.expectUnsavedModal();
        await settings.continueEditing();
        await expect(page).toHaveURL(/#\/settings/);
        await expect(page.getByText('Unsaved changes', { exact: false })).toBeVisible();

        // Discard leaves.
        await page.evaluate(() => { window.location.hash = '#/nowhere'; });
        await settings.expectUnsavedModal();
        await settings.discardChanges();
        await expect(page).toHaveURL(/#\/nowhere/);
        await expect(page.getByText('This page does not exist.')).toBeVisible();
    });

    test('APP0005 : Settings <-> Subscriptions through the menu without a page load', { tag: ['@Lite', '@Test_APP0005'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const sheetOn = (id: string) => page.evaluate((sheet) => {
            const link = document.getElementById(sheet) as HTMLLinkElement | null;
            return !!link && !link.disabled;
        }, id);

        await page.goto(`${APP}#/settings`);
        await expect(page.locator('#wpuf-settings-root nav button').first()).toBeVisible({ timeout: 30000 });
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
        expect(await sheetOn('wpuf-settings-screen-css')).toBe(true);

        await page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Subscriptions' }).first().click();
        await expect(page).toHaveURL(/#\/subscriptions/);
        await expect(page.locator('#wpuf-subscription-page button', { hasText: 'Add Subscription' }).first()).toBeVisible({ timeout: 30000 });
        expect(await sheetOn('wpuf-subscriptions-screen-css')).toBe(true);
        expect(await sheetOn('wpuf-settings-screen-css')).toBe(false);
        await expect(page.locator('#toplevel_page_wp-user-frontend li.current a')).toHaveText('Subscriptions');

        await page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Settings' }).first().click();
        await expect(page.locator('#wpuf-settings-root nav button').first()).toBeVisible({ timeout: 30000 });
        expect(await sheetOn('wpuf-settings-screen-css')).toBe(true);
        expect(await sheetOn('wpuf-subscriptions-screen-css')).toBe(false);
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'no page load between routes').toBe(true);
    });

    test('APP0006 : Subscriptions list -> new form -> back / forward inside the route', { tag: ['@Lite', '@Test_APP0006'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const details = page.locator('//button[normalize-space()="Subscription Details"]');

        await page.goto(`${APP}#/subscriptions`);
        await page.locator('#wpuf-subscription-page button', { hasText: 'Add Subscription' }).first().click();
        await expect(page).toHaveURL(/#\/subscriptions\?action=new/);
        await expect(details).toBeVisible({ timeout: 30000 });

        await page.goBack();
        await expect(page).toHaveURL(/#\/subscriptions$/);
        await expect(details).toHaveCount(0);

        await page.goForward();
        await expect(details).toBeVisible({ timeout: 30000 });

        await page.reload();
        await expect(page).toHaveURL(/#\/subscriptions\?action=new/);
        await expect(details).toBeVisible({ timeout: 30000 });
    });

    test('APP0007 : Post forms list <-> registration forms list through the menu without a page load', { tag: ['@Pro', '@Test_APP0007'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const listType = () => page.evaluate(() => ((window as unknown as { wpuf_forms_list?: { post_type?: string } }).wpuf_forms_list || {}).post_type || 'wpuf_forms');

        await page.goto(`${APP}#/post-forms`);
        await expect(page.locator('#wpuf-post-forms-list-table-view h3', { hasText: 'Post Forms' }).first()).toBeVisible({ timeout: 30000 });
        await expect(page.locator('button:has-text("AI Form Builder")').first()).toBeVisible();
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
        expect(await listType()).toBe('wpuf_forms');

        const registration = page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Registration Forms' }).first();
        test.skip(!(await proRegistrationList()), 'registration forms need Pro');

        await registration.click();
        await expect(page.locator('#wpuf-profile-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
        await expect(page.locator('#wpuf-profile-forms-list-table-view th', { hasText: /User Role/i }).first()).toBeVisible({ timeout: 30000 });
        expect(await listType()).toBe('wpuf_profile');

        await page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Post Forms' }).first().click();
        await expect(page.locator('#wpuf-post-forms-list-table-view th', { hasText: /Post Type/i }).first()).toBeVisible({ timeout: 30000 });
        expect(await listType()).toBe('wpuf_forms');
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'no page load between the lists').toBe(true);
    });

    /** Builder state from the page: form name, field count, store form id. */
    const builderState = () => page.evaluate(() => {
        const w = window as unknown as { wp: { data: { select: ( name: string ) => { getPost: () => { ID: number; post_title: string }; getFormFields: () => unknown[]; getIsDirty: () => boolean } } }; wpuf: { storeName: string } };
        const store = w.wp.data.select( w.wpuf.storeName );

        return { id: store.getPost().ID, title: store.getPost().post_title, fields: store.getFormFields().length, dirty: store.getIsDirty() };
    });
    const builderReady = async (title: string) => {
        await expect(page.locator('#wpuf-form-builder #wpuf-form-builder-app input[name="post_title"]').first()).toHaveValue(title, { timeout: 30000 });
    };
    /** Rename in the builder header (the save posts the header input) and mark the form changed. */
    const renameForm = async (title: string) => {
        const name = page.locator('#wpuf-form-builder input[name="post_title"]').first();
        await name.click();
        await name.fill(title);
        await name.press('Enter');
        await page.evaluate(() => {
            const w = window as unknown as { wp: { data: { dispatch: ( n: string ) => { markDirty: () => void } } }; wpuf: { storeName: string } };
            w.wp.data.dispatch( w.wpuf.storeName ).markDirty();
        });
    };
    const newForm = (title: string, type = 'wpuf_forms') => Number(aiWp(['eval', `echo wpuf_create_sample_form( '${title}', '${type}' );`], true).trim().split('\n').pop());

    test('APP0009 : Post list -> builder -> list without a page load', { tag: ['@Lite', '@Test_APP0009'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0009 builder');
        const sheetOn = (sheet: string) => page.evaluate((s) => {
            const link = document.getElementById(s) as HTMLLinkElement | null;
            return !!link && !link.disabled;
        }, sheet);

        await page.goto(`${APP}#/post-forms`);
        await expect(page.locator('#wpuf-post-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });

        await page.evaluate((formId) => { window.location.hash = `#/post-forms/${formId}/edit`; }, id);
        await builderReady('APP0009 builder');
        expect(await page.evaluate(() => document.body.classList.contains('wpuf-builder-screen'))).toBe(true);
        expect(await sheetOn('wpuf-admin-form-builder-css'), 'builder sheet on').toBe(true);
        expect(await sheetOn('wpuf-forms-list-css'), 'list sheet off').toBe(false);
        await expect(page.locator('#toplevel_page_wp-user-frontend li.current a')).toHaveText('Post Forms');
        await expect(page.locator('input[name="wpuf_form_id"]')).toHaveValue(String(id));
        await expect(page.locator('input[name="wpuf_form_builder_nonce"]')).toHaveCount(1);

        // The builder folds the WordPress menu: its rows show in a flyout on hover. The flyout
        // timing is the browser's, so the click goes to the menu link itself (same handler).
        const postFormsLink = page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Post Forms' }).first();
        await page.locator('#toplevel_page_wp-user-frontend').hover();
        await postFormsLink.dispatchEvent('click');
        await expect(page.locator('#wpuf-post-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
        expect(await page.evaluate(() => document.body.classList.contains('wpuf-builder-screen'))).toBe(false);
        expect(await sheetOn('wpuf-forms-list-css'), 'list sheet back on').toBe(true);
        expect(await sheetOn('wpuf-admin-form-builder-css'), 'builder sheet off').toBe(false);
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'no page load').toBe(true);
        aiWp(['post', 'delete', String(id), '--force']);
    });

    test('APP0010 : One builder after another without stale data', { tag: ['@Lite', '@Test_APP0010'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const first = newForm('APP0010 first');
        const second = Number(aiWp(['eval', "echo wpuf_create_sample_form( 'APP0010 second', 'wpuf_forms', true );"], true).trim().split('\n').pop());

        // Old builder URL: the load step runs, then the app route.
        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-post-forms&action=edit&id=${first}`);
        await expect(page).toHaveURL(new RegExp(`page=wp-user-frontend#/post-forms/${first}/edit`));
        await builderReady('APP0010 first');
        await page.evaluate(() => {
            const w = window as unknown as { wp: { hooks: { addAction: ( h: string, n: string, cb: () => void ) => void } }; rootInits: number };
            w.rootInits = 0;
            w.wp.hooks.addAction( 'wpuf.formBuilder.rootInit', 'e2e/count', () => { w.rootInits++; } );
        });
        const a = await builderState();
        expect(a.id).toBe(first);

        await page.evaluate((formId) => { window.location.hash = `#/post-forms/${formId}/edit`; }, second);
        await builderReady('APP0010 second');
        const b = await builderState();
        expect(b.id).toBe(second);
        expect(b.fields, 'blank form has its own (fewer) fields').toBeLessThan(a.fields);
        expect(b.dirty).toBe(false);
        expect(await page.evaluate(() => (window as unknown as { rootInits: number }).rootInits), 'rootInit once per opened builder').toBe(1);
        await expect(page.locator('input[name="wpuf_form_id"]')).toHaveValue(String(second));
        await expect(page.locator('#wpuf-form-builder')).toHaveCount(1);

        aiWp(['post', 'delete', String(first), String(second), '--force']);
    });

    test('APP0011 : Unsaved builder changes ask before leaving; save works in the app', { tag: ['@Lite', '@Test_APP0011'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0011 guard');

        await page.goto(`${APP}#/post-forms/${id}/edit`);
        await builderReady('APP0011 guard');
        await renameForm('APP0011 renamed');
        expect((await builderState()).dirty).toBe(true);

        await page.evaluate(() => { window.location.hash = '#/post-forms'; });
        await expect(page.getByRole('heading', { name: 'Unsaved Changes' })).toBeVisible();
        await page.getByRole('button', { name: 'Continue Editing' }).click();
        await expect(page).toHaveURL(new RegExp(`#/post-forms/${id}/edit`));
        await expect(page.locator('#wpuf-form-builder')).toHaveCount(1);

        // Save in the app: the REST save reads the route's hidden inputs.
        const saved = page.waitForResponse((r) => r.url().includes(`admin/forms/${id}`) && 'POST' === r.request().method());
        await page.locator('//button[normalize-space(text())="Save"]').first().click();
        expect((await saved).status()).toBe(200);
        await expect.poll(() => aiWp(['post', 'get', String(id), '--field=post_title']).trim()).toBe('APP0011 renamed');

        await renameForm('APP0011 unsaved');
        await page.evaluate(() => { window.location.hash = '#/post-forms'; });
        await page.getByRole('button', { name: 'Discard Changes' }).click();
        await expect(page.locator('#wpuf-post-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
        aiWp(['post', 'delete', String(id), '--force']);
    });

    test('APP0012 : New form route creates a form; registration builder opens in the app', { tag: ['@Lite', '@Test_APP0012'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        await page.goto(`${APP}#/post-forms/new`);
        await expect(page).toHaveURL(/#\/post-forms\/\d+\/edit$/, { timeout: 30000 });
        await builderReady('Sample Form');
        const created = (await builderState()).id;
        expect(aiWp(['post', 'get', String(created), '--field=post_type']).trim()).toBe('wpuf_forms');
        aiWp(['post', 'delete', String(created), '--force']);

        const registration = await page.evaluate(() => !!((window as unknown as { wpufAdmin: { app: { routes: { id: string; mode: string }[] } } }).wpufAdmin.app.routes.find((r) => 'registration-form-edit' === r.id && 'app' === r.mode)));
        test.skip(!registration, 'registration builder needs Pro');

        const reg = newForm('APP0012 registration', 'wpuf_profile');
        await page.evaluate((formId) => { window.location.hash = `#/registration-forms/${formId}/edit`; }, reg);
        await builderReady('APP0012 registration');
        expect((await builderState()).id).toBe(reg);
        await expect(page.locator('#toplevel_page_wp-user-frontend li.current a')).toHaveText('Registration Forms');
        await expect(page.locator('#wpuf-form-builder')).toHaveClass(/wpuf-form-builder-profile/);
        aiWp(['post', 'delete', String(reg), '--force']);
    });

    test('APP0013 : Add New: blank form and a template open their builder in the app', { tag: ['@Lite', '@Test_APP0013'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const screens = [
            { list: '#/post-forms', template: 'post_form_template_post', postType: 'wpuf_forms', route: 'post-forms' },
            { list: '#/registration-forms', template: 'simple_user_signup_template', postType: 'wpuf_profile', route: 'registration-forms' },
        ];

        for (const screen of screens) {
            await page.goto(`${APP}${screen.list}`);
            const add = page.locator('.new-wpuf-form').first();

            if ('wpuf_profile' === screen.postType && !(await add.isVisible({ timeout: 15000 }).catch(() => false))) {
                continue; // registration forms need Pro
            }

            const picker = page.locator('.wpuf-template-picker');

            // Blank Form: the new form route, no page load.
            await add.click();
            await expect(picker).toBeVisible();
            await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
            await picker.locator('[data-template="blank"] a').first().click({ force: true });
            await expect(page).toHaveURL(new RegExp(`#/${screen.route}/\\d+/edit$`), { timeout: 30000 });
            await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toBeVisible({ timeout: 30000 });
            expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'blank form without a page load').toBe(true);
            const blank = (await builderState()).id;

            // A template: created through the admin forms REST route, then its builder in the app (no page load).
            await page.goto(`${APP}${screen.list}`);
            await add.click();
            await expect(picker).toBeVisible();
            await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
            const created = page.waitForResponse((r) => r.request().method() === 'POST' && /admin(\/|%2F)forms(\?|$)/.test(r.url()));
            await picker.locator(`[data-template="${screen.template}"] a`).first().click();
            expect((await created).status(), 'template form created by REST').toBe(201);
            await expect(page).toHaveURL(new RegExp(`page=wp-user-frontend#/${screen.route}/\\d+/edit$`), { timeout: 30000 });
            await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toBeVisible({ timeout: 30000 });
            expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'template form without a page load').toBe(true);
            const state = await builderState();
            expect(state.id).not.toBe(blank);
            expect(state.fields, 'the template brought its fields').toBeGreaterThan(0);

            if (HAS_WP_CLI) {
                expect(aiWp(['post', 'get', String(state.id), '--field=post_type']).trim()).toBe(screen.postType);
                aiWp(['post', 'delete', String(blank), String(state.id), '--force']);
            }
        }
    });

    test('APP0014 : AI form builder runs in the app', { tag: ['@Lite', '@Test_APP0014'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site (AI mock key)');

        const ai = new AiFormBuilderPage(page);
        ai.configureMock();

        try {
            for (const list of ['post', 'profile'] as const) {
                const route = 'profile' === list ? 'registration-forms' : 'post-forms';

                // A fresh load: a hash-only goto keeps the boot data read before the mock key was set.
                await page.goto('about:blank');
                await page.goto(`${APP}#/${route}`);
                const button = page.locator(ai.S.listButton).first();

                if ('profile' === list && !(await button.isVisible({ timeout: 15000 }).catch(() => false))) {
                    continue; // registration forms need Pro
                }

                await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
                await button.click();
                await expect(page).toHaveURL(new RegExp(`page=wp-user-frontend#/${route}/ai`));
                await expect(page.locator(ai.S.inputHeading)).toBeVisible({ timeout: 30000 });
                expect(await page.evaluate(() => document.body.classList.contains('wpuf-ai-form-builder-page'))).toBe(true);
                expect(await page.evaluate(() => (window as unknown as { wpufAIFormBuilder: { formType: string } }).wpufAIFormBuilder.formType)).toBe(list);

                await ai.generate('profile' === list ? 'Create a sign up form' : 'Create a contact form');
                await page.locator(ai.S.editInBuilderButton).click();
                await expect(page).toHaveURL(new RegExp(`#/${route}/\\d+/edit$`), { timeout: 30000 });
                await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toBeVisible({ timeout: 30000 });
                expect(await page.evaluate(() => document.body.classList.contains('wpuf-ai-form-builder-page')), 'AI body class gone on the builder').toBe(false);
                expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'AI -> builder without a page load').toBe(true);
                const id = (await builderState()).id;
                expect(aiWp(['post', 'get', String(id), '--field=post_type']).trim()).toBe('profile' === list ? 'wpuf_profile' : 'wpuf_forms');
                aiWp(['post', 'delete', String(id), '--force']);
            }

            // Old AI URLs keep their nonce check, then land on the route with their stage.
            await page.goto(`${APP}#/post-forms`);
            await expect(page.locator('#wpuf-post-forms-list-table-view').first()).toBeVisible({ timeout: 30000 });
            const nonce = await page.evaluate(() => (window as unknown as { wpuf_forms_list: { template_nonce: string } }).wpuf_forms_list.template_nonce);
            await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?action=post_form_template&template=ai_form&_wpnonce=${nonce}&description=Old%20link`);
            await expect(page).toHaveURL(/page=wp-user-frontend#\/post-forms\/ai\?description=Old(%20|\+)link$/);
            await expect(page.locator(ai.S.inputHeading)).toBeVisible({ timeout: 30000 });
            await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?action=wpuf_ai_form_success&_wpnonce=bad`);
            await expect(page.locator('body')).toContainText('Security check failed');
        } finally {
            ai.restore();
        }
    });

    test('APP0015 : List row action buttons work in the app', { tag: ['@Lite', '@Test_APP0015'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const title = `APP0015 ${Date.now()}`;
        const id = newForm(title);
        const list = page.locator('#wpuf-post-forms-list-table-view');
        const rowAction = async (formTitle: string, item: string) => {
            await page.locator(`button[aria-label="Actions for ${formTitle}"]`).first().click();
            await page.getByRole('menuitem', { name: item, exact: true }).click();
        };
        const openList = async (hash = '#/post-forms') => {
            await page.goto(`${APP}${hash}`);
            await expect(list.locator('table').first()).toBeVisible({ timeout: 30000 });
        };

        // Edit: the builder route, no page load.
        await openList();
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
        await rowAction(title, 'Edit');
        await expect(page).toHaveURL(new RegExp(`#/post-forms/${id}/edit$`));
        await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toHaveValue(title, { timeout: 30000 });
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'Edit without a page load').toBe(true);

        // Row and bulk actions run over REST in place (no page load), develop's notice texts as toasts.
        const toast = page.locator('li[data-sonner-toast]');
        await openList();
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });

        // Duplicate: toast with a "View form" action to the copy's builder route.
        await rowAction(title, 'Duplicate');
        // Duplicate asks first.
        await page.getByRole('alertdialog').getByRole('button', { name: 'Duplicate' }).click();
        await expect(toast.filter({ hasText: 'Form duplicated successfully.' }).first()).toBeVisible({ timeout: 30000 });
        const viewForm = toast.filter({ hasText: 'Form duplicated successfully.' }).first().getByRole('button', { name: 'View form' });
        await viewForm.click();
        await expect(page).toHaveURL(/#\/post-forms\/\d+\/edit$/);
        const copy = Number(builderFormId(page.url()));
        expect(copy, 'the copy exists').toBeGreaterThan(id);
        const copyTitle = aiWp(['post', 'get', String(copy), '--field=post_title']).trim();
        await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toHaveValue(copyTitle, { timeout: 30000 });
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'Duplicate and View form without a page load').toBe(true);

        // Trash the copy, restore it from the Trash tab, then delete it permanently: counts follow.
        await openList();
        await page.evaluate(() => { (window as unknown as { wpufNoReload: boolean }).wpufNoReload = true; });
        const trashTab = page.getByRole('tab', { name: /Trash/ }).first();
        const trashCount = async () => Number(((await trashTab.innerText()).match(/\d+/) || ['0'])[0]);
        const before = await trashCount();
        await rowAction(copyTitle, 'Trash');
        await page.getByRole('alertdialog').getByRole('button', { name: 'Move to Trash' }).click();
        await expect(toast.filter({ hasText: '1 form moved to the trash.' }).first()).toBeVisible({ timeout: 30000 });
        await expect.poll(trashCount, { message: 'the Trash tab count follows without a reload' }).toBe(before + 1);
        expect(aiWp(['post', 'get', String(copy), '--field=post_status']).trim()).toBe('trash');

        await list.getByText(/^Trash/).first().click();
        await expect(page.locator(`button[aria-label="Actions for ${copyTitle}"]`).first()).toBeVisible({ timeout: 30000 });
        await rowAction(copyTitle, 'Restore');
        await expect(toast.filter({ hasText: '1 form restored from the trash.' }).first()).toBeVisible({ timeout: 30000 });

        await list.getByText(/^All/).first().click();
        await expect(page.locator(`button[aria-label="Actions for ${copyTitle}"]`).first()).toBeVisible({ timeout: 30000 });
        await rowAction(copyTitle, 'Trash');
        await page.getByRole('alertdialog').getByRole('button', { name: 'Move to Trash' }).click();
        await expect(toast.filter({ hasText: '1 form moved to the trash.' }).last()).toBeVisible({ timeout: 30000 });
        await list.getByText(/^Trash/).first().click();
        await expect(page.locator(`button[aria-label="Actions for ${copyTitle}"]`).first()).toBeVisible({ timeout: 30000 });
        await rowAction(copyTitle, 'Delete Permanently');
        await page.getByRole('alertdialog').getByRole('button', { name: 'Delete Permanently' }).click();
        await expect(toast.filter({ hasText: '1 form permanently deleted.' }).first()).toBeVisible({ timeout: 30000 });

        // Bulk: select the remaining form, Move to trash.
        await list.getByText(/^All/).first().click();
        await page.getByRole('checkbox', { name: `Select ${title}` }).first().check();
        await page.getByRole('button', { name: 'Move to trash' }).first().click();
        await page.getByRole('alertdialog').getByRole('button', { name: 'Move to Trash' }).click();
        await expect(toast.filter({ hasText: '1 form moved to the trash.' }).last()).toBeVisible({ timeout: 30000 });
        expect(aiWp(['post', 'get', String(id), '--field=post_status']).trim()).toBe('trash');
        expect(await page.evaluate(() => (window as unknown as { wpufNoReload?: boolean }).wpufNoReload), 'row and bulk actions without a page load').toBe(true);
        aiWp(['post', 'delete', String(id), '--force']); // the copy was deleted permanently above

        // Registration list (Pro): Edit opens its builder route.
        if (await proRegistrationList()) {
            const regTitle = `APP0015 reg ${Date.now()}`;
            const regId = newForm(regTitle, 'wpuf_profile');
            await page.goto(`${APP}#/registration-forms`);
            await expect(page.locator(`button[aria-label="Actions for ${regTitle}"]`).first()).toBeVisible({ timeout: 30000 });
            await rowAction(regTitle, 'Edit');
            await expect(page).toHaveURL(new RegExp(`#/registration-forms/${regId}/edit$`));
            await expect(page.locator('#wpuf-form-builder input[name="post_title"]').first()).toHaveValue(regTitle, { timeout: 30000 });
            aiWp(['post', 'delete', String(regId), '--force']);
        }
    });

    test('APP0016 : Pro without a valid license shows the license-required card for Registration Forms', { tag: ['@Pro', '@Test_APP0016'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const card = page.locator('.wpuf-app-state');
        await page.goto(`${APP}#/post-forms`);
        test.skip(!(await proRegistrationList()), 'needs licensed Pro to start from');

        // The stored license stays in this process; it is never printed.
        const saved = aiWp(['option', 'get', 'wpuf_license', '--format=json']).trim();
        aiWp(['eval', '$l = get_option( "wpuf_license" ); $l["status"] = "deactivate"; update_option( "wpuf_license", $l );']);

        try {
            // A real page load (the same URL with a hash only would be a route change).
            await page.goto(`${APP}#/post-forms`);
            await page.reload();
            await expect(page.locator('#wpuf-post-forms-list-table-view table').first()).toBeVisible({ timeout: 30000 });

            // Registration Forms is the license-required card in the app: never the list.
            const row = page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'Registration Forms' }).first();
            expect(await row.getAttribute('href'), 'menu row opens the app route').toContain('#/registration-forms');
            await page.goto(`${APP}#/registration-forms`);
            await expect(card.getByRole('heading', { name: 'WPUF Pro License Required' })).toBeVisible({ timeout: 30000 });
            await expect(page.locator('#wpuf-profile-forms-list-table-view')).toHaveCount(0);

            // The builder route is not served either.
            await page.goto(`${APP}#/registration-forms/1/edit`);
            await expect(page.locator('#wpuf-form-builder')).toHaveCount(0);

            // The card's action goes to the license page of the app.
            await page.goto(`${APP}#/registration-forms`);
            await card.getByRole('link', { name: 'Activate License' }).click();
            await expect(page).toHaveURL(/#\/license/, { timeout: 30000 });

            // Free routes still run in the app.
            await page.goto(`${APP}#/settings`);
            await expect(page.locator('#wpuf-settings-root nav button').first()).toBeVisible({ timeout: 30000 });
        } finally {
            aiWp(['option', 'update', 'wpuf_license', saved, '--format=json']);
        }

        await page.goto(`${APP}#/post-forms`);
        await page.reload();
        await page.goto(`${APP}#/registration-forms`);
        await expect(page.locator('#wpuf-profile-forms-list-table-view').first(), 'license restored: the list is back').toBeVisible({ timeout: 30000 });
    });

    test('APP0017 : License page in the app (Pro)', { tag: ['@Pro', '@Test_APP0017'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const row = page.locator('#toplevel_page_wp-user-frontend a', { hasText: 'License' }).first();
        test.skip(!(await row.count()) || !((await row.getAttribute('href')) || '').includes('#/license'), 'license route needs Pro');

        // The stored license stays in this process; it is never printed.
        const saved = aiWp(['option', 'get', 'wpuf_license', '--format=json']).trim();
        const key = (JSON.parse(saved) as { key?: string }).key || '';
        const card = page.locator('.wpuf-license-card');
        const serverCalls: string[] = [];
        page.on('request', (request) => { if (/appsero/i.test(request.url())) serverCalls.push(request.url()); });

        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf_updates`);
        await expect(page).toHaveURL(/page=wp-user-frontend#\/license/);
        await expect(card.locator('[data-license-status="active"]')).toBeVisible({ timeout: 30000 });
        await expect(card.locator('[data-license-key-hint]')).toHaveText(new RegExp(`${key.slice(-4)}$`));
        expect(key.length, 'test site has a license').toBeGreaterThan(8);
        expect(await page.content(), 'the key is never in the page').not.toContain(key);

        aiWp(['eval', '$l = get_option( "wpuf_license" ); $l["status"] = "deactivate"; update_option( "wpuf_license", $l );']);

        try {
            await page.reload();
            await expect(card.locator('[data-license-status="inactive"]')).toBeVisible({ timeout: 30000 });
            await expect(card.locator('#wpuf-license-key')).toBeVisible();
            await card.getByRole('button', { name: 'Activate' }).click();
            await expect(card.locator('[data-license-error]')).toHaveText('Enter your license key.');
            expect(serverCalls, 'no license server call for an empty key').toEqual([]);
        } finally {
            aiWp(['option', 'update', 'wpuf_license', saved, '--format=json']);
        }
    });

    test('APP0018 : Registration Forms without Pro in the app', { tag: ['@Lite', '@Test_APP0018'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        const promo = await page.evaluate(() => !!((window as unknown as { wpufAdmin: { app: { routes: { id: string; app: string }[] } } }).wpufAdmin.app.routes.find((r) => 'registration-forms' === r.id && 'registration-promo' === r.app)));
        test.skip(!promo, 'only without Pro');

        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf-profile-forms`);
        await expect(page).toHaveURL(/page=wp-user-frontend#\/registration-forms/);
        await expect(page.locator('[data-registration-shortcode]')).toHaveText('[wpuf-registration]', { timeout: 30000 });
        await expect(page.locator('#toplevel_page_wp-user-frontend li.current a')).toHaveText('Registration Forms');
        await expect(page.locator('.wpuf-registration-pro img[src*="/images/modules/"]')).toHaveCount(6);
        await expect(page.locator('.wpuf-registration-pro').getByRole('button', { name: 'Upgrade to PRO' })).toBeVisible();

        // develop's page printed the admin notices; the route keeps them (QA story 12).
        const notices = await page.evaluate(() => ((window as unknown as { wpufAdmin: { app: { routes: { id: string; notices?: boolean }[] } } }).wpufAdmin.app.routes.find((r) => 'registration-forms' === r.id) || {}).notices);
        expect(notices, 'registration-forms shows admin notices').toBe(true);
    });

    test('APP0019 : Help, Tools, Transactions and Coupons old URLs land in the app with notices on', { tag: ['@Lite', '@Test_APP0019'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        const pages = [
            { old: 'admin.php?page=wpuf-support', route: '/help', id: 'help', menu: 'Help' },
            { old: 'admin.php?page=wpuf_tools&tab=export', route: '/tools?tab=export', id: 'tools', menu: 'Tools' },
            { old: 'admin.php?page=wpuf_transaction', route: '/transactions', id: 'transactions', menu: 'Transactions' },
            { old: 'edit.php?post_type=wpuf_coupon', route: '/coupons', id: 'coupons', menu: 'Coupons' },
        ];

        for ( const item of pages ) {
            await page.goto(`${Urls.baseUrl}/wp-admin/${item.old}`);
            await expect(page).toHaveURL(new RegExp('page=wp-user-frontend#' + item.route.replace(/[?]/g, '\\?') + '$'));
            await expect(page.locator('#toplevel_page_wp-user-frontend li.current a')).toHaveText(item.menu);

            // develop printed admin notices on these pages; the route keeps them.
            const notices = await page.evaluate((id) => ((window as unknown as { wpufAdmin: { app: { routes: { id: string; notices?: boolean }[] } } }).wpufAdmin.app.routes.find((r) => id === r.id) || {}).notices, item.id);
            expect(notices, item.id + ' shows admin notices').toBe(true);
        }
    });

    test('APP0020 : Post forms list Submissions count opens the form\'s Submissions page', { tag: ['@Lite', '@Test_APP0020'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const title = 'APP0020 subs ' + faker.string.alphanumeric(6);
        const id = newForm(title);
        const posts = ['publish', 'pending', 'pending', 'draft'].map((status, i) => {
            const pid = aiWp(['post', 'create', `--post_title=APP0020 post ${status} ${i}`, `--post_status=${status}`, '--porcelain'], true).trim().split('\n').pop() as string;
            aiWp(['post', 'meta', 'add', pid, '_wpuf_form_id', String(id)]);
            return pid;
        });
        // A post of another form never shows.
        const other = aiWp(['post', 'create', '--post_title=APP0020 other form', '--post_status=publish', '--porcelain'], true).trim().split('\n').pop() as string;

        try {
            await page.goto(`${APP}#/post-forms`);
            await page.locator('#wpuf-post-forms-list-table-view input[placeholder="Search Forms"]').first().fill(title);
            const row = page.locator('tr', { hasText: title }).first();
            await expect(row).toBeVisible({ timeout: 30000 });
            await expect(page.locator('thead th', { hasText: /^Submissions$/i })).toHaveCount(1);
            await expect(row.locator('button[title^="View posts submitted"]')).toHaveText('4');
            await expect(row.getByRole('button', { name: '2 pending' })).toBeVisible();

            await row.locator('button[title^="View posts submitted"]').click();
            await expect(page).toHaveURL(new RegExp(`#/post-forms/${id}/submissions`));
            const app = page.locator('#wpuf-admin-app');
            await expect(app.getByRole('tab', { name: 'Submissions', selected: true })).toBeVisible({ timeout: 30000 });
            await expect(app.locator('tbody tr')).toHaveCount(4);
            await expect(app.locator('tbody tr', { hasText: 'APP0020 other form' })).toHaveCount(0);
            for (const [name, count] of [['All', 4], ['Published', 1], ['Pending Review', 2], ['Draft', 1]] as const) {
                await expect(app.getByRole('tab', { name: new RegExp(`^${name}\\s*\\(${count}\\)`) })).toBeVisible();
            }
            // WordPress posts list columns for posts.
            for (const head of ['Title', 'Author', 'Categories', 'Tags', 'Status', 'Date']) {
                await expect(app.locator('thead th', { hasText: new RegExp(`^${head}$`, 'i') })).toHaveCount(1);
            }

            await app.getByRole('tab', { name: /^Pending Review/ }).click();
            await expect(app.locator('tbody tr')).toHaveCount(2);

            await app.locator('input[placeholder="Search submissions"]').fill('no-such-submission-xyz');
            await expect(app.getByText('No submission matches those filters')).toBeVisible();
            await app.getByRole('button', { name: 'Clear filters' }).click();
            await expect(app.locator('tbody tr')).toHaveCount(4);

            // A reload stays on the page; Back returns to the list.
            await page.reload();
            await expect(app.locator('tbody tr')).toHaveCount(4, { timeout: 30000 });
            await app.getByRole('link', { name: 'Back to forms' }).click();
            await expect(page).toHaveURL(/#\/post-forms$/);
        } finally {
            [...posts, other].forEach((pid) => aiWp(['post', 'delete', pid, '--force']));
            aiWp(['post', 'delete', String(id), '--force']);
        }
    });

    test('APP0024 : Post form builder Submissions tab and back to Form Editor / Settings', { tag: ['@Lite', '@Test_APP0024'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0024 tabs ' + faker.string.alphanumeric(6));

        try {
            await page.goto(`${APP}#/post-forms/${id}/edit`);
            await expect(page.getByRole('tab', { name: 'Form Editor' }).first()).toBeVisible({ timeout: 30000 });
            await page.getByRole('tab', { name: 'Submissions' }).click();
            await expect(page).toHaveURL(new RegExp(`#/post-forms/${id}/submissions`));
            await expect(page.getByText('No submissions yet')).toBeVisible({ timeout: 30000 });

            await page.getByRole('tab', { name: 'Settings' }).click();
            await expect(page).toHaveURL(new RegExp(`#/post-forms/${id}/edit\\?tab=settings`));
            await expect(page.getByRole('tab', { name: 'Settings', selected: true })).toBeVisible({ timeout: 30000 });

            await page.getByRole('tab', { name: 'Submissions' }).click();
            await page.getByRole('tab', { name: 'Form Editor' }).click();
            await expect(page.getByRole('tab', { name: 'Form Editor', selected: true })).toBeVisible({ timeout: 30000 });
        } finally {
            aiWp(['post', 'delete', String(id), '--force']);
        }

        // Registration forms have no Submissions tab.
        if (await proRegistrationList()) {
            const reg = newForm('APP0024 reg ' + faker.string.alphanumeric(6), 'wpuf_profile');
            try {
                await page.goto(`${APP}#/registration-forms/${reg}/edit`);
                await expect(page.getByRole('tab', { name: 'Form Editor' }).first()).toBeVisible({ timeout: 30000 });
                await expect(page.getByRole('tab', { name: 'Submissions' })).toHaveCount(0);
            } finally {
                aiWp(['post', 'delete', String(reg), '--force']);
            }
        }
    });

    /** Leave the builder without saving: drop the unsaved state so the route guard does not stop the next test. */
    const discardBuilder = () => page.evaluate(() => {
        const w = window as unknown as { wp: { data: { dispatch: ( n: string ) => { markClean: () => void } } }; wpuf: { storeName: string } };
        w.wp.data.dispatch( w.wpuf.storeName ).markClean();
    });

    /** Hover a seam / cell and pick a field type from its "+" list (real mouse: the "+" shows on hover). */
    // The builder scrolls a new field into view smoothly, which can move the target
    // away from the mouse: hover again until the + shows.
    const hoverUntilShown = async (hoverTarget: import('@playwright/test').Locator, button: import('@playwright/test').Locator) => {
        await expect.poll(async () => {
            await hoverTarget.scrollIntoViewIfNeeded();
            await hoverTarget.hover();
            return button.evaluate((b) => getComputedStyle(b).opacity);
        }).toBe('1');
    };

    const pickFromPlus = async (hoverTarget: import('@playwright/test').Locator, button: import('@playwright/test').Locator, search: string, template: string) => {
        await expect(button).toHaveCount(1);
        await hoverUntilShown(hoverTarget, button);
        await button.click();
        await page.locator('input[type="search"][placeholder="Search fields"]').fill(search);
        await page.locator(`[data-insert-field="${template}"]`).click();
        // A custom (meta) field opens the custom field tip once, as a palette click does: close it.
        const tip = page.locator('[role="alertdialog"]').filter({ hasText: 'custom field data' });
        if (await tip.waitFor({ state: 'visible', timeout: 2000 }).then(() => true).catch(() => false)) {
            await tip.getByRole('button', { name: 'Okay' }).click();
            await expect(tip).toHaveCount(0);
        }
    };

    test('APP0021 : Builder seam + adds a field at that position', { tag: ['@Lite', '@Test_APP0021'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0021 seam');
        await page.goto(`${APP}#/post-forms/${id}/edit`);
        await builderReady('APP0021 seam');
        const rows = page.locator('#form-preview-stage ul.wpuf-form > li[data-dnd-item]');
        const before = await rows.count();
        expect(before, 'sample form has fields').toBeGreaterThan(1);

        // Between the first and the second field: the seam before row 2 (index 1).
        const seam = page.locator('#form-preview-stage li.wpuf-insert-seam[data-insert-index="1"]');
        await pickFromPlus(seam, seam.locator('button'), 'Website', 'website_url');
        await expect(rows).toHaveCount(before + 1);
        await expect(rows.nth(1)).toHaveClass(/form-field-website_url/);

        // After the last field.
        const last = page.locator('#form-preview-stage ul.wpuf-form > li.wpuf-insert-seam').last();
        await expect(last).toHaveAttribute('data-insert-index', String(before + 1));
        await pickFromPlus(last, last.locator('button'), 'Email', 'email_address');
        await expect(rows).toHaveCount(before + 2);
        await expect(rows.last()).toHaveClass(/form-field-email_address/);
        expect((await builderState()).dirty, 'adding marks the form changed').toBe(true);

        await discardBuilder();
        aiWp(['post', 'delete', String(id), '--force']);
    });

    test('APP0022 : Column cell + adds a field into that column', { tag: ['@Lite', '@Test_APP0022'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0022 column');
        await page.goto(`${APP}#/post-forms/${id}/edit`);
        await builderReady('APP0022 column');
        await page.locator('[data-form-field="column_field"]').first().click();
        const column = page.locator('#form-preview-stage li.form-field-column_field').last();
        await expect(column).toBeVisible();
        const cell = column.locator('[data-column="column-2"]');
        const plus = cell.locator('button[aria-label="Add a field to this column"]');

        await hoverUntilShown(cell, plus);
        await plus.click();
        // A column refuses columns: not in its list.
        await expect(page.locator('[data-insert-field="column_field"]')).toHaveCount(0);
        await expect(page.locator('[data-insert-field="text_field"]')).toHaveCount(1);
        await page.keyboard.press('Escape');

        await pickFromPlus(cell, plus, 'Text', 'text_field');
        await expect(cell.locator('li.form-field-text_field')).toHaveCount(1);
        await expect(column.locator('[data-column="column-1"] li[class*="form-field-"]')).toHaveCount(0);

        await discardBuilder();
        aiWp(['post', 'delete', String(id), '--force']);
    });

    test('APP0023 : Inner fields stored without ids open for editing', { tag: ['@Lite', '@Test_APP0023'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        // Develop stores column inner fields without an id; build such a form directly.
        const php = "$f = wpuf_create_sample_form( 'APP0023 legacy column', 'wpuf_forms', true );"
            + " $col = [ 'template' => 'column_field', 'input_type' => 'column_field', 'columns' => 2, 'label' => '', 'is_meta' => 'no', 'inner_fields' => [ 'column-1' => [ [ 'template' => 'text_field', 'input_type' => 'text', 'label' => 'Inner A', 'name' => 'inner_a_1', 'is_meta' => 'yes' ] ], 'column-2' => [ [ 'template' => 'text_field', 'input_type' => 'text', 'label' => 'Inner B', 'name' => 'inner_b_1', 'is_meta' => 'yes' ] ] ] ];"
            + " wp_insert_post( [ 'post_type' => 'wpuf_input', 'post_status' => 'publish', 'post_parent' => $f, 'menu_order' => 0, 'post_content' => maybe_serialize( wp_slash( $col ) ) ] ); echo $f;";
        const id = Number(aiWp(['eval', php], true).trim().split('\n').pop());

        await page.goto(`${APP}#/post-forms/${id}/edit`);
        await builderReady('APP0023 legacy column');
        const column = page.locator('#form-preview-stage li.form-field-column_field').first();
        await expect(column).toBeVisible();

        for (const [cell, label] of [['column-1', 'Inner A'], ['column-2', 'Inner B']]) {
            const inner = column.locator(`[data-column="${cell}"] li[class*="form-field-"]`).first();
            await inner.hover();
            await inner.locator('.wpuf-column-field-control-buttons span.inline-flex.h-6').first().click();
            await expect.poll(() => page.evaluate(() => {
                const w = window as unknown as { wp: { data: { select: ( n: string ) => { getEditingField: () => { label?: string } | null } } }; wpuf: { storeName: string } };
                const f = w.wp.data.select( w.wpuf.storeName ).getEditingField();
                return f ? f.label : null;
            }), { message: `${label} opens its own settings` }).toBe(label);
        }

        await discardBuilder();
        aiWp(['post', 'delete', String(id), '--force']);
    });

    test('APP0025 : Pro modules switch on and off; without a valid license every module is locked', { tag: ['@Pro', '@Test_APP0025'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');
        test.skip(!new AiFormBuilderPage(page).proActive(), 'modules need Pro');

        const MODULE = 'seo/wpuf-seo.php';
        // WP-CLI JSON escapes the slash (`seo\/wpuf-seo.php`), so match the file name only.
        const isOn = () => aiWp(['option', 'get', 'wpuf_pro_active_modules', '--format=json']).includes('wpuf-seo.php');
        const wasOn = isOn();
        const license = aiWp(['option', 'get', 'wpuf_license', '--format=json']).trim();

        try {
            await page.goto('about:blank');
            await page.goto(`${APP}#/modules`);
            const sw = page.locator(`li[data-module="${MODULE}"] [role="switch"]`);
            await expect(sw).toBeVisible({ timeout: 30000 });

            for (const want of [!wasOn, wasOn]) {
                const saved = page.waitForResponse((r) => r.url().includes('admin/modules') && 'POST' === r.request().method());
                await sw.click();
                expect((await saved).status()).toBe(200);
                await expect(sw).toHaveAttribute('aria-checked', String(want));
                expect(isOn(), `stored after switching ${want ? 'on' : 'off'}`).toBe(want);
            }

            // No valid license: the license-required card (ba20fb21), no switches, and the
            // REST route refuses to switch one on.
            aiWp(['eval', '$l = get_option( "wpuf_license" ); $l["status"] = "deactivate"; update_option( "wpuf_license", $l );'], true);
            await page.goto('about:blank');
            await page.goto(`${APP}#/modules`);
            const card = page.locator('.wpuf-app-state');
            await expect(card.getByRole('heading', { name: 'WPUF Pro License Required' })).toBeVisible({ timeout: 30000 });
            await expect(card.getByRole('link', { name: 'Activate License' })).toHaveAttribute('href', '#/license');
            await expect(page.locator('li[data-module] [role="switch"]')).toHaveCount(0);
            const status = await page.evaluate(async (module) => {
                const nonce = (window as unknown as { wpApiSettings?: { nonce: string } }).wpApiSettings?.nonce || '';
                const res = await fetch('/wp-json/wpuf/v1/admin/modules', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, body: JSON.stringify({ module, active: true }) });
                return res.status;
            }, MODULE);
            expect(status).toBe(403);
        } finally {
            aiWp(['option', 'update', 'wpuf_license', license, '--format=json']);
        }
    });

    test('APP0026 : Forms list search with no match shows the empty state; clearing it brings the list back', { tag: ['@Lite', '@Test_APP0026'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');

        await page.goto('about:blank');
        await page.goto(`${APP}#/post-forms`);
        const list = page.locator('#wpuf-post-forms-list-table-view');
        const search = list.locator('input[placeholder="Search Forms"]').first();
        await expect(list.locator('tbody tr').first()).toBeVisible({ timeout: 30000 });

        await search.fill('no-such-form-' + faker.string.alphanumeric(10));
        await expect(list.getByText('No forms found matching your search!')).toBeVisible({ timeout: 15000 });

        await search.fill('');
        await expect(list.locator('tbody tr').first()).toBeVisible({ timeout: 15000 });
        await expect(list.getByText('No forms found matching your search!')).toHaveCount(0);
    });

    test('APP0027 : Builder refuses a second single-instance field; Remove asks first and Cancel keeps the field', { tag: ['@Lite', '@Test_APP0027'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const id = newForm('APP0027 single ' + faker.string.alphanumeric(6));

        try {
            await page.goto('about:blank');
            await page.goto(`${APP}#/post-forms/${id}/edit`);
            await expect(page.locator('[data-form-field="post_title"]').first()).toBeVisible({ timeout: 30000 });
            const titles = page.locator('li[data-dnd-item].form-field-post_title');
            const before = await titles.count();
            expect(before).toBe(1);

            // The sample form has a Post Title already: a second one is refused.
            await page.locator('[data-form-field="post_title"]').first().click();
            const oops = page.locator('[role="alertdialog"]').filter({ hasText: 'You already have this field in the form' });
            await expect(oops).toBeVisible();
            await oops.getByRole('button').last().click();
            await expect(oops).toHaveCount(0);
            await expect(titles).toHaveCount(before);

            // Remove on a field asks first; Cancel keeps it.
            const fields = page.locator('li[data-dnd-item]');
            const fieldsBefore = await fields.count();
            await titles.first().hover();
            await titles.first().getByRole('button', { name: 'Remove' }).first().click();
            const confirm = page.locator('[role="alertdialog"]').filter({ hasText: 'delete this field' });
            await expect(confirm).toBeVisible();
            await confirm.getByRole('button', { name: /Cancel/ }).click();
            await expect(confirm).toHaveCount(0);
            await expect(fields).toHaveCount(fieldsBefore);
        } finally {
            await discardBuilder().catch(() => {});
            aiWp(['post', 'delete', String(id), '--force']);
        }
    });

    test('APP0028 : Tools import refuses a file with a script block and imports a clean export', { tag: ['@Lite', '@Test_APP0028'] }, async () => {
        test.skip(!(await appOn()), 'admin app is off');
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');

        const title = 'APP0028 export ' + faker.string.alphanumeric(6);
        const id = newForm(title);
        const dir = join(process.cwd(), 'test-results');
        const clean = join(dir, 'app0028-clean.json');
        const dirty = join(dir, 'app0028-script.json');

        try {
            await page.goto('about:blank');
            await page.goto(`${APP}#/tools?tab=import`);
            const exported = await page.evaluate(async (formId) => {
                const nonce = (window as unknown as { wpApiSettings?: { nonce: string } }).wpApiSettings?.nonce || '';
                const res = await fetch(`/wp-json/wpuf/v1/admin/tools/export?type=wpuf_forms&ids[]=${formId}`, { headers: { 'X-WP-Nonce': nonce } });
                // The Export tab saves only `forms` from this response as the file.
                return JSON.stringify((await res.json()).forms);
            }, id);
            mkdirSync(dir, { recursive: true });
            writeFileSync(clean, exported);
            writeFileSync(dirty, '<script>alert(1)</script>' + exported);
            const countForms = () => Number(aiWp(['post', 'list', '--post_type=wpuf_forms', `--title=${title}`, '--format=count']).trim());
            expect(countForms()).toBe(1);

            const input = page.locator('#wpbody-content input[type="file"]').first();
            const imported = () => page.waitForResponse((r) => r.url().includes('admin/tools/import') && 'POST' === r.request().method());

            await input.setInputFiles(dirty);
            let response = imported();
            await page.getByRole('button', { name: 'Import Forms' }).click();
            expect((await response).status()).toBe(415);
            await expect(page.getByText('Please upload a valid JSON export file.').first()).toBeVisible();
            expect(countForms()).toBe(1);

            await input.setInputFiles(clean);
            response = imported();
            await page.getByRole('button', { name: 'Import Forms' }).click();
            expect((await response).status()).toBe(200);
            await expect(page.getByText('Forms imported successfully.').first()).toBeVisible();
            expect(countForms()).toBe(2);
        } finally {
            const ids = aiWp(['post', 'list', '--post_type=wpuf_forms', `--title=${title}`, '--format=ids']).trim();
            if (ids) aiWp(['post', 'delete', ...ids.split(/\s+/), '--force']);
        }
    });
});
