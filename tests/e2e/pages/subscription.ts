require('dotenv').config();
import { Page, expect } from '@playwright/test';
import { selectors } from './selectors';
import { Urls } from '../utils/testData';
import { base } from '../pages/base';




export class subscription extends base {
    readonly page: Page;

    constructor(page: Page) {
        super(page);
    }

    //Subscription pack - Basic
    async create_SubscriptionPack_Basic(subscriptionPackName: string, subscriptionPackDescription: string, SubscriptionPackPrice: string, SubscriptionPackExpiration: number) {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit.php?post_type=wpuf_subscription', { waitUntil: 'networkidle' }),
        ]);

        //First tab
        await this.page.isVisible('//a[@href="#wpuf-payment-settings"]');
            //Enter Subscription title
            await this.validateAndClick('//a[contains(text(),"Add Subscription")]');
            await this.validateAndFillStrings('//input[@id="title"]")]', subscriptionPackName);
            //Add Description
            await this.validateAndFillStrings('//body[@id="tinymce"]', subscriptionPackDescription);
            //Add Amount
            await this.validateAndFillStrings('//input[@id="wpuf-billing-amount"]', SubscriptionPackPrice) //in $$
            //Add Expiry days
            await this.validateAndFillStrings('//input[@id="wpuf-expiration-number"]', SubscriptionPackExpiration.toString()) //In Days
        //Second tab
        await this.page.isVisible('//a[@href="#wpuf-post-restriction"]');
        //Third tab
        await this.page.isVisible('//a[@href="#taxonomy-restriction""]');
        //Publish subscription
        await this.validateAndClick('//input[@id="publish"]');

        //Validate Subscription created
        const validateSubscriptionPublished = await this.page.textContent('//div[@id="message"]');
        expect(validateSubscriptionPublished).toContain('Subscription pack published.');

        return subscriptionPackName;
    }


    //Subscription pack - FEATURED Post
    async update_Subscription_Featured(subscriptionPackName: string ,subscriptionFeaturedCount: number) {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit.php?post_type=wpuf_subscription', { waitUntil: 'networkidle' }),
        ]);

        this.create_SubscriptionPack_Basic

        //Second tab
        await this.validateAndClick('//a[@href="#wpuf-post-restriction"]');
            //Apply restrictions
            await this.validateAndFillNumbers('//input[@id="wpuf-sticky-item"]', subscriptionFeaturedCount); //Add number of featured restrictions
        //Third tab
        await this.validateAndClick('//a[@href="#taxonomy-restriction""]');
        //Publish subscription
        await this.validateAndClick('//input[@id="publish"]');

        //Validate Subscription created
        const validateSubscriptionPublished = await this.page.textContent('//div[@id="message"]');
        expect(validateSubscriptionPublished).toContain('Subscription pack published.');
    }

    //Subscription pack - POST restrictions
    async update_Subscription_PostRestriction(Subscription_Posts_Count: number) {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit.php?post_type=wpuf_subscription', { waitUntil: 'networkidle' }),
        ]);

        //First tab
        await this.page.isVisible('//a[@href="#wpuf-payment-settings"]');
            //Enter Subscription title
            await this.validateAndClick('//a[contains(text(),"Add Subscription")]');
            await this.validateAndFillStrings('//input[@id="title"]")]', 'Subscription-Pack-1');
            //Add Description
            await this.validateAndFillStrings('//body[@id="tinymce"]', 'This is a test Subscription-Pack-1');
            //Add Amount
            await this.validateAndFillStrings('//input[@id="wpuf-billing-amount"]', "20") //in $$
            //Add Expiry days
            await this.validateAndFillStrings('//input[@id="wpuf-expiration-number"]', "100") //In Days
        //Second tab
        await this.validateAndClick('//a[@href="#wpuf-post-restriction"]');
        //Apply restrictions
        await this.validateAndFillNumbers('//input[@id="wpuf-post"]', Subscription_Posts_Count); //Add number of posts restrictions
        //Third tab
        await this.validateAndClick('//a[@href="#taxonomy-restriction""]');
        //Publish subscription
        await this.validateAndClick('//input[@id="publish"]');

        //Validate Subscription created
        const validateSubscriptionPublished = await this.page.textContent('//div[@id="message"]');
        expect(validateSubscriptionPublished).toContain('Subscription pack published.');
    }

    //Subscription pack - Post and Page restrictions
    async update_Subscription_PageRestriction(Subscription_Pages_Count: number) {
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/edit.php?post_type=wpuf_subscription', { waitUntil: 'networkidle' }),
        ]);

        //Second tab
        await this.validateAndClick('//a[@href="#wpuf-post-restriction"]');
            //Apply restrictions
            await this.validateAndFillNumbers('//input[@id="wpuf-page"]', Subscription_Pages_Count); //Add number of pages restrictions
        //Third tab
        await this.validateAndClick('//a[@href="#taxonomy-restriction""]');
        //Publish subscription
        await this.validateAndClick('//input[@id="publish"]');

        //Validate Subscription created
        const validateSubscriptionPublished = await this.page.textContent('//div[@id="message"]');
        expect(validateSubscriptionPublished).toContain('Subscription pack published.');
    }






}
