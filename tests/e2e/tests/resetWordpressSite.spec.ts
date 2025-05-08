require('dotenv').config();
import { test, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { SettingsSetupPage } from '../pages/settingsSetup'
import { Users } from '../utils/testData'
import { BasicLogoutPage } from '../pages/basicLogout';


export default function resetWordpressSite() {


    test.describe('TEST :-->', () => {

        test('000:[Reset Local Site] Resetting Local Site', async ({ page }) => {
            const BasicLogin = new BasicLoginPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();
            const BasicLogout = new BasicLogoutPage(page);
            await BasicLogout.logOut();

        });



    });

}