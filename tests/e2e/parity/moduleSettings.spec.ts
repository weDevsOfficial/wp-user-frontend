import { test, expect, type Locator, type Page } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { statSync, readFileSync } from 'fs';
import { join } from 'path';
import { paritySite, paritySitesConfigured, parityWp, type ParitySite } from '../utils/paritySites';

/**
 * Pro modules through core (task 4.5b, modules.md): module settings the React
 * registration builder renders itself store what develop stores.
 */
const FIXTURE = 'registration-form-choices.json';

type Settings = Record<string, unknown>;

/** Pick an option of a native select (develop) or the Select wrapper (branch). */
async function pick(page: Page, scope: Locator, label: string) {
    const native = scope.locator('select');
    if (await native.count()) {
        await native.first().selectOption({ label });
        return;
    }
    await scope.locator('[data-slot="select-trigger"]').first().click();
    await page.locator('[data-slot="select-item"]:visible').filter({ hasText: new RegExp(`^\\s*${label}\\s*$`) }).first().click();
}

async function openTab(page: Page, label: string) {
    await page.locator('#wpuf-form-builder li[class*="sidebar-item"]').filter({ hasText: new RegExp(`^\\s*${label}\\s*$`) }).first().click();
    await page.waitForTimeout(600);
}

test.describe('Parity module settings', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0035 : Mailchimp conditional logic edits and stores like develop', { tag: ['@Parity', '@Test_PAR0035'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Record<string, Settings> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE, 'Parity mailchimp conditions');
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await admin.doOpenBuilder('wpuf_profile', formId);
            await admin.doOpenBuilderSettings([]);
            await openTab(page, 'Mailchimp');
            const block = page.locator('.wpuf-integration-conditional-logic-container');
            await block.getByRole('radio', { name: 'Yes', exact: true }).check();
            await pick(page, block.locator('.wpuf-conditional-logic-settings').first(), 'Any');
            const rule = (n: number) => block.locator('.wpuf-conditional-rule').nth(n);
            await pick(page, rule(0).locator('.cond-field'), 'Color');
            await pick(page, rule(0).locator('.cond-operator'), 'is not');
            await pick(page, rule(0).locator('.cond-option'), 'Blue');
            await rule(0).locator('.wpuf-repeater-add').click();
            await pick(page, rule(1).locator('.cond-field'), 'Topics');
            await pick(page, rule(1).locator('.cond-operator'), 'any selection');
            await rule(1).locator('.wpuf-repeater-add').click();
            await pick(page, rule(2).locator('.cond-field'), 'Size');
            await pick(page, rule(2).locator('.cond-option'), 'Large');
            await rule(1).locator('.wpuf-repeater-remove').click();
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        }));

        expect(stored.branch.integrations, 'integrations.mailchimp.wpuf_cond').toEqual(stored.develop.integrations);
    });

    test('PAR0036 : switching stored Mailchimp conditions off stores like develop', { tag: ['@Parity', '@Test_PAR0036'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Record<string, Settings> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE, 'Parity mailchimp conditions off');
            parityWp(site, ['eval', `$s = (array) get_post_meta( ${formId}, 'wpuf_form_settings', true ); $s['enable_mailchimp'] = 'on'; $s['integrations'] = [ 'mailchimp' => [ 'wpuf_cond' => [ 'condition_status' => 'yes', 'cond_logic' => 'all', 'conditions' => [ [ 'name' => 'qa_size', 'operator' => '=', 'option' => 's' ] ] ] ] ]; update_post_meta( ${formId}, 'wpuf_form_settings', $s );`]);
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await admin.doOpenBuilder('wpuf_profile', formId);
            await admin.doOpenBuilderSettings([]);
            await openTab(page, 'Mailchimp');
            const block = page.locator('.wpuf-integration-conditional-logic-container');
            await expect(block.locator('.wpuf-conditional-rule'), 'stored rule shown').toHaveCount(1);
            await block.getByRole('radio', { name: 'No', exact: true }).check();
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        }));

        console.log('PAR0036', JSON.stringify({ develop: stored.develop.integrations, branch: stored.branch.integrations }));
        expect(stored.branch.integrations, 'integrations.mailchimp.wpuf_cond').toEqual(stored.develop.integrations);
    });
});

