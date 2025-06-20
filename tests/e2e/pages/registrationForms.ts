import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';
import { Base } from './base';

export class RegistrationFormsPage extends Base {

    constructor(page: Page) {
        super(page);
    }




    /************************************************* LITE *************************************************/
    /******* @Registration Forms - Lite *******/
    /************************************************/

    //Registration forms page - only WPUF-Lite activated
    async validateRegistrationFormsProFeature() {
        // Visit Registration forms page
        await Promise.all([this.page.goto(this.wpufRegistrationFormPage)]);

        const validateWPUFProActivate = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log('WPUF Pro is Activated');
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.page.innerText(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText);
            await expect(checkProFeaturesText).toContain('Unlock PRO Features');

            //Check Setup
            const checkUpgradeToProOption = this.page.locator(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkUpgradeToProOption);
            expect(checkUpgradeToProOption).toBeTruthy();
        }
    }


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(registrationFormPageTitle: string) {
        // Visit Registration forms page
        await Promise.all([this.page.goto(this.wpufRegistrationFormPage)]);

        let storeShortcode: string = '';

        //Copy Shortcode
        storeShortcode = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode);
        console.log(storeShortcode);

        //Visit Pages
        await Promise.all([this.page.goto(this.pagesPage)]);

        //Add New Page
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addNewPage);

        // Check if the Welcome Modal is visible
        try {
            await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closeWelcomeModal);
        } catch (error) {
            console.log('Welcome Modal not visible!');
        }

        // Check if the Choose Pattern Modal is visible
        try {
            await this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closePatternModal).click({ timeout: 10000 });
        } catch (error) {
            console.log('Pattern Modal not visible!');
        }

        //Add Page Title
        await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addPageTitle, registrationFormPageTitle);

        //Click Add Block Button
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockAddButton);

        //Search and Add Shortcode block
        await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockSearchBox, 'Shortcode');
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addShortCodeBlock);

        //Enter Registration Shortcode
        await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.enterRegistrationShortcode, storeShortcode?.toString() ?? '');

        //Click Publish Page
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.clickPublishPage);
        // //Allow Permission
        // await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
        //Confirm Publish
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.confirmPublish);


        //Go to Pages 
        await Promise.all([this.page.goto(this.pagesPage)]);

        //Validate Page Created
        //Search Page
        await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBox, registrationFormPageTitle);
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBoxSubmit);

        //Validate Page
        const validatePageCreated = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validatePageCreated);
        expect(validatePageCreated).toContain(registrationFormPageTitle);

    }












    /************************************************* PRO *************************************************/
    /******* @Create Registration Forms - Pro *******/
    /***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegistrationName: string) {
        //Visit Post Form Page
        await Promise.all([this.page.goto(this.wpufRegistrationFormPage)]);
        //CreateNewRegistrationForm

        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);

        //Start
        await this.assertionValidate(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);

        //ClickBlankForm
        //Templates 
        await this.assertionValidate(Selectors.registrationForms.createBlankForm_RF.hoverBlankForm);
        await this.page.hover(Selectors.registrationForms.createBlankForm_RF.hoverBlankForm);
        await this.assertionValidate(Selectors.registrationForms.createBlankForm_RF.clickBlankForm);
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickBlankForm);



        //EnterName
        await this.page.waitForLoadState('domcontentloaded');
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.editNewFormName);
        await this.validateAndFillStrings(Selectors.registrationForms.createBlankForm_RF.enterNewFormName, newRegistrationName);
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.confirmNewNameTickButton);

    }


}