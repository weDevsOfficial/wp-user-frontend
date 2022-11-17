import type { Page } from '@playwright/test';
//import { isVisible } from '../framework/common-actions';

import { SelectorsPage } from './selectors';

export class HomePage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async open() {
        await this.page.goto('https://ratul.ajaira.website/wp-admin/');
    }

    async goToLoginPage() {
        await this.page.click('a[routerlink="/login"]');
    }

    // async userIsLoggedIn(): Promise<boolean> {
    //     return await isVisible(this.page, 'a[routerlink="/editor"]');
    // } 

    async goToSettings() {
        await this.page.click('a[routerlink="/settings"]');
    }
}