// Keys develop's save writes without an edit (see registrationSettings.spec.ts).
const CANONICAL = ['mob_number', 'sms_body', 'sms_enable', 'sms_sender_name', 'wpuf_user_status', 'redirect_to', 'notification', 'reg_page_id', 'profile_page_id', 'integrations'];
const SIDES = ['develop', 'branch'] as const;

/** Develop's settings with the keys only its save canonicalized folded in. */
function folded(develop: Settings, branch: Settings, fixture: Settings): Settings {
    const copy = JSON.parse(JSON.stringify(develop)) as Settings;
    for (const key of CANONICAL) {
        if (JSON.stringify(branch[key]) === JSON.stringify(fixture[key])) {
            if (key in branch) {
                copy[key] = branch[key];
            } else {
                delete copy[key];
            }
        }
    }
    return copy;
}

/** PHP notices / warnings logged from WPUF free or Pro since `from` bytes. */
function wpufLogLines(site: ParitySite, from: number): string[] {
    const file = join(site.wpPath, 'wp-content', 'debug.log');
    let text = '';
    try {
        text = readFileSync(file).subarray(from).toString('utf-8');
    } catch {
        return [];
    }
    return text.split('\n')
        .filter((line) => /PHP (Notice|Warning|Fatal|Parse|Deprecated)/.test(line) && /plugins\/(wp-user-frontend|wpuf-pro)\//.test(line))
        .map((line) => line.replace(/^\[[^\]]+\]\s*/, '').replace(site.wpPath, '<site>'));
}

function logSize(site: ParitySite): number {
    try {
        return statSync(join(site.wpPath, 'wp-content', 'debug.log')).size;
    } catch {
        return 0;
    }
}

async function tabsOf(admin: ParitySitePage): Promise<string[]> {
    await admin.doOpenBuilderSettings([]);
    return (await admin.page.locator('#wpuf-form-builder li[class*="sidebar-item"]').allInnerTexts()).map((t) => t.trim()).filter(Boolean);
}

/**
 * Module dependency states (modules.md tests). These tests change site-wide
 * state (options, active plugins) on both sites and restore it: run them
 * alone, not next to other parity specs.
 */
