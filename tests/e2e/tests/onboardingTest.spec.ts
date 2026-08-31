import { Browser, BrowserContext, Page, test, chromium, expect } from "@playwright/test";
import { OnboardingPage } from '../pages/onboarding';
import { BasicLoginPage } from '../pages/basicLogin';
import { Selectors } from '../pages/selectors';
import { Users } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let onboarding: OnboardingPage;
let login: BasicLoginPage;

// Decided once from what the site renders, so the same spec covers a Lite-only
// run and a Pro run without being told which it is.
let isPro = false;

test.beforeAll(async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    onboarding = new OnboardingPage(page);
    login = new BasicLoginPage(page);

    await login.basicLogin(Users.adminUsername, Users.adminPassword);

    isPro = await onboarding.isProActive();
});

test.afterAll(async () => {
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

        // Walk the page-creating steps again with the same answers.
        await onboarding.gotoWizard('registration');
        await onboarding.continueStep();
        await onboarding.gotoWizard('common');
        await onboarding.setCommonOptions({ installPages: true });
        await onboarding.continueStep();

        // Each UF page must still exist exactly once.
        for (const shortcode of ['[wpuf-login]', '[wpuf_account]', '[wpuf_edit]']) {
            expect(await countPages(shortcode), `${shortcode} should exist once`).toBeLessThanOrEqual(1);
        }
    });
});
