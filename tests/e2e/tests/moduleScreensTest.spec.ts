import { existsSync } from 'fs';
import { join } from 'path';
import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { aiWp, AiFormBuilderPage } from '../pages/aiFormBuilder';
import { Users, Urls } from '../utils/testData';

const HAS_WP_CLI = !!(process.env.WPUF_E2E_WP_PATH || process.env.WPUF_E2E_WP_ENV_DIR || existsSync(join(process.cwd(), '.wp-env.json')));

let browser: Browser;
let context: BrowserContext;
let page: Page;

const APP = `${Urls.baseUrl}/wp-admin/admin.php?page=wp-user-frontend`;
const MAIL_MODULES = ['campaign-monitor/campaign-monitor.php', 'convertkit/convertkit.php', 'getresponse/getresponse.php', 'mailchimp/wpuf-mailchimp.php', 'report/wpuf-report.php', 'private-message/private-message.php'];
const OPTIONS = ['wpuf_mailchimp_api_key', 'wpuf_mc_lists', 'wpuf_convertkit_api_key', 'wpuf_ck_lists', 'wpuf_getresponse_api_key', 'wpuf_gr_lists', 'wpuf_campaign_monitor_api_key', 'wpuf_camp_monitor_lists'];

let activeBefore = '';
const optionsBefore: Record<string, string> = {};

/** An option as JSON, '' when it does not exist (WP-CLI exits 1 on a missing option). */
function readOption(name: string): string {
    try {
        return aiWp(['option', 'get', name, '--format=json']).trim();
    } catch {
        return '';
    }
}

/**
 * Pro module screens in the React admin app (module-screens.md): every mail
 * connection is managed in Settings > Integrations > Email marketing, the
 * module screens only list what the service returned, and Reports draws on
 * the shared chart stack. The mail modules and Reports are switched on for
 * the spec through the active-modules option and restored after it; the
 * services are never called with a real key (a wrong key must fail cleanly).
 *
 * @TestScenario : [Module screens]
 * @Test_MOD0001 : Settings > Integrations lists Email marketing once; the four provider cards; the connection form blocks an empty key, tries a wrong key and shows the inline error; nothing is stored
 * @Test_MOD0002 : The Mailchimp screen is the app route #/integrations/mailchimp: the stored lists with subscribers and the registration forms using them, quick links to Settings; disconnected it shows the empty state with Connect in Settings
 * @Test_MOD0003 : Reports is the app route #/reports: the skeleton, the four pill tabs, the stat cards and the charts, the authors table on Posts, the period in the route query
 * @Test_MOD0004 : The account page's Message section is the React chat on wpuf/v1/messages: picker, send (Enter), attachment, delete message, delete thread, deep link, no Vue on the page
 */

test.beforeAll(async () => {
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext({ ignoreHTTPSErrors: true });
    page = await context.newPage();
    await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);

    if (HAS_WP_CLI) {
        activeBefore = readOption('wpuf_pro_active_modules') || '[]';
        OPTIONS.forEach((option) => {
            optionsBefore[option] = readOption(option);
        });
        const active: string[] = JSON.parse(activeBefore || '[]');
        aiWp(['option', 'update', 'wpuf_pro_active_modules', JSON.stringify([...new Set([...active, ...MAIL_MODULES])]), '--format=json']);
    }
});

test.afterAll(async () => {
    if (HAS_WP_CLI) {
        aiWp(['option', 'update', 'wpuf_pro_active_modules', activeBefore || '[]', '--format=json']);
        OPTIONS.forEach((option) => {
            if (optionsBefore[option]) {
                aiWp(['option', 'update', option, optionsBefore[option], '--format=json']);
            } else {
                aiWp(['option', 'delete', option]);
            }
        });
    }
    await browser.close();
});

/** Fake Mailchimp lists in the module's own option shape (what a connect stores). */
function seedMailchimp(): void {
    aiWp(['option', 'update', 'wpuf_mailchimp_api_key', 'e2e-mock-key-us21']);
    aiWp(['option', 'update', 'wpuf_mc_lists', JSON.stringify([
        { id: 'list0001', name: 'Newsletter', web_id: '100001', members: 12840 },
        { id: 'list0002', name: 'Product updates', web_id: '100002', members: 87 },
    ]), '--format=json']);
}

