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
        await expect(this.page.locator(Selectors.postFormSettings.postTypePage(postTitle))).toBeVisible();

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
        await expect(this.page.locator(Selectors.postFormSettings.postCategory(category))).toBeVisible();
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

        await expect(this.page.locator(Selectors.postFormSettings.checkPostTitle(postTitle))).toBeVisible();
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

        await expect(this.page.locator(Selectors.postFormSettings.checkPageTitle(pageTitle))).toBeVisible();
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

        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
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

        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
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

        await expect(this.page.locator(Selectors.postFormSettings.submitPostButtonText(value))).toBeVisible();
    }

    async enableMultiStep(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Check if multi-step is already enabled
        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepCheckbox).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
        }

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async validateMultiStepProgessbar(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postForms.addCustomFields_Common.customFieldsStepStart);
        if (await this.page.isVisible(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose)) {
            await this.validateAndClick(Selectors.postForms.addCustomFields_Common.prompt1PopUpModalClose);
        }
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.categoryBlock);
        await this.validateAndClick(Selectors.postForms.addTaxonomies_PF.tagsBlock);

        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateCategory);
        await this.assertionValidate(Selectors.postForms.validateTaxonomies_PF.validateTags);
        await this.assertionValidate(Selectors.postForms.validateCustomFields_Common.validateStepStart);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await expect(this.page.locator(Selectors.postFormSettings.multiStepProgressbar)).toBeVisible();
    }

    async validateMultiStepByStep(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepCheckbox).isChecked();
        if (isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.progressbarTypeContainer);
            await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.progressbarTypeDropdown);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.progressbarTypeOption('step_by_step'));
        }

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.page.goto(Urls.baseUrl + '/account/?section=submit-post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await expect(this.page.locator(Selectors.postFormSettings.multiStepByStep)).toBeVisible();
    }

    async disableMultiStep(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Check if multi-step is enabled
        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepCheckbox).isChecked();
        if (isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
        }

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditor);
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.removeStepStart);
        await this.validateAndClick(Selectors.postFormSettings.confirmDelete);
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
        
        
    }

    async setPostUpdateStatus(formName: string, status: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on post update status dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postUpdateStatusContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postUpdateStatusDropdown);

        // Select the status
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postUpdateStatusOption(status));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setPostUpdateMessage(formName: string, message: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Clear and fill the message field
        await this.page.locator(Selectors.postFormSettings.postSettingsSection.postUpdateMessageContainer).clear();
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.postUpdateMessageContainer, message);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setLockUserEditingAfter(formName: string, hours: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Clear and fill the hours field
        await this.page.locator(Selectors.postFormSettings.postSettingsSection.lockUserEditingAfterInput).clear();
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.lockUserEditingAfterInput, hours);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setUpdatePostButtonText(formName: string, buttonText: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Clear and fill the button text field
        await this.page.locator(Selectors.postFormSettings.postSettingsSection.updatePostButtonTextInput).clear();
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.updatePostButtonTextInput, buttonText);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async validatePostUpdateStatusInForm(postTitle: string, postContent: string, postExcerpt: string, expectedStatus: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/account/?section=post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
        await this.page.waitForLoadState('networkidle');

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(1000);
        // await this.assertionValidate(Selectors.postFormSettings.checkSuccessMessage);

        await this.page.goto(Urls.baseUrl + '/account/?section=post', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');
        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(expectedStatus);
        }
    }

    async pendingToLive() {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/edit.php', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.quickEditButton);
        await this.page.waitForLoadState('networkidle');

        await this.page.selectOption(Selectors.postFormSettings.statusDropdown, 'publish');
        await this.validateAndClick(Selectors.postFormSettings.updateStatus);        
        
    }

    async validatePostUpdateMessageInForm(expectedMessage: string, formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Validate the message content
        const messageValue = await this.page.locator(Selectors.postFormSettings.postSettingsSection.postUpdateMessageContainer).inputValue();
        await expect(messageValue).toBe(expectedMessage);
    }

    async validateUpdatePostButtonTextInForm(expectedButtonText: string, formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Validate the button text
        const buttonTextValue = await this.page.locator(Selectors.postFormSettings.postSettingsSection.updatePostButtonTextInput).inputValue();
        await expect(buttonTextValue).toBe(expectedButtonText);
    }

    async setSuccessfulRedirectionToUpdatedPost(formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.successfulRedirectionDropdown);

        // Select "updated post"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionOption('post'));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setSuccessfulRedirectionToSamePage(formName: string, message: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.successfulRedirectionDropdown);

        // Select "same page"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionOption('same'));

        // Fill the message
        await this.page.locator(Selectors.postFormSettings.postSettingsSection.successfulRedirectionMessage).clear();
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.successfulRedirectionMessage, message);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setSuccessfulRedirectionToPage(formName: string, pageName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.successfulRedirectionDropdown);

        // Select "to a page"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionOption('page'));

        await this.page.waitForTimeout(500);

        // Select the specific page
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionPageContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.successfulRedirectionPageDropdown);
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionPageOption(pageName));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setSuccessfulRedirectionToCustomUrl(formName: string, customUrl: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionContainer);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.successfulRedirectionDropdown);

        // Select "to a custom URL"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.successfulRedirectionOption('url'));

        // Fill the custom URL
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.successfulRedirectionUrlInput, customUrl);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async validateSuccessfulRedirectionInForm(expectedRedirection: string, formName: string) {
        // Go to form edit page
        await this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' });
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
        await this.page.waitForLoadState('networkidle');

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Validate the selected redirection option
        const redirectionText = await this.page.locator(Selectors.postFormSettings.postSettingsSection.successfulRedirectionContainer).innerText();
        await expect(redirectionText).toContain(expectedRedirection);
    }
}
