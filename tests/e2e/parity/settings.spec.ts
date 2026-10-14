import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * React settings save stores what the legacy screen stored (task 1.4, B3 to B7).
 */
test.describe('Branch settings save', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('SET0001 : settings save keeps the legacy shapes and untouched data', { tag: ['@Parity', '@Test_SET0001'] }, () => {
        const result = JSON.parse(parityWp(paritySite('branch'), [
            'eval-file', path.join(parityDir, 'wp', 'check-settings-save.php'), '--exec=define("WP_ADMIN",true);',
        ]));

        expect(result.multicheck, 'multicheck stored as key => key').toEqual({ post_title: 'post_title' });
        expect(result.multicheck_empty, 'empty multicheck stored as the hidden input').toBe('');
        expect(result.multiselect_empty, 'empty multiselect left out').toBe('absent');
        expect(result.custom_css, 'custom CSS keeps selectors').toBe('ul > li { color: red; }');
        expect(result.untouched, 'untouched field keeps its backslash').toBe('keep\\me');
        expect(result.footer_text, 'email footer keeps HTML and backslashes').toBe('<p>Sent by <a href="https://example.com">C:\\Shop</a></p>');
        expect(result.tax_base, 'edited base saved').toEqual({ country: 'US', state: 'NY' });
        expect(result.tax_rates_kept, 'unchanged rates keep the stored form').toEqual({ 3: { country: 'US', state: 'CA', rate: '7.25' } });
        expect(result.tax_rates_new, 'new rates keep the typed text, clamped to 100').toEqual([
            { country: 'GB', state: '', rate: '20' },
            { country: 'DE', state: '', rate: '100' },
        ]);
    });

    test('SET0002 : settings screen posts only the edited field and writes nothing else', { tag: ['@Parity', '@Test_SET0002'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const dump = () => JSON.parse(parityWp(branch, [
            'eval', 'global $wpdb; $o = []; foreach ( $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE \'wpuf%\' OR option_name = \'n8n\'" ) as $n ) { $o[ $n ] = get_option( $n ); } echo wp_json_encode( $o );',
        ]));
        const before = dump();
        const admin = await ParitySitePage.doOpen(browser, branch);
        const { field, body } = await admin.doEditFirstSettingsText('SET0002 value');
        await admin.doClose();
        const after = dump();

        const sections = Object.keys(body.settings);
        expect(sections.length, 'one section posted').toBe(1);
        expect(Object.keys(body.settings[sections[0]]), 'only the edited field posted').toEqual([field]);
        expect(body.extra, 'no own-option data posted').toEqual({});

        const changed = Object.keys(after).filter((name) => JSON.stringify(after[name]) !== JSON.stringify(before[name]));
        expect(changed, 'only the edited option changed').toEqual([sections[0]]);
        expect(after[sections[0]][field]).toBe('SET0002 value');
        // Existing fields keep their values; a field missing before may now hold
        // its default (legacy std on the section's first save, SET0004).
        const othersBefore = { ...(before[sections[0]] ?? {}) };
        delete othersBefore[field];
        const keptAfter = Object.fromEntries(Object.keys(othersBefore).map((key) => [key, after[sections[0]][key]]));
        expect(keptAfter, 'existing fields untouched').toEqual(othersBefore);

        if (before[sections[0]] === undefined) {
            parityWp(branch, ['option', 'delete', sections[0]]);
        } else {
            parityWp(branch, ['option', 'update', sections[0], JSON.stringify(before[sections[0]]), '--format=json']);
        }
    });

    test('SET0003 : login colors store nothing by default and preview the chosen layout (B22)', { tag: ['@Parity', '@Test_SET0003'] }, () => {
        const out = parityWp(paritySite('branch'), [
            'eval',
            'wp_set_current_user( 1 ); $d = rest_do_request( new WP_REST_Request( "GET", "/wpuf/v1/settings" ) )->get_data(); foreach ( $d["data"]["fields"]["wpuf_profile"] as $f ) { if ( isset( $f["name"] ) && "wpuf_login_label_text_color" === $f["name"] ) { echo wp_json_encode( [ $f["default"], $f["std"], $f["preview_by"], $f["preview"]["layout2"] ] ); } }',
            '--exec=define("WP_ADMIN",true);',
        ]);

        expect(JSON.parse(out.trim().split('\n').pop() || '[]'), 'empty std like develop, layout preview for React').toEqual(['', '', 'wpuf_login_form_layout', '#ffffff']);
    });

    test('SET0004 : first save stores missing defaults; desc HTML is filtered (1.15)', { tag: ['@Parity', '@Test_SET0004'] }, () => {
        const out = parityWp(paritySite('branch'), [
            'eval-file', path.join(parityDir, 'wp', 'check-settings-defaults.php'), '--exec=define("WP_ADMIN",true);',
        ]);

        expect(JSON.parse(out.trim().split('\n').pop() || '{}')).toEqual({
            default_stored: 'set4-std',
            edited: 'x',
            desc: 'See <a href="https://example.com">docs</a>alert(1)',
        });
    });

    test('SET0005 : React settings apply the legacy section hooks, tax and role filters (1.16)', { tag: ['@Parity', '@Test_SET0005'] }, () => {
        const out = parityWp(paritySite('branch'), [
            'eval-file', path.join(parityDir, 'wp', 'check-settings-legacy-hooks.php'), '--exec=define("WP_ADMIN",true);',
        ]);

        expect(JSON.parse(out.trim().split('\n').pop() || '{}')).toEqual({
            top: '<p class="set5">SET5 top</p>',
            bottom: '<p>SET5 bottom</p>',
            fired_other: true,
            tax_country: 'SET5',
            roles: { set5: 'SET5 role' },
        });
    });

    test('SET0006 : Pro settings and Feature_Lock reach WPUF REST routes without a request URL (1.22, B30)', { tag: ['@Parity', '@Test_SET0006'] }, () => {
        const run = (extra: string[]) => JSON.parse(parityWp(paritySite('branch'), ['eval-file', path.join(parityDir, 'wp', 'check-pro-rest-boot.php'), ...extra]).trim().split('\n').pop() || '{}');

        expect(run([]), 'valid license').toEqual({ pro_section: true, locked: false, sections: true });
        expect(run(['invalid']), 'inactive license: the lock hides Pro sections').toEqual({ pro_section: false, locked: true, sections: true });
    });
    test('SET0008 : each control type edited on the React screen stores what the legacy screen stores on develop (4.6a, G2b)', { tag: ['@Parity', '@Test_SET0008'] }, async ({ browser }) => {
        test.setTimeout(240_000);
        const OPTIONS = ['wpuf_general', 'wpuf_dashboard', 'wpuf_payment'];
        // section => field => value (checkbox: 'on'; multi select: list).
        const EDITS: Record<string, Record<string, string | string[]>> = {
            wpuf_general: { load_script: 'on', custom_css: 'a { color: red; }', admin_access: 'manage_options', show_admin_bar: ['administrator', 'editor', 'author'] },
            wpuf_dashboard: { per_page: '7' },
            wpuf_payment: { enable_payment: 'on', currency: 'BDT', wpuf_price_num_decimals: '3' },
        };
        const sites = { develop: paritySite('develop'), branch: paritySite('branch') };
        const read = (site: typeof sites.develop) => JSON.parse(parityWp(site, ['eval', `$o = []; foreach ( ${JSON.stringify(OPTIONS).replace('[', '[ ').replace(']', ' ]')} as $n ) { $o[ $n ] = get_option( $n, null ); } echo wp_json_encode( $o );`])) as Record<string, unknown>;
        const before = { develop: read(sites.develop), branch: read(sites.branch) };
        const restore = (name: 'develop' | 'branch') => {
            for (const option of OPTIONS) {
                const old = before[name][option];
                parityWp(sites[name], null === old ? ['option', 'delete', option] : ['option', 'update', option, JSON.stringify(old), '--format=json']);
            }
        };

        try {
            // Develop: the legacy screen, one options.php form per section.
            const legacy = await ParitySitePage.doOpen(browser, sites.develop);
            for (const [section, fields] of Object.entries(EDITS)) {
                await legacy.page.goto('/wp-admin/admin.php?page=wpuf-settings');
                await legacy.page.evaluate(([sec, values]) => {
                    for (const [field, value] of Object.entries(values as Record<string, string | string[]>)) {
                        const nodes = Array.from(document.querySelectorAll(`[name="${sec}[${field}]"], [name="${sec}[${field}][]"]`)) as HTMLInputElement[];
                        for (const node of nodes) {
                            if (node instanceof HTMLSelectElement) {
                                Array.from(node.options).forEach((option) => {
                                    option.selected = Array.isArray(value) ? value.includes(option.value) : option.value === value;
                                });
                            } else if ('checkbox' === node.type) {
                                node.checked = Array.isArray(value) ? value.includes(node.value) : 'on' === value;
                            } else if ('hidden' !== node.type) {
                                node.value = value as string;
                            }
                        }
                        if (!nodes.length) {
                            throw new Error(`legacy field not found: ${sec}[${field}]`);
                        }
                    }
                }, [section, fields] as const);
                const form = legacy.page.locator(`form:has([name^="${section}["])`).first();
                await Promise.all([legacy.page.waitForNavigation(), form.evaluate((el) => HTMLFormElement.prototype.submit.call(el))]);
            }
            await legacy.doClose();

            // Branch: the React screen through its controls.
            const admin = await ParitySitePage.doOpen(browser, sites.branch);
            const page = admin.page;
            const root = page.locator('#wpuf-settings-root');
            const row = (label: string) => root.locator('.wpuf-input-container').filter({ has: page.locator('label', { hasText: new RegExp(`^\\s*${label}\\s*$`) }) }).first();
            const save = async () => {
                const saved = page.waitForResponse((response) => response.url().includes('wpuf/v1/settings') && 'POST' === response.request().method());
                await root.getByRole('button', { name: 'Save', exact: true }).click();
                await saved;
            };
            const openTab = async (name: string) => {
                await root.locator('nav button', { hasText: new RegExp(`^\\s*${name}\\s*$`) }).click();
                await page.waitForTimeout(400);
            };
            await page.goto('/wp-admin/admin.php?page=wpuf-settings');
            await root.locator('nav button').first().waitFor();

            // Tick (not toggle): unset checkboxes show their default, which may already be on.
            const tick = async (id: string) => {
                if (!(await root.locator(`#${id}`).isChecked())) {
                    await root.locator(`label[for="${id}"]`).click();
                }
                await expect(root.locator(`#${id}`)).toBeChecked();
            };
            await tick('load_script');
            await root.locator('#custom_css').fill('a { color: red; }');
            await root.locator('#admin_access').click();
            await page.locator('[data-slot="select-item"]:visible', { hasText: /^\s*Admin Only\s*$/ }).click();
            // Show Admin Bar shows its default four roles: drop Contributor.
            await row('Show Admin Bar').locator('[data-slot="smart-multi-select-trigger"]').click();
            await page.locator('[data-slot="command-item"]:visible', { hasText: /^\s*Contributor\s*$/ }).click();
            await page.keyboard.press('Escape');
            await save();

            await openTab('Frontend Posting');
            await root.locator('#per_page').fill('7');
            await save();

            await openTab('Payments');
            await tick('enable_payment');
            await row('Currency').getByRole('combobox').click();
            await page.keyboard.type('Bangladeshi');
            await page.locator('[data-slot="command-item"]:visible').first().click();
            await root.locator('#wpuf_price_num_decimals').fill('3');
            await save();
            await admin.doClose();

            const after = { develop: read(sites.develop), branch: read(sites.branch) };
            for (const [section, fields] of Object.entries(EDITS)) {
                const dev = after.develop[section] as Record<string, unknown>;
                const br = after.branch[section] as Record<string, unknown>;
                for (const field of Object.keys(fields)) {
                    expect.soft(br[field], `${section}.${field} stored like develop's legacy screen`).toEqual(dev[field]);
                }
            }
        } finally {
            restore('develop');
            restore('branch');
        }
    });
});
