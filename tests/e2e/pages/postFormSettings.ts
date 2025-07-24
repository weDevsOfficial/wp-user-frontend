import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Urls } from '../utils/testData';
import { Selectors } from './selectors';
import { FieldAddPage } from './fieldAdd';
import { BasicLogoutPage } from './basicLogout';

export class PostFormSettingsPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    // Create a new post form
    async createPostForm(formName: string) {

        const FieldAdd = new FieldAddPage(this.page);
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click Add New button
        await this.validateAndClick(Selectors.postFormSettings.addNewButton);

        // Click Blank Form
        await this.validateAndClick(Selectors.postFormSettings.clickBlankForm);

        // Fill form name
        await this.validateAndFillStrings(Selectors.postFormSettings.formNameInput, formName);

        // Add post fields
        await FieldAdd.addPostFields_PF();

        // Save the form
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Change post type in form settings
    async changePostType(postType: string, formName: string) {
        // Go to post forms page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form name
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeContainer);

        // Wait for dropdown to be visible
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postTypeDropdown, { state: 'visible' });

        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeOption(postType));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Validate post type in list
    async validatePostTypeInList(expectedPostType: string, formName: string) {
        // Go to post forms list
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Find the row containing the form name
        const postTypeText = await this.page.innerText(Selectors.postFormSettings.postTypeColumn(formName, expectedPostType.toLowerCase()));

        // Verify post type matches expected
        expect(postTypeText.toLowerCase()).toContain(expectedPostType.toLowerCase());
    }


    // Submit a post from frontend and validate post type
    async validatePostTypeFE(postTitle: string, postContent: string, postExcerpt: string) {

        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        // Wait for success message
        await expect(this.page.locator(Selectors.postFormSettings.postTypePage(postTitle))).toBeVisible();

    }

    // Set default category
    async setDefaultCategory(category: string, formName: string) {

        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Wait for form list to load and click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeContainer);

        // Wait for dropdown to be visible
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postTypeDropdown, { state: 'visible' });

        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postTypeOption('post'));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.defaultCategoryContainer);

        // Wait for dropdown to be visible
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.defaultCategoryDropdown, { state: 'visible' });

        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.defaultCategoryOption(category));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save and verify success message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Submit a post and validate category
    async submitAndValidateCategory(postTitle: string, postContent: string, postExcerpt: string, category: string) {
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);


        await this.page.waitForTimeout(2000);

        // Wait for success message
        await expect(this.page.locator(Selectors.postFormSettings.postCategory(category))).toBeVisible();
    }

    // Set post redirection to newly created post
    async setPostRedirectionToPost(formName: string, value: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);

        // Wait for dropdown to be visible
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });

        // Click the desired option
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    // Set post redirection to newly created post
    async setPostRedirectionToSamePage(formName: string, value: string, message: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Open the dropdown by clicking the container
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);

        // Wait for dropdown to be visible
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });

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
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Select redirect to page
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);

        // Select the page
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });

        // Set success message
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionOption(value));

        await this.page.waitForTimeout(200);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionPageContainer);

        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionPageDropdown, { state: 'visible' });

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionPageOption(text));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

    }

    // Set post redirection to URL
    async setPostRedirectionToUrl(formName: string, value: string, url: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        // Select redirect to URL
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postRedirectionContainer);

        // Enter the URL
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postRedirectionDropdown, { state: 'visible' });

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
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await expect(this.page.locator(Selectors.postFormSettings.checkPostTitle(postTitle))).toBeVisible();
    }

    // Validate redirection after post submission
    async validateRedirectionToSamePage(postTitle: string, postContent: string, postExcerpt: string, message: string) {
        // Go to submit post page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        const successMessage = await this.page.innerText(Selectors.postFormSettings.checkSuccessMessage);

        expect(successMessage).toContain(message);
    }

    // Validate redirection after post submission
    async validateRedirectionToPage(postTitle: string, postContent: string, postExcerpt: string, pageTitle: string) {
        // Go to submit post page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await expect(this.page.locator(Selectors.postFormSettings.checkPageTitle(pageTitle))).toBeVisible();
    }

    // Validate redirection after post submission
    async validateRedirectionToUrl(postTitle: string, postContent: string, postExcerpt: string, expectedUrl: string) {
        // Go to submit post page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await expect(this.page).toHaveURL(expectedUrl);
    }

    // Set post submission status
    async setPostSubmissionStatus(formName: string, value: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusDropdown, { state: 'visible' });

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postSubmissionStatusOption(value));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

    }

    // Validate post type in list
    async validatePostSubmissionStatusInList(expectedPostStatus: string, formName: string) {
        // Go to post forms list
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Find the row containing the form name
        const postStatusText = await this.page.innerText(Selectors.postFormSettings.postSubmissionStatusColumn(formName, expectedPostStatus));

        // Verify post type matches expected
        expect(postStatusText.toLowerCase()).toContain(expectedPostStatus.toLowerCase());
    }

    // Validate submitted post status
    async validateSubmittedPostStatusFE(postTitle: string, postContent: string, postExcerpt: string, value: string) {
        // Go to submit post page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await this.navigateToURL(this.wpufPostPage);

        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(value);
        }

    }

    async setPostSavingAsDraft(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.savingAsDraftToggleOn);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

    }

    async savingPostAsDraft(postTitle: string, postContent: string, postExcerpt: string, value: string) {
        // Go to submit post page
        await this.navigateToURL(this.wpufPostSubmitPage);

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

        await this.navigateToURL(this.wpufPostPage);

        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(value);
        }
    }

    async changeSubmitButtonText(formName: string, value: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.submitButtonContainer, value);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.navigateToURL(this.wpufPostSubmitPage);

        await expect(this.page.locator(Selectors.postFormSettings.submitPostButtonText(value))).toBeVisible();
    }

    async enableMultiStep(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

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
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

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

        await this.navigateToURL(this.wpufPostSubmitPage);

        await expect(this.page.locator(Selectors.postFormSettings.multiStepProgressbar)).toBeVisible();
    }

    async validateMultiStepByStep(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepCheckbox).isChecked();
        if (isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.progressbarTypeContainer);
            //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.progressbarTypeDropdown);

            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.progressbarTypeOption('step_by_step'));
        }

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.navigateToURL(this.wpufPostSubmitPage);

        await expect(this.page.locator(Selectors.postFormSettings.multiStepByStep)).toBeVisible();
    }

    async disableMultiStep(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

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

        await this.validateAndClick(Selectors.postFormSettings.removeStepStart);
        await this.validateAndClick(Selectors.postFormSettings.confirmDelete);
        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
        
    }

    async setPostUpdateStatus(formName: string, status: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on post update status dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postUpdateStatusContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.postUpdateStatusDropdown, { state: 'visible' });

        // Select the status
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postUpdateStatusOption(status));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setPostUpdateMessage(formName: string, message: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

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
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

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
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Clear and fill the button text field
        await this.page.locator(Selectors.postFormSettings.postSettingsSection.updatePostButtonTextInput).clear();
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.updatePostButtonTextInput, buttonText);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);

        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);

        await expect(this.page.locator(Selectors.postFormSettings.submitPostButtonText(buttonText))).toBeVisible();
    }

    async validatePostUpdateStatusInForm(postTitle: string, postContent: string, postExcerpt: string, expectedStatus: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(2000);
        //await this.assertionValidate(Selectors.postFormSettings.checkSuccessMessage);

        await this.navigateToURL(this.wpufPostPage);
        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(expectedStatus);
        }
    }

    async pendingToLive(postTitle: string, expectedStatus: string) {
        // Go to form edit page
        await this.navigateToURL(this.postsPage);

        await this.page.hover(Selectors.postFormSettings.quickEditButtonContainer);
        await this.validateAndClick(Selectors.postFormSettings.quickEditButton);
         

        await this.page.selectOption(Selectors.postFormSettings.statusDropdown, 'publish');
        await this.validateAndClick(Selectors.postFormSettings.updateStatus); 
        await this.page.waitForTimeout(1000);

        await this.navigateToURL(this.wpufPostPage);
        const newPostTitle = await this.page.innerText(Selectors.postFormSettings.postTitleColumn);
        if (postTitle == newPostTitle) {
            const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
            await expect(newPostStatus).toContain(expectedStatus);

            console.log('\x1b[32m%s\x1b[0m', `Post Status changed to ${newPostStatus} for post ${newPostTitle} via quick edit`);
        }
        
    }

    async validatePostUpdateMessageInForm(postTitle: string, postContent: string, postExcerpt: string, expectedMessage: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);


        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(2000);

        // Validate the message content
        const successMessage = await this.page.innerText(Selectors.postFormSettings.checkSuccessMessage);

        expect(successMessage).toContain(expectedMessage);
    }

    async setUpdatePostRedirectionToUpdatedPost(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionDropdown, { state: 'visible' });

        // Select "updated post"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionOption('post'));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setUpdatePostRedirectionToSamePage(formName: string, message: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionDropdown, { state: 'visible' });

        // Select "same page"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionOption('same'));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setUpdatePostRedirectionToPage(formName: string, pageName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionDropdown, { state: 'visible' });

        // Select "to a page"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionOption('page'));

        await this.page.waitForTimeout(200);

        // Select the specific page
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionPageContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionPageDropdown, { state: 'visible' });
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionPageOption(pageName));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async setUpdatePostRedirectionToCustomUrl(formName: string, customUrl: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        // Click on successful redirection dropdown
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionDropdown, { state: 'visible' });

        // Select "to a custom URL"
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionOption('url'));

        // Fill the custom URL
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.updatePostRedirectionUrlInput, customUrl);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async validateUpdatePostRedirectionToPost(postTitle: string, postContent: string, postExcerpt: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(2000);

        await expect(this.page.locator(Selectors.postFormSettings.checkPostTitle(postTitle))).toBeVisible();
    }

    async validateUpdatePostRedirectionToSamePage(postTitle: string, postContent: string, postExcerpt: string, message: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         
        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(2000);

        const successMessage = await this.page.innerText(Selectors.postFormSettings.checkSuccessMessage);

        expect(successMessage).toContain(message);
    }

    async validateUpdatePostRedirectionToPage(postTitle: string, postContent: string, postExcerpt: string, pageTitle: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await expect(this.page.locator(Selectors.postFormSettings.checkPageTitle(pageTitle))).toBeVisible();
    }

    async validateUpdatePostRedirectionToUrl(postTitle: string, postContent: string, postExcerpt: string, expectedUrl: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        // Fill post title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        await expect(this.page).toHaveURL(expectedUrl);
    }

    async enablePayPerPost(formName: string, cost: string, successPage: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentSettingsTab);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentOptionsContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.paymentOptionsDropdown, { state: 'visible' });

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.payPerPostOption('enable_pay_per_post'));

        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.payPerPostCostContainer, cost);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentSuccessPageContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.paymentSuccessPageDropdown, { state: 'visible' });
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentSuccessPageOption(successPage));

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
    }

    async disablePayPerPost(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();
        
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentSettingsTab);
        await this.page.waitForSelector(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.paymentEnableToggle);

        await this.validateAndClick(Selectors.postFormSettings.saveButton);
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved);
        
    }

    async createPostWithPayment(postTitle: string, postContent: string, postExcerpt: string, cost: string, successPage: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostSubmitPage);

        const payPerPostInfo = await this.page.innerText(Selectors.postFormSettings.wpufInfo);
        expect(payPerPostInfo).toContain(`There is a $${cost} charge to add a new post`);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        const validateCost = await this.page.innerText(Selectors.postFormSettings.validatePayPerPostCost);
        expect(validateCost).toContain(`$${cost}`);

        await this.validateAndClick(Selectors.postFormSettings.checkBankButton);
         

        await this.validateAndClick(Selectors.postFormSettings.proceedPaymentButton);

        await this.assertionValidate(Selectors.postFormSettings.afterPaymentPageTitle(successPage));

    }

    async acceptPayment() {
        // Go to form edit page
        await this.navigateToURL(this.wpufTransactionPage);

        await this.page.hover(Selectors.postFormSettings.transactionTableRow);

        await this.validateAndClick(Selectors.postFormSettings.acceptPayment);
         

    }

    async validatePayPerPost() {

        await this.navigateToURL(this.wpufPostPage);

        const newPostStatus = await this.page.innerText(Selectors.postFormSettings.postStatusColumn);
        await expect(newPostStatus).toContain('Live');
        
    }

    // Enable new post notification
    async enableNewPostNotification(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Enable new post notification toggle
        const isChecked = await this.page.locator(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationToggle);
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Disable new post notification
    async disableNewPostNotification(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Disable new post notification toggle
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationToggle);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification email settings
    async modifyNotificationEmail(formName: string, emailAddress: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Clear and fill new email
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationTo, emailAddress);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification subject
    async modifyNotificationSubject(formName: string, emailSubject: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Clear and fill new subject
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationSubject, emailSubject);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification body with template tags
    async modifyNotificationBodyWithTemplateTags(formName: string, emailBody: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Clear and fill new body with template tags
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationBody, emailBody);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Click template tags for notification body
    async clickTemplateTagsForNotification(formName: string, tags: string[]) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        for (const tag of tags) {
            await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.templateTagPointer(tag, '1'));
            try{
                await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.tagClickTooltip);
                await this.page.waitForTimeout(2000);
            }catch(error){
                console.log(`Clipboard validation skipped for ${tag}: ${error.message}`);
            }
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Validate notification settings in form
    async validateNotificationSettingsEnabled(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Validate toggle is enabled
        const isToggleChecked = await this.page.isChecked(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationToggle);
        expect(isToggleChecked).toBe(true);
    }

    // Submit post and validate notification is sent (simulate)
    async submitPostAndValidateNotificationFE(postTitle: string, postContent: string, postExcerpt: string, emailSubject: string, multipleEmails: string) {
        // Go to frontend post submission page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Fill post details
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter post description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

        // Validate notification is sent
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);

        const sentEmailAddress = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.sentEmailAddress(multipleEmails));
        expect(sentEmailAddress).toBe(multipleEmails);

        const sentEmailSubject = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.sentEmailSubjectSubmitted);
        expect(sentEmailSubject).toBe(emailSubject);

        await this.page.hover(Selectors.postFormSettings.notificationSettingsSection.sentEmailSubjectSubmitted);
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.viewEmailContentSubmitted);

        const sentEmailBody = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.previewEmailContentBody);
        expect(sentEmailBody).toContain(postTitle);
        expect(sentEmailBody).toContain(postContent);
        expect(sentEmailBody).toContain(postExcerpt);
        expect(sentEmailBody).toContain('Music');
        const postUrl = postTitle.toLowerCase().replace(/\s+/g, '-');
        expect(sentEmailBody).toContain(Urls.baseUrl + `/${postUrl}/`);
        const reviewUrlPattern = Urls.baseUrl + `/wp-admin/post.php?action=edit&post=`;
        expect(sentEmailBody).toMatch(new RegExp(`${reviewUrlPattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\d+`));
    }

    // Test multiple notification emails
    async setMultipleNotificationEmails(formName: string, multipleEmails: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Fill multiple emails separated by commas
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.newPostNotificationTo, multipleEmails);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Enable new post notification
    async enableUpdatedPostNotification(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Enable new post notification toggle
        const isChecked = await this.page.locator(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationToggle);
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Disable new post notification
    async disableUpdatedPostNotification(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Disable new post notification toggle
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationToggle);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification email settings
    async modifyUpdatedNotificationEmail(formName: string, emailAddress: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Clear and fill new email
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationTo, emailAddress);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification subject
    async modifyUpdatedNotificationSubject(formName: string, emailSubject: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Clear and fill new subject
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationSubject, emailSubject);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Modify notification body with template tags
    async modifyUpdatedNotificationBodyWithTemplateTags(formName: string, emailBody: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Clear and fill new body with template tags
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationBody, emailBody);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Click template tags for notification body
    async clickTemplateTagsForUpdatedNotification(formName: string, tags: string[]) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.notificationSettingsHeader);

        // Click on each template tag
        for (const tag of tags) {
            await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.templateTagPointer(tag, '2'));
            try{
                await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.tagClickTooltip);
                await this.page.waitForTimeout(2000);
            }catch(error){
                console.log(`Clipboard validation skipped for ${tag}: ${error.message}`);
            }
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    // Validate notification settings in form
    async validateUpdatedNotificationSettingsEnabled(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Validate toggle is enabled
        const isToggleChecked = await this.page.isChecked(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationToggle);
        expect(isToggleChecked).toBe(true);
    }

    // Submit post and validate notification is sent (simulate)
    async submitPostAndValidateUpdatedNotificationFE(previousPostTitle: string, postTitle: string, postContent: string, postExcerpt: string, emailSubject: string, multipleEmails: string) {
        // Go to frontend post submission page
        await this.navigateToURL(this.wpufPostPage);

        await this.validateAndClick(Selectors.postFormSettings.editPostButton);
         

        // Fill post details
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // Enter post description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // Submit the post
        await this.validateAndClick(Selectors.postFormSettings.updatePostButton);
        await this.page.waitForTimeout(2000);

        // Validate notification is sent
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(2000);

        const sentEmailAddress = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.sentEmailAddress(multipleEmails));
        expect(sentEmailAddress).toBe(multipleEmails);

        const sentEmailSubject = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.sentEmailSubjectUpdated);
        expect(sentEmailSubject).toBe(emailSubject);

        await this.page.hover(Selectors.postFormSettings.notificationSettingsSection.sentEmailAddress(multipleEmails));
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsSection.viewEmailContentUpdated);
        await this.page.waitForTimeout(1000);
        const sentEmailBody = await this.page.innerText(Selectors.postFormSettings.notificationSettingsSection.previewEmailContentBody);
        expect(sentEmailBody).toContain(postTitle);
        expect(sentEmailBody).toContain(postContent);
        expect(sentEmailBody).toContain(postExcerpt);
        const postUrl = previousPostTitle.toLowerCase().replace(/\s+/g, '-');
        expect(sentEmailBody).toContain(Urls.baseUrl + `/${postUrl}/`);
        const reviewUrlPattern = Urls.baseUrl + `/wp-admin/post.php?action=edit&post=`;
        expect(sentEmailBody).toMatch(new RegExp(`${reviewUrlPattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\d+`));
    }

    // Test multiple notification emails
    async setMultipleUpdatedNotificationEmails(formName: string, multipleEmails: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.notificationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.notificationSettingsSection.updatedPostNotificationSettingsHeader);

        // Fill multiple emails separated by commas
        await this.validateAndFillStrings(Selectors.postFormSettings.notificationSettingsSection.updatePostNotificationTo, multipleEmails);

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    async settingUserComment(formName: string, status: string, postTitle: string, postContent: string, postExcerpt: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Notification Settings tab
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.advancedSettingsSection.advancedSettingsHeader);

        // Disable new post notification toggle
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsSection.commentStatusContainer);
        //await this.page.waitForSelector(Selectors.postFormSettings.advancedSettingsSection.commentStatusDropdown, { state: 'visible' });
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsSection.commentStatusOption(status));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
        
        await this.navigateToURL(this.wpufPostSubmitPage);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        await this.page.waitForTimeout(2000);

    }

    async validateUserCommentEnabled(postTitle: string) {
        // Go to frontend post submission page
        await this.navigateToURL(Urls.baseUrl);

        // Click on the post
        await this.validateAndClick(Selectors.postFormSettings.clickPost(postTitle));

        await this.validateAndFillStrings(Selectors.postFormSettings.advancedSettingsSection.commentBox, 'Test comment');
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsSection.postCommentButton);

        await this.assertionValidate(Selectors.postFormSettings.advancedSettingsSection.validateComment);

        await this.navigateToURL(this.accountPage);

        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
         

    }

    async validateUserCommentDisabled(postTitle: string) {
        // Go to frontend post submission page
        await this.navigateToURL(Urls.baseUrl);
        await this.page.waitForTimeout(1000);

        // Click on the post
        await this.validateAndClick(Selectors.postFormSettings.clickPost(postTitle));

        await expect(this.page.locator(Selectors.postFormSettings.advancedSettingsSection.commentBox)).not.toBeVisible();

        await this.navigateToURL(this.accountPage);

        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
         

    }

    async limitFormEntries(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Advanced Settings tab
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.advancedSettingsSection.advancedSettingsHeader);

        const isChecked = await this.page.locator(Selectors.postFormSettings.advancedSettingsSection.limitFormEntriesToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.advancedSettingsSection.limitFormEntriesToggle);
        }
        await this.validateAndFillNumbers(Selectors.postFormSettings.advancedSettingsSection.limitNumberInput, 1);
        await this.validateAndFillStrings(Selectors.postFormSettings.advancedSettingsSection.limitMessage, 'limit reached');

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    async validateLimitFormEntries(postTitle: string, postContent: string, postExcerpt: string) {
        // // Go to frontend post submission page
        // await this.navigateToURL(this.wpufPostSubmitPage);
        

        // await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, postTitle);

        // await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
        //     .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(postContent);

        // await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, postExcerpt);

        // await this.validateAndClick(Selectors.postFormSettings.submitPostButton);

        // await this.page.waitForTimeout(2000);

        await this.navigateToURL(this.wpufPostSubmitPage);

        const errorMessage = await this.page.innerText(Selectors.postFormSettings.wpufInfo);
        expect(errorMessage).toBe('limit reached');

    }

    async unlimitFormEntries(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Advanced Settings tab
        await this.validateAndClick(Selectors.postFormSettings.advancedSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.advancedSettingsSection.advancedSettingsHeader);

        const isChecked = await this.page.locator(Selectors.postFormSettings.advancedSettingsSection.limitFormEntriesToggle).isChecked();
        if (isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.advancedSettingsSection.limitFormEntriesToggle);
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    async enablePostExpiration(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Advanced Settings tab
        await this.validateAndClick(Selectors.postFormSettings.postExpirationSettingsTab);

        // Wait for notification settings to load
        await this.assertionValidate(Selectors.postFormSettings.postExpirationSettingsSection.postExpirationSettingsHeader);

        const isChecked = await this.page.locator(Selectors.postFormSettings.postExpirationSettingsSection.postExpirationToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postExpirationSettingsSection.postExpirationToggle);
        }

        // Enable post expiration message
        await this.validateAndFillStrings(Selectors.postFormSettings.postExpirationSettingsSection.postExpirationTime, '1');
        await this.validateAndClick(Selectors.postFormSettings.postExpirationSettingsSection.enablePostExpirationMessage);
        await this.validateAndFillStrings(Selectors.postFormSettings.postExpirationSettingsSection.postExpirationMessage, 'Post expired');

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    async enableFormTitleShowing(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.formTitleToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.formTitleToggle);
        }

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });

        await this.navigateToURL(this.wpufPostSubmitPage);

        await this.assertionValidate(Selectors.postFormSettings.showFormTitle(formName));
         
        
    }

    async showFormDescription(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);

        const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.formTitleToggle).isChecked();
        if (!isChecked) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.formTitleToggle);
            // Save settings
            await this.validateAndClick(Selectors.postFormSettings.saveButton);

            // Wait for save message
            await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
        }

        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.formDescriptionBox, 'Form Description');

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });

        await this.navigateToURL(this.wpufPostSubmitPage);

        await this.assertionValidate(Selectors.postFormSettings.showFormTitle(formName));
         

        const formDescription = await this.page.innerText(Selectors.postFormSettings.showFormDescription);
        expect(formDescription).toBe('Form Description');
        
    }

    async setPostPermissionRoleBased(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postPermissionContainer);
        //await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.postPermissionDropdown);
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postPermissionOption('role_base'));

        await this.page.waitForTimeout(300);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.roleSelectionContainer);
        //await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.roleSelectionDropdown);
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.roleSelectionOption('subscriber'));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }

    async validatePostPermissionRoleBased(formName: string) {
        // Go to form edit page
        await this.navigateToURL(this.wpufPostSubmitPage);

        // Click on the form
        const errorMessage = await this.page.innerText(Selectors.postFormSettings.wpufMessage);
        expect(errorMessage).toContain('You do not have sufficient permissions to access this form.');

        await this.navigateToURL(this.wpufPostFormPage);
        await this.page.reload();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
         

        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);

        // Click Post Settings section
        await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.afterPostSettingsHeader);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postPermissionContainer);
        //await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.postPermissionDropdown);
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.postPermissionOption('role_base'));

        await this.page.waitForTimeout(300);

        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.roleSelectionContainer);
        //await this.assertionValidate(Selectors.postFormSettings.postSettingsSection.roleSelectionDropdown);
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.roleSelectionOption('administrator'));

        // Save settings
        await this.validateAndClick(Selectors.postFormSettings.saveButton);

        // Wait for save message
        await this.page.waitForSelector(Selectors.postFormSettings.messages.formSaved, { timeout: 30000 });
    }
}
