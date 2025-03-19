require('dotenv').config();
import type { Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';

export class BasicLogoutPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async logOut() {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        await this.page.hover(Selectors.logout.basicLogout.logoutHoverUsername);
        await this.page.click(Selectors.logout.basicLogout.logoutButton);

        //Validate LOGOUT
        await this.page.isVisible(Selectors.logout.basicLogout.logoutSuccess);
        console.log("LogOut Done");


    }
}
