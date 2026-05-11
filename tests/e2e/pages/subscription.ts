import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { Urls, SubscriptionPacks, PostForm } from '../utils/testData';

/**
 * SubscriptionPage - Page Object Model for WPUF Subscription Module
 * 
 * This class handles all subscription-related operations including:
 * - Creating subscription packs (Admin)
 * - Managing pack settings (Admin)
 * - Subscribing to packs (Frontend)
 * - Validating subscription status (Frontend & Admin)
 * - Transaction management (Admin)
 */

export class SubscriptionPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    /*************************************************/
    /******* @Admin - Subscription Pack CRUD *********/
    /*************************************************/

    /**
     * Create a Free Subscription Pack using Classic Editor
     * @param packData - Subscription pack data object
     */

    async navigateToSubscriptionPage() {
        await this.navigateToURL(this.wpufSubscriptionPage);
    }

    async createSubscriptionPack(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.createNewPackButton);
    }

    async navigateToSubscriptionDetailsSection() {
        // Click Subscription Details Section
        await this.validateAndClick(Selectors.subscription.newPackPage.subscriptionDetailsSection);
        await this.waitForLoading();
    }

    async fillSubscriptionOverviewDetails(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Fill Pack Name
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.planNameInput, packData.name);

        // Fill Pack Description
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.planSummaryInput, packData.description);
    }

    async fillAccessAndVisibilityDetails(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Click Access and Visibility Section
        await this.validateAndClick(Selectors.subscription.newPackPage.accessAndVisibilitySection);
        await this.waitForLoading();
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.planSlugInput, packData.slug);
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.sortOrderInput, packData.sortOrder.toString());
    }

    async fillPostExpirationDetails(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Click Post Expiration Section
        await this.validateAndClick(Selectors.subscription.newPackPage.postExpirationSection);
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.newPackPage.enablePostExpirationToggle);
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.postExpirationTimeInput, packData.expirationTime.toString());
        await this.selectOptionWithValue(Selectors.subscription.newPackPage.postExpirationUnitSelect, packData.expirationUnit);
        await this.selectOptionWithValue(Selectors.subscription.newPackPage.expiredPostStatusSelect, packData.expiredPostStatus);
        await this.validateAndClick(Selectors.subscription.newPackPage.sendExpirationMailToggle);
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.expirationMessageTextarea, packData.expirationMessage);
        await this.validateAndClick(Selectors.subscription.newPackPage.enablepostNumberRollback);
    }

    async navigateToPaymentSettingsSection() {
        // Click Payment Settings Section
        await this.validateAndClick(Selectors.subscription.newPackPage.paymentSettingsTab);
        await this.waitForLoading();
    }
    async fillNonRecurringPaymentDetails(packData: typeof SubscriptionPacks.freeBasicPack) {

        // Set Billing Amount (0 for free)
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingAmountInput, packData.billingAmount.toString());

        // Set Expiration Number
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.expirationNumberInput, packData.expirationNumber.toString());

        // Set Expiration Period
        await this.selectOptionWithValue(Selectors.subscription.newPackPage.expirationPeriodSelect, packData.expirationPeriod);
    }

    async fillRecurringPaymentWithoutTrial(packData: typeof SubscriptionPacks.freeBasicPack, billingLimitInput: string) {

        // Set Billing Amount (0 for free)
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingAmountInput, packData.billingAmount.toString());

        await this.validateAndClick(Selectors.subscription.newPackPage.enableRecurringPaymentToggle);

        // Set Billing Cycle Number
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingCycleInput, packData.expirationNumber.toString());

        // Set Cycle Period
        await this.selectOptionWithValue(Selectors.subscription.newPackPage.cyclePeriodSelect, packData.expirationPeriod);

        await this.validateAndClick(Selectors.subscription.newPackPage.stopCycleToggle);

        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingLimitInput, billingLimitInput);
        
        
    }

    async fillRecurringPaymentWithTrial(packData: typeof SubscriptionPacks.freeBasicPack, billingLimitInput: string, expirationNumber:string, expirationPeriod:string) {

        // Set Billing Amount (0 for free)
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingAmountInput, packData.billingAmount.toString());

        await this.validateAndClick(Selectors.subscription.newPackPage.enableRecurringPaymentToggle);

        // Set Billing Cycle Number
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingCycleInput, packData.expirationNumber.toString());

        // Set Cycle Period
        await this.selectOptionWithValue(Selectors.subscription.newPackPage.cyclePeriodSelect, packData.expirationPeriod);

        await this.validateAndClick(Selectors.subscription.newPackPage.stopCycleToggle);

        await this.validateAndFillStrings(Selectors.subscription.newPackPage.billingLimitInput, billingLimitInput);

        await this.validateAndClick(Selectors.subscription.newPackPage.enableTrialToggle);

        await this.validateAndFillStrings(Selectors.subscription.newPackPage.trialPeriodInput, expirationNumber);

        await this.selectOptionWithValue(Selectors.subscription.newPackPage.trialPeriodUnitSelect, expirationPeriod);
        
        
    }

    async publishPack() {
        // Publish Pack
        await this.page.locator(Selectors.subscription.newPackPage.savePackButton).hover();
        await this.waitForLoading();
        await this.page.waitForTimeout(500);
        await this.validateAndClick(Selectors.subscription.newPackPage.publishPackButton);
        SubscriptionPacks.packCounts.publishedPackCount++;

    }

    async updatePack() {
        // Publish Pack
        await this.page.locator(Selectors.subscription.newPackPage.updatePackButton).hover();
        await this.waitForLoading();
        await this.page.waitForTimeout(500);
        await this.validateAndClick(Selectors.subscription.newPackPage.publishPackButton);
    }

    async draftPack() {
        // Publish Pack
        await this.page.locator(Selectors.subscription.newPackPage.updatePackButton).hover();
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.newPackPage.draftPackButton);
        SubscriptionPacks.packCounts.draftPackCount++;
    }

    async validatePackPublishedBE(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Validate pack created
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackCreated(packData.name));
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackPublished(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" created successfully`);
    }

    async validatePackDraftedBE(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Validate pack created
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackCreated(packData.name));
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackDrafted(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" created successfully`);
    }

    async navigateToTrashTab() {
        await this.validateAndClick(Selectors.subscription.listPage.trashTab1);
        await this.waitForLoading();
    }

    async navigateToDraftTab() {
        await this.validateAndClick(Selectors.subscription.listPage.draftTab1);
        await this.waitForLoading();
    }

    async validatePackFreeBE(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Validate pack is free
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackFree(packData.name));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" is free`);
    }

    async validateAllPackCountBE() {
        // Validate all pack count
        await this.assertionValidate(Selectors.subscription.newPackPage.validateAllPackCount(SubscriptionPacks.packCounts.allPackCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ All pack count validated: ${SubscriptionPacks.packCounts.allPackCount}`);
    }

    async validatePublishedPackCountBE() {
        // Validate published pack count
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePublishedPackCount(SubscriptionPacks.packCounts.publishedPackCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ Published pack count validated: ${SubscriptionPacks.packCounts.publishedPackCount}`);
    }

    async validateDraftPackCountBE() {
        // Validate draft pack count
        await this.assertionValidate(Selectors.subscription.newPackPage.validateDraftPackCount(SubscriptionPacks.packCounts.draftPackCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ Draft pack count validated: ${SubscriptionPacks.packCounts.draftPackCount}`);
    }

    async validateTrashPackCountBE() {
        // Validate trash pack count
        await this.assertionValidate(Selectors.subscription.newPackPage.validateTrashPackCount(SubscriptionPacks.packCounts.trashPackCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ Trash pack count validated: ${SubscriptionPacks.packCounts.trashPackCount}`);
    }

    async validateSubscribersCountBE(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Validate subscribers count
        await this.assertionValidate(Selectors.subscription.newPackPage.validateSubscriberscount(packData.name, SubscriptionPacks.packCounts.subscribersCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscribers count validated: ${SubscriptionPacks.packCounts.subscribersCount}`);
    }

    async validateSubscribersCountBuilder(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Validate subscribers count
        await this.assertionValidate(Selectors.subscription.newPackPage.validateSubscriberscount(packData.name, SubscriptionPacks.packCounts.subscribersCount));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscribers count validated: ${SubscriptionPacks.packCounts.subscribersCount}`);
    }

    async navigateToPreferencesSection() {
        // Click Preferences Section
        await this.validateAndClick(Selectors.subscription.newPackPage.packPreferences);
        await this.waitForLoading();
    }

    async validatePackPaidBE(packData: typeof SubscriptionPacks.paidPack) {
        // Validate pack is paid
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackPaid(packData.name, packData.billingAmount.toString()));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" is paid`);
    }

    async validateRecurringPackPaidBE(packData: typeof SubscriptionPacks.paidPack) {
        // Validate pack is paid
        await this.assertionValidate(Selectors.subscription.newPackPage.validateRecurringPackPaid(packData.name, packData.billingAmount.toString(), packData.expirationNumber.toString(), packData.expirationPeriod));
        await this.assertionValidate(Selectors.subscription.newPackPage.recurringPackIcon(packData.name));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" is paid`);
    }

    async validateRecurringPackPaidFE(packData: typeof SubscriptionPacks.paidPack) {
        // Validate pack is paid
        await this.assertionValidate(Selectors.subscription.frontendPage.validateRecurringPackPaid(packData.name, packData.billingAmount.toString(), packData.expirationNumber.toString(), packData.expirationPeriod, '5'));

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" is paid`);
    }

    async navigateToAdvancedSection() {
        // Click Advanced Configuration Section
        await this.validateAndClick(Selectors.subscription.newPackPage.advanceConfigurationSection);
        await this.waitForLoading();
    }

    async clickAdditionalOptions(){
        // Click Additional Options Section
        await this.validateAndClick(Selectors.subscription.newPackPage.additionalOptionsSection);
        await this.waitForLoading();
    }

    async clickPostCategories(){
        // Click Additional Options Section
        await this.validateAndClick(Selectors.subscription.newPackPage.postCategoriesSection);
        await this.waitForLoading();
    }

    async clickPostViewCategories(){
        // Click Additional Options Section
        await this.validateAndClick(Selectors.subscription.newPackPage.postViewCategoriesSection);
        await this.waitForLoading();
    }

    async fillPostNumbers(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Set Number of Posts
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.maxPostsInput, packData.maxPosts.toString());
    }

    async fillPageNumbers(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Set Number of Pages
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.maxPagesInput, packData.maxPages.toString());
    }

    async fillUserRequests(packData: typeof SubscriptionPacks.freeBasicPack) {
        // Set Number of User Requests
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.maxUserReqInput, packData.maxUserRequests.toString());
    }

    async fillFeaturedItems(packData: typeof SubscriptionPacks.freeBasicPack){
        // Set Number of Featured Items
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.maxFeaturedItemsInput, packData.maxFeaturedItems.toString());
        await this.validateAndClick(Selectors.subscription.newPackPage.removeFeaturedOnExpiryToggle);
    }

    async selectPostCategories(packData: typeof SubscriptionPacks.freeBasicPack){
        // Set Number of Featured Items
        await this.validateAndClick(Selectors.subscription.newPackPage.postCategoriesSelect);
        await this.waitForLoading();
        await this.assertionValidate(Selectors.subscription.newPackPage.postCategoriesDropdown);
        await this.validateAndClick(Selectors.subscription.newPackPage.selectCategory);
    }

    async selectPostViewCategories(packData: typeof SubscriptionPacks.freeBasicPack){
        // Set Number of Featured Items
        await this.validateAndClick(Selectors.subscription.newPackPage.postViewCategoriesSelect);
        await this.waitForLoading();
        await this.assertionValidate(Selectors.subscription.newPackPage.postViewCategoriesDropdown);
        await this.validateAndClick(Selectors.subscription.newPackPage.selectViewCategory);
    }

    async navigateToSubscriptionPageFE() {
        await this.navigateToURL(this.subscriptionFrontendPage);
    }

    async validateSubscriptionPackTitleFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.frontendPage.subscriptionPageTitle);
        await this.assertionValidate(Selectors.subscription.frontendPage.packTitleFE(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" title is displayed on frontend`);
    }

    async validateSubscriptionPackDescriptionFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.frontendPage.packDescriptionFE(packData.name, packData.description));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" description is displayed on frontend`);
    }

    async validateSubscriptionPackPriceFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.frontendPage.packPriceFE(packData.name, packData.billingAmount.toString()));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" price is displayed on frontend`);
    }

    async validateSubscriptionPackFreeFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.frontendPage.freePackButton(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" free button is displayed on frontend`);
    }

    async clickSubscriptionPackExpandFeaturesFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.frontendPage.expandFeaturesButton(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" expand features button is displayed on frontend`);
    }

    async clickSubscriptionPackShrinkFeaturesFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.frontendPage.lessFeaturesButton(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" shrink features button is displayed on frontend`);
    }

    async clickSubscriptionPackBuyNowFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.frontendPage.buyNowButton(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Subscription pack "${packData.name}" buy now button on frontend`);
    }

    async validateFreeSubscriptionPackPaymentPageFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.paymentPage.paymentPageTitle);
        await this.assertionValidate(Selectors.subscription.paymentPage.freePackActivcateMsg);
        console.log('\x1b[32m%s\x1b[0m', `✅ Free subscription pack "${packData.name}" payment page is displayed on frontend`);
    }

    async navigateToAccountSubscriptionPageFE() {
        await this.navigateToURL(this.accountSubscriptionPage);
        await this.waitForLoading();
    }

    async navigateToPostFormListPage() {
        await this.navigateToURL(this.wpufPostFormPage);
        await this.waitForLoading();
    }

    async navigateToPostSubmissionPage() {
        await this.navigateToURL(this.wpufPostSubmitPage);
        await this.waitForLoading();
    }

    async enableMandatorySubsription(formName: string) {

        let flag = true;

        while (flag == true) {
            // // Go to form edit page
            // await this.navigateToURL(this.wpufPostFormPage);

            // try {
            //     await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
            // } catch (error) {
            //     await this.navigateToURL(this.wpufPostFormPage);
            //     await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
            // }


            // await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
            // await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentSettingsTab);
            await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentOptionsContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.paymentOptionsDropdown);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.payPerPostOption('force_pack_purchase'));

            await this.validateAndClick(Selectors.postFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.postFormSettings.messages.formSaved, Selectors.postFormSettings.saveButton);

        }
    }

    async validateMaxPostsLimit(packData: typeof SubscriptionPacks.paidPack) {
        let limit = packData.maxPosts;
        while (limit <= packData.maxPosts) {
            await this.validateAndClick(Selectors.subscription.newPackPage.featuredItemCheckbox);
            await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, `Test Post title ${limit}`);
            await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
                .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(`Test Post Description ${limit}`);
            await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
            await this.page.waitForTimeout(2000);
            console.log('\x1b[32m%s\x1b[0m', `✅ Post submitted: ${`Test Post title ${limit}`}`);
            limit++;
        }
        if (limit > packData.maxPosts) {
            await this.navigateToPostSubmissionPage();
            await this.checkElementText(Selectors.postFormSettings.wpufInfo, `Your Subscription pack is exhausted. There is a $0.00 charge to add a new post`);
        }
        console.log('\x1b[32m%s\x1b[0m', `✅ Max posts limit validated: ${packData.maxPosts}`);
    }

    async validateShowedMaxLimitFE(packData: typeof SubscriptionPacks.paidPack, featureName: string, count: string) {
        if (count === '0') {
            await expect(this.page.locator(Selectors.subscription.accountPage.showedLimits(packData.name, featureName, count.toString()))).not.toBeVisible();
        console.log('\x1b[32m%s\x1b[0m', `✅ Showed max ${featureName} limit exceeded: ${count}`);
        }else{
            await this.assertionValidate(Selectors.subscription.accountPage.showedLimits(packData.name, featureName, count.toString()));
        console.log('\x1b[32m%s\x1b[0m', `✅ Showed max ${featureName} limit validated: ${count}`);
        }
    }

    async validateFeaturedItemExceeded(){
        await expect(this.page.locator(Selectors.subscription.newPackPage.featuredItemCheckbox)).not.toBeVisible();
        console.log('\x1b[32m%s\x1b[0m', `✅ Featured item exceeded`);
    }

    async validateFreeSubscriptionPackActivationInAccountFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.accountPage.subscriptionMsg);
        await this.assertionValidate(Selectors.subscription.accountPage.detailsCard);
        await this.assertionValidate(Selectors.subscription.accountPage.currentPackName(packData.name));
        await this.assertionValidate(Selectors.subscription.accountPage.freePack);
        console.log('\x1b[32m%s\x1b[0m', `✅ Free Subscription pack "${packData.name}" activation is displayed in account`);
    }

    async validatePaidSubscriptionPackActivationInAccountFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.assertionValidate(Selectors.subscription.accountPage.subscriptionMsg);
        await this.assertionValidate(Selectors.subscription.accountPage.detailsCard);
        await this.assertionValidate(Selectors.subscription.accountPage.currentPackName(packData.name));
        await this.assertionValidate(Selectors.subscription.accountPage.currentPackPrice(packData.billingAmount.toString()));
        console.log('\x1b[32m%s\x1b[0m', `✅ Free Subscription pack "${packData.name}" activation is displayed in account`);
    }

    /**
     * Calculate expected expiration date based on subscription settings
     * @param expirationNumber - Number of time units (e.g., 1, 30)
     * @param expirationPeriod - Time unit (day, week, month, year)
     * @returns Formatted date string (e.g., "February 23, 2026")
     */
    calculateExpirationDate(packData: typeof SubscriptionPacks.paidPack): string {
        const currentDate = new Date();

        switch (packData.expirationPeriod.toLowerCase()) {
            case 'day':
                currentDate.setDate(currentDate.getDate() + packData.expirationNumber);
                break;
            case 'week':
                currentDate.setDate(currentDate.getDate() + (packData.expirationNumber * 7));
                break;
            case 'month':
                currentDate.setMonth(currentDate.getMonth() + packData.expirationNumber);
                break;
            case 'year':
                currentDate.setFullYear(currentDate.getFullYear() + packData.expirationNumber);
                break;
            default:
                throw new Error(`Invalid expiration period: ${packData.expirationPeriod}`);
        }

        // Format date to match WordPress format: "February 23, 2026"
        const options: Intl.DateTimeFormatOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return currentDate.toLocaleDateString('en-US', options);
    }

    /**
     * Validate subscription expiration date in frontend account page
     * @param packData - Subscription pack data containing expiration settings
     */
    async validateSubscriptionExpirationDateFE(packData: typeof SubscriptionPacks.freeBasicPack, expectedExpirationDate: string) {

        // Get actual expiration date from frontend
        const expirationElement = await this.page.locator(Selectors.subscription.accountPage.expirationDate);
        await expirationElement.waitFor({ state: 'visible', timeout: 10000 });

        const actualExpirationText = await expirationElement.textContent();

        // Extract date from "Expire: February 23, 2026" format
        const actualExpirationDate = actualExpirationText?.replace('Expire:', '').trim();

        // Validate expiration date
        expect(actualExpirationDate).toBe(expectedExpirationDate);

        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription expiration date validated successfully`);
        console.log('\x1b[36m%s\x1b[0m', `   Expected: ${expectedExpirationDate}`);
        console.log('\x1b[36m%s\x1b[0m', `   Actual: ${actualExpirationDate}`);
        console.log('\x1b[36m%s\x1b[0m', `   Pack: "${packData.name}" (${packData.expirationNumber} ${packData.expirationPeriod})`);
    }

    async clickExpandPackDetailsInAccountFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.accountPage.showDetailsButton);
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Expand pack details button in account`);
    }

    async clickShrinkPackDetailsInAccountFE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.accountPage.hideDetailsButton);
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Expand pack details button in account`);
    }

    async clickEditFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButton(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.editButton);
        await this.waitForLoading();
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Edit from 3-dot menu`);
    }

    async clickQuickEditFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButton(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.quickEditButton);
        await this.waitForLoading();
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Quick Edit from 3-dot menu`);
    }

    async quickEditPack(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.planNameInput, packData.name);
        await this.waitForLoading();
        console.log('\x1b[32m%s\x1b[0m', `✅ Quick edited pack "${packData.name}"`);
    }

    async clickDraftFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButton(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.darftButton);
        SubscriptionPacks.packCounts.draftPackCount++;
        await this.waitForLoading();
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Draft from 3-dot menu`);
    }

    async clickTrashFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButtonDraft(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.trashButton);
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.listPage.confirmTrashButton);
        SubscriptionPacks.packCounts.trashPackCount++;
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Trash from 3-dot menu`);
    }

    async clickPublishedFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButton(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.publishButton);
        await this.waitForLoading();
        SubscriptionPacks.packCounts.publishedPackCount++;
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Publish from 3-dot menu`);
    }

    async clickRestoreFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButtonTrash(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.restoreButton);
        SubscriptionPacks.packCounts.trashPackCount--;
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Restore from 3-dot menu`);
    }

    async clickDeletePermanentlyFrom3DotMenu(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.validateAndClick(Selectors.subscription.listPage.threeDotButtonTrash(packData.name));
        await this.validateAndClick(Selectors.subscription.listPage.deletePermanentlyButton);
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.listPage.confirmDeleteButton);
        SubscriptionPacks.packCounts.trashPackCount--;
        console.log('\x1b[32m%s\x1b[0m', `✅ Clicked on Delete Permanently from 3-dot menu`);
    }

    async validatePackTrashedBE(packData: typeof SubscriptionPacks.freeBasicPack) {
        await this.waitForLoading();
        await this.assertionValidate(Selectors.subscription.newPackPage.validatePackTrashed(packData.name));
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack "${packData.name}" is trashed`);
    }

    async validatePackNotExistsFromFE() {
        await this.assertionValidate(Selectors.subscription.accountPage.packNotExistsMsg);
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription pack is unsubscribed from FE`);
    }

    async editPreferances(buttonColor: string) {
        await this.validateAndClick(Selectors.subscription.newPackPage.packPreferences);
        await this.waitForLoading();
        await this.validateAndFillStrings(Selectors.subscription.newPackPage.inputColor, buttonColor);
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.newPackPage.savePreferencesButton);
        await this.waitForLoading();
        console.log('\x1b[32m%s\x1b[0m', `✅ Button color set to ${buttonColor}`);
    }

    async validateBuyNowButtonColorFE(packName: string, buttonColor: string) {
        await this.assertionValidate(Selectors.subscription.newPackPage.buyNowButtonColorFE(packName, buttonColor));
        console.log('\x1b[32m%s\x1b[0m', `✅ Buy now button color is ${buttonColor}`);
    }

    async validateOneTimePaymentBE(packData: typeof SubscriptionPacks.paidPack) {
        await this.assertionValidate(Selectors.subscription.frontendPage.oneTimePayment(packData.name, packData.billingAmount.toString()));
        console.log('\x1b[32m%s\x1b[0m', `✅ Price & one time payment is displayed in paid pack`);
    }

    /**
     * Complete Payment with Bank Transfer
     */
    async completeBankPayment(cost: string, successPage: string) {
        // Select Bank Payment option
        await this.checkElementText(Selectors.postFormSettings.validatePayPerPostCost, `$${cost}`);
        await this.validateAndClick(Selectors.subscription.paymentPage.bankPaymentOption);
        await this.validateAndClick(Selectors.postFormSettings.proceedPaymentButton);
        console.log('\x1b[32m%s\x1b[0m', `✅ Bank payment submitted successfully`);
        await this.assertionValidate(Selectors.postFormSettings.afterPaymentPageTitle(successPage));
    }

    async completeCardPayment(cost: string, successPage: string) {
        // Select Bank Payment option
        await this.checkElementText(Selectors.postFormSettings.validatePayPerPostCost, `$${cost}`);
        await this.validateAndClick(Selectors.subscription.paymentPage.stripePaymentOption);
        
        await this.validateAndClick(Selectors.postFormSettings.proceedPaymentButton);
        console.log('\x1b[32m%s\x1b[0m', `✅ Bank payment submitted successfully`);
        await this.assertionValidate(Selectors.postFormSettings.afterPaymentPageTitle(successPage));
    }

    /**
     * Validate No Subscription Message
     */
    async validateNoSubscriptionFE() {
        // Verify no subscription message
        await this.assertionValidate(Selectors.subscription.accountPage.noSubscriptionPara);
        console.log('\x1b[32m%s\x1b[0m', `✅ No subscription message displayed correctly`);
    }

    /**
     * Cancel User Subscription
     */
    async cancelSubscription() {
        // Click Cancel button
        await this.validateAndClick(Selectors.subscription.accountPage.cancelButton);
        await this.waitForLoading();
        await this.validateAndClick(Selectors.subscription.accountPage.confirmModal);
        await this.waitForLoading();
        await expect(this.page.locator(Selectors.subscription.accountPage.cancelButton)).not.toBeVisible();
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription cancelled successfully`);
    }

    async validateSubscriptionCanceled() {
        await this.assertionValidate(Selectors.subscription.accountPage.noSubscriptionPara);
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription canceled successfully`);
    }
    /**
     * Get Remaining Post Count from Account
     * @param postType - Type of post (e.g., 'post', 'page')
     * @returns Number of remaining posts
     */
    async getRemainingPostCount(postType: string = 'post'): Promise<string> {
        await this.navigateToURL(this.accountSubscriptionPage);
        await this.waitForLoading();

        // Click on Subscription tab
        await this.validateAndClick(Selectors.subscription.accountPage.detailsCard);
        await this.waitForLoading();

        // Get remaining count
        const countElement = this.page.locator(Selectors.subscription.accountPage.remainingPosts(postType));
        const count = await countElement.innerText();

        console.log('\x1b[34m%s\x1b[0m', `Remaining ${postType} count: ${count}`);
        return count;
    }
}

