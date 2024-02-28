require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData';

export class base {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async validateAndClick(locator: string) {
        expect(await this.page.isVisible(locator)).toBeTruthy();
        await this.page.click(locator)
    };

    async validateAndFill(locator: string, value: string) {
        expect(await this.page.isVisible(locator)).toBeTruthy();
        await this.page.fill(locator, value);
    }



}
