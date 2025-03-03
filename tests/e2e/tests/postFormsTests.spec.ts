require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
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
 *
 * @TestScenario : [Post-Forms]
 * @Test0010 : Admin is creating Blank Form with > PostFields... [Mandatory]
 * @Test0011 : Admin is creating Blank Form with > PF + Taxonomies...
 * @Test0012 : Admin is creating Blank Form with > PF + CustomFields...
 * @Test0013 : Admin is creating Blank Form with > PF + Others...
 * @Test0014 : Admin is creating Blank Form with all Fields...
 * @Test0015 : Admin is creating a Preset Post Form...
 * @Test0016 : Admin is creating a Preset Post Form - with Guest Enabled
 * @Test0017 : Admin is Updating Settings with default Post Form
 * @Test0018 : Admin is Submitting Form from Frontend
 *
 *
 */

//TODO: Create a BeforeAll for login

    test('0010:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
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


    test('0011:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
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


    test('0012:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
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


    test('0013:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const PostForms = new PostFormsPage(page);
        const FieldOptionsCommon = new FieldOptionsCommonPage(page);


        //Post Blank Form
        await PostForms.createBlankFormPostForm(PostForm.pfPostName4);
        //PostFields
        await FieldOptionsCommon.addPostFields_PF();
        //Others + Validate
        await FieldOptionsCommon.addOthers_Common();
        await FieldOptionsCommon.validateOthers_Common();
        await FieldOptionsCommon.setMultiStepSettings_Common();

        //Save
        await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName4);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName4);
    });


    test('0014:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({ page }) => {
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
        await FieldOptionsCommon.setMultiStepSettings_Common();

        //Save
        await FieldOptionsCommon.saveForm_Common(PostForm.pfPostName1);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(PostForm.pfPostName1);
    });


    test('0015:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({ page }) => {
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


    test('0016:[Post-Forms-FE] Here, Admin is creating a Preset Post Form - with Guest Enabled', async ({ page }) => {
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


    test('0017:[Post-Forms-FE] Here, Admin is Updating Settings with default Post Form', async ({ page }) => {
        const PostForms = new PostFormsPage(page);
        const SettingsSetup = new SettingsSetupPage(page);

        const postFormPresetFrontendTitle = 'FE PostForm - 0001';

        await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontendTitle);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0018:[Post-Forms-FE] Here, User is Submitting Form from Frontend', async ({ page }) => {
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