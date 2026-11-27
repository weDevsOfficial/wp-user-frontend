import { test, expect } from '@playwright/test';
import { ParityPage, ParitySitePage, type FormDump } from '../pages/parity';
import { paritySite, paritySitesConfigured, parityWp } from '../utils/paritySites';

/**
 * Registration builder settings (task 4.5a, B15): every settings tab of a
 * registration form, filled through each builder's own UI, stores the same
 * `wpuf_form_settings` as develop (PAR0031), and every toggle / select shows
 * the same rows (PAR0032). Develop's save canonicalization is not copied (Q6).
 */
const FIXTURE = 'registration-form.json';
type Settings = Record<string, unknown>;

// Keys develop writes on every save without an edit (rendered inputs posted;
// the hidden wpuf_user_status follows the approval toggle). Folded only when the
// branch kept the stored value.
// Develop's save also drops stored keys it does not render (`redirect_to` on a
// registration form) and adds the rendered defaults (`notification` group, page
// ids, Mailchimp's integration wpuf_cond: 4.5b).
const CANONICAL = ['mob_number', 'sms_body', 'sms_enable', 'sms_sender_name', 'wpuf_user_status', 'redirect_to', 'notification', 'reg_page_id', 'profile_page_id', 'integrations'];
// Rows left out on both sides (none since 4.5b ported Mailchimp's conditions).
const DEFERRED_ROWS: string[] = [];
const kept = (list: string[] = []) => list.filter((row) => !DEFERRED_ROWS.some((prefix) => row.startsWith(prefix)));

async function tabsOf(admin: ParitySitePage): Promise<string[]> {
    await admin.doOpenBuilderSettings([]);
    return (await admin.page.locator('#wpuf-form-builder li[class*="sidebar-item"]').allInnerTexts()).map((t) => t.trim()).filter(Boolean);
}

