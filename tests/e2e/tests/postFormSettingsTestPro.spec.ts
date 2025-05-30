import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
dotenv.config();
import { PostFormSettingsPage } from '../pages/postFormSettingsPage';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, PostForm, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';
import * as fs from "fs";

export default function postFormGeneralSettingsTestsPro() {

    let browser: Browser;
    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Launch browser
        browser = await chromium.launch();
        
        // Create a single context
        context = await browser.newContext();
        
        // Create a single page
        page = await context.newPage();
    });

    test.describe('Post Form Settings Tests Pro', () => {
        /**----------------------------------POST FORM SETTINGS----------------------------------**
         *
         * @TestScenario : [Post Form Settings]
         * @Test_PFS0001 : Admin is changing post type
         *
         */

        let formName: string;
        const postTitle = faker.word.words(3);
        const postContent = faker.lorem.paragraph();
        const postExcerpt = faker.lorem.paragraph();
        const category = 'Music'; // Using one of the default categories from the screenshot

        // Add your Pro-specific tests here with { tag: ['@Pro'] }

        test.beforeAll(async () => {
            formName = PostForm.pfPostName1 + faker.word.words(1);
            const basicLogin = new BasicLoginPage(page);
            await basicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            const postFormSettings = new PostFormSettingsPage(page);
            // Create a new post form
            await postFormSettings.createPostForm(formName);
            const settingsSetup = new SettingsSetupPage(page);
            await settingsSetup.changeSettingsSetDefaultPostForm(formName);
        });
        
        test('PFS0029 : Admin is enabling multi-step form', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.enableMultiStep(formName);
        });

        test('PFS0030 : Admin is validating multi-step progressbar', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateMultiStepProgessbar(formName);
        });

        test('PFS0031 : Admin is validating multi-step by step', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateMultiStepByStep(formName);
        });

        test('PFS0032 : Admin is disabling multi-step form', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.disableMultiStep(formName);
        });
        
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });

    
}