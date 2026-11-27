import { test } from '@playwright/test';
import * as path from 'path';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * Fixture generator, not a check: builds forms through develop's own builder UI
 * so every stored shape is genuine, then writes them to parity/fixtures.
 * Run on demand: PARITY_GENERATE=1 npx playwright test --project=parity generateFixtures
 */
test.describe('Parity fixture generator', () => {
    test.skip(!paritySitesConfigured() || !process.env.PARITY_GENERATE, 'set PARITY_GENERATE=1 to regenerate fixtures');

    test('PARGEN01 : post form with every palette field type (develop UI)', { tag: ['@ParityGenerate'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const develop = paritySite('develop');
        const admin = await ParitySitePage.doOpen(browser, develop);
        const formId = await admin.doOpenNewBuilder('wpuf_forms');
        const types = await admin.getPaletteFieldTypes();
        const skipped: string[] = [];

        for (const type of types) {
            if (!(await admin.doAddFieldFromPalette(type))) {
                skipped.push(type);
            }
        }

        // Save, reload, save again: the second save writes each field's own row id
        // (and select normalization) as every long-lived develop form has it.
        await admin.doSaveBuilder();
        await admin.doOpenBuilder('wpuf_forms', formId);
        await admin.doSaveBuilder();
        await admin.doClose();

        const dump = parity.readForm(develop, formId);
        dump.post_title = 'Parity All Fields';
        parity.doWriteJson(path.join(parityDir, 'fixtures', 'post-form-all-fields.json'), dump);
        test.info().annotations.push({ type: 'fields', description: `${dump.fields.length} stored, skipped: ${skipped.join(', ') || 'none'}` });
    });
});
