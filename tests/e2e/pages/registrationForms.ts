import * as dotenv from 'dotenv';
dotenv.config();
import { expect, Page } from '@playwright/test';
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
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'domcontentloaded' }),
        ]);

        const validateWPUFProActivate = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log('WPUF Pro is Activated');
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.page.innerText(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText);
            await expect(checkProFeaturesText).toContain('Unlock PRO Features');

            //Check Setup
            const checkUpgradeToProOption = await this.page.locator(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkUpgradeToProOption);
            expect(checkUpgradeToProOption).toBeTruthy();
        }
    }


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(registrationFormPageTitle: string) {
        // Visit Registration forms page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'domcontentloaded' }),
        ]);

        let storeShortcode: string = '';

        //Copy Shortcode
        storeShortcode = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode);
        console.log(storeShortcode);

        //Visit Pages
        const visitPagesAdminMenuOption = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'domcontentloaded' }),
        ]);

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
            await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closePatternModal);
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
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'domcontentloaded' }),
        ]);

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
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'domcontentloaded' }),
        ]);
        //CreateNewRegistrationForm

        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);

        // await this.page.waitForTimeout(1000 * 5);
        //Start
        await this.assertionValidate(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);
        await this.validateAndClick(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);    //TODO: Issue here
        // await this.page.waitForTimeout(1000 * 5);


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