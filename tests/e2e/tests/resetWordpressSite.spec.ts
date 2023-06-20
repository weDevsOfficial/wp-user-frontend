require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { settingsSetup } from '../pages/settingsSetup' 
import { testData } from '../utils/testData'


export default function resetWordpressSite() {


test.describe('TEST :-->', () => {
    
    test('0000:[Reset Local Site] Resetting Local Site', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        const SettingsSetup = new settingsSetup(page);

        await BasicLogin.basicLogin(testData.users.adminUsername, testData.users.adminPassword);
        await SettingsSetup.resetWordpressSite();

    });

    


});

}