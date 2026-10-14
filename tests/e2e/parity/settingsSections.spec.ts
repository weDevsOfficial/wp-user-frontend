import { test, expect, type Locator, type Page } from '@playwright/test';
import path from 'path';
import { ParityPage, ParitySitePage } from '../pages/parity';
import { parityDir, paritySite, paritySitesConfigured, parityWp, type ParitySite } from '../utils/paritySites';

/**
 * G2b per settings section (task 4.6b): every standard field of a section is
 * edited through the React screen's own controls on the branch, the same
 * values are entered in develop's legacy screen (its options.php form), and
 * the stored option values are compared field by field.
 *
 * Fields the React screen does not show for the current state (dependency
 * hidden) are left out on both sides and listed in the attachment; custom
 * callback blocks (tax, role templates, profile roles, AI keys) have their own
 * checks (SET0001, SET0009).
 *
 * The tests change site options on both sites and restore them: run with
 * one worker.
 */
interface SchemaField { section: string; name: string; kind: string; label: string; options: { key: string; label: string }[]; pro: boolean }
type Value = string | string[];
interface Plan { field: SchemaField; type: string; value: Value; labels: string[] }

const SCHEMA: SchemaField[] = paritySitesConfigured()
    ? JSON.parse(parityWp(paritySite('branch'), ['eval-file', path.join(parityDir, 'wp', 'dump-settings-schema.php'), '--exec=define("WP_ADMIN",true);']))
    : [];
const SECTIONS = [...new Set(SCHEMA.map((field) => field.section))];

const TEXT_KINDS = ['text', 'email', 'url', 'password', 'cb:wpuf_settings_password_preview'];
const MULTI_KINDS = ['multicheck', 'multiselect', 'multi-select', 'cb:wpuf_settings_multiselect'];
// Own-option / custom blocks, not plain section fields.
const CUSTOM_SECTIONS = ['wpuf_ai'];

/** The deterministic edit for a field, or a reason to leave it out. */
function plan(field: SchemaField): Plan | string {
    const { kind, name, options } = field;
    const real = options.filter((option) => '' !== option.key && '-1' !== option.key);
    const base = { field, labels: [] as string[] };

    if (CUSTOM_SECTIONS.includes(field.section)) {
        return 'custom section';
    }
    if (TEXT_KINDS.includes(kind)) {
        return { ...base, type: 'text', value: /email/.test(name) && 'email' === kind ? 'parity@example.com' : `v-${name}` };
    }
    if ('number' === kind) {
        return { ...base, type: 'text', value: '7' };
    }
    if ('textarea' === kind) {
        return { ...base, type: 'text', value: `textarea ${name}` };
    }
    if ('file' === kind) {
        return { ...base, type: 'text', value: 'https://example.com/parity.png' };
    }
    if ('wysiwyg' === kind) {
        return { ...base, type: 'wysiwyg', value: `<p>Body <strong>${name}</strong></p>` };
    }
    // Cloudflare Turnstile is switched on its provider card (General > Security).
    if ('enable_turnstile' === name && 'wpuf_general' === field.section) {
        return { ...base, type: 'card-check', value: 'on', labels: ['Cloudflare'] };
    }
    if ('checkbox' === kind || 'toggle' === kind) {
        return { ...base, type: 'checkbox', value: 'on' };
    }
    if ('color' === kind || 'color-picker' === kind) {
        return { ...base, type: 'color', value: '#112233' };
    }
    if ('select' === kind) {
        if (!real.length) {
            return 'no options';
        }
        const pick = real[real.length - 1];
        return { ...base, type: 'select', value: pick.key, labels: [pick.label] };
    }
    if ('radio' === kind || 'radio_inline' === kind) {
        if (!real.length) {
            return 'no options';
        }
        const pick = real[real.length - 1];
        return { ...base, type: 'radio', value: pick.key, labels: [pick.label] };
    }
    if (MULTI_KINDS.includes(kind)) {
        if (real.length < 2) {
            return 'no options';
        }
        const picks = [real[0], real[real.length - 1]];
        return { ...base, type: 'multi', value: picks.map((option) => option.key), labels: picks.map((option) => option.label) };
    }
    if ('gateway_selector' === kind) {
        return { ...base, type: 'gateways', value: ['paypal'] };
    }
    if ('html' === kind) {
        return 'static';
    }
    return `custom (${kind})`;
}

