import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured } from '../utils/paritySites';

type FieldReport = { added: boolean; alert: string; shape?: { label: string; controls: string[]; text: string; height: number; hidden: number } };

/**
 * Every palette field type, added by click to a new form on both builders:
 * same types offered, same accepted/refused, same stage preview, same stored
 * defaults (task 1.9 field cross-check). Field options edits are PAR0004.
 */
test.describe('Parity every field', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    for (const [id, postType] of [['PAR0016', 'wpuf_forms'], ['PAR0017', 'wpuf_profile']] as const) {
        test(`${id} : every ${postType} palette field adds, previews and stores like develop`, { tag: ['@Parity', `@Test_${id}`] }, async ({ browser }) => {
            test.setTimeout(10 * 60 * 1000);
            const parity = new ParityPage();
            const palette: Record<string, string[]> = {};
            const reports: Record<string, Record<string, FieldReport>> = {};
            const stored: Record<string, Record<string, Record<string, unknown>>> = {};

            for (const name of ['develop', 'branch'] as const) {
                const site = paritySite(name);
                const admin = await ParitySitePage.doOpen(browser, site);
                const formId = await admin.doOpenNewBuilder(postType);
                palette[name] = await admin.getPaletteFieldTypes();
                reports[name] = {};

                for (const type of palette[name]) {
                    await admin.doDismissAlerts();
                    const stage = admin.page.locator('li[class*="form-field-"]');
                    const before = await stage.count();
                    await admin.page.locator(`.wpuf-field-button[data-form-field="${type}"]`).first().click();
                    await expect.poll(async () => (await stage.count()) > before || '' !== (await admin.getAlertText()), { timeout: 10000 }).toBeTruthy();
                    const added = (await stage.count()) > before;
                    const report: FieldReport = { added, alert: await admin.getAlertText() };
                    await admin.doDismissAlerts();
                    if (added) {
                        report.shape = await admin.getLastStageFieldShape();
                    }
                    reports[name][type] = report;
                }

                await admin.doSaveBuilder();
                await admin.doClose();
                stored[name] = {};
                for (const field of parity.readForm(site, formId).fields) {
                    const content = { ...(field.post_content as Record<string, unknown>) };
                    // Row ids and per-save flags differ by design; random inner names are PAR0015.
                    delete content.id;
                    delete content.is_new;
                    stored[name][String(content.template)] = content;
                }
                await test.info().attach(`${name}-report.json`, { path: parity.doWriteJson(test.info().outputPath(`${name}-report.json`), { palette: palette[name], reports: reports[name], stored: stored[name] }) });
            }

            // reCAPTCHA / Turnstile buttons depend on each site's API keys, not on the builder.
            const keyed = ['recaptcha', 'cloudflare_turnstile'];
            const comparable = (types: string[]) => types.filter((type) => !keyed.includes(type));
            // The math captcha preview shows random numbers.
            for (const name of ['develop', 'branch']) {
                const shape = reports[name].math_captcha?.shape;
                if (shape) {
                    shape.text = shape.text.replace(/\d+[-+x]\d+/g, 'N?N');
                }
            }
            // Canvas rows were redesigned on the owner's request (design sync
            // 2026-10-08: floating action toolbar instead of develop's 36px bar under
            // each row, 16px padding): row heights differ by design. Labels,
            // controls, text and hidden parts are still compared.
            for (const name of ['develop', 'branch']) {
                for (const type of Object.keys(reports[name])) {
                    if (reports[name][type]?.shape) {
                        delete (reports[name][type].shape as { height?: number }).height;
                    }
                }
            }
            expect.soft(comparable(palette.branch), 'same palette types in the same order').toStrictEqual(comparable(palette.develop));
            for (const type of comparable(palette.develop)) {
                expect.soft(reports.branch[type], `${type}: add, alert and preview`).toStrictEqual(reports.develop[type]);
            }
            for (const template of comparable(Object.keys(stored.develop))) {
                expect.soft(stored.branch[template], `${template}: stored defaults`).toStrictEqual(stored.develop[template]);
            }
        });
    }
});
