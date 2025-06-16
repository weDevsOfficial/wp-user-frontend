import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormsPage } from '../pages/postForms';
import { FieldOptionsCommonPage } from '../pages/fieldOptionsCommon';
import { PostFormsFrontendPage } from '../pages/postFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm, Urls } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie
import { BasicLogoutPage } from '../pages/basicLogout';

export default function postFormsTests() {

    let browser: Browser;
    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async () => {
        // Clear state file
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));

        // Launch browser
        const args = ['--enable-experimental-web-platform-features'];
        if (!Urls.baseUrl.startsWith('http://localhost')) {
            args.push(`--unsafely-treat-insecure-origin-as-secure=${Urls.baseUrl}`);
        }

        browser = await chromium.launch({args});

        // Create a single context
        context = await browser.newContext();

        // Create a single page
        page = await context.newPage();
    });


    test.describe('Post-Forms', () => {
        /**----------------------------------POSTFORMS----------------------------------**
         *
         * @TestScenario : [Post-Forms]
         * @Test_PF0001 : Admin is creating Blank Form with > PostFields
         * @Test_PF0002 : Admin is creating Blank Form with > PF + Taxonomies
         * @Test_PF0003 : Admin is creating Blank Form with > PF + CustomFields
         * @Test_PF0004 : Admin is creating Blank Form with > PF + Others
         * @Test_PF0005 : Admin is creating Blank Form with all Fields
         * @Test_PF0006 : Admin is creating a Preset Post Form
         * @Test_PF0007 : Admin is creating a Preset Post Form - with Guest Enabled
         * @Test_PF0008 : Admin is Updating Settings with default Post Form
         * @Test_PF0009 : Admin is Submitting Form from Frontend
         *
         *
         */

        //TODO: Create a BeforeAll for login

        test('PF001 : Admin is creating Blank Form with > PostFields', { tag: ['@Lite'] }, async () => {
            const BasicLogin = new BasicLoginPage(page);
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);

            //Log into Admin Dashboard
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);

            //Post Blank Form
            await PostForms.createBlankFormPostForm(PostForm.pfPostName1);
            //PostFields + Validate
            await FieldOptionsCommon.addPostFields_PF();
            await FieldOptionsCommon.validatePostFields_PF();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName1);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName1);
        });


        test('PF0002 : Admin is creating Blank Form with > Taxonomies', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);

            //Post Blank Form
            await PostForms.createBlankFormPostForm(PostForm.pfPostName2);
            //PostFields
            await FieldOptionsCommon.addPostFields_PF();
            //Taxonomies + Validate
            await FieldOptionsCommon.addTaxonomies_PF();
            await FieldOptionsCommon.validateTaxonomies_PF();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName2);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName2);
        });


        test('PF0003 : Admin is creating Blank Form with > CustomFields', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);


            //Post Blank Form
            await PostForms.createBlankFormPostForm(PostForm.pfPostName3);
            //PostFields
            await FieldOptionsCommon.addPostFields_PF();
            //CustomFields + Validate
            await FieldOptionsCommon.addCustomFields_Common();
            await FieldOptionsCommon.validateCustomFields_Common();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName3);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName3);
        });


        test('PF0004 : Admin is creating Blank Form with > Others', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);


            //Post Blank Form
            await PostForms.createBlankFormPostForm(PostForm.pfPostName4);
            //PostFields
            await FieldOptionsCommon.addPostFields_PF();
            //Others + Validate
            await FieldOptionsCommon.addOthers_Common();
            await FieldOptionsCommon.validateOthers_Common();
            //await FieldOptionsCommon.setMultiStepSettings_Common();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName4);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName4);
        });


        test('PF0005 : Admin is creating a Blank Post Form with all Fields', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);

            //Post Blank Form
            await PostForms.createBlankFormPostForm(PostForm.pfPostName1);
            //PostFields + Validate
            await FieldOptionsCommon.addPostFields_PF();
            await FieldOptionsCommon.validatePostFields_PF();
            //Taxonomies + Validate
            await FieldOptionsCommon.addTaxonomies_PF();
            await FieldOptionsCommon.validateTaxonomies_PF();
            //CustomFields + Validate
            await FieldOptionsCommon.addCustomFields_Common();
            await FieldOptionsCommon.validateCustomFields_Common();
            //Others + Validate
            await FieldOptionsCommon.addOthers_Common();
            await FieldOptionsCommon.validateOthers_Common();
            //await FieldOptionsCommon.setMultiStepSettings_Common();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName1);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName1);
        });


        test('PF0006 : Admin is creating a Preset Post Form', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);

            //Post Preset Form
            await PostForms.createPresetPostForm(PostForm.pfPostName2);
            //Validate
            await FieldOptionsCommon.validatePostFields_PF();
            await FieldOptionsCommon.validateTaxonomiesPreset_PF();

            //Save
            await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName2);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName2);
        });


        test('PF0007 : Admin is creating a Preset Post Form - with Guest Enabled', { tag: ['@Lite'] }, async () => {
            const PostForms = new PostFormsPage(page);
            const FieldOptionsCommon = new FieldOptionsCommonPage(page);

            //For Front-End
            //Create Post Form
            const postFormPresetFrontendTitle = 'FE PostForm';
            //Post Preset Form
            await PostForms.createPresetPostFormWithGuestEnabled(postFormPresetFrontendTitle);
            //Validate
            await FieldOptionsCommon.validatePostFields_PF();
            await FieldOptionsCommon.validateTaxonomiesPreset_PF();

            //Save
            await FieldOptionsCommon.saveForm_Common(postFormPresetFrontendTitle);
            //Validate
            await FieldOptionsCommon.validatePostFormCreated(postFormPresetFrontendTitle);
        });


        test('PF0008 : Admin is Updating Settings with default Post Form', { tag: ['@Lite'] }, async () => {
            const SettingsSetup = new SettingsSetupPage(page);

            const postFormPresetFrontendTitle = 'FE PostForm';

            await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontendTitle);

            await new BasicLogoutPage(page).logOut();
        });


        test('PF0009 : User is Submitting Form from Frontend', { tag: ['@Lite'] }, async () => {
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