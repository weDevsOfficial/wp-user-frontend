import { expect, type Browser, type BrowserContext, type Page } from '@playwright/test';
import * as fs from 'fs';
import * as os from 'os';
import * as path from 'path';
import { Selectors } from './selectors';
import { Users } from '../utils/testData';
import { parityDir, parityWp, type ParitySite } from '../utils/paritySites';
import { BUILDER_URL, builderFormId } from '../utils/builderUrl';

/**
 * Stored form as dumped by parity/wp/dump-form.php: every value with its PHP type,
 * IDs replaced by positions.
 */
export interface FormDump {
    post_type: string;
    post_status: string;
    post_title: string;
    meta: Record<string, unknown>;
    fields: Array<{
        menu_order: number;
        post_status: string;
        post_content: unknown;
        meta: Record<string, unknown>;
    }>;
}

/**
 * Data-layer page object for the parity suite (develop vs branch). Seeds identical
 * data on both sites and compares what each site stored.
 */
export class ParityPage {

    /** Create a form on a site from a fixture in parity/fixtures and return its id. */
    doSeedForm(site: ParitySite, fixture: string, title?: string): number {
        const out = parityWp(site, ['eval-file', path.join(parityDir, 'wp', 'seed-form.php'), path.join(parityDir, 'fixtures', fixture), ...(title ? [title] : [])]);
        const id = Number(out.trim().split(/\s+/).pop());
        if (!id) {
            throw new Error(`Seeding ${fixture} on ${site.name} returned no id: ${out}`);
        }
        return id;
    }

    /** Read a fixture from parity/fixtures. */
    readFixture(fixture: string): FormDump {
        return JSON.parse(fs.readFileSync(path.join(parityDir, 'fixtures', fixture), 'utf-8')) as FormDump;
    }

    /** Read a stored form from a site. */
    readForm(site: ParitySite, formId: number): FormDump {
        return JSON.parse(parityWp(site, ['eval-file', path.join(parityDir, 'wp', 'dump-form.php'), String(formId)])) as FormDump;
    }

    /** Write a value as pretty JSON (evidence files next to the test output) and return the path. */
    doWriteJson(file: string, value: unknown): string {
        fs.mkdirSync(path.dirname(file), { recursive: true });
        fs.writeFileSync(file, JSON.stringify(value, null, 2));
        return file;
    }

    /** Assert both builders showed the same option rows and accepted the same edits. */
    validateRowsEqual(develop: Record<string, string[]>, branch: Record<string, string[]>) {
        expect.soft(branch, 'branch builder must show the same option rows as develop').toStrictEqual(develop);
    }

    /**
     * A copy of a stored form without the new-field markers (`is_new: true` and
     * its `id`) that `Admin_Form_Builder::save_form()` consumes on every build.
     */
    withoutNewFieldMarkers(form: FormDump): FormDump {
        const copy = JSON.parse(JSON.stringify(form)) as FormDump;
        for (const field of copy.fields) {
            const content = field.post_content as Record<string, unknown> | null;
            if (content && typeof content === 'object' && content.is_new === true) {
                delete content.is_new;
                delete content.id;
            }
        }
        return copy;
    }

    /**
     * A copy of develop's stored form without the two develop behaviours the
     * branch does not copy on purpose (owner decisions, ground-truth B29):
     * the top-level `selected` develop writes from the Visibility value on a
     * field that never had one, and develop's rewrite of an untouched
     * `wpuf_cond` (taken from the branch only when the branch kept the fixture's
     * rules; blank rule rows: develop's null = the branch's '').
     */
    withoutAgreedDeviations(develop: FormDump, branch: FormDump, fixture: FormDump): FormDump {
        const copy = JSON.parse(JSON.stringify(develop)) as FormDump;
        copy.fields.forEach((field, index) => {
            const dev = field.post_content as Record<string, unknown> | null;
            const br = branch.fields[index]?.post_content as Record<string, unknown> | undefined;
            const orig = fixture.fields[index]?.post_content as Record<string, unknown> | undefined;
            if (!dev || !br || !orig) {
                return;
            }
            // Develop's visibility watcher copies the visibility choice into
            // `selected` (the option default), wiping a default the user picked; the
            // branch keeps the picked default (agreed: do not copy the bug).
            const visibility = dev.wpuf_visibility as { selected?: unknown } | undefined;
            if (visibility && dev.selected === visibility.selected && dev.selected !== br.selected) {
                if ('selected' in br) {
                    dev.selected = br.selected;
                } else if (!('selected' in orig)) {
                    delete dev.selected;
                }
            }
            // Develop never reveals "Visible on product page" on a taxonomy field
            // without a stored woo_attr (its store adds the key non-reactively);
            // the branch shows the row, so it can be ticked.
            if (!('woo_attr_vis' in orig) && !('woo_attr_vis' in dev) && 'woo_attr_vis' in br) {
                dev.woo_attr_vis = br.woo_attr_vis;
            }
            if (dev.template === 'column_field' && br.column_space !== orig.column_space) {
                dev.column_space = br.column_space;
                dev.css = br.css;
            }
            if (JSON.stringify(br.wpuf_cond) === JSON.stringify(orig.wpuf_cond)) {
                if ('wpuf_cond' in br) {
                    dev.wpuf_cond = br.wpuf_cond;
                } else {
                    delete dev.wpuf_cond;
                }
            }
            // Develop's conditional-logic panel also rewrites the rule arrays when it
            // opens (one blank row for a field without rules); the branch writes only
            // what the user changed (4.4d). A field whose rules were not edited on the
            // branch (only Yes / No or All / Any) takes the branch's arrays.
            const devCond = dev.wpuf_cond as Record<string, unknown[] | string> | undefined;
            const brCond = br.wpuf_cond as Record<string, unknown[] | string> | undefined;
            const origCond = orig.wpuf_cond as Record<string, unknown[] | string> | undefined;
            const rulesOf = (cond?: Record<string, unknown>) => {
                const { condition_status: _status, cond_logic: _logic, ...rules } = cond || {};
                return JSON.stringify(rules);
            };
            if (devCond && brCond && origCond && rulesOf(brCond) === rulesOf(origCond)
                && devCond.condition_status === brCond.condition_status && devCond.cond_logic === brCond.cond_logic) {
                dev.wpuf_cond = brCond;
            } else if (devCond && brCond && Array.isArray(devCond.cond_field)) {
                // A blank rule row: develop's <select> reports an unselected operator as
                // undefined (stored null), the branch keeps ''; both mean "no operator".
                (devCond.cond_field as unknown[]).forEach((name, i) => {
                    if ('' !== name) {
                        return;
                    }
                    for (const key of ['cond_operator', 'cond_option', 'option_title', 'input_type', 'field_type']) {
                        const devList = devCond[key] as unknown[] | undefined;
                        const brList = brCond[key] as unknown[] | undefined;
                        if (Array.isArray(devList) && Array.isArray(brList) && null === devList[i] && '' === brList[i]) {
                            devList[i] = '';
                        }
                    }
                });
            }
        });
        return copy;
    }

