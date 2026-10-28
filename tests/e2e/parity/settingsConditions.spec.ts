import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * Settings conditions (task 4.4e): every toggle / checkbox / select of a
 * settings tab shows and hides the same rows on the branch as on develop
 * (develop: form-builder.js FormDependencyHandler + Pro's
 * form-builder-wpuf-forms.js), nested conditions included.
 */
const FIXTURE = 'post-form-parity.json';
const TABS = ['General', 'Payment Settings', 'Notification Settings', 'Display Settings', 'Advanced', 'Post Expiration', 'AI Review', 'N8N', 'SMS'];

test.describe('Parity settings conditions', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');
    test.describe.configure({ mode: 'parallel' });

    for (const [index, tab] of TABS.entries()) {
        test(`PAR0026 : settings tab "${tab}": every condition shows the same rows as develop`, { tag: ['@Parity', '@Test_PAR0026'] }, async ({ browser }) => {
            test.setTimeout(240_000);
            const parity = new ParityPage();
            const lines: Partial<Record<'develop' | 'branch', string[]>> = {};

            await Promise.all((['develop', 'branch'] as const).map(async (name) => {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, FIXTURE, `Parity settings conditions ${index}`);
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder('wpuf_forms', formId);
                await admin.doOpenBuilderSettings([]);
                lines[name] = await admin.doProbeSettingsConditions(tab);
                await admin.doClose();
            }));

            await test.info().attach('conditions.json', { path: parity.doWriteJson(test.info().outputPath('conditions.json'), lines) });
            // Develop's Pro script shows every expiration row when expiration turns on,
            // after its own rule hid the message (it needs "Send post expiration email"),
            // so develop showed the e-mail message with the e-mail off. The branch keeps
            // the rule (agreed, 4.4e): a develop line that differs only by that row folds.
            const develop = (lines.develop || []).map((line, i) => {
                const branchLine = (lines.branch || [])[i];
                return branchLine && line.replace(' ; Post Expired Message', '') === branchLine ? branchLine : line;
            });
            expect(lines.branch, `${tab}: rows shown per control state`).toStrictEqual(develop);
        });
    }
});
