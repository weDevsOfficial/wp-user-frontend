import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page, type Locator } from '@playwright/test';
import { Base } from './base';
import { Selectors } from './selectors';

/**
 * Page Object for the React settings screen (WPUF > Settings).
 *
 * The app mounts on `#wpuf-settings-root`; tabs/fields are addressed by their
 * accessible name/text (the app uses Tailwind classes, no test ids). Storage
 * parity is the key contract — `save()` + `reloadAndAssert*` cover round-trips.
 */
export class SettingsReactPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    private get S() {
        return Selectors.settingsReact;
    }

    get root(): Locator {
        return this.page.locator(this.S.root);
    }

    /** Navigate to the settings page and wait for the React app to finish loading. */
    async goto() {
        await this.navigateToURL(this.wpufSettingsPage);
        await this.root.waitFor({ state: 'visible' });
        // Loading skeleton replaced by the real UI.
        await expect(this.page.locator(this.S.loadingSkeleton)).toHaveCount(0, { timeout: 20000 });
    }

    /** Click a left-nav tab by its visible name (e.g. "General", "Email"). */
    async openTab(name: string) {
        await this.page.locator(this.S.nav).locator('button', { hasText: name }).first().click();
        await this.waitForLoading();
    }

    /** Click a sub-tab pill by its visible name (e.g. "Social Login", "Tax"). */
    async openSubTab(name: string) {
        await this.root.locator('button', { hasText: name }).first().click();
    }

    /** Assert the panel title (the big tab heading / "Search results"). */
    async expectPanelTitle(text: string) {
        await expect(this.page.locator(this.S.panelTitle).first()).toContainText(text);
    }

    /** Locate a field control by its visible label text (label sits above the input/textarea). */
    fieldByLabel(label: string): Locator {
        return this.page.locator(
            `xpath=//*[@id="wpuf-settings-root"]//label[normalize-space()="${label}"]/following::*[self::input or self::textarea][1]`
        );
    }

    /** Fill a text/number field addressed by its label. */
    async fillByLabel(label: string, value: string) {
        const input = this.fieldByLabel(label);
        await input.scrollIntoViewIfNeeded();
        await input.fill('');
        await input.fill(value);
    }

    /** Read the current value of a field by its label. */
    async valueByLabel(label: string): Promise<string> {
        return this.fieldByLabel(label).inputValue();
    }

    // ---- Search -----------------------------------------------------------

    async search(term: string) {
        await this.page.locator(this.S.searchInput).fill(term);
    }

    async clearSearch() {
        await this.page.locator(this.S.searchClearButton).click();
    }

    async expectNoResults() {
        await expect(this.page.locator(this.S.noResults)).toBeVisible();
    }

    // ---- Save / dirty state ----------------------------------------------

    private get saveButton(): Locator {
        return this.page.getByRole('button', { name: 'Save', exact: true });
    }

    /** Click Save and wait for the "Saved" confirmation. */
    async save() {
        await this.saveButton.click();
        await expect(this.page.getByText('Saved', { exact: true })).toBeVisible({ timeout: 15000 });
    }

    /** Click the footer Cancel button (reverts unsaved edits in place). */
    async cancel() {
        await this.page.getByRole('button', { name: 'Cancel', exact: true }).click();
    }

    async expectUnsavedModal() {
        await expect(this.page.locator(this.S.unsavedModalTitle)).toBeVisible();
    }

    async discardChanges() {
        await this.page.locator(this.S.unsavedDiscardButton).click();
    }

    async continueEditing() {
        await this.page.locator(this.S.unsavedContinueButton).click();
    }

    // ---- Legacy fallback --------------------------------------------------

    async switchToClassic() {
        await this.page.locator(this.S.classicViewLink).click();
        await expect(this.page.locator(this.S.legacyScreenWrap)).toBeVisible();
    }

    async switchToNew() {
        await this.page.locator(this.S.switchToNewLink).click();
        await this.root.waitFor({ state: 'visible' });
    }

    /** Force the classic screen via the emergency URL override (no DB write). */
    async gotoLegacyOverride() {
        await this.navigateToURL(this.wpufSettingsPage + '&wpuf_settings_ui=legacy');
        await expect(this.page.locator(this.S.legacyScreenWrap)).toBeVisible();
    }
}
