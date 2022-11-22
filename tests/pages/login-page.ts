require('dotenv').config();
import { expect, Page, selectors } from '@playwright/test';
import { SelectorsPage } from './selectors';


export class LoginPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async login(email, password) {
        
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
        await this.page.goto('http://localhost:8889/wp-admin/index.php?page=wpuf-setup', { waitUntil: 'networkidle' }); 
        const WPUFSetup = await this.page.isVisible(SelectorsPage.login.clickWPUFSetupSkip);
                if (WPUFSetup == true) {
                    await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                }
        console.log("1.1: Skip WPUF Setup >");

       //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(SelectorsPage.login.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;
        console.log("1.2: Landed in Dashboard");

        
        const CheckPlugin_In_Menu = await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
        if (CheckPlugin_In_Menu == false) {
            await this.page.isVisible('//li[@id="menu-plugins"]');
            await this.page.click('//li[@id="menu-plugins"]');
                //Activate Plugin
                const ActivateWPUF = await this.page.isVisible('//a[@aria-label="Activate WP User Frontend"]');
                const ActivateWPUF_Pro = await this.page.isVisible('//a[@id="activate-wpuf-pro"]');
                if ( ActivateWPUF == true && ActivateWPUF_Pro== true) {
                    await this.page.click('//a[@aria-label="Activate WP User Frontend"]');
                    await this.page.click('//a[@id="activate-wpuf-pro"]');

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
                console.log("3: " + "Text is > " + checkText);
                await expect(checkText).toContain("Add Form");
            }

            console.log("1.5: Login Done"); //TODO: Fix this
        }
    }


}
