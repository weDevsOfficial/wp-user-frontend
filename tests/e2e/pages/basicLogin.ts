import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { test, expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { SettingsSetupPage } from './settingsSetup';
import { Base } from './base';
import {
    authFileFor,
    ensureAuthDir,
    readSavedCookies,
    clearSavedSession,
} from '../utils/authSession';

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

        // Fast path: re-inject a previously saved session for this role instead
        // of typing credentials again. Falls through to UI login if none/stale.
        if (await this.restoreSession(adminEmail)) {
            return;
        }

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

        // Persist the freshly authenticated session for later reuse.
        await this.saveSession(adminEmail);

    }

    //Login and Plugin Visit
    async basicLoginAndPluginVisit(email: string, password: string) {
        await test.step("Login from backend and visit the wpuf plugin page", async () => {
            const SettingsSetup = new SettingsSetupPage(this.page);

            // Reuse the shared session-aware login (restore-or-UI-login + save).
            await this.basicLogin(email, password);

            //Redirection to WPUF Home Page
            await SettingsSetup.pluginVisitWPUF();

        })
    }

    /**************************************************/
    /************ @Session Persistence **************/
    /************************************************/

    /**
     * Attempt to restore a previously saved session for `identifier` by injecting
     * its cookies into the current context. Returns true only when the injected
     * session is confirmed logged in; otherwise clears it and returns false so the
     * caller performs a normal UI login.
     */
    async restoreSession(identifier: string): Promise<boolean> {
        const cookies = readSavedCookies(identifier);
        if (!cookies) {
            // No saved session — preserve original behaviour untouched.
            return false;
        }

        try {
            const context = this.page.context();
            // Drop whatever role is currently active, then adopt the saved one.
            await context.clearCookies();
            await context.addCookies(cookies);

            if (await this.isLoggedIn()) {
                console.log('\x1b[36m%s\x1b[0m', `♻️  Reused saved session for ${identifier}`);
                return true;
            }

            // Saved session is stale (expired / logged out server-side).
            await context.clearCookies();
            clearSavedSession(identifier);
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Saved session for ${identifier} was stale — logging in fresh`);
            return false;
        } catch (error) {
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Could not reuse session for ${identifier}: ${error}`);
            return false;
        }
    }

    /** Save the current context's authenticated state to `.auth/<role>.json`. */
    async saveSession(identifier: string): Promise<void> {
        try {
            ensureAuthDir();
            await this.page.context().storageState({ path: authFileFor(identifier) });
            console.log('\x1b[36m%s\x1b[0m', `💾 Saved session for ${identifier}`);
        } catch (error) {
            // Non-fatal: the login already succeeded, we just could not cache it.
            console.log('\x1b[33m%s\x1b[0m', `⚠️  Could not save session for ${identifier}: ${error}`);
        }
    }

    /** True when the current context lands on wp-admin without a login prompt. */
    async isLoggedIn(): Promise<boolean> {
        await this.navigateToURL(this.wpAdminPage);
        if (this.page.url().includes('wp-login.php')) {
            return false;
        }
        // The login form's user field only renders when logged out.
        const loginPromptVisible = await this.page.isVisible(Selectors.login.basicLogin.loginEmailField);
        return !loginPromptVisible;
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
