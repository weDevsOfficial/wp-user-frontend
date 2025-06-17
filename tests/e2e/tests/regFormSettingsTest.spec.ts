import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { RegFormSettingsPage } from '../pages/regFormSettingsPage';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, PostForm, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';
import * as fs from "fs";
import { BasicLogoutPage } from '../pages/basicLogout';

export default function regFormSettingsTest() {
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

    test.describe('Reg Form Settings Tests', () => {
        /**----------------------------------POST FORM SETTINGS----------------------------------**
         *
         * @TestScenario : [Registration Form Settings]
         * @Test_RFS0001 : Admin is setting newly registered user role to administrator
         * @Test_RFS0002 : Admin is validating newly registered user role to administrator
         * @Test_RFS0003 : Admin is setting newly registered user role to editor
         * @Test_RFS0004 : Admin is validating newly registered user role to editor
         * @Test_RFS0005 : Admin is setting newly registered user role to author
         * @Test_RFS0006 : Admin is validating newly registered user role to author
         * @Test_RFS0007 : Admin is setting newly registered user role to contributor
         * @Test_RFS0008 : Admin is validating newly registered user role to contributor
         * @Test_RFS0009 : Admin is setting newly registered user role to subscriber
         * @Test_RFS0010 : Admin is validating newly registered user role to subscriber
         */

        let formName: string;
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

        test('RFS0001 : Admin is setting newly registered user role to administrator', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.settingNewlyRegisteredUserRole(formName, 'administrator');
        });

        test('RFS0002 : Admin is validating newly registered user role to administrator', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.validateNewlyRegisteredUserRole(formName, 'administrator');
        });

        test('RFS0003 : Admin is setting newly registered user role to editor', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.settingNewlyRegisteredUserRole(formName, 'editor');
        });

        test('RFS0004 : Admin is validating newly registered user role to editor', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.validateNewlyRegisteredUserRole(formName, 'editor');
        });

        test('RFS0005 : Admin is setting newly registered user role to author', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.settingNewlyRegisteredUserRole(formName, 'author');
        });

        test('RFS0006 : Admin is validating newly registered user role to author', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.validateNewlyRegisteredUserRole(formName, 'author');
        });

        test('RFS0007 : Admin is setting newly registered user role to contributor', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.settingNewlyRegisteredUserRole(formName, 'contributor');
        });

        test('RFS0008 : Admin is validating newly registered user role to contributor', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.validateNewlyRegisteredUserRole(formName, 'contributor');
        });

        test('RFS0009 : Admin is setting newly registered user role to subscriber', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.settingNewlyRegisteredUserRole(formName, 'subscriber');
        });

        test('RFS0010 : Admin is validating newly registered user role to subscriber', { tag: ['@Pro'] }, async () => {
            const regFormSettings = new RegFormSettingsPage(page);
            await regFormSettings.validateNewlyRegisteredUserRole(formName, 'subscriber');
        });
    });
}