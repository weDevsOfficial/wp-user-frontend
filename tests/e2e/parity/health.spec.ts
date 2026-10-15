import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * Admin screens on the branch load without JS errors and without assets served
 * as HTML (task 1.8, B23). Run with every Pro module active.
 */
test.describe('Branch admin health', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('HLT0001 : builders and form lists load with no page errors or missing assets', { tag: ['@Parity', '@Test_HLT0001'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const parity = new ParityPage();
        const postForm = parity.doSeedForm(branch, 'post-form-parity.json');
        const regForm = parity.doSeedForm(branch, 'registration-form.json');
        const admin = await ParitySitePage.doOpen(browser, branch);
        const problems: Record<string, { errors: string[]; badAssets: string[] }> = {};

        for (const path of [
            `/wp-admin/admin.php?page=wpuf-post-forms&action=edit&id=${postForm}`,
            `/wp-admin/admin.php?page=wpuf-profile-forms&action=edit&id=${regForm}`,
            '/wp-admin/admin.php?page=wpuf-post-forms',
            '/wp-admin/admin.php?page=wpuf-profile-forms',
            '/wp-admin/admin.php?page=wpuf_subscription',
            '/wp-admin/admin.php?page=wpuf-settings',
        ]) {
            problems[path] = await admin.getLoadProblems(path);
        }
        await admin.doClose();

        for (const [path, found] of Object.entries(problems)) {
            expect.soft(found, path).toEqual({ errors: [], badAssets: [] });
        }
    });

    test('HLT0002 : registration list shows the Pro shortcode pair and the AI entry', { tag: ['@Parity', '@Test_HLT0002'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const formId = new ParityPage().doSeedForm(branch, 'registration-form.json');
        const admin = await ParitySitePage.doOpen(browser, branch);
        await admin.page.goto('/wp-admin/admin.php?page=wpuf-profile-forms');
        const codes = admin.page.locator('td code');
        await expect(codes.first()).toBeVisible();
        const all = await codes.allTextContents();
        const ai = await admin.page.getByRole('button', { name: 'AI Form Builder' }).count();
        await admin.doClose();

        expect(all, 'registration shortcode').toContain(`[wpuf_profile type="registration" id="${formId}"]`);
        expect(all, 'edit profile shortcode').toContain(`[wpuf_profile type="profile" id="${formId}"]`);
        expect(ai, 'AI Form Builder button').toBe(1);
    });

    test('HLT0003 : old subscriptions script handle still prints data localized on it (B24)', { tag: ['@Parity', '@Test_HLT0003'] }, () => {
        const out = parityWp(paritySite('branch'), [
            'eval',
            'wp_set_current_user( 1 ); do_action( "wpuf_load_subscription_page" ); wp_localize_script( "wpuf-admin-subscriptions", "WPUF_HLT0003", [ "ok" => 1 ] ); ob_start(); wp_print_scripts(); $o = ob_get_clean(); echo wp_json_encode( [ substr_count( $o, "js/screens/subscriptions.js" ), false !== strpos( $o, "WPUF_HLT0003" ) ] );',
            '--exec=define("WP_ADMIN",true);',
        ]);

        expect(JSON.parse(out.trim().split('\n').pop() || '[]'), 'script loaded once, alias data printed').toEqual([1, true]);
    });

    test('HLT0004 : builders print the wpuf_single_objects global like develop (B27)', { tag: ['@Parity', '@Test_HLT0004'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const parity = new ParityPage();
        const admin = await ParitySitePage.doOpen(browser, branch);
        const found: Record<string, unknown> = {};

        for (const [postType, fixture] of [['wpuf_forms', 'post-form-parity.json'], ['wpuf_profile', 'registration-form.json']]) {
            await admin.doOpenBuilder(postType, parity.doSeedForm(branch, fixture));
            found[postType] = await admin.page.evaluate(() => {
                const list = (window as unknown as { wpuf_single_objects?: unknown }).wpuf_single_objects;
                return Array.isArray(list) && list.includes('post_title');
            });
        }
        await admin.doClose();

        expect(found).toEqual({ wpuf_forms: true, wpuf_profile: true });
    });

    test('HLT0005 : subscription notices render and clear (B34)', { tag: ['@Parity', '@Test_HLT0005'] }, async ({ browser }) => {
        const admin = await ParitySitePage.doOpen(browser, paritySite('branch'));
        await admin.page.goto('/wp-admin/admin.php?page=wpuf_subscription');
        await admin.page.waitForFunction(() => !!(window as unknown as { wp?: { data?: { select: (s: string) => unknown } } }).wp?.data?.select('wpuf/subscriptions-notice'));
        await admin.page.evaluate(() => (window as unknown as { wp: { data: { dispatch: (s: string) => { addNotice: (n: object) => void } } } }).wp.data.dispatch('wpuf/subscriptions-notice').addNotice({ type: 'success', message: 'HLT0005 notice' }));
        // Shared toasts since 4.1 (sonner), where develop's notice list was.
        const toast = admin.page.locator('[data-sonner-toast]', { hasText: 'HLT0005 notice' });
        await expect(toast).toBeVisible();
        await expect(toast).toBeHidden({ timeout: 10000 });
        expect(await admin.page.evaluate(() => (window as unknown as { wp: { data: { select: (s: string) => { getNotices: () => unknown[] } } } }).wp.data.select('wpuf/subscriptions-notice').getNotices().length), 'notice removed from the store').toBe(0);
        await admin.doClose();
    });
});
