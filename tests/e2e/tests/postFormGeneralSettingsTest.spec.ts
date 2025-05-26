import * as dotenv from 'dotenv';
import { test } from '@playwright/test';
import { faker } from '@faker-js/faker';
dotenv.config();
import { PostFormSettingsPage } from '../pages/postFormSettingsPage';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, PostForm, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';

export default function postFormGeneralSettingsTests() {

    test.describe('Post Form Settings Tests @Both :-->', () => {
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
         *
         */

        let formName: string;
        const postTitle = faker.word.words(3);
        const postContent = faker.lorem.paragraph();
        const postExcerpt = faker.lorem.paragraph();
        const category = 'Music'; // Using one of the default categories from the screenshot

        test('PFS0001 : Admin is changing post type', async ({ page }) => {
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

        test('PFS0002 : Admin is validating post type', async ({ page }) => {
            const postFormSettings = new PostFormSettingsPage(page);

            // Validate that the post type shows correctly in the list
            await postFormSettings.validatePostTypeInList(formName, 'page');
        });

        test('PFS0003 : Admin is validating post type from FE', async ({ page }) => {

            const postFormSettings = new PostFormSettingsPage(page);
            // Submit a post and validate it appears as a page
            await postFormSettings.validatePostTypeFE(postTitle, postContent, postExcerpt);
        });

        test('PFS0004 : Admin is setting the default category', async ({ page }) => {

            const postFormSettings = new PostFormSettingsPage(page);
            // Set default category
            await postFormSettings.setDefaultCategory(category, formName);
        });

        test('PFS0005 : Admin is validating default category from FE', async ({ page }) => {
            const postFormSettings = new PostFormSettingsPage(page);
            const postTitle1 = faker.word.words(3);
            const postContent1 = faker.lorem.paragraph();
            const postExcerpt1 = faker.lorem.paragraph();

            // Submit a post and validate its category
            await postFormSettings.submitAndValidateCategory(postTitle1, postContent1, postExcerpt1, category);
        });

        test('PFS0006 : Admin is setting successful post redirection to newly created post', async ({ page }) => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPost(formName, 'post');
        });

        test('PFS0007 : Admin is checking post redirection to newly created post', async ({ page }) => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();

            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPost(postTitle, postContent, postExcerpt);
        });

        test('PFS0008 : Admin is setting successful post redirection to same page', async ({ page }) => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToSamePage(formName, 'same', 'Post published successfully');
        });

        test('PFS0009 : Admin is checking post redirection to same page', async ({ page }) => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();

            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post published successfully');
        });

        test('PFS0010 : Admin is setting successful post redirection to another page', async ({ page }) => {
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToPage(formName, 'page', 'Dashboard');
        });

        test('PFS0011 : Admin is checking post redirection to another page', async ({ page }) => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToPage(postTitle, postContent, postExcerpt, 'Dashboard');
        });

        test('PFS0012 : Admin is setting successful post redirection to a url', async ({ page }) => {
            const redirectUrl = Urls.baseUrl + '/thank-you/';
            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.setPostRedirectionToUrl(formName, 'url', redirectUrl);
        });

        test('PFS0013 : Admin is checking post redirection to a url', async ({ page }) => {
            const postTitle = faker.word.words(3);
            const postContent = faker.lorem.paragraph();
            const postExcerpt = faker.lorem.paragraph();
            const expectedUrl = Urls.baseUrl + '/thank-you/';

            const postFormSettings = new PostFormSettingsPage(page);
            await postFormSettings.validateRedirectionToUrl(postTitle, postContent, postExcerpt, expectedUrl);
        });
    });
}