    /**
     * Option-row readings with the agreed deviations folded in: a saved field's
     * Meta Key reads `readonly` on the branch (develop accepts typing but its
     * store ignores it), the woo_attr_vis row develop never reveals, and the
     * column_space row develop never rendered.
     */
    withoutAgreedRowDeviations(rows: string[] = []): string[] {
        return rows
            .filter((row) => !row.startsWith('panel-field-opt-checkbox | Visible on product page |'))
            // Develop has no Vue component for the `number` option type, so it never
            // showed Space Between Columns; the branch does.
            .filter((row) => !row.startsWith('panel-field-opt-text | Space Between Columns |'))
            .map((row) => (row.startsWith('panel-field-opt-text | Meta Key |') ? 'panel-field-opt-text | Meta Key | (agreed)' : row))
            // Filler values carry the row index, which shifts once a row is left out.
            .map((row) => row.replace(/fill=P (\d+) \d+\.(\d+)/g, 'fill=P $1 #.$2'));
    }

    /** Assert two stored forms are identical (values and PHP types). */
    validateFormsEqual(develop: FormDump, branch: FormDump) {
        expect(branch, 'branch storage must equal develop storage').toStrictEqual(develop);
    }
}

/** Builder screen slug per stored post type. */
const builderPage: Record<string, string> = {
    wpuf_forms: 'wpuf-post-forms',
    wpuf_profile: 'wpuf-profile-forms',
};

/**
 * Browser-side page object for one parity site. Each site gets its own isolated
 * context so the two logins never share cookies.
 */
export class ParitySitePage {
    private constructor(readonly site: ParitySite, readonly context: BrowserContext, readonly page: Page) {}

    /**
     * Admin login shared by every worker of one run (keyed by the runner's pid).
     * WordPress keeps all of a user's session tokens in one user meta row, so
     * logins made at the same moment overwrite each other's token: workers log
     * in one at a time behind a lock and reuse the first login.
     */
    private static async getSession(browser: Browser, site: ParitySite) {
        const dir = path.join(os.tmpdir(), 'wpuf-parity-auth');
        const key = `${process.ppid}-${site.url.replace(/[^a-z0-9]+/gi, '_')}`;
        const file = path.join(dir, `${key}.json`);
        const lock = path.join(dir, `${key}.lock`);
        fs.mkdirSync(dir, { recursive: true });

        for (let waited = 0; ; waited += 200) {
            try {
                fs.mkdirSync(lock);
                break;
            } catch {
                // A lock left by a crashed worker is taken over after 60 seconds.
                if (waited > 60000) {
                    fs.rmdirSync(lock);
                }
                await new Promise((resolve) => setTimeout(resolve, 200));
            }
        }

        try {
            if (fs.existsSync(file)) {
                return JSON.parse(fs.readFileSync(file, 'utf-8'));
            }
            const context = await browser.newContext({ baseURL: site.url });
            const page = await context.newPage();
            // Land on the profile screen, not the dashboard: its widgets make remote
            // calls (news feed, update checks) that queue when tests run in parallel.
            await page.goto(`/wp-login.php?redirect_to=${encodeURIComponent(`${site.url}/wp-admin/profile.php`)}`);
            await page.locator(Selectors.login.basicLogin.loginEmailField).fill(Users.adminUsername);
            await page.locator(Selectors.login.basicLogin.loginPasswordField).fill(Users.adminPassword);
            await Promise.all([
                page.waitForURL(/wp-admin/, { waitUntil: 'domcontentloaded', timeout: 45000 }),
                page.locator(Selectors.login.basicLogin.loginButton).click(),
            ]);
            const state = await context.storageState();
            await context.close();
            fs.writeFileSync(file, JSON.stringify(state));
            return state;
        } finally {
            fs.rmdirSync(lock);
        }
    }

    /** Open an isolated, logged-in admin session on a site. */
    static async doOpen(browser: Browser, site: ParitySite): Promise<ParitySitePage> {
        const storageState = await ParitySitePage.getSession(browser, site);
        const context = await browser.newContext({ baseURL: site.url, storageState });
        const page = await context.newPage();
        page.setDefaultTimeout(15000);
        return new ParitySitePage(site, context, page);
    }

    /** Open the builder of a form and wait until its Save button is shown. */
    async doOpenBuilder(postType: string, formId: number) {
        // DOM ready, then the Save button is the readiness signal; the full load event
        // can take long when several builders open at once. Visible, not enabled: the
        // branch keeps Save disabled until something changes.
        await this.page.goto(`/wp-admin/admin.php?page=${builderPage[postType]}&action=edit&id=${formId}`, { waitUntil: 'domcontentloaded', timeout: 45000 });
        await expect(this.page.locator(Selectors.parity.builderSaveButton).first()).toBeVisible({ timeout: 45000 });
    }

