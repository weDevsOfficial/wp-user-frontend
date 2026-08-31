import { Browser, BrowserContext, Page, test, chromium, expect } from "@playwright/test";
import { OnboardingPage } from '../pages/onboarding';
import { BasicLoginPage } from '../pages/basicLogin';
import { Selectors } from '../pages/selectors';
import { Users } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';
import { wpCli } from '../utils/wpEnvCli';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let onboarding: OnboardingPage;
let login: BasicLoginPage;

// Decided once from what the site renders, so the same spec covers a Lite-only
// run and a Pro run without being told which it is.
let isPro = false;

// This spec drives a wizard whose whole job is rewriting site-wide settings, and
// the specs that run after it in the same suite read those settings. Every option
// it touches is captured whole and put back afterwards, so the next spec inherits
// the site it expected rather than one that has been through onboarding.
//
// Whole options rather than individual keys on purpose: `option patch get` returns
// nothing for these sections, so a per-key snapshot reads as null and the restore
// would delete the very values it was meant to preserve.
const TOUCHED_OPTIONS = [
    'wpuf_frontend_posting',
    'wpuf_profile',
    'wpuf_dashboard',
    'wpuf_payment',
    'wpuf_my_account',
    'wpuf_general',
    'wpuf_onboarding_features',
    'wpuf_onboarding_progress',
    'wpuf_onboarding_completed',
    'wpuf_onboarding_plugin_errors',
];

const originalOptions: Record<string, string | null> = {};

function snapshotSiteState() {
    for (const option of TOUCHED_OPTIONS) {
        try {
            const value = wpCli(`option get ${option} --format=json`).trim();
            originalOptions[option] = value === '' ? null : value;
        } catch {
            // Not set on this site; the restore should leave it unset.
            originalOptions[option] = null;
        }
    }
}

/**
 * Quote a value for a single-quoted shell argument.
 *
 * These option payloads contain apostrophes (one of the stock payment strings
 * reads "you don't have a PayPal account"), which close the quote early and
 * corrupt the command, so the restore silently does nothing.
 */