test.describe('Module screens', () => {
    test.beforeEach(async () => {
        test.skip(!HAS_WP_CLI, 'needs WP-CLI on the site');
        test.skip(!new AiFormBuilderPage(page).proActive(), 'module screens need Pro');
    });

    test('MOD0001 : Email marketing settings: one section, four cards, a wrong key fails cleanly', { tag: ['@Pro', '@Test_MOD0001'] }, async () => {
        aiWp(['option', 'delete', 'wpuf_mailchimp_api_key']);
        await page.goto(`${APP}#/settings?tab=integrations&sub=wpuf_mail_integrations`);
        await page.reload();

        const field = page.locator('.wpuf-mail-integrations');
        await expect(field).toBeVisible({ timeout: 30000 });

        // The section sits under the Integrations tab only: no second nav entry.
        await expect(page.getByRole('button', { name: 'Email marketing' })).toHaveCount(0);
        await expect(page.getByRole('tab', { name: 'Email marketing' })).toHaveCount(1);
        await expect(field.locator('[data-settings-card]')).toHaveCount(4);

        await field.locator('[data-settings-card="mailchimp"]').click();
        const form = field.locator('[data-integration-form="mailchimp"]');
        await expect(form).toBeVisible();
        await expect(form.getByRole('button', { name: 'Test Connection' })).toBeDisabled();

        // Empty key: no request, the inline message and the focus on the field.
        await form.getByRole('button', { name: 'Connect', exact: true }).click();
        await expect(form.getByRole('alert')).toContainText('required');
        expect(await page.evaluate(() => document.activeElement?.id)).toBe('wpuf-mailchimp-api_key');

        // A wrong key: the service refuses, the error sits inline, nothing is stored.
        await page.fill('#wpuf-mailchimp-api_key', 'wrong-key-us1');
        await expect(form.getByRole('button', { name: 'Test Connection' })).toBeEnabled();
        const connect = page.waitForResponse((r) => r.url().includes('/wpuf/v1/integrations/mailchimp/connect') && 'POST' === r.request().method());
        await form.getByRole('button', { name: 'Connect', exact: true }).click();
        expect((await connect).status()).toBe(400);
        await expect(field.locator('[data-wpuf-tone="error"]')).toBeVisible({ timeout: 30000 });
        expect(readOption('wpuf_mailchimp_api_key')).toBe('');
        await expect(field.locator('[data-settings-card="mailchimp"] svg')).toHaveCount(0);
    });

    test('MOD0002 : The Mailchimp screen lists the stored lists and links to Settings', { tag: ['@Pro', '@Test_MOD0002'] }, async () => {
        seedMailchimp();
        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf_mailchimp`);
        await expect(page).toHaveURL(/#\/integrations\/mailchimp$/);

        const screen = page.locator('.wpuf-integration');
        await expect(screen.locator('table tbody tr')).toHaveCount(2, { timeout: 30000 });
        await expect(screen.locator('thead')).toContainText('Subscribers');
        await expect(screen.locator('tbody tr').first()).toContainText('Newsletter');
        await expect(screen.locator('tbody tr').first()).toContainText('12,840');
        await expect(screen.getByRole('button', { name: 'Manage connection' })).toBeVisible();
        await expect(screen.getByRole('link', { name: /change the key in Settings/ })).toHaveAttribute('href', /settings\?tab=wpuf_mail_integrations/);

        // Disconnected (as Settings > Disconnect does): the empty state points at Settings.
        aiWp(['option', 'delete', 'wpuf_mailchimp_api_key']);
        aiWp(['option', 'delete', 'wpuf_mc_lists']);
        await page.reload();
        await expect(screen.getByRole('button', { name: 'Connect in Settings' }).first()).toBeVisible({ timeout: 30000 });
        await expect(screen.locator('table')).toHaveCount(0);
    });

    test('MOD0003 : Reports is the app route with tabs, cards, charts and the authors table', { tag: ['@Pro', '@Test_MOD0003'] }, async () => {
        await page.goto(`${Urls.baseUrl}/wp-admin/admin.php?page=wpuf_reports`);
        await expect(page).toHaveURL(/#\/reports$/);

        const reports = page.locator('.wpuf-reports');
        await expect(reports.locator('.recharts-surface').first()).toBeVisible({ timeout: 30000 });
        await expect(reports.locator('[role=tablist] [role=tab]')).toHaveCount(4);
        // The trend always draws; the breakdown donut draws when the site has users by role.
        expect(await reports.locator('.recharts-surface').count()).toBeGreaterThanOrEqual(1);
        await expect(reports.getByText('Registered in this period')).toBeVisible();

        await reports.locator('[role=tab]', { hasText: 'Posts' }).click();
        await expect(page).toHaveURL(/#\/reports\?tab=posts/);
        await expect(reports.getByText('Post statistics by author')).toBeVisible({ timeout: 30000 });

        await reports.locator('[role=tab]', { hasText: 'Transactions' }).click();
        await expect(reports.getByText('Total sales')).toBeVisible({ timeout: 30000 });
        // No breakdown card on Transactions: one trend card, drawn or in its empty state on a site without completed payments.
        await expect(reports.getByText('Sales and tax')).toBeVisible({ timeout: 30000 });
        await expect(reports.locator('[role=status][aria-busy=true]')).toHaveCount(0, { timeout: 30000 });
        expect(await reports.locator('.recharts-surface').count()).toBeLessThanOrEqual(1);

        // The period is in the route query and the report reloads behind its skeleton.
        const reload = page.waitForResponse((r) => r.url().includes('/wpuf/v1/reports/transactions') && r.url().includes('range=last_month'));
        await page.selectOption('#wpuf-reports-range', 'last_month').catch(async () => {
            await page.locator('#wpuf-reports-range').click();
            await page.getByRole('option', { name: 'Last Month' }).click();
        });
        expect((await reload).status()).toBe(200);
        await expect(page).toHaveURL(/range=last_month/);
    });
    test('MOD0004 : The Message section is the React chat with every action working', { tag: ['@Pro', '@Test_MOD0004'] }, async () => {
        const accountUrl = aiWp(['eval', 'echo get_permalink( (int) wpuf_get_option( "account_page", "wpuf_my_account" ) );'], true).trim();
        test.skip(!accountUrl.startsWith('http'), 'needs the account page');
        const other = Number(aiWp(['eval', '$u = get_users( [ "exclude" => [ 1 ], "number" => 1, "fields" => "ID" ] ); echo $u ? $u[0] : 0;']).trim());
        test.skip(!other, 'needs a second user');

        await page.goto(`${accountUrl}?section=message`);
        const chat = page.locator('#wpuf-private-message');
        await expect(chat.locator('.wpuf-pm-list')).toBeVisible({ timeout: 30000 });
        expect(await page.evaluate(() => [...document.scripts].some((s) => /vendor\/vue|vue\.js|vue-router/.test(s.src)))).toBe(false);

        // New message -> picker (lazy chunk) -> the other user -> #/user/<id>
        await chat.locator('.wpuf-pm-list__head .wpuf-pm-btn').click();
        await expect(page.locator('.wpuf-pm-modal .wpuf-pm-user').first()).toBeVisible({ timeout: 30000 });
        await page.locator(`.wpuf-pm-modal a[href="#/user/${other}"]`).first().click();
        await expect(page).toHaveURL(new RegExp(`#/user/${other}$`));
        await expect(chat.locator('.wpuf-pm-chat')).toBeVisible();

        // Send with Enter, then a text file; the list follows
        const stamp = `MOD0004 ${Date.now()}`;
        const sent = page.waitForResponse((r) => r.url().includes(`/wpuf/v1/messages/${other}`) && 'POST' === r.request().method());
        await chat.locator('.wpuf-pm-composer__input').fill(stamp);
        await page.keyboard.press('Enter');
        expect((await sent).status()).toBe(200);
        await expect(chat.locator('.wpuf-pm-msg--mine').last()).toContainText(stamp);
        await expect(chat.locator('.wpuf-pm-row').first()).toContainText(stamp.slice(0, 20));
        await page.setInputFiles('#wpuf-message-attachment', { name: 'mod0004.txt', mimeType: 'text/plain', buffer: Buffer.from('attachment') });
        await expect(chat.locator('.wpuf-pm-preview')).toHaveCount(1);
        await chat.locator('.wpuf-pm-send').click();
        await expect(chat.locator('.wpuf-pm-msg').last().locator('.wpuf-pm-file')).toHaveCount(1, { timeout: 30000 });

        // Delete one message, then the thread (confirm dialogs), back to the empty pane
        const before = await chat.locator('.wpuf-pm-msg').count();
        await chat.locator('.wpuf-pm-msg').last().hover();
        await chat.locator('.wpuf-pm-msg').last().locator('.wpuf-pm-msg__delete').click();
        await page.locator('.wpuf-dialog__footer .wpuf-dialog__btn:last-child').click();
        await expect(chat.locator('.wpuf-pm-msg')).toHaveCount(before - 1, { timeout: 30000 });
        await chat.locator('.wpuf-pm-iconbtn--danger').click();
        await page.locator('.wpuf-dialog__footer .wpuf-dialog__btn:last-child').click();
        await expect(chat.locator('.wpuf-pm-empty--pane')).toBeVisible({ timeout: 30000 });
        await expect(page).toHaveURL(/#\/$/);
    });
});
