import { Page, Locator, expect } from '@playwright/test';

export class WaitHelpers {
    constructor(private page: Page) {}

    /**
     * Wait for element to be visible and ready for interaction
     */
    async waitForElementReady(locator: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'visible', timeout });
        return element;
    }

    /**
     * Wait for element to be attached to DOM (may not be visible)
     */
    async waitForElementAttached(locator: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'attached', timeout });
        return element;
    }

    /**
     * Wait for element to be hidden/removed
     */
    async waitForElementHidden(locator: string, timeout: number = 30000): Promise<void> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'hidden', timeout });
    }

    /**
     * Wait for element to be enabled (not disabled)
     */
    async waitForElementEnabled(locator: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'visible', timeout });
        await expect(element).toBeEnabled({ timeout });
        return element;
    }

    /**
     * Wait for network to be idle (no requests for 500ms)
     */
    async waitForNetworkIdle(timeout: number = 30000): Promise<void> {
        await this.page.waitForLoadState('networkidle', { timeout });
    }

    /**
     * Wait for specific URL to be loaded
     */
    async waitForURL(url: string, timeout: number = 30000): Promise<void> {
        await this.page.waitForURL(url, { timeout });
    }

    /**
     * Wait for element to contain specific text
     */
    async waitForElementText(locator: string, text: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'visible', timeout });
        await expect(element).toContainText(text, { timeout });
        return element;
    }

    /**
     * Wait for element to have specific attribute value
     */
    async waitForElementAttribute(locator: string, attribute: string, value: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'visible', timeout });
        await expect(element).toHaveAttribute(attribute, value, { timeout });
        return element;
    }

    /**
     * Wait for page to be fully loaded
     */
    async waitForPageLoad(timeout: number = 30000): Promise<void> {
        await this.page.waitForLoadState('domcontentloaded', { timeout });
        await this.page.waitForLoadState('load', { timeout });
    }

    /**
     * Wait for element to be stable (no animations/layout shifts)
     */
    async waitForElementStable(locator: string, timeout: number = 30000): Promise<Locator> {
        const element = this.page.locator(locator);
        await element.waitFor({ state: 'visible', timeout });
        // Wait a bit more for any animations to complete
        await this.page.waitForTimeout(500);
        return element;
    }
} 