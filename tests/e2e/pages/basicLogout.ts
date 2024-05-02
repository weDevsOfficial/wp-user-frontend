require('dotenv').config();
import type { Page } from '@playwright/test';
import { selectors } from './selectors';
import { Urls } from '../utils/testData';

export class basicLogoutPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async logOut() {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        await this.page.hover(selectors.logout.basicLogout.logoutHoverUsername);
        await this.page.click(selectors.logout.basicLogout.logoutButton);

        //Validate LOGOUT
        await this.page.isVisible(selectors.logout.basicLogout.logoutSuccess);
        console.log("LogOut Done");


    }
}