    /**
     * Click Save and wait for the save request to succeed: develop posts the AJAX
     * action, the branch the REST route `wpuf/v1/admin/forms/{id}` (same payload and
     * `{ success, data }` body).
     *
     * `untouched`: nothing was changed. The branch keeps Save disabled then ("No
     * changes to save"), so no request is sent and the stored form stays as it was;
     * develop still posts. Without it Save must be enabled (an edit marks the form dirty).
     * Returns whether a save request was sent.
     */
    async doSaveBuilder({ untouched = false }: { untouched?: boolean } = {}): Promise<boolean> {
        const button = this.page.locator(Selectors.parity.builderSaveButton).first();
        if (untouched && await button.isDisabled()) {
            await expect(button, 'disabled Save says why').toHaveAttribute('title', 'No changes to save');
            return false;
        }
        await expect(button, 'Save is enabled after a change').toBeEnabled();
        const saved = this.page.waitForResponse((response) =>
            response.request().method() === 'POST'
            && ((response.url().includes('admin-ajax.php')
                && (response.request().postData() || '').includes('wpuf_form_builder_save_form'))
                || /wpuf\/v1\/admin\/forms\/\d+/.test(decodeURIComponent(response.url()))));
        // A stage add of a meta field opens the custom field tip (as develop does); it blocks the Save click.
        await this.doDismissAlerts();
        await this.page.locator(Selectors.parity.builderSaveButton).first().click();
        const response = await saved;
        expect(response.ok(), 'builder save request must succeed').toBeTruthy();
        expect(await response.json(), 'builder save must report success').toMatchObject({ success: true });
        return true;
    }

    /**
     * Load an admin URL and collect page errors plus scripts/styles that came
     * back as something other than JS/CSS (a missing file 301s to an HTML 404).
     */
    async getLoadProblems(path: string): Promise<{ errors: string[]; badAssets: string[] }> {
        const errors: string[] = [];
        const badAssets: string[] = [];
        const onError = (error: Error) => errors.push(String(error).slice(0, 200));
        const onResponse = (response: import('@playwright/test').Response) => {
            const type = response.headers()['content-type'] || '';
            const kind = response.request().resourceType();
            // 304 (unchanged, served from the browser cache) carries no content type.
            if (304 !== response.status() && ['script', 'stylesheet'].includes(kind) && !/javascript|css/.test(type) && response.url().startsWith(this.site.url)) {
                badAssets.push(`${response.status()} ${response.url()}`);
            }
        };
        this.page.on('pageerror', onError);
        this.page.on('response', onResponse);
        await this.page.goto(path, { waitUntil: 'networkidle' });
        this.page.off('pageerror', onError);
        this.page.off('response', onResponse);
        return { errors, badAssets };
    }

    /**
     * On the React settings screen, type into the first text field, save, and
     * return the field id plus the body the screen posted.
     */
    async doEditFirstSettingsText(value: string): Promise<{ field: string; body: { settings: Record<string, Record<string, unknown>>; extra: Record<string, unknown> } }> {
        await this.page.goto('/wp-admin/admin.php?page=wpuf-settings');
        const input = this.page.locator(Selectors.parity.settingsTextInputs).first();
        await expect(input).toBeVisible();
        const field = (await input.getAttribute('id')) || '';
        await input.fill(value);
        const saved = this.page.waitForRequest((request) => request.url().includes('wpuf/v1/settings') && request.method() === 'POST');
        await this.page.locator(Selectors.parity.settingsSaveButton).first().click();
        const request = await saved;
        await request.response();
        return { field, body: request.postDataJSON() };
    }

    /** In the open field settings panel, set Visibility to subscribed users and tick a pack by its title. */
    async doSetSubscriptionVisibility(packTitle: string) {
        const panel = this.page.locator(Selectors.parity.fieldOptionsPanel);
        // Native inputs on develop; the shared Radio / Checkbox (role + data-value) on the branch (4.4c).
        await panel.locator('input[type="radio"][value="subscribed_users"]:not([aria-hidden="true"]), [role="radio"][data-value="subscribed_users"]').first().check();
        await panel.getByRole('checkbox', { name: packTitle, exact: true }).check();
    }

    /** In the open field settings panel, tick a checkbox by its label. */
    async doCheckFieldOption(label: string) {
        await this.page.locator(Selectors.parity.fieldOptionsPanel).getByRole('checkbox', { name: label }).check();
    }

    /**
     * Run a forms-list row action (duplicate, delete) as the list does: develop's
     * nonce-checked admin URL, or the admin forms REST route where the list has
     * no classic action any more.
     */
    async doFormsListAction(page: string, formId: number, action: string) {
        await this.page.goto(`/wp-admin/admin.php?page=${page}`);
        const list = await this.page.evaluate(() => (window as unknown as { wpuf_forms_list: { bulk_nonce?: string; rest_nonce: string } }).wpuf_forms_list);

        if (list.bulk_nonce) {
            await this.page.goto(`/wp-admin/admin.php?page=${page}&id=${formId}&action=${action}&_wpnonce=${list.bulk_nonce}`);

            return;
        }

        const response = await this.page.request.fetch(`/wp-json/wpuf/v1/admin/forms/${formId}${'delete' === action ? '' : `/${action}`}`, {
            method: 'delete' === action ? 'DELETE' : 'POST',
            headers: { 'X-WP-Nonce': list.rest_nonce },
        });

        if (!response.ok()) {
            throw new Error(`forms list ${action} failed: ${response.status()}`);
        }
    }

    /** In the open builder, go to Settings and follow a path of menu labels (e.g. Modules, Zapier). */
    async doOpenBuilderSettings(path: string[]) {
        const root = this.page.locator('#wpuf-form-builder');
        for (const label of ['Settings', ...path]) {
            await root.getByText(label, { exact: true }).locator('visible=true').last().click();
        }
    }

    /** Whether the builder store marks the form as having unsaved changes. */
    async getBuilderIsDirty(): Promise<boolean> {
        return this.page.evaluate(() => {
            const w = window as unknown as { wp: { data: { select: (s: string) => { getIsDirty: () => boolean } } }; wpuf?: { storeName?: string } };
            return !!w.wp.data.select(w.wpuf?.storeName || 'wpuf/form-builder').getIsDirty();
        });
    }

