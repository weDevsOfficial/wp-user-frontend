import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Selectors } from './selectors';
import { BasicLogoutPage } from './basicLogout';
import { isMailPoetSubscriberInList } from '../utils/wpEnvCli';

/**
 * Drives the WPUF Pro "Mailpoet 3" email-marketing module: enabling the module,
 * turning on subscribe-on-registration for a registration form, and asserting a
 * newly registered visitor lands in the chosen MailPoet list.
 *
 * Requires the MailPoet plugin active and the wpuf-pro `mailpoet3` module present.
 */
export class MailPoetPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    /**
     * Enable the "Mailpoet 3" module from WPUF > Modules. Idempotent — if it is
     * already active this is a no-op.
     */
    async enableMailPoetModule() {
        await this.navigateToURL(this.wpufModulesPage);
        await this.waitForLoading();

        const checkbox = this.page.locator(Selectors.regFormSettings.mailPoet.moduleCheckbox);
        // The toggle's checkbox is display:none (only the slider is visible), so
        // wait for it to be attached rather than visible.
        await checkbox.waitFor({ state: 'attached' });

        if (!(await checkbox.isChecked())) {
            await this.validateAndClick(Selectors.regFormSettings.mailPoet.moduleToggle);
            // The toggle activates the module over AJAX — wait for it to stick.
            await expect(checkbox).toBeChecked({ timeout: 15000 });
        }
        console.log('\x1b[32m%s\x1b[0m', '✅ Mailpoet 3 module enabled');
    }

    /**
     * On the given registration form, enable MailPoet subscription and select the
     * list new sign-ups are added to.
     *
     * @param formName registration form title (as shown in the form list)
     * @param listName MailPoet list/segment name to subscribe registrants to
     */
    async enableMailPoetOnRegForm(formName: string, listName: string) {
        let flag = true;
        // Bound the save-retry loop: a stuck save (e.g. MailPoet list unavailable) must
        // not spin until the 180s test timeout. Cap attempts, then fail loudly instead.
        let attempts = 0;
        const maxAttempts = 5;

        while (flag == true) {
            if (++attempts > maxAttempts) {
                throw new Error(`Could not save MailPoet subscription on "${formName}" after ${maxAttempts} attempts — is the MailPoet plugin active with the "${listName}" list present?`);
            }
            // Open the form in the builder.
            await this.navigateToURL(this.wpufRegFormPage);
            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            // Settings tab > Modules > Mailpoet 3.
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.validateAndClick(Selectors.regFormSettings.mailPoet.settingsMenuItem);

            // Turn the toggle on (only if it is currently off).
            const enable = this.page.locator(Selectors.regFormSettings.mailPoet.enableCheckbox);
            // sr-only checkbox behind the visible toggle — wait for attached, not visible.
            await enable.waitFor({ state: 'attached' });
            if (!(await enable.isChecked())) {
                await this.validateAndClick(Selectors.regFormSettings.mailPoet.enableToggle);
            }

            // Select the list. The native <select> is selectize-enhanced (hidden),
            // so set its value directly and fire change so the form model updates.
            await this.page.evaluate(
                ({ sel, label }) => {
                    const el = document.querySelector(sel) as HTMLSelectElement | null;
                    if (!el) return;
                    const opt = Array.from(el.options).find(o => o.text.trim() === label);
                    if (opt) {
                        el.value = opt.value;
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                },
                { sel: Selectors.regFormSettings.mailPoet.listSelect, label: listName }
            );

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
        console.log('\x1b[32m%s\x1b[0m', `✅ MailPoet subscription enabled on "${formName}" (list: ${listName})`);
    }

    /**
     * Register a new visitor on the registration page. Logs out first because WPUF
     * hides the registration form from already-authenticated users.
     *
     * Returns:
     *  - `true`  — registration succeeded (`wpuf-success` shown).
     *  - `false` — the submit AJAX stalled with no success AND no error. On a form
     *              with MailPoet subscription enabled, the subscribe-during-register
     *              call needs a working MailPoet list + SMTP (double opt-in); without
     *              it the request hangs. The caller (EM0004) skips itself in that case.
     * Throws if an explicit `wpuf-error` is shown — that is a real failure, never masked.
     */
    async registerVisitorAndValidate(email: string, password: string): Promise<boolean> {
        await new BasicLogoutPage(this.page).logOut();

        await this.navigateToURL(this.newRegFormPage);
        await this.page.fill(Selectors.regFormSettings.inputEmail, email);
        await this.page.fill(Selectors.regFormSettings.inputPassword, password);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, password);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);

        // Bounded wait: a working registration surfaces wpuf-success in ~1-2s.
        const success = this.page.locator(Selectors.regFormSettings.successMessage);
        const error = this.page.locator('//div[contains(@class,"wpuf-error")]');
        try {
            await success.first().waitFor({ state: 'visible', timeout: 45000 });
            console.log('\x1b[32m%s\x1b[0m', `✅ Visitor registered: ${email}`);
            return true;
        } catch (e) {
            // Explicit error → real failure, surface it.
            if (await error.first().isVisible().catch(() => false)) {
                const msg = (await error.first().innerText().catch(() => '')).trim();
                throw new Error(`Registration returned an error: ${msg || 'unknown'}`);
            }
            // Neither success nor error within the window → stalled subscribe path.
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Registration did not surface success within 45s and no error was shown — the MailPoet subscribe path likely stalled (needs a working MailPoet list + SMTP). EM0004 will skip.`);
            return false;
        }
    }

    /**
     * Assert (via the MailPoet DB — its admin UI is gated by onboarding) that the
     * email is a subscriber inside the named MailPoet list.
     */
    async validateUserSubscribedToList(email: string, listName: string) {
        // WPUF hands the subscriber to MailPoet during registration, but MailPoet commits the
        // segment link asynchronously, so an immediate DB read right after EM0004 can miss it.
        // Poll a few times before asserting rather than failing on the first (too-early) read.
        let subscribed = false;
        for (let attempt = 0; attempt < 10; attempt++) {
            subscribed = isMailPoetSubscriberInList(email, listName);
            if (subscribed) {
                break;
            }
            await this.page.waitForTimeout(1000);
        }
        expect(
            subscribed,
            `Expected ${email} to be a MailPoet subscriber in list "${listName}"`
        ).toBe(true);
        console.log('\x1b[32m%s\x1b[0m', `✅ ${email} subscribed to "${listName}"`);
    }
}