/** Make a settings row visible: open accordions, then try each card of its section. */
async function findRow(page: Page, section: Locator, name: string): Promise<Locator | null> {
    const row = section.locator(`[data-setting="${name}"]`);
    if (await row.count()) {
        return row.first();
    }
    for (let i = 0; i < 30; i++) {
        const closed = section.locator('button[aria-expanded="false"][aria-controls]');
        if (!(await closed.count())) {
            break;
        }
        await closed.first().click();
        if (await row.count()) {
            return row.first();
        }
    }
    const cards = section.locator('button[data-settings-card]');
    for (let i = 0; i < (await cards.count()); i++) {
        await cards.nth(i).click();
        if (await row.count()) {
            return row.first();
        }
    }
    return null;
}

/** Enter one planned value through the React control of its row. */
async function editReact(page: Page, row: Locator, item: Plan): Promise<string | null> {
    const { type, value, labels, field } = item;
    // Centered: the sticky Save card covers the bottom of the window.
    await row.evaluate((el) => el.scrollIntoView({ block: 'center' }));

    if ('text' === type) {
        const input = row.locator('input[type="text"], input[type="number"], input[type="password"], input[type="email"], input[type="url"], textarea:not(.wp-editor-area)').first();
        if (!(await input.count()) || !(await input.isEditable())) {
            return 'no editable input';
        }
        await input.fill(value as string);
        return null;
    }
    if ('wysiwyg' === type) {
        const id = `wpuf-editor-${field.name}`;
        // The TipTap editor (shared/ui/RichEditor): type the HTML in its Code tab, as
        // a user does in the WordPress editor's Text tab. TinyMCE is the fallback engine.
        const code = row.locator('.wpuf-rich-editor [role="tab"]').filter({ hasText: 'Code' });
        if (await code.count()) {
            await code.first().click();
            const area = row.locator(`textarea#${id}`);
            await area.waitFor({ timeout: 5000 });
            // The Code tab holds the wpautop-free text (WordPress' Text tab): the
            // value as TinyMCE's removep() turns the develop side's visual HTML.
            const text = await page.evaluate((html) => {
                const api = (window as unknown as { wp?: { editor?: { removep?: (h: string) => string } } }).wp?.editor;
                return api && api.removep ? api.removep(html) : html;
            }, value as string);
            await area.fill(text);
            return null;
        }
        try {
            await expect.poll(() => page.evaluate((editor) => !!(window as unknown as { tinymce?: { get: (i: string) => { initialized?: boolean } | null } }).tinymce?.get(editor)?.initialized, id), { timeout: 8000 }).toBe(true);
        } catch {
            return 'editor not initialized';
        }
        await page.evaluate(([editor, html]) => {
            const mce = (window as unknown as { tinymce: { get: (i: string) => { setContent: (c: string) => void; fire: (e: string) => void } } }).tinymce.get(editor);
            mce.setContent(html);
            mce.fire('change');
        }, [id, value as string]);
        return null;
    }
    if ('checkbox' === type) {
        const box = row.locator('[role="checkbox"], [role="switch"]').first();
        if (!(await box.count())) {
            return 'no checkbox';
        }
        if ('true' !== (await box.getAttribute('aria-checked'))) {
            await box.click();
        }
        return null;
    }
    if ('radio' === type) {
        let radio = row.locator(`[role="radio"][data-value="${value}"]`).first();
        if (!(await radio.count()) && labels[0]) {
            // Picture radios (login form layout): one button per option, named by its label.
            radio = row.locator('button[role="radio"]').filter({ hasText: labels[0] }).first();
        }
        if (!(await radio.count())) {
            return 'no radio';
        }
        await radio.click();
        return null;
    }
    if ('select' === type) {
        const smart = row.locator('[data-slot="smart-select-trigger"]');
        const searchable = (await smart.count()) > 0;
        const trigger = searchable ? smart.first() : row.locator('[data-slot="select-trigger"]').first();
        if (!(await trigger.count())) {
            return 'no select';
        }
        await trigger.click();
        const item = searchable ? '[data-slot="command-item"]' : '[data-slot="select-item"]';
        await page.locator(`${item}:visible`).first().waitFor({ timeout: 5000 }).catch(() => undefined);
        if (searchable) {
            await page.keyboard.type(labels[0].slice(0, 14));
            await page.waitForTimeout(450);
        }
        const option = page.locator(`${item}:visible`).filter({ has: page.getByText(labels[0], { exact: true }) });
        if (!(await option.count())) {
            await page.keyboard.press('Escape');
            await page.locator(`${item}:visible`).first().waitFor({ state: 'hidden', timeout: 3000 }).catch(() => undefined);
            return `option not listed: ${labels[0]}`;
        }
        // The list keeps aligning itself to the trigger while it opens.
        await option.first().click({ force: true });
        await page.locator(`${item}:visible`).first().waitFor({ state: 'hidden', timeout: 3000 }).catch(() => undefined);
        const shown = (await trigger.innerText()).trim().replace(/\s+/g, ' ');
        return shown === labels[0] ? null : `pick not shown (trigger says "${shown}")`;
    }
    if ('multi' === type) {
        const trigger = row.locator('[data-slot="smart-multi-select-trigger"]');
        if (!(await trigger.count())) {
            return 'no multi select';
        }
        await trigger.first().click();
        const content = page.locator('[data-slot="smart-multi-select-content"]:visible');
        const clear = content.getByText('Clear', { exact: true });
        if (await clear.count()) {
            await clear.first().click();
        }
        for (const label of labels) {
            await content.locator('[data-slot="command-item"]').filter({ has: page.getByText(label, { exact: true }) }).first().click();
        }
        await page.keyboard.press('Escape');
        return null;
    }
    if ('color' === type) {
        const trigger = row.locator('button[aria-label], button').first();
        await trigger.click();
        const picker = page.locator('.components-color-picker:visible');
        if (!(await picker.count())) {
            return 'no color picker';
        }
        const hex = picker.locator('input').last();
        if (!(await hex.count())) {
            await page.keyboard.press('Escape');
            return 'no hex input';
        }
        await hex.fill((value as string).replace('#', ''));
        await hex.press('Enter');
        await page.keyboard.press('Escape');
        await picker.waitFor({ state: 'hidden', timeout: 3000 }).catch(() => undefined);
        const shown = (await trigger.innerText()).trim().toLowerCase();
        return shown.includes((value as string).toLowerCase()) ? null : `color not shown (trigger says "${shown}")`;
    }
    return `no driver for ${type}`;
}