    /** Set the content of a builder rich-text setting (TinyMCE) by its setting name. */
    async doSetRichTextSetting(settingName: string, html: string) {
        // Develop's PHP wp_editor() posts the setting name (id wpuf_<key>_<post id>);
        // the branch editor's id is derived from it.
        const named = this.page.locator(`textarea[name="${settingName}"]`);
        const editorId = (await named.count()) ? (await named.first().getAttribute('id')) as string : `wpuf-editor-${settingName.replace(/\W+/g, '_')}`;
        await expect.poll(() => this.page.evaluate((id) => !!(window as unknown as { tinymce?: { get: (i: string) => { initialized?: boolean } | null } }).tinymce?.get(id)?.initialized, editorId), { timeout: 10000 }).toBe(true);
        await this.page.evaluate(([id, content]) => {
            const editor = (window as unknown as { tinymce: { get: (i: string) => { setContent: (c: string) => void; fire: (e: string) => void } } }).tinymce.get(id);
            editor.setContent(content);
            editor.fire('change');
        }, [editorId, html]);
    }

    /** Click a settings toggle by its input id and save the builder. */
    async doToggleSettingAndSave(id: string) {
        await this.page.locator(`label[for="${id}"]`).last().click();
        await this.doSaveBuilder();
    }

    /** Raw HTML of an admin URL as served (for checks on what the server prints). */
    async getAdminHtml(path: string): Promise<string> {
        const response = await this.page.goto(path);
        return response ? response.text() : '';
    }

    /**
     * Post the builder save AJAX request directly from the open builder page,
     * with the serialized builder form changed by `edit` (query-string pairs).
     * Returns the JSON response.
     */
    async doRawBuilderSave(edit: Record<string, string | null>): Promise<{ success: boolean; data?: unknown }> {
        return this.page.evaluate(async (changes) => {
            const form = document.getElementById('wpuf-form-builder') as HTMLFormElement;
            const params = new URLSearchParams(new FormData(form) as unknown as Record<string, string>);
            for (const [key, value] of Object.entries(changes)) {
                if (value === null) {
                    params.delete(key);
                } else {
                    params.set(key, value);
                }
            }
            const body = new URLSearchParams({
                action: 'wpuf_form_builder_save_form',
                form_data: params.toString(),
                form_fields: '[]',
                notifications: '[]',
            });
            const response = await fetch((window as unknown as { ajaxurl: string }).ajaxurl, { method: 'POST', credentials: 'same-origin', body });
            return response.json();
        }, edit);
    }

    /** Open the "add new" builder; returns the id of the created draft form. */
    async doOpenNewBuilder(postType: string): Promise<number> {
        await this.page.goto(`/wp-admin/admin.php?page=${builderPage[postType]}&action=add-new`);
        // The builder page (`action=edit&id=N`) or, with the admin app, its route (`#/post-forms/N/edit`).
        await this.page.waitForURL(BUILDER_URL);
        await expect(this.page.locator(Selectors.parity.builderSaveButton).first()).toBeVisible();
        return Number(builderFormId(this.page.url()));
    }

    /** Field types offered by the palette that add a field on click. */
    async getPaletteFieldTypes(): Promise<string[]> {
        return this.page.locator(Selectors.parity.paletteFieldButtons).evaluateAll(
            (buttons) => buttons.map((button) => button.getAttribute('data-form-field') || '').filter(Boolean));
    }

    /**
     * Click a palette button. Returns true when the stage gained a field, false when
     * the builder refused it with an alert (dismissed here).
     */
    async doAddFieldFromPalette(type: string): Promise<boolean> {
        const stage = this.page.locator(Selectors.parity.stageFields);
        const alert = this.page.locator(Selectors.parity.alertPopup);
        await this.doDismissAlerts();
        const before = await stage.count();
        await this.page.locator(Selectors.parity.paletteFieldButton(type)).first().click();
        await expect.poll(async () => (await stage.count()) > before || (await alert.count()) > 0, { message: `add ${type}`, timeout: 10000 })
            .toBeTruthy();
        const added = (await stage.count()) > before;
        await this.doDismissAlerts();
        return added;
    }

    /**
     * Drag a palette field type onto a builder drop zone with the mouse (the
     * builder's one dnd-kit context, D16): from the palette button to the
     * zone's center, or its top edge (`top`: insert first). React builder only.
     */
    async doDropTemplate(target: string, template: string, where: 'center' | 'top' = 'center') {
        const source = this.page.locator(Selectors.parity.paletteFieldButton(template)).first();
        await source.scrollIntoViewIfNeeded();
        const from = await source.boundingBox();
        await this.page.mouse.move(from!.x + from!.width / 2, from!.y + from!.height / 2);
        await this.page.mouse.down();
        await this.page.mouse.move(from!.x + from!.width / 2 - 10, from!.y + from!.height / 2 + 10, { steps: 4 });
        const zone = this.page.locator(target).first();
        await zone.scrollIntoViewIfNeeded();
        const to = await zone.boundingBox();
        const y = 'top' === where ? to!.y + 8 : to!.y + to!.height / 2;
        await this.page.mouse.move(to!.x + to!.width / 2, y, { steps: 16 });
        await this.page.waitForTimeout(100);
        await this.page.mouse.up();
        // dnd-kit swallows a click for a moment after a drag ends.
        await this.page.waitForTimeout(300);
    }

    /**
     * Comparable shape of the last top level stage field: label, form controls and
     * visible text, with the hover action bar left out (framework markup differs).
     */
    async getLastStageFieldShape(): Promise<{ label: string; controls: string[]; text: string; height: number; hidden: number }> {
        return this.page.locator(Selectors.parity.stageFields).evaluateAll((items) => {
            const top = items.filter((item) => !item.parentElement?.closest('li[class*="form-field-"]'));
            const live = top[top.length - 1] as HTMLElement;
            // Layout checks: row height (to 20px) and elements left invisible, e.g. a
            // preview whose stylesheet is missing.
            const height = Math.round(live.getBoundingClientRect().height / 20) * 20;
            const hidden = Array.from(live.querySelectorAll('*')).filter((node) => !node.closest('[class*="buttons"]')
                && 'hidden' === getComputedStyle(node).visibility).length;
            const clone = live.cloneNode(true) as HTMLElement;
            // Action bars and dnd-kit screen reader hints are framework markup, not field preview.
            clone.querySelectorAll('[class*="buttons"], [id^="DndDescribedBy"], [id^="DndLiveRegion"]').forEach((node) => node.remove());
            const controls = Array.from(clone.querySelectorAll('input, select, textarea')).map((control) => {
                const el = control as HTMLInputElement;
                const options = 'SELECT' === el.tagName
                    ? '[' + Array.from((el as unknown as HTMLSelectElement).options).map((option) => option.text.trim()).join('|') + ']'
                    : '';
                return `${el.tagName.toLowerCase()}:${el.type || ''}${el.placeholder ? ' ph=' + el.placeholder : ''}${options}`;
            });
            const label = (clone.querySelector('label')?.textContent || '').replace(/\s+/g, ' ').trim();
            // Text compared without whitespace: Vue templates add spaces between nodes, JSX does not.
            return { label, controls, text: (clone.textContent || '').replace(/\s+/g, ''), height, hidden };
        });
    }

