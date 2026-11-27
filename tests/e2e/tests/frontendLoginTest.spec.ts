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
    wpCli,
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
// wp-env prints status lines around wp-cli output: pick the value line.
const cliValue = (out: string, re: RegExp): string => ( out.split( '\n' ).map( (l) => l.trim() ).find( (l) => re.test( l ) ) || '' );
// Self-seeded subscriber — this spec must not depend on setup-phase fixtures.
const feUser = 'flspecuser';
const feUserEmail = 'flspecuser@yopmail.com';
const feUserPassword = 'FLspec-Pass-2026!';

test.beforeAll(async () => {
    // Seed via wp-cli (fast, self-cleaning): page with [wpuf-login], registered
    // as WPUF's login page so the form posts back to itself.
    const seeded = seedPageWithShortcode(loginPageTitle, '[wpuf-login]');
    loginUrl = seeded.url;
    loginPageBefore = getWpufOptionKey('wpuf_profile', 'login_page');
    setWpufOptionKey('wpuf_profile', 'login_page', String(seeded.id));

    turnstileBefore = getWpufOptionKey('wpuf_general', 'enable_turnstile');
    setWpufOptionKey('wpuf_general', 'enable_turnstile', 'off');

    seedUser(feUser, feUserEmail, feUserPassword);

    // The wp-cli seeds above block the event loop; the browser starts after them so its
    // connection is not left idle that long (under load it dropped: "browser has been closed").
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext();
    page = await context.newPage();
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
     * @Test_FL0002 : Empty submit is rejected ("Username is required." under the field)
     * @Test_FL0003 : Invalid credentials are rejected, visitor stays logged out
     * @Test_FL0004 : Lost password with an unknown email gets the same neutral answer as a real account
     * @Test_FL0005 : Lost password for a known user reaches the mailer (env-gated)
     * @Test_FL0006 : Valid credentials log the user in (logged-in view shown)
     * @Test_FL0007 : Login and lost-password screens load only the login app (no classic bundle, no Pro jQuery script)
     * @Test_FL0008 : Empty lost-password submit is rejected under the field
     * @Test_FL0009 : Reset password with a key: mismatch caught, reset saved, success message
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

    test('FL0004 : Lost password with an unknown email gets the neutral answer', { tag: ['@Lite', '@FrontendLogin', '@Negative'] }, async () => {
        const unknown = `nouser_${faker.string.alphanumeric(8)}@example.com`;
        await new FrontendLoginPage(page).validateLostPasswordUnknownEmail(loginUrl, unknown);
    });

    test('FL0005 : Lost password for a known user reaches the mailer', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        const outcome = await new FrontendLoginPage(page).requestLostPasswordKnownUser(loginUrl, feUser);
        // WPUF's side (user lookup, reset key, redirect) is proven either way;
        // actual delivery needs the env's mail stack (same class as EM0004).
        test.skip(outcome === 'mailfail', 'Reset flow reached the mailer but this env cannot send mail (SMTP gap) — WPUF logic verified.');
    });

    test('FL0007 : Login screens load only the login app', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        await new FrontendLoginPage(page).validateLoginAppAssets(loginUrl);
    });

    test('FL0008 : Empty lost-password submit is rejected', { tag: ['@Lite', '@FrontendLogin', '@Negative'] }, async () => {
        await new FrontendLoginPage(page).validateLostPasswordEmptyBlocked(loginUrl);
    });

    test('FL0009 : Reset password with a key', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        // A fresh key straight from WordPress (the e-mail cannot be read in every env).
        const key = cliValue(wpCli(`eval 'echo get_password_reset_key( get_user_by( "login", "${feUser}" ) );'`), /^[A-Za-z0-9]{20}$/);
        test.skip(! key, 'no reset key from wp-cli');
        // Same password again, so FL0006 still logs in with it.
        await new FrontendLoginPage(page).validateResetPassword(loginUrl, feUser, key, feUserPassword);
    });

    // Last: changes the shared page's auth state.
    test('FL0006 : Valid credentials log the user in', { tag: ['@Lite', '@FrontendLogin'] }, async () => {
        await new FrontendLoginPage(page).validateValidLogin(loginUrl, feUser, feUserPassword);
    });
});
