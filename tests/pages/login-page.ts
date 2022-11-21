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
        //await this.page.goto('BASEURL', { waitUntil: 'networkidle' });
        await this.page.fill(SelectorsPage.login.loginEmailField, email);
        await this.page.fill(SelectorsPage.login.loginPasswordField, password);
        await this.page.click(SelectorsPage.login.loginButton);
        //await this.page.goto('http://localhost:8889/wp-admin/index.php?page=wpuf-setup', { waitUntil: 'networkidle' }); 
        const WPUFSetup = await this.page.isVisible(SelectorsPage.login.clickWPUFSetupSkip);
                if (WPUFSetup == true) {
                    await this.page.click(SelectorsPage.login.clickWPUFSetupSkip);
                }

       //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(SelectorsPage.login.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;
        console.log("0: " + DashboardLanded);

        
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
            console.log("1: " + CheckPlugin_In_Menu);
            await this.page.click(SelectorsPage.login.clickWPUFSidebar);
            await this.page.waitForLoadState('domcontentloaded');

            //ASSERTION > Check if-VALID
            const availableText = await this.page.isVisible(SelectorsPage.createPostForm.clickPostFormMenuOption);
            console.log("2: " + availableText);
            if (availableText == true) {    
                const checkText = await this.page.innerText(SelectorsPage.login.wpufPostForm_CheckAddButton);
                console.log("3: " + "Text is > " + checkText);
                await expect(checkText).toContain("Add Form");
            }

            console.log("4: " + "Login Done"); //TODO: Fix this
        }
    }


}
