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
    async changePostType(postType: string) {
        // Click Settings tab
        await this.validateAndClick(Selectors.postFormSettings.clickFormEditorSettings);
        
        // Wait for settings to load
        await this.page.waitForTimeout(1000);
        
        // Click Post Settings section
        await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.beforePostSettingsHeader);
        
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
    async validatePostTypeInList(formName: string, expectedPostType: string) {
        // Go to post forms list
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms', { waitUntil: 'domcontentloaded' }),
        ]);

        // Find the row containing the form name
        const postTypeText = await this.page.innerText(Selectors.postFormSettings.postTypeColumn);
        
        // Verify post type matches expected
        expect(postTypeText.toLowerCase()).toContain(expectedPostType.toLowerCase());
    }

    // Configure form template
    async configureFormTemplate(usePostTemplate: boolean = true) {
        if (usePostTemplate) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.formTemplatePostRadio);
        }
    }

    // Enable multi-step form
    async enableMultiStepForm(enable: boolean = true) {
        const currentState = await this.page.isChecked(Selectors.postFormSettings.postSettingsSection.enableMultistepToggle);
        if (currentState !== enable) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultistepToggle);
        }
    }

    // Configure draft settings
    async enableDraftSaving(enable: boolean = true) {
        const currentState = await this.page.isChecked(Selectors.postFormSettings.postSettingsSection.draftPostToggle);
        if (currentState !== enable) {
            await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.draftPostToggle);
        }
    }

    // Set submit button text
    async setSubmitButtonText(text: string) {
        await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.submitButtonText, text);
    }

    // Set post status
    async setPostStatus(status: string) {
        await this.page.selectOption(Selectors.postFormSettings.postSettingsSection.postStatusSelect, status);
    }

    // Configure redirection
    async configureRedirection(redirectType: 'post' | 'page' | 'url', value?: string) {
        await this.page.selectOption(Selectors.postFormSettings.postSettingsSection.redirectToSelect, redirectType);
        
        if (redirectType === 'page' && value) {
            await this.page.selectOption(Selectors.postFormSettings.postSettingsSection.pageSelect, value);
        } else if (redirectType === 'url' && value) {
            await this.validateAndFillStrings(Selectors.postFormSettings.postSettingsSection.customUrlInput, value);
        }
    }
}