    /** Text of the open SweetAlert, or '' when none is open. */
    async getAlertText(): Promise<string> {
        const alert = this.page.locator(Selectors.parity.alertPopup);
        return (await alert.count()) ? (await alert.first().innerText()).replace(/\s+/g, ' ').trim() : '';
    }

    /** Confirm any open SweetAlert (info or refusal) so the builder accepts clicks again. */
    async doDismissAlerts() {
        const alert = this.page.locator(Selectors.parity.alertPopup);
        while ((await alert.count()) > 0) {
            // Develop's custom field tip has "Don't show again" as its confirm button; click the same one on the branch.
            const dontShow = alert.first().getByRole('button', { name: "Don't show again" });
            await ((await dontShow.count()) ? dontShow.first() : this.page.locator(Selectors.parity.alertConfirm).first()).click();
            await expect(alert).toHaveCount(0);
        }
    }

    /**
     * Open the settings panel of the stage field at a position (top level order) and
     * expand every section. Returns false when that stage item is not visible
     * (e.g. hidden fields), so nothing can be edited through the UI.
     */
    async doOpenFieldSettings(position: number): Promise<boolean> {
        await this.doDismissAlerts();
        const field = this.page.locator(Selectors.parity.stageFields).nth(position);
        if (!(await field.isVisible())) {
            return false;
        }
        await field.scrollIntoViewIfNeeded();
        await field.hover();
        await field.getByText(Selectors.parity.stageFieldEdit, { exact: true }).first().click();
        await expect(this.page.locator(Selectors.parity.fieldOptionsPanel)).toBeVisible();
        await expect(this.page.locator(Selectors.parity.fieldOptionRows).first()).toBeVisible();
        const heads = this.page.locator(Selectors.parity.fieldOptionsSectionHeads);
        for (let i = 0; i < await heads.count(); i++) {
            const body = heads.nth(i).locator('xpath=..').locator(Selectors.parity.fieldOptionsSectionBody);
            if ((await body.count()) === 0 || !(await body.first().isVisible())) {
                await heads.nth(i).click();
            }
        }
        return true;
    }

