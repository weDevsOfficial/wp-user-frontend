require('dotenv').config();
import type { Page } from '@playwright/test';
import { Selectors_LogoutPage } from './selectors_logout';

export class BasicLogoutPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async logOut() {
        const site_url = String(process.env.BASE_URL);

        await this.page.goto(site_url, { waitUntil: 'networkidle' });
        
        await this.page.hover(Selectors_LogoutPage.basicLogout.logoutHoverUsername);
        await this.page.click(Selectors_LogoutPage.basicLogout.logoutButton);

       //Validate LOGOUT
        await this.page.isVisible(Selectors_LogoutPage.basicLogout.logoutSucces);
        console.log("LogOut Done"); //TODO: Fix this


    }
}
