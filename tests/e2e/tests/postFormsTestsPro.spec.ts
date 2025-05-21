require('dotenv').config();
import { test, Page } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormsProPage } from '../pages/postFormsPro';
import { FieldOptionsCommonProPage } from '../pages/fieldOptionsCommonPro';
import { PostFormsFrontendPage } from '../pages/postFormsFrontend';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Users, PostForm } from '../utils/testData';
import * as fs from "fs"; //Clear Cookie



export default function postFormsTestsPro() {


test.describe('Post-Forms @Pro :-->', () => {
/**----------------------------------POSTFORMS----------------------------------**
 *
 * @TestScenario : [Post-Forms]
 * @Test_PF0001 : Admin is creating Blank Form with > PF + CustomFields Pro
 * @Test_PF0002 : Admin is creating Blank Form with > PF + Others Pro
 * @Test_PF0003 : Admin is creating a Blank Post Form with all Pro Fields
 * @Test_PF0004 : Admin is creating a Preset Post Form - with Guest Enabled Pro
 * @Test_PF0005 : Admin is Updating Settings with default Post Form Pro
 * @Test_PF0006 : Admin is Submitting Form from Frontend Pro
 *
 */

//TODO: Create a BeforeAll for login


    test('PF0001 : Admin is creating Blank Form with > CustomFields Pro', async ({ page }) => {
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


    test('PF0002 : Admin is creating Blank Form with > Others Pro', async ({ page }) => {
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


    test('PF0003 : Admin is creating a Blank Post Form with all Pro Fields', async ({ page }) => {
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

    test('PF0004 : Admin is creating a Preset Post Form - with Guest Enabled Pro', async ({ page }) => {
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


    test('PF0005 : Admin is Updating Settings with default Post Form Pro', async ({ page }) => {
        const PostFormsPro = new PostFormsProPage(page);
        const SettingsSetup = new SettingsSetupPage(page);

        const postFormPresetFrontendTitle = 'FE PostForm - 0001';

        await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontendTitle);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('PF0006 : User is Submitting Form from Frontend', async ({ page }) => {
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