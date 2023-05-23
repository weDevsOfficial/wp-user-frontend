require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../../utils/testData'


//import { TestData } from '../tests/testdata';

 

export class registrationFormsCreate {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


    /**
     * 
     * 
     * @Here Admin creates a new Blank Post Form 
     * 
     * 
     */
    //BlankForm
    async create_BlankForm_RF(newRegistrationName) {
        //Visit Post Form Page
        const wpuf_reg_form_page = testData.urls.baseUrl + 'admin.php?page=wpuf-profile-forms';
        
        await this.page.goto(wpuf_reg_form_page, { waitUntil: 'networkidle' }); 

         //Create_New_Registration_Form
         
         await this.page.click(selectors.registrationForms.create_BlankForm_RF.clickRegistrationFormMenuOption);
 
         await this.page.waitForTimeout(1000 * 5);
         //Start
         await expect(this.page.isVisible(selectors.registrationForms.create_BlankForm_RF.clickRegistraionAddForm)).toBeTruthy();
         await this.page.click(selectors.registrationForms.create_BlankForm_RF.clickRegistraionAddForm);    //TODO: Issue here
         await this.page.waitForTimeout(1000 * 5);
         
   
         //Click_Blank_Form
         //Templates 
         await expect(this.page.isVisible(selectors.registrationForms.create_BlankForm_RF.hoverBlankForm)).toBeTruthy();   
         await this.page.hover(selectors.registrationForms.create_BlankForm_RF.hoverBlankForm);   
         await expect(this.page.isVisible(selectors.registrationForms.create_BlankForm_RF.clickBlankForm)).toBeTruthy();   
         await this.page.click(selectors.registrationForms.create_BlankForm_RF.clickBlankForm); 
 


        //Enter_Name
        await this.page.waitForLoadState('domcontentloaded');
        await this.page.click(selectors.registrationForms.create_BlankForm_RF.editNewFormName); 
        await this.page.fill(selectors.registrationForms.create_BlankForm_RF.enterNewFormName, newRegistrationName);   
        await this.page.click(selectors.registrationForms.create_BlankForm_RF.confirmNewNameTickButton);  
                
        }




















    /**
     * 
     * 
     * @Action1 Admin adds Profile Fields to Form
     * @Action2 - This is Same as POST Forms -> Custom Fields
     * @Action3 - This is Same as POST Forms -> Others 
     * 
     * 
     */
    //ProfileFields
    async add_ProfileFields_RF() {
        //Post_Fields
        await this.page.waitForLoadState('domcontentloaded')
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldUsername);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldFirstName);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldLastName);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldDisplayName);        
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldNickName);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldEmail);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldWebsiteUrl);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFielBioInfo);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldPassword);
        await this.page.click(selectors.registrationForms.add_ProfileFields_RF.profileFieldAvatar);
    };












     /**
     * 
     * 
     * @Here Admin checks if Created form is displayed in Registration Form Table/Page/List
     * 
     * 
     */

    async validate_BlankForm_Created_RF(validateNewPostName_RF) {
        //Return HOME
        await this.page.click(selectors.registrationForms.create_BlankForm_RF.clickRegistrationFormMenuOption);
        await this.page.waitForLoadState('domcontentloaded');
        
        //ASSERTION > Check if-VALID
        const checkNew_BlankForm_CreatedValid_RF = await this.page.isVisible(selectors.registrationForms.navigate_RF_Page.checkAddButton_RF);
        if (checkNew_BlankForm_CreatedValid_RF == true) {  
            const checkNewFormCreated_RF = await this.page.innerText(selectors.registrationForms.navigate_RF_Page.postFormsPageFormTitleCheck_RF);
            await expect(checkNewFormCreated_RF).toContain(validateNewPostName_RF);
            console.log(checkNewFormCreated_RF);
            console.log(validateNewPostName_RF);
        }
    };





}