    /**
     * Apply the same deterministic edit to every option row of the open panel:
     * text-like inputs get a value derived from the row, radios pick their last
     * option, checkboxes toggle, selects pick their last option. Rows of the
     * given types are skipped. Returns one line per row (type, label, what was done)
     * so both sites can be compared row by row.
     */
    async doFillFieldOptions(tag: string, skipRowTypes: string[] = []): Promise<string[]> {
        const rows = this.page.locator(Selectors.parity.fieldOptionRows);
        const done: string[] = [];
        for (let i = 0; i < await rows.count(); i++) {
            const row = rows.nth(i);
            const rowType = ((await row.getAttribute('class')) || '').split(/\s+/).find((c) => c.startsWith('panel-field-opt-')) || 'unknown';
            const label = ((await row.locator('label').allTextContents())[0] || '').replace(/\s+/g, ' ').trim().slice(0, 40);
            if (skipRowTypes.includes(rowType)) {
                done.push(`${rowType} | ${label} | skipped`);
                continue;
            }
            const actions: string[] = [];
            // Option list editor (dropdown, radio, checkbox, pricing, ...): add a row,
            // rename every row (and price it), then pick the last one as default.
            if (await row.locator('.option-field-option-chooser').count()) {
                await row.locator('.plus-buttons').last().click();
                const optionRows = row.locator('.option-field-option');
                const optionCount = await optionRows.count();
                for (let o = 0; o < optionCount; o++) {
                    const inputs = optionRows.nth(o).locator('input[type="text"], input[type="number"]');
                    await inputs.first().fill(`Choice ${tag} ${o}`);
                    await inputs.first().blur();
                    if ((await inputs.count()) > 1) {
                        await inputs.last().fill(String(10 + o));
                        await inputs.last().blur();
                    }
                }
                // Native inputs on develop; plugin-ui role=radio / role=checkbox on the branch (4.4c).
                const chooser = optionRows.last().locator('[role="radio"], [role="checkbox"]');
                if (await chooser.count()) {
                    await chooser.first().click();
                } else {
                    await optionRows.last().locator('input[type="radio"], input[type="checkbox"]').first().check({ force: true });
                }
                done.push(`${rowType} | ${label} | options=${optionCount}`);
                continue;
            }
            // Selectize hides the real <select> behind a search box: pick its last
            // option through the selectize API, as the native branch below does.
            const selectized = row.locator('select.selectized');
            for (let c = 0; c < await selectized.count(); c++) {
                // Only lists a user can reach: skip controls that are hidden or locked.
                const reachable = await selectized.nth(c).evaluate((el) => {
                    const control = el.nextElementSibling as HTMLElement | null;
                    return !(el as HTMLSelectElement).disabled && !!control && !!control.offsetParent;
                });
                if (!reachable) {
                    continue;
                }
                // Recorded by the option's label, as the branch's MultiSelect shows it (4.4c).
                const picked = await selectized.nth(c).evaluate((el) => {
                    const widget = (el as unknown as { selectize?: { options: Record<string, { text?: string }>; addItem: (v: string) => void } }).selectize;
                    const values = Array.from((el as HTMLSelectElement).options).map((o) => o.value).filter(Boolean);
                    const keys = widget ? Object.keys(widget.options) : [];
                    const value = values.length ? values[values.length - 1] : keys[keys.length - 1];
                    if (widget && value) {
                        widget.addItem(value);
                    }
                    return (widget && value && widget.options[value]?.text) || value || '';
                });
                if (picked) {
                    actions.push(`select=${picked}`);
                }
            }
            // Shared Select / MultiSelect wrappers (plugin-ui, branch 4.4c): driven where
            // develop's selectize lists are, before the row's boxes open more controls.
            // Select: last option by its text, like develop's custom dropdown below.
            const puiSelects = row.locator('[data-slot="select-trigger"]:visible:not([disabled]):not([data-disabled])');
            for (let c = 0; c < await puiSelects.count(); c++) {
                await puiSelects.nth(c).click();
                const options = this.page.locator('[role="option"]:visible');
                const optionCount = await options.count();
                if (optionCount) {
                    const text = ((await options.nth(optionCount - 1).textContent()) || '').trim();
                    await options.nth(optionCount - 1).click();
                    actions.push(`select=${text}`);
                }
            }
            // Shared MultiSelect wrapper (plugin-ui, branch 4.4c; develop: selectize):
            // add the last option, recorded by its value like the selectize branch above.
            const puiMulti = row.locator('[data-slot="smart-multi-select-trigger"]:visible:not(.pointer-events-none)'); // skips disabled lists, as above
            for (let c = 0; c < await puiMulti.count(); c++) {
                await puiMulti.nth(c).click();
                const options = this.page.locator('[data-slot="smart-multi-select-content"] [data-slot="command-group"]').first().locator('[data-slot="command-item"]:visible');
                const optionCount = await options.count();
                if (optionCount) {
                    const option = options.nth(optionCount - 1);
                    const picked = ((await option.textContent()) || '').trim();
                    await option.click();
                    actions.push(`select=${picked}`);
                }
                await this.page.keyboard.press('Escape');
            }
            const texts = row.locator('input[type="text"]:visible, input[type="number"]:visible, input[type="url"]:visible, input[type="email"]:visible, textarea:visible').filter({ hasNot: this.page.locator('xpath=self::*[ancestor::div[contains(@class,"selectize-input")]]') });
            for (let t = 0; t < await texts.count(); t++) {
                const input = texts.nth(t);
                if (!(await input.isEditable())) {
                    actions.push('readonly');
                    continue;
                }
                const isNumber = (await input.getAttribute('type')) === 'number';
                const value = isNumber ? String(7 + t) : /meta key/i.test(label) ? `p_${tag}_${t}` : `P ${tag} ${i}.${t}`;
                await input.fill(value);
                await input.blur();
                actions.push(`fill=${value}`);
            }
            // Native radios / checkboxes (develop) or the shared wrappers' role=radio /
            // role=checkbox with data-value (branch, 4.4c); their hidden inputs are skipped.
            const radios = row.locator('input[type="radio"]:visible:not([aria-hidden="true"]), [role="radio"]:visible');
            if (await radios.count()) {
                const radio = radios.last();
                if ('radio' === (await radio.getAttribute('role'))) {
                    await radio.click();
                } else {
                    await radio.check({ force: true });
                }
                actions.push(`radio=${(await radio.getAttribute('value')) ?? (await radio.getAttribute('data-value'))}`);
            }
            const checks = row.locator('input[type="checkbox"]:visible:not([aria-hidden="true"]), [role="checkbox"]:visible');
            for (let c = 0; c < await checks.count(); c++) {
                const check = checks.nth(c);
                await check.click({ force: true });
                actions.push(`toggle=${(await check.getAttribute('value')) ?? (await check.getAttribute('data-value'))}`);
            }
            const selects = row.locator('select:visible:not([disabled])');
            for (let c = 0; c < await selects.count(); c++) {
                const values = await selects.nth(c).locator('option').evaluateAll((options) => options.map((o) => (o as HTMLOptionElement).value));
                if (values.length) {
                    await selects.nth(c).selectOption(values[values.length - 1]);
                    actions.push(`select=${values[values.length - 1]}`);
                }
            }
            const customSelects = row.locator(Selectors.parity.customSelectButton);
            for (let c = 0; c < await customSelects.count(); c++) {
                await customSelects.nth(c).click();
                const options = row.locator(Selectors.parity.customSelectOption);
                const optionCount = await options.count();
                if (optionCount) {
                    const text = ((await options.nth(optionCount - 1).textContent()) || '').trim();
                    await options.nth(optionCount - 1).click();
                    actions.push(`select=${text}`);
                }
            }
            await this.doDismissAlerts();
            done.push(`${rowType} | ${label} | ${actions.join(', ') || 'no control'}`);
        }
        return done;
    }