/** Enter the applied values in develop's legacy form of the section and submit it. */
async function editLegacy(page: Page, section: string, items: Plan[]): Promise<string[]> {
    await page.goto('/wp-admin/admin.php?page=wpuf-settings');
    const missing = await page.evaluate(([sec, edits]) => {
        const notFound: string[] = [];
        for (const edit of edits as { name: string; type: string; value: string | string[] }[]) {
            const nodes = Array.from(document.querySelectorAll(`[name="${sec}[${edit.name}]"], [name="${sec}[${edit.name}][]"], [name^="${sec}[${edit.name}]["]`)) as HTMLInputElement[];
            const live = nodes.filter((node) => 'hidden' !== node.type);
            if (!live.length) {
                notFound.push(edit.name);
                continue;
            }
            const list = Array.isArray(edit.value) ? edit.value : [edit.value];
            for (const node of live) {
                if (node instanceof HTMLSelectElement) {
                    // Options built from site data (forms, pages) can differ between the sites.
                    if (!list.every((wanted) => Array.from(node.options).some((option) => option.value === wanted))) {
                        notFound.push(edit.name);
                    }
                    Array.from(node.options).forEach((option) => {
                        option.selected = list.includes(option.value);
                    });
                } else if ('checkbox' === node.type) {
                    node.checked = 'checkbox' === edit.type ? true : list.includes(node.value);
                } else if ('radio' === node.type) {
                    node.checked = list.includes(node.value);
                } else if ('wysiwyg' === edit.type && (window as unknown as { tinymce?: { get: (i: string) => unknown } }).tinymce?.get(node.id)) {
                    // As a user in the Visual tab: the editor's save (run by a real form
                    // submit, skipped by HTMLFormElement.submit()) writes the textarea,
                    // wpautop paragraphs removed.
                    const mce = (window as unknown as { tinymce: { get: (i: string) => { setContent: (c: string) => void; save: () => void } } }).tinymce.get(node.id);
                    mce.setContent(list[0]);
                    mce.save();
                } else {
                    node.value = list[0];
                }
            }
        }
        return notFound;
    }, [section, items.map((item) => ({ name: item.field.name, type: item.type, value: item.value }))] as const);
    const form = page.locator(`form:has([name^="${section}["])`).first();
    await Promise.all([page.waitForNavigation(), form.evaluate((el) => HTMLFormElement.prototype.submit.call(el))]);
    return missing;
}

