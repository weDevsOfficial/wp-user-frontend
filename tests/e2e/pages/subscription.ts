require('dotenv').config();
import { Page, expect } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData';
import { base } from '../pages/base';

export class subscription extends base {
    readonly page: Page;

    constructor(page: Page) {
        super(page);
    }

    async createSubscriptionPack() {
        await Promise.all([
            this.page.goto(testData.urls.baseUrl + '/wp-admin/edit.php?post_type=wpuf_subscription', { waitUntil: 'networkidle' }),
        ]);
        //First tab
        await this.page.isVisible('//a[@href="#wpuf-payment-settings"]');
        //Enter Subscription title
        await this.validateAndClick('//a[contains(text(),"Add Subscription")]');
        await this.validateAndFill('//input[@id="title"]")]', 'Subscription-Pack-1');
        //Add Description
        await this.validateAndFill('//body[@id="tinymce"]', 'This is a test Subscription-Pack-1');
        //Add Amount
        await this.validateAndFill('//input[@id="wpuf-billing-amount"]', "20") //in $$
        //Add Expiry days
        await this.validateAndFill('//input[@id="wpuf-expiration-number"]', "100") //In Days

        //Second tab
        await this.validateAndClick('//a[@href="#wpuf-post-restriction"]');
        //Apply restrictions
        await this.validateAndFill('//input[@id="wpuf-post"]', '5'); //Add number of posts restrictions
        await this.validateAndFill('//input[@id="wpuf-page"]', '2'); //Add number of pages restrictions


        //Third tab
        await this.validateAndClick('//a[@href="#taxonomy-restriction""]');

        //Publish subscription
        await this.validateAndClick('//input[@id="publish"]');

        //Validate Subscription created
        const validateSubscriptionPublished = await this.page.textContent('//div[@id="message"]');
        expect(validateSubscriptionPublished).toContain('Subscription pack published.');
        
    }
}
