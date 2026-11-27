import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * Task 2.6: the PHP builder hooks the Vue views fired still reach other plugins
 * on the React builder (Builder/HookBridge.php + common/LegacySlot.jsx), and the
 * WPUF features that used the removed Vue-only hooks work through React.
 */
test.describe('Parity builder hook bridge', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0022 : third-party settings rows and tabs render, save like develop, untouched save adds nothing', { tag: ['@Parity', '@Test_PAR0022'] }, async ({ browser }) => {
        const site = paritySite('branch');
        const muDir = path.join(site.wpPath, 'wp-content', 'mu-plugins');
        const fixture = path.join(muDir, 'wpuf-parity-third-party-builder-hooks.php');
        fs.mkdirSync(muDir, { recursive: true });
        fs.copyFileSync(path.join(parityDir, 'wp', 'third-party-builder-hooks.php'), fixture);

        try {
            const parity = new ParityPage();
            const formId = parity.doSeedForm(site, 'post-form-parity.json');
            const settingsOf = () => parity.readForm(site, formId).meta.wpuf_form_settings as Record<string, unknown>;
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            const errors: string[] = [];
            page.on('pageerror', (error) => errors.push(String(error)));

            await admin.doOpenBuilder('wpuf_forms', formId);
            await expect(page.locator('.wpuf-legacy-slot .tp-tab'), 'third-party tab printed next to the builder tabs').toHaveText('TP tab');

            // Untouched save, settings panel visited: no third-party key is added.
            const openSettings = async () => {
                await page.locator('a[href="#form-settings"]').click();
                await page.locator('[data-settings="advanced"]').first().click();
                await expect(page.locator('.wpuf-legacy-slot .tp-row')).toBeVisible();
            };
            await openSettings();
            await expect(page.locator('.wpuf-legacy-slot .tp-settings'), 'settings tab hook output shown').toBeVisible();
            expect(await page.evaluate(() => (window as any).tpScriptRan), 'scripts are not run').toBeUndefined();
            expect(await page.locator('.tp-label').getAttribute('onclick'), 'event attributes stripped').toBeNull();
            await expect(page.locator('submit-button-conditional-logics'), "WPUF's own Vue listener output is not printed").toHaveCount(0);
            await admin.doSaveBuilder({ untouched: true });
            const untouched = settingsOf();
            expect(['tp_enabled', 'tp_label', 'tp_extra'].filter((key) => key in untouched), 'untouched save adds no third-party keys').toEqual([]);

            // Edit, switch to the Form Editor (panel unmounts), save from there.
            await page.locator('.tp-enabled').check();
            await page.locator('.tp-label').fill('Hello');
            await page.locator('.tp-extra').fill('Extra');
            await page.locator('a[href="#form-editor"]').click();
            await admin.doSaveBuilder();
            const edited = settingsOf();
            expect([edited.tp_enabled, edited.tp_label, edited.tp_extra], 'slot inputs stored in wpuf_settings shape').toEqual(['yes', 'Hello', 'Extra']);

            // Reload: values come back from the stored settings; untick removes the key.
            await admin.doOpenBuilder('wpuf_forms', formId);
            await openSettings();
            await expect(page.locator('.tp-label')).toHaveValue('Hello');
            await page.locator('.tp-enabled').uncheck();
            await admin.doSaveBuilder();
            const unticked = settingsOf();
            expect('tp_enabled' in unticked, 'unticked checkbox removed, as a form post').toBe(false);
            expect(unticked.tp_label, 'other slot values kept').toBe('Hello');
            await admin.doClose();
            expect(errors, 'no page errors').toEqual([]);
        } finally {
            fs.unlinkSync(fixture);
        }
    });

    test('PAR0023 : features behind the removed Vue hooks work in React (bulk add with Pro, teaser without)', { tag: ['@Parity', '@Test_PAR0023'] }, async ({ browser }) => {
        const site = paritySite('branch');
        const parity = new ParityPage();

        // With Pro: Bulk Add button + modal (were wpuf_field_option_data_actions / _after).
        const formId = parity.doSeedForm(site, 'post-form-all-fields.json');
        const admin = await ParitySitePage.doOpen(browser, site);
        const page = admin.page;
        await admin.doOpenBuilder('wpuf_forms', formId);
        const row = page.locator('#form-preview-stage > ul > li.form-field-dropdown_field').first();
        await row.scrollIntoViewIfNeeded();
        await row.hover();
        await row.getByText('Edit', { exact: true }).first().click();
        const panel = page.locator('.wpuf-form-builder-field-options');
        await panel.getByRole('button', { name: 'Bulk Add' }).click();
        await page.locator('textarea').last().fill('Red\nGreen\nRed');
        await page.getByRole('button', { name: /^Add/ }).last().click();
        await admin.doSaveBuilder();
        await admin.doClose();
        const dropdown = parity.readForm(site, formId).fields.map((field) => field.post_content as Record<string, any>)
            .find((field) => 'dropdown_field' === field.template)!;
        const options = dropdown.options as Record<string, string>;
        expect(Object.values(options).slice(-3), 'bulk-added labels').toEqual(['Red', 'Green', 'Red']);
        expect(Object.keys(options).slice(-3), 'values never repeat').toEqual(['red', 'green', 'red_1']);

        // Without Pro: the free teaser links to Pro (was Free_Loader on wpuf_field_option_data_actions).
        const modulesBackup = parityWp(site, ['option', 'get', 'wpuf_pro_active_modules', '--format=json']).trim();
        parityWp(site, ['plugin', 'deactivate', 'wpuf-pro']);
        try {
            const free = await ParitySitePage.doOpen(browser, site);
            const freeForm = parity.doSeedForm(site, 'post-form-all-fields.json');
            await free.doOpenBuilder('wpuf_forms', freeForm);
            const freeRow = free.page.locator('#form-preview-stage > ul > li.form-field-dropdown_field').first();
            await freeRow.scrollIntoViewIfNeeded();
            await freeRow.hover();
            await freeRow.getByText('Edit', { exact: true }).first().click();
            const teaser = free.page.locator('.wpuf-form-builder-field-options a', { hasText: 'Bulk Add' });
            await expect(teaser, 'free shows the Bulk Add teaser').toBeVisible();
            expect(await teaser.getAttribute('href'), 'teaser links to Pro').toBe(await free.page.evaluate(() => (window as any).wpuf_form_builder.pro_link));
            await free.doClose();
        } finally {
            parityWp(site, ['plugin', 'activate', 'wpuf-pro']);
            parityWp(site, ['option', 'update', 'wpuf_pro_active_modules', modulesBackup, '--format=json']);
        }
    });

    test('PAR0030 : retired Vue hooks still fire (deprecated, admin notice) and every React builder slot takes fills', { tag: ['@Parity', '@Test_PAR0030'] }, async ({ browser }) => {
        const site = paritySite('branch');
        const muDir = path.join(site.wpPath, 'wp-content', 'mu-plugins');
        const fixture = path.join(muDir, 'wpuf-parity-retired-hooks.php');
        fs.copyFileSync(path.join(parityDir, 'wp', 'third-party-retired-hooks.php'), fixture);
        parityWp(site, ['option', 'delete', 'wpuf_retired_builder_hooks', 'wpuf_retired_builder_hooks_dismissed', 'wpuf_parity_retired_action_ran']);

        try {
            const parity = new ParityPage();
            const formId = parity.doSeedForm(site, 'post-form-cond-logic.json', 'Parity retired hooks');
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            const errors: string[] = [];
            page.on('pageerror', (error) => errors.push(String(error)));
            await admin.doOpenBuilder('wpuf_forms', formId);

            // PHP: the retired hooks ran, the outside callbacks were recorded, Vue markup is not printed.
            const known = JSON.parse(parityWp(site, ['option', 'get', 'wpuf_retired_builder_hooks', '--format=json']));
            expect(Object.keys(known).sort(), 'outside callbacks recorded per retired hook').toEqual(['wpuf_builder_field_options', 'wpuf_form_builder_js_root_mixins']);
            expect(parityWp(site, ['option', 'get', 'wpuf_parity_retired_action_ran']).trim(), 'retired action still fires').toMatch(/^\d+$/);
            await expect(page.locator('.tp-vue-markup'), 'retired action output discarded').toHaveCount(0);
            expect(await page.evaluate(() => (window as unknown as { wpuf_mixins: { root: string[] } }).wpuf_mixins.root), 'filtered mixins still reach window.wpuf_mixins').toContain('tp_root_mixin');

            // The admin notice names the hooks (on any admin page) and can be dismissed.
            await page.goto('/wp-admin/index.php');
            const notice = page.locator('.notice', { hasText: 'retired with the Vue builder' });
            await expect(notice).toContainText('wpuf_builder_field_options');
            await expect(notice).toContainText('wpuf-parity-retired-hooks.php');
            await notice.getByRole('link', { name: 'Dismiss' }).click();
            await expect(page.locator('.notice', { hasText: 'retired with the Vue builder' })).toHaveCount(0);

            // React slots: stage (submit area + bottom), field options, option editor, settings panel.
            await admin.doOpenBuilder('wpuf_forms', formId);
            await expect(page.locator('[data-slot-name="wpuf-form-builder-canvas-submit-area"]')).toBeVisible();
            await expect(page.locator('[data-slot-name="wpuf-form-builder-canvas-bottom"]')).toBeAttached();
            const color = page.locator('#form-preview-stage li[class*="form-field-"]', { hasText: 'Color' }).first();
            await color.hover();
            await color.locator(':scope > div:last-child').getByText('Edit', { exact: true }).first().click();
            await expect(page.locator('[data-slot-name="wpuf-form-builder-field-options-after"]')).toContainText('field=qa_color');
            await expect(page.locator('[data-slot-name="wpuf-form-builder-option-data-actions"]')).toBeVisible();
            await expect(page.locator('[data-slot-name="wpuf-form-builder-option-data-after"]')).toBeVisible();
            await admin.doOpenBuilderSettings([]);
            await expect(page.locator('[data-slot-name="wpuf-form-builder-settings-general"]').first()).toBeAttached();

            // The event hub forwards to wp.hooks.
            const echoed = await page.evaluate(() => {
                const w = window as unknown as { wpuf_form_builder: { event_hub: { $on: (n: string, c: (v: string) => void) => void; $emit: (n: string, v: string) => void } } };
                let got = '';
                w.wpuf_form_builder.event_hub.$on('tp-event', (v) => { got = v; });
                w.wpuf_form_builder.event_hub.$emit('tp-event', 'hello');
                return got;
            });
            expect(echoed, 'event_hub $on / $emit through wp.hooks').toBe('hello');
            await admin.doClose();
            expect(errors, 'no page errors').toEqual([]);
        } finally {
            fs.rmSync(fixture, { force: true });
            parityWp(site, ['option', 'delete', 'wpuf_retired_builder_hooks', 'wpuf_retired_builder_hooks_dismissed', 'wpuf_parity_retired_action_ran']);
        }
    });
});
