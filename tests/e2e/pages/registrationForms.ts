require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { Urls } from '../utils/testData';




export class registrationForms {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
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


        const validateWPUFProActivate = await this.page.isVisible(selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log("WPUF Pro is Activated");
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.page.innerText(selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText);
            await expect(checkProFeaturesText).toContain("Unlock PRO Features");

            //Check Setup
            const checkUpgradeToProOption = await this.page.locator(selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkUpgradeToProOption);
            expect(checkUpgradeToProOption).toBeTruthy();
        }
    };


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(registrationFormPageTitle) {
        // Visit Registration forms page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);

        //Validate Shortcode
        const validateShortcode = await this.page.locator(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validateShortcode);
        await expect(validateShortcode).toBeTruthy();

        //Copy Shortcode
        const storeShortcode = await this.page.innerText(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode);
        console.log(storeShortcode);


        //Visit Pages
        const visitPagesAdminMenuOption = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Add New Page
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addNewPage);

        //Add Page Title
        await this.page.fill(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addPageTitle, registrationFormPageTitle);

        //Click Add Block Button
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockAddButton);

        //Search and Add Shortcode block
        await this.page.fill(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockSearchBox, 'Shortcode');
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addShortCodeBlock);

        //Enter Registration Shortcode
        await this.page.fill(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.enterRegistrationShortcode, storeShortcode);

        //Click Publish Page
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.clickPublishPage);
        //Allow Permission
        expect(await this.page.isVisible(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission)).toBeTruthy();
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
        //Confirm Publish
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.confirmPublish);


        //Go to Pages 
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Validate Page Created
        //Search Page
        await this.page.fill(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBox, registrationFormPageTitle);
        await this.page.click(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBoxSubmit);

        //Validate Page
        const validatePageCreated = await this.page.innerText(selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validatePageCreated);
        expect(validatePageCreated).toContain(registrationFormPageTitle);

    };












    /************************************************* PRO *************************************************/
    /******* @Create Registration Forms - Pro *******/
    /***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegistrationName) {
        //Visit Post Form Page
        const wpufRegistrationFormPage = Urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
        ]);
        //CreateNewRegistrationForm

        await this.page.click(selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);

        await this.page.waitForTimeout(1000 * 5);
        //Start
        await expect(this.page.isVisible(selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm)).toBeTruthy();
        await this.page.click(selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);    //TODO: Issue here
        await this.page.waitForTimeout(1000 * 5);


        //ClickBlankForm
        //Templates 
        await expect(this.page.isVisible(selectors.registrationForms.createBlankForm_RF.hoverBlankForm)).toBeTruthy();
        await this.page.hover(selectors.registrationForms.createBlankForm_RF.hoverBlankForm);
        await expect(this.page.isVisible(selectors.registrationForms.createBlankForm_RF.clickBlankForm)).toBeTruthy();
        await this.page.click(selectors.registrationForms.createBlankForm_RF.clickBlankForm);



        //EnterName
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.click(selectors.registrationForms.createBlankForm_RF.editNewFormName);
        await this.page.fill(selectors.registrationForms.createBlankForm_RF.enterNewFormName, newRegistrationName);
        await this.page.click(selectors.registrationForms.createBlankForm_RF.confirmNewNameTickButton);

    }


}