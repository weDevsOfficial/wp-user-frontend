import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';

export class Base {
    readonly page: Page;

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

}
