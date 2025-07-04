import * as dotenv from 'dotenv';
dotenv.config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';

export class BasicLogoutPage extends Base {

    constructor(page: Page) {
        super(page);
    }

    async logOut() {
        await this.page.goto(this.wpAdminPage , { waitUntil: 'domcontentloaded', timeout: 30000 });

        await this.page.waitForTimeout(200);
        await this.page.hover(Selectors.logout.basicLogout.logoutHoverUsername);
        await this.page.waitForTimeout(100);
        await this.validateAndClick(Selectors.logout.basicLogout.logoutButton);

        //Validate LOGOUT
        await this.assertionValidate(Selectors.logout.basicLogout.logoutSuccess);
        console.log('LogOut Done');


    }
}
