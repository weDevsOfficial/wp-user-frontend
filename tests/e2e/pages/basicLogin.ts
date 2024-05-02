require('dotenv').config();
import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { settingsSetup } from '../pages/settingsSetup';
import { Urls } from '../utils/testData';

export class basicLoginPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;

    }


    /**************************************************/
    /*************** @Login **************************/
    /************************************************/

    //Basic Login
    async basicLogin(email, password) {
        const adminEmail = email;
        const adminPassword = password;

        //Go to BackEnd
        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
        //if in BackEnd or FrontEnd
        if (emailStateCheck == true) {
            await this.backendLogin(adminEmail, adminPassword);
        }
        else {
            await this.frontendLogin(adminEmail, adminPassword);
        }

        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
    };

    //Login and Plugin Visit
    async basicLoginAndPluginVisit(email, password) {
        const SettingsSetup = new settingsSetup(this.page);
        const adminEmail = email;
        const adminPassword = password;

        await Promise.all([
            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        const emailStateCheck = await this.page.isVisible(selectors.login.basicLogin.loginEmailField);
        //if in BackEnd or FrontEnd
        if (emailStateCheck == true) {
            await this.backendLogin(adminEmail, adminPassword);
        }
        else {
            await this.frontendLogin(adminEmail, adminPassword);
        }


        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });

        //Redirection to WPUF Home Page
        await SettingsSetup.pluginVisitWPUF();
    };

    //Validate Login
    async validateBasicLogin() {
        //Go to BackEnd
        await Promise.all([

            this.page.goto(Urls.baseUrl + '/wp-admin/', { waitUntil: 'networkidle' }),
        ]);

        //Validate LOGIN
        await this.page.waitForLoadState('domcontentloaded');
        const dashboardLanded = await this.page.isVisible(selectors.login.validateBasicLogin.logingSuccessDashboard);
        await expect(dashboardLanded).toBeTruthy;
    };




    /**************************************************/
    /**************** @Login Page ********************/
    /************************************************/

    //BackEnd Login
    async backendLogin(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField, password);

        await this.page.click(selectors.login.basicLogin.rememberMeField);
        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton);
    }

    //FrontEnd Login
    async frontendLogin(email, password) {
        await this.page.fill(selectors.login.basicLogin.loginEmailField2, email);

        const passwordCheck = await this.page.isVisible(selectors.login.basicLogin.loginPasswordField2);
        await expect(passwordCheck).toBeTruthy();
        await this.page.fill(selectors.login.basicLogin.loginPasswordField2, password);

        const loginButtonCheck = await this.page.isVisible(selectors.login.basicLogin.loginButton2);
        await expect(loginButtonCheck).toBeTruthy();
        await this.page.click(selectors.login.basicLogin.loginButton2);
    }




}
