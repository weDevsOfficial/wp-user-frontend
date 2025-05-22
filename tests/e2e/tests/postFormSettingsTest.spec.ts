import * as dotenv from 'dotenv';
import { test } from '@playwright/test';
import { faker } from '@faker-js/faker';


dotenv.config();
import { PostFormSettingsPage } from '../pages/postFormSettingsPage';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, PostForm } from '../utils/testData';

export default function postFormSettingsTests() {

test.describe('Post Form Settings Tests @Both :-->', () => {
    /**----------------------------------POST FORM SETTINGS----------------------------------**
     *
     * @TestScenario : [Post Form Settings]
     * @Test_PFS0001 : Admin is changing post type
     * @Test_PFS0002 : Admin is validating post type
     *
     */

    const formName = PostForm.pfPostName1 + faker.word.words(1);
    test('PFS0001 : Admin is changing post type', async ({ page }) => {
        const basicLogin = new BasicLoginPage(page);
        const postFormSettings = new PostFormSettingsPage(page);

        // Log into Admin Dashboard
        await basicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        
        // Create a new post form
        await postFormSettings.createPostForm(formName);
        
        // Change post type to 'page'
        await postFormSettings.changePostType('page');
    });

    test('PFS0002 : Admin is validating post type', async ({ page }) => {
        const postFormSettings = new PostFormSettingsPage(page);
        
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostTypeInList(formName, 'page');
    });
});
}