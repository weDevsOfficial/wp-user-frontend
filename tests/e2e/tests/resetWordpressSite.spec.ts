require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { settingsSetup } from '../pages/settingsSetup'
import { Users } from '../utils/testData'


export default function resetWordpressSite() {


    test.describe('TEST :-->', () => {

        test('0000:[Reset Local Site] Resetting Local Site', async ({ page }) => {
            const BasicLogin = new basicLoginPage(page);
            const SettingsSetup = new settingsSetup(page);

            await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
            await SettingsSetup.resetWordpressSite();

        });



    });

}