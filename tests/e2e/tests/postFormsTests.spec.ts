require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { postForms } from '../pages/postForms';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon'
import { postFormsFrontEnd } from '../pages/postFromsFrontEnd'
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie
import { settingsSetup } from '../pages/settingsSetup';



fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });




export default function postFormsTests() {


test.describe('TEST :-->', () => {

//TODO: Create a BeforeAll for login

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

    test('0010:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);

        //Post Blank Form
        await PostForms.createBlankFormPostForm(testData.postForms.pfPostName1);
        //PostFields + Validate
        await FieldOptionsCommon.addPostFields_PF();
        await FieldOptionsCommon.validatePostFields_PF();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName1);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName1);

    });


    test('0011:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        //Post Blank Form
        await PostForms.createBlankFormPostForm(testData.postForms.pfPostName2);
        //PostFields
        await FieldOptionsCommon.addPostFields_PF();
        //Taxonomies + Validate
        await FieldOptionsCommon.addTaxonomies_PF();
        await FieldOptionsCommon.validateTaxonomies_PF();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName2);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName2);

    });


    test('0012:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);


        //Post Blank Form
        await PostForms.createBlankFormPostForm(testData.postForms.pfPostName3);
        //PostFields
        await FieldOptionsCommon.addPostFields_PF();
        //CustomFields + Validate
        await FieldOptionsCommon.addCustomFields_Common();
        await FieldOptionsCommon.validateCustomFields_Common();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName3);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName3);

    });


    test('0013:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);


        //Post Blank Form
        await PostForms.createBlankFormPostForm(testData.postForms.pfPostName4);
        //PostFields
        await FieldOptionsCommon.addPostFields_PF();
        //Others + Validate
        await FieldOptionsCommon.addOthers_Common();
        await FieldOptionsCommon.validateOthers_Common();
        await FieldOptionsCommon.setMultiStepSettings_Common();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName4);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName4);

    });


    test('0014:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({page}) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        //Post Blank Form
        await PostForms.createBlankFormPostForm(testData.postForms.pfPostName1);
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
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName1);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName1);

    });


    test('0015:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({page}) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        //Post Preset Form
        await PostForms.createPresetPostForm(testData.postForms.pfPostName2);
        //Validate
        await FieldOptionsCommon.validatePostFields_PF();
        await FieldOptionsCommon.validateTaxonomiesPreset_PF();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName2);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(testData.postForms.pfPostName2);

    });


    test('0016:[Post-Forms-FE] Here, Admin is creating a Preset Post Form - with Guest Enabled', async ({page}) => {
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        //For Front-End
        //Create Post Form
        const postFormPresetFrontEnd = 'FE PostForm';
        //Post Preset Form
        await PostForms.createPresetPostFormWithGuestEnabled(postFormPresetFrontEnd);
        //Validate
        await FieldOptionsCommon.validatePostFields_PF();
        await FieldOptionsCommon.validateTaxonomiesPreset_PF();

        //Save
        await FieldOptionsCommon.saveForm_Common(postFormPresetFrontEnd);
        //Validate
        await FieldOptionsCommon.validatePostFormCreated(postFormPresetFrontEnd);

    });


    test('0017:[Post-Forms-FE] Here, Admin is Updating Settings with default Post Form', async ({page}) => {
        const PostForms = new postForms(page);
        const SettingsSetup = new settingsSetup(page);

        const postFormPresetFrontEndTitle = 'FE PostForm';

        await SettingsSetup.changeSettingsSetDefaultPostForm(postFormPresetFrontEndTitle);
        
    });


    test('0018:[Post-Forms-FE] Here, Admin is Submitting Form from Frontend', async ({page}) => {
        const BasicLogin = new basicLoginPage(page);
        const PostFormsFrontEnd = new postFormsFrontEnd(page);

        await BasicLogin.basicLogin('TestUser0001@yopmai.com', 'Dokan@123')
        //Complete Post from FrontEnd
        await PostFormsFrontEnd.createPostFormFrontEnd();
        //Validate Submitted Post
        await PostFormsFrontEnd.validatePostFormCreatedFrontend();

    });

});


};