import * as dotenv from 'dotenv';
import { test, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users } from '../utils/testData';
import { BasicLogoutPage } from '../pages/basicLogout';

/**----------------------------------Reset Site----------------------------------**
 *
 * @Test_Scenarios : [Reset Site] 
 * @Test_RLS0001 : Admin is resetting Local Site
 * 
 */

export default function resetWordpressSite() {
    test.describe('TEST :-->', () => {

        test('RLS0001 : Admin is resetting Local Site', async ({ page }) => {
            const BasicLogin = new BasicLoginPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();
            const BasicLogout = new BasicLogoutPage(page);
            await BasicLogout.logOut();

        });
    });
}