import type { Page } from '@playwright/test';
import { SelectorsPage } from './selectors';

export class LogoutPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async logOut() {
        await this.page.hover(SelectorsPage.logout.logoutHoverUsername);
        await this.page.click(SelectorsPage.logout.logoutButton);

       //Validate LOGOUT
        await this.page.isVisible(SelectorsPage.logout.logoutSucces);
        console.log("LogOut Done"); //TODO: Fix this


    }


    // async userIsLoggedOut(): Promise<boolean> {
    //     return Common_Actions.isVisible(this.page, 'a[routerlink="/login"]');
    // }
}