test.describe('Parity module dependencies', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');
    test.describe.configure({ mode: 'serial' });

    test('PAR0037 : newsletter modules with their lists available render, fill and store like develop', { tag: ['@Parity', '@Test_PAR0037'] }, async ({ browser }) => {
        test.setTimeout(300_000);
        // The API-key modules read their cached lists; two fake lists = connected.
        const LIST_OPTIONS = ['wpuf_mc_lists', 'wpuf_ck_lists', 'wpuf_gr_lists', 'wpuf_camp_monitor_lists'];
        const lists = JSON.stringify([{ id: 'list-a', name: 'List A' }, { id: 'list-b', name: 'List B' }]);
        const TABS = ['Mailchimp', 'Kit (Formerly ConvertKit)', 'GetResponse', 'Campaign Monitor'];
        const parity = new ParityPage();
        const stored: Partial<Record<'develop' | 'branch', FormDump>> = {};
        const rows: Record<string, string[]> = {};
        const before: Record<string, Record<string, string | null>> = {};

        for (const name of SIDES) {
            before[name] = {};
            for (const option of LIST_OPTIONS) {
                try {
                    before[name][option] = parityWp(paritySite(name), ['option', 'get', option, '--format=json']).trim();
                } catch {
                    before[name][option] = null;
                }
                parityWp(paritySite(name), ['option', 'update', option, lists, '--format=json']);
            }
        }

        try {
            await Promise.all(SIDES.map(async (name) => {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, FIXTURE, 'Parity newsletter modules');
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder('wpuf_profile', formId);
                await tabsOf(admin);
                rows[name] = [];
                for (const [i, tab] of TABS.entries()) {
                    rows[name].push(`# ${tab}`, ...(await admin.doFillFormSettings(tab, `m${i}`)));
                }
                await admin.doSaveBuilder();
                await admin.doClose();
                stored[name] = parity.readForm(site, formId);
            }));
        } finally {
            for (const name of SIDES) {
                for (const option of LIST_OPTIONS) {
                    const old = before[name][option];
                    parityWp(paritySite(name), null === old ? ['option', 'delete', option] : ['option', 'update', option, old, '--format=json']);
                }
            }
        }

        await test.info().attach('rows.json', { path: parity.doWriteJson(test.info().outputPath('rows.json'), rows) });
        expect.soft(rows.branch, 'same rows and edits').toStrictEqual(rows.develop);
        // Only the four modules' keys: develop's save also wrote the shown
        // defaults of every untouched tab (canonicalization, PAR0031 covers it).
        const KEYS = ['enable_mailchimp', 'mailchimp_list', 'enable_double_optin', 'integrations', 'enable_convertkit', 'convertkit_list', 'enable_getresponse', 'getresponse_list', 'enable_campaign_monitor', 'campaign_monitor_list'];
        const pickKeys = (settings: Settings) => Object.fromEntries(KEYS.filter((key) => key in settings).map((key) => [key, settings[key]]));
        const fixture = (parity.readFixture(FIXTURE).meta as Record<string, Settings>).wpuf_form_settings;
        const dev = (stored.develop!.meta as Record<string, Settings>).wpuf_form_settings;
        const br = (stored.branch!.meta as Record<string, Settings>).wpuf_form_settings;
        expect(pickKeys(br), 'module settings equal develop').toStrictEqual(pickKeys(folded(dev, br, fixture)));
    });

    test('PAR0038 : with MailPoet and BuddyPress inactive the registration builder loads clean and shows what develop shows', { tag: ['@Parity', '@Test_PAR0038'] }, async ({ browser }) => {
        test.setTimeout(300_000);
        const PLUGINS = ['mailpoet', 'buddypress'];
        const parity = new ParityPage();
        const tabs: Record<string, string[]> = {};
        const lines: Record<string, string[]> = {};
        const errors: Record<string, string[]> = {};
        const php: Record<string, string[]> = {};

        for (const name of SIDES) {
            parityWp(paritySite(name), ['plugin', 'deactivate', ...PLUGINS]);
        }

        try {
            await Promise.all(SIDES.map(async (name) => {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, FIXTURE, 'Parity module dependencies off');
                const from = logSize(site);
                const admin = await ParitySitePage.doOpen(browser, site);
                errors[name] = [];
                admin.page.on('console', (message) => {
                    if ('error' === message.type()) {
                        errors[name].push(message.text());
                    }
                });
                admin.page.on('pageerror', (error) => errors[name].push(error.message));
                await admin.doOpenBuilder('wpuf_profile', formId);
                tabs[name] = await tabsOf(admin);
                lines[name] = [];
                for (const tab of tabs[name].filter((t) => /MailPoet|BuddyPress/i.test(t))) {
                    lines[name].push(`# ${tab}`, ...(await admin.doProbeSettingsConditions(tab)));
                }
                // The probe puts every control back, so the branch may have nothing to save.
                await admin.doSaveBuilder({ untouched: true });
                await admin.doClose();
                php[name] = wpufLogLines(site, from);
            }));
        } finally {
            for (const name of SIDES) {
                parityWp(paritySite(name), ['plugin', 'activate', ...PLUGINS]);
                // BuddyPress sends the next admin page to its welcome screen after activation.
                parityWp(paritySite(name), ['transient', 'delete', '_bp_activation_redirect']);
            }
        }

        await test.info().attach('state.json', { path: parity.doWriteJson(test.info().outputPath('state.json'), { tabs, lines, errors, php }) });
        expect(tabs.branch, 'same settings tabs').toEqual(tabs.develop);
        expect(lines.branch, 'same module rows').toStrictEqual(lines.develop);
        expect(errors.branch, 'no JS errors on the branch builder').toEqual([]);
        expect(php.branch.filter((line) => !php.develop.includes(line)), 'no new WPUF PHP notices').toEqual([]);
    });
});