const readOption = (site: ParitySite, option: string): Record<string, unknown> | null => JSON.parse(parityWp(site, ['eval', `echo wp_json_encode( get_option( '${option}', null ) );`]));

test.describe('Parity settings sections', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    for (const sectionId of SECTIONS) {
        test(`SET0010 : section ${sectionId}: every field edited on the React screen stores what develop's legacy screen stores`, { tag: ['@Parity', '@Test_SET0010'] }, async ({ browser }) => {
            test.setTimeout(420_000);
            const parity = new ParityPage();
            const sites = { develop: paritySite('develop'), branch: paritySite('branch') };
            const before = { develop: readOption(sites.develop, sectionId), branch: readOption(sites.branch, sectionId) };
            const skipped: Record<string, string> = {};
            const applied: Plan[] = [];

            try {
                // Branch: React screen.
                const admin = await ParitySitePage.doOpen(browser, sites.branch);
                const page = admin.page;
                await page.setViewportSize({ width: 1440, height: 1000 });
                await page.goto('/wp-admin/admin.php?page=wpuf-settings');
                const root = page.locator('#wpuf-settings-root');
                await root.locator('nav button').first().waitFor();
                const ia = await page.evaluate(() => (window as unknown as { wp: { data: { select: (s: string) => { getIa: () => { id: string; sections: string[] }[] } } } }).wp.data.select('wpuf/settings').getIa());
                const tab = ia.find((item) => (item.sections || []).includes(sectionId));
                test.skip(!tab, `section ${sectionId} is not on the React screen`);
                await page.goto(`/wp-admin/admin.php?page=wpuf-settings&tab=${tab!.id}&sub=${sectionId}`);
                const section = root.locator(`[data-settings-section="${sectionId}"]`);
                await section.waitFor({ timeout: 20_000 });

                for (const field of SCHEMA.filter((item) => item.section === sectionId)) {
                    const item = plan(field);
                    if ('string' === typeof item) {
                        skipped[field.name] = item;
                        continue;
                    }
                    // A pick equal to the stored value is no edit: take another option.
                    if (('select' === item.type || 'radio' === item.type) && String((before.branch || {})[field.name] ?? '') === item.value) {
                        const other = field.options.filter((option) => '' !== option.key && '-1' !== option.key && option.key !== item.value);
                        if (other.length) {
                            item.value = other[0].key;
                            item.labels = [other[0].label];
                        }
                    }
                    if ('card-check' === item.type || 'gateways' === item.type) {
                        const cards = section.locator('button[data-settings-card]');
                        let done = false;
                        for (let i = 0; i < (await cards.count()); i++) {
                            const card = cards.nth(i);
                            const id = (await card.getAttribute('data-settings-card')) || '';
                            const want = 'card-check' === item.type ? (id === item.labels[0] ? true : null) : (item.value as string[]).includes(id);
                            if (null === want) {
                                continue;
                            }
                            const box = card.locator('[role="checkbox"]');
                            await card.evaluate((el) => el.scrollIntoView({ block: 'center' }));
                            if (('true' === (await box.getAttribute('aria-checked'))) !== want) {
                                await box.click();
                            }
                            done = true;
                        }
                        if (done) {
                            applied.push(item);
                        } else {
                            skipped[field.name] = 'react: no card';
                        }
                        continue;
                    }
                    const row = await findRow(page, section, field.name);
                    if (!row) {
                        skipped[field.name] = 'not shown on the React screen (dependency)';
                        continue;
                    }
                    // Without Pro, a Pro field is a disabled preview on both screens.
                    if (await row.locator('fieldset[disabled]').count()) {
                        skipped[field.name] = 'Pro preview (display only)';
                        continue;
                    }
                    const problem = await editReact(page, row, item);
                    if (problem) {
                        skipped[field.name] = `react: ${problem}`;
                        continue;
                    }
                    applied.push(item);
                }

                const save = root.getByRole('button', { name: 'Save', exact: true });
                if (applied.length && (await save.isEnabled())) {
                    const saved = page.waitForResponse((response) => response.url().includes('wpuf/v1/settings') && 'POST' === response.request().method());
                    await save.click();
                    expect((await saved).status(), 'React save succeeds').toBe(200);
                }
                await admin.doClose();

                // Develop: legacy screen, the same values.
                const legacy = await ParitySitePage.doOpen(browser, sites.develop);
                const missing = applied.length ? await editLegacy(legacy.page, sectionId, applied) : [];
                await legacy.doClose();
                for (const name of missing) {
                    skipped[name] = 'no input (or no such option) on develop\'s legacy screen';
                }

                const stored = { develop: readOption(sites.develop, sectionId) || {}, branch: readOption(sites.branch, sectionId) || {} };
                const compared = applied.filter((item) => !missing.includes(item.field.name));
                await test.info().attach('result.json', { path: parity.doWriteJson(test.info().outputPath('result.json'), { applied: compared.map((item) => item.field.name), skipped, stored }) });

                for (const item of compared) {
                    expect.soft(stored.branch[item.field.name], `${sectionId}.${item.field.name} (${item.field.kind})`).toEqual(stored.develop[item.field.name]);
                }
                console.log(`SET0010 ${sectionId}: compared ${compared.length}, skipped ${JSON.stringify(skipped)}`);
            } finally {
                for (const name of ['develop', 'branch'] as const) {
                    const old = before[name];
                    parityWp(sites[name], null === old ? ['option', 'delete', sectionId] : ['option', 'update', sectionId, JSON.stringify(old), '--format=json']);
                }
            }
        });
    }
});

