import { test, type BrowserContext, type Page } from '@playwright/test';
import { faker } from '@faker-js/faker';
import { BasicLoginPage } from '../pages/basicLogin';
import { BusinessQaPage } from '../pages/businessQa';
import { WpufApi } from '../pages/api/WpufApi';
import { createAdminAppPassword, getWpufOptionKey, seedPageWithShortcode, seedUser, setWpufOptionKey, wpCli } from '../utils/wpEnvCli';

/**
 * Business QA round checks on the React frontend (qa-stories business BS-17 / BS-18).
 * Self-contained: seeds a member with one post, a post form and the pages, and
 * removes them afterwards.
 *
 * @Test_Scenarios : [Business QA — React frontend]
 * @Test_BQ0001 : [wpuf_dashboard layout=list] shows the posts card only; the full layout keeps the member blocks
 * @Test_BQ0002 : An untouched post form leaves without "leave page?"; a real edit asks
 * @Test_BQ0003 : The delete question opened by keyboard starts on Cancel; Enter keeps the post
 * @Test_BQ0004 : Keyboard focus on a dashboard link shows the neutral ring, not the browser's blue outline
 */

test.describe.configure({ mode: 'serial' });

let context: BrowserContext;
let page: Page;
let qa: BusinessQaPage;
let admin: WpufApi;
let formId = 0;
const pages: number[] = [];
const login = 'qa_bq_' + faker.string.alphanumeric(6).toLowerCase();
const password = faker.internet.password({ length: 16 });
let delSetting: string | null = null;
let urls: { list: string; full: string; form: string };

test.beforeAll(async ({ browser }) => {
    admin = await WpufApi.create(createAdminAppPassword());

    const created = await admin.post('/admin/forms', { type: 'wpuf_forms', template: 'post_form_template_post' });
    formId = Number((await created.json()).data.id);

    seedUser(login, `${login}@example.test`, password);
    const userId = wpCli(`user get ${login} --field=ID`).trim().split(/\s+/).pop();
    wpCli(`post create --post_type=post --post_status=publish --post_author=${userId} --post_title="BQ member post" --porcelain`);

    delSetting = getWpufOptionKey('wpuf_dashboard', 'enable_post_del');
    setWpufOptionKey('wpuf_dashboard', 'enable_post_del', 'yes');

    const list = seedPageWithShortcode('QA BQ dashboard list', '[wpuf_dashboard layout=list]');
    const full = seedPageWithShortcode('QA BQ dashboard', '[wpuf_dashboard]');
    const form = seedPageWithShortcode('QA BQ form', `[wpuf_form id=${formId}]`);
    pages.push(list.id, full.id, form.id);
    urls = { list: list.url, full: full.url, form: form.url };

    context = await browser.newContext();
    page = await context.newPage();
    qa = new BusinessQaPage(page);
    await new BasicLoginPage(page).basicLogin(login, password);
});

test.afterAll(async () => {
    await context?.close();
    for (const id of pages) {
        wpCli(`post delete ${id} --force`);
    }
    if (formId) {
        await admin.deleteForm(formId);
    }
    setWpufOptionKey('wpuf_dashboard', 'enable_post_del', delSetting || 'yes');
    wpCli(`user delete ${login} --yes`);
    await admin?.dispose();
});

test.describe('Business QA: React frontend', () => {
    test('BQ0001 : Dashboard list layout shows the posts card only', { tag: ['@Lite', '@Test_BQ0001'] }, async () => {
        await qa.openPage(urls.list);
        await qa.validateListLayoutShowsPostsCardOnly();

        await qa.openPage(urls.full);
        await qa.validateFullLayoutKeepsMemberBlocks();
    });

    test('BQ0002 : The exit warning needs a real edit', { tag: ['@Lite', '@Test_BQ0002'] }, async () => {
        await qa.openPage(urls.form);
        await qa.validateExitPrompt(false);

        await qa.openPage(urls.form);
        await qa.validateExitPrompt(true);
    });

    test('BQ0003 : The delete question starts on Cancel', { tag: ['@Lite', '@Test_BQ0003'] }, async () => {
        await qa.openPage(urls.full);
        await qa.validateDeleteDialogStartsOnCancel();
    });

    test('BQ0004 : Keyboard focus shows the neutral ring', { tag: ['@Lite', '@Test_BQ0004'] }, async () => {
        await qa.openPage(urls.full);
        await qa.validateNeutralFocusRing();
    });
});
