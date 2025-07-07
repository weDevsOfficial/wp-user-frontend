import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormProPage } from '../pages/postFormPro';
import { FieldAddProPage } from '../pages/fieldAddPro';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie
import { BasicLogoutPage } from '../pages/basicLogout';
import { faker } from '@faker-js/faker';

export default function postFormTestPro() {

    let browser: Browser;
    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));

        // Launch browser
        browser = await chromium.launch();

        // Create a single context
        context = await browser.newContext();

        // Create a single page
        page = await context.newPage();
    });


    test.describe('Post-Forms Pro', () => {
        /**----------------------------------POSTFORMS----------------------------------**
         *
         * @TestScenario : [Post-Forms]
         * @Test_PF0001 : Admin is creating a Blank Post Form with all Fields
         * @Test_PF0002 : Admin is Updating Settings with default Post Form
         * @Test_PF0003 : User is Creating Post from Frontend
         * @Test_PF0004 : User is Validating Post created
         * @Test_PF0005 : User is validating Entered Data for Created Post
         *
         */

        //TODO: Create a BeforeAll for login

        //Log into Admin Dashboard
        test.beforeAll(async () => {
            const BasicLogin = new BasicLoginPage(page);
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        });

        test('PF0001 : Admin is creating a Blank Post Form with all Fields', { tag: ['@Pro'] }, async () => {
            const PostFormPro = new PostFormProPage(page);
            const FieldAddPro = new FieldAddProPage(page);

            PostForm.formName = faker.word.words(3);
            //Post Blank Form
            await PostFormPro.createBlankFormPostFormPro(PostForm.formName);
            //PostFields + Validate
            await FieldAddPro.addPostFields_PF_pro();
            await FieldAddPro.validatePostFields_PF_pro();
            //CustomFields + Validate
            await FieldAddPro.addCustomFields_Common_pro();
            await FieldAddPro.validateCustomFields_Common_pro();
            //Others + Validate
            await FieldAddPro.addOthers_Common_pro();
            await FieldAddPro.validateOthers_Common_pro();
            //await FieldOptionsCommon.setMultiStepSettings_Common();

            //Save
            await FieldAddPro.saveForm_Common_pro(PostForm.formName);
            //Validate
            await FieldAddPro.validatePostFormCreatedPro(PostForm.formName);
        });

        test('PF0002 : Admin is Updating Settings with default Post Form', { tag: ['@Pro'] }, async () => {
            const SettingsSetup = new SettingsSetupPage(page);

            await SettingsSetup.changeSettingsSetDefaultPostForm(PostForm.formName);

            await new BasicLogoutPage(page).logOut();
        });


        test('PF0003 : User is Submitting Form from Frontend', { tag: ['@Pro'] }, async () => {
            const PostFormPro = new PostFormPage(page);

            await new BasicLoginPage(page).basicLogin(Users.userEmail, Users.userPassword);

            await PostFormPro.createPostFE();
        });

        test('PF0004 : User is Validating Post created', { tag: ['@Lite'] }, async () => {
            const PostFormClass = new PostFormPage(page);
            
            await PostFormClass.validatePostCreated();
            
        });

        test('PF0005 : User is validating Entered Data for Created Post', { tag: ['@Pro'] }, async () => {
            const PostFormClass = new PostFormPage(page);

            await PostFormClass.validateEnteredData();
        });

    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });


}