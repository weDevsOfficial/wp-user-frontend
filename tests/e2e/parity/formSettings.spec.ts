import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * G2 for the post form's settings tabs (task 4.4e, 0.4j PAR0009): the same
 * deterministic edit on every visible settings row of a tab, made through each
 * builder's own UI, stores the same `wpuf_form_settings` on both sites, and both
 * builders show the same rows. Develop's silent canonicalization on save (every
 * rendered input posted, so untouched keys gain their shown default) is not
 * copied (owner, Q6): such keys are folded in by `withoutCanonicalization`.
 */
const FIXTURE = 'post-form-parity.json';
const TABS = ['General', 'Payment Settings', 'Notification Settings', 'Display Settings', 'Advanced', 'Post Expiration', 'AI Review', 'N8N', 'SMS'];

type Settings = Record<string, unknown>;

/**
 * Develop's settings with the keys only develop's canonicalization changed: a
 * key the branch kept as stored (untouched on the branch) takes the branch's
 * value when develop's differs only by being the default develop shows for it.
 */
function withoutCanonicalization(develop: Settings, branch: Settings, fixture: Settings, canonical: string[]): Settings {
    const copy = JSON.parse(JSON.stringify(develop)) as Settings;
    // `roles` declares always_selected ['administrator'] (wpuf-functions.php); develop's
    // builder ignored it, so picking only Subscriber locked admins out; the branch
    // keeps Administrator first in the list (agreed fix, 4.4e).
    if (Array.isArray(copy.roles) && Array.isArray(branch.roles) && branch.roles.includes('administrator') && !copy.roles.includes('administrator')) {
        copy.roles = ['administrator', ...(copy.roles as string[])];
    }
    for (const key of canonical) {
        const same = JSON.stringify(branch[key]) === JSON.stringify(fixture[key]);
        if (same) {
            if (key in branch) {
                copy[key] = branch[key];
            } else {
                delete copy[key];
            }
        }
    }
    return copy;
}

test.describe('Parity form settings', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');
    test.describe.configure({ mode: 'parallel' });

    for (const [index, tab] of TABS.entries()) {
        test(`PAR0024 : settings tab "${tab}": filling every row stores the same on branch as on develop`, { tag: ['@Parity', '@Test_PAR0024'] }, async ({ browser }) => {
            const parity = new ParityPage();
            const stored: Partial<Record<'develop' | 'branch', FormDump>> = {};
            const rows: Partial<Record<'develop' | 'branch', string[]>> = {};

            await Promise.all((['develop', 'branch'] as const).map(async (name) => {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, FIXTURE, `Parity form settings ${index}`);
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder('wpuf_forms', formId);
                await admin.doOpenBuilderSettings([]);
                rows[name] = await admin.doFillFormSettings(tab, String(index));
                await admin.doSaveBuilder();
                await admin.doClose();
                stored[name] = parity.readForm(site, formId);
            }));

            await test.info().attach('rows.json', { path: parity.doWriteJson(test.info().outputPath('rows.json'), rows) });
            await test.info().attach('stored.json', { path: parity.doWriteJson(test.info().outputPath('stored.json'), stored) });

            const kept = (list: string[] = []) => list.filter((row) => !DEFERRED_ROWS.some((prefix) => row.startsWith(prefix)));
            expect.soft(kept(rows.branch), 'branch builder shows the same settings rows and accepts the same edits').toStrictEqual(kept(rows.develop));

            const fixture = (parity.readFixture(FIXTURE).meta as Record<string, Settings>).wpuf_form_settings;
            const dev = (stored.develop!.meta as Record<string, Settings>).wpuf_form_settings;
            const br = (stored.branch!.meta as Record<string, Settings>).wpuf_form_settings;
            expect(br, 'branch form settings equal develop\'s').toStrictEqual(withoutCanonicalization(dev, br, fixture, CANONICAL));
        });
    }
});

