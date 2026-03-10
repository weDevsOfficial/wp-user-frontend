import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { FieldAddPage } from '../pages/fieldAdd';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie
import { BasicLogoutPage } from '../pages/basicLogout';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll(async () => {
    // Launch browser
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();
});


test.describe('Post-Forms', () => {
    // Configure fail-fast behavior for this spec file
    configureSpecFailFast();
    
    /**----------------------------------POSTFORM----------------------------------**
     *
     * @TestScenario : [Post-Forms]
     * @Test_PF0001 : Admin is creating Blank Form with all Fields
     * @Test_PF0002 : PF0002 : Admin is creating page with shortcode for Post Form
     * @Test_PF0003 : User is Creating Post from Frontend
     * @Test_PF0004 : User is Validating Post created
     * @Test_PF0005 : User is validating Entered Data for Created Post
     * @Test_PF0006 : Admin is creating a Preset Post Form
     * @Test_PF0007 : Admin is creating a Preset Post Form - with Guest Enabled
     * @Test_PF0008 : Admin is creating page with shortcode
     * @Test_PF0009 : Guest is creating post from frontend
     * @Test_PF0010 : Guest is validating post created
     * @Test_PF0011 : Admin is setting necessary setup for product form
     * @Test_PF0012 : Admin is creating a product Post Form
     * @Test_PF0013 : Admin is creating product page page with shortcode
     * @Test_PF0014 : Admin is creating product from FE
     * @Test_PF0015 : Admin is validating product created
     * @Test_PF0016 : Admin is validating entered product data
     * @Test_PF0017 : Admin is validating entered product data BE
     * @Test_PF0018 : Admin is setting necessary setup for downloads form
     * @Test_PF0019 : Admin is creating a downloads Post Form
     * @Test_PF0020 : Admin is creating downloads page with shortcode
     * @Test_PF0021 : Admin is creating downloads from FE
     * @Test_PF0022 : Admin is validating downloads created
     * @Test_PF0023 : Admin is validating entered downloads data
     * @Test_PF0024 : Admin is validating entered downloads data BE
     *
     */

    let pfShortCode: string;
    let productShortCode: string;
    let downloadsShortCode: string;

    test('PF0001 : Admin is creating a Blank Post Form with all Fields', { tag: ['@Lite'] }, async () => {
        await page.waitForTimeout(15000);
        await new BasicLoginPage(page).basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        const PostFormClass = new PostFormPage(page);
        const FieldAdd = new FieldAddPage(page);

        PostForm.formName = 'PostForm';
        //Post Blank Form
        await PostFormClass.createBlankFormPostForm(PostForm.formName);
        //PostFields + Validate
        await FieldAdd.addPostFields_PF();
        await FieldAdd.validatePostFields_PF();
        //Taxonomies + Validate
        await FieldAdd.addTaxonomies_PF();
        await FieldAdd.validateTaxonomies_PF();
        //CustomFields + Validate
        await FieldAdd.addCustomFields_Common();
        await FieldAdd.validateCustomFields_Common();
        //Others + Validate
        await FieldAdd.addOthers_Common();
        await FieldAdd.validateOthers_Common();
        //Save
        await FieldAdd.saveForm_Common();
        //Validate
        pfShortCode = await FieldAdd.validatePostFormCreated(PostForm.formName);
        console.log('PF Short Code: ' + pfShortCode);
    });

    test('PF0002 : Admin is creating page with shortcode for Post Form', { tag: ['@Lite'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await PostFormClass.createPageWithShortcodeGeneral(pfShortCode, 'Post Here');

        await new BasicLogoutPage(page).logOut();
    });


    test('PF0003 : User is Creating Post from Frontend', { tag: ['@Lite'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await new BasicLoginPage(page).basicLogin(Users.userEmail, Users.userPassword);

        //Complete Post from Frontend
        PostForm.title = faker.word.words(3);
        await PostFormClass.createPostFE();
    });

    test('PF0004 : User is Validating Post created', { tag: ['@Lite'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await PostFormClass.validatePostCreated();

    });

    test('PF0005 : User is validating Entered Data for Created Post', { tag: ['@Lite'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await PostFormClass.validateEnteredData();
    });


    test('PF0006 : Admin is creating a Preset Post Form', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);
        const FieldAdd = new FieldAddPage(page);

        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);

        //Post Preset Form
        await PostForm.createPresetPostForm('PF Preset');
        //Validate
        await FieldAdd.validatePostFields_PF();
        await FieldAdd.validateTaxonomiesPreset_PF();

        //Save
        await FieldAdd.saveForm_Common();
        //Validate
        await FieldAdd.validatePostFormCreated('PF Preset');
    });


    test('PF0007 : Admin is creating a Preset Post Form - with Guest Enabled', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);
        const FieldAdd = new FieldAddPage(page);

        //For Front-End
        //Create Post Form
        const postFormPresetFrontendTitle = 'Guest PostForm';
        //Post Preset Form
        await PostForm.createPresetPostFormWithGuestEnabled(postFormPresetFrontendTitle);
        //Validate
        await FieldAdd.validatePostFields_PF();
        await FieldAdd.validateTaxonomiesPreset_PF();

        //Save
        await FieldAdd.saveForm_Common();
        //Validate
        pfShortCode = await FieldAdd.validatePostFormCreated(postFormPresetFrontendTitle);
        console.log('PF Short Code: ' + pfShortCode);
    });

    test('PF0008 : Admin is creating page with shortcode ', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcodeGeneral(pfShortCode, 'GuestPostForm');

        await new BasicLogoutPage(page).logOut();
    });

    test('PF0009 : Guest is creating post from frontend', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createGuestPostFE();
    });

    test('PF0010 : Guest is validating post created', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateGuestPostCreated();
    });

    test('PF0011 : Admin is setting necessary setup for product form', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);

        await PostForm.setupForWooProduct();
    });

    test('PF0012 : Admin is creating a product Post Form', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);
        const FieldAdd = new FieldAddPage(page);

        //Post Preset Form
        await PostForm.createProductPostForm();
        // Add
        await FieldAdd.addProductTaxoFields_PF();
        //Validate
        await FieldAdd.validateProductPostFields_PF();

        //Save
        await FieldAdd.saveForm_Common();

        await page.waitForTimeout(5000);
        
        //Validate
        productShortCode = await FieldAdd.validatePostFormCreated('WooCommerce Product');
        console.log('Product Short Code: ' + productShortCode);

    });

    test('PF0013 : Admin is creating product page with shortcode ', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcodeGeneral(productShortCode, 'Add Product');
    });

    test('PF0014 : Admin is creating product from FE ', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createProductFE();
    });

    test('PF0015 : Admin is validating product created', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateProductCreated();
    });

    test('PF0016 : Admin is validating entered product data', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredProductData();
    });

    test('PF0017 : Admin is validating entered product data from BE', { tag: ['@Lite'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredProductDataBE();
    });

    test('PF0018 : Admin is setting necessary setup for downloads form', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.setupForEDDProduct();
    });

    test('PF0019 : Admin is creating a downloads Post Form', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);
        const FieldAdd = new FieldAddPage(page);

        //Post Preset Form
        await PostForm.createDownloadsPostForm();
        // Add
        await FieldAdd.addDownloadsTaxoFields_PF();
        //Validate
        await FieldAdd.validateDownloadsPostFields_PF();

        //Save
        await FieldAdd.saveForm_Common();
        
        await page.waitForTimeout(5000);
        //Validate
        downloadsShortCode = await FieldAdd.validatePostFormCreated('EDD Download');
        console.log('Downloads Short Code: ' + downloadsShortCode);

    });

    test('PF0020 : Admin is creating add downloads page with shortcode ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcodeGeneral(downloadsShortCode, 'Add Downloads');
    });

    test('PF0021 : Admin is creating downloads from FE ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createDownloadsFE();
    });

    test('PF0022 : Admin is validating downloads created', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateDownloadsCreated();
    });

    test('PF0023 : Admin is validating entered downloads data', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredDownloadsData();
    });

    test('PF0024 : Admin is validating entered downloads data BE', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredDownloadsDataBE();
    });

});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});