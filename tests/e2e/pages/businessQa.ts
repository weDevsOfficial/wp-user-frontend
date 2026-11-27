import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Selectors } from './selectors';

/**
 * Checks from the business QA round (qa-stories business BS-17 / BS-18): the
 * `[wpuf_dashboard layout=list]` embed, the post form's exit warning and keyboard
 * focus on the React frontend.
 */
export class BusinessQaPage extends Base {
    readonly s = Selectors.businessQa;

    constructor(page: Page) {
        super(page);
    }

    async openPage(url: string) {
        await this.page.goto(url);
        await expect(this.page.locator('main').first()).toBeVisible();
    }

    /** BS1701: the list layout shows the posts card only (vendor dashboards embed it). */
    async validateListLayoutShowsPostsCardOnly() {
        await expect(this.page.locator(this.s.dashboardPostsCard)).toBeVisible();
        await expect(this.page.locator(this.s.dashboardHero)).toHaveCount(0);
        await expect(this.page.locator(this.s.dashboardSide)).toHaveCount(0);
        await expect(this.page.locator(this.s.dashboardStatusCards)).toHaveCount(0);
    }

    /** BS1701: the full layout keeps the member blocks. */
    async validateFullLayoutKeepsMemberBlocks() {
        await expect(this.page.locator(this.s.dashboardHero)).toBeVisible();
        await expect(this.page.locator(this.s.dashboardPostsCard)).toBeVisible();
    }

    /**
     * BS1802: leave the form and report whether the browser asked "leave page?".
     * A pointer press outside the form gives the page the user gesture the browser
     * needs before it may show the prompt at all.
     */
    async leaveAndCheckPrompt(typeInTitle: boolean): Promise<boolean> {
        let prompted = false;
        const onDialog = async (dialog: import('@playwright/test').Dialog) => {
            if ('beforeunload' === dialog.type()) {
                prompted = true;
            }
            await dialog.accept().catch(() => { /* already handled */ });
        };

        this.page.on('dialog', onDialog);
        await expect(this.page.locator(this.s.postTitleInput)).toBeVisible();
        await this.page.locator('h1').first().click();

        if (typeInTitle) {
            await this.page.locator(this.s.postTitleInput).pressSequentially('Draft title');
        }

        await this.page.goto(this.wpAdminPage.replace(/wp-admin\/$/, ''));
        this.page.off('dialog', onDialog);

        return prompted;
    }

    async validateExitPrompt(typeInTitle: boolean) {
        const prompted = await this.leaveAndCheckPrompt(typeInTitle);
        expect(prompted, typeInTitle ? 'a real edit asks before leaving' : 'an untouched form leaves without asking').toBe(typeInTitle);
    }

    /** BS1803: the delete question opened by keyboard starts on Cancel; Enter keeps the post. */
    async validateDeleteDialogStartsOnCancel() {
        const rows = this.page.locator(this.s.rowDelete);
        const before = await rows.count();
        expect(before, 'the member has a post to delete').toBeGreaterThan(0);

        await rows.first().focus();
        await this.page.keyboard.press('Enter');
        await expect(this.page.locator(this.s.dialog)).toBeVisible();
        await expect(this.page.locator(this.s.dialogCancel)).toBeFocused();

        await this.page.keyboard.press('Enter');
        await expect(this.page.locator(this.s.dialog)).toHaveCount(0);
        await expect(rows).toHaveCount(before);
    }

    /** BS1803: keyboard focus on a dashboard link shows the kit's neutral ring, not the browser's blue outline. */
    async validateNeutralFocusRing() {
        const link = this.page.locator(`${this.s.dashboardRoot} a`).first();

        await this.page.locator('h1').first().click();
        await link.focus();
        await this.page.keyboard.press('Shift+Tab');
        await this.page.keyboard.press('Tab');

        await expect.poll(async () => link.evaluate((el) => {
            const style = getComputedStyle(el);
            return { outline: style.outlineStyle, ring: /0px 0px 0px 3px/.test(style.boxShadow) };
        })).toEqual({ outline: 'none', ring: true });
    }
}
