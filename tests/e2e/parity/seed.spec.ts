import { test } from '@playwright/test';
import { ParityPage } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

/**
 * Parity harness smoke (task 0.4a): the seed writes the same stored form on both
 * sites, so later parity specs start from identical data.
 */
test.describe('Parity seed', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    for (const fixture of ['post-form-parity.json', 'post-form-all-fields.json', 'registration-form.json']) {
        test(`PAR0001 : seeding ${fixture} stores identical data on develop and branch`, { tag: ['@Parity', '@Test_PAR0001'] }, () => {
            const parity = new ParityPage();
            const develop = paritySite('develop');
            const branch = paritySite('branch');

            const developDump = parity.readForm(develop, parity.doSeedForm(develop, fixture));
            const branchDump = parity.readForm(branch, parity.doSeedForm(branch, fixture));

            parity.validateFormsEqual(developDump, branchDump);
        });
    }
});