test.describe('Parity registration settings', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');
    test.describe.configure({ mode: 'parallel' });

    test('PAR0031 : registration settings: filling every row of every tab stores the same as develop', { tag: ['@Parity', '@Test_PAR0031'] }, async ({ browser }) => {
        test.setTimeout(300_000);
        const parity = new ParityPage();
        const stored: Partial<Record<'develop' | 'branch', FormDump>> = {};
        const rows: Partial<Record<'develop' | 'branch', string[]>> = {};
        const tabs: Partial<Record<'develop' | 'branch', string[]>> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE, 'Parity registration settings');
            const admin = await ParitySitePage.doOpen(browser, site);
            await admin.doOpenBuilder('wpuf_profile', formId);
            tabs[name] = await tabsOf(admin);
            rows[name] = [];
            for (const [i, tab] of tabs[name]!.entries()) {
                rows[name]!.push(`# ${tab}`, ...(await admin.doFillFormSettings(tab, `r${i}`)));
            }
            await admin.doSaveBuilder();
            await admin.doClose();
            stored[name] = parity.readForm(site, formId);
        }));

        await test.info().attach('rows.json', { path: parity.doWriteJson(test.info().outputPath('rows.json'), { tabs, rows }) });
        await test.info().attach('stored.json', { path: parity.doWriteJson(test.info().outputPath('stored.json'), stored) });

        expect(tabs.branch, 'same settings tabs').toEqual(tabs.develop);
        expect.soft(kept(rows.branch), 'same rows and edits').toStrictEqual(kept(rows.develop));
        const fixture = (parity.readFixture(FIXTURE).meta as Record<string, Settings>).wpuf_form_settings;
        const dev = JSON.parse(JSON.stringify((stored.develop!.meta as Record<string, Settings>).wpuf_form_settings)) as Settings;
        const br = (stored.branch!.meta as Record<string, Settings>).wpuf_form_settings;
        for (const key of CANONICAL) {
            if (JSON.stringify(br[key]) === JSON.stringify(fixture[key])) {
                if (key in br) {
                    dev[key] = br[key];
                } else {
                    delete dev[key];
                }
            }
        }
        expect(br, 'registration settings equal develop').toStrictEqual(dev);
    });

    test('PAR0032 : registration settings: every condition shows the same rows as develop', { tag: ['@Parity', '@Test_PAR0032'] }, async ({ browser }) => {
        test.setTimeout(300_000);
        const parity = new ParityPage();
        const lines: Partial<Record<'develop' | 'branch', string[]>> = {};

        await Promise.all((['develop', 'branch'] as const).map(async (name) => {
            const site = paritySite(name);
            const formId = parity.doSeedForm(site, FIXTURE, 'Parity registration conditions');
            const admin = await ParitySitePage.doOpen(browser, site);
            await admin.doOpenBuilder('wpuf_profile', formId);
            lines[name] = [];
            for (const tab of await tabsOf(admin)) {
                lines[name]!.push(`# ${tab}`, ...(await admin.doProbeSettingsConditions(tab)));
            }
            await admin.doClose();
        }));

        await test.info().attach('conditions.json', { path: parity.doWriteJson(test.info().outputPath('conditions.json'), lines) });
        expect(kept(lines.branch), 'rows shown per control state').toStrictEqual(kept(lines.develop));
    });

    test('PAR0033 : approval stored only as wpuf_user_status (templates) shows the toggle on and an untouched save keeps it', { tag: ['@Parity', '@Test_PAR0033'] }, async ({ browser }) => {
        const parity = new ParityPage();
        const site = paritySite('branch');
        const formId = parity.doSeedForm(site, FIXTURE, 'Parity approval template');
        parityWp(site, ['eval', `$s = get_post_meta( ${formId}, 'wpuf_form_settings', true ); unset( $s['user_status'] ); $s['wpuf_user_status'] = 'pending'; update_post_meta( ${formId}, 'wpuf_form_settings', $s );`]);
        const admin = await ParitySitePage.doOpen(browser, site);
        await admin.doOpenBuilder('wpuf_profile', formId);
        await admin.doOpenBuilderSettings([]);
        const toggle = admin.page.locator('.wpuf-input-container', { hasText: 'Required Approval After Registration' }).locator('[role="switch"]');
        await expect(toggle, 'approval toggle shows on').toHaveAttribute('aria-checked', 'true');
        await admin.doSaveBuilder({ untouched: true });
        let settings = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        expect(settings.wpuf_user_status, 'untouched save keeps pending').toBe('pending');
        expect('user_status' in settings, 'nothing added').toBe(false);

        // Switching it off writes both keys (registration toggle off = key absent).
        await toggle.click();
        await admin.doSaveBuilder();
        await admin.doClose();
        settings = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
        expect(settings.wpuf_user_status, 'off = approved').toBe('approved');
        expect('user_status' in settings, 'toggle off leaves user_status out').toBe(false);
    });
    for (const tab of ['Visual', 'Code'] as const) {
        test(`PAR0034 : editing the email body in the ${tab} tab stores what develop stores`, { tag: ['@Parity', '@Test_PAR0034'] }, async ({ browser }) => {
            const parity = new ParityPage();
            const stored: Record<string, Settings> = {};

            await Promise.all((['develop', 'branch'] as const).map(async (name) => {
                const site = paritySite(name);
                const formId = parity.doSeedForm(site, FIXTURE, `Parity email body ${tab}`);
                parityWp(site, ['eval', `$s = (array) get_post_meta( ${formId}, 'wpuf_form_settings', true ); $s['user_notification'] = 'on'; $s['notification_type'] = 'email_verification'; update_post_meta( ${formId}, 'wpuf_form_settings', $s );`]);
                const admin = await ParitySitePage.doOpen(browser, site);
                const page = admin.page;
                await admin.doOpenBuilder('wpuf_profile', formId);
                await admin.doOpenBuilderSettings([]);
                await page.locator('#wpuf-form-builder li[class*="sidebar-item"]').filter({ hasText: /^\s*Notification Settings\s*$/ }).first().click();
                // Develop's PHP wp_editor() has the setting name; the branch editor's id derives from it.
                const named = page.locator('textarea[name="wpuf_settings[notification][verification_body]"]');
                const id = (await named.count()) ? await named.first().getAttribute('id') : 'wpuf-editor-wpuf_settings_notification_verification_body_';
                await expect.poll(() => page.evaluate((editor) => !!(window as unknown as { tinymce?: { get: (i: string) => { initialized?: boolean } | null } }).tinymce?.get(editor as string)?.initialized, id)).toBe(true);
                // Typed like a user: clear the body, two paragraphs, one bold word.
                if ('Visual' === tab) {
                    await page.frameLocator(`#${id}_ifr`).locator('body').click();
                    await page.keyboard.press('ControlOrMeta+a');
                    await page.keyboard.press('Backspace');
                    await page.keyboard.type('Hi ');
                    await page.keyboard.press('ControlOrMeta+b');
                    await page.keyboard.type('{username}');
                    await page.keyboard.press('ControlOrMeta+b');
                    await page.keyboard.type(',');
                    await page.keyboard.press('Enter');
                    await page.keyboard.type('PAR0034 {activation_link}');
                } else {
                    await page.locator(`#${id}-html`).click();
                    await page.locator(`textarea#${id}`).click();
                    await page.keyboard.press('ControlOrMeta+a');
                    await page.keyboard.press('Backspace');
                    await page.keyboard.type('Hi <strong>{username}</strong>,');
                    await page.keyboard.press('Enter');
                    await page.keyboard.press('Enter');
                    await page.keyboard.type('PAR0034 {activation_link}');
                }
                await admin.doSaveBuilder();
                await admin.doClose();
                stored[name] = (parity.readForm(site, formId).meta as Record<string, Settings>).wpuf_form_settings;
            }));

            const body = (s: Settings) => (s.notification as Record<string, string>).verification_body;
            if ('Code' === tab) {
                // Develop's save copied tinyMCE.getContent() into the textarea even in the
                // Code tab, where TinyMCE still holds the text without wpautop, so the
                // typed paragraph break became a space (develop bug). The branch keeps it
                // (wpautop, as switching back to Visual shows it). Agreed deviation, 4.5a.
                expect(body(stored.develop), 'develop collapses the paragraphs').toBe('<p>Hi <strong>{username}</strong>, PAR0034 {activation_link}</p>');
                // Line breaks as a form post stores them (CRLF, owner decision 2026-10-05).
                expect(body(stored.branch), 'branch keeps them').toBe('<p>Hi <strong>{username}</strong>,</p>\r\n<p>PAR0034 {activation_link}</p>');
                return;
            }
            expect(body(stored.branch), 'verification_body').toBe(body(stored.develop));
        });
    }
});
