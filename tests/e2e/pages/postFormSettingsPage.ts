import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Urls } from '../utils/testData';
import { Selectors } from './selectors';
import { FieldOptionsCommonPage } from '../pages/fieldOptionsCommon';

export class PostFormSettingsPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    // Create a new post form
    async createPostForm(formName: string) {

        const FieldOptionsCommon = new FieldOptionsCommonPage(this.page);
        // Go to post forms page
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' }),
        ]);

        // Click Add New button
        await this.validateAndClick(Selectors.postFormSettings.addNewButton);

        // Click Blank Form
        await this.validateAndClick(Selectors.postFormSettings.clickBlankForm);
        
        // Fill form name
        await this.validateAndFillStrings(Selectors.postFormSettings.formNameInput, formName);

        // Add post fields
        await FieldOptionsCommon.addPostFields_PF();
        
        // Save the form
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
    }

    // Change post type in form settings
    async changePostType(postType: string, formName: string) {
        // Go to post forms page
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' }),
        ]);
        
        // Click on the form name
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        
        // Wait for settings to load
        await this.page.waitForTimeout(500);
        
        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeContainer);
        
        // Wait for dropdown to be visible
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postTypeDropdown, { state: 'visible' });
        
        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeOption(postType));
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        
        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Validate post type in list
    async validatePostTypeInList(expectedPostType: string) {
        // Go to post forms list
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' }),
        ]);

        // Find the row containing the form name
        const postTypeText = await this.page.innerText(Selectors.postFormSettings.postTypeColumn);
        
        // Verify post type matches expected
        expect(postTypeText.toLowerCase()).toContain(expectedPostType.toLowerCase());
    }


    // Submit a post from frontend and validate post type
    async validatePostTypeFE(postTitle: string, postContent: string, postExcerpt: string) {

        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(1000);
        
        // Wait for success message
        await expect(this.assertionValidate(Selectors.postFormSettings.postTypePage(postTitle))).toBeTruthy();
        
    }

    // Set default category
    async setDefaultCategory(category: string, formName: string) {

        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        
        // Wait for form list to load and click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeContainer);
        
        // Wait for dropdown to be visible
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postTypeDropdown, { state: 'visible' });
        
        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeOption('post'));
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        
        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.defaultCategoryContainer);
        
        // Wait for dropdown to be visible
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.defaultCategoryDropdown, { state: 'visible' });
        
        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.defaultCategoryOption(category));
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        
        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Submit a post and validate category
    async submitAndValidateCategory(postTitle: string, postContent: string, postExcerpt: string, category: string) {
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(1000);
        
        // Wait for success message
        await expect(this.assertionValidate(Selectors.postFormSettings.postCategory(category))).toBeTruthy();
    }

    // Set post redirection to newly created post
    async setPostRedirectionToPost(formName: string, value: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        
        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);
        
        // Wait for dropdown to be visible
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });
        
        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Set post redirection to newly created post
    async setPostRedirectionToSamePage(formName: string, value: string, message: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        
        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);
        
        // Wait for dropdown to be visible
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });
        
        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));

        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.postRedirectionMessage, message);
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Set post redirection to another page
    async setPostRedirectionToPage(formName: string, value: string, text: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        
        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Select redirect to page
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);
        
        // Select the page
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown);
        
        // Set success message
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));

        await this.page.waitForTimeout(500);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionPageContainer);

        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionPageDropdown);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionPageOption(text));
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

    }

    // Set post redirection to URL
    async setPostRedirectionToUrl(formName: string, value: string, url: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        
        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');
        
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        // Select redirect to URL
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);
        
        // Enter the URL
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown);

        // Set success message
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));
        
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.postRedirectionUrlInput, url);
        
        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Validate redirection after post submission
    async validateRedirectionToPost(postTitle: string, postContent: string, postExcerpt: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
        await this.page.waitForTimeout(1000);

        await expect(this.assertionValidate(Selectors.postFormSettings.checkPostTitle(postTitle))).toBeTruthy();
    }

    // Validate redirection after post submission
    async validateRedirectionToSamePage(postTitle: string, postContent: string, postExcerpt: string, message: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
        await this.page.waitForTimeout(1000);

        const successMessage = await this.page.innerText(Selectors.postFormSettings.checkSuccessMessage);

        expect(successMessage).toContain(message);
    }

    // Validate redirection after post submission
    async validateRedirectionToPage(postTitle: string, postContent: string, postExcerpt: string, pageTitle: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
        await this.page.waitForTimeout(1000);

        await expect(this.assertionValidate(Selectors.postFormSettings.checkPageTitle(pageTitle))).toBeTruthy();
    }

    // Validate redirection after post submission
    async validateRedirectionToUrl(postTitle: string, postContent: string, postExcerpt: string, expectedUrl: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
        await this.page.waitForTimeout(1000);

        await expect(this.page).toHaveURL(expectedUrl);
    }

    // Set post submission status
    async setPostSubmissionStatus(formName: string, value: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusDropdown);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusOption(value));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
        
    }

    // Validate post type in list
    async validatePostSubmissionStatusInList(expectedPostStatus: string) {
        // Go to post forms list
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' }),
        ]);

        // Find the row containing the form name
        const postStatusText = await this.page.innerText(Selectors.postFormSettings.postSubmissionStatusColumn);
        
        // Verify post type matches expected
        expect(postStatusText.toLowerCase()).toContain(expectedPostStatus.toLowerCase());
    }

    // Validate submitted post status
    async validateSubmittedPostStatusFE(postTitle: string, postContent: string, postExcerpt: string, value: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);
        await this.page.waitForTimeout(1000);

        await this.page.goto(Urls.baseUrl + '/account/?section=post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        const newPostTitle = await  this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(value);
        }
        
    }

    async setPostSavingAsDraft(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.savingAsDraftToggleOn);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
        
    }

    async savingPostAsDraft(postTitle: string, postContent: string, postExcerpt: string, value: string) {
        // Go to submit post page
        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);
        
        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);
        
        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.saveDraftButton);
        await this.page.waitForTimeout(200);
        await expect(this.page.locator(Selectors.postFormSettings.draftSavedAlert)).toBeVisible();

        await this.page.goto(Urls.baseUrl + '/account/?section=post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        const newPostTitle = await  this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(value);
        }
    }

    async changeSubmitButtonText(formName: string, value: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.submitButtonContainer, value);
        
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.assertionValidate(Selectors.postFormSettings.submitPostButtonText(value));
    }
}