function shellQuote(value: string): string {
    return `'${value.split("'").join(`'\\''`)}'`;
}

function restoreSiteState() {
    for (const option of TOUCHED_OPTIONS) {
        const before = originalOptions[option];

        try {
            if (before === null) {
                wpCli(`option delete ${option}`);
            } else {
                wpCli(`option update ${option} ${shellQuote(before)} --format=json`);
            }
        } catch {
            // Deleting an option that was never there is already the wanted state.
        }
    }
}

test.beforeAll(async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    onboarding = new OnboardingPage(page);
    login = new BasicLoginPage(page);

    snapshotSiteState();

    await login.basicLogin(Users.adminUsername, Users.adminPassword);

    isPro = await onboarding.isProActive();
});

test.afterAll(async () => {
    // Put the site back before the next spec starts.
    restoreSiteState();

    await context?.close();
    await browser?.close();
});

test.describe('Onboarding Wizard Tests', () => {

    configureSpecFailFast();

    /**----------------------------------ONBOARDING WIZARD----------------------------------**
     *
     * @TestScenario : [Guided onboarding wizard, free and Pro]
     * @Test_ONB0001 : Admin is opening the wizard from User Frontend > Tools
     * @Test_ONB0002 : Admin is validating the step rail uses check icons, never digits
     * @Test_ONB0003 : Admin is validating every feature is offered and selectable
     * @Test_ONB0004 : Admin is selecting every feature and continuing
     * @Test_ONB0005 : Admin is validating the post form step and its author permissions
     * @Test_ONB0006 : Admin is validating the registration step offers pages to pick
     * @Test_ONB0007 : Admin is turning on "After they sign up" (auto login)
     * @Test_ONB0008 : Admin is validating auto login reached the settings screen
     * @Test_ONB0009 : Admin is validating author edit and delete reached the settings screen
     * @Test_ONB0010 : Admin is validating the login layout picker is locked without Pro
     * @Test_ONB0011 : Admin is validating gateway cards carry logos
     * @Test_ONB0012 : Admin is validating the PRO badge appears only when Pro is inactive
     * @Test_ONB0013 : Admin is validating the PRO badge keeps its aspect ratio
     * @Test_ONB0014 : Admin is validating gateway cards are centred
     * @Test_ONB0015 : Admin is completing the settings step
     * @Test_ONB0016 : Admin is validating payments reached the settings screen
     * @Test_ONB0017 : Admin is validating every WPUF menu is present with all features on
     * @Test_ONB0018 : Admin is re-running onboarding with payments and directory off
     * @Test_ONB0019 : Admin is validating the payment menus are hidden
     * @Test_ONB0020 : Admin is validating the user directory menu is hidden
     * @Test_ONB0021 : Admin is validating the core menus survive
     * @Test_ONB0022 : Admin is validating unticking payments switched the payment setting off
     * @Test_ONB0023 : Admin is validating unrelated settings were left alone
     * @Test_ONB0024 : Admin is validating the re-run warning is shown once completed
     * @Test_ONB0025 : Admin is validating a second run creates no duplicate pages
     * @Test_ONB0026 : Admin is validating the wizard logo is a vector that loads
     * @Test_ONB0027 : Admin is validating every companion plugin logo loads
     * @Test_ONB0028 : Admin is validating no wizard screen ships a raster image
     * @Test_ONB0029 : Admin is validating plugin icons render at icon size
     * @Test_ONB0030 : Admin is validating required fields are marked accessibly
     * @Test_ONB0031 : Admin is validating behaviour comes from an enqueued script
     * @Test_ONB0032 : Admin is validating the wizard fits common desktop widths
     * @Test_ONB0033 : Admin is validating a card is always offered for every gateway
     * @Test_ONB0034 : Admin is validating each gateway says whether it is ready to use
     * @Test_ONB0035 : Admin is validating an unavailable gateway is inert, not a link
     *
     ***-----------------------------------------------------------------------------------**/

    test('ONB0001 : Admin is opening the wizard from User Frontend > Tools', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoTools();

        await expect(page.locator(Selectors.onboarding.entry.onboardingBox)).toBeVisible();

        await onboarding.startFromTools();

        expect(await onboarding.wizardIsOpen()).toBeTruthy();
    });

    test('ONB0002 : Admin is validating the step rail uses check icons, never digits', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('features');

        // Matches the User Directory wizard, whose markers are always the check glyph.
        const rail = await onboarding.getMarkerReport();

        expect(rail.markers, 'the rail should render markers').toBeGreaterThan(0);
        expect(rail.icons, `every marker should draw a tick (markers=${rail.markers}, icons=${rail.icons})`).toBe(rail.markers);
        expect(rail.digits, `no marker should print a digit, found: ${rail.digitText.join(', ')}`).toBe(0);
    });

    test('ONB0003 : Admin is validating every feature is offered and selectable', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('features');

        const states = await onboarding.getFeatureStates();

        // All four are always on offer, whatever the site has been through.
        expect(Object.keys(states)).toEqual(
            expect.arrayContaining(['post_form', 'registration', 'user_directory', 'payments'])
        );

        // Every one of them has to be switchable in both directions. "All ticked by
        // default" is not asserted here: it holds only until the picker is first
        // saved, and this suite saves it further down, so the claim would pass or
        // fail on the order the suite happens to run in rather than on the code.
        await onboarding.setFeatures([]);
        expect(Object.values(await onboarding.getFeatureStates()).some(Boolean)).toBeFalsy();

        await onboarding.setFeatures(['post_form', 'registration', 'user_directory', 'payments']);
        expect(Object.values(await onboarding.getFeatureStates()).every(Boolean)).toBeTruthy();
    });

    test('ONB0004 : Admin is selecting every feature and continuing', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('features');
        await onboarding.setFeatures(['post_form', 'registration', 'user_directory', 'payments']);
        await onboarding.continueStep();

        expect(await onboarding.wizardIsOpen()).toBeTruthy();
    });

    test('ONB0005 : Admin is validating the post form step and its author permissions', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('post_form');

        await expect(page.locator(Selectors.onboarding.postForm.templateSelect)).toBeVisible();

        await onboarding.setPostFormOptions(true, true);
        await onboarding.continueStep();
    });

    test('ONB0006 : Admin is validating the registration step offers pages to pick', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('registration');

        // First install creates the pages these pickers need, so a fresh site must
        // offer more than just "create a new page".
        const loginOptions = await onboarding.getPageSelectOptionCount(Selectors.onboarding.registration.loginPageSelect);
        expect(loginOptions).toBeGreaterThan(1);

        // Free ships [wpuf-registration] and Pro ships [wpuf_profile]; either way the
        // step offers a registration page rather than only an upsell.
        const regOptions = await onboarding.getPageSelectOptionCount(Selectors.onboarding.registration.regPageSelect);
        expect(regOptions).toBeGreaterThan(0);
    });

    test('ONB0007 : Admin is turning on "After they sign up" (auto login)', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('registration');

        expect(await onboarding.setCheckboxIfPresent(Selectors.onboarding.registration.autologinCheckbox, true)).toBeTruthy();

        await onboarding.continueStep();
    });

    test('ONB0008 : Admin is validating auto login reached the settings screen', { tag: ['@Basic'] }, async () => {
        // The wizard's promise is that a choice made here shows up where the admin
        // would go looking for it afterwards.
        expect(await onboarding.settingsCheckboxIsOn(Selectors.onboardingSettingsCheck.autologin)).toBeTruthy();
    });

    test('ONB0009 : Admin is validating author edit and delete reached the settings screen', { tag: ['@Basic'] }, async () => {
        // Stored as yes/no selects rather than checkboxes.
        expect(await onboarding.settingsSelectValue(Selectors.onboardingSettingsCheck.enablePostEdit)).toBe('yes');
        expect(await onboarding.settingsSelectValue(Selectors.onboardingSettingsCheck.enablePostDelete)).toBe('yes');
    });

    test('ONB0010 : Admin is validating the login layout picker is locked without Pro', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('registration');

        const layouts = await onboarding.getLayoutPickerState();

        test.skip(layouts.total === 0, 'Login layout picker is not rendered on this build.');

        if (isPro) {
            // With Pro the layouts are a real choice.
            expect(layouts.disabled, 'Pro should leave the layouts selectable').toBe(0);

            return;
        }

        // Without Pro every layout is locked, and the basic one is what shows, even if
        // a Pro-only layout was stored while Pro was active.
        expect(layouts.disabled, 'every layout should be locked without Pro').toBe(layouts.total);
        expect(layouts.checked, 'the basic layout should stay selected').toBe('layout1');
        expect(layouts.previewLabel.length, 'the preview should name a layout').toBeGreaterThan(0);
    });

    test('ONB0011 : Admin is validating gateway cards carry logos', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');

        const cards = await onboarding.getGatewayCards();

        expect(cards.length).toBeGreaterThan(0);

        // Built from the same source the settings screen uses, so every card has art.
        for (const card of cards) {
            expect(card.hasIcon, `gateway "${card.label}" should render a logo`).toBeTruthy();
        }
    });

    test('ONB0012 : Admin is validating the PRO badge appears only when Pro is inactive', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');

        const cards = await onboarding.getGatewayCards();
        const proCards = cards.filter(card => card.isPro);

        if (isPro) {
            expect(proCards.length, 'no gateway should be badged while Pro is active').toBe(0);

            return;
        }

        expect(proCards.length, 'a Pro-only gateway should be badged while Pro is inactive').toBeGreaterThan(0);
    });

    test('ONB0013 : Admin is validating the PRO badge keeps its aspect ratio', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');

        const hasBadge = await page.locator(Selectors.onboarding.common.gatewayProBadge).count() > 0;

        test.skip(!hasBadge, 'No PRO badge on this build, so there is nothing to measure.');

        // The asset is a 39x22 pill; drawing it square squashes it.
        expect(await onboarding.proBadgeKeepsAspectRatio()).toBeTruthy();
    });

    test('ONB0014 : Admin is validating gateway cards are centred', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');

        expect(await onboarding.gatewayCardsAreCentred()).toBeTruthy();
    });

    test('ONB0015 : Admin is completing the settings step', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');
        await onboarding.setCommonOptions({
            installPages: true,
            hideAdminBar: true,
            addLogoutMenu: true,
            enablePayments: true,
        });
        await onboarding.continueStep();
    });

    test('ONB0016 : Admin is validating payments reached the settings screen', { tag: ['@Basic'] }, async () => {
        expect(await onboarding.settingsCheckboxIsOn(Selectors.onboardingSettingsCheck.enablePayment)).toBeTruthy();
    });

    test('ONB0017 : Admin is validating every WPUF menu is present with all features on', { tag: ['@Basic'] }, async () => {
        const labels = await onboarding.getWpufSubmenuLabels();

        expect(labels).toEqual(expect.arrayContaining(['Post Forms', 'Tools', 'Settings']));
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.subscriptionsMenu)).toBeTruthy();
    });

    test('ONB0018 : Admin is re-running onboarding with payments and directory off', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('features');
        await onboarding.setFeatures(['post_form', 'registration']);
        await onboarding.continueStep();

        const states = await (async () => {
            await onboarding.gotoWizard('features');

            return onboarding.getFeatureStates();
        })();

        expect(states.payments).toBeFalsy();
        expect(states.user_directory).toBeFalsy();
    });

    test('ONB0019 : Admin is validating the payment menus are hidden', { tag: ['@Basic'] }, async () => {
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.subscriptionsMenu)).toBeFalsy();
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.transactionsMenu)).toBeFalsy();
    });

    test('ONB0020 : Admin is validating the user directory menu is hidden', { tag: ['@Basic'] }, async () => {
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.userDirectoryMenu)).toBeFalsy();
    });

    test('ONB0021 : Admin is validating the core menus survive', { tag: ['@Basic'] }, async () => {
        // Switching a feature off must never take the plugin's own screens with it.
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.postFormsMenu)).toBeTruthy();
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.settingsMenu)).toBeTruthy();
        expect(await onboarding.menuIsVisible(Selectors.onboardingMenus.toolsMenu)).toBeTruthy();
    });

    test('ONB0022 : Admin is validating unticking payments switched the payment setting off', { tag: ['@Basic'] }, async () => {
        // Hiding the menus is not enough: the setting the rest of the plugin reads
        // has to follow, or the gateways keep working while the admin believes they
        // switched the whole thing off.
        expect(await onboarding.settingsCheckboxIsOn(Selectors.onboardingSettingsCheck.enablePayment)).toBeFalsy();
    });

    test('ONB0023 : Admin is validating unrelated settings were left alone', { tag: ['@Basic'] }, async () => {
        // Switching payments off touches payments only.
        expect(await onboarding.settingsCheckboxIsOn(Selectors.onboardingSettingsCheck.autologin)).toBeTruthy();
        expect(await onboarding.settingsSelectValue(Selectors.onboardingSettingsCheck.enablePostEdit)).toBe('yes');
    });

    test('ONB0024 : Admin is validating the re-run warning is shown once completed', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('ready');

        const label = await onboarding.getEntryButtonLabel();

        // A finished run offers to start over, and says what that costs.
        expect(label).toContain('Onboarding');

        if (label.includes('Again')) {
            expect(await onboarding.rerunWarningIsVisible()).toBeTruthy();
        }
    });

    test('ONB0025 : Admin is validating a second run creates no duplicate pages', { tag: ['@Basic'] }, async () => {
        const countPages = async (shortcode: string) => {
            await onboarding.navigateToURL(`${onboarding.adminHomeUrl.replace('/index.php', '')}/edit.php?post_type=page&s=${encodeURIComponent(shortcode)}`);

            return await page.locator('#the-list tr.iedit').count();
        };

        const shortcodes = ['[wpuf-login]', '[wpuf_account]', '[wpuf_edit]'];
        const before: Record<string, number> = {};

        for (const shortcode of shortcodes) {
            before[shortcode] = await countPages(shortcode);
        }

        // Walk the page-creating steps again with the same answers.
        await onboarding.gotoWizard('registration');
        await onboarding.continueStep();
        await onboarding.gotoWizard('common');
        await onboarding.setCommonOptions({ installPages: true });
        await onboarding.continueStep();

        // The claim is that a second run adds nothing, so this compares against what
        // the site had a moment ago rather than asserting one page per shortcode.
        // Other specs, and the setup project, seed pages carrying these same
        // shortcodes, so an absolute count says nothing about the wizard.
        for (const shortcode of shortcodes) {
            const after = await countPages(shortcode);

            expect(after, `${shortcode} count should not grow on a second run`).toBeLessThanOrEqual(before[shortcode]);
        }
    });

    test('ONB0026 : Admin is validating the wizard logo is a vector that loads', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('features');

        const logo = page.locator('.wpuf-onboarding-topbar img');

        await expect(logo).toBeVisible();
        await expect(logo).toHaveAttribute('src', /onboarding-logo\.svg$/);

        // A missing or broken file still renders an <img>, so the decoded width is
        // what actually proves it arrived.
        const loaded = await logo.evaluate((img: HTMLImageElement) => img.complete && img.naturalWidth > 0);
        expect(loaded, 'the header logo should decode').toBeTruthy();
    });

    test('ONB0027 : Admin is validating every companion plugin logo loads', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('plugins');

        const logos = await onboarding.getPluginLogos();

        test.skip(logos.length === 0, 'Every companion plugin is already active, so the step is not shown.');

        for (const logo of logos) {
            expect(logo.file, `${logo.name} should use a vector logo`).toMatch(/\.svg$/);
            expect(logo.loaded, `${logo.name} logo (${logo.file}) should decode`).toBeTruthy();
        }
    });

    test('ONB0028 : Admin is validating no wizard screen ships a raster image', { tag: ['@Basic'] }, async () => {
        // Walked per step, because each renders its own artwork.
        for (const step of ['features', 'post_form', 'registration', 'common', 'plugins', 'ready']) {
            await onboarding.gotoWizard(step);

            const images = await onboarding.getImageReport();

            for (const image of images) {
                expect(image.isSvg, `${step} step still ships a raster image: ${image.file}`).toBeTruthy();
                expect(image.loaded, `${step} step image ${image.file} should decode`).toBeTruthy();
            }
        }
    });

    test('ONB0029 : Admin is validating plugin icons render at icon size', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('plugins');

        const logo = page.locator('.wpuf-onboarding-logo').first();

        test.skip(await logo.count() === 0, 'Every companion plugin is already active, so the step is not shown.');

        // The art is a 96px tile; drawing it much smaller shrinks the tile to a speck.
        const box = await logo.boundingBox();
        expect(box!.width, 'plugin icon should read as an icon, not a dot').toBeGreaterThanOrEqual(40);
    });

    test('ONB0030 : Admin is validating required fields are marked accessibly', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('registration');

        const marks = await onboarding.getRequiredMarkers();

        expect(marks.marks, 'required labels should carry a marker').toBeGreaterThan(0);
        expect(marks.requiredControls, 'marked labels need a required control').toBeGreaterThan(0);

        // Colour alone must not carry the meaning (WCAG 1.4.1), so each marker is
        // paired with a word only assistive tech reads.
        expect(marks.srWords, 'every marker needs its screen-reader word').toBe(marks.marks);
    });

    test('ONB0031 : Admin is validating behaviour comes from an enqueued script', { tag: ['@Basic'] }, async () => {
        await onboarding.gotoWizard('common');

        const scripts = await onboarding.getScriptReport();

        expect(scripts.externalFiles, 'the wizard script should be enqueued').toContain('wpuf-onboarding.js');
        expect(scripts.inlineBehaviourBlocks, 'no step should carry inline behaviour').toBe(0);

        // Proves the enqueued file is actually wired up, not merely present: this
        // show/hide is one of the behaviours that moved into it.
        const gateway = await page.evaluate(() => {
            const toggle = document.getElementById('wpuf-onboarding-enable-payment') as HTMLInputElement | null;
            const field = document.getElementById('wpuf-onboarding-gateway-field') as HTMLElement | null;

            if (!toggle || !field) {
                return null;
            }

            toggle.checked = false;
            toggle.dispatchEvent(new Event('change'));
            const hidden = field.style.display === 'none';

            toggle.checked = true;
            toggle.dispatchEvent(new Event('change'));

            return { hidden, shownAgain: field.style.display !== 'none' };
        });

        // The gateway picker only exists while payments are switched on. When it is
        // not rendered the checks above already prove the file is enqueued and that
        // nothing is inline, so there is no reason to skip the whole scenario.
        if (gateway !== null) {
            expect(gateway.hidden).toBeTruthy();
            expect(gateway.shownAgain).toBeTruthy();
        }
    });

    test('ONB0032 : Admin is validating the wizard fits common desktop widths', { tag: ['@Basic'] }, async () => {
        const widths = [1024, 1280, 1440, 1920];

        for (const width of widths) {
            await page.setViewportSize({ width, height: 900 });

            for (const step of ['features', 'registration', 'common', 'plugins']) {
                await onboarding.gotoWizard(step);

                const health = await onboarding.getLayoutHealth();

                expect(health.overflow, `${step} step scrolls sideways at ${width}px`).toBeLessThanOrEqual(0);
                expect(health.labelOverlaps, `step labels collide at ${width}px`).toBe(0);
            }
        }

        await page.setViewportSize({ width: 1280, height: 900 });
    });

    test('ONB0033 : Admin is validating a card is always offered for every gateway', { tag: ['@Basic'] }, async () => {
        // An earlier scenario switches payments off, so this one puts them back
        // rather than skipping on a state it can set for itself.
        await onboarding.enablePaymentsFeature();
        await onboarding.gotoWizard('common');

        const cards = await onboarding.getGatewayCards();

        expect(cards.length, 'the gateway picker should render with payments on').toBeGreaterThan(0);

        const labels = cards.map(card => card.label);

        // Bank and PayPal ship with the free plugin.
        expect(labels.some(label => /bank/i.test(label)), 'bank transfer should be offered').toBeTruthy();
        expect(labels.some(label => /paypal/i.test(label)), 'PayPal should be offered').toBeTruthy();

        // The card-style gateway must never simply vanish. Without Pro it is a
        // badged upsell; with Pro but the module switched off it points at Modules.
        // Dropping it entirely reads as "not supported", which is what this guards.
        expect(labels.some(label => /credit card|stripe/i.test(label)), 'a card gateway should always be offered').toBeTruthy();
    });

    test('ONB0034 : Admin is validating each gateway says whether it is ready to use', { tag: ['@Basic'] }, async () => {
        await onboarding.enablePaymentsFeature();
        await onboarding.gotoWizard('common');

        const cards = await onboarding.getGatewayCards();

        expect(cards.length, 'the gateway picker should render with payments on').toBeGreaterThan(0);

        const bank = cards.find(card => /bank/i.test(card.label));
        const paypal = cards.find(card => /paypal/i.test(card.label));

        // Bank takes money the moment onboarding finishes; PayPal cannot until its
        // keys are entered, and saying so here is the difference between a working
        // checkout and a silently broken one.
        expect(bank!.hint, 'bank should say it works immediately').toMatch(/right away/i);
        expect(paypal!.hint, 'PayPal should say it needs credentials').toMatch(/key|setting/i);

        // And the step as a whole repeats it, linked to where the keys go.
        const notice = page.locator('.wpuf-onboarding-help.is-warning');
        await expect(notice).toBeVisible();
        await expect(notice).toContainText('PayPal');

        // These cards are a smaller decision than the feature cards, so they stay compact.
        for (const card of cards) {
            expect(card.height, `${card.label} card should stay compact`).toBeLessThanOrEqual(130);
        }
    });

    test('ONB0035 : Admin is validating an unavailable gateway is inert, not a link', { tag: ['@Basic'] }, async () => {
        await onboarding.enablePaymentsFeature();
        await onboarding.gotoWizard('common');

        const card = page.locator('.wpuf-onboarding-card.is-pro, .wpuf-onboarding-card.is-unavailable').first();

        test.skip(await card.count() === 0, 'Every gateway is available on this build, so there is no inert card.');

        // It cannot be switched on from here, so it must not offer to be clicked or
        // ticked. It states why instead, the way PayPal states what it still needs.
        const shape = await card.evaluate(node => ({
            tag: node.tagName,
            href: node.getAttribute('href'),
            hasCheckbox: !!node.querySelector('input'),
            hint: (node.querySelector('.wpuf-onboarding-card-hint')?.textContent || '').trim(),
        }));

        expect(shape.tag, 'an unavailable gateway should not be an anchor').not.toBe('A');
        expect(shape.href, 'an unavailable gateway should have no link target').toBeNull();
        expect(shape.hasCheckbox, 'an unavailable gateway should not be selectable').toBeFalsy();
        expect(shape.hint.length, 'an unavailable gateway should say why').toBeGreaterThan(0);

        // And clicking it should leave the admin where they were, mid-wizard.
        const before = page.url();
        await card.click({ force: true });
        await page.waitForTimeout(400);
        expect(page.url(), 'clicking the card should not navigate away').toBe(before);
    });
});
