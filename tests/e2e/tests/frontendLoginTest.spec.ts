import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { FrontendLoginPage } from '../pages/frontendLogin';
import { configureSpecFailFast } from '../utils/specFailFast';
import {
    seedPageWithShortcode,
    seedUser,
    getWpufOptionKey,
    setWpufOptionKey,
    deleteWpufOptionKey,
} from '../utils/wpEnvCli';

let browser: Browser;
let context: BrowserContext;
let page: Page;

let loginUrl: string;
// Snapshot of wpuf_general.enable_turnstile so afterAll can restore it. The
// suite's .env Turnstile keys are stubs, so a live Turnstile check would block
// every frontend login; it is disabled for this spec only.
let turnstileBefore: string | null = null;
let loginPageBefore: string | null = null;

const loginPageTitle = 'WPUF FE Login';
// Self-seeded subscriber — this spec must not depend on setup-phase fixtures.
const feUser = 'flspecuser';
const feUserEmail = 'flspecuser@yopmail.com';
const feUserPassword = 'FLspec-Pass-2026!';

test.beforeAll(async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    // Seed via wp-cli (fast, self-cleaning): page with [wpuf-login], registered
    // as WPUF's login page so the form posts back to itself.
    const seeded = seedPageWithShortcode(loginPageTitle, '[wpuf-login]');
    loginUrl = seeded.url;
    loginPageBefore = getWpufOptionKey('wpuf_profile', 'login_page');
    setWpufOptionKey('wpuf_profile', 'login_page', String(seeded.id));

    turnstileBefore = getWpufOptionKey('wpuf_general', 'enable_turnstile');
    setWpufOptionKey('wpuf_general', 'enable_turnstile', 'off');

    seedUser(feUser, feUserEmail, feUserPassword);
});

test.afterAll(async () => {
    // Restore the options this spec changed.
    if (turnstileBefore === null) {
        deleteWpufOptionKey('wpuf_general', 'enable_turnstile');
    } else {
        setWpufOptionKey('wpuf_general', 'enable_turnstile', turnstileBefore);
    }
    if (loginPageBefore === null) {
        deleteWpufOptionKey('wpuf_profile', 'login_page');
    } else {
        setWpufOptionKey('wpuf_profile', 'login_page', loginPageBefore);
    }
    await browser?.close();
});

test.describe('Frontend Login ([wpuf-login])', () => {
    // Shared page + auth state — stop the file on the first failure.
    configureSpecFailFast();

    /**
     * @Test_Scenarios : [FRONTEND LOGIN — [wpuf-login] shortcode + lost password]
     * @Test_FL0001 : Login form renders with all controls on the shortcode page
     * @Test_FL0002 : Empty submit is rejected ("Username is required.")
     * @Test_FL0003 : Invalid credentials are rejected, visitor stays logged out
     * @Test_FL0004 : Lost password with an unknown email is rejected
     * @Test_FL0005 : Lost password for a known user reaches the mailer (env-gated)
     * @Test_FL0006 : Valid credentials log the user in (logged-in view shown)
     */

    test('FL0001 : Login form renders on the shortcode page', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        await new FrontendLoginPage(page).validateLoginFormRenders(loginUrl);
    });

    test('FL0002 : Empty submit is rejected', { tag: ['@Lite', '@FrontendLogin', '@Negative'] }, async () => {
        await new FrontendLoginPage(page).validateEmptySubmitBlocked(loginUrl);
    });

    test('FL0003 : Invalid credentials are rejected', { tag: ['@Lite', '@FrontendLogin', '@Negative'] }, async () => {
        await new FrontendLoginPage(page).validateInvalidCredentialsBlocked(loginUrl, feUser);
    });

    test('FL0004 : Lost password with unknown email is rejected', { tag: ['@Lite', '@FrontendLogin', '@Negative'] }, async () => {
        const unknown = `nouser_${faker.string.alphanumeric(8)}@example.com`;
        await new FrontendLoginPage(page).validateLostPasswordUnknownEmail(loginUrl, unknown);
    });

    test('FL0005 : Lost password for a known user reaches the mailer', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        const outcome = await new FrontendLoginPage(page).requestLostPasswordKnownUser(loginUrl, feUser);
        // WPUF's side (user lookup, reset key, redirect) is proven either way;
        // actual delivery needs the env's mail stack (same class as EM0004).
        test.skip(outcome === 'mailfail', 'Reset flow reached the mailer but this env cannot send mail (SMTP gap) — WPUF logic verified.');
    });

    // Last: changes the shared page's auth state.
    test('FL0006 : Valid credentials log the user in', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        await new FrontendLoginPage(page).validateValidLogin(loginUrl, feUser, feUserPassword);
    });
});
