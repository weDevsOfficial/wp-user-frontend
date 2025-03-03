require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { SettingsSetupPage } from '../pages/settingsSetup'
import { Users } from '../utils/testData'


export default function resetWordpressSite() {


    test.describe('TEST :-->', () => {

        test('0000:[Reset Local Site] Resetting Local Site', async ({ page }) => {
            const BasicLogin = new BasicLoginPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();

        });



    });

}