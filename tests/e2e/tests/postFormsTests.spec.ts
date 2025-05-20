require('dotenv').config();
import { test, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormsPage } from '../pages/postForms';
import { FieldOptionsCommonPage } from '../pages/fieldOptionsCommon';
import { PostFormsFrontendPage } from '../pages/postFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from "fs"; //Clear Cookie



export default function postFormsTests() {


test.describe('Post-Forms @Lite :-->', () => {
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

    test('PF001 :[Post-Forms] Admin is creating Blank Form with > PostFields', async ({ page }) => {
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


    test('PF0002 :[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
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


    test('PF0003 :[Post-Forms] Admin is creating Blank Form with > CustomFields', async ({ page }) => {
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


    test('PF0004 :[Post-Forms] Admin is creating Blank Form with > Others', async ({ page }) => {
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


    test('PF0005 :[Post-Forms] Admin is creating a Blank Post Form with all Fields', async ({ page }) => {
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


    test('PF0006 :[Post-Forms] Admin is creating a Preset Post Form', async ({ page }) => {
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


    test('PF0007 :[Post-Forms-FE] Admin is creating a Preset Post Form - with Guest Enabled', async ({ page }) => {
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


    test('PF0008 :[Post-Forms-FE] Admin is Updating Settings with default Post Form', async ({ page }) => {
        const SettingsSetup = new SettingsSetupPage(page);

        const postFormPresetFrontendTitle = 'FE PostForm - 0001';

        await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontendTitle);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('PF0009 :[Post-Forms-FE] User is Submitting Form from Frontend', async ({ page }) => {
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


};