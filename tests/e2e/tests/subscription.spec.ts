require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon';
import { subscription } from '../pages/subscription';
import { Urls, Users, SubscriptionPack } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie
import { stringify } from 'querystring';


export default function subscriptionsTests() {

    test.describe('Basic Subscription - Check', () => {
        /**-----------------------*/
        /**-------DATA_SET-------*/
        /**---------------------*/
        let SubscriptionPackName: string = SubscriptionPack.subscriptionPackName;
        let SubscriptionPackDescription: string = SubscriptionPack.subscriptionPackDescription;
        let SubscriptionPackPrice: string = SubscriptionPack.SubscriptionPackPrice;
        let SubscriptionPackExpiration: number = SubscriptionPack.SubscriptionPackExpiration;



        /**------------------------------------------*/
        /**-------TEST-Scenarios: Basic-------*/
        /**----------------------------------------*/
        test('0023: Create - Subscription plan', { tag: '@Subscription' }, async ({ page }) => {
            const BasicLogin = new basicLoginPage(page);
            const Subscription = new subscription(page);
            //Basic login
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            SubscriptionPackName = await Subscription.create_SubscriptionPack_Basic(`[Basic]+${SubscriptionPackName}`, SubscriptionPackDescription, SubscriptionPackPrice, SubscriptionPackExpiration);
            // Log the returned subscription pack name
            console.log('Subscription Pack Created:', SubscriptionPackName);
        });

        test('0024: Purchase Subscription - Basic', { tag: ['@Subscription', '@Frontend'] }, async ({ page }) => {


        })

    });


    test.describe('Subscription - Featured Posts', () => {
        let subscriptionPackName: string = "";
        test('0025: Create - Subscription plan', { tag: '@Subscription' }, async ({ page }) => {
            const BasicLogin = new basicLoginPage(page);
            const Subscription = new subscription(page);
            //Basic login
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            subscriptionPackName = await Subscription.create_SubscriptionPack_Basic(`[Basic]+${SubscriptionPack.subscriptionPackName}`, SubscriptionPack.subscriptionPackDescription, SubscriptionPack.SubscriptionPackPrice, SubscriptionPack.SubscriptionPackExpiration);
            // Log the returned subscription pack name
            console.log('Subscription Pack Created:', subscriptionPackName);
        });

        test('0026: Update - Subscription plan: Featured Posts', { tag: '@Subscription' }, async ({ page }) => {
            const BasicLogin = new basicLoginPage(page);
            const Subscription = new subscription(page);
            //Update Subscription - Featured Posts
            await Subscription.update_Subscription_Featured(subscriptionPackName, SubscriptionPack.subscriptionFeaturedCount)
        });



    });



}