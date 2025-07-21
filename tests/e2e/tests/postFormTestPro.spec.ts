import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormProPage } from '../pages/postFormPro';
import { FieldAddPage } from '../pages/fieldAdd';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie
import { BasicLogoutPage } from '../pages/basicLogout';
import { faker } from '@faker-js/faker';

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


test.describe('Post-Forms Pro', () => {
    /**----------------------------------POSTFORMS----------------------------------**
     *
     * @TestScenario : [Post-Forms]
     * @Test_PF0001 : Admin is creating a Blank Post Form with all Fields
     * @Test_PF0002 : Admin is Updating Settings with default Post Form
     * @Test_PF0003 : User is Creating Post from Frontend
     * @Test_PF0004 : User is Validating Post created
     * @Test_PF0005 : User is validating Entered Data for Created Post
     * @Test_PF0011 : Admin is setting necessary setup for product form
     * @Test_PF0012 : Admin is creating a product Post Form
     * @Test_PF0013 : Admin is creating product page page with shortcode
     * @Test_PF0014 : Admin is creating product from FE
     * @Test_PF0015 : Admin is validating product created
     * @Test_PF0016 : Admin is validating entered product data
     * @Test_PF0017 : Admin is setting necessary setup for downloads form
     * @Test_PF0018 : Admin is creating a downloads Post Form
     * @Test_PF0019 : Admin is creating downloads page with shortcode
     * @Test_PF0020 : Admin is creating downloads from FE
     * @Test_PF0021 : Admin is validating downloads created
     * @Test_PF0022 : Admin is validating entered downloads data
     * @Test_PF0023 : Admin is validating entered downloads data BE
     *
     */

    let productShortCode: string;
    let downloadsShortCode: string;

    test('PF0001 : Admin is creating a Blank Post Form with all Fields', { tag: ['@Pro'] }, async () => {
        await new BasicLoginPage(page).basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        const PostFormPro = new PostFormProPage(page);
        const FieldAddPro = new FieldAddPage(page);

        PostForm.formName = faker.word.words(3);
        //Post Blank Form
        await PostFormPro.createBlankFormPostFormPro(PostForm.formName);
        //PostFields + Validate
        await FieldAddPro.addPostFields_PF();
        await FieldAddPro.validatePostFields_PF();
        //Category + Validate
        await FieldAddPro.addTaxonomies_PF();
        await FieldAddPro.validateTaxonomies_PF();
        //CustomFields + Validate
        await FieldAddPro.addCustomFields_Common();
        await FieldAddPro.validateCustomFields_Common();
        //Others + Validate
        await FieldAddPro.addOthers_Common();
        await FieldAddPro.validateOthers_Common();
        //await FieldOptionsCommon.setMultiStepSettings_Common();

        //Save
        await FieldAddPro.saveForm_Common();
        //Validate
        await FieldAddPro.validatePostFormCreated(PostForm.formName);
    });

    test('PF0002 : Admin is Updating Settings with default Post Form', { tag: ['@Pro'] }, async () => {
        const SettingsSetup = new SettingsSetupPage(page);

        await SettingsSetup.changeSettingsSetDefaultPostForm(PostForm.formName);

        await new BasicLogoutPage(page).logOut();
    });


    test('PF0003 : User is Submitting Form from Frontend', { tag: ['@Pro'] }, async () => {
        const PostFormPro = new PostFormPage(page);

        await new BasicLoginPage(page).basicLogin(Users.userEmail, Users.userPassword);

        await PostFormPro.createPostFE();
    });

    test('PF0004 : User is Validating Post created', { tag: ['@Pro'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await PostFormClass.validatePostCreated();

    });

    test('PF0005 : User is validating Entered Data for Created Post', { tag: ['@Pro'] }, async () => {
        const PostFormClass = new PostFormPage(page);

        await PostFormClass.validateEnteredData();
    });

    test('PF0011 : Admin is setting necessary setup for product form', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);

        await PostForm.setupForWooProduct();
    });

    test('PF0012 : Admin is creating a product Post Form', { tag: ['@Pro'] }, async () => {
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
        //Validate
        productShortCode = await FieldAdd.validatePostFormCreated('WooCommerce Product');
        console.log('Product Short Code: ' + productShortCode);

    });

    test('PF0013 : Admin is creating product page with shortcode ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcodeGeneral(productShortCode, 'Add Product');
    });

    test('PF0014 : Admin is creating product from FE ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createProductFE();
    });

    test('PF0015 : Admin is validating product created', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateProductCreated();
    });

    test('PF0016 : Admin is validating entered product data', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredProductData();
    });

    test('PF0017 : Admin is setting necessary setup for downloads form', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.setupForEDDProduct();
    });

    test('PF0018 : Admin is creating a downloads Post Form', { tag: ['@Pro'] }, async () => {
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
        //Validate
        downloadsShortCode = await FieldAdd.validatePostFormCreated('EDD Download');
        console.log('Downloads Short Code: ' + downloadsShortCode);

    });

    test('PF0019 : Admin is creating add downloads page with shortcode ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createPageWithShortcodeGeneral(downloadsShortCode, 'Add Downloads');
    });

    test('PF0020 : Admin is creating downloads from FE ', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.createDownloadsFE();
    });

    test('PF0021 : Admin is validating downloads created', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateDownloadsCreated();
    });

    test('PF0022 : Admin is validating entered downloads data', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredDownloadsData();
    });

    test('PF0023 : Admin is validating entered downloads data BE', { tag: ['@Pro'] }, async () => {
        const PostForm = new PostFormPage(page);

        await PostForm.validateEnteredDownloadsDataBE();
    });

});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});