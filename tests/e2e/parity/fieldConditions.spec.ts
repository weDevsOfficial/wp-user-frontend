import { test, expect, type Locator, type Page } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * Per-field conditional logic (task 4.4d, B14 / B11): the same rule edits made
 * through each builder's own panel (develop: Vue field-conditional-logic with
 * native selects; branch: React on the shared wrappers) store the same
 * `wpuf_cond` on both sites; opening a panel writes nothing.
 */
const FIXTURE = 'post-form-cond-logic.json';

/** Open a stage field's options by its label and expand the closed sections. */
async function openField(page: Page, label: string): Promise<Locator> {
    const rows = page.locator('#form-preview-stage li[class*="form-field-"]');
    for (let i = 0; i < await rows.count(); i++) {
        const row = rows.nth(i);
        if ((await row.innerText()).split('\n').some((line) => line.trim() === label)) {
            await row.scrollIntoViewIfNeeded();
            await row.hover();
            await row.locator(':scope > div:last-child').getByText('Edit', { exact: true }).first().click();
            break;
        }
    }
    const panel = page.locator('.wpuf-form-builder-field-options');
    await expect(panel.locator('.panel-field-opt').first()).toBeVisible();
    const sections = panel.locator('.option-fields-section');
    for (let i = 0; i < await sections.count(); i++) {
        if (!(await sections.nth(i).locator('.option-field-section-fields').first().isVisible())) {
            await sections.nth(i).locator('> h3').click();
        }
    }
    return panel.locator('.panel-field-opt-conditional-logic');
}

/** Pick an option by its text: native <select> (develop) or the Select wrapper (branch). */
async function pick(page: Page, scope: Locator, label: string) {
    const native = scope.locator('select');
    if (await native.count()) {
        await native.first().selectOption({ label });
        return;
    }
    await scope.locator('[data-slot="select-trigger"]').first().click();
    await page.locator('[data-slot="select-item"]:visible').filter({ hasText: new RegExp(`^\\s*${label}\\s*$`) }).first().click();
}

/** All / Any: develop's field-dropdown or the Select wrapper. */
async function pickLogic(page: Page, block: Locator, label: string) {
    const dropdown = block.locator('.wpuf-dropdown');
    if (await dropdown.count()) {
        await dropdown.first().click();
        await dropdown.first().locator('li', { hasText: label }).first().click();
        return;
    }
    await pick(page, block.locator('.condiotional-logic-container > div').first(), label);
}

test.describe('Parity field conditional logic', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0028 : field conditional logic rules edit and store like develop', { tag: ['@Parity', '@Test_PAR0028'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Partial<Record<'develop' | 'branch', FormDump>> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE);
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await admin.doOpenBuilder('wpuf_forms', formId);

            // Opened only: nothing stored changes.
            await openField(page, 'City');
            await openField(page, 'Bio');

            // Yes alone: only condition_status changes.
            const target = await openField(page, 'Target');
            await target.getByRole('radio', { name: 'Yes', exact: true }).click();

            const block = await openField(page, 'Has Rules');
            const row = (n: number) => block.locator('.condiotional-logic-repeater > li').nth(n);
            await expect(row(1)).toBeVisible();
            await pickLogic(page, block, 'All');
            await pick(page, row(0).locator('.cond-option'), 'Red');
            await pick(page, row(1).locator('.cond-operator'), 'has any value');

            // New rows go to the end; a picked field brings its first operator / option.
            await row(0).locator('.wpuf-repeater-add').click();
            await pick(page, row(2).locator('.cond-field'), 'Size');
            await pick(page, row(2).locator('.cond-option'), 'Large');
            await row(0).locator('.wpuf-repeater-add').click();
            await pick(page, row(3).locator('.cond-field'), 'Age');
            await pick(page, row(3).locator('.cond-operator'), 'is not');
            await row(3).locator('.cond-option input[type="text"]').fill('7');
            await row(0).locator('.wpuf-repeater-add').click();
            await pick(page, row(4).locator('.cond-field'), 'Bio');
            await pick(page, row(4).locator('.cond-operator'), 'contains');
            await row(4).locator('.cond-option input[type="text"]').fill('hi');
            // A blank row stays blank.
            await row(0).locator('.wpuf-repeater-add').click();
            // Remove the City rule.
            await row(1).locator('.wpuf-repeater-remove').click();
            await expect(block.locator('.condiotional-logic-repeater > li')).toHaveCount(5);

            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = parity.readForm(site, formId);
        }));

        await test.info().attach('stored.json', { path: parity.doWriteJson(test.info().outputPath('stored.json'), stored) });

        const cond = (form: FormDump, label: string) => form.fields.map((field) => field.post_content as Record<string, any>).find((field) => label === field.label)!.wpuf_cond;
        expect(cond(stored.branch as FormDump, 'Has Rules').cond_field, 'rules after the edits').toEqual(['qa_color', 'qa_size', 'qa_age', 'qa_bio', '']);
        parity.validateFormsEqual(
            parity.withoutAgreedDeviations(stored.develop as FormDump, stored.branch as FormDump, parity.readFixture(FIXTURE)),
            stored.branch as FormDump,
        );
    });

    test('PAR0029 : a taxonomy rule stores the first term and its title (develop left the title stale)', { tag: ['@Parity', '@Test_PAR0029'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const site = paritySite('branch');
        const formId = parity.doSeedForm(site, FIXTURE);
        const admin = await ParitySitePage.doOpen(browser, site);
        await admin.doOpenBuilder('wpuf_forms', formId);

        const block = await openField(admin.page, 'Target');
        await block.getByRole('radio', { name: 'Yes', exact: true }).click();
        const row = block.locator('.condiotional-logic-repeater > li').first();
        await pick(admin.page, row.locator('.cond-field'), 'Category');
        const shown = (await row.locator('.cond-option [data-slot="select-trigger"]').innerText()).trim();

        await admin.doSaveBuilder();
        await admin.doClose();

        const target = parity.readForm(site, formId).fields.map((field) => field.post_content as Record<string, any>).find((field) => 'Target' === field.label)!;
        expect(target.wpuf_cond.cond_field).toEqual(['category']);
        expect(typeof target.wpuf_cond.cond_option[0], 'term id stored as a number, as develop').toBe('number');
        expect(target.wpuf_cond.option_title[0], 'title = the shown first term').toBe(shown);
    });
});
