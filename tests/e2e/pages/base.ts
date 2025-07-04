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
        try {
            await this.page.locator(locator).waitFor({ state: 'visible', timeout: 30000 });
            await expect(this.page.locator(locator)).toBeVisible();
            await this.waitForLoading();
            return true;
        } catch (error) {
            throw new Error(`Element not visible: ${locator}. Error: ${error.message}`);
        }
    }

    // Validate and Click
    async validateAndClick(locator: string) {
        try {
            const element = this.page.locator(locator);
            await element.waitFor({ state: 'visible', timeout: 30000 });
            await expect(element).toBeVisible();
            await element.click({ timeout: 10000 });
            await this.waitForLoading();
        } catch (error) {
            throw new Error(`Failed to click element: ${locator}. Error: ${error.message}`);
        }
    }

    // Validate and Click by text
    async validateAndClickByText(locator: string) {
        try {
            const element = this.page.getByText(locator, { exact: false });
            await element.waitFor({ state: 'visible', timeout: 30000 });
            await expect(element).toBeVisible();
            await element.click({ timeout: 10000 });
            await this.waitForLoading();
        } catch (error) {
            throw new Error(`Failed to click text element: ${locator}. Error: ${error.message}`);
        }
    }

    // Validate and Click any
    async validateAndClickAny(locator: string) {
        const elements = this.page.locator(locator);
        const count = await elements.count();

        for (let i = 0; i < count; i++) {
            const element = elements.nth(i);
            if (await element.isVisible()) {
                await element.click();
                await this.waitForLoading();
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
                await this.waitForLoading();
                return; // Exit the function once a visible element is clicked
            }
        }

        throw new Error(`No visible elements found for locator: ${locator}`);
    }

    // Validate and Fill Strings
    async validateAndFillStrings(locator: string, value: string) {
        try {
            const element = this.page.locator(locator);
            await element.waitFor({ state: 'visible', timeout: 30000 });
            await expect(element).toBeVisible();
            await element.fill(value, { timeout: 10000 });
            await this.waitForLoading();
        } catch (error) {
            throw new Error(`Failed to fill element: ${locator} with value: ${value}. Error: ${error.message}`);
        }
    }

    // Validate and Fill Numbers
    async validateAndFillNumbers(locator: string, value: number) {
        try {
            const element = this.page.locator(locator);
            await element.waitFor({ state: 'visible', timeout: 30000 });
            await expect(element).toBeVisible();
            await element.fill(value.toString(), { timeout: 10000 });
            await this.waitForLoading();
        } catch (error) {
            throw new Error(`Failed to fill element: ${locator} with number: ${value}. Error: ${error.message}`);
        }
    }

    // Validate and CheckBox
    async validateAndCheckBox(locator: string) {
        try {
            const element = this.page.locator(locator);
            await element.waitFor({ state: 'visible', timeout: 30000 });
            await expect(element).toBeVisible();
            await element.check({ timeout: 10000 });
            await this.waitForLoading();
            } catch (error) {
            throw new Error(`Failed to check checkbox: ${locator}. Error: ${error.message}`);
        }
    }

    // Match Toast Notification message(s)
    async matchToastNotifications(extractedToast: string, matchWithToast: string) {
        try {
            expect(matchWithToast).toContain(extractedToast);
            await this.waitForLoading();
        } catch (error) {
            throw new Error(`Toast message mismatch. Expected to contain: "${extractedToast}" but got: "${matchWithToast}"`);
        }
    }

    // Wait for networkidle
    async waitForLoading() {
        await this.page.waitForLoadState('domcontentloaded', { timeout: 30000 });
    }

}
