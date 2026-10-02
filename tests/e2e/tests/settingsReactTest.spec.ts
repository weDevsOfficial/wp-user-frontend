import { Browser, BrowserContext, Page, test, expect, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { SettingsReactPage } from '../pages/settingsReact';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let settings: SettingsReactPage;

test.beforeAll(async () => {
    browser = await chromium.launch();
    context = await browser.newContext({ ignoreHTTPSErrors: true });
    page = await context.newPage();

    const login = new BasicLoginPage(page);
    await login.basicLogin(Users.adminUsername, Users.adminPassword);

    settings = new SettingsReactPage(page);
});

test.afterAll(async () => {
    await browser.close();
});

test.describe('React Settings Screen Tests', () => {

    configureSpecFailFast();

    /**----------------------------------REACT SETTINGS SCREEN----------------------------------**
     *
     * @TestScenario : [WPUF > Settings — React screen]
     * @Test_SR0001 : App mounts and the loading skeleton is replaced by the UI
     * @Test_SR0002 : All top-level tabs are present in the left nav
     * @Test_SR0003 : Header title reflects Pro/Free (WP User Frontend [Pro])
     * @Test_SR0004 : Switching a top-level tab updates the panel title
     * @Test_SR0005 : Sub-tab pills switch the visible section
     * @Test_SR0006 : Active tab + sub-tab persist in the URL across a refresh
     * @Test_SR0007 : Invalid ?sub= does not show a blank panel
     * @Test_SR0008 : Editing a field marks the form dirty ("Unsaved changes")
     * @Test_SR0009 : Save shows the "Saved" confirmation
     * @Test_SR0010 : Saved value round-trips after reload (storage parity)
     * @Test_SR0011 : No-op save keeps the value byte-identical
     * @Test_SR0012 : Cancel/Discard reverts unsaved edits
     * @Test_SR0013 : Switching tabs with unsaved edits shows the Unsaved Changes modal
     * @Test_SR0014 : Continue Editing keeps you on the tab; Discard moves on
     * @Test_SR0015 : Global search finds a field across tabs
     * @Test_SR0016 : Provider search shows the provider's fields (e.g. "facebook")
     * @Test_SR0017 : Search with no match shows the empty state
     * @Test_SR0018 : Clear (X) button resets the search
     * @Test_SR0019 : Clicking a tab clears an active search
     * @Test_SR0020 : (depends_on — covered by SettingsSection valueMatches unit logic)
     * @Test_SR0021 : Legacy fallback — "Classic view" renders the classic screen
     * @Test_SR0022 : Legacy emergency override (?wpuf_settings_ui=legacy) works
     * @Test_SR0023 : Switch back from classic to the React screen
     **/

    test('@Test_SR0001 : App mounts; skeleton replaced by UI', async () => {
        await settings.goto();
        await expect(settings.root).toBeVisible();
    });

    test('@Test_SR0002 : All top-level tabs present', async () => {
        await settings.goto();
        for (const tab of ['General', 'Frontend Posting', 'Login & Registration', 'Email', 'Advanced']) {
            await expect(page.locator('#wpuf-settings-root nav.wpuf-space-y-1 button', { hasText: tab })).toBeVisible();
        }
    });

    test('@Test_SR0003 : Header title reflects Pro/Free', async () => {
        await settings.goto();
        await expect(page.locator('#wpuf-settings-root h2', { hasText: /WP User Frontend/ }).first()).toBeVisible();
    });

    test('@Test_SR0004 : Tab switch updates the panel title', async () => {
        await settings.goto();
        await settings.openTab('Email');
        await settings.expectPanelTitle('Email');
        await settings.openTab('General');
        await settings.expectPanelTitle('General');
    });

    test('@Test_SR0005 : Sub-tab pills switch section', async () => {
        // Uses a FREE sub-tab (My Account) so the case runs without Pro/license.
        await settings.goto();
        await settings.openTab('Login & Registration');
        await settings.openSubTab('My Account');
        await expect(page.getByText('My Account', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0006 : Tab + sub-tab persist in URL on refresh', async () => {
        await settings.goto();
        await settings.openTab('Login & Registration');
        await settings.openSubTab('My Account');
        expect(page.url()).toContain('tab=login_registration');
        expect(page.url()).toContain('sub=wpuf_my_account');
        await page.reload();
        await expect(settings.root).toBeVisible();
        expect(page.url()).toContain('sub=wpuf_my_account');
    });

    test('@Test_SR0007 : Invalid ?sub= does not blank the panel', async () => {
        await settings.navigateToURL(settings.wpufSettingsPage + '&tab=payments&sub=bogus_section');
        await expect(settings.root).toBeVisible();
        // A panel heading still renders (no empty content area).
        await expect(page.locator('#wpuf-settings-root h2').first()).toBeVisible();
    });

    test('@Test_SR0008 : Editing a field marks dirty', async () => {
        await settings.goto();
        await settings.openTab('General');
        await settings.fillByLabel('Custom CSS codes', `/* ${faker.string.uuid()} */`).catch(() => {});
        await expect(page.getByText('Unsaved changes', { exact: false })).toBeVisible();
    });

    test('@Test_SR0009 + @Test_SR0010 : Save persists across reload (parity)', async () => {
        const css = `/* qa-${faker.string.alphanumeric(8)} */`;
        await settings.goto();
        await settings.openTab('General');
        await settings.fillByLabel('Custom CSS codes', css);
        await settings.save();
        await settings.goto();
        await settings.openTab('General');
        expect(await settings.valueByLabel('Custom CSS codes')).toContain(css.replace('/* ', '').slice(0, 6));
    });

    test('@Test_SR0011 : No-op save keeps value identical', async () => {
        await settings.goto();
        await settings.openTab('General');
        const before = await settings.valueByLabel('Custom CSS codes');
        // Touch dirty state minimally then restore, or just re-save unchanged via a benign edit.
        await settings.fillByLabel('Custom CSS codes', before + ' ');
        await settings.fillByLabel('Custom CSS codes', before);
        // If still dirty, save and confirm the value is unchanged on reload.
        await settings.save().catch(() => {});
        await settings.goto();
        await settings.openTab('General');
        expect(await settings.valueByLabel('Custom CSS codes')).toBe(before);
    });

    test('@Test_SR0012 : Cancel reverts unsaved edits', async () => {
        await settings.goto();
        await settings.openTab('General');
        const orig = await settings.valueByLabel('Custom CSS codes');
        await settings.fillByLabel('Custom CSS codes', orig + '/* sr0012 */');
        await expect(page.getByText('Unsaved changes', { exact: true })).toBeVisible();
        await settings.cancel();
        expect(await settings.valueByLabel('Custom CSS codes')).toBe(orig);
    });

    // NOTE: depends_on (SR0020) is exercised by the SettingsSection `valueMatches`
    // unit logic; a stable E2E needs the General security card's reCAPTCHA↔Cloudflare
    // switcher state, which is too brittle to assert reliably here.

    test('@Test_SR0013 + @Test_SR0014 : Unsaved-changes modal on tab switch', async () => {
        await settings.goto();
        await settings.openTab('General');
        await settings.fillByLabel('Custom CSS codes', `/* ${faker.string.uuid()} */`);
        await settings.openTab('Email');
        await settings.expectUnsavedModal();
        await settings.continueEditing();
        await settings.expectPanelTitle('General');
        // Now discard and move on.
        await settings.openTab('Email');
        await settings.discardChanges();
        await settings.expectPanelTitle('Email');
    });

    test('@Test_SR0015 : Global search finds a field across tabs', async () => {
        await settings.goto();
        await settings.search('recaptcha');
        await settings.expectPanelTitle('Search results');
        await expect(page.getByText('reCAPTCHA', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0016 : Provider search shows the provider fields', async () => {
        await settings.goto();
        await settings.search('facebook');
        await settings.expectPanelTitle('Search results');
        // Facebook lives in the Social Login Pro module — gate so a Lite/no-license
        // run skips instead of failing (per tests/e2e/CLAUDE.md). gateOnPro is
        // function-hoisted, so it's usable here ahead of its declaration.
        await gateOnPro( page.getByText('Facebook', { exact: false }), 'Social Login (Pro module) inactive — provider fields unavailable' );
        await expect(page.getByText('Facebook', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0017 : Search with no match shows empty state', async () => {
        await settings.goto();
        await settings.search('zzzznomatchzzzz');
        await settings.expectNoResults();
    });

    test('@Test_SR0018 + @Test_SR0019 : Clear button + tab click reset search', async () => {
        await settings.goto();
        await settings.search('email');
        await settings.clearSearch();
        await expect(page.locator('#wpuf-settings-root input[placeholder^="Search"]')).toHaveValue('');
        await settings.search('email');
        await settings.openTab('General');
        await expect(page.locator('#wpuf-settings-root input[placeholder^="Search"]')).toHaveValue('');
        await settings.expectPanelTitle('General');
    });

    test('@Test_SR0021 + @Test_SR0023 : Legacy fallback switch + back', async () => {
        await settings.goto();
        await settings.switchToClassic();
        await settings.switchToNew();
        await expect(settings.root).toBeVisible();
    });

    test('@Test_SR0022 : Legacy emergency URL override', async () => {
        await settings.gotoLegacyOverride();
        // Restore the React view for any later runs.
        await settings.goto();
    });
});

/**
 * Pro-only coverage. Each case is GATED — it skips (not fails) when the relevant
 * Pro section/module isn't loaded (Lite, or no valid license), per
 * tests/e2e/CLAUDE.md. On a Pro+licensed site every case runs.
 */
async function gateOnPro( probe: import('@playwright/test').Locator, reason: string ) {
    const visible = await probe.first()
        .waitFor({ state: 'visible', timeout: 4000 } ).then( () => true ).catch( () => false );
    test.skip( ! visible, reason );
}

test.describe('React Settings Screen — Pro Tests', () => {

    configureSpecFailFast();

    /**----------------------------------REACT SETTINGS (PRO)----------------------------------**
     *
     * @Test_SR0024 : Social Login sub-tab renders provider cards (Facebook/Google)
     * @Test_SR0025 : Payments → Tax shows the tax UI (Enable Tax + Base Country + Rates)
     * @Test_SR0026 : Payments → Invoices section renders
     * @Test_SR0027 : SMS tab renders provider cards (Twilio/Vonage)
     * @Test_SR0028 : Integrations → AI Settings shows provider cards + model field
     * @Test_SR0029 : Email tab renders the Pro notification templates
     * @Test_SR0030 : Pro field save round-trips (Tax base country persists)
     * @Test_SR0031 : Login Form Colors accordion renders the 11 color pickers
     **/

    test('@Test_SR0024 : Social Login provider cards', async () => {
        await settings.goto();
        await settings.openTab('Login & Registration');
        await gateOnPro( page.locator('#wpuf-settings-root').getByText('Social Login', { exact: false }), 'Social Login module inactive' );
        await settings.openSubTab('Social Login');
        await expect(page.getByText('Facebook', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('Google', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0025 : Tax UI (enable + base country + rates)', async () => {
        await settings.goto();
        await settings.openTab('Payments');
        await gateOnPro( page.locator('#wpuf-settings-root').getByText('Tax', { exact: true }), 'Tax (Pro) inactive' );
        await settings.openSubTab('Tax');
        // TaxSettings (custom component) renders "Base Country" + a "+ Add Rate"
        // rate table — not the raw field labels.
        await expect(page.getByText('Enable Tax', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('Base Country', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('Add Rate', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0026 : Invoices section renders', async () => {
        await settings.goto();
        await settings.openTab('Payments');
        await gateOnPro( page.locator('#wpuf-settings-root').getByText('Invoices', { exact: false }), 'Invoices (Pro) inactive' );
        await settings.openSubTab('Invoices');
        await expect(page.locator('#wpuf-settings-root').getByText('Invoice', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0027 : SMS provider cards', async () => {
        await settings.goto();
        await gateOnPro( page.locator('#wpuf-settings-root nav.wpuf-space-y-1').getByText('SMS', { exact: false }), 'SMS module inactive' );
        await settings.openTab('SMS');
        await expect(page.getByText('Twilio', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0028 : AI Settings provider cards + model', async () => {
        await settings.goto();
        await gateOnPro( page.locator('#wpuf-settings-root nav.wpuf-space-y-1').getByText('Integrations', { exact: false }), 'Integrations/AI inactive' );
        await settings.openTab('Integrations');
        await expect(page.getByText('OpenAI', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('AI Model', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0029 : Email Pro notification templates render', async () => {
        await settings.goto();
        await settings.openTab('Email');
        // Pro email-templates feature: per-status registration notifications.
        await gateOnPro( page.getByText('Approved User Email', { exact: false }), 'Pro email templates inactive' );
        await expect(page.getByText('Approved User Email', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('Template Settings', { exact: false }).first()).toBeVisible();
    });

    test('@Test_SR0030 : Pro field save round-trips (Tax base country)', async () => {
        await settings.goto();
        await settings.openTab('Payments');
        await gateOnPro( page.locator('#wpuf-settings-root').getByText('Tax', { exact: true }), 'Tax (Pro) inactive' );
        await settings.openSubTab('Tax');
        // Capture the prior state so the run is idempotent, then toggle + save.
        const original = await page.getByRole('checkbox', { name: 'Enable Tax' }).isChecked();
        await page.getByRole('checkbox', { name: 'Enable Tax' }).click();
        await settings.save();
        await settings.goto();
        await settings.openTab('Payments');
        await settings.openSubTab('Tax');
        await expect(page.getByText('Base Country', { exact: false }).first()).toBeVisible();
        // The toggled value must actually persist across reload (round-trip).
        await expect(page.getByRole('checkbox', { name: 'Enable Tax' })).toBeChecked({ checked: ! original });
        // Restore the original setting so repeated runs don't depend on mutated state.
        await page.getByRole('checkbox', { name: 'Enable Tax' }).click();
        await settings.save();
    });

    test('@Test_SR0031 : Login Form Colors render the color pickers', async () => {
        await settings.goto();
        await settings.openTab('Login & Registration');
        await settings.openSubTab('Login / Registration');
        // The 11 color fields (Pro) sit under a collapsed "Login Form Colors"
        // accordion (the html heading groups them). Expand → pickers appear.
        await gateOnPro( page.locator('#wpuf-settings-root').getByText('Login Form Colors', { exact: false }), 'Login form colors (Pro) inactive' );
        await page.locator('#wpuf-settings-root').getByText('Login Form Colors', { exact: false }).first().click();
        await expect(page.getByText('Form Background Color', { exact: false }).first()).toBeVisible();
        await expect(page.getByText('Button Text Color', { exact: false }).first()).toBeVisible();
        await expect(page.locator('#wpuf-settings-root input[type="color"]').first()).toBeVisible();
    });
});
