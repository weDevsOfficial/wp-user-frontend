import { test } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { paritySite, paritySitesConfigured, parityProActive } from '../utils/paritySites';

/**
 * G2 for builder field options (task 0.4c-2), one test per field type: the same
 * deterministic edit on every option row of that field, made through each site's
 * own builder UI, must store the same values on the branch as on develop. The
 * option rows the panel shows are compared too (a missing row = missing UI on the
 * branch). Conditional logic rows are covered by PAR0028 / PAR0029
 * (parity/fieldConditions.spec.ts, task 4.4d).
 */
const FIXTURE = 'post-form-all-fields.json';

// Field types the filler cannot drive the same way on both builders (none since
// task 0.4c-3; add `template: 'reason'` here to skip one).
const HARNESS_GAPS: Record<string, string> = {};
const SKIP_ROWS = ['panel-field-opt-conditional-logic'];

const PRO_ACTIVE = paritySitesConfigured() ? parityProActive() : true;

const templates = new ParityPage().readFixture(FIXTURE).fields
    .map((field) => (field.post_content as { template: string }).template);

test.describe('Parity field fill', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    // Each test seeds its own form and touches no site-wide option, so the tests
    // may run on several workers (`npm run test:parity` runs them on 4 workers; the local PHP pool needs room for 8 builders).
    test.describe.configure({ mode: 'parallel' });

    for (const [index, template] of templates.entries()) {
        test(`PAR0004 : field ${String(index).padStart(2, '0')} ${template}: filling every option stores the same on branch as on develop`, { tag: ['@Parity', '@Test_PAR0004'] }, async ({ browser }) => {
            test.skip(template in HARNESS_GAPS, `harness gap: ${HARNESS_GAPS[template]}`);
            const parity = new ParityPage();
            const stored: Partial<Record<'develop' | 'branch', FormDump>> = {};
            const rows: Partial<Record<'develop' | 'branch', string[]>> = {};

            // Both sites at once: they are separate WordPress installs.
            await Promise.all((['develop', 'branch'] as const).map(async (name) => {
                const site = paritySite(name);
                // Own title per test: seeding replaces forms with the same title.
                const formId = parity.doSeedForm(site, FIXTURE, `Parity field fill ${String(index).padStart(2, '0')}`);
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder('wpuf_forms', formId);
                const opened = await admin.doOpenFieldSettings(index);
                rows[name] = opened
                    ? await admin.doFillFieldOptions(String(index), SKIP_ROWS)
                    : ['stage item not visible'];
                // Nothing edited when the field is not on the stage (hidden fields).
                await admin.doSaveBuilder({ untouched: !opened });
                await admin.doClose();
                stored[name] = parity.readForm(site, formId);
            }));

            await test.info().attach('rows.json', { path: parity.doWriteJson(test.info().outputPath('rows.json'), rows) });
            await test.info().attach('stored.json', { path: parity.doWriteJson(test.info().outputPath('stored.json'), stored) });

            // Free-only run: develop's save drops every stored form setting it does not
            // render without Pro (expiration, AI review, pricing...) and adds its shown
            // defaults; the branch keeps the stored settings (Q6). This test edits field
            // options only, so the form settings are left out of the comparison.
            if (!PRO_ACTIVE) {
                (stored.develop!.meta as Record<string, unknown>).wpuf_form_settings = (stored.branch!.meta as Record<string, unknown>).wpuf_form_settings;
            }

            // Skipped row types (conditional logic: PAR0028) are left out on both sides.
            const kept = (list: string[] = []) => parity.withoutAgreedRowDeviations(list.filter((row) => !SKIP_ROWS.some((type) => row.startsWith(`${type} |`))));
            parity.validateRowsEqual({ [template]: kept(rows.develop) }, { [template]: kept(rows.branch) });
            parity.validateFormsEqual(
                parity.withoutAgreedDeviations(stored.develop as FormDump, stored.branch as FormDump, parity.readFixture(FIXTURE)),
                stored.branch as FormDump,
            );
        });
    }
});
