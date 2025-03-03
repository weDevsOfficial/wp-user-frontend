require('dotenv').config();
import { expect, Page } from '@playwright/test';

export class Base {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async validateAndClick(locator: string) {
        expect(await this.page.isVisible(locator)).toBeTruthy();
        await this.page.click(locator)
    };

    async validateAndFillStrings(locator: string, value: string) {
        expect(await this.page.isVisible(locator)).toBeTruthy();
        await this.page.fill(locator, value);
    }

    async validateAndFillNumbers(locator: string, value: number) {
        expect(await this.page.isVisible(locator)).toBeTruthy();
        await this.page.fill(locator, value.toString());
    }

}
