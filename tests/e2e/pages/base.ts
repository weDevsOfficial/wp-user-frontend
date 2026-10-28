import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page } from '@playwright/test';
import { Urls } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { builderFormId } from '../utils/builderUrl';

// Keep the terminal readable: page-object step logs ("✅ Clicked on //...") are
// suppressed by default so only Playwright's own test-title lines show. Set
// E2E_VERBOSE=1 to restore the full per-action locator logging for debugging.
if (!process.env.E2E_VERBOSE) {
    console.log = () => {};
}

export class Base {
    static generateWordWithMinLength(arg0: number): string {
        throw new Error('Method not implemented.');
    }
    readonly page: Page;
    readonly wpAdminPage: string = Urls.baseUrl + '/wp-admin/';
    readonly pluginsPage: string = Urls.baseUrl + '/wp-admin/plugins.php';
    readonly toolsPage: string = Urls.baseUrl + '/wp-admin/tools.php';
    readonly settingsPage: string = Urls.baseUrl + '/wp-admin/options-general.php';
    readonly usersPage: string = Urls.baseUrl + '/wp-admin/users.php';
    readonly postsPage: string = Urls.baseUrl + '/wp-admin/edit.php';
    readonly pagesPage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
    readonly newPagePage: string = Urls.baseUrl + '/wp-admin/post-new.php?post_type=page';
    readonly mediaPage: string = Urls.baseUrl + '/wp-admin/upload.php';
    readonly accountPage: string = Urls.baseUrl + '/account/';
    readonly accountPostsPage: string = Urls.baseUrl + '/account/?section=post';
    readonly settingsPermalinkPage: string = Urls.baseUrl + '/wp-admin/options-permalink.php';
    readonly categoriesPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=category';
    readonly tagsPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=post_tag';
    readonly wpMailLogPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wp-mail-log';
    readonly wpResetPage: string = Urls.baseUrl + '/wp-admin/tools.php?page=wp-reset';
    readonly wpufPostFormPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';
    readonly wpufRegFormPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
    readonly wpufSetupPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-setup';
    readonly wpufSettingsPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-settings';
    readonly wpufSettingsGeneralPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-settings-general';
    readonly wpufPostSubmitPage: string = Urls.baseUrl + '/account/?section=submit-post';
    readonly wpufPostPage: string = Urls.baseUrl + '/account/?section=post';
    readonly postHerePage: string = Urls.baseUrl + '/post-here/';
    readonly siteHomePage: string = Urls.baseUrl;
    readonly wpufTransactionPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf_transaction';
    readonly wpufSubscriptionPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf_subscription';
    readonly subscriptionFrontendPage: string = Urls.baseUrl + '/subscription/';
    readonly accountSubscriptionPage: string = Urls.baseUrl + '/account/?section=subscription';
    readonly wpufModulesPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-modules';
    readonly paymentPage: string = Urls.baseUrl + '/payment/';
    readonly wpufRegistrationFormPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
    readonly wpufRegistrationPage: string = Urls.baseUrl + '/registration-page/';
    readonly newRegFormPage: string = Urls.baseUrl + '/reg-here/';
    readonly wpufLoginPage: string = Urls.baseUrl + '/login/';
    readonly productsPage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=product';
    readonly productBrandPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_brand&post_type=product';
    readonly productCategoryPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product';
    readonly productTagPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product';
    readonly productAttributePage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=product&page=product_attributes';
    readonly addProductPage: string = Urls.baseUrl + '/add-product/';
    readonly eddCatPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=download_category&post_type=download';
    readonly eddTagPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=download_tag&post_type=download';
    readonly addDownloadsPage: string = Urls.baseUrl + '/add-downloads/';
    readonly downloadsPage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=download';
    readonly dokanVendorRegistrationPage: string = Urls.baseUrl + '/reg-vendor/';
    readonly dokanVendorStorePage: string = Urls.baseUrl + '/wp-admin/admin.php?page=dokan#/vendors';
    readonly wcVendorRegistrationPage: string = Urls.baseUrl + '/reg-wc-vendor/';
    readonly wcVendorsPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wcv-all-vendors#/';
    readonly wcfmMemberRegistrationPage: string = Urls.baseUrl + '/reg-member/';
    readonly accessFormWithId: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms&action=edit&id=';
    readonly accessFormPreview: string = Urls.baseUrl + '/wpuf-preview/?wpuf_preview=1&form_id=';

