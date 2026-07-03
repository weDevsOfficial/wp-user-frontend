import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { BasicLoginPage } from '../pages/basicLogin';
import { RegFormPage } from '../pages/regForm';
import { MailPoetPage } from '../pages/mailPoet';
import { Users } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;

const formName: string = 'MailPoet Reg';
const regFormPageTitle: string = 'Reg Here';
const mailPoetList: string = 'Newsletter mailing list';
let userEmail: string = '';
let userPassword: string = '';

test.beforeAll(async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();
});

test.describe('MailPoet Registration Tests', () => {
    // Stop the rest of the file on the first failure (shared page + state).
    configureSpecFailFast();

    /**
     * @Test_Scenarios : [EMAIL MARKETING — MailPoet subscribe on registration]
     * @Test_EM0001 : Admin enables the Mailpoet 3 module
     * @Test_EM0002 : Admin creates a registration form for MailPoet subscription
     * @Test_EM0003 : Admin enables MailPoet subscription on the registration form
     * @Test_EM0004 : Visitor registers and the account is created
     * @Test_EM0005 : Registered user is added to the MailPoet mailing list
     */

    test('EM0001 : Admin enables the Mailpoet 3 module', { tag: ['@Pro', '@EmailMarketing'] }, async () => {
        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
        await new MailPoetPage(page).enableMailPoetModule();
    });

    test('EM0002 : Admin creates a registration form for MailPoet subscription', { tag: ['@Pro', '@EmailMarketing'] }, async () => {
        await new RegFormPage(page).createBlankForm_RF(formName, regFormPageTitle);
    });

    test('EM0003 : Admin enables MailPoet subscription on the registration form', { tag: ['@Pro', '@EmailMarketing'] }, async () => {
        // Still authenticated from EM0001 (shared page).
        await new MailPoetPage(page).enableMailPoetOnRegForm(formName, mailPoetList);
    });

    test('EM0004 : Visitor registers and the account is created', { tag: ['@Pro', '@EmailMarketing'] }, async () => {
        userEmail = faker.internet.email();
        userPassword = userEmail;
        await new MailPoetPage(page).registerVisitorAndValidate(userEmail, userPassword);
    });

    test('EM0005 : Registered user is added to the MailPoet mailing list', { tag: ['@Pro', '@EmailMarketing'] }, async () => {
        await new MailPoetPage(page).validateUserSubscribedToList(userEmail, mailPoetList);
    });
});
