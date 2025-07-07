import * as dotenv from 'dotenv';
dotenv.config();
import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { FieldAddPage } from '../pages/fieldAdd';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from 'fs'; //Clear Cookie
import { BasicLogoutPage } from '../pages/basicLogout';
import { faker } from '@faker-js/faker';

export default function postFormTest() {

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


    test.describe('Post-Forms', () => {
        /**----------------------------------POSTFORM----------------------------------**
         *
         * @TestScenario : [Post-Forms]
         * @Test_PF0001 : Admin is creating Blank Form with all Fields
         * @Test_PF0002 : Admin is Updating Settings with default Post Form
         * @Test_PF0003 : User is Creating Post from Frontend
         * @Test_PF0004 : User is Validating Post created
         * @Test_PF0005 : User is validating Entered Data for Created Post
         * @Test_PF0006 : Admin is creating a Preset Post Form
         * @Test_PF0007 : Admin is creating a Preset Post Form - with Guest Enabled
         * @Test_PF0008 : Admin is creating page with shortcode
         * @Test_PF0009 : Guest is creating post from frontend
         * @Test_PF0010 : Guest is validating post created
         *
         */

        let pfShortCode:string;
        //TODO: Create a BeforeAll for login

        //Log into Admin Dashboard
        test.beforeAll(async () => {
            const BasicLogin = new BasicLoginPage(page);
            await BasicLogin.basicLoginAndPluginVisit(Users.adminUsername, Users.adminPassword);
        });


        test('PF0001 : Admin is creating a Blank Post Form with all Fields', { tag: ['@Lite'] }, async () => {
            const PostFormClass = new PostFormPage(page);
            const FieldAdd = new FieldAddPage(page);

            PostForm.formName = faker.word.words(3);
            //Post Blank Form
            await PostFormClass.createBlankFormPostForm(PostForm.formName);
            //PostFields + Validate
            await FieldAdd.addPostFields_PF();
            await FieldAdd.validatePostFields_PF();
            //Taxonomies + Validate
            await FieldAdd.addTaxonomies_PF();
            await FieldAdd.validateTaxonomies_PF();
            //CustomFields + Validate
            await FieldAdd.addCustomFields_Common();
            await FieldAdd.validateCustomFields_Common();
            //Others + Validate
            await FieldAdd.addOthers_Common();
            await FieldAdd.validateOthers_Common();
            //Save
            await FieldAdd.saveForm_Common(PostForm.formName);
            //Validate
            await FieldAdd.validatePostFormCreated(PostForm.formName);
        });

        test('PF0002 : Admin is Updating Settings with default Post Form', { tag: ['@Lite'] }, async () => {
            const SettingsSetup = new SettingsSetupPage(page);

            await SettingsSetup.changeSettingsSetDefaultPostForm(PostForm.formName);

            await new BasicLogoutPage(page).logOut();
        });


        test('PF0003 : User is Creating Post from Frontend', { tag: ['@Lite'] }, async () => {
            const PostFormClass = new PostFormPage(page);

            await new BasicLoginPage(page).basicLogin(Users.userEmail, Users.userPassword);

            //Complete Post from Frontend
            PostForm.title = faker.word.words(3);
            await PostFormClass.createPostFE();
        });

        test('PF0004 : User is Validating Post created', { tag: ['@Lite'] }, async () => {
            const PostFormClass = new PostFormPage(page);
            
            await PostFormClass.validatePostCreated();
            
        });

        test('PF0005 : User is validating Entered Data for Created Post', { tag: ['@Lite'] }, async () => {
            const PostFormClass = new PostFormPage(page);

            await PostFormClass.validateEnteredData();
        });


        test('PF0006 : Admin is creating a Preset Post Form', { tag: ['@Lite'] }, async () => {
            const PostForm = new PostFormPage(page);
            const FieldAdd = new FieldAddPage(page);

            await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);

            //Post Preset Form
            await PostForm.createPresetPostForm('PF Preset');
            //Validate
            await FieldAdd.validatePostFields_PF();
            await FieldAdd.validateTaxonomiesPreset_PF();

            //Save
            await FieldAdd.saveForm_Common('PF Preset');
            //Validate
            await FieldAdd.validatePostFormCreated('PF Preset');
        });


        test('PF0007 : Admin is creating a Preset Post Form - with Guest Enabled', { tag: ['@Lite'] }, async () => {
            const PostForm = new PostFormPage(page);
            const FieldAdd = new FieldAddPage(page);

            //For Front-End
            //Create Post Form
            const postFormPresetFrontendTitle = 'Guest PostForm';
            //Post Preset Form
            await PostForm.createPresetPostFormWithGuestEnabled(postFormPresetFrontendTitle);
            //Validate
            await FieldAdd.validatePostFields_PF();
            await FieldAdd.validateTaxonomiesPreset_PF();

            //Save
            await FieldAdd.saveForm_Common(postFormPresetFrontendTitle);
            //Validate
            pfShortCode = await FieldAdd.validatePostFormCreated(postFormPresetFrontendTitle);
            console.log('PF Short Code: ' + pfShortCode);
        });

        test('PF0008 : Admin is creating page with shortcode ', { tag: ['@Lite'] }, async () => {
            const PostForm = new PostFormPage(page);

            await PostForm.createPageWithShortcode(pfShortCode, 'GuestPostForm');

            await new BasicLogoutPage(page).logOut();
        });

        test('PF0009 : Guest is creating post from frontend', { tag: ['@Lite'] }, async () => {
            const PostForm = new PostFormPage(page);
            
            await PostForm.createGuestPostFE();
        });

        test('PF0010 : Guest is validating post created', { tag: ['@Lite'] }, async () => {
            const PostForm = new PostFormPage(page);
            
            await PostForm.validateGuestPostCreated();
        });

    });

    test.afterAll(async () => {
        // Clear state file after tests
        fs.writeFileSync('state.json', JSON.stringify({ cookies: [], origins: [] }));
        
        // Close the browser
        await browser.close();
    });
}