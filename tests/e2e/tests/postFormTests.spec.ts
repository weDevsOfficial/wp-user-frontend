require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import { basicLoginPage } from '../pages/basicLogin';
import { postForms } from '../pages/postForms';
import { fieldOptionsCommon } from '../pages/fieldOptionsCommon'
import { testData } from '../utils/testData';

import * as fs from "fs"; //Clear Cookie



fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });




export default function PostFormTests() {


test.describe('TEST :-->', () => {
    
//TODO: Create a BeforeAll for login

/**----------------------------------POSTFORMS----------------------------------**
     * 
     * 
     * @TestScenario : [Post-Forms]
     * @Test008 : Admin is creating Blank Form with > PostFields... [Mandatory]
     * @Test009 : Admin is creating Blank Form with > PF + Taxonomies...
     * @Test0010 : Admin is creating Blank Form with > PF + CustomFields...
     * @Test0011 : Admin is creating Blank Form with > PF + Others...
     * @Test0012 : Admin is creating Blank Form with all Fields...
     * @Test0013 : Admin is creating a Preset Post Form...
     * 
     * 
     *  
     */ 

    test('008:[Post-Forms] Here, Admin is creating Blank Form with > PostFields', async ({ page }) => {
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
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName1);

    });


    test('009:[Post-Forms] Admin is creating Blank Form with > Taxonomies', async ({ page }) => {
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
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName2);

    });


    test('0010:[Post-Forms] Here, Admin is creating Blank Form with > CustomFields', async ({ page }) => {
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
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName3);
    });


    test('0011:[Post-Forms] Here, Admin is creating Blank Form with > Others', async ({ page }) => {
        const BasicLogin = new basicLoginPage(page);
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
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
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName4);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });


    test('0012:[Post-Forms] Here, Admin is creating a Blank Post Form with all Fields', async ({page}) => {
        const BasicLogin = new basicLoginPage(page);
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
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
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName1);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });

    });


    test('0013:[Post-Forms] Here, Admin is creating a Preset Post Form', async ({page}) => {
        const BasicLogin = new basicLoginPage(page);
        const PostForms = new postForms(page);
        const FieldOptionsCommon = new fieldOptionsCommon(page);

        await BasicLogin.basicLoginAndPluginVisit(testData.users.adminUsername, testData.users.adminPassword);
        
        //Post Preset Form
        await PostForms.createPresetPostForm(testData.postForms.pfPostName2);
        //Validate
        await FieldOptionsCommon.validatePostFields_PF();
        await FieldOptionsCommon.validateTaxonomiesPreset_PF();

        //Save
        await FieldOptionsCommon.saveForm_Common(testData.postForms.pfPostName2);
        //Validate
        await FieldOptionsCommon.validateBlankFormCreated_PF(testData.postForms.pfPostName2);

        fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
    });



});

};