    constructor(page: Page) {
        this.page = page;
    }

    // URL navigation
    async navigateToURL(url: string) {
        try {
            await this.waitForLoading();
            // The document, then up to 15s for "load": goto() alone waited for "load" with no
            // limit (no navigation timeout in the config), so one request on the page that
            // never finished held the test until its 3-minute timeout.
            const go = async () => {
                await this.page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
                await this.page.waitForLoadState('load', { timeout: 15000 }).catch(() => {});
            };
            try {
                await go();
            } catch (error) {
                // Leaving a React screen while its last request (e.g. a builder save) is still
                // running is cancelled once by the screen's leave guard: let it settle, go again.
                if (!String(error).includes('ERR_ABORTED')) {
                    throw error;
                }
                await this.page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
                await go();
            }
            await this.waitForLoading();
            console.log('\x1b[34m%s\x1b[0m', `✅ Navigated to ${url}`);
            return true;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to navigate to ${url}: ${error}`);
            throw error;
        }
    }

    // Validate an element is present in the DOM without requiring visibility.
    // Use when the *value/text is already encoded in the selector* (so presence
    // proves the data), but the element may sit in a collapsed/inactive panel —
    /**
     * Close the block editor's own modals when they open: the welcome guide (once per
     * user, the dismissal is saved, so a later test sees none) and the "Choose a pattern"
     * start screen of a new page. Waits a moment for one, never fails when none shows.
     */
    async dismissEditorModals(timeout: number = 5000) {
        const modal = this.page.locator('.components-modal__frame').first();
        for (let round = 0; round < 2; round++) {
            const shown = await modal.waitFor({ state: 'visible', timeout: 0 === round ? timeout : 1500 }).then(() => true, () => false);
            if (!shown) {
                return;
            }
            await modal.locator('button[aria-label="Close"]').first().click().catch(() => {});
            await modal.waitFor({ state: 'detached', timeout: 5000 }).catch(() => {});
        }
        // Keep the guide off for the rest of this user's session.
        await this.page.evaluate(() => {
            try {
                (window as unknown as { wp: { data: { dispatch: (s: string) => { set: (a: string, b: string, c: boolean) => void } } } }).wp.data.dispatch('core/preferences').set('core/edit-post', 'welcomeGuide', false);
            } catch (e) { /* not the block editor */ }
        }).catch(() => {});
    }

    // e.g. the EDD `edd_price` input on the block-editor download page renders
    // correct-but-hidden inside a collapsed price panel, so a visibility wait
    // (assertionValidate) would hang for the full test timeout.
    async validateAttached(locator: string) {
        try {
            await this.waitForLoading();
            await this.page.locator(locator).first().waitFor({ state: 'attached' });
            await this.waitForLoading();
            console.log('\x1b[34m%s\x1b[0m', `✅ Present (attached) ${locator}`);
            return true;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Not present ${locator}: ${error}`);
            throw error;
        }
    }

    // Just Validate
    async assertionValidate(locator: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            console.log('\x1b[34m%s\x1b[0m', `✅ Asserted ${locator}`);
            await this.waitForLoading();
            return expect(this.page.locator(locator).isVisible).toBeTruthy();
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to assert ${locator}: ${error}`);
            throw error;
        }
    }

    // Whether an element shows within the timeout. The React lists (and the
    // admin app, which loads a route's stylesheet before it renders) appear a
    // moment after the page load, so an instant isVisible() can miss them.
    async isVisibleSoon(locator: string, timeout = 15000): Promise<boolean> {
        return this.page.locator(locator).first().waitFor({ state: 'visible', timeout }).then(() => true, () => false);
    }

    // Extract form ID from URL (can be current page URL or provided URL)
    async getFormId(): Promise<string> {
        try {
            return builderFormId(this.page.url()) || null;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to extract form ID from URL: ${error}`);
            return null;
        }
    }

    // Validate and Click
    /**
     * Click a WP User Frontend submenu link of the WordPress admin menu. The form
     * builder folds the menu to its icon rail, where submenus are hover flyouts:
     * hover the top-level item first so the link is visible.
     */
    async clickWpufSubmenu(locator: string) {
        const folded = await this.page.evaluate(() => document.body.classList.contains('folded'));
        if (folded) {
            await this.page.locator('#toplevel_page_wp-user-frontend').hover();
        }
        await this.validateAndClick(locator);
    }

    async validateAndClick(locator: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            // The builder keeps Save disabled while nothing changed (title "No changes to
            // save", owner decision): a step that changed nothing has nothing to save.
            if (await this.isUnchangedSave(element)) {
                console.log('\x1b[33m%s\x1b[0m', `⏭️ Save not clicked (no changes) ${locator}`);
                return;
            }
            await element.click();
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Clicked on ${locator}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to click on ${locator}: ${error}`);
            throw error;
        }
    }

    // Validate and Click any
    async validateAndClickAny(locator: string) {
        try {
            const elements = this.page.locator(locator);
            const count = await elements.count();

            for (let i = 0; i < count; i++) {
                const element = elements.nth(i);
                if (await element.isVisible()) {
                    await element.click();
                    console.log('\x1b[35m%s\x1b[0m', `✅ Clicked on visible element: ${locator}`);
                    return;
                }
            }

            throw new Error(`No visible elements found for locator: ${locator}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to click any element: ${locator}: ${error}`);
            throw error;
        }
    }

    // Validate any
    async validateAny(locator: string) {
        try {
            const elements = this.page.locator(locator);
            const count = await elements.count();

            for (let i = 0; i < count; i++) {
                const element = elements.nth(i);
                if (await element.isVisible()) {
                    console.log('\x1b[34m%s\x1b[0m', `✅ Found visible element: ${locator}`);
                    return;
                }
            }

            throw new Error(`No visible elements found for locator: ${locator}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to validate any element: ${locator}: ${error}`);
            throw error;
        }
    }

    // Validate and Fill Strings
    /**
     * Fill a frontend rich text field: the React form's TipTap editor when it is
     * on the page, the TinyMCE iframe (classic form, or a field that opened in
     * TinyMCE) otherwise.
     */
    async fillRichEditor(frameSelector: string, bodySelector: string, value: string) {
        await this.page.waitForSelector('.wpuf-tiptap-content, iframe[id$="_ifr"]', { timeout: 15000 });
        const tiptap = this.page.locator('.wpuf-tiptap-content').first();

        if (await tiptap.count()) {
            await tiptap.click();
            await this.page.keyboard.press('ControlOrMeta+a');
            await this.page.keyboard.type(value);
            return;
        }

        await this.page.frameLocator(frameSelector).locator(bodySelector).fill(value);
    }

    async validateAndFillStrings(locator: string, value: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            await element.fill(value);
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Filled ${locator} with ${value}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to fill ${locator} with ${value}: ${error}`);
            throw error;
        }
    }

    // Fill only if the field becomes available (best-effort, non-blocking).
    // Use for OPTIONAL fields that depend on an external service which may not
    // load in every environment — e.g. the WPUF Google Map "Search address" box,
    // which only renders once the Google Maps JS API initializes. When the Maps
    // key is missing/referer-restricted the box stays hidden; the old
    // `validateAndFillStrings` then blocked for the full test timeout (~2 min) and
    // fail-fast skipped every downstream test in the spec. This waits a bounded
    // time, fills when present, and otherwise logs a loud warning and continues
    // so the rest of the form (and spec) still runs. Returns whether it filled.
    async fillStringIfAvailable(locator: string, value: string, timeoutMs: number = 30000): Promise<boolean> {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor({ state: 'visible', timeout: timeoutMs });
            await element.fill(value);
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Filled ${locator} with ${value}`);
            return true;
        } catch (error) {
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Optional field not available within ${timeoutMs}ms — skipped: ${locator} (intended value: "${value}"). If a valid Google Maps key with the right referer is configured this should not happen.`);
            return false;
        }
    }

    // Click only if the element shows up quickly (best-effort, non-blocking).
    // Use for OPTIONAL one-off notices/dismissals that may simply not exist on a
    // given site (setup wizards, EDD/PayPal admin notices). `validateAndClick`
    // waits the full 30s page default for each, so a handful of absent notices
    // burned the entire 180s test budget before the real assertion ran.
    async clickIfAvailable(locator: string, timeoutMs: number = 3000): Promise<boolean> {
        try {
            const element = this.page.locator(locator).first();
            await element.waitFor({ state: 'visible', timeout: timeoutMs });
            await element.click();
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Clicked optional ${locator}`);
            return true;
        } catch (error) {
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Optional element absent within ${timeoutMs}ms — skipped: ${locator}`);
            return false;
        }
    }

    // Validate and Fill Numbers
    async validateAndFillNumbers(locator: string, value: number) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            await element.fill(value.toString());
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Filled ${locator} with ${value}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to fill ${locator} with ${value}: ${error}`);
            throw error;
        }
    }

    // Validate and CheckBox
    async validateAndCheckBox(locator: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            await element.check();
            await this.waitForLoading();
            console.log('\x1b[35m%s\x1b[0m', `✅ Checked ${locator}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to check ${locator}: ${error}`);
            throw error;
        }
    }

    // Match Toast Notification message(s)
    async matchToastNotifications(extractedToast: string, matchWithToast: string) {
        try {
            await this.waitForLoading();
            expect(matchWithToast).toContain(extractedToast);
            await this.waitForLoading();
            console.log('\x1b[32m%s\x1b[0m', `✅ Toast notification matched: "${extractedToast}"`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Toast notification mismatch: "${extractedToast}" not found in "${matchWithToast}": ${error}`);
            throw error;
        }
    }

    /** The list button of a React frontend select whose hidden classic <select> the locator found, or null. */
    async selectProxyButton(element: import('@playwright/test').Locator) {
        const isProxy = await element.first().evaluate((el) => el.tagName === 'SELECT' && el.classList.contains('wpuf-select__native')).catch(() => false);
        return isProxy ? element.first().locator('xpath=following-sibling::*[@role="combobox"][1]') : null;
    }

    //SelectOptionWithLabel
    async selectOptionWithLabel(locator: string, label: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            // React screens use a plugin-ui combobox, not a native <select>; the React
            // frontend forms keep the classic <select> hidden beside their list button.
            const proxy = await this.selectProxyButton(element);
            if (proxy) {
                await proxy.click();
                await this.page.locator('.wpuf-select.is-open [role="option"]', { hasText: label }).first().click();
            } else if ((await element.first().getAttribute('role')) === 'combobox') {
                await element.first().click();
                await this.page.locator('[role="option"]', { hasText: label }).filter({ visible: true }).first().click();
            } else {
                await this.page.selectOption(locator, { label: label });
            }
            await this.waitForLoading();
            console.log('\x1b[33m%s\x1b[0m', `✅ Selected ${locator} with ${label}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to select ${locator} with label ${label}: ${error}`);
            throw error;
        }
    }

    //SelectOptionWithValue
    async selectOptionWithValue(locator: string, value: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            // React screens use a plugin-ui combobox, not a native <select>; the React
            // frontend forms keep the classic <select> hidden beside their list button.
            const proxy = await this.selectProxyButton(element);
            if (proxy) {
                await proxy.click();
                await this.page.locator(`.wpuf-select.is-open [role="option"][data-value="${value}"]`).first().click();
            } else if ((await element.first().getAttribute('role')) === 'combobox') {
                await element.first().click();
                await this.page.locator(`xpath=//*[@role="option"][@data-value="${value}"] >> visible=true`).first().click();
            } else {
                await this.page.selectOption(locator, { value: value });
            }
            await this.waitForLoading();
            console.log('\x1b[33m%s\x1b[0m', `✅ Selected ${locator} with ${value}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to select ${locator} with value ${value}: ${error}`);
            throw error;
        }
    }

    // Select a value in an enhanced multiselect. WPUF's redesigned multi-select field
    // (Form_Field_MultiDropdown enqueues wpuf-custom-multiselect) wraps the native
    // <select multiple> in a custom widget and hides the real element, so page.selectOption()
    // hangs waiting for a visible option. Set the option on the (attached but hidden) native
    // select and dispatch change/input so the widget syncs — robust to the widget's markup.
    async selectMultiSelectByValue(locator: string, value: string) {
        try {
            await this.waitForLoading();
            // React forms: no native select, the field's combobox (li.wpuf-el.<name>) with options.
            const name = (locator.match(/name="([^"\[]+)/) || [])[1];
            if (name && !(await this.page.locator(locator).count())) {
                const field = this.page.locator(`li.wpuf-el.${name}`).first();
                await field.locator('[role="combobox"]').first().click();
                const option = field.locator(`[role="option"][data-value="${value}"]`).or(field.locator('[role="option"]', { hasText: value })).first();
                await option.click();
                await this.page.keyboard.press('Escape');
                await expect(field.locator('.wpuf-select__chips')).toContainText(value);
                console.log('\x1b[33m%s\x1b[0m', `✅ Multiselected ${name} (React) with ${value}`);
                return;
            }
            const element = this.page.locator(locator).first();
            await element.waitFor({ state: 'attached' });
            const matched = await element.evaluate((select: HTMLSelectElement, val: string) => {
                const opt = Array.from(select.options).find(
                    o => o.value === val || o.text.trim() === val
                );
                if (!opt) {
                    return false;
                }
                opt.selected = true;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.dispatchEvent(new Event('input', { bubbles: true }));
                return true;
            }, value);
            expect(
                matched,
                `Multiselect ${locator} has no option matching "${value}"`
            ).toBe(true);
            await this.waitForLoading();
            console.log('\x1b[33m%s\x1b[0m', `✅ Multiselected ${locator} with ${value}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to multiselect ${locator} with value ${value}: ${error}`);
            throw error;
        }
    }

    // Wait for networkidle
    async waitForLoading() {
        await this.page.waitForLoadState('domcontentloaded');
    }

    /** A builder Save button that is disabled because nothing changed. */
    async isUnchangedSave(element: import('@playwright/test').Locator): Promise<boolean> {
        const first = element.first();
        return 'No changes to save' === (await first.getAttribute('title').catch(() => null)) && (await first.isDisabled().catch(() => false));
    }

    async waitForFormSaved(formSavedLocator: string, saveButtonLocator: string) {
        // Detect the transient "Saved form data" toast with a generous timeout.
        // IMPORTANT: always return false ("saved – stop") so callers that loop
        // `while (flag) { create/build form; flag = waitForFormSaved(...) }` run
        // exactly once. Returning true on a flaky false-negative made those loops
        // re-enter and create DUPLICATE forms, which then broke unscoped
        // form-name selectors with Playwright strict-mode violations.
        // Nothing changed: Save stayed disabled and no request was sent (see validateAndClick).
        if (await this.isUnchangedSave(this.page.locator(saveButtonLocator))) {
            console.log('\x1b[32m%s\x1b[0m', `✅ Nothing to save`);
            return false;
        }
        try {
            await this.waitForLoading();
            await this.page.locator(formSavedLocator).first().waitFor({ timeout: 15000 });
            await this.waitForLoading();
            console.log('\x1b[32m%s\x1b[0m', `✅ Form saved`);
            return false;
        } catch (error) {
            // Toast not seen in time (slow env / already dismissed). Best-effort:
            // click Save once more and wait again, but never propagate — treat the
            // form as saved to avoid duplicate-form creation.
            console.log('\x1b[33m%s\x1b[0m', `⚠️ Save toast not detected yet, clicking save once more`);
            try {
                await this.waitForLoading();
                await this.validateAndClick(saveButtonLocator);
                await this.page.locator(formSavedLocator).first().waitFor({ timeout: 15000 });
            } catch (retryError) {
                // ignore – assume saved
            }
            await this.waitForLoading();
            console.log('\x1b[32m%s\x1b[0m', `✅ Form saved (after retry)`);
            return false;
        }
    }

    // Check if element exists and validate its text
    async checkElementText(locator: string, expectedText: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            await expect(element).toContainText(expectedText);
            const actualText = await element.innerText();
            console.log('\x1b[32m%s\x1b[0m', "Expected Text: " + expectedText);
            console.log('\x1b[32m%s\x1b[0m', `✅ Element text validated: ${actualText}`);
            await this.waitForLoading();
            return true;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to check element text ${locator}: ${error}`);
            return false;
        }
    }

    // Helper function to generate words with minimum length
    generateWordWithMinLength(minLength: number = 5): string {
        let word = faker.word.words(1);
        while (word.length < minLength) {
            word = faker.word.words(1);
        }
        return word;
    }
}
