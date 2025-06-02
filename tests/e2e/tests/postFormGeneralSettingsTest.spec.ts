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

    test.describe('Post Form Settings Tests', () => {
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
         * @Test_PFS0029 : Admin is enabling multi-step form
         * @Test_PFS0030 : Admin is validating multi-step progressbar
         * @Test_PFS0031 : Admin is validating multi-step by step
         * @Test_PFS0032 : Admin is disabling multi-step form
         * @Test_PFS0033 : Admin is setting post update status to draft
         * @Test_PFS0034 : Admin is validating post update status
         * @Test_PFS0035 : Admin is setting post update status to pending review
         * @Test_PFS0036 : Admin is validating post update status
         * @Test_PFS0037 : Admin is setting post update status to private
         * @Test_PFS0038 : Admin is validating post update status to private
         * @Test_PFS0039 : Admin is setting post update status to no change
         * @Test_PFS0040 : Admin is validating post update status to no change
         * @Test_PFS0041 : Admin is setting post update status to published
         * @Test_PFS0042 : Admin is validating post update status to published
         * @Test_PFS0043 : Admin is setting successful redirection to updated post
         * @Test_PFS0044 : Admin is validating successful redirection to updated post
         * @Test_PFS0045 : Admin is setting successful redirection to same page
         * @Test_PFS0046 : Admin is validating successful redirection to same page
         * @Test_PFS0047 : Admin is setting successful redirection to a page
         * @Test_PFS0048 : Admin is validating successful redirection to a page
         * @Test_PFS0049 : Admin is setting successful redirection to custom URL
         * @Test_PFS0050 : Admin is validating successful redirection to custom URL
         * @Test_PFS0051 : Admin is setting post update message
         * @Test_PFS0052 : Admin is validating post update message in form
         * @Test_PFS0053 : Admin is setting lock user editing after time
         * @Test_PFS0054 : Admin is setting update post button text
         * @Test_PFS0055 : Admin is validating update post button text in form
         *
         */

        let formName: string;
        const postTitle = faker.word.words(3);
        const postContent = faker.lorem.paragraph();
        const postExcerpt = faker.lorem.paragraph();
        const category = 'Music'; // Using one of the default categories from the screenshot

        test('PFS0001 : Admin is changing post type', { tag: ['@Lite'] }, async () => {
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

        test('PFS0002 : Admin is validating post type', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostTypeInList('page');
        });

        test('PFS0003 : Admin is validating post type from FE', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Submit a post and validate it appears as a page
            await postFormSettings.validatePostTypeFE(postTitle, postContent, postExcerpt);
        });

        test('PFS0004 : Admin is setting the default category', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Set default category
            await postFormSettings.setDefaultCategory(category, formName);
        });

        test('PFS0005 : Admin is validating default category from FE', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            const postTitle1 = faker.word.words(3);
            const postContent1 = faker.lorem.paragraph();
            const postExcerpt1 = faker.lorem.paragraph();
            // Submit a post and validate its category
            await postFormSettings.submitAndValidateCategory(postTitle1, postContent1, postExcerpt1, category);
        });

        test('PFS0006 : Admin is setting successful post redirection to newly created post', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPost(formName, 'post');
        });

        test('PFS0007 : Admin is checking post redirection to newly created post', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPost(postTitle, postContent, postExcerpt);
        });

        test('PFS0008 : Admin is setting successful post redirection to same page', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToSamePage(formName, 'same', 'Post published successfully');
        });

        test('PFS0009 : Admin is checking post redirection to same page', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post published successfully');
        });

        test('PFS0010 : Admin is setting successful post redirection to another page', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPage(formName, 'page', 'Dashboard');
        });

        test('PFS0011 : Admin is checking post redirection to another page', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPage(postTitle, postContent, postExcerpt, 'Dashboard');
        });

        test('PFS0012 : Admin is setting successful post redirection to a url', { tag: ['@Lite'] }, async () => {
            const redirectUrl = Urls.baseUrl + '/thank-you/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToUrl(formName, 'url', redirectUrl);
        });

        test('PFS0013 : Admin is checking post redirection to a url', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const expectedUrl = Urls.baseUrl + '/thank-you/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToUrl(postTitle, postContent, postExcerpt, expectedUrl);
            await postFormSettings.setPostRedirectionToPost(formName, 'post');
        });

        test('PFS0014 : Admin is setting post submission status to draft', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'draft');
        });

        test('PFS0015 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Draft');
        });

        test('PFS0016 : Admin is checking post submission status to draft - FE', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Offline');
        });

        test('PFS0017 : Admin is setting post submission status to pending', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'pending');
        });

        test('PFS0018 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Pending Review');
        });

        test('PFS0019 : Admin is checking post submission status to pending', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Awaiting Approval');
        });

        test('PFS0020 : Admin is setting post submission status to private', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'private');
        });

        test('PFS0021 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Private');
        });

        test('PFS0022 : Admin is checking post submission status to private', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Private');
        });

        test('PFS0023 : Admin is setting post submission status to publish', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSubmissionStatus(formName, 'publish');
        });
        
        test('PFS0024 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostSubmissionStatusInList('Published');
        });

        test('PFS0025 : Admin is checking post submission status to publish', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Live');
        });

        test('PFS0026 : Admin is enabling post saving as draft', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostSavingAsDraft(formName);
        });

        test('PFS0027 : Admin is saving post as draft', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.savingPostAsDraft(postTitle, postContent, postExcerpt, 'Offline');
        });

        test('PFS0028 : Admin is changing submit button text', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.changeSubmitButtonText(formName, 'Publish');
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

        test('PFS0033 : Admin is setting post update status to draft', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostUpdateStatus(formName, 'draft');
        });

        test('PFS0034 : Admin is validating post update status for draft', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Offline');
        });
        test('PFS0035 : Admin is setting post update status to pending review', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostUpdateStatus(formName, 'pending');
        });

        test('PFS0036 : Admin is validating post update status for pending review', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Awaiting Approval');
            await postFormSettings.pendingToLive();
        });

        test('PFS0037 : Admin is setting post update status to private', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostUpdateStatus(formName, 'private');
        });

        test('PFS0038 : Admin is validating post update status for private', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Private');
        });

        test('PFS0039 : Admin is setting post update status to no change', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostUpdateStatus(formName, '_nochange');
        });

        test('PFS0040 : Admin is validating post update status for no change', { tag: ['@Lite'] }, async () => {const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Private');
        });

        test('PFS0041 : Admin is setting post update status to published', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostUpdateStatus(formName, 'publish');
        });

        test('PFS0042 : Admin is validating post update status for published', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Live');
        });

        test('PFS0043 : Admin is setting successful redirection to updated post', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setUpdatePostRedirectionToUpdatedPost(formName);
        });

        test('PFS0044 : Admin is validating successful redirection to updated post', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateUpdatePostRedirectionToPost(postTitle, postContent, postExcerpt);
        });

        test('PFS0045 : Admin is setting successful redirection to same page', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setUpdatePostRedirectionToSamePage(formName, 'Post updated successfully');
        });

        test('PFS0046 : Admin is validating successful redirection to same page', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateUpdatePostRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post updated successfully');
        });

        test('PFS0047 : Admin is setting successful redirection to a page', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setUpdatePostRedirectionToPage(formName, 'Dashboard');
        });

        test('PFS0048 : Admin is validating successful redirection to a page', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateUpdatePostRedirectionToPage(postTitle, postContent, postExcerpt, 'Dashboard');
        });

        test('PFS0049 : Admin is setting successful redirection to custom URL', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            const customUrl = Urls.baseUrl + '/dashboard/';
            await postFormSettings.setUpdatePostRedirectionToCustomUrl(formName, customUrl);
        });

        test('PFS0050 : Admin is validating successful redirection to custom URL', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const customUrl = Urls.baseUrl + '/dashboard/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateUpdatePostRedirectionToUrl(postTitle, postContent, postExcerpt, customUrl);
            await postFormSettings.setUpdatePostRedirectionToSamePage(formName, 'Post updated successfully');
        });

        test('PFS0051 : Admin is setting post update message', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            const customMessage = 'Post has been updated successfully!';
            await postFormSettings.setPostUpdateMessage(formName, customMessage);
        });

        test('PFS0052 : Admin is validating post update message in form', { tag: ['@Lite'] }, async () => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            const customMessage = 'Post has been updated successfully!';
            await postFormSettings.validatePostUpdateMessageInForm(postTitle, postContent, postExcerpt, customMessage);
        });

        test('PFS0053 : Admin is setting lock user editing after time', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setLockUserEditingAfter(formName, '24');
        });

        test('PFS0054 : Admin is setting update post button text', { tag: ['@Lite'] }, async () => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setUpdatePostButtonText(formName, 'Save Changes');
        });
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });
}