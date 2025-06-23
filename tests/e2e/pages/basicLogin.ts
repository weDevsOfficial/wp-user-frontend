import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { Urls } from '../utils/testData';
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
        await Promise.all([this.page.goto(this.wpAdminPage )]);
        await this.page.reload();
        await this.waitForLoading();

        const emailStateCheck = await this.page.isVisible(Selectors.login.basicLogin.loginEmailField);
        //if in BackEnd or FrontEnd
        if (emailStateCheck) {
            await this.backendLogin(adminEmail, adminPassword);
        }
        else {
            await this.frontendLogin(adminEmail, adminPassword);
        }

        //Store Cookie State
        await this.page.context().storageState({ path: 'state.json' });
    }

    //Login and Plugin Visit
    async basicLoginAndPluginVisit(email: string, password: string) {
        const SettingsSetup = new SettingsSetupPage(this.page);
        const adminEmail = email;
        const adminPassword = password;

        await Promise.all([this.page.goto(this.wpAdminPage )]);
        await this.waitForLoading();

        const emailStateCheck = await this.page.isVisible(Selectors.login.basicLogin.loginEmailField);
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
    }

    //Validate Login
    async validateBasicLogin() {
        //Go to BackEnd
        await Promise.all([this.page.goto(this.wpAdminPage )]);
        await this.waitForLoading();
        await this.assertionValidate(Selectors.login.validateBasicLogin.logingSuccessDashboard);
    }




    /**************************************************/
    /**************** @Login Page ********************/
    /************************************************/

    //BackEnd Login
    async backendLogin(email: string, password: string) {
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginEmailField, email);
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginPasswordField, password);

        await this.validateAndClick(Selectors.login.basicLogin.rememberMeField);
        await this.validateAndClick(Selectors.login.basicLogin.loginButton);
    }

    //FrontEnd Login
    async frontendLogin(email: string, password: string) {
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginEmailField2, email);
        await this.validateAndFillStrings(Selectors.login.basicLogin.loginPasswordField2, password);

        await this.page.waitForTimeout(5000);

        await this.assertionValidate(Selectors.login.basicLogin.loginButton2);
        await this.validateAndClick(Selectors.login.basicLogin.loginButton2);
    }




}
