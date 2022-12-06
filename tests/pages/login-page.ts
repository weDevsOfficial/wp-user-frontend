require('dotenv').config();
import { expect, Page, selectors } from '@playwright/test';
import { SelectorsPage } from './selectors';


export class LoginPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async login(email, password) {
        console.log("0001: Running Login Done");
        await this.page.goto('http://localhost:8889/wp-admin/', { waitUntil: 'networkidle' });  //TODO: User BASE_URL
        
        const EmailCheck = await this.page.isVisible(SelectorsPage.login.loginEmailField);
        await expect(EmailCheck).toBeTruthy();
        await this.page.fill(SelectorsPage.login.loginEmailField, email);

        const PasswordCheck = await this.page.isVisible(SelectorsPage.login.loginPasswordField);
        await expect(PasswordCheck).toBeTruthy();
        await this.page.fill(SelectorsPage.login.loginPasswordField, password);

        const LoginButtonCheck = await this.page.isVisible(SelectorsPage.login.loginButton);
        await expect(LoginButtonCheck).toBeTruthy();
        await this.page.click(SelectorsPage.login.loginButton);

        console.log("1.0: Login Done");
        //WPUF Setup
        await this.page.goto('http://localhost:8889/wp-admin/index.php?page=wpuf-setup', { waitUntil: 'networkidle' }); 
        const WPUFSetup = await this.page.isVisible(SelectorsPage.login.clickWPUFSetupSkip);
                if (WPUFSetup == true) {
                    //await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                    await this.page.click(SelectorsPage.login.clickWPUFSetupLetsGo);
                    await this.page.click(SelectorsPage.login.clickWPUFSetupContinue);
                    await this.page.click(SelectorsPage.login.clickWPUFSetupEnd);
                }
        console.log("1.1: Setup WPUF Complete");

       //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(SelectorsPage.login.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;
        console.log("1.2: Landed in Dashboard");

        
        const CheckPlugin_In_Menu = await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
        if (CheckPlugin_In_Menu == false) {
            await this.page.isVisible(SelectorsPage.login.clickPluginsSidebar);
            await this.page.click(SelectorsPage.login.clickPluginsSidebar);
                //Activate Plugin
                const ActivateWPUF = await this.page.isVisible(SelectorsPage.login.clickWPUF_LitePlugin);
                const ActivateWPUF_Pro = await this.page.isVisible(SelectorsPage.login.clickWPUF_ProPlugin);
                if ( ActivateWPUF == true && ActivateWPUF_Pro== true) {
                    await this.page.click(SelectorsPage.login.clickWPUF_LitePlugin);
                    await this.page.click(SelectorsPage.login.clickWPUF_ProPlugin);

                    await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
                    await this.page.click(SelectorsPage.login.clickWPUFSidebar);
                }
                else {
                    await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
                    await this.page.click(SelectorsPage.login.clickWPUFSidebar);
                }
        }

        else {
            console.log("1.3: Check WPUF in Menu");
            await this.page.click(SelectorsPage.login.clickWPUFSidebar);
            await this.page.waitForLoadState('domcontentloaded');

            //ASSERTION > Check if-VALID
            const availableText = await this.page.isVisible(SelectorsPage.createPostForm.clickPostFormMenuOption);
            console.log("1.4: Click WPUF in Menu");
            if (availableText == true) {    
                const checkText = await this.page.innerText(SelectorsPage.login.wpufPostForm_CheckAddButton);
                console.log("1.5: " + "Text is > " + checkText);
                await expect(checkText).toContain("Add Form");
            }

            console.log("1.6: Login Done"); //TODO: Fix this
        }

        //Change Settings
        await this.page.click('//a[contains(text(), "Settings")]');
        await this.page.click('#wpuf_profile-tab');
        await this.page.selectOption('//select[@id="wpuf_profile[login_page]"]', {label: '— Select —'});
        console.log("1.7: Settings Changed > to always Admin");

    }


    //Log without the WPUF Setup page
    async login2(email, password) {
        console.log("0001.2: Running Login Done");
        await this.page.goto('http://localhost:8889/wp-admin/', { waitUntil: 'networkidle' });  //TODO: User BASE_URL
        
        const EmailCheck = await this.page.isVisible(SelectorsPage.login.loginEmailField2);
        await expect(EmailCheck).toBeTruthy();
        await this.page.fill(SelectorsPage.login.loginEmailField2, email);

        const PasswordCheck = await this.page.isVisible(SelectorsPage.login.loginPasswordField2);
        await expect(PasswordCheck).toBeTruthy();
        await this.page.fill(SelectorsPage.login.loginPasswordField2, password);

        const LoginButtonCheck = await this.page.isVisible(SelectorsPage.login.loginButton2);
        await expect(LoginButtonCheck).toBeTruthy();
        await this.page.click(SelectorsPage.login.loginButton2);

        await this.page.click(SelectorsPage.login.adminDashboard);


       //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(SelectorsPage.login.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;
        console.log("1.2: Landed in Dashboard");

        
        const CheckPlugin_In_Menu = await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
        if (CheckPlugin_In_Menu == false) {
            await this.page.isVisible(SelectorsPage.login.clickPluginsSidebar);
            await this.page.click(SelectorsPage.login.clickPluginsSidebar);
                //Activate Plugin
                const ActivateWPUF = await this.page.isVisible(SelectorsPage.login.clickWPUF_LitePlugin);
                const ActivateWPUF_Pro = await this.page.isVisible(SelectorsPage.login.clickWPUF_ProPlugin);
                if ( ActivateWPUF == true && ActivateWPUF_Pro== true) {
                    await this.page.click(SelectorsPage.login.clickWPUF_LitePlugin);
                    await this.page.click(SelectorsPage.login.clickWPUF_ProPlugin);

                    await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
                    await this.page.click(SelectorsPage.login.clickWPUFSidebar);
                }
                else {
                    await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
                    await this.page.click(SelectorsPage.login.clickWPUFSidebar);
                }
        }

        else {
            console.log("1.3: Check WPUF in Menu");
            await this.page.click(SelectorsPage.login.clickWPUFSidebar);
            await this.page.waitForLoadState('domcontentloaded');

            //ASSERTION > Check if-VALID
            const availableText = await this.page.isVisible(SelectorsPage.createPostForm.clickPostFormMenuOption);
            console.log("1.4: Click WPUF in Menu");
            if (availableText == true) {    
                const checkText = await this.page.innerText(SelectorsPage.login.wpufPostForm_CheckAddButton);
                console.log("1.5: " + "Text is > " + checkText);
                await expect(checkText).toContain("Add Form");
            }

            console.log("1.6: Login Done"); //TODO: Fix this
        }
    }


}
