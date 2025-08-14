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
    readonly dokanVendorRegistrationPage: string = Urls.baseUrl + '/reg-vendor/';
    readonly dokanVendorStorePage: string = Urls.baseUrl + '/wp-admin/admin.php?page=dokan#/vendors';
    readonly wcVendorRegistrationPage: string = Urls.baseUrl + '/reg-wc-vendor/';
    readonly wcVendorsPage: string = Urls.baseUrl + '/wp-admin/admin.php?page=wcv-all-vendors#/';
    readonly wcfmMemberRegistrationPage: string = Urls.baseUrl + '/reg-member/';

    constructor(page: Page) {
        this.page = page;
    }

    // URL navigation
    async navigateToURL(url: string) {
        try {
            await this.waitForLoading();
            await this.page.goto(url);
            await this.waitForLoading();
            console.log('\x1b[34m%s\x1b[0m', `✅ Navigated to ${url}`);
            return true;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to navigate to ${url}: ${error}`);
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

    // Validate and Click
    async validateAndClick(locator: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
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

    //SelectOptionWithLabel
    async selectOptionWithLabel(locator: string, label: string) {
        try {
            await this.waitForLoading();
            const element = this.page.locator(locator);
            await element.waitFor();
            expect(element.isVisible).toBeTruthy();
            await this.page.selectOption(locator, { label: label });
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
            await this.page.selectOption(locator, { value: value });
            await this.waitForLoading();
            console.log('\x1b[33m%s\x1b[0m', `✅ Selected ${locator} with ${value}`);
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to select ${locator} with value ${value}: ${error}`);
            throw error;
        }
    }

    // Wait for networkidle
    async waitForLoading() {
        await this.page.waitForLoadState('domcontentloaded');
    }

    async waitForFormSaved(formSavedLocator: string, saveButtonLocator: string) {
        try {
            let formNotSaved = true;
            let count = 1;
            while (formNotSaved && count < 2) {
                try {
                    await this.waitForLoading();
                    await this.page.locator(formSavedLocator).waitFor({ timeout: 5000 });
                    await this.waitForLoading();
                    formNotSaved = false;
                } catch (error) {
                    console.log('\x1b[33m%s\x1b[0m', `⚠️ Form not saved yet, clicking save button`);
                    await this.waitForLoading();
                    await this.validateAndClick(saveButtonLocator);
                    await this.waitForLoading();
                    count++;
                }
            }
            console.log('\x1b[32m%s\x1b[0m', `✅ Form saved`);
            return false;
        } catch (error) {
            console.log('\x1b[31m%s\x1b[0m', `❌ Failed to save form`);
            return true;
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
}
