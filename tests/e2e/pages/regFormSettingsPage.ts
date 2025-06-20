import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Urls, Users } from '../utils/testData';
import { Selectors } from './selectors';
import { FieldOptionsCommonPage } from '../pages/fieldOptionsCommon';
import { BasicLogoutPage } from './basicLogout';
import { BasicLoginPage } from './basicLogin';

export class RegFormSettingsPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    async settingNewlyRegisteredUserRole(formName: string, role: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        // Click on the form
        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));

        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.userRoleContainer);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.userRoleDropdown);
        await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.userRoleOption(role));
        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();

    }

    async validateNewlyRegisteredUserRole(userEmail: string, userPassword: string, role: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.newRegFormPage)]);
        await this.waitForLoading();

        // Click on the form
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.assertionValidate(Selectors.regFormSettings.successMessage);

        if(role === 'subscriber'){
            await this.page.goto(this.accountPage);
            await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        }else{
            await new BasicLogoutPage(this.page).logOut();
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        }
        //Validate Registered User
        //Go to Users List
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);
        //Search Username
        await this.validateAndFillStrings(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchBox, userEmail);
        //Click Search
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchButton);

        //Validate Email present
        const validateUserCreated = await this.page.innerText(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserCreated);

        expect(validateUserCreated, `Expected user with email ${userEmail} to be found in admin`).toBe(userEmail);

        const validateUserRole = await this.page.innerText(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserRole);

        expect(validateUserRole.toLocaleLowerCase(), `Expected user with email ${userEmail} to be found in admin`).toBe(role);
        
    }

    async enableRequireApproval(formName: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        // Click on the form
        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));

        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        const isApprovalEnabled = await this.page.isChecked(Selectors.regFormSettings.regSettingsSection.approvalToggle);
        if(!isApprovalEnabled){
            await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.approvalToggle);
        }
        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async validateApprovalEnabled(userEmail: string, userPassword: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.newRegFormPage)]);
        await this.waitForLoading();
        
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.assertionValidate(Selectors.regFormSettings.successMessage);

        await this.page.goto(this.accountPage);
        const restrictionMessage = await this.page.innerText(Selectors.regFormSettings.wpufMessage);
        expect(restrictionMessage).toBe('This page is restricted. Please Log in to view this page.');
    }

    async validateLoginDenied(userEmail: string, userPassword: string) {
        await this.page.goto(this.wpAdminPage);
        await new BasicLoginPage(this.page).backendLogin(userEmail, userPassword);
        const errorLoginMessage = await this.page.innerText(Selectors.regFormSettings.wpLoginErrorMessage);
        expect(errorLoginMessage).toBe('ERROR: Your account has to be approved by an administrator before you can login.');

        await this.page.goto(this.wpufLoginPage);
        await new BasicLoginPage(this.page).frontendLogin(userEmail, userPassword);
        const errorWPUFLoginMessage = await this.page.innerText(Selectors.regFormSettings.wpLoginErrorMessage);
        expect(errorWPUFLoginMessage).toBe('ERROR: Your account has to be approved by an administrator before you can login.');
    }

    async approveNewUser(userEmail: string) {

        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersList);
        await this.validateAndFillStrings(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchBox, userEmail);
        await this.validateAndClick(Selectors.registrationForms.validateUserRegisteredAdminEnd.adminUsersSearchButton);

        await this.page.hover(Selectors.registrationForms.validateUserRegisteredAdminEnd.validateUserCreated)
        await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.approveUser);
        await new BasicLogoutPage(this.page).logOut();
    }

    async validateLoginAfterApproval(userEmail: string, userPassword: string) {
        await this.page.goto(this.wpufLoginPage);
        await new BasicLoginPage(this.page).frontendLogin(userEmail, userPassword);
        expect(this.page.url()).toBe(this.siteHomePage + '/');

        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);

    }

    // After Sign Up Settings Methods

    async setAfterRegistrationRedirectionToSamePage(formName: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('same'));

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setAfterRegistrationRedirectionToPage(formName: string, pageName: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('page'));

        await this.page.waitForTimeout(200);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageOption(pageName));

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setAfterRegistrationRedirectionToUrl(formName: string, customUrl: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('url'));

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionUrlInput, customUrl);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setRegistrationSuccessMessage(formName: string, message: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.registrationSuccessMessageInput, message);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setSubmitButtonText(formName: string, buttonText: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.submitButtonTextInput, buttonText);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setAfterProfileUpdateRedirectionToSamePage(formName: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('same'));

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setAfterProfileUpdateRedirectionToPage(formName: string, pageName: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('page'));

        await this.page.waitForTimeout(200);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageOption(pageName));

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setAfterProfileUpdateRedirectionToUrl(formName: string, customUrl: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
        await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
        await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('url'));

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionUrlInput, customUrl);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setUpdateProfileMessage(formName: string, message: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.updateProfileMessageInput, message);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    async setUpdateButtonText(formName: string, buttonText: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        
        await Promise.all([this.page.goto(this.wpufRegFormPage)]);
        await this.waitForLoading();

        await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
        await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
        await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

        await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.updateButtonTextInput, buttonText);

        await this.validateAndClick(Selectors.regFormSettings.saveButton);
        await this.assertionValidate(Selectors.regFormSettings.formSaved);

        await new BasicLogoutPage(this.page).logOut();
    }

    // Validation Methods

    async validateAfterRegistrationRedirectionToSamePage(userEmail: string, userPassword: string, expectedMessage: string) {
        await this.page.goto(this.newRegFormPage);
        await this.waitForLoading();
        
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);

        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.registrationSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
    }

    async validateAfterRegistrationRedirectionToPage(userEmail: string, userPassword: string, expectedPageTitle: string) {
        await this.page.goto(this.newRegFormPage);
        await this.waitForLoading();
        
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);

        await this.waitForLoading();
        await expect(this.page.locator(Selectors.regFormSettings.frontendValidation.afterRegPageTitle(expectedPageTitle))).toBeVisible();
    }

    async validateAfterRegistrationRedirectionToUrl(userEmail: string, userPassword: string, expectedUrl: string) {
        await this.page.goto(this.newRegFormPage);
        await this.waitForLoading();
        
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);

        await this.waitForLoading();
        await expect(this.page).toHaveURL(expectedUrl);
    }

    async validateRegistrationSuccessMessage(userEmail: string, userPassword: string, expectedMessage: string) {
        await this.page.goto(this.newRegFormPage);
        await this.waitForLoading();
        
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);

        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.registrationSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
    }

    async validateSubmitButtonText(expectedButtonText: string) {
        await this.page.goto(this.newRegFormPage);
        await this.waitForLoading();
        
        await expect(this.page.locator(Selectors.regFormSettings.submitRegisterButtonText(expectedButtonText))).toBeVisible();
    }

    async validateAfterProfileUpdateRedirectionToSamePage(firstName: string, displayName: string, expectedMessage: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);
        
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.waitForLoading();

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);
        await this.waitForLoading();
        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.updateProfileSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateAfterProfileUpdateRedirectionToPage(firstName: string, displayName: string, expectedPageTitle: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);
        
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.waitForLoading();

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);
        await this.waitForLoading();

        await expect(this.page.locator(Selectors.regFormSettings.frontendValidation.afterRegPageTitle(expectedPageTitle))).toBeVisible();

        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateAfterProfileUpdateRedirectionToUrl(firstName: string, displayName: string, expectedUrl: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);
        
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.waitForLoading();

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);

        await this.waitForLoading();
        await expect(this.page).toHaveURL(expectedUrl);
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateUpdateProfileMessage(firstName: string, displayName: string, expectedMessage: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);
        
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.waitForLoading();

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);

        await this.waitForLoading();
        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.updateProfileSuccessMessage);
        expect(successMessage).toBe(expectedMessage);

        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateUpdateButtonText(expectedButtonText: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);
        
        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);
        await this.waitForLoading();
        
        await expect(this.page.locator(Selectors.regFormSettings.submitRegisterButtonText(expectedButtonText))).toBeVisible();

        await this.page.goto(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

}