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
    let postTitle: string = '';
    let postContent: string = '';
    let postExcerpt: string = '';

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Launch browser
        const args = ['--enable-experimental-web-platform-features'];
        if (!Urls.baseUrl.startsWith('http://localhost')) {
            args.push(`--unsafely-treat-insecure-origin-as-secure=${Urls.baseUrl}`);
        }

        browser = await chromium.launch({args});
        
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
        const category = 'Music'; // Using one of the default categories from the screenshot
        const emailAddress = faker.internet.email();
        const emailSubject = faker.word.words(3);
        const emailBody = `Hi Admin,
            A new post has been submitted to {sitename}.
            Details:
            Title: {post_title}
            Author: {author} ({author_email})
            Content: {post_content}
            Excerpt: {post_excerpt}
            Category: {category}
            Tags: {tags}
            Review URL: {editlink}
            Public URL: {permalink}
            Best regards,
            Team {sitename}`;
        const multipleEmails = `${faker.internet.email()}, ${faker.internet.email()}, ${faker.internet.email()}`;

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

        test('PFS0069 : Admin is enabling updated post notification', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.enableUpdatedPostNotification(formName);
        });

        test('PFS0070 : Admin is validating Updated post notification settings enabled', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateUpdatedNotificationSettingsEnabled(formName);
        });

        test('PFS0071 : Admin is modifying Updated post notification email', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.modifyUpdatedNotificationEmail(formName, emailAddress);
        });

        test('PFS0072 : Admin is modifying Updated post notification subject', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.modifyUpdatedNotificationSubject(formName, emailSubject);
        });

        test('PFS0073 : Admin is modifying Updated post notification body with template tags', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.modifyUpdatedNotificationBodyWithTemplateTags(formName, emailBody);
        });

        test('PFS0074 : Admin is clicking and validating template tags for Updated post notification', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            const templateTags = ['{post_title}', '{post_content}', '{post_excerpt}'];
            //, '{tags}', '{category}', '{author}', '{author_email}', '{author_bio}', '{sitename}', '{siteurl}', '{permalink}', '{editlink}'
            await postFormSettings.clickTemplateTagsForUpdatedNotification(formName, templateTags);
        });

        test('PFS0075 : Admin is setting multiple Updated post notification emails', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setMultipleUpdatedNotificationEmails(formName, multipleEmails);
        });

        test.skip('PFS0076 : Admin is submitting post and validating Updated post notification from FE', { tag: ['@Pro'] }, async () => {
            let previousPostTitle = postTitle;
            postTitle = faker.word.words(3);
            postContent = faker.lorem.paragraph();
            postExcerpt = postContent;
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.submitPostAndValidateUpdatedNotificationFE(previousPostTitle, postTitle, postContent, postExcerpt, emailSubject, multipleEmails);
        });

        test('PFS0077 : Admin is disabling updated post notification', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.disableUpdatedPostNotification(formName);
        });

        test.skip('PFS0085 : Admin is enabling conditional logic on form submission', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            
        });

        test.skip('PFS0086 : Admin is validating conditional logic on form submission', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            
        });

        test('PFS0087 : Admin is enabling post expiration', { tag: ['@Pro'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.enablePostExpiration(formName);
        });

        
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });

    
}