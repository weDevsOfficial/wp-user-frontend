require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon';
import { subscription } from '../pages/subscription';
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie


export default function subscriptionsTests() {

    test.describe('TEST :-->', () => {

        /**----------------------------------REGISTRATIONFORMS----------------------------------**
             * 
             * @TestScenario : [Reg-Forms]
             * @Test0023 : Admin is creating - Subscription plan
             * @Test0020 :
             * 
             *  
             */

        test('0023:[Subscription] Here, Admin is creating - Subscription plan', async ({ page }) => {
            const BasicLogin = new basicLoginPage(page);
            const Subscription = new subscription(page);
            //Basic login
            await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
            //Create Subscription pack
            await Subscription.createSubscriptionPack();
        });


    });

}