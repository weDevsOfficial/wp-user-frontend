import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * Pro field option inputs behave like develop's field-* components (task 1.9
 * field cross-check): opening a panel writes nothing, edits store develop's shape.
 */
test.describe('Parity pro option inputs', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0021 : step start, country list, address and pricing options edit and store like develop', { tag: ['@Parity', '@Test_PAR0021'] }, async ({ browser }) => {
        const site = paritySite('branch');
        const formId = new ParityPage().doSeedForm(site, 'post-form-all-fields.json');
        const admin = await ParitySitePage.doOpen(browser, site);
        const page = admin.page;
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(String(error)));
        await admin.doOpenBuilder('wpuf_forms', formId);

        const panel = page.locator('.wpuf-form-builder-field-options');
        const isDirty = () => page.evaluate(() => (window as any).wp.data.select('wpuf/form-builder').getIsDirty());
        const openOptions = async (template: string) => {
            const row = page.locator(`#form-preview-stage > ul > li.form-field-${template}`).first();
            await row.scrollIntoViewIfNeeded();
            await row.hover();
            await row.getByText('Edit', { exact: true }).first().click();
            await expect(panel.locator('.panel-field-opt').first()).toBeVisible();
            // Expand closed sections (advanced options start closed).
            const sections = panel.locator('.option-fields-section');
            for (let i = 0; i < await sections.count(); i++) {
                if (!(await sections.nth(i).locator('.option-field-section-fields').isVisible())) {
                    await sections.nth(i).locator('> h3').click();
                }
            }
        };

        // Opening each panel (country pickers, map, tooltips) writes nothing.
        for (const template of ['step_start', 'country_list_field', 'address_field', 'pricing_checkbox', 'google_map', 'signature_field']) {
            if (await page.locator(`#form-preview-stage > ul > li.form-field-${template}`).count()) {
                await openOptions(template);
            }
        }
        expect(await isDirty(), 'opening option panels leaves the form clean').toBe(false);

        // Step start: three text rows; button text lands under step_start.
        await openOptions('step_start');
        const stepInputs = panel.locator('.panel-field-opt-multi-step input[type="text"]');
        await expect(stepInputs).toHaveCount(3);
        await stepInputs.nth(1).fill('Back');

        // Country list: "Hide these" asks for a country until one is picked; a switch clears the other list.
        // Pickers are the shared Select / MultiSelect (4.4c; develop: selectize).
        await openOptions('country_list_field');
        await panel.locator('.default-country [data-slot="select-trigger"]').click();
        await page.locator('[role="option"]', { hasText: /^Bangladesh$/ }).first().click();
        await panel.getByRole('button', { name: 'Hide these' }).click();
        await expect(panel.getByText('Please select at least one country'), 'empty list alert').toBeVisible();
        await panel.locator('.country-list-selector [data-slot="smart-multi-select-trigger"]').click();
        await page.locator('[data-slot="smart-multi-select-content"] [data-slot="command-input"]').fill('Bangla');
        await page.locator('[data-slot="smart-multi-select-content"] [data-slot="command-item"]', { hasText: 'Bangladesh' }).first().click();
        await page.keyboard.press('Escape');
        await expect(panel.getByText('Please select at least one country')).toHaveCount(0);
        await panel.getByRole('button', { name: 'Only show' }).click();

        // Address: parts come from the stored field, country part has the same controls.
        await openOptions('address_field');
        const parts = panel.locator('.panel-field-opt-address > div');
        expect(await parts.count(), 'one row per stored address part').toBe(6);
        await parts.nth(4).locator('> div').first().click();
        await expect(panel.locator('.address-country-default .default-country [data-slot="select-trigger"]'), 'default country select').toBeVisible();

        // Pricing options: drag the second row above the first.
        await openOptions('pricing_checkbox');
        const labels = () => panel.locator('.option-field-option td:nth-child(2) input').evaluateAll((inputs) => inputs.map((input) => (input as HTMLInputElement).value));
        const before = await labels();
        await panel.locator('.option-field-option .sort-handler').nth(1).dragTo(panel.locator('.option-field-option').nth(0));
        await expect.poll(labels, { message: 'pricing rows reordered' }).toEqual([before[1], before[0], ...before.slice(2)]);

        await admin.doSaveBuilder();
        await admin.doClose();
        expect(errors, 'no page errors').toEqual([]);

        const fields = new ParityPage().readForm(site, formId).fields.map((field) => field.post_content as Record<string, any>);
        const byTemplate = (template: string) => fields.find((field) => template === field.template)!;
        expect(byTemplate('step_start').step_start.prev_button_text, 'previous button text stored').toBe('Back');
        const countryList = byTemplate('country_list_field').country_list;
        expect(countryList.name, 'default country stored as its code').toBe('BD');
        expect(countryList.country_list_visibility_opt_name).toBe('show');
        expect(countryList.country_select_hide_list || [], 'hide list cleared by the switch').toEqual([]);
        expect(Object.values(byTemplate('pricing_checkbox').options), 'reordered pricing options stored').toEqual([before[1], before[0], ...before.slice(2)]);
    });
});
