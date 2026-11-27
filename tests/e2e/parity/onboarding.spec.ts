import { test, expect, type Page } from '@playwright/test';
import { execFileSync } from 'child_process';
import * as path from 'path';
import { ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured, parityWp, type ParitySite } from '../utils/paritySites';

/**
 * Onboarding wizard, functional pass on the branch (task 4.7). The wizard is
 * new on the branch (develop has the old Setup_Wizard), so the reference for
 * what it stores is the settings screen: a legacy re-save of each section must
 * not change a value the wizard wrote (same shapes), and the gateways must be
 * the keyed map checkout reads (B10).
 *
 * The tests walk the wizard on the branch site and restore its state: one worker.
 */
const WIZARD = '/wp-admin/index.php?page=wpuf-onboarding';

function siteState(site: ParitySite, mode: 'save' | 'restore', file: string) {
    return execFileSync('wp', [`--path=${site.wpPath}`, 'eval-file', path.join(parityDir, 'wp', 'site-state.php')], {
        encoding: 'utf-8',
        env: { ...process.env, WPUF_STATE_MODE: mode, WPUF_STATE_FILE: file },
        stdio: ['pipe', 'pipe', 'pipe'],
    });
}

const option = (site: ParitySite, name: string) => JSON.parse(parityWp(site, ['eval', `echo wp_json_encode( get_option( '${name}', null ) );`]));

async function submitStep(page: Page) {
    await Promise.all([page.waitForNavigation(), page.locator('button[name="wpuf_onboarding_save"].wpuf-onboarding-btn-primary').first().click()]);
}

const stepOf = (page: Page) => new URL(page.url()).searchParams.get('step') || 'features';

