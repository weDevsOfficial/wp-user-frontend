import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Urls } from '../utils/testData';

export class Base {
    readonly page: Page;
    readonly wpAdminPage: string = Urls.baseUrl + '/wp-admin/';
    readonly pluginsPage: string = Urls.baseUrl + '/wp-admin/plugins.php';
    readonly toolsPage: string = Urls.baseUrl + '/wp-admin/tools.php';
    readonly settingsPage: string = Urls.baseUrl + '/wp-admin/options-general.php';
    readonly usersPage: string = Urls.baseUrl + '/wp-admin/users.php';
    readonly postsPage: string = Urls.baseUrl + '/wp-admin/edit.php';
    readonly pagesPage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
    readonly mediaPage: string = Urls.baseUrl + '/wp-admin/upload.php';
    readonly accountPage: string = Urls.baseUrl + '/account/';
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
    readonly siteHomePage: string = Urls.baseUrl;
    readonly wpufTransactionPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf_transaction';
    readonly wpufRegistrationFormPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
    readonly wpufRegistrationPage: string = Urls.baseUrl + '/registration-page/';
    readonly newRegFormPage: string = Urls.baseUrl + '/reg-here/';
    readonly wpufLoginPage: string = Urls.baseUrl + '/login/';

    constructor(page: Page) {
        this.page = page;
    }

    // Just Validate
    async assertionValidate(locator: string) {
        await this.page.locator(locator).waitFor();
        return expect(this.page.locator(locator).isVisible).toBeTruthy();
    }

    // Validate and Click
    async validateAndClick(locator: string) {
        await this.page.locator(locator).waitFor();
        expect(this.page.locator(locator).isVisible).toBeTruthy();
        await this.page.locator(locator).click();
    }

    // Validate and Click by text
    async validateAndClickByText(locator: string) {
        await this.page.getByText(locator).waitFor();
        expect(this.page.getByText(locator).isVisible).toBeTruthy();
        await this.page.getByText(locator).click();
    }

    // Validate and Click any
    async validateAndClickAny(locator: string) {
        const elements = this.page.locator(locator);
        const count = await elements.count();

        for (let i = 0; i < count; i++) {
            const element = elements.nth(i);
            if (await element.isVisible()) {
                await element.click();
                return; // Exit the function once a visible element is clicked
            }
        }

        throw new Error(`No visible elements found for locator: ${locator}`);
    }

    // Validate any
    async validateAny(locator: string) {
        const elements = this.page.locator(locator);
        const count = await elements.count();

        for (let i = 0; i < count; i++) {
            const element = elements.nth(i);
            if (await element.isVisible()) {
                return; // Exit the function once a visible element is clicked
            }
        }

        throw new Error(`No visible elements found for locator: ${locator}`);
    }

    // Validate and Fill Strings
    async validateAndFillStrings(locator: string, value: string) {
        await this.page.locator(locator).waitFor();
        expect(this.page.locator(locator).isVisible).toBeTruthy();
        await this.page.locator(locator).fill(value);
    }

    // Validate and Fill Numbers
    async validateAndFillNumbers(locator: string, value: number) {
        await this.page.locator(locator).waitFor();
        expect(this.page.locator(locator).isVisible).toBeTruthy();
        await this.page.locator(locator).fill(value.toString());
    }

    // Validate and CheckBox
    async validateAndCheckBox(locator: string) {
        await this.page.locator(locator).waitFor();
        expect(this.page.locator(locator).isVisible).toBeTruthy();
        await this.page.locator(locator).check();
    }

    // Match Toast Notification message(s)
    async matchToastNotifications(extractedToast: string, matchWithToast: string) {
        expect(matchWithToast).toContain(extractedToast);
    }

    // Wait for networkidle
    async waitForLoading() {
        await this.page.waitForLoadState('networkidle');
        await this.page.waitForLoadState('domcontentloaded');
    }

}