    /**
     * G2 for form settings (task 4.4e): in the open builder's Settings, open the
     * tab `tab` and make one deterministic edit on every visible settings row
     * (rows revealed by an edit are filled too). Drives develop's controls
     * (native inputs, selectize) and the branch's (native, selectize or the
     * shared wrappers). Returns one reading per row: `label | actions`.
     */
    async doFillFormSettings(tab: string, tag: string): Promise<string[]> {
        const root = this.page.locator('#wpuf-form-builder');
        // The settings sidebar item (develop `wpuf-group/sidebar-item`, branch `group/sidebar-item`).
        await root.locator('li[class*="sidebar-item"]').filter({ hasText: new RegExp(`^\\s*${tab.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*$`) }).first().click();
        await this.page.waitForTimeout(400);
        // Next row = the first visible row not filled yet (develop fades rows in and
        // out over 200 ms and fetches taxonomy rows by AJAX, so indexes shift).
        const pending = root.locator('.wpuf-input-container:visible:not([data-parity-row])');
        const done: string[] = [];
        for (let i = 0; i < 80 && await pending.count(); i++) {
            // Tag the row so the locator keeps pointing at it (a filtered locator re-resolves).
            // Tab + index: develop keeps other tabs' rows in the page (hidden).
            const rowTag = `${tag}-${i}`;
            await pending.first().evaluate((el, n) => el.setAttribute('data-parity-row', n), rowTag);
            const row = root.locator(`.wpuf-input-container[data-parity-row="${rowTag}"]`);
            const label = (((await row.locator('label').first().textContent().catch(() => '')) || '').trim().replace(/\s+/g, ' ')) || `row ${i}`;
            const actions: string[] = [];

            // Selectize lists (develop; branch before 4.4e): last option through the API.
            const selectized = row.locator('select.selectized');
            for (let c = 0; c < await selectized.count(); c++) {
                const picked = await selectized.nth(c).evaluate((el) => {
                    const select = el as HTMLSelectElement & { selectize?: { options: Record<string, { text?: string }>; addItem: (v: string) => void; setValue: (v: string) => void } };
                    const control = el.nextElementSibling as HTMLElement | null;
                    if (select.disabled || !control || !control.offsetParent || !select.selectize) {
                        return '';
                    }
                    const keys = Object.keys(select.selectize.options).filter(Boolean);
                    const value = keys[keys.length - 1];
                    if (!value) {
                        return '';
                    }
                    if (select.multiple) {
                        select.selectize.addItem(value);
                    } else {
                        select.selectize.setValue(value);
                    }
                    return select.selectize.options[value]?.text || value;
                });
                if (picked) {
                    actions.push(`select=${picked}`);
                }
            }
            // Shared Select / MultiSelect wrappers: last option by its text.
            const puiSelects = row.locator('[data-slot="select-trigger"]:visible:not([disabled]):not([data-disabled])');
            for (let c = 0; c < await puiSelects.count(); c++) {
                await puiSelects.nth(c).click();
                const options = this.page.locator('[data-slot="select-item"]:visible');
                // The list renders after its open animation starts.
                await options.first().waitFor({ state: 'visible', timeout: 3000 }).catch(() => {});
                const count = await options.count();
                if (count) {
                    const text = ((await options.nth(count - 1).textContent()) || '').trim();
                    await options.nth(count - 1).click();
                    actions.push(`select=${text}`);
                }
                // The list must be closed before the next row (it can cover its trigger).
                if (await this.page.locator('[data-slot="select-content"]:visible').count()) {
                    await this.page.keyboard.press('Escape');
                }
                await expect(this.page.locator('[data-slot="select-content"]:visible')).toHaveCount(0);
            }
            const puiMulti = row.locator('[data-slot="smart-multi-select-trigger"]:visible:not(.pointer-events-none)');
            for (let c = 0; c < await puiMulti.count(); c++) {
                await puiMulti.nth(c).click();
                const options = this.page.locator('[data-slot="smart-multi-select-content"] [data-slot="command-group"]').first().locator('[data-slot="command-item"]:visible');
                const count = await options.count();
                if (count) {
                    const text = ((await options.nth(count - 1).textContent()) || '').trim();
                    await options.nth(count - 1).click();
                    actions.push(`select=${text}`);
                }
                await this.page.keyboard.press('Escape');
            }
            // Native selects.
            const selects = row.locator('select:visible:not(.selectized):not([disabled])');
            for (let c = 0; c < await selects.count(); c++) {
                const options = await selects.nth(c).locator('option').evaluateAll((list) => list.map((o) => ({ value: (o as HTMLOptionElement).value, text: (o.textContent || '').trim() })));
                const last = options[options.length - 1];
                if (last) {
                    await selects.nth(c).selectOption(last.value);
                    actions.push(`select=${last.text}`);
                }
            }
            // Shared ColorPicker (branch): open it and type the hex develop's native input gets.
            const colorTriggers = row.locator('[data-slot="popover-trigger"][aria-label]:visible');
            for (let c = 0; c < await colorTriggers.count(); c++) {
                await colorTriggers.nth(c).click();
                const hex = this.page.locator('[data-slot="popover-content"] .components-input-control__input:visible').first();
                await hex.fill('123456');
                await hex.press('Enter');
                await this.page.keyboard.press('Escape');
                actions.push('fill=#123456');
            }
            // React date settings (schedule From / To): a picker instead of develop's text input;
            // pick the date / time develop's run types (2026-12-01 10:00), so both store the same text.
            const datePickers = row.locator('[data-wpuf-date-setting] [data-slot="popover-trigger"]:visible');
            for (let d = 0; d < await datePickers.count(); d++) {
                await datePickers.nth(d).click();
                const pop = this.page.locator('[data-slot="popover-content"]:visible').last();
                for (let n = 0; n < 36 && !(await pop.getByText('December 2026').count()); n++) {
                    await pop.locator('button[aria-label*="next" i]').first().click();
                }
                await pop.locator('[role="gridcell"]:not([data-outside]) button, button[name="day"]').filter({ hasText: /^1$/ }).first().click();
                await pop.locator('[aria-label="Hour"]').click();
                await this.page.locator('[role="option"][data-value="10"]').first().click();
                await this.page.keyboard.press('Escape');
                actions.push('fill=2026-12-01 10:00');
            }
            // Text-like inputs.
            const inputs = row.locator('input:visible:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([aria-hidden="true"]), textarea:visible')
                .filter({ hasNot: this.page.locator('xpath=self::*[ancestor::div[contains(@class,"selectize-input")]]') });
            for (let t = 0; t < await inputs.count(); t++) {
                const input = inputs.nth(t);
                if (!(await input.isEditable())) {
                    actions.push('readonly');
                    continue;
                }
                const type = (await input.getAttribute('type')) || 'text';
                const isDate = /datepicker/.test((await input.getAttribute('class')) || '');
                // Text from the row label (not its index): rows one builder adds or drops do not shift the values.
                const value = 'number' === type ? String(7 + t) : 'color' === type ? '#123456' : isDate ? '2026-12-01 10:00' : `S ${tag} ${label.slice(0, 24)}.${t}`;
                if ('color' === type) {
                    await input.evaluate((el, v) => {
                        const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
                        setter.call(el, v);
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }, value);
                } else {
                    await input.fill(value);
                }
                if (isDate) {
                    await this.page.keyboard.press('Escape');
                }
                await input.blur();
                actions.push(`fill=${value}`);
            }
            // Toggles and checkboxes (develop's toggle input is sr-only) / wrapper switches.
            const boxes = row.locator('input[type="checkbox"]:not([aria-hidden="true"]), [role="switch"]:visible, [role="checkbox"]:visible');
            for (let c = 0; c < await boxes.count(); c++) {
                // display:none inputs cannot be used (develop's sr-only toggles have a box).
                if (!(await boxes.nth(c).boundingBox())) {
                    continue;
                }
                await boxes.nth(c).click({ force: true });
                actions.push('toggle');
            }
            // Picture radios: the last one.
            const radios = row.locator('input[type="radio"]:not([aria-hidden="true"]), [role="radio"]:visible');
            let radio = null;
            for (let r = await radios.count() - 1; r >= 0; r--) {
                if (await radios.nth(r).boundingBox()) {
                    radio = radios.nth(r);
                    break;
                }
            }
            if (radio) {
                await radio.click({ force: true });
                actions.push(`radio=${(await radio.getAttribute('value')) ?? (await radio.getAttribute('data-value'))}`);
            }
            await this.doDismissAlerts();
            done.push(`${label} | ${actions.join(', ') || 'no control'}`);
            await this.page.waitForTimeout(300);
            await this.page.waitForLoadState('networkidle');
        }
        return done;
    }

