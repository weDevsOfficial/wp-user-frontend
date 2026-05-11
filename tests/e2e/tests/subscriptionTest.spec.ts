import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { BasicLogoutPage } from '../pages/basicLogout';
import { SubscriptionPage } from '../pages/subscription';
import { Users, SubscriptionPacks } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';
import { subscribe } from "diagnostics_channel";
import { PostFormSettingsPage } from "../pages/postFormSettings";
import { SettingsSetupPage } from "../pages/settingsSetup";

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll(async () => {
    // Launch browser in headed mode for better debugging
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();
});

test.describe('Subscription-Module', () => {
    // Configure fail-fast behavior for this spec file
    configureSpecFailFast();

    /**----------------------------------SUBSCRIPTION PACK TESTS----------------------------------**
     * 
     * @TestScenario : [Subscription-Packs]
     * 
     * @Test_SB0001 : Admin creates a Free Subscription Pack
     * @Test_SB0002 : Admin validates Free Pack in subscription builder
     * @Test_SB0003 : Admin validates All packs count
     * @Test_SB0004 : Admin validates Published packs count
     * @Test_SB0005 : Admin validates free Subscription pack details in FE
     * @Test_SB0006 : Admin expands & shrink Subscription pack card in FE
     * @Test_SB0007 : Admin buy free Subscription pack in FE
     * @Test_SB0008 : Admin validates free Subscription pack activation in FE
     * @Test_SB0009 : Admin expand & shrink pack details in FE
     * @Test_SB0010 : Admin validates subscribers count
     * @Test_SB0011 : Admin edit and validates subscription pack in BE
     * @Test_SB0012 : Admin validates edited subscription pack in FE
     * @Test_SB0013 : Admin validates edited subscription pack details in FE
     * @Test_SB0014 : Admin set buy now button color
     * @Test_SB0015 : Admin validate buy now button color in FE
     * @Test_SB0016 : Admin draft the pack from edit page
     * @Test_SB0017 : Admin publish the pack from menu
     * @Test_SB0018 : Admin quick edit the pack from BE
     * @Test_SB0019 : Admin draft the pack from menu
     * @Test_SB0020 : Admin validates all packs counts again
     * @Test_SB0021 : Admin trash the pack from menu
     * @Test_SB0022 : Admin validates pack trashed
     * @Test_SB0023 : Admin restored pack to draft
     * @Test_SB0024 : Admin deletes the pack permanently
     * @Test_SB0025 : Admin validates pack not exists from FE
     * @Test_SB0026 : Admin creates a Paid Subscription Pack
     * @Test_SB0027 : Admin validates Paid Pack in subscription builder
     * @Test_SB0028 : Admin validates one time payment in paid pack
     * @Test_SB0029 : Admin completes bank payment
     * @Test_SB0030 : Admin validates not subscribed without payment accepted
     * @Test_SB0031 : Admin excepts transaction
     * @Test_SB0032 : Admin validates subscription after bank payment
     * @Test_SB0033 : Admin validates subscription expiration date (day) for paid pack
     * @Test_SB0034 : Admin validates showed max posts limit
     * @Test_SB0035 : Admin validates showed max pages limit
     * @Test_SB0036 : Admin validates showed max user requests limit
     * @Test_SB0037 : Admin setup post type to page & mandatory subscription
     * @Test_SB0038 : Admin validates max posts limit for paid pack
     * @Test_SB0039 : Admin validates featured item exceeded
     * @Test_SB0040 : Admin validates decreased max posts limit
     * @Test_SB0041 : Admin validates decreased max pages limit
     * @Test_SB0042 : Admin cancels subscription
     * @Test_SB0043 : Admin validates subscription canceled
     * @Test_SB0044 : Admin creates a Recurring Paid Subscription Pack
     * 
     */

    /*************************************************/
    /******* SCENARIO GROUP 1: Pack Creation *********/
    /*************************************************/

    test('SB0001 : Admin creates a Free Subscription Pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        await page.waitForTimeout(30000);
        const BasicLogin = new BasicLoginPage(page);
        await test.step("Login as an admin for accessing dashboard", async () => {
            await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
        })
        // Create Free Subscription Pack
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Initialize Free Subscription Pack", async () => {
            await SubscriptionPg.createSubscriptionPack(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Fill Subscription Details", async () => {
            await SubscriptionPg.navigateToSubscriptionDetailsSection();
            await SubscriptionPg.fillSubscriptionOverviewDetails(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Fill Access and Visibility Details", async () => {
            await SubscriptionPg.fillAccessAndVisibilityDetails(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Fill Post Expiration Details", async () => {
            await SubscriptionPg.fillPostExpirationDetails(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Fill Non Recurring Payment Details", async () => {
            await SubscriptionPg.navigateToPaymentSettingsSection();
            await SubscriptionPg.fillNonRecurringPaymentDetails(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Publish Pack", async () => {
            await SubscriptionPg.publishPack();
            SubscriptionPacks.packCounts.allPackCount++;
        })
    });

    test('SB0002 : Admin validates Free Pack in subscription builder', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate pack free backend", async() => {
            await SubscriptionPg.validatePackFreeBE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0003 : Admin validates All packs count', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate All packs count", async () => {
            await SubscriptionPg.validateAllPackCountBE();
        })
    });

    test('SB0004 : Admin validates Published packs count', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate Published packs count", async () => {
            await SubscriptionPg.validatePublishedPackCountBE();
        })
    });

    test('SB0005 : Admin validates free Subscription pack details in FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Validate Free Subscription pack Title", async () => {
            await SubscriptionPg.validateSubscriptionPackTitleFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Free Subscription pack Description", async () => {
            await SubscriptionPg.validateSubscriptionPackDescriptionFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Free Subscription pack Free", async () => {
            await SubscriptionPg.validateSubscriptionPackFreeFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0006 : Admin expands & shrink Subscription pack card in FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Click on Subscription pack Expand Features Button", async () => {
            await SubscriptionPg.clickSubscriptionPackExpandFeaturesFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Click on Subscription pack See less Button", async () => {
            await SubscriptionPg.clickSubscriptionPackShrinkFeaturesFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0007 : Admin buy free Subscription pack in FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Click on buy now button", async () => {
            await SubscriptionPg.clickSubscriptionPackBuyNowFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate free Subscription pack activation", async () => {
            await SubscriptionPg.validateFreeSubscriptionPackPaymentPageFE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.subscribersCount++;
        })
    });

    test('SB0008 : Admin validates free Subscription pack activation in FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Subscription pack activation in Account", async () => {
            await SubscriptionPg.validateFreeSubscriptionPackActivationInAccountFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0009 : Admin expand & shrink pack details in FE ', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Expand Subscription pack details in Account", async () => {
            await SubscriptionPg.clickExpandPackDetailsInAccountFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Shrink Subscription pack details in Account", async () => {
            await SubscriptionPg.clickShrinkPackDetailsInAccountFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0010 : Admin validates subscribers count', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Validate subscribers count", async () => {
            await SubscriptionPg.validateSubscribersCountBE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0011 : Admin edit and validates subscription pack in BE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on edit from 3-dot menu", async () => {
            await SubscriptionPg.clickEditFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        SubscriptionPacks.freeBasicPack.name = "Free Basic Pack Edited";
        SubscriptionPacks.freeBasicPack.description = "A free subscription pack for basic users with limited posting capabilities Edited";
        await test.step("Fill Subscription Details", async () => {
            await SubscriptionPg.fillSubscriptionOverviewDetails(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Save The edited Pack", async () => {
            await SubscriptionPg.updatePack();
        })
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0012 : Admin validates edited subscription pack in FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Subscription pack activation in Account", async () => {
            await SubscriptionPg.validateFreeSubscriptionPackActivationInAccountFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0013 : Admin validates edited subscription pack details in FE ', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Validate Free Subscription pack Title", async () => {
            await SubscriptionPg.validateSubscriptionPackTitleFE(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Free Subscription pack Description", async () => {
            await SubscriptionPg.validateSubscriptionPackDescriptionFE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0014 : Admin set buy now button color ', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Edit preferences", async () => {
            await SubscriptionPg.editPreferances(SubscriptionPacks.buttonColor);
        })
    });

    test('SB0015 : Admin validate buy now button color in FE ', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Validate buy now button color", async () => {
            await SubscriptionPg.validateBuyNowButtonColorFE(SubscriptionPacks.freeBasicPack.name, SubscriptionPacks.buttonColor);
        })
    });

    test('SB0016 : Admin draft the pack from edit page', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on edit from 3-dot menu", async () => {
            await SubscriptionPg.clickEditFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Draft the edited Pack", async () => {
            await SubscriptionPg.draftPack();
            SubscriptionPacks.packCounts.publishedPackCount--;
        })
        await test.step("Validate Pack Drafted Backend", async () => {
            await SubscriptionPg.validatePackDraftedBE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0017 : Admin publish the pack from menu', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on publish from 3-dot menu", async () => {
            await SubscriptionPg.clickPublishedFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.draftPackCount--;
        })
    });

    test('SB0018 : Admin quick edit the pack from BE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on quick edit from 3-dot menu", async () => {
            await SubscriptionPg.clickQuickEditFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        SubscriptionPacks.freeBasicPack.name = "Free Basic Pack";
        await test.step("Quick edit the pack", async () => {
            await SubscriptionPg.quickEditPack(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Update the pack", async () => {
            await SubscriptionPg.updatePack();
        })
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0019 : Admin draft the pack from menu', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on draft from 3-dot menu", async () => {
            await SubscriptionPg.clickDraftFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Pack Drafted Backend", async () => {
            await SubscriptionPg.validatePackDraftedBE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.publishedPackCount--;
        })
    });
    test('SB0020 : Admin validates all packs counts again', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Count all packs counts again", async () => {
            await SubscriptionPg.validateAllPackCountBE();
            await SubscriptionPg.validateDraftPackCountBE();
        })
    });

    test('SB0021 : Admin trash the pack from menu', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on trash from 3-dot menu", async () => {
            await SubscriptionPg.clickTrashFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate trash pack count", async () => {
            await SubscriptionPg.validateTrashPackCountBE();
        })
    });

    test('SB0022 : Admin validates pack trashed', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Validate pack trashed", async () => {
            await SubscriptionPg.navigateToTrashTab();
            await SubscriptionPg.validatePackTrashedBE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.draftPackCount--;
            SubscriptionPacks.packCounts.allPackCount--;
        })
    });

    test('SB0023 : Admin restored pack to draft', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on restore from 3-dot menu", async () => {
            await SubscriptionPg.navigateToTrashTab();
            await SubscriptionPg.clickRestoreFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Pack Restored to draft", async () => {
            await SubscriptionPg.navigateToDraftTab();
            await SubscriptionPg.validatePackDraftedBE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.draftPackCount++;
            SubscriptionPacks.packCounts.allPackCount++;
        })
    });

    test('SB0024 : Admin deletes the pack permanently', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Click on trash from 3-dot menu", async () => {
            await SubscriptionPg.clickTrashFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
        await test.step("Validate Pack Trashed Backend", async () => {
            await SubscriptionPg.navigateToTrashTab();
            await SubscriptionPg.validatePackTrashedBE(SubscriptionPacks.freeBasicPack);
            SubscriptionPacks.packCounts.allPackCount--;
            SubscriptionPacks.packCounts.draftPackCount--;
        })
        await test.step("Click on delete permanently from 3-dot menu", async () => {
            await SubscriptionPg.clickDeletePermanentlyFrom3DotMenu(SubscriptionPacks.freeBasicPack);
        })
    });

    test('SB0025 : Admin validates pack not exists from FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate pack unsubscribed from FE", async () => {
            await SubscriptionPg.validatePackNotExistsFromFE();
            SubscriptionPacks.packCounts.subscribersCount--;
        })
        console.log(`all ${SubscriptionPacks.packCounts.allPackCount}`);
        console.log(`published ${SubscriptionPacks.packCounts.publishedPackCount}`);
        console.log(`draft ${SubscriptionPacks.packCounts.draftPackCount}`);
        console.log(`trash ${SubscriptionPacks.packCounts.trashPackCount}`);
        console.log(`subscribers ${SubscriptionPacks.packCounts.subscribersCount}`);
    });

    test('SB0026 : Admin creates a Paid Subscription Pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        // Create Free Subscription Pack
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Initialize Paid Subscription Pack", async () => {
            await SubscriptionPg.createSubscriptionPack(SubscriptionPacks.paidPack);
        })
        await test.step("Fill Subscription Details", async () => {
            await SubscriptionPg.navigateToSubscriptionDetailsSection();
            await SubscriptionPg.fillSubscriptionOverviewDetails(SubscriptionPacks.paidPack);
        })
        await test.step("Fill Access and Visibility Details", async () => {
            await SubscriptionPg.fillAccessAndVisibilityDetails(SubscriptionPacks.paidPack);
        })
        await test.step("Fill Non Recurring Payment Details", async () => {
            await SubscriptionPg.navigateToPaymentSettingsSection();
            await SubscriptionPg.fillNonRecurringPaymentDetails(SubscriptionPacks.paidPack);
        })
        await test.step("Navigate to Advanced Configuration Section", async () => {
            await SubscriptionPg.navigateToAdvancedSection();
        })
        await test.step("Fill Post Numbers", async () => {
            await SubscriptionPg.fillPostNumbers(SubscriptionPacks.paidPack);
        })
        await test.step("Fill Page Numbers", async () => {
            await SubscriptionPg.fillPageNumbers(SubscriptionPacks.paidPack);
        })
        await test.step("Fill User Requests", async () => {
            await SubscriptionPg.fillUserRequests(SubscriptionPacks.paidPack);
        })
        await test.step("Click on additional options section", async () => {
            await SubscriptionPg.clickAdditionalOptions();
        })
        await test.step("Fill Featured Items", async () => {
            await SubscriptionPg.fillFeaturedItems(SubscriptionPacks.paidPack);
        })
        await test.step("Publish Pack", async () => {
            await SubscriptionPg.publishPack();
            SubscriptionPacks.packCounts.allPackCount++;
        })
    });

    test('SB0027 : Admin validates Paid Pack in subscription builder', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.paidPack);
        })
        await test.step("Validate pack paid backend", async() => {
            await SubscriptionPg.validatePackPaidBE(SubscriptionPacks.paidPack);
        })
    });

    test('SB0028 : Admin validates one time payment in paid pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Validate one time payment", async() => {
            await SubscriptionPg.validateOneTimePaymentBE(SubscriptionPacks.paidPack);
        })
    });

    test('SB0029 : Admin completes bank payment', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Click on buy now button", async () => {
            await SubscriptionPg.clickSubscriptionPackBuyNowFE(SubscriptionPacks.paidPack);
        })
        await test.step("Complete bank payment", async () => {
            await SubscriptionPg.completeBankPayment(SubscriptionPacks.paidPack.billingAmount.toString(), 'Order Received');
        })
    });

    test('SB0030 : Admin validates not subscribed without payment accepted', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate no subscription", async() => {
            await SubscriptionPg.validatePackNotExistsFromFE();
        })
    });

    test('SB0031 : Admin excepts transaction', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new PostFormSettingsPage(page);
        await test.step("Validate bank payment in FE", async() => {
            await SubscriptionPg.acceptPayment();
        })
    });

    test('SB0032 : Admin validates subscription after bank payment', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Subscription pack activation in Account", async () => {
            await SubscriptionPg.validatePaidSubscriptionPackActivationInAccountFE(SubscriptionPacks.paidPack);
        })
    });
    test('SB0033 : Admin validates subscription expiration date (day) for paid pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        let expectedExpirationDate: string;
        
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Calculate Expiration Date", async () => {
            expectedExpirationDate = SubscriptionPg.calculateExpirationDate(SubscriptionPacks.paidPack);
            console.log('\x1b[36m%s\x1b[0m', `📅 Expected Expiration Date: ${expectedExpirationDate}`);
        })
        await test.step("Validate Paid Pack Expiration Date (1 day)", async () => {
            await SubscriptionPg.validateSubscriptionExpirationDateFE(SubscriptionPacks.paidPack, expectedExpirationDate);
        })
    });

    test('SB0034 : Admin validates showed max posts limit', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Max Posts Limit", async () => {
            await SubscriptionPg.validateShowedMaxLimitFE(SubscriptionPacks.paidPack, 'Posts', SubscriptionPacks.paidPack.maxPosts === -1 ? 'Unlimited' : SubscriptionPacks.paidPack.maxPosts.toString());
        })
    });

    test('SB0035 : Admin validates showed max pages limit', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Max Pages Limit", async () => {
            await SubscriptionPg.validateShowedMaxLimitFE(SubscriptionPacks.paidPack, 'Pages', SubscriptionPacks.paidPack.maxPages === -1 ? 'Unlimited' : SubscriptionPacks.paidPack.maxPages.toString());
        })
    });

    test('SB0036 : Admin validates showed max user requests limit', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Max User Requests Limit", async () => {
            await SubscriptionPg.validateShowedMaxLimitFE(SubscriptionPacks.paidPack, 'User Requests', SubscriptionPacks.paidPack.maxPosts === -1 ? 'Unlimited' : SubscriptionPacks.paidPack.maxPosts.toString());
        })
    });

    test('SB0037 : Admin setup post type to page & mandatory subscription', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        const PostFormSettingsPg = new PostFormSettingsPage(page);
        // Change post type to 'page'
        await test.step("Setup post type to page", async () => {
            await PostFormSettingsPg.changePostType('page', 'Sample Form');
        })
        // Enable mandatory subscription
        await test.step("Enabling mandatory subscription", async () => {
            await SubscriptionPg.enableMandatorySubsription('Sample Form');
        })
    });

    test('SB0038 : Admin validates max posts limit for paid pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToPostSubmissionPage();
        })
        await test.step("Validate Max Posts Limit", async () => {
            await SubscriptionPg.validateMaxPostsLimit(SubscriptionPacks.paidPack);
        })
    });

    test('SB0039 : Admin validates featured item exceeded', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate Featured Item Exceeded", async () => {
            await SubscriptionPg.validateFeaturedItemExceeded();
        })
    });

    test('SB0040 : Admin validates decreased max posts limit', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Max Posts Limit", async () => {
            await SubscriptionPg.validateShowedMaxLimitFE(SubscriptionPacks.paidPack, 'Posts', (SubscriptionPacks.paidPack.maxPosts-1) === -1 ? 'Unlimited' : (SubscriptionPacks.paidPack.maxPosts-1).toString());
        })
    });

    test('SB0041 : Admin validates decreased max pages limit', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Max Pages Limit", async () => {
            await SubscriptionPg.validateShowedMaxLimitFE(SubscriptionPacks.paidPack, 'Pages', (SubscriptionPacks.paidPack.maxPages-1) === -1 ? 'Unlimited' : (SubscriptionPacks.paidPack.maxPages-1).toString());
        })
    });

    test('SB0042 : Admin cancels subscription', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Cancel Subscription", async () => {
            await SubscriptionPg.cancelSubscription();
        })
    });

    test('SB0043 : Admin validates subscription canceled', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Account Subscription Page", async () => {
            await SubscriptionPg.navigateToAccountSubscriptionPageFE();
        })
        await test.step("Validate Subscription Canceled", async () => {
            await SubscriptionPg.validateSubscriptionCanceled();
        })
    });

    test('SB0044 : Admin creates a Recurring Paid Subscription Pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        // Create Free Subscription Pack
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPage();
        })
        await test.step("Initialize Paid Subscription Pack", async () => {
            await SubscriptionPg.createSubscriptionPack(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Fill Subscription Details", async () => {
            await SubscriptionPg.navigateToSubscriptionDetailsSection();
            await SubscriptionPg.fillSubscriptionOverviewDetails(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Fill Access and Visibility Details", async () => {
            await SubscriptionPg.fillAccessAndVisibilityDetails(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Fill Non Recurring Payment Details", async () => {
            await SubscriptionPg.navigateToPaymentSettingsSection();
            await SubscriptionPg.fillRecurringPaymentWithoutTrial(SubscriptionPacks.recurringPaidPack, '5');
        })
        await test.step("Navigate to Advanced Configuration Section", async () => {
            await SubscriptionPg.navigateToAdvancedSection();
        })
        await test.step("Click on post categories section", async () => {
            await SubscriptionPg.clickPostCategories();
        })
        await test.step("Select Post Categories", async () => {
            await SubscriptionPg.selectPostCategories(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Click on post view categories section", async () => {
            await SubscriptionPg.clickPostViewCategories();
        })
        await test.step("Select Post View Categories", async () => {
            await SubscriptionPg.selectPostViewCategories(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Publish Pack", async () => {
            await SubscriptionPg.publishPack();
            SubscriptionPacks.packCounts.allPackCount++;
        })
    });

    test('SB0045 : Admin validates Recurring Paid Subscription Pack', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Validate Pack Published Backend", async () => {
            await SubscriptionPg.validatePackPublishedBE(SubscriptionPacks.recurringPaidPack);
        })
        await test.step("Validate pack paid backend", async() => {
            await SubscriptionPg.validateRecurringPackPaidBE(SubscriptionPacks.recurringPaidPack);
        })
    });

    test('SB0046 : Admin validates Recurring Paid Subscription Pack FE', { tag: ['@Pro', '@Subscription'] }, async () => {
        const SubscriptionPg = new SubscriptionPage(page);
        await test.step("Navigate to Subscription Page", async () => {
            await SubscriptionPg.navigateToSubscriptionPageFE();
        })
        await test.step("Validate recurring pack FE", async() => {
            await SubscriptionPg.validateRecurringPackPaidFE(SubscriptionPacks.recurringPaidPack);
        })
    });

    // test('SB0029 : Admin completes card payment', { tag: ['@Pro', '@Subscription'] }, async () => {
    //     const SubscriptionPg = new SubscriptionPage(page);
    //     await test.step("Navigate to Subscription Page", async () => {
    //         await SubscriptionPg.navigateToSubscriptionPageFE();
    //     })
    //     await test.step("Click on buy now button", async () => {
    //         await SubscriptionPg.clickSubscriptionPackBuyNowFE(SubscriptionPacks.paidPack);
    //     })
    //     await test.step("Complete bank payment", async () => {
    //         await SubscriptionPg.completeBankPayment(SubscriptionPacks.paidPack.billingAmount.toString(), 'Order Received');
    //     })
    // });

    // test('SB0030 : Admin validates not subscribed without payment accepted', { tag: ['@Pro', '@Subscription'] }, async () => {
    //     const SubscriptionPg = new SubscriptionPage(page);
    //     await test.step("Navigate to Subscription Page", async () => {
    //         await SubscriptionPg.navigateToAccountSubscriptionPageFE();
    //     })
    //     await test.step("Validate no subscription", async() => {
    //         await SubscriptionPg.validatePackNotExistsFromFE();
    //     })
    // });

    // test('SB0031 : Admin excepts transaction', { tag: ['@Pro', '@Subscription'] }, async () => {
    //     const SubscriptionPg = new PostFormSettingsPage(page);
    //     await test.step("Validate bank payment in FE", async() => {
    //         await SubscriptionPg.acceptPayment();
    //     })
    // });

    // test('SB0032 : Admin validates subscription after bank payment', { tag: ['@Pro', '@Subscription'] }, async () => {
    //     const SubscriptionPg = new SubscriptionPage(page);
    //     await test.step("Navigate to Subscription Page", async () => {
    //         await SubscriptionPg.navigateToAccountSubscriptionPageFE();
    //     })
    //     await test.step("Validate Subscription pack activation in Account", async () => {
    //         await SubscriptionPg.validatePaidSubscriptionPackActivationInAccountFE(SubscriptionPacks.paidPack);
    //     })
    // });
    // test('SB0033 : Admin validates subscription expiration date (day) for paid pack', { tag: ['@Pro', '@Subscription'] }, async () => {
    //     const SubscriptionPg = new SubscriptionPage(page);
    //     let expectedExpirationDate: string;
        
    //     await test.step("Navigate to Account Subscription Page", async () => {
    //         await SubscriptionPg.navigateToAccountSubscriptionPageFE();
    //     })
    //     await test.step("Calculate Expiration Date", async () => {
    //         expectedExpirationDate = SubscriptionPg.calculateExpirationDate(SubscriptionPacks.paidPack);
    //         console.log('\x1b[36m%s\x1b[0m', `📅 Expected Expiration Date: ${expectedExpirationDate}`);
    //     })
    //     await test.step("Validate Paid Pack Expiration Date (1 day)", async () => {
    //         await SubscriptionPg.validateSubscriptionExpirationDateFE(SubscriptionPacks.paidPack, expectedExpirationDate);
    //     })
    // });






});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});

