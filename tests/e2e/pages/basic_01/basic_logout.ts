import type { Page } from '@playwright/test';
import { Selectors_LogoutPage } from './selectors_logout';

export class BasicLogoutPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async logOut() {
        await this.page.goto('http://localhost:8889/wp-admin/', { waitUntil: 'networkidle' });
        
        await this.page.hover(Selectors_LogoutPage.basicLogout.logoutHoverUsername);
        await this.page.click(Selectors_LogoutPage.basicLogout.logoutButton);

       //Validate LOGOUT
        await this.page.isVisible(Selectors_LogoutPage.basicLogout.logoutSucces);
        console.log("LogOut Done"); //TODO: Fix this


    }
}
