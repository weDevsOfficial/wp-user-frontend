require('dotenv').config();
import { test, expect } from '@playwright/test';

//import { TestData } from './testdata';
import { HomePage } from '../pages/home-page';
import { LoginPage } from '../pages/login-page';
import { SettingsPage } from '../pages/settings-page';
import { LogoutPage } from '../pages/logout-page';
import { PostForms } from '../pages/post-forms';
import { PostFormsFrontEnd } from '../pages/post-forms-frontend';
import { RegistrationForms } from '../pages/registration-forms';

import { Practice } from '../pages/test';






test.describe('TEST :-->', () => {

//LOGIN-0001
//     test('0001: Admin > LOGIN', async ({ page }) => {
//         //const homePage = new HomePage(page);
//         const loginPage = new LoginPage(page);
        
//         //await homePage.open();
//         await loginPage.login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
//     });

    
//LOGOUT-0002
//     test('0002: Admin > LOGOUT', async ({ page }) => {
//         const homePage = new HomePage(page);
//         const loginPage = new LoginPage(page);
//         const logoutPage = new LogoutPage(page);

//         await homePage.open();
//         await loginPage.login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
//         await logoutPage.logOut();
//          //TODO: Add assertion
//     });



//Add_Post > BLANK_Form > 0003
    test('0003: Create New > Using BLANK Template', async ({ page }) => {
        //const homePage = new HomePage(page);
        const loginPage = new LoginPage(page);
        const postForms = new PostForms(page);
        
       // await homePage.open();
        await loginPage.login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
        await postForms.createNewPostBlankForm(process.env.NEW_POST_BLANK_NAME);  //TODO: User if/else for Div loading
         //TODO: Add assertion
     });


// //TODO: UNDO
// //Check_FRONT_END > BLANK_POST_Form > 0004
//     test('0003.1: Check Front-End for BLANK Template', async ({ page }) => {
//         //const homePage = new HomePage(page);
//         const loginPage = new LoginPage(page);
//         const postFormsFrontEnd = new PostFormsFrontEnd(page);
        
//     // await homePage.open();
//         await loginPage.login(process.env.BASE_URL2, process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
//         await postFormsFrontEnd.checkNewPostBlankFormFrontEnd(process.env.NEW_POSTFORM_BLANK_FRONTEND_POST_TITLE); 
//         //TODO: Add assertion
//     });


// //Add_Registration > BLANK_Form > 0005
//     test('0004: Create New > BLOG FORM', async ({ page }) => {
//         const loginPage = new LoginPage(page);
//         const registrationForms = new RegistrationForms(page);
        
//        // await homePage.open();
//         await loginPage.login(process.env.BASE_URL2, process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
//         await registrationForms.createNewRegistrationBlankForm(process.env.NEW_REGISTRAION_BLOG_NAME);
//     });










    
//TODO: Test Runner
    // test('0003: Create New > Using BLANK Template', async ({ page }) => {
    //     //const homePage = new HomePage(page);
    //     const loginPage = new LoginPage(page);
    //     const practice = new Practice(page);
        
    // // await homePage.open();
    //     await loginPage.login(process.env.ADMIN_USERNAME, process.env.ADMIN_PASSWORD);
    //     await practice.checkNewPostBlankFormFrontEnd(process.env.NEW_POST_BLANK_NAME); 
    //     //TODO: Add assertion
    // });



    

});