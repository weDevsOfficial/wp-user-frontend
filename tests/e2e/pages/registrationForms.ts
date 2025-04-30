import dotenv from "dotenv";
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
    async validateRegistrationFormsProFeatureLite() {
        // Visit Registration forms page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);


        const validateWPUFProActivate = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log("WPUF Pro is Activated");
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.validateAndGetText(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText);
            await expect(checkProFeaturesText).toContain("Unlock PRO Features");

            //Check Setup
            const checkUpgradeToProOption = await this.page.locator(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkUpgradeToProOption);
            expect(checkUpgradeToProOption).toBeTruthy();
        }
    };


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(registrationFormPageTitle: string) {
        // Visit Registration forms page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);

        //Validate Shortcode
        const validateShortcode = await this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validateShortcode);
        expect(validateShortcode).toBeTruthy();

        //Copy Shortcode
        const storeShortcode: String | null = await this.validateAndGetText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode);
        console.log(storeShortcode);

        //Visit Pages
        const visitPagesAdminMenuOption = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Add New Page
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addNewPage);
        await this.page.reload();

        // Check if the Choose Pattern Modal is visible
        let closePatternModal = this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closePatternModal);
        try {
            await closePatternModal.waitFor({ state: 'visible', timeout: 5000 });
            await closePatternModal.click({ timeout: 5000 });
        } catch (error) {
            console.log('Pattern Modal not visible!');
        }

        // Check if the Welcome Modal is visible
        let closeWelcomeModal = this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closeWelcomeModal);
        try {
            await closeWelcomeModal.waitFor({ state: 'visible', timeout: 2000 });
            await closeWelcomeModal.click({ timeout: 5000 });
        } catch (error) {
            console.log('Welcome Modal not visible!');
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
        //Allow Permission
        await this.assertionValidate(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
        //Confirm Publish
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.confirmPublish);


        //Go to Pages 
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Validate Page Created
        //Search Page
        await this.validateAndFillStrings(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBox, registrationFormPageTitle);
        await this.validateAndClick(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBoxSubmit);

        //Validate Page
        const validatePageCreated = await this.validateAndGetText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validatePageCreated);
        expect(validatePageCreated).toContain(registrationFormPageTitle);

    };












    /************************************************* PRO *************************************************/
    /******* @Create Registration Forms - Pro *******/
    /***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegistrationName: string) {
        //Visit Post Form Page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
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