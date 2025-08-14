import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { RegFormSettingsPage } from '../pages/regFormSettings';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';
import * as fs from "fs";
import { RegFormPage } from '../pages/regForm';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let formName: string = "";
let userEmail: string = "";
let userPassword: string = "";
let newRegFormPage: string = "Reg Here";

test.beforeAll(async () => {
    // Launch browser
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();

});

test.describe('Reg Form Settings Tests', () => {
    // Configure fail-fast behavior for this spec file
    configureSpecFailFast();

    /**----------------------------------REGISTRATION FORM SETTINGS----------------------------------**
     *
     * @TestScenario : [Registration Form Settings]
     * @Test_RFS0001 : Admin is setting newly registered user role to administrator
     * @Test_RFS0002 : Admin is validating newly registered user role to administrator
     * @Test_RFS0003 : Admin is setting newly registered user role to editor
     * @Test_RFS0004 : Admin is validating newly registered user role to editor
     * @Test_RFS0005 : Admin is setting newly registered user role to author
     * @Test_RFS0006 : Admin is validating newly registered user role to author
     * @Test_RFS0007 : Admin is setting newly registered user role to contributor
     * @Test_RFS0008 : Admin is validating newly registered user role to contributor
     * @Test_RFS0009 : Admin is setting newly registered user role to subscriber
     * @Test_RFS0010 : Admin is validating newly registered user role to subscriber
     * @Test_RFS0011 : Admin is enabling approval for new user registration
     * @Test_RFS0012 : Validating new user needs approval
     * @Test_RFS0013 : Validating new user can not login before approval
     * @Test_RFS0014 : Admin is approving new user
     * @Test_RFS0015 : Validating new user can login after approval
     * @Test_RFS0016 : Admin is setting after registration redirection to same page
     * @Test_RFS0017 : Admin is validating after registration redirection to same page
     * @Test_RFS0018 : Admin is setting after registration redirection to a page
     * @Test_RFS0019 : Admin is validating after registration redirection to a page
     * @Test_RFS0020 : Admin is setting after registration redirection to custom URL
     * @Test_RFS0021 : Admin is validating after registration redirection to custom URL
     * @Test_RFS0022 : Admin is setting registration success message
     * @Test_RFS0023 : Admin is validating registration success message
     * @Test_RFS0024 : Admin is setting submit button text
     * @Test_RFS0025 : Admin is validating submit button text
     * @Test_RFS0026 : Admin is setting after profile update redirection to same page
     * @Test_RFS0027 : Admin is validating after profile update redirection to same page
     * @Test_RFS0028 : Admin is setting after profile update redirection to a page
     * @Test_RFS0029 : Admin is validating after profile update redirection to a page
     * @Test_RFS0030 : Admin is setting after profile update redirection to custom URL
     * @Test_RFS0031 : Admin is validating after profile update redirection to custom URL
     * @Test_RFS0032 : Admin is setting update profile message
     * @Test_RFS0033 : Admin is validating update profile message
     * @Test_RFS0034 : Admin is setting update button text
     * @Test_RFS0035 : Admin is validating update button text
     * @Test_RFS0036 : Admin is enabling user notification
     * @Test_RFS0037 : Admin is setting email verification notification
     * @Test_RFS0038 : Admin is setting email verification notification subject
     * @Test_RFS0039 : Admin is setting email verification notification body
     * @Test_RFS0040 : Admin is clicking template tags for email verification notification
     * @Test_RFS0041 : User registers and validates email verification notification
     * @Test_RFS0042 : User clicks on activation link and validates email verification
     * @Test_RFS0043 : Admin is setting welcome email notification
     * @Test_RFS0044 : Admin is setting welcome email notification subject
     * @Test_RFS0045 : Admin is setting welcome email notification body
     * @Test_RFS0046 : Admin is clicking template tags for welcome email notification
     * @Test_RFS0047 : User registers and validates welcome email notification
     * @Test_RFS0048 : Admin is disabling user notification
     * @Test_RFS0049 : Admin is enabling admin notification
     * @Test_RFS0050 : Admin is setting admin notification subject
     * @Test_RFS0051 : Admin is setting admin notification message
     * @Test_RFS0052 : Admin is clicking template tags for admin notification
     * @Test_RFS0053 : User registers and validates admin notification email
     * @Test_RFS0054 : Admin is disabling admin notification
     * @Test_RFS0055 : Admin is enabling multi-step form progressbar
     * @Test_RFS0056 : Admin is validating multi-step progressbar
     * @Test_RFS0057 : Admin is enabling multi-step form by step
     * @Test_RFS0058 : Admin is validating multi-step by step
     * @Test_RFS0059 : Admin is disabling multi-step form
     */


    let activationLink: string = "";

    test('RFS0001 : Admin is setting newly registered user role to administrator', { tag: ['@Pro'] }, async () => {
        await page.waitForTimeout(15000);
        formName = 'RF Settings';
        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
        await new RegFormPage(page).createBlankForm_RF(formName, newRegFormPage);
        await new SettingsSetupPage(page).changeSettingsSetEditProfilePageDefault(formName);
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.settingNewlyRegisteredUserRole(formName, 'administrator');
    });

    test('RFS0002 : Admin is validating newly registered user role to administrator', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateNewlyRegisteredUserRole(userEmail, userPassword, 'administrator');
    });

    test('RFS0003 : Admin is setting newly registered user role to editor', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.settingNewlyRegisteredUserRole(formName, 'editor');
    });

    test('RFS0004 : Admin is validating newly registered user role to editor', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateNewlyRegisteredUserRole(userEmail, userPassword, 'editor');
    });

    test('RFS0005 : Admin is setting newly registered user role to author', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.settingNewlyRegisteredUserRole(formName, 'author');
    });

    test('RFS0006 : Admin is validating newly registered user role to author', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateNewlyRegisteredUserRole(userEmail, userPassword, 'author');
    });

    test('RFS0007 : Admin is setting newly registered user role to contributor', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.settingNewlyRegisteredUserRole(formName, 'contributor');
    });

    test('RFS0008 : Admin is validating newly registered user role to contributor', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateNewlyRegisteredUserRole(userEmail, userPassword, 'contributor');
    });

    test('RFS0009 : Admin is setting newly registered user role to subscriber', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.settingNewlyRegisteredUserRole(formName, 'subscriber');
    });

    test('RFS0010 : Admin is validating newly registered user role to subscriber', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateNewlyRegisteredUserRole(userEmail, userPassword, 'subscriber');
    });

    test('RFS0011 : Admin is enabling approval for new user registration', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.enableRequireApproval(formName);
    });

    test('RFS0012 : Validating new user needs approval', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateApprovalEnabled(userEmail, userPassword);
    });

    test('RFS0013 : Validating new user can not login before approval', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateLoginDenied(userEmail, userPassword);
    });

    test('RFS0014 : Admin is approving new user', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.approveNewUser(userEmail);
    });

    test('RFS0015 : Validating new user can login after approval', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateLoginAfterApproval(userEmail, userPassword);
    });

    test('RFS0016 : Admin is setting after registration redirection to same page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.setAfterRegistrationRedirectionToSamePage(formName);
    });

    test('RFS0017 : Admin is validating after registration redirection to same page', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedMessage = 'Registration successful. Please wait for admin approval';
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateAfterRegistrationRedirectionToSamePage(userEmail, userPassword, expectedMessage);
    });

    test('RFS0018 : Admin is setting after registration redirection to a page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.setAfterRegistrationRedirectionToPage(formName, 'Thank You');
    });

    test('RFS0019 : Admin is validating after registration redirection to a page', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedPageTitle = 'Thank You';
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateAfterRegistrationRedirectionToPage(userEmail, userPassword, expectedPageTitle);
    });

    test('RFS0020 : Admin is setting after registration redirection to custom URL', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const customUrl = Urls.baseUrl + '/thank-you/';
        await regFormSettings.setAfterRegistrationRedirectionToUrl(formName, customUrl);
    });

    test('RFS0021 : Admin is validating after registration redirection to custom URL', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedUrl = Urls.baseUrl + '/thank-you/';
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateAfterRegistrationRedirectionToUrl(userEmail, userPassword, expectedUrl);
        await regFormSettings.setAfterRegistrationRedirectionToSamePage(formName);
    });

    test('RFS0022 : Admin is setting registration success message', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const customMessage = 'Registration successful. Please wait for admin approval';
        await regFormSettings.setRegistrationSuccessMessage(formName, customMessage);
    });

    test('RFS0023 : Admin is validating registration success message', { tag: ['@Pro'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedMessage = 'Registration successful. Please wait for admin approval';
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateRegistrationSuccessMessage(userEmail, userPassword, expectedMessage);
    });

    test('RFS0024 : Admin is setting submit button text', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const buttonText = 'Join Now';
        await regFormSettings.setSubmitButtonText(formName, buttonText);
    });

    test('RFS0025 : Admin is validating submit button text', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const expectedButtonText = 'Join Now';
        await regFormSettings.validateSubmitButtonText(expectedButtonText);
    });

    test('RFS0026 : Admin is setting after profile update redirection to same page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.setAfterProfileUpdateRedirectionToSamePage(formName);
    });

    test('RFS0027 : Admin is validating after profile update redirection to same page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const firstName = faker.person.firstName();
        const displayName = faker.word.words(1);
        const expectedMessage = 'Profile updated successfully';
        await regFormSettings.validateAfterProfileUpdateRedirectionToSamePage(firstName, displayName, expectedMessage);
    });

    test('RFS0028 : Admin is setting after profile update redirection to a page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.setAfterProfileUpdateRedirectionToPage(formName, 'Thank You');
    });

    test('RFS0029 : Admin is validating after profile update redirection to a page', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const firstName = faker.person.firstName();
        const displayName = faker.word.words(1);
        const expectedPageTitle = 'Thank You';
        await regFormSettings.validateAfterProfileUpdateRedirectionToPage(firstName, displayName, expectedPageTitle);
    });

    test('RFS0030 : Admin is setting after profile update redirection to custom URL', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const customUrl = Urls.baseUrl + '/dashboard/';
        await regFormSettings.setAfterProfileUpdateRedirectionToUrl(formName, customUrl);
    });

    test('RFS0031 : Admin is validating after profile update redirection to custom URL', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const firstName = faker.person.firstName();
        const displayName = faker.word.words(1);
        const expectedUrl = Urls.baseUrl + '/dashboard/';
        await regFormSettings.validateAfterProfileUpdateRedirectionToUrl(firstName, displayName, expectedUrl);
        await regFormSettings.setAfterProfileUpdateRedirectionToSamePage(formName);
    });

    test('RFS0032 : Admin is setting update profile message', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const customMessage = 'Great! Your profile information has been saved and updated successfully.';
        await regFormSettings.setUpdateProfileMessage(formName, customMessage);
    });

    test('RFS0033 : Admin is validating update profile message', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const firstName = faker.person.firstName();
        const displayName = faker.word.words(1);
        const expectedMessage = 'Great! Your profile information has been saved and updated successfully.';
        await regFormSettings.validateUpdateProfileMessage(firstName, displayName, expectedMessage);
    });

    test('RFS0034 : Admin is setting update button text', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const buttonText = 'Save Changes';
        await regFormSettings.setUpdateButtonText(formName, buttonText);
    });

    test('RFS0035 : Admin is validating update button text', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const expectedButtonText = 'Save Changes';
        await regFormSettings.validateUpdateButtonText(expectedButtonText);
    });

    test('RFS0036 : Admin is enabling user notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.enableUserNotification(formName);
    });

    test('RFS0037 : Admin is setting email verification notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const body = 'Congrats {username}! You are Successfully registered to {blogname}. To activate your account, please click the link below {activation_link} Thanks!';
        await regFormSettings.setEmailVerificationNotification(formName);
    });

    test.skip('RFS0038 : Admin is setting email verification notification subject', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const subject = 'Verify Your Email Address';
        await regFormSettings.setEmailVerificationNotificationSubject(formName, subject);
    });

    test.skip('RFS0039 : Admin is setting email verification notification body', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const body = 'Congrats {username}! You are Successfully registered to {blogname}. To activate your account, please click the link below {activation_link} Thanks!';
        await regFormSettings.setEmailVerificationNotificationBody(formName, body);
    });

    test.skip('RFS0040 : Admin is clicking template tags for email verification notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const tags = ['{username}', '{blogname}', '{activation_link}'];
        await regFormSettings.clickTemplateTagsForEmailVerificationNotification(formName, tags);
    });

    test.skip('RFS0041 : User registers and validates email verification notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedSubject = 'Verify Your Email Address';
        activationLink = await regFormSettings.registerUserAndValidateEmailVerification(userEmail, userPassword, expectedSubject);
    });

    test.skip('RFS0042 : User clicks on activation link and validates email verification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.approveNewUser(userEmail);
        await regFormSettings.validateEmailVerification(activationLink, userEmail, userPassword);
    });

    test.skip('RFS0043 : Admin is setting welcome email notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.setWelcomeEmailNotification(formName);
    });

    test.skip('RFS0044 : Admin is setting welcome email notification subject', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const subject = 'Welcome to Our Platform!';
        await regFormSettings.setWelcomeEmailNotificationSubject(formName, subject);
    });

    test.skip('RFS0045 : Admin is setting welcome email notification body', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const body = 'Hi {username}, Congrats! You are Successfully registered to {blogname}. Thanks';
        await regFormSettings.setWelcomeEmailNotificationBody(formName, body);
    });

    test.skip('RFS0046 : Admin is clicking template tags for welcome email notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const tags = ['{username}', '{blogname}'];
        await regFormSettings.clickTemplateTagsForWelcomeEmailNotification(formName, tags);
    });

    test.skip('RFS0047 : User registers and validates welcome email notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedSubject = 'Welcome to Our Platform!';
        await regFormSettings.registerUserAndValidateWelcomeEmail(userEmail, userPassword, expectedSubject);
    });

    test('RFS0048 : Admin is disabling user notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.disableUserNotification(formName);
    });

    test('RFS0049 : Admin is enabling admin notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.enableAdminNotification(formName);
    });
    test('RFS0050 : Admin is setting admin notification subject', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const subject = 'New User Registration Alert';
        await regFormSettings.setAdminNotificationSubject(formName, subject);
    });

    test('RFS0051 : Admin is setting admin notification message', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const message = 'A new user {username} with email {user_email} has registered on your site.';
        await regFormSettings.setAdminNotificationMessage(formName, message);
    });

    test('RFS0052 : Admin is clicking template tags for admin notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        const tags = ['{username}'];
        await regFormSettings.clickTemplateTagsForAdminNotification(formName, tags);
    });

    test('RFS0053 : User registers and validates admin notification email', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        userEmail = faker.internet.email();
        userPassword = userEmail;
        const expectedSubject = 'New User Registration Alert';
        await regFormSettings.registerUserAndValidateAdminNotification(userEmail, userPassword, expectedSubject);
    });

    test('RFS0054 : Admin is disabling admin notification', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.disableAdminNotification(formName);
    });

    test('RFS0055 : Admin is enabling multi-step form progressbar', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.enableMultiStepProgressbar(formName);
    });

    test('RFS0056 : Admin is validating multi-step progressbar', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateMultiStepProgessbar();
    });

    test('RFS0057 : Admin is enabling multi-step form by step', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.enableMultiStepByStep(formName);
    });

    test('RFS0058 : Admin is validating multi-step by step', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.validateMultiStepByStep();
    });

    test('RFS0059 : Admin is disabling multi-step form', { tag: ['@Pro'] }, async () => {
        const regFormSettings = new RegFormSettingsPage(page);
        await regFormSettings.disableMultiStep(formName);
    });
});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});