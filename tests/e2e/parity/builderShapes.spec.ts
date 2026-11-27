import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import * as path from 'path';
import { parityDir, paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * Builder edits store the same shapes as develop (task 1.5, B12 read_only) and
 * a save keeps what the builder does not edit (B13, 1.10). The B11 condition
 * shape is unit tested (conditionalUtils.test.js) until the conditional logic
 * UI is wired (task 4.4d, B14).
 */
test.describe('Parity builder shapes', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0005 : read only stores the develop shape; integrations kept', { tag: ['@Parity', '@Test_PAR0005'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const fixture = 'post-form-conditions.json';
        const stored: Record<string, FormDump> = {};

        for (const name of ['develop', 'branch'] as const) {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, fixture);
            const admin = await ParitySitePage.doOpen(browser, site);
            await admin.doOpenBuilder('wpuf_forms', formId);
            expect(await admin.doOpenFieldSettings(3), `${name}: open Nickname settings`).toBe(true);
            await admin.doCheckFieldOption('Make this field read only');
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = parity.readForm(site, formId);
            await test.info().attach(`${name}-stored.json`, { path: parity.doWriteJson(test.info().outputPath(`${name}-stored.json`), stored[name]) });
        }

        const nickname = (form: FormDump) => form.fields[3].post_content as Record<string, unknown>;
        expect(nickname(stored.branch).read_only, 'read_only stored as a boolean').toBe(true);
        // Develop rewrites the untouched wpuf_cond when the panel opens (ground-truth
        // 1.18); the branch keeps it as stored (Q6). Everything else must match.
        const { wpuf_cond: branchCond, ...branchRest } = nickname(stored.branch);
        const { wpuf_cond: _developCond, ...developRest } = nickname(stored.develop);
        expect(branchRest, 'branch field equals develop field').toStrictEqual(developRest);
        expect(branchCond, 'untouched conditions kept').toStrictEqual((parity.readFixture(fixture).fields[3].post_content as Record<string, unknown>).wpuf_cond);
        expect(stored.branch.meta.integrations, 'integrations kept').toStrictEqual(parity.readFixture(fixture).meta.integrations);
    });

    test('PAR0006 : without Pro a builder save keeps hidden custom taxonomy fields (1.11)', { tag: ['@Parity', '@Test_PAR0006'] }, () => {
        const out = parityWp(paritySite('branch'), [
            'eval-file', path.join(parityDir, 'wp', 'check-hidden-taxonomy.php'), '--skip-plugins=wpuf-pro',
        ]);
        const result = JSON.parse(out.trim().split('\n').pop() || '{}');

        expect(result.pro_active, 'Pro skipped').toBe(false);
        expect(result.kept, 'title kept, hidden taxonomy kept, removed field deleted').toEqual([true, true, false]);
    });

    test('PAR0007 : duplicate copies integrations and version; delete removes the field posts (1.12)', { tag: ['@Parity', '@Test_PAR0007'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const formId = new ParityPage().doSeedForm(branch, 'post-form-conditions.json');
        parityWp(branch, ['post', 'meta', 'update', String(formId), 'wpuf_form_version', '4.3.13']);
        const latestForm = () => Number(parityWp(branch, ['post', 'list', '--post_type=wpuf_forms', '--post_status=any', '--orderby=ID', '--order=DESC', '--posts_per_page=1', '--field=ID']).trim());
        const meta = (id: number, key: string) => parityWp(branch, ['post', 'meta', 'get', String(id), key, '--format=json']).trim();
        const fieldCount = (id: number) => Number(parityWp(branch, ['post', 'list', '--post_type=wpuf_input', `--post_parent=${id}`, '--post_status=any', '--format=count']).trim());

        const admin = await ParitySitePage.doOpen(browser, branch);
        await admin.doFormsListAction('wpuf-post-forms', formId, 'duplicate');
        const copyId = latestForm();
        expect(copyId, 'a copy was made').toBeGreaterThan(formId);
        expect(meta(copyId, 'integrations'), 'integrations copied').toBe(meta(formId, 'integrations'));
        expect(meta(copyId, 'wpuf_form_version'), 'version copied').toBe(meta(formId, 'wpuf_form_version'));

        parityWp(branch, ['post', 'update', String(copyId), '--post_status=trash']);
        const fieldsBefore = fieldCount(copyId);
        await admin.doFormsListAction('wpuf-post-forms', copyId, 'delete');
        await admin.doClose();

        expect(fieldsBefore, 'copy had field posts').toBeGreaterThan(0);
        expect(parityWp(branch, ['post', 'list', '--post_type=wpuf_forms', '--post_status=any', `--post__in=${copyId}`, '--format=count']).trim(), 'form deleted').toBe('0');
        expect(fieldCount(copyId), 'field posts deleted with the form').toBe(0);
    });

    test('PAR0008 : forms list REST limits post types and sorts newest first; pack hook fires once (1.13)', { tag: ['@Parity', '@Test_PAR0008'] }, () => {
        const out = parityWp(paritySite('branch'), ['eval-file', path.join(parityDir, 'wp', 'check-list-and-pack-hooks.php')]);

        expect(JSON.parse(out.trim().split('\n').pop() || '{}')).toEqual({
            post_rejected: 400,
            profile_ok: 200,
            newest_first: true,
            per_page_capped: true,
            hook_calls: 1,
            hook_id_saved: true,
        });
    });

    test('PAR0009 : registration toggle off leaves the key out so the module stops (1.14)', { tag: ['@Parity', '@Test_PAR0009'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const formId = new ParityPage().doSeedForm(branch, 'registration-form.json');
        const zapier = () => parityWp(branch, ['eval', `$s = get_post_meta( ${formId}, "wpuf_form_settings", true ); echo array_key_exists( "enable_zapier", (array) $s ) ? $s["enable_zapier"] : "ABSENT";`]).trim();
        const admin = await ParitySitePage.doOpen(browser, branch);
        await admin.doOpenBuilder('wpuf_profile', formId);
        await admin.doOpenBuilderSettings(['Modules', 'Zapier']);

        await admin.doToggleSettingAndSave('enable_zapier');
        const whenOn = zapier();
        await admin.doToggleSettingAndSave('enable_zapier');
        const whenOff = zapier();
        await admin.doClose();

        expect(whenOn, 'on').toBe('on');
        expect(whenOff, 'off = key absent, like develop').toBe('ABSENT');
    });

    test('PAR0010 : visibility for subscribed users stores the pack id like develop (1.18)', { tag: ['@Parity', '@Test_PAR0010'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const stored: Record<string, unknown> = {};
        const packs: Record<string, string> = {};

        for (const name of ['develop', 'branch'] as const) {
            const site = paritySite(name);
            packs[name] = parityWp(site, ['post', 'create', '--post_type=wpuf_subscription', '--post_status=publish', '--post_title=PAR0010 pack', '--porcelain']).trim();
            // Packs saved from the UI carry _sort_order; the pack list queries by it.
            parityWp(site, ['post', 'meta', 'update', packs[name], '_sort_order', '1']);
            // The pack goes even when the test fails, so retries never see two packs with this title.
            try {
                const formId = parity.doSeedForm(site, 'post-form-conditions.json');
                const admin = await ParitySitePage.doOpen(browser, site);
                await admin.doOpenBuilder('wpuf_forms', formId);
                expect(await admin.doOpenFieldSettings(3), `${name}: open Nickname settings`).toBe(true);
                await admin.doSetSubscriptionVisibility('PAR0010 pack');
                await admin.doSaveBuilder();
                await admin.doClose();
                stored[name] = (parity.readForm(site, formId).fields[3].post_content as Record<string, unknown>).wpuf_visibility;
            } finally {
                parityWp(site, ['post', 'delete', packs[name], '--force']);
            }
        }

        expect(stored.branch, 'branch stores the pack id').toEqual({ selected: 'subscribed_users', choices: [packs.branch] });
        expect(stored.develop, 'develop stores the pack id').toEqual({ selected: 'subscribed_users', choices: [packs.develop] });
    });

    test('PAR0012 : registration email body edits through TinyMCE and saves under notification (1.20, B31)', { tag: ['@Parity', '@Test_PAR0012'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const formId = new ParityPage().doSeedForm(branch, 'registration-form.json');
        parityWp(branch, ['eval', `$s = (array) get_post_meta( ${formId}, "wpuf_form_settings", true ); $s["user_notification"] = "on"; $s["notification_type"] = "email_verification"; update_post_meta( ${formId}, "wpuf_form_settings", $s );`]);
        const admin = await ParitySitePage.doOpen(browser, branch);
        await admin.doOpenBuilder('wpuf_profile', formId);
        await admin.doOpenBuilderSettings(['Notification Settings']);
        await expect.poll(() => admin.page.evaluate(() => !!(window as unknown as { tinymce?: { get: (i: string) => { initialized?: boolean } | null } }).tinymce?.get('wpuf-editor-wpuf_settings_notification_verification_body_')?.initialized)).toBe(true);
        const afterOpen = await admin.page.evaluate(() => {
            const wpData = (window as unknown as { wp: { data: { select: (s: string) => { getSettings: () => Record<string, unknown> } } }; wpuf?: { storeName?: string } });
            return wpData.wp.data.select(wpData.wpuf?.storeName || 'wpuf/form-builder').getSettings().notification ?? null;
        });
        expect(afterOpen, 'opening the editor writes nothing').toBeNull();
        await admin.doSetRichTextSetting('wpuf_settings[notification][verification_body]', '<p>PAR0012 {activation_link}</p>');
        await admin.doSaveBuilder();
        await admin.doClose();

        const body = parityWp(branch, ['eval', `$s = get_post_meta( ${formId}, "wpuf_form_settings", true ); echo $s["notification"]["verification_body"] ?? "ABSENT";`]).trim();
        // TinyMCE HTML, as develop's builder save stores it (PAR0034).
        expect(body).toBe('<p>PAR0012 {activation_link}</p>');
    });

    test('PAR0013 : opening field options (dropdown, text with icon) writes nothing (1.20, B33)', { tag: ['@Parity', '@Test_PAR0013'] }, async ({ browser }) => {
        const branch = paritySite('branch');
        const formId = new ParityPage().doSeedForm(branch, 'post-form-conditions.json');
        const admin = await ParitySitePage.doOpen(browser, branch);
        await admin.doOpenBuilder('wpuf_forms', formId);
        const dirty: Record<string, boolean> = {};

        for (const [label, position] of [['dropdown', 2], ['text', 3]] as const) {
            expect(await admin.doOpenFieldSettings(position), `open ${label}`).toBe(true);
            await admin.page.waitForTimeout(500);
            dirty[label] = await admin.getBuilderIsDirty();
        }
        await admin.doClose();

        expect(dirty).toEqual({ dropdown: false, text: false });
    });

    test('PAR0011 : both subscription save paths store one shape and fire the pack hooks (1.21, B36)', { tag: ['@Parity', '@Test_PAR0011'] }, () => {
        const out = parityWp(paritySite('branch'), ['eval-file', path.join(parityDir, 'wp', 'check-subscription-save.php')]);
        const result = JSON.parse(out.trim().split('\n').pop() || '{}');
        const shape = { _billing_amount: '9.99', _post_expiration_time: '7 day', _post_expiration_number: '7', _post_expiration_period: 'day', postnum_rollback: 'yes' };

        expect(result.rest, 'REST save').toEqual(shape);
        expect(result.classic, 'classic metabox save').toEqual(shape);
        expect(result.rest_hooks, 'REST hooks, once each, in order').toEqual([
            'wpuf_before_update_subscription_pack',
            'wpuf_before_update_subscription_pack_meta',
            'wpuf_after_update_subscription_pack_meta',
            'wpuf_update_subscription_pack',
        ]);
        expect(result.classic_hooks).toEqual(['wpuf_update_subscription_pack']);
    });

    test('PAR0014 : untouched subscription save leaves the pack byte-identical (1.9, G3)', { tag: ['@Parity', '@Test_PAR0014'] }, () => {
        const out = parityWp(paritySite('branch'), ['eval-file', path.join(parityDir, 'wp', 'check-subscription-noop.php')]);

        expect(JSON.parse(out.trim().split('\n').pop() || '{}')).toEqual({ saved: true, post: true, changed: [] });
    });
});
