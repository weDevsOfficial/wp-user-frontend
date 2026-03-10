import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { test, expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { SettingsSetupPage } from './settingsSetup';
import { Base } from './base';

export class BasicLoginPage extends Base {

    constructor(page: Page) {
        super(page);
    }


    /**************************************************/
    /*************** @Login **************************/
    /************************************************/

    //Basic Login
    async basicLogin(email: string, password: string) {
        const adminEmail = email;
        const adminPassword = password;

        //Go to BackEnd
        await this.navigateToURL(this.wpAdminPage);

        const emailStateCheck = await this.page.isVisible(Selectors.login.basicLogin.loginEmailField);
        //if in BackEnd or FrontEnd
        if (emailStateCheck) {
            await this.backendLogin(adminEmail, adminPassword);
        }
        else {
            await this.frontendLogin(adminEmail, adminPassword);
        }

    }

    //Login and Plugin Visit
    async basicLoginAndPluginVisit(email: string, password: string) {
        await test.step("Login from backend and visit the wpuf plugin page", async () => {
            const SettingsSetup = new SettingsSetupPage(this.page);
            const adminEmail = email;
            const adminPassword = password;

            await this.navigateToURL(this.wpAdminPage);

            const emailStateCheck = await this.page.isVisible(Selectors.login.basicLogin.loginEmailField);
            //if in BackEnd or FrontEnd
            if (emailStateCheck == true) {
                await this.backendLogin(adminEmail, adminPassword);
            }
            else {
                await this.frontendLogin(adminEmail, adminPassword);
            }

            //Redirection to WPUF Home Page
            await SettingsSetup.pluginVisitWPUF();

        })
    }

    //Validate Login
    async validateBasicLogin() {
        //Go to BackEnd
        await this.navigateToURL(this.wpAdminPage);
        await this.assertionValidate(Selectors.login.validateBasicLogin.logingSuccessDashboard);
    }




    /**************************************************/
    /**************** @Login Page ********************/
    /************************************************/

    //BackEnd Login
    async backendLogin(email: string, password: string) {
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginEmailField, email);
        await this.page.waitForTimeout(500);
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginPasswordField, password);
        await this.page.waitForTimeout(500);
        await this.validateAndClick(Selectors.login.basicLogin.rememberMeField);
        await this.page.waitForTimeout(500);
        await this.validateAndClick(Selectors.login.basicLogin.loginButton);

    }

    //FrontEnd Login
    async frontendLogin(email: string, password: string) {
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginEmailField2, email);
        await this.page.waitForTimeout(500);
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginPasswordField2, password);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.login.basicLogin.loginButton2);
        await this.validateAndClick(Selectors.login.basicLogin.loginButton2);
    }




}