test.describe('Parity form settings: default terms', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0025 : default category per taxonomy row stores the picked term ids like develop', { tag: ['@Parity', '@Test_PAR0025'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Record<string, Settings> = {};
        const labels: Record<string, string[]> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE, 'Parity default terms');
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await admin.doOpenBuilder('wpuf_forms', formId);
            await admin.doOpenBuilderSettings([]);
            // Develop adds the rows by AJAX after load.
            const row = page.locator('.taxonomy-container[data-taxonomy="category"]');
            await expect(row).toBeVisible();
            labels[name] = await page.locator('.taxonomy-container label').allInnerTexts();
            const selectized = row.locator('select.selectized');
            if (await selectized.count()) {
                await selectized.evaluate((el) => {
                    const widget = (el as unknown as { selectize: { options: Record<string, unknown>; addItem: (v: string) => void } }).selectize;
                    const keys = Object.keys(widget.options);
                    widget.addItem(keys[keys.length - 1]);
                });
            } else {
                await row.locator('[data-slot="smart-multi-select-trigger"]').click();
                const items = page.locator('[data-slot="smart-multi-select-content"] [data-slot="command-group"]').first().locator('[data-slot="command-item"]:visible');
                await items.last().click();
                await page.keyboard.press('Escape');
            }
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        }));

        expect(labels.branch, 'same taxonomy rows').toEqual(labels.develop);
        expect(stored.branch.default_category, 'default_category = picked term ids').toEqual(stored.develop.default_category);
    });
});

test.describe('Parity form settings: submit button conditions', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0027 : submit button conditions edit and store like develop', { tag: ['@Parity', '@Test_PAR0027'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Record<string, Settings> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, 'post-form-cond-logic.json', 'Parity submit conditions');
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await admin.doOpenBuilder('wpuf_forms', formId);
            await admin.doOpenBuilderSettings([]);
            await page.locator('#wpuf-form-builder li[class*="sidebar-item"]').filter({ hasText: /^\s*Advanced\s*$/ }).first().click();
            const block = page.locator('.wpuf-submit-button-conditional-logic-container');
            await block.getByRole('radio', { name: 'Yes', exact: true }).check();
            // Native selects on develop, the Select wrapper on the branch.
            const pick = async (scope: import('@playwright/test').Locator, label: string) => {
                const native = scope.locator('select');
                if (await native.count()) {
                    await native.first().selectOption({ label });
                    return;
                }
                await scope.locator('[data-slot="select-trigger"]').first().click();
                await page.locator('[data-slot="select-item"]:visible').filter({ hasText: new RegExp(`^\\s*${label}\\s*$`) }).first().click();
            };
            await pick(block.locator('.wpuf-conditional-logic-settings').first(), 'All');
            const rule = (n: number) => block.locator('.wpuf-conditional-rule').nth(n);
            await pick(rule(0).locator('.cond-field'), 'Size');
            await pick(rule(0).locator('.cond-option'), 'Large');
            await rule(0).locator('button[title="Add Condition"], button[aria-label="Add Condition"]').first().click();
            await pick(rule(1).locator('.cond-field'), 'City');
            await pick(rule(1).locator('.cond-operator'), 'contains');
            await rule(1).locator('.cond-option input').fill('Dha');
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        }));

        // Develop's Vue component computed each rule's input_type but its save posted
        // the DOM inputs, which have none, so develop stored ''. The branch stores the
        // type (the frontend then runs its typed check instead of guessing; agreed, 4.4e).
        const develop = JSON.parse(JSON.stringify(stored.develop.submit_button_cond)) as { conditions: Record<string, string>[] };
        const branch = stored.branch.submit_button_cond as { conditions: Record<string, string>[] };
        develop.conditions.forEach((rule, i) => {
            if ('' === rule.input_type && branch.conditions[i]) {
                rule.input_type = branch.conditions[i].input_type;
            }
        });
        expect(branch, 'submit_button_cond').toEqual(develop);
    });
});

/**
 * Keys develop's save canonicalizes (rewrites to the shown default without an
 * edit). Filled from the first measured run: only keys the branch kept as stored
 * are folded, so a missing branch edit still fails.
 */
// SMS module inputs are rendered (and posted) on every develop form even when
// the module is off.
const CANONICAL: string[] = ['mob_number', 'sms_body', 'sms_enable', 'sms_sender_name'];

/** Rows left out on both sides. */
const DEFERRED_ROWS = [
    // Default terms per taxonomy: when the picked post type has no hierarchical
    // taxonomy, develop's AJAX returns nothing and leaves the previous type's
    // rows on screen; the branch shows none. Covered by PAR0025.
    'Default ',
];
