import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { PostFormSettingsPage } from '../pages/postFormSettingsPage';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, PostForm, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';
import * as fs from "fs";

export default function postFormGeneralSettingsTests() {
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

    test.describe('Post Form Settings Tests @Lite :-->', () => {
        /**----------------------------------POST FORM SETTINGS----------------------------------**
         *
         * @TestScenario : [Post Form Settings]
         * @Test_PFS0001 : Admin is changing post type
         * @Test_PFS0002 : Admin is validating post type
         * @Test_PFS0003 : Admin is validating post type from FE
         * @Test_PFS0004 : Admin is setting the default category
         * @Test_PFS0005 : Admin is validating default category from FE
         * @Test_PFS0006 : Admin is setting successful post redirection to newly created post
         * @Test_PFS0007 : Admin is checking post redirection to newly created post
         * @Test_PFS0008 : Admin is setting successful post redirection to same page
         * @Test_PFS0009 : Admin is checking post redirection to same page
         * @Test_PFS0010 : Admin is setting successful post redirection to another page
         * @Test_PFS0011 : Admin is checking post redirection to another page
         * @Test_PFS0012 : Admin is setting successful post redirection to a url
         * @Test_PFS0013 : Admin is checking post redirection to a url
         * @Test_PFS0014 : Admin is setting post submission status to draft
         * @Test_PFS0015 : Admin is validating post submission status to draft - list
         * @Test_PFS0016 : Admin is checking post submission status to draft - FE
         * @Test_PFS0017 : Admin is setting post submission status to pending
         * @Test_PFS0018 : Admin is validating post submission status to draft - list
         * @Test_PFS0019 : Admin is checking post submission status to pending
         * @Test_PFS0020 : Admin is setting post submission status to private
         * @Test_PFS0021 : Admin is validating post submission status to draft - list
         * @Test_PFS0022 : Admin is checking post submission status to private
         * @Test_PFS0023 : Admin is setting post submission status to publish
         * @Test_PFS0024 : Admin is validating post submission status to draft - list
         * @Test_PFS0025 : Admin is checking post submission status to publish
         * @Test_PFS0026 : Admin is enabling post saving as draft
         * @Test_PFS0027 : Admin is saving post as draft
         * @Test_PFS0028 : Admin is changing submit button text
         *
         */

        let formName: string;
        const postTitle = faker.word.words(3);
        const postContent = faker.lorem.paragraph();
        const postExcerpt = faker.lorem.paragraph();
        const category = 'Music'; // Using one of the default categories from the screenshot

        test('PFS0001 : Admin is changing post type', async () => {
            formName = PostForm.pfPostName1 + faker.word.words(1);
            const basicLogin = new BasicLoginPage(page);
            await basicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            const postFormSettings = new PostFormSettingsPage(page);
            // Create a new post form
            await postFormSettings.createPostForm(formName);
            const settingsSetup = new SettingsSetupPage(page);
            await settingsSetup.changeSettingsSetDefaultPostForm(formName);
            // Change post type to 'page'
            await postFormSettings.changePostType('page', formName);
        });

        test('PFS0002 : Admin is validating post type', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostTypeInList('page');
        });

        test('PFS0003 : Admin is validating post type from FE', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Submit a post and validate it appears as a page
            await postFormSettings.validatePostTypeFE(postTitle, postContent, postExcerpt);
        });

        test('PFS0004 : Admin is setting the default category', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Set default category
            await postFormSettings.setDefaultCategory(category, formName);
        });

        test('PFS0005 : Admin is validating default category from FE', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            const postTitle1 = faker.word.words(3);
            const postContent1 = faker.lorem.paragraph();
            const postExcerpt1 = faker.lorem.paragraph();
            // Submit a post and validate its category
            await postFormSettings.submitAndValidateCategory(postTitle1, postContent1, postExcerpt1, category);
        });

        test('PFS0006 : Admin is setting successful post redirection to newly created post', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPost(formName, 'post');
        });

        test('PFS0007 : Admin is checking post redirection to newly created post', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPost(postTitle, postContent, postExcerpt);
        });

        test('PFS0008 : Admin is setting successful post redirection to same page', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToSamePage(formName, 'same', 'Post published successfully');
        });

        test('PFS0009 : Admin is checking post redirection to same page', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post published successfully');
        });

        test('PFS0010 : Admin is setting successful post redirection to another page', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPage(formName, 'page', 'Dashboard');
        });

        test('PFS0011 : Admin is checking post redirection to another page', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPage(postTitle, postContent, postExcerpt, 'Dashboard');
        });

        test('PFS0012 : Admin is setting successful post redirection to a url', async () => {
            const redirectUrl = Urls.baseUrl + '/thank-you/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToUrl(formName, 'url', redirectUrl);
        });

        test('PFS0013 : Admin is checking post redirection to a url', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const expectedUrl = Urls.baseUrl + '/thank-you/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToUrl(postTitle, postContent, postExcerpt, expectedUrl);
            await postFormSettings.setPostRedirectionToPost(formName, 'post');
        });

        test('PFS0014 : Admin is setting post submission status to draft', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'draft');
        });

        test('PFS0015 : Admin is validating post submission status to draft - list', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Draft');
        });

        test('PFS0016 : Admin is checking post submission status to draft - FE', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Offline');
        });

        test('PFS0017 : Admin is setting post submission status to pending', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'pending');
        });

        test('PFS0018 : Admin is validating post submission status to draft - list', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Pending Review');
        });

        test('PFS0019 : Admin is checking post submission status to pending', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Awaiting Approval');
        });

        test('PFS0020 : Admin is setting post submission status to private', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'private');
        });

        test('PFS0021 : Admin is validating post submission status to draft - list', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Private');
        });

        test('PFS0022 : Admin is checking post submission status to private', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Private');
        });

        test('PFS0023 : Admin is setting post submission status to publish', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'publish');
        });
        
        test('PFS0024 : Admin is validating post submission status to draft - list', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Published');
        });

        test('PFS0025 : Admin is checking post submission status to publish', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Live');
        });

        test('PFS0026 : Admin is enabling post saving as draft', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSavingAsDraft(formName);
        });

        test('PFS0027 : Admin is saving post as draft', async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.savingPostAsDraft(postTitle, postContent, postExcerpt, 'Offline');
        });

        test('PFS0028 : Admin is changing submit button text', async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.changeSubmitButtonText(formName, 'Publish');
        });
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });
}