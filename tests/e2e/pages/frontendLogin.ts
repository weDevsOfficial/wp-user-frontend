import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';

const S = Selectors.login.frontendLogin;

/**
 * POM for the Lite `[wpuf-login]` shortcode page (templates/login-form.php,
 * lost-pass-form.php, logged-in.php — includes/Free/Simple_Login.php).
 *
 * All flows run logged-out on a shared page; the valid-login test runs last in
 * the spec since it changes auth state.
 */
export class FrontendLoginPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    /** The rendered login form exposes all its core controls. */
    async validateLoginFormRenders(loginUrl: string) {
        await this.navigateToURL(loginUrl);
        await expect(this.page.locator(S.loginForm)).toBeVisible();
        await expect(this.page.locator(S.usernameField)).toBeVisible();
        await expect(this.page.locator(S.passwordField)).toBeVisible();
        await expect(this.page.locator(S.rememberMeCheckbox)).toBeVisible();
        await expect(this.page.locator(S.submitButton)).toBeVisible();
        await expect(this.page.locator(S.lostPasswordLink)).toBeVisible();
    }

    /** Submitting with no username is rejected with the required-field error. */
    async validateEmptySubmitBlocked(loginUrl: string) {
        await this.navigateToURL(loginUrl);
        await this.page.locator(S.submitButton).click();
        await expect(this.page.locator(S.errorNotice)).toBeVisible();
        await expect(this.page.locator(S.errorNotice)).toContainText(/username is required/i);
        await expect(this.page.locator(S.loginForm)).toBeVisible();
    }

    /** Wrong credentials surface an error and leave the visitor logged out. */
    async validateInvalidCredentialsBlocked(loginUrl: string, username: string) {
        await this.navigateToURL(loginUrl);
        await this.page.locator(S.usernameField).fill(username);
        await this.page.locator(S.passwordField).fill('definitely-wrong-password-123');
        await this.page.locator(S.submitButton).click();
        await expect(this.page.locator(S.errorNotice)).toBeVisible();
        // Still the login form, not the logged-in view.
        await expect(this.page.locator(S.loginForm)).toBeVisible();
        await expect(this.page.locator(S.loggedInView)).toHaveCount(0);
    }

    /** Append a query string whether or not the permalink already has one (?page_id=N). */
    private lostPasswordUrl(loginUrl: string): string {
        return loginUrl + (loginUrl.includes('?') ? '&' : '?') + 'action=lostpassword';
    }

    /** Unknown e-mail on the lost-password form is rejected with the no-user error. */
    async validateLostPasswordUnknownEmail(loginUrl: string, email: string) {
        await this.navigateToURL(this.lostPasswordUrl(loginUrl));
        await expect(this.page.locator(S.lostPasswordForm)).toBeVisible();
        await this.page.locator(S.lostPasswordUserField).fill(email);
        await this.page.locator(S.lostPasswordSubmit).click();
        await expect(this.page.locator(S.errorNotice)).toBeVisible();
        await expect(this.page.locator(S.errorNotice)).toContainText(/no user registered with that email/i);
    }

    /**
     * Known user requests a reset. Returns:
     *  - 'sent'     — confirmation message shown (mail handed off)
     *  - 'mailfail' — WPUF reached the mailer but the env cannot send (SMTP gap)
     */
    async requestLostPasswordKnownUser(loginUrl: string, userLogin: string): Promise<'sent' | 'mailfail'> {
        await this.navigateToURL(this.lostPasswordUrl(loginUrl));
        await expect(this.page.locator(S.lostPasswordForm)).toBeVisible();
        await this.page.locator(S.lostPasswordUserField).fill(userLogin);
        await this.page.locator(S.lostPasswordSubmit).click();
        // Success redirects to ?checkemail=confirm with a .wpuf-message. When the
        // env has no mail transport, email_reset_pass wp_die()s with a bare
        // "The e-mail could not be sent." page — an environment gap, not a WPUF bug.
        await this.page.waitForLoadState('load');
        const bodyText = (await this.page.locator('body').innerText()) ?? '';
        if (/e-?mail could not be sent/i.test(bodyText)) {
            return 'mailfail';
        }
        const message = this.page.locator(S.messageNotice);
        await expect(message.first()).toBeVisible();
        await expect(message.first()).toContainText(/check your e-?mail/i);
        return 'sent';
    }

    /** Valid credentials log in; revisiting the page shows the logged-in view. */
    async validateValidLogin(loginUrl: string, username: string, password: string) {
        await this.navigateToURL(loginUrl);
        await this.page.locator(S.usernameField).fill(username);
        await this.page.locator(S.passwordField).fill(password);
        await this.page.locator(S.submitButton).click();
        await this.navigateToURL(loginUrl);
        await expect(this.page.locator(S.loggedInView)).toBeVisible();
        await expect(this.page.locator(S.loggedInView)).toContainText(/currently logged in/i);
        await expect(this.page.locator(S.loginForm)).toHaveCount(0);
    }
}