/** Pick an option by label in a settings Select (plain or searchable) inside `scope`. */
async function pickOption(page: Page, scope: Locator, label: string) {
    await scope.evaluate((el) => el.scrollIntoView({ block: 'center' }));
    const smart = scope.locator('[data-slot="smart-select-trigger"]');
    const searchable = (await smart.count()) > 0;
    const trigger = searchable ? smart.first() : scope.locator('[data-slot="select-trigger"]').first();
    await trigger.click();
    const item = searchable ? '[data-slot="command-item"]' : '[data-slot="select-item"]';
    await page.locator(`${item}:visible`).first().waitFor();
    if (searchable) {
        await page.keyboard.type(label.slice(0, 14));
        await page.waitForTimeout(450);
    }
    await page.locator(`${item}:visible`).filter({ has: page.getByText(label, { exact: true }) }).first().click({ force: true });
    await page.locator(`${item}:visible`).first().waitFor({ state: 'hidden', timeout: 3000 }).catch(() => undefined);
    await expect(trigger, `picked ${label}`).toContainText(label);
}

test.describe('Parity settings custom blocks', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('SET0011 : role forms, login layout, e-mail roles, tax and AI blocks store what develop\'s legacy screen stores', { tag: ['@Parity', '@Test_SET0011'] }, async ({ browser }) => {
        test.setTimeout(420_000);
        const OPTIONS = ['wpuf_profile', 'wpuf_mails', 'wpuf_ai', 'wpuf_payment_tax', 'wpuf_base_country_state', 'wpuf_tax_rates'];
        const sites = { develop: paritySite('develop'), branch: paritySite('branch') };
        const readAll = (site: ParitySite) => Object.fromEntries(OPTIONS.map((option) => [option, readOption(site, option)])) as Record<string, Record<string, unknown> | null>;
        const before = { develop: readAll(sites.develop), branch: readAll(sites.branch) };
        // A registration form both sites have (form ids are site data).
        const forms = (site: ParitySite) => JSON.parse(parityWp(site, ['post', 'list', '--post_type=wpuf_profile', '--post_status=publish', '--fields=ID,post_title', '--format=json'])) as { ID: number; post_title: string }[];
        const shared = forms(sites.branch).find((form) => forms(sites.develop).some((other) => other.ID === form.ID && other.post_title === form.post_title));
        test.skip(!shared, 'no registration form with the same id on both sites');
        const formId = String(shared!.ID);
        const formTitle = shared!.post_title;

        try {
            // Branch: React screen.
            const admin = await ParitySitePage.doOpen(browser, sites.branch);
            const page = admin.page;
            await page.setViewportSize({ width: 1440, height: 1000 });
            const root = page.locator('#wpuf-settings-root');
            const open = async (tab: string, sectionId: string) => {
                await page.goto(`/wp-admin/admin.php?page=wpuf-settings&tab=${tab}&sub=${sectionId}`);
                const section = root.locator(`[data-settings-section="${sectionId}"]`);
                await section.waitFor({ timeout: 20_000 });
                for (let i = 0; i < 30; i++) {
                    const closed = section.locator('button[aria-expanded="false"][aria-controls]');
                    if (!(await closed.count())) {
                        break;
                    }
                    await closed.first().click();
                }
                return section;
            };
            const save = async () => {
                const saved = page.waitForResponse((response) => response.url().includes('wpuf/v1/settings') && 'POST' === response.request().method());
                await root.getByRole('button', { name: 'Save', exact: true }).click();
                expect((await saved).status(), 'React save succeeds').toBe(200);
            };

            let section = await open('login_registration', 'wpuf_profile');
            const roles = section.locator('[data-setting="profile_form_roles"]');
            await pickOption(page, roles.locator('div.flex.items-center.gap-4').filter({ hasText: /^\s*Editor/ }).first(), formTitle);
            // Picture radio: one button per layout.
            const layout = section.locator('[data-setting="wpuf_login_form_layout"] button[role="radio"]').filter({ hasText: 'Layout 3' });
            await layout.evaluate((el) => el.scrollIntoView({ block: 'center' }));
            await layout.click();
            await expect(layout).toHaveAttribute('aria-checked', 'true');
            await save();

            section = await open('email', 'wpuf_mails');
            const mailRoles = section.locator('[data-setting="pending_user_email_default_roles"]');
            await mailRoles.evaluate((el) => el.scrollIntoView({ block: 'center' }));
            await mailRoles.locator('[data-slot="smart-multi-select-trigger"]').click();
            const list = page.locator('[data-slot="smart-multi-select-content"]:visible');
            const clear = list.getByText('Clear', { exact: true });
            if (await clear.count()) {
                await clear.first().click();
            }
            for (const label of ['Editor', 'Subscriber']) {
                await list.locator('[data-slot="command-item"]').filter({ has: page.getByText(label, { exact: true }) }).first().click();
            }
            await page.keyboard.press('Escape');
            await save();

            section = await open('payments', 'wpuf_payment_tax');
            const base = section.locator('[data-setting="wpuf_base_country_state"]');
            await pickOption(page, base.locator('div.min-w-0.flex-1').first(), 'Bangladesh');
            const rates = section.locator('[data-setting="wpuf_tax_rates"]');
            if (!(await rates.locator('input[type="number"]').count())) {
                await rates.getByRole('button', { name: /Add Rate/ }).click();
            }
            const rate = rates.locator('div.flex.items-center.gap-3').first();
            await pickOption(page, rate.locator('div.min-w-0.flex-1').nth(0), 'Bangladesh');
            await pickOption(page, rate.locator('div.min-w-0.flex-1').nth(1), 'Country Wide');
            await rate.locator('input[type="number"]').fill('5.5');
            await save();

            section = await open('integrations', 'wpuf_ai');
            await section.locator('button[data-value="anthropic"]').click();
            await section.locator('#temperature').fill('0.3');
            await save();
            await admin.doClose();

            const branch = readAll(sites.branch);
            const model = String((branch.wpuf_ai || {}).ai_model ?? '');

            // Develop: legacy screen.
            const legacy = await ParitySitePage.doOpen(browser, sites.develop);
            const submit = async (sectionId: string, fill: (args: Record<string, string>) => void, args: Record<string, string>) => {
                await legacy.page.goto('/wp-admin/admin.php?page=wpuf-settings');
                await legacy.page.evaluate(fill, args);
                const form = legacy.page.locator(`form:has([name^="${sectionId}["])`).first();
                await Promise.all([legacy.page.waitForNavigation(), form.evaluate((el) => HTMLFormElement.prototype.submit.call(el))]);
            };
            const setSelect = `const setSelect = (name, value) => { const el = document.querySelector('[name="' + name + '"]'); if (!el) { throw new Error('legacy field not found: ' + name); } if (!Array.from(el.options).some((o) => o.value === value)) { el.add(new Option(value, value)); } el.value = value; };`;
            await submit('wpuf_profile', new Function('a', `${setSelect} setSelect('wpuf_profile[roles][editor]', a.form); document.querySelectorAll('[name="wpuf_profile[wpuf_login_form_layout]"]').forEach((r) => { r.checked = 'layout3' === r.value; });`) as (args: Record<string, string>) => void, { form: formId });
            await submit('wpuf_mails', new Function('a', `document.querySelectorAll('[name="wpuf_mails[pending_user_email_default_roles][]"]').forEach((c) => { c.disabled = false; c.checked = ['editor', 'subscriber'].includes(c.value); });`) as (args: Record<string, string>) => void, {});
            await submit('wpuf_payment_tax', new Function('a', `${setSelect} setSelect('wpuf_base[country]', 'BD'); setSelect('wpuf_base[state]', ''); setSelect('wpuf_tax_rates[0][country]', 'BD'); setSelect('wpuf_tax_rates[0][state]', 'country_wide'); document.querySelector('[name="wpuf_tax_rates[0][rate]"]').value = '5.5';`) as (args: Record<string, string>) => void, {});
            await submit('wpuf_ai', new Function('a', `${setSelect} document.querySelectorAll('[name="wpuf_ai[ai_provider]"]').forEach((r) => { r.checked = 'anthropic' === r.value; }); setSelect('wpuf_ai[ai_model]', a.model); document.querySelector('[name="wpuf_ai[temperature]"]').value = '0.3';`) as (args: Record<string, string>) => void, { model });
            await legacy.doClose();

            const develop = readAll(sites.develop);
            await test.info().attach('stored.json', { path: new ParityPage().doWriteJson(test.info().outputPath('stored.json'), { develop, branch }) });
            const get = (all: Record<string, Record<string, unknown> | null>, option: string, key: string) => (all[option] || {})[key];

            expect.soft(get(branch, 'wpuf_profile', 'roles'), 'profile forms per role').toEqual(get(develop, 'wpuf_profile', 'roles'));
            expect.soft(get(branch, 'wpuf_profile', 'wpuf_login_form_layout'), 'login form layout').toEqual(get(develop, 'wpuf_profile', 'wpuf_login_form_layout'));
            expect.soft(get(branch, 'wpuf_mails', 'pending_user_email_default_roles'), 'pending user e-mail roles').toEqual(get(develop, 'wpuf_mails', 'pending_user_email_default_roles'));
            expect.soft(branch.wpuf_base_country_state, 'tax base country / state').toEqual(develop.wpuf_base_country_state);
            expect.soft((branch.wpuf_tax_rates as unknown as unknown[])?.[0], 'first tax rate').toEqual((develop.wpuf_tax_rates as unknown as unknown[])?.[0]);
            // The model follows the provider on both screens (their own scripts), not compared.
            for (const key of ['ai_provider', 'temperature']) {
                expect.soft(get(branch, 'wpuf_ai', key), `wpuf_ai.${key}`).toEqual(get(develop, 'wpuf_ai', key));
            }
        } finally {
            for (const name of ['develop', 'branch'] as const) {
                for (const option of OPTIONS) {
                    const old = before[name][option];
                    parityWp(sites[name], null === old ? ['option', 'delete', option] : ['option', 'update', option, JSON.stringify(old), '--format=json']);
                }
            }
        }
    });
});
