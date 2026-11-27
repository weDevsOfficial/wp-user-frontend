import { test, expect } from '@playwright/test';
import { spawnSync } from 'child_process';
import * as fs from 'fs';
import * as path from 'path';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * G5 runtime gate (task 1.9): crawl the WPUF admin screens on develop and on
 * the branch in the same run (same fixtures, contract recorder mu-plugin on),
 * then diff develop -> branch. The diff is attached; the loss count may not
 * rise above CONTRACT_BASELINE (346, the 2026-10-02 snapshot diff). Accepted
 * losses come from an owner-approved allow file (CONTRACT_ALLOW).
 */
async function crawl(browser: import('@playwright/test').Browser, name: 'develop' | 'branch', label: string): Promise<string> {
    const site = paritySite(name);
    const parity = new ParityPage();
    const postForm = parity.doSeedForm(site, 'post-form-parity.json');
    const regForm = parity.doSeedForm(site, 'registration-form.json');
    const screens = [
        'wp-user-frontend',
        'wpuf-modules',
        'wpuf-post-forms',
        'wpuf-post-forms&action=add-new',
        `wpuf-post-forms&action=edit&id=${postForm}`,
        'wpuf-profile-forms',
        `wpuf-profile-forms&action=edit&id=${regForm}`,
        'wpuf-settings',
        'wpuf_subscription',
        'wpuf_subscribers',
        'wpuf_tools',
        'wpuf_transaction',
    ];

    // Recorder state of the admin app's redirect chain (runtime-recorder.php).
    parityWp(site, ['option', 'delete', 'wpuf_contract_pending', 'wpuf_contract_last']);
    parityWp(site, ['option', 'update', 'wpuf_contract_record', label]);
    const admin = await ParitySitePage.doOpen(browser, site);
    // One untimed visit first so first-load writes (option defaults) are not recorded.
    parityWp(site, ['option', 'delete', 'wpuf_contract_record']);
    await admin.page.goto('/wp-admin/admin.php?page=wp-user-frontend', { waitUntil: 'networkidle' });
    parityWp(site, ['option', 'update', 'wpuf_contract_record', label]);
    for (const screen of screens) {
        await admin.page.goto(`/wp-admin/admin.php?page=${screen}`, { waitUntil: 'networkidle' });
    }
    await admin.doClose();
    parityWp(site, ['option', 'delete', 'wpuf_contract_record', 'wpuf_contract_pending', 'wpuf_contract_last']);

    return path.join(site.wpPath, 'wp-content', 'uploads', 'wpuf-contracts', label);
}

test.describe('Runtime contract', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('CTR0001 : admin screens keep the develop runtime contract (hooks, handles, globals)', { tag: ['@Parity', '@Test_CTR0001'] }, async ({ browser }) => {
        const label = `ctr-${Date.now()}`;
        const developDir = await crawl(browser, 'develop', label);
        const branchDir = await crawl(browser, 'branch', label);
        // A crawl that recorded nothing would diff two empty sets and pass with 0.
        for (const dir of [developDir, branchDir]) {
            const recorded = fs.existsSync(dir) ? fs.readdirSync(dir).filter((file) => file.endsWith('.json')).length : 0;
            expect(recorded, `contract recordings in ${dir}`).toBeGreaterThan(0);
        }
        const allow = process.env.CONTRACT_ALLOW ? [`--allow=${process.env.CONTRACT_ALLOW}`] : [];
        const run = spawnSync('php', [path.resolve(parityDir, '..', '..', 'contracts', 'diff.php'), developDir, branchDir, ...allow], { encoding: 'utf-8' });
        const diff = run.stdout || '';
        await test.info().attach('runtime-diff.txt', { body: diff, contentType: 'text/plain' });

        const lost = diff.split('\n').filter((line) => line.trim().startsWith('LOST')).length;
        console.log(`CTR0001: ${lost} runtime losses (baseline ${process.env.CONTRACT_BASELINE || 346})`);
        expect(lost, 'runtime losses may not grow').toBeLessThanOrEqual(Number(process.env.CONTRACT_BASELINE || 346));
    });
});