    /**
     * Settings condition matrix (task 4.4e): in Settings tab `tab`, set every
     * toggle / checkbox (on, off) and every select (each option) and record the
     * settings rows shown after each state. A control is then left in the state
     * that showed the most rows, and controls that appeared are probed too, so
     * nested conditions are reached. Same walk on both builders.
     * Returns `label = state -> shown labels` lines.
     */
    async doProbeSettingsConditions(tab: string): Promise<string[]> {
        const root = this.page.locator('#wpuf-form-builder');
        await root.locator('li[class*="sidebar-item"]').filter({ hasText: new RegExp(`^\\s*${tab.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*$`) }).first().click();
        await this.page.waitForTimeout(500);
        // Develop fades rows with jQuery's default 400 ms.
        const settle = async () => {
            await this.page.waitForTimeout(600);
            await this.page.waitForLoadState('networkidle');
        };
        const shown = async () => (await root.locator('.wpuf-input-container:visible').evaluateAll((rows) => rows
            .map((row) => ((row.querySelector('label')?.textContent) || '').trim().replace(/\s+/g, ' '))
            .filter((text) => text && !text.startsWith('Default ')))).join(' ; ');
        const out: string[] = [];
        const probed = new Set<string>();

        for (let guard = 0; guard < 60; guard++) {
            // Next unprobed control row.
            const rows = root.locator('.wpuf-input-container:visible');
            let target = -1;
            let label = '';
            for (let i = 0; i < await rows.count(); i++) {
                const text = (((await rows.nth(i).locator('label').first().textContent().catch(() => '')) || '').trim().replace(/\s+/g, ' '));
                // Multi-selects never show or hide rows: not probed (develop's are selectized <select multiple>).
                const hasControl = await rows.nth(i).locator('input[type="checkbox"]:not([aria-hidden="true"]), [role="switch"], [role="checkbox"], select:not([multiple]), [data-slot="select-trigger"]').count();
                if (text && !text.startsWith('Default ') && hasControl && !probed.has(text)) {
                    target = i;
                    label = text;
                    break;
                }
            }
            if (target < 0) {
                break;
            }
            probed.add(label);
            const row = rows.nth(target);
            await row.evaluate((el) => el.setAttribute('data-parity-probe', ''));
            const probe = root.locator('.wpuf-input-container[data-parity-probe]').last();

            const toggle = probe.locator('input[type="checkbox"]:not([aria-hidden="true"]), [role="switch"], [role="checkbox"]').first();
            const states: { name: string; count: number; apply: () => Promise<void> }[] = [];
            if (await toggle.count() && await toggle.boundingBox()) {
                for (const name of ['flip', 'flip back']) {
                    await toggle.click({ force: true });
                    await settle();
                    const view = await shown();
                    out.push(`${label} = ${name} -> ${view}`);
                    states.push({ name, count: view.split(' ; ').length, apply: async () => { await toggle.click({ force: true }); await settle(); } });
                }
                // Leave it in the state that showed more rows.
                if (states[0].count > states[1].count) {
                    await toggle.click({ force: true });
                    await settle();
                }
            } else {
                // Options of the select, by text.
                const selectized = probe.locator('select.selectized:not([multiple])').first();
                const native = probe.locator('select:not(.selectized):not([multiple])').first();
                const trigger = probe.locator('[data-slot="select-trigger"]').first();
                let texts: string[] = [];
                let pick: (text: string) => Promise<void>;
                if (await selectized.count()) {
                    texts = await selectized.evaluate((el) => Object.values((el as unknown as { selectize: { options: Record<string, { text: string }> } }).selectize.options).map((o) => o.text));
                    pick = async (text) => {
                        await selectized.evaluate((el, t) => {
                            const widget = (el as unknown as { selectize: { options: Record<string, { text: string }>; setValue: (v: string) => void } }).selectize;
                            const key = Object.keys(widget.options).find((k) => widget.options[k].text === t);
                            if (key !== undefined) {
                                widget.setValue(key);
                            }
                        }, text);
                    };
                } else if (await trigger.count()) {
                    await trigger.click();
                    const items = this.page.locator('[data-slot="select-item"]:visible');
                    await items.first().waitFor({ state: 'visible', timeout: 3000 }).catch(() => {});
                    texts = (await items.allInnerTexts()).map((t) => t.trim());
                    await this.page.keyboard.press('Escape');
                    pick = async (text) => {
                        await trigger.click();
                        await this.page.locator('[data-slot="select-item"]:visible').filter({ hasText: new RegExp(`^\\s*${text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*$`) }).first().click();
                    };
                } else if (await native.count()) {
                    texts = (await native.locator('option').allInnerTexts()).map((t) => t.trim());
                    pick = async (text) => { await native.selectOption({ label: text }); };
                } else {
                    continue;
                }
                // Selects with many options (post types, pages, templates) only show
                // rows by a few values: probe the first 12. Empty placeholders
                // ("-- Select --") are skipped: develop's selectize does not list them.
                let best = { text: '', count: -1 };
                for (const text of texts.filter((t) => !/^-+\s*Select\b/i.test(t)).slice(0, 12)) {
                    await pick!(text);
                    await settle();
                    const view = await shown();
                    out.push(`${label} = ${text} -> ${view}`);
                    if (view.split(' ; ').length > best.count) {
                        best = { text, count: view.split(' ; ').length };
                    }
                }
                if (best.text) {
                    await pick!(best.text);
                    await settle();
                }
            }
        }
        return out;
    }

    async doClose() {
        await this.context.close();
    }
}
