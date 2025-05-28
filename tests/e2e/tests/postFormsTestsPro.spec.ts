import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormsProPage } from '../pages/postFormsPro';
import { FieldOptionsCommonProPage } from '../pages/fieldOptionsCommonPro';
import { PostFormsFrontendPage } from '../pages/postFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie

export default function postFormsTestsPro() {

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


    test.describe('Post-Forms @Pro :-->', () => {
        /**----------------------------------POSTFORMS----------------------------------**
         *
         * @TestScenario : [Post-Forms]
         * @Test_PF0001_PRO : Admin is creating Blank Form with > PF + CustomFields Pro
         * @Test_PF0002_PRO : Admin is creating Blank Form with > PF + Others Pro
         * @Test_PF0003_PRO : Admin is creating a Blank Post Form with all Pro Fields
         * @Test_PF0004_PRO : Admin is creating a Preset Post Form - with Guest Enabled Pro
         * @Test_PF0005_PRO : Admin is Updating Settings with default Post Form Pro
         * @Test_PF0006_PRO : Admin is Submitting Form from Frontend Pro
         *
         */

        //TODO: Create a BeforeAll for login


        test('PF0001_PRO : Admin is creating Blank Form with > CustomFields Pro', { tag: ['@Pro'] }, async ({ page }) => {
            const BasicLogin = new BasicLoginPage(page);
            const PostFormsPro = new PostFormsProPage(page);
            const FieldOptionsCommonPro = new FieldOptionsCommonProPage(page);


            //Log into Admin Dashboard
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
            //Post Blank Form
            await PostFormsPro.createBlankFormPostFormPro(PostForm.pfPostName3);
            //PostFields
            await FieldOptionsCommonPro.addPostFields_PF_pro();
            //CustomFields + Validate
            await FieldOptionsCommonPro.addCustomFields_Common_pro();
            await FieldOptionsCommonPro.validateCustomFields_Common_pro();

            //Save
            await FieldOptionsCommonPro.saveForm_Common_pro(PostForm.pfPostName3);
            //Validate
            await FieldOptionsCommonPro.validatePostFormCreatedPro(PostForm.pfPostName3);
        });


        test('PF0002_PRO : Admin is creating Blank Form with > Others Pro', { tag: ['@Pro'] }, async ({ page }) => {
            const PostFormsPro = new PostFormsProPage(page);
            const FieldOptionsCommonPro = new FieldOptionsCommonProPage(page);


            //Post Blank Form
            await PostFormsPro.createBlankFormPostFormPro(PostForm.pfPostName4);
            //PostFields
            await FieldOptionsCommonPro.addPostFields_PF_pro();
            //Others + Validate
            await FieldOptionsCommonPro.addOthers_Common_pro();
            await FieldOptionsCommonPro.validateOthers_Common_pro();
            //await FieldOptionsCommon.setMultiStepSettings_Common();

            //Save
            await FieldOptionsCommonPro.saveForm_Common_pro(PostForm.pfPostName4);
            //Validate
            await FieldOptionsCommonPro.validatePostFormCreatedPro(PostForm.pfPostName4);
        });


        test('PF0003_PRO : Admin is creating a Blank Post Form with all Pro Fields', { tag: ['@Pro'] }, async ({ page }) => {
            const PostFormsPro = new PostFormsProPage(page);
            const FieldOptionsCommonPro = new FieldOptionsCommonProPage(page);

            //Post Blank Form
            await PostFormsPro.createBlankFormPostFormPro(PostForm.pfPostName1);
            //PostFields + Validate
            await FieldOptionsCommonPro.addPostFields_PF_pro();
            await FieldOptionsCommonPro.validatePostFields_PF_pro();
            //CustomFields + Validate
            await FieldOptionsCommonPro.addCustomFields_Common_pro();
            await FieldOptionsCommonPro.validateCustomFields_Common_pro();
            //Others + Validate
            await FieldOptionsCommonPro.addOthers_Common_pro();
            await FieldOptionsCommonPro.validateOthers_Common_pro();
            //await FieldOptionsCommon.setMultiStepSettings_Common();

            //Save
            await FieldOptionsCommonPro.saveForm_Common_pro(PostForm.pfPostName1);
            //Validate
            await FieldOptionsCommonPro.validatePostFormCreatedPro(PostForm.pfPostName1);
        });

        test('PF0004_PRO : Admin is creating a Preset Post Form - with Guest Enabled Pro', { tag: ['@Pro'] }, async ({ page }) => {
            const PostFormsPro = new PostFormsProPage(page);
            const FieldOptionsCommonPro = new FieldOptionsCommonProPage(page);

            //For Front-End
            //Create Post Form
            const postFormPresetFrontendTitle = 'FE PostForm';
            //Post Preset Form
            await PostFormsPro.createPresetPostFormWithGuestEnabledPro(postFormPresetFrontendTitle);
            //Validate
            await FieldOptionsCommonPro.validatePostFields_PF_pro();
            await FieldOptionsCommonPro.validateTaxonomiesPreset_PF_pro();

            //Save
            await FieldOptionsCommonPro.saveForm_Common_pro(postFormPresetFrontendTitle);
            //Validate
            await FieldOptionsCommonPro.validatePostFormCreatedPro(postFormPresetFrontendTitle);
        });


        test('PF0005_PRO : Admin is Updating Settings with default Post Form Pro', { tag: ['@Pro'] }, async ({ page }) => {
            const PostFormsPro = new PostFormsProPage(page);
            const SettingsSetup = new SettingsSetupPage(page);

            const postFormPresetFrontendTitle = 'FE PostForm';

            await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontendTitle);

            fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
        });


        test('PF0006_PRO : User is Submitting Form from Frontend', { tag: ['@Pro'] }, async ({ page }) => {
            const BasicLogin = new BasicLoginPage(page);
            const PostFormsFrontend = new PostFormsFrontendPage(page);

            //New User created Login
            const newUserEmail = Users.userEmail;
            const newUserPassword = Users.userPassword;
            await BasicLogin.basicLogin(newUserEmail, newUserPassword);

            //Complete Post from Frontend
            const postFormTitle = PostForm.pfTitle;
            await PostFormsFrontend.createPostFormFrontend(postFormTitle);
            //Valdiate Post form created form Frontend
            await PostFormsFrontend.validatePostFormCreatedFrontend(postFormTitle);

            fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

        });
    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });


}