test.describe('Onboarding wizard', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');
    test.describe.configure({ mode: 'serial' });

    test('ONB0001 : a full run stores the gateways as checkout reads them and every value in the settings screen\'s shape', { tag: ['@Parity', '@Test_ONB0001'] }, async ({ browser }) => {
        test.setTimeout(420_000);
        const site = paritySite('branch');
        const stateFile = test.info().outputPath('state.json');
        siteState(site, 'save', stateFile);
        const SECTIONS = ['wpuf_general', 'wpuf_frontend_posting', 'wpuf_dashboard', 'wpuf_my_account', 'wpuf_profile', 'wpuf_payment'];
        const before = Object.fromEntries(SECTIONS.map((name) => [name, option(site, name) || {}])) as Record<string, Record<string, unknown>>;

        try {
            parityWp(site, ['eval', 'foreach ( [ "wpuf_onboarding_progress", "wpuf_onboarding_features", "wpuf_onboarding_completed", "wpuf_onboarding_plugin_errors" ] as $o ) { delete_option( $o ); }']);
            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            const errors: string[] = [];
            page.on('pageerror', (error) => errors.push(error.message));
            const visited: string[] = [];

            await page.goto(WIZARD);
            for (let guard = 0; guard < 8; guard++) {
                const step = stepOf(page);
                visited.push(step);
                if ('features' === step) {
                    const boxes = page.locator('input[name="features[]"]');
                    for (let i = 0; i < (await boxes.count()); i++) {
                        await expect(boxes.nth(i), 'every feature ticked on the first visit').toBeChecked();
                    }
                } else if ('post_form' === step) {
                    const template = page.locator('#wpuf-onboarding-template');
                    const values = await template.locator('option').evaluateAll((els) => els.map((el) => (el as HTMLOptionElement).value));
                    await template.selectOption(values.find((value) => value && 'skip' !== value) || 'skip');
                    await page.locator('input[name="enable_post_edit"]').check();
                    await page.locator('input[name="enable_post_del"]').uncheck();
                } else if ('registration' === step) {
                    for (const id of ['#wpuf-onboarding-login-page', '#wpuf-onboarding-reg-page', '#wpuf-onboarding-account-page']) {
                        const select = page.locator(id);
                        if (await select.locator('option[value="create"]').count()) {
                            await select.selectOption('create');
                        }
                    }
                    await page.locator('input[name="autologin_after_registration"]').check();
                } else if ('common' === step) {
                    await page.locator('input[name="add_logout_menu"]').uncheck();
                    await page.locator('#wpuf-onboarding-enable-payment').check();
                    const gateways = page.locator('input[name="active_gateways[]"]');
                    for (let i = 0; i < (await gateways.count()); i++) {
                        const id = await gateways.nth(i).getAttribute('value');
                        await gateways.nth(i).setChecked(['paypal', 'bank'].includes(id || ''), { force: true });
                    }
                } else if ('plugins' === step) {
                    // Nothing is installed from the test.
                    const plugins = page.locator('input[name="plugins[]"]');
                    for (let i = 0; i < (await plugins.count()); i++) {
                        await plugins.nth(i).uncheck({ force: true });
                    }
                } else if ('ready' === step) {
                    break;
                }
                await submitStep(page);
            }

            expect(visited, 'every step in order').toEqual(expect.arrayContaining(['features', 'post_form', 'registration', 'common', 'ready']));
            expect(errors, 'no script error in the wizard').toEqual([]);
            await admin.doClose();

            const written = Object.fromEntries(SECTIONS.map((name) => [name, option(site, name) || {}])) as Record<string, Record<string, unknown>>;

            // B10: the gateways are the keyed map checkout reads.
            expect(written.wpuf_payment.active_gateways, 'active gateways as a keyed map').toEqual({ paypal: 'paypal', bank: 'bank' });
            expect(written.wpuf_payment.enable_payment).toBe('on');
            const checkout = JSON.parse(parityWp(site, ['eval', '$p = new WeDevs\\Wpuf\\Frontend\\Payment(); echo wp_json_encode( array_keys( $p->get_active_gateways() ) );']));
            expect(checkout.sort(), 'checkout offers the picked gateways').toEqual(['bank', 'paypal']);

            expect(written.wpuf_dashboard).toMatchObject({ enable_post_edit: 'yes', enable_post_del: 'no' });
            expect(written.wpuf_profile.autologin_after_registration).toBe('on');
            for (const [section, key] of [['wpuf_profile', 'login_page'], ['wpuf_frontend_posting', 'default_post_form']]) {
                const id = Number(written[section][key]);
                expect(id, `${section}.${key} points at a post`).toBeGreaterThan(0);
                expect(parityWp(site, ['eval', `echo get_post_status( ${id} );`]).trim(), `${section}.${key} exists`).toBe('publish');
            }

            // Shapes: a settings-screen re-save of each section (the React screen's REST
            // save, same sanitising store) leaves what the wizard wrote untouched.
            const changed: Record<string, string[]> = {};
            for (const name of SECTIONS) {
                changed[name] = Object.keys(written[name]).filter((key) => JSON.stringify(written[name][key]) !== JSON.stringify(before[name][key]));
            }
            const screen = await ParitySitePage.doOpen(browser, site);
            await screen.page.goto('/wp-admin/admin.php?page=wpuf-settings');
            await screen.page.waitForFunction(() => !!(window as any).wp?.apiFetch);
            for (const name of SECTIONS.filter((section) => changed[section].length)) {
                await screen.page.evaluate(async (section) => {
                    const res = await (window as any).wp.apiFetch({ path: '/wpuf/v1/settings' });
                    await (window as any).wp.apiFetch({ path: '/wpuf/v1/settings', method: 'POST', data: { settings: { [section]: res.data.values[section] || {} } } });
                }, name);
            }
            await screen.doClose();
            // Page and form ids are integers until the first settings save turns them
            // into the posted strings: develop's Admin_Installer::init_pages() has
            // always stored them like that, and every reader casts them. Anything
            // else must be identical.
            const sameValue = (wizard: unknown, screen: unknown) => ('number' === typeof wizard ? String(wizard) === String(screen) : JSON.stringify(wizard) === JSON.stringify(screen));
            // `install_wpuf_pages` is the installer's own marker in wpuf_general, not a settings field.
            const MARKERS = ['install_wpuf_pages'];
            const differing: string[] = [];
            for (const name of SECTIONS) {
                const after = option(site, name) || {};
                for (const key of changed[name].filter((item) => !MARKERS.includes(item))) {
                    if (!sameValue(written[name][key], after[key])) {
                        differing.push(`${name}.${key}: wizard ${JSON.stringify(written[name][key])}, settings screen ${JSON.stringify(after[key])}`);
                    }
                }
            }
            expect(differing, 'values the wizard wrote are what the settings screen stores').toEqual([]);
            console.log('ONB0001 wrote', JSON.stringify(changed));
        } finally {
            siteState(site, 'restore', stateFile);
        }
    });

    test('ONB0002 : a running User Directory is shown ticked and is switched off only after the confirmation', { tag: ['@Parity', '@Test_ONB0002'] }, async ({ browser }) => {
        test.setTimeout(240_000);
        const site = paritySite('branch');
        const stateFile = test.info().outputPath('state.json');
        siteState(site, 'save', stateFile);
        const active = () => 'yes' === parityWp(site, ['eval', 'echo ( new WeDevs\\Wpuf\\Admin\\Onboarding() )->is_directory_active() ? "yes" : "no";']).trim();

        try {
            // The directory runs, but an earlier wizard run did not pick it.
            parityWp(site, ['eval', '( new WeDevs\\Wpuf\\Admin\\Onboarding() )->toggle_directory( true ); update_option( "wpuf_onboarding_features", [ "post_form", "registration", "payments" ] );']);
            expect(active(), 'directory active before the test').toBe(true);

            const admin = await ParitySitePage.doOpen(browser, site);
            const page = admin.page;
            await page.goto(`${WIZARD}&step=features`);
            const box = page.locator('input[name="features[]"][value="user_directory"]');
            const confirm = page.locator('#wpuf-onboarding-directory-confirm');
            await expect(box, 'shown ticked while the module is active').toBeChecked();
            await expect(confirm).toBeHidden();

            // Untick, then "Keep it on".
            await box.uncheck({ force: true });
            await expect(confirm, 'asks before switching it off').toBeVisible();
            await confirm.getByRole('button', { name: 'Keep it on' }).click();
            await expect(box).toBeChecked();
            await expect(confirm).toBeHidden();

            // Untick and submit without answering: nothing is switched off.
            await box.uncheck({ force: true });
            await submitStep(page);
            expect(active(), 'unanswered question keeps the directory').toBe(true);
            expect(option(site, 'wpuf_onboarding_features'), 'still picked').toContain('user_directory');

            // A request without the confirmation (no script) keeps it too.
            await page.goto(`${WIZARD}&step=features`);
            await page.evaluate(() => {
                const input = document.querySelector('input[name="features[]"][value="user_directory"]') as HTMLInputElement;
                input.removeAttribute('data-confirm-off');
                input.checked = false;
                const form = input.form as HTMLFormElement;
                const save = document.createElement('input');
                save.type = 'hidden';
                save.name = 'wpuf_onboarding_save';
                save.value = '1';
                form.appendChild(save);
                HTMLFormElement.prototype.submit.call(form);
            });
            await page.waitForLoadState('load');
            expect(active(), 'server keeps the directory without the confirmation').toBe(true);

            // Untick, "Turn it off", submit: switched off.
            await page.goto(`${WIZARD}&step=features`);
            await box.uncheck({ force: true });
            await confirm.getByRole('button', { name: 'Turn it off' }).click();
            await expect(box).not.toBeChecked();
            await submitStep(page);
            expect(active(), 'switched off after the confirmation').toBe(false);
            expect(option(site, 'wpuf_onboarding_features')).not.toContain('user_directory');

            // Not active: no question, ticking switches it on again.
            await page.goto(`${WIZARD}&step=features`);
            await expect(box).not.toBeChecked();
            await expect(confirm).toHaveCount(0);
            await box.check({ force: true });
            await submitStep(page);
            expect(active(), 'ticking switches it on').toBe(true);
            await admin.doClose();
        } finally {
            siteState(site, 'restore', stateFile);
        }
    });
});
