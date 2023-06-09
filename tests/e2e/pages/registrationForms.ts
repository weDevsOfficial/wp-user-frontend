require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


 

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
        const wpufRegistrationFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        try {
            await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
            // Add more promises here if needed
            ]);
        } catch (error) {
            // Handle the error here
            console.error('An error occurred during Promise.all():', error);
        }

        const validateWPUFProActivate = await this.page.isVisible(selectors.registrationForms.navigatePage_RF.checkAddButton_RF);
        if (validateWPUFProActivate == true) {
            console.log("WPUF Pro is Activated");
        }
        else {
            //Check Pro Features Header
            const checkProFeaturesText = await this.page.innerText('//h2[text()="Unlock PRO Features"]');
            await expect(checkProFeaturesText).toContain("Unlock PRO Features");

            //Check Setup
            const checkUpgradeToProOption = await this.page.locator('//a[contains(text(),"Upgrade to PRO")]');
            await expect(checkUpgradeToProOption).toBeTruthy();
        }
    };


    //Create Registration page using Shortcode
    async createRegistrationPageUsingShortcodeLite(registrationFormPageTitle) {
        // Visit Registration forms page
        const wpufRegistrationFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        try {
            await Promise.all([
            this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }),
            // Add more promises here if needed
            ]);
        } catch (error) {
            // Handle the error here
            console.error('An error occurred during Promise.all():', error);
        }

        //Validate Shortcode
        const validateShortcode = await this.page.locator('//code[text()="[wpuf-registration]"]');
        await expect(validateShortcode).toBeTruthy();

        //Copy Shortcode
        const storeShortcode = await this.page.innerText('//code[text()="[wpuf-registration]"]')
        console.log(storeShortcode);


        //Visit Pages
        const visitPagesAdminMenuOption = testData.urls.baseUrl + '/wp-admin/edit.php?post_type=page';

        try {
            await Promise.all([
            this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
            // Add more promises here if needed
            ]);
        } catch (error) {
            // Handle the error here
            console.error('An error occurred during Promise.all():', error);
        }
        
        //Add New Page
        await this.page.click('//a[@class="page-title-action"]');

        //Add Page Title
        await this.page.fill('//h1[@aria-label="Add title"]', registrationFormPageTitle);

        //Click Add Block Button
        await this.page.click('//button[@aria-label="Add block"]');

        //Search and Add Shortcode block
        await this.page.fill('//input[@placeholder="Search"]', 'Shortcode');
        await this.page.click('//span[text()="Shortcode"]');

        //Enter Registration Shortcode
        await this.page.fill('//textarea[@aria-label="Shortcode text"]', storeShortcode);

        //Validate Shortcode entered
        //await expect('//textarea[@aria-label="Shortcode text"]').toContain(storeShortcode);

        //Click Publish Page
        await this.page.click('//button[text()="Publish"]');
        //Confirm Publish
        await this.page.click('//button[contains(@class,"components-button editor-post-publish-button")]');

        // //Wait for Page to load
        // const pageCreated = await this.page.isVisible('//a[@class="components-button is-primary"]');
        // await expect(pageCreated).toBeTruthy();

        //Go to Pages 
        await Promise.all([
        this.page.goto(visitPagesAdminMenuOption, { waitUntil: 'networkidle' }),
        ]);
        
        //Validate Page Created
        //Search Page
        await this.page.fill('//input[@type="search"]', registrationFormPageTitle);
        await this.page.click('//input[@id="search-submit"]');

        //Validate Page
        const validatePageCreated = await this.page.innerText('//a[@class="row-title"]');
        await expect(validatePageCreated).toContain(registrationFormPageTitle);

    };


    //Change Settings - Registration Page
    async changeSettingsSetRegistrationPage(registrationFormPageTitle) {
        const wpufPostFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-post-forms';

        await this.page.goto(wpufPostFormPage, { waitUntil: 'networkidle' });
        //Change Settings
        await this.page.click(selectors.login.wpufSettingsPage.settingsTab);
        await expect (await this.page.isVisible(selectors.login.wpufSettingsPage.settingsTabProfile2)).toBeTruthy();
        await this.page.click(selectors.login.wpufSettingsPage.settingsTabProfile2);
        await this.page.selectOption(selectors.login.wpufSettingsPage.settingsTabProfileRegistrationPage, {label: registrationFormPageTitle});
        await this.page.click(selectors.login.wpufSettingsPage.settingsTabProfileSubmit);
        
        await this.page.waitForLoadState('domcontentloaded');
        
    };
















/************************************************* PRO *************************************************/
/******* @Create Registration Forms - Pro *******/
/***********************************************/

    //BlankForm
    async createBlankForm_RF(newRegistrationName) {
        //Visit Post Form Page
        const wpufRegistrationFormPage = testData.urls.baseUrl + '/wp-admin/admin.php?page=wpuf-profile-forms';
        
        await this.page.goto(wpufRegistrationFormPage, { waitUntil: 'networkidle' }); 

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