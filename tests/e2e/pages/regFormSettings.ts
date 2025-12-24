import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Users } from '../utils/testData';
import { Selectors } from './selectors';
import { BasicLogoutPage } from './basicLogout';
import { BasicLoginPage } from './basicLogin';

export class RegFormSettingsPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    async settingNewlyRegisteredUserRole(formName: string, role: string) {

        let flag = true;

        while (flag == true) {
            // Go to form edit page
            await this.navigateToURL(this.wpufRegFormPage);

            // Click on the form
            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.userRoleContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.userRoleDropdown);
            await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.userRoleOption(role));
            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }

    }

    async validateNewlyRegisteredUserRole(userEmail: string, userPassword: string, role: string) {
        // Go to form edit page
        await this.navigateToURL(this.newRegFormPage);

        // Click on the form
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        await this.assertionValidate(Selectors.regFormSettings.successMessage);

        if (role === 'subscriber') {
            await this.navigateToURL(this.accountPage);
            await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
        } else {
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

        let flag = true;

        while (flag == true) {
            // Go to form edit page
            await this.navigateToURL(this.wpufRegFormPage);
            // Click on the form
            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            const isApprovalEnabled = await this.page.locator(Selectors.regFormSettings.regSettingsSection.approvalToggle).isChecked();
            if (!isApprovalEnabled) {
                await this.validateAndClick(Selectors.regFormSettings.regSettingsSection.approvalToggle);
            }
            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

            if (flag == false) {
                await this.navigateToURL(this.wpufRegFormPage);
                // Click on the form
                try {
                    await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
                } catch (error) {
                    await this.navigateToURL(this.wpufRegFormPage);
                    await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
                }

                await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

                await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

                const validateApprovalEnabled = await this.page.locator(Selectors.regFormSettings.regSettingsSection.approvalToggle).isChecked();
                if(validateApprovalEnabled == false) {
                    flag = true;
                }
            }

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
            await this.page.waitForTimeout(2000);
        }
    }

    async validateApprovalEnabled(userEmail: string, userPassword: string) {
        // Go to form edit page
        await this.navigateToURL(this.newRegFormPage);

        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        await this.assertionValidate(Selectors.regFormSettings.successMessage);

        await this.navigateToURL(this.accountPage);
        const restrictionMessage = await this.page.innerText(Selectors.regFormSettings.wpufMessage);
        expect.soft(restrictionMessage).toBe('This page is restricted. Please Log in to view this page.');
    }

    async validateLoginDenied(userEmail: string, userPassword: string) {
        await this.navigateToURL(this.wpAdminPage);
        await new BasicLoginPage(this.page).backendLogin(userEmail, userPassword);
        const errorLoginMessage = await this.page.innerText(Selectors.regFormSettings.wpLoginErrorMessage);
        expect(errorLoginMessage).toBe('ERROR: Your account has to be approved by an administrator before you can login.');

        await this.navigateToURL(this.wpufLoginPage);
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
        await this.navigateToURL(this.wpufLoginPage);
        await new BasicLoginPage(this.page).frontendLogin(userEmail, userPassword);
        expect(this.page.url()).toBe(this.siteHomePage + '/');

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);

    }

    // After Sign Up Settings Methods

    async setAfterRegistrationRedirectionToSamePage(formName: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('same'));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setAfterRegistrationRedirectionToPage(formName: string, pageName: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('page'));

            await this.page.waitForTimeout(500);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionPageOption(pageName));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setAfterRegistrationRedirectionToUrl(formName: string, customUrl: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionOption('url'));

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.afterRegistrationRedirectionUrlInput, customUrl);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setRegistrationSuccessMessage(formName: string, message: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.registrationSuccessMessageInput, message);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setSubmitButtonText(formName: string, buttonText: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.submitButtonTextInput, buttonText);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setAfterProfileUpdateRedirectionToSamePage(formName: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('same'));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setAfterProfileUpdateRedirectionToPage(formName: string, pageName: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('page'));

            await this.page.waitForTimeout(200);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionPageOption(pageName));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setAfterProfileUpdateRedirectionToUrl(formName: string, customUrl: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionDropdown);
            await this.validateAndClick(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionOption('url'));

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.afterProfileUpdateRedirectionUrlInput, customUrl);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setUpdateProfileMessage(formName: string, message: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.updateProfileMessageInput, message);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setUpdateButtonText(formName: string, buttonText: string) {

        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            await this.validateAndFillStrings(Selectors.regFormSettings.afterSignUpSettingsSection.updateButtonTextInput, buttonText);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);

        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    // Validation Methods

    async validateAfterRegistrationRedirectionToSamePage(userEmail: string, userPassword: string, expectedMessage: string) {
        await this.navigateToURL(this.newRegFormPage);

        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);

        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.registrationSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
    }

    async validateAfterRegistrationRedirectionToPage(userEmail: string, userPassword: string, expectedPageTitle: string) {
        await this.navigateToURL(this.newRegFormPage);

        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        await expect(this.page.locator(Selectors.regFormSettings.frontendValidation.afterRegPageTitle(expectedPageTitle))).toBeVisible();
    }

    async validateAfterRegistrationRedirectionToUrl(userEmail: string, userPassword: string, expectedUrl: string) {
        await this.navigateToURL(this.newRegFormPage);

        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        await expect(this.page).toHaveURL(expectedUrl);
    }

    async validateRegistrationSuccessMessage(userEmail: string, userPassword: string, expectedMessage: string) {
        await this.navigateToURL(this.newRegFormPage);

        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.registrationSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
    }

    async validateSubmitButtonText(expectedButtonText: string) {
        await this.navigateToURL(this.newRegFormPage);

        await expect(this.page.locator(Selectors.regFormSettings.submitRegisterButtonText(expectedButtonText))).toBeVisible();
    }

    async validateAfterProfileUpdateRedirectionToSamePage(firstName: string, displayName: string, expectedMessage: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);
        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.updateProfileSuccessMessage);
        expect(successMessage).toBe(expectedMessage);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateAfterProfileUpdateRedirectionToPage(firstName: string, displayName: string, expectedPageTitle: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);

        await expect(this.page.locator(Selectors.regFormSettings.frontendValidation.afterRegPageTitle(expectedPageTitle))).toBeVisible();

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateAfterProfileUpdateRedirectionToUrl(firstName: string, displayName: string, expectedUrl: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);

        await expect(this.page).toHaveURL(expectedUrl);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateUpdateProfileMessage(firstName: string, displayName: string, expectedMessage: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);

        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.firstNameField, firstName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.displayNameField, displayName);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.newPasswordField, Users.userPassword);
        await this.validateAndFillStrings(Selectors.regFormSettings.frontendValidation.confirmPasswordField, Users.userPassword);
        await this.validateAndClick(Selectors.regFormSettings.frontendValidation.updateProfileSubmitButton);

        const successMessage = await this.page.innerText(Selectors.regFormSettings.frontendValidation.updateProfileSuccessMessage);
        expect(successMessage).toBe(expectedMessage);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async validateUpdateButtonText(expectedButtonText: string) {
        await new BasicLoginPage(this.page).basicLogin(Users.userName, Users.userPassword);

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.settingsSetup.accountPageTabs.editProfileTab);

        await expect(this.page.locator(Selectors.regFormSettings.submitRegisterButtonText(expectedButtonText))).toBeVisible();

        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async enableUserNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Enable User Notification
            const isUserNotificationEnabled = await this.page.isChecked(Selectors.regFormSettings.notificationSettingsSection.enableUserNotificationToggle);
            if (!isUserNotificationEnabled) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.enableUserNotificationToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    // Notification Settings Methods

    async setEmailVerificationNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);
            // Select Email Verification
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.emailVerificationRadio);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setEmailVerificationNotificationSubject(formName: string, subject: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Set Subject and Body
            await this.validateAndFillStrings(Selectors.regFormSettings.notificationSettingsSection.confirmationEmailSubjectInput, subject);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setEmailVerificationNotificationBody(formName: string, body: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Handle iframe for rich text editor
            await this.page.frameLocator(Selectors.regFormSettings.notificationSettingsSection.confirmationEmailBodyTextarea)
                .locator(Selectors.regFormSettings.notificationSettingsSection.textareaBody).fill(body);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    // Click template tags for notification body
    async clickTemplateTagsForEmailVerificationNotification(formName: string, tags: string[]) {
        let flag = true;

        while (flag == true) {
            // Go to form edit page
            await this.navigateToURL(this.wpufRegFormPage);

            // Click on the form
            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            // Click Settings tab
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

            // Click Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);

            // Wait for notification settings to load
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Click on each template tag
            for (const tag of tags) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.templateTagPointer(tag, '1'));
                try {
                    await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.tagClickTooltip);
                    await this.page.waitForTimeout(2000);
                } catch (error) {
                    console.log(`Clipboard validation skipped for ${tag}: ${error.message}`);
                }
            }

            // Save settings
            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async setWelcomeEmailNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Select Welcome Email
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.welcomeEmailRadio);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setWelcomeEmailNotificationSubject(formName: string, subject: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Set Subject and Body
            await this.validateAndFillStrings(Selectors.regFormSettings.notificationSettingsSection.welcomeEmailSubjectInput, subject);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setWelcomeEmailNotificationBody(formName: string, body: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Handle iframe for rich text editor
            await this.page.frameLocator(Selectors.regFormSettings.notificationSettingsSection.welcomeEmailBodyTextarea)
                .locator(Selectors.regFormSettings.notificationSettingsSection.textareaBody).fill(body);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async clickTemplateTagsForWelcomeEmailNotification(formName: string, tags: string[]) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            for (const tag of tags) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.templateTagPointer(tag, '2'));
                try {
                    await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.tagClickTooltip);
                    await this.page.waitForTimeout(2000);
                } catch (error) {
                    console.log(`Clipboard validation skipped for ${tag}: ${error.message}`);
                }
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async enableAdminNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Enable Admin Notification
            const isAdminNotificationEnabled = await this.page.isChecked(Selectors.regFormSettings.notificationSettingsSection.enableAdminNotificationToggle);
            if (!isAdminNotificationEnabled) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.enableAdminNotificationToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setAdminNotificationSubject(formName: string, subject: string) {
        let flag = true;

        while (flag == true) {

            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Set Subject and Message
            await this.validateAndFillStrings(Selectors.regFormSettings.notificationSettingsSection.adminNotificationSubjectInput, subject);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async setAdminNotificationMessage(formName: string, message: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Set Message
            await this.validateAndFillStrings(Selectors.regFormSettings.notificationSettingsSection.adminNotificationMessageTextarea, message);

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async clickTemplateTagsForAdminNotification(formName: string, tags: string[]) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);

            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            for (const tag of tags) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.templateTagPointer(tag, '3'));
                try {
                    await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.tagClickTooltip);
                    await this.page.waitForTimeout(2000);
                } catch (error) {
                    console.log(`Clipboard validation skipped for ${tag}: ${error.message}`);
                }
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async registerUserAndValidateEmailVerification(userEmail: string, userPassword: string, expectedSubject: string) {
        await this.navigateToURL(this.newRegFormPage);

        // Fill registration form
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        // Validate registration success message
        const successMessage = await this.page.innerText(Selectors.regFormSettings.successMessage);
        expect(successMessage).toContain('Please check your email for activation link');

        // Login as admin to check WP Mail Log
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        // Navigate to WP Mail Log
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.regFormSettings.wpMailLogValidation.wpMailLogPage);

        // Check the latest email

        // Validate email recipient
        const emailTo = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentEmailAddress(userEmail));
        expect(emailTo).toContain(userEmail);

        // Validate email subject
        const emailSubject = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentEmailSubject(expectedSubject));
        expect(emailSubject).toBe(expectedSubject);

        // View email content to validate body
        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.viewEmailContent(expectedSubject));

        const activationLink = await this.page.locator(Selectors.regFormSettings.wpMailLogValidation.grabActivationLink).getAttribute('href');
        //expect(emailBody).toContain(expectedBodyContent);

        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.modalCloseButton);



        await new BasicLogoutPage(this.page).logOut();

        return activationLink;
    }

    async validateEmailVerification(activationLink: string, userEmail: string, userPassword: string) {
        await this.navigateToURL(activationLink);
        await new BasicLoginPage(this.page).backendLogin(userEmail, userPassword);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
    }

    async registerUserAndValidateWelcomeEmail(userEmail: string, userPassword: string, expectedSubject: string) {
        await this.navigateToURL(this.newRegFormPage);

        // Fill registration form
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        // Validate registration success message
        const successMessage = await this.page.innerText(Selectors.regFormSettings.successMessage);
        expect(successMessage).toContain('Welcome! Your account has been created successfully. Please check your email for further instruction.');

        // Login as admin to check WP Mail Log
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        // Navigate to WP Mail Log
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.regFormSettings.wpMailLogValidation.wpMailLogPage);

        // Check the latest email

        // Validate email recipient
        const emailTo = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentEmailAddress(userEmail));
        expect(emailTo).toContain(userEmail);

        // Validate email subject
        const emailSubject = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentEmailSubject(expectedSubject));
        expect(emailSubject).toBe(expectedSubject);

        // View email content to validate body
        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.viewEmailContent(expectedSubject));

        const emailBody = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.previewEmailContentBody);
        expect(emailBody).toContain('Congrats! You are Successfully registered to');

        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.modalCloseButton);
    }

    async disableUserNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Disable User Notification
            const isUserNotificationEnabled = await this.page.isChecked(Selectors.regFormSettings.notificationSettingsSection.enableUserNotificationToggle);
            if (isUserNotificationEnabled) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.enableUserNotificationToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

    async registerUserAndValidateAdminNotification(userEmail: string, userPassword: string, expectedSubject: string) {
        await this.navigateToURL(this.newRegFormPage);

        // Fill registration form
        await this.page.fill(Selectors.regFormSettings.inputEmail, userEmail);
        await this.page.fill(Selectors.regFormSettings.inputPassword, userPassword);
        await this.page.fill(Selectors.regFormSettings.inputConfirmPassword, userPassword);
        await this.validateAndClick(Selectors.regFormSettings.submitRegisterButton);
        await this.page.waitForTimeout(2000);
        // Validate registration success message
        const successMessage = await this.page.innerText(Selectors.regFormSettings.successMessage);
        expect(successMessage).toContain('Welcome! Your account has been created successfully. Please check your email for further instruction.');

        // Login as admin to check WP Mail Log
        await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);

        // Navigate to WP Mail Log
        await this.navigateToURL(this.wpMailLogPage);
        await this.page.waitForTimeout(1000);
        await this.assertionValidate(Selectors.regFormSettings.wpMailLogValidation.wpMailLogPage);

        // Validate email subject
        const emailSubject = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.sentLatestEmailSubject(expectedSubject));
        expect(emailSubject).toBe(expectedSubject);

        // View email content to validate body
        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.viewLatestEmailContent(expectedSubject));

        const emailBody = await this.page.innerText(Selectors.regFormSettings.wpMailLogValidation.previewEmailContentBody);
        expect(emailBody).toContain(userEmail); // Admin notification should contain user info

        await this.validateAndClick(Selectors.regFormSettings.wpMailLogValidation.modalCloseButton);
    }

    async disableAdminNotification(formName: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }
            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Notification Settings tab
            await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.notificationSettingsSection.notificationSettingsHeader);

            // Disable Admin Notification
            const isAdminNotificationEnabled = await this.page.isChecked(Selectors.regFormSettings.notificationSettingsSection.enableAdminNotificationToggle);
            if (isAdminNotificationEnabled) {
                await this.validateAndClick(Selectors.regFormSettings.notificationSettingsSection.enableAdminNotificationToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

    }

    async enableMultiStepProgressbar(formName: string) {
        let flag = true;

        while (flag == true) {
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Multi-Step Settings tab
            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsHeader);

            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepSettingsHeader);

            // Check if multi-step is already enabled
            const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle).isChecked();
            if (!isChecked) {
                await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeDropdown);
            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeOption('progressive'));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
        if (flag == false) {
            flag = true;

            while (flag == true) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.page.reload();

                try {
                    await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
                } catch (error) {
                    await this.navigateToURL(this.wpufRegFormPage);
                    await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
                }

                await this.validateAndClick(Selectors.regFormSettings.addCustomFields_Common.customFieldsStepStart);
                await this.validateAndClick(Selectors.regFormSettings.addCustomFields_Common.customFieldsText);
                await this.validateAndClick(Selectors.regFormSettings.addCustomFields_Common.customFieldsUrl);

                await this.validateAndClick(Selectors.regFormSettings.saveButton);
                flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
            }
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async validateMultiStepProgessbar() {

        await this.navigateToURL(this.newRegFormPage);

        await expect(this.page.locator(Selectors.regFormSettings.advancedSettingsSection.multiStepProgressbar)).toBeVisible();
    }

    async enableMultiStepByStep(formName: string) {
        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Multi-Step Settings tab
            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsHeader);

            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepSettingsHeader);

            // Check if multi-step is already enabled
            const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle).isChecked();
            if (!isChecked) {
                await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeContainer);
            await this.page.waitForTimeout(500);
            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeDropdown);
            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.multiStepTypeOption('step_by_step'));

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }

        if (flag == false) {
            await new BasicLogoutPage(this.page).logOut();
        }
    }

    async validateMultiStepByStep() {
        await this.navigateToURL(this.newRegFormPage);

        await expect(this.page.locator(Selectors.regFormSettings.advancedSettingsSection.multiStepByStep)).toBeVisible();
    }

    async disableMultiStep(formName: string) {
        let flag = true;

        while (flag == true) {
            await new BasicLoginPage(this.page).basicLogin(Users.adminUsername, Users.adminPassword);
            await this.navigateToURL(this.wpufRegFormPage);

            try {
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            } catch (error) {
                await this.navigateToURL(this.wpufRegFormPage);
                await this.validateAndClick(Selectors.regFormSettings.clickForm(formName));
            }

            await this.validateAndClick(Selectors.regFormSettings.clickFormEditorSettings);
            await this.assertionValidate(Selectors.regFormSettings.regSettingsSection.regSettingsHeader);

            // Navigate to Multi-Step Settings tab
            await this.validateAndClick(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsTab);
            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.advancedSettingsHeader);

            await this.assertionValidate(Selectors.regFormSettings.advancedSettingsSection.multiStepSettingsHeader);

            // Check if multi-step is already enabled
            const isChecked = await this.page.locator(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle).isChecked();
            if (isChecked) {
                await this.validateAndClick(Selectors.postFormSettings.postSettingsSection.enableMultiStepToggle);
            }

            await this.validateAndClick(Selectors.regFormSettings.saveButton);
            flag = await this.waitForFormSaved(Selectors.regFormSettings.formSaved, Selectors.regFormSettings.saveButton);
        }
    }

}