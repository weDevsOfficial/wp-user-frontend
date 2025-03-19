require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Urls } from '../utils/testData';




export class RegistrationFormsPage {
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


        const validateWPUFProActivate = await this.page.isVisible(Selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log("WPUF Pro is Activated");
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.page.innerText(Selectors.registrationForms.validateRegistrationFormsProFeatureLite.checkProFeaturesText);
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
        const storeShortcode = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.storeShortcode);
        console.log(storeShortcode);

        //Visit Pages
        const visitPagesAdminMenuOption = Urls.baseUrl + '/wp-admin/edit.php?post_type=page';
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Add New Page
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addNewPage);
        await this.page.reload();

         // Check if the Welcome Modal is visible
         let closeWelcomeModal = this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closeWelcomeModal);
         try {
             await closeWelcomeModal.waitFor({ state: 'visible', timeout: 5000 });
             await closeWelcomeModal.click();
         } catch (error) {
             console.log('Welcome Modal not visible!');
         }
       
        // Check if the Choose Pattern Modal is visible
        let closePatternModal = this.page.locator(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.closePatternModal);
        try {
            await closePatternModal.waitFor({ state: 'visible', timeout: 5000 });
            await closePatternModal.click();
        } catch (error) {
            console.log('Pattern Modal not visible!');
        }
 
        //Add Page Title
        await this.page.fill(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addPageTitle, registrationFormPageTitle);

        //Click Add Block Button
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockAddButton);

        //Search and Add Shortcode block
        await this.page.fill(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.blockSearchBox, 'Shortcode');
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.addShortCodeBlock);

        //Enter Registration Shortcode
        await this.page.fill(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.enterRegistrationShortcode, storeShortcode);

        //Click Publish Page
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.clickPublishPage);
        //Allow Permission
        expect(await this.page.isVisible(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission)).toBeTruthy();
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.allowShortcodePermission);
        //Confirm Publish
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.confirmPublish);


        //Go to Pages 
        await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);

        //Validate Page Created
        //Search Page
        await this.page.fill(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBox, registrationFormPageTitle);
        await this.page.click(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.pagesSearchBoxSubmit);

        //Validate Page
        const validatePageCreated = await this.page.innerText(Selectors.registrationForms.createRegistrationPageUsingShortcodeLite.validatePageCreated);
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

        await this.page.click(Selectors.registrationForms.createBlankForm_RF.clickRegistrationFormMenuOption);

        await this.page.waitForTimeout(1000 * 5);
        //Start
        await expect(this.page.isVisible(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm)).toBeTruthy();
        await this.page.click(Selectors.registrationForms.createBlankForm_RF.clickRegistraionAddForm);    //TODO: Issue here
        await this.page.waitForTimeout(1000 * 5);


        //ClickBlankForm
        //Templates 
        await expect(this.page.isVisible(Selectors.registrationForms.createBlankForm_RF.hoverBlankForm)).toBeTruthy();
        await this.page.hover(Selectors.registrationForms.createBlankForm_RF.hoverBlankForm);
        await expect(this.page.isVisible(Selectors.registrationForms.createBlankForm_RF.clickBlankForm)).toBeTruthy();
        await this.page.click(Selectors.registrationForms.createBlankForm_RF.clickBlankForm);



        //EnterName
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.click(Selectors.registrationForms.createBlankForm_RF.editNewFormName);
        await this.page.fill(Selectors.registrationForms.createBlankForm_RF.enterNewFormName, newRegistrationName);
        await this.page.click(Selectors.registrationForms.createBlankForm_RF.confirmNewNameTickButton);

    }


}