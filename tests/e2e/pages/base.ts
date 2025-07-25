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
    readonly newPagePage: string = Urls.baseUrl + '/wp-admin/post-new.php?post_type=page';
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
    readonly postHerePage: string = Urls.baseUrl + '/post-here/';
    readonly siteHomePage: string = Urls.baseUrl;
    readonly wpufTransactionPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf_transaction';
    readonly wpufRegistrationFormPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
    readonly wpufRegistrationPage: string = Urls.baseUrl + '/registration-page/';
    readonly newRegFormPage: string = Urls.baseUrl + '/reg-here/';
    readonly wpufLoginPage: string = Urls.baseUrl + '/login/';
    readonly productBrandPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_brand&post_type=product';
    readonly productCategoryPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product';
    readonly productTagPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product';
    readonly productAttributePage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=product&page=product_attributes';
    readonly addProductPage: string = Urls.baseUrl + '/add-product/';
    readonly eddCatPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=download_category&post_type=download';
    readonly eddTagPage: string = Urls.baseUrl + '/wp-admin/edit-tags.php?taxonomy=download_tag&post_type=download';
    readonly addDownloadsPage: string = Urls.baseUrl + '/add-downloads/';
    readonly downloadsPage: string = Urls.baseUrl + '/wp-admin/edit.php?post_type=download';


    constructor(page: Page) {
        this.page = page;
    }

    // URL navigation
    async navigateToURL(url: string) {
        await this.waitForLoading();
        await this.page.goto(url);
        await this.waitForLoading();
        console.log('\x1b[34m%s\x1b[0m', `✅ Navigated to ${url}`);
        return true;
    }

    // Just Validate
    async assertionValidate(locator: string) {
        await this.waitForLoading();
        await this.page.locator(locator).waitFor();
        console.log('\x1b[34m%s\x1b[0m', `✅ Asserted ${locator}`);
        return expect(this.page.locator(locator).isVisible).toBeTruthy();
    }

    // Validate and Click
    async validateAndClick(locator: string) {
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await element.click();
        await this.waitForLoading();
        console.log('\x1b[35m%s\x1b[0m', `✅ Clicked on ${locator}`);
    }

    // Validate and Click by text
    async validateAndClickByText(locator: string) {
        await this.waitForLoading();
        const element = this.page.getByText(locator, { exact: true });
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await element.click();
        await this.waitForLoading();
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
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await element.fill(value);
        await this.waitForLoading();
        console.log('\x1b[35m%s\x1b[0m', `✅ Filled ${locator} with ${value}`);
    }

    // Validate and Fill Numbers
    async validateAndFillNumbers(locator: string, value: number) {
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await element.fill(value.toString());
        await this.waitForLoading();
        console.log('\x1b[35m%s\x1b[0m', `✅ Filled ${locator} with ${value}`);
    }

    // Validate and CheckBox
    async validateAndCheckBox(locator: string) {
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await element.check();
        await this.waitForLoading();
        console.log('\x1b[35m%s\x1b[0m', `✅ Checked ${locator}`);
    }

    // Match Toast Notification message(s)
    async matchToastNotifications(extractedToast: string, matchWithToast: string) {
        await this.waitForLoading();
        expect(matchWithToast).toContain(extractedToast);
        await this.waitForLoading();
    }

    //SelectOptionWithLabel
    async selectOptionWithLabel(locator: string, label: string) {
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await this.page.selectOption(locator, { label: label });
        await this.waitForLoading();
        console.log('\x1b[33m%s\x1b[0m', `✅ Selected ${locator} with ${label}`);
    }

    //SelectOptionWithLabel
    async selectOptionWithValue(locator: string, value: string) {
        await this.waitForLoading();
        const element = this.page.locator(locator);
        await element.waitFor();
        expect(element.isVisible).toBeTruthy();
        await this.page.selectOption(locator, { value: value });
        await this.waitForLoading();
        console.log('\x1b[33m%s\x1b[0m', `✅ Selected ${locator} with ${value}`);
    }

    // Wait for networkidle
    async waitForLoading() {
        await this.page.waitForLoadState('domcontentloaded', { timeout: 30000 });
    }

    async waitForFormSaved(formSavedLocator: string, saveButtonLocator: string) {
        let formNotSaved = true;
        while (formNotSaved) {
            try {
                await this.waitForLoading();
                await this.page.waitForSelector(formSavedLocator, { timeout: 30000 });
                await this.waitForLoading();
                formNotSaved = false;
            } catch (error) {
                await this.waitForLoading();
                await this.validateAndClick(saveButtonLocator);
                await this.waitForLoading();
            }
        }
        console.log('\x1b[32m%s\x1b[0m', `✅ Form saved`);
    }
}
