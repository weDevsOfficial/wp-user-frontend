require('dotenv').config();
import { expect, Page } from '@playwright/test';
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

       //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const DashboardLanded = await this.page.isVisible(SelectorsPage.login.logingSuccessDashboard);
        await expect(DashboardLanded).toBeTruthy;
        console.log("0: " + DashboardLanded);


        
        const CheckPluginMenu = await this.page.isVisible(SelectorsPage.login.clickWPUFSidebar);
        console.log("1: " + CheckPluginMenu);
        // await expect(SelectorsPage.login.clickWPUFSidebar).toBeTruthy();  //Assertion
        
        //await (await this.page.waitForSelector(SelectorsPage.login.clickWPUFSidebar)).click;  //TODO: Change this
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
