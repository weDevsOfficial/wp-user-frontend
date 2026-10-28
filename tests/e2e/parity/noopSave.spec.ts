import { test } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * G3 for the builders (task 0.4b): opening a form and saving it untouched must not
 * change anything stored (owner decision Q6, ground-truth §3 R10). Develop's own
 * untouched save is recorded as an attachment for reference: it canonicalizes
 * settings, which the branch must not copy.
 */
test.describe('Parity no-op save', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    for (const fixture of ['post-form-parity.json', 'post-form-all-fields.json', 'registration-form.json']) {
        test(`PAR0002 : untouched builder save keeps ${fixture} byte-identical on the branch`, { tag: ['@Parity', '@Test_PAR0002'] }, async ({ browser }) => {
            const parity = new ParityPage();
            const results: Record<string, { before: FormDump; after: FormDump; saved: boolean }> = {};

            for (const name of ['develop', 'branch'] as const) {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, fixture);
                const before = parity.readForm(site, formId);
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder(before.post_type, formId);
                const saved = await admin.doSaveBuilder({ untouched: true });
                await admin.doClose();
                results[name] = { before, after: parity.readForm(site, formId), saved };
            }

            for (const name of ['develop', 'branch'] as const) {
                await test.info().attach(`${name}-noop-save.json`, { path: parity.doWriteJson(test.info().outputPath(`${name}-noop-save.json`), results[name]) });
            }

            // Saved: the store drops the seeded new-field markers. Not saved (Save stays
            // disabled while nothing changed): the stored form is untouched, markers included.
            const expected = results.branch.saved ? parity.withoutNewFieldMarkers(results.branch.before) : results.branch.before;
            parity.validateFormsEqual(expected, results.branch.after);
        });

    }
});
