import { execSync } from 'child_process';

/**
 * The wp-env CLI container that backs the site under test. wp-env serves the dev
 * site (:8888) from `cli` and the tests site (:8889, the default target) from
 * `tests-cli`. DB assertions must run against whichever one QA_BASE_URL points at,
 * otherwise they read a different database than the browser navigates and fail
 * (e.g. "Command failed" when a page created for the run isn't in that DB).
 */
function wpEnvCliContainer(): string {
    return ( process.env.QA_BASE_URL || '' ).includes( ':8888' ) ? 'cli' : 'tests-cli';
}

/**
 * Run a wp-cli command inside the wp-env container backing QA_BASE_URL and return
 * stdout.
 *
 * Works both locally and in CI because both bring the environment up with
 * `wp-env` (see .github/workflows/e2e-wpuf.yml). `--skip-plugins --skip-themes`
 * keeps the call fast and avoids the dokan-lite CLI fatal. Use this only for
 * verifying side effects a plugin writes to the DB that have no stable UI
 * surface (e.g. MailPoet subscribers, whose admin UI is gated by onboarding).
 */
export function wpCli(command: string): string {
    // Use the current package name: `wp-env` is a deprecated shim that only prints
    // "Please run npx @wordpress/env instead" and exits 0 without running wp-cli, which
    // made every wpCli() call return that message and throw when parsing its result.
    const full = `npx @wordpress/env run ${wpEnvCliContainer()} wp --skip-plugins --skip-themes ${command}`;
    // `@wordpress/env` resolves the instance from its working directory. In CI the suite and
    // the wp-env instance share this directory, so the default cwd is correct. For a local
    // setup where the wp-env project lives elsewhere (its .wp-env.json is not under tests/e2e),
    // point WPUF_E2E_WP_ENV_DIR at that project dir so DB assertions hit the running instance
    // instead of an unstarted one in the test cwd.
    const cwd = process.env.WPUF_E2E_WP_ENV_DIR || process.cwd();
    return execSync(full, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'], cwd } );
}

/**
 * Create a fresh WordPress Application Password for the given admin user and
 * return it (spaces stripped) for use as HTTP Basic auth against the REST API.
 *
 * Works locally and in CI because both bring the env up with wp-env. Application
 * Passwords are retrievable only at creation time, so a new one is minted per run;
 * this is harmless in the disposable test env. Used by the REST API test layer to
 * authenticate `wpuf/v1` requests without coupling them to a browser login.
 */
export function createAdminAppPassword(user = 'admin', label = 'wpuf-e2e-api'): string {
    const raw = wpCli(`user application-password create ${user} ${label} --porcelain`);
    return raw.replace(/\s+/g, '');
}

/**
 * Mint an Application Password for an arbitrary (typically non-admin) user login,
 * used to exercise capability/authorization checks — e.g. a subscriber hitting an
 * admin-guarded `wpuf/v1` route must get 403, not 200.
 */
export function createUserAppPassword(userLogin: string, label = 'wpuf-e2e-api-role'): string {
    return createAdminAppPassword(userLogin, label);
}

/**
 * Return true if the given email is a MailPoet subscriber inside the named list
 * (segment). Used to assert WPUF's "subscribe on registration" behavior.
 */
export function isMailPoetSubscriberInList(email: string, listName: string): boolean {
    const sql =
        'SELECT s.email FROM wp_mailpoet_subscribers s ' +
        'JOIN wp_mailpoet_subscriber_segment ss ON ss.subscriber_id = s.id ' +
        'JOIN wp_mailpoet_segments seg ON seg.id = ss.segment_id ' +
        `WHERE s.email = '${email}' AND seg.name = '${listName}';`;
    const out = wpCli(`db query "${sql}"`);
    return out.includes(email);
}

/**
 * Ensure a WordPress option (array-shaped) exists so `option patch` calls work.
 */
function ensureOptionSection(section: string): void {
    try {
        wpCli(`option get ${section}`);
    } catch {
        wpCli(`option add ${section} '{}' --format=json`);
    }
}

/**
 * Read one key from an array-shaped WPUF option (e.g. wpuf_profile.login_page).
 * Returns null when the option or key does not exist.
 */
export function getWpufOptionKey(section: string, key: string): string | null {
    try {
        return wpCli(`option patch get ${section} ${key}`).trim();
    } catch {
        return null;
    }
}

/**
 * Write one key into an array-shaped WPUF option, creating the option/key as needed.
 */
export function setWpufOptionKey(section: string, key: string, value: string): void {
    ensureOptionSection(section);
    try {
        wpCli(`option patch update ${section} ${key} '${value}'`);
    } catch {
        wpCli(`option patch insert ${section} ${key} '${value}'`);
    }
}

/**
 * Delete one key from an array-shaped WPUF option (no-op when absent).
 */
export function deleteWpufOptionKey(section: string, key: string): void {
    try {
        wpCli(`option patch delete ${section} ${key}`);
    } catch {
        // key was not set — nothing to remove
    }
}

/**
 * Seed a published page holding a shortcode and return its id + permalink.
 *
 * Self-cleaning: deletes any previous pages with the same title first, so
 * re-runs without a site reset never accumulate duplicates (the MailPoet-spec
 * strict-mode lesson). Faster and steadier than driving the block editor UI.
 */
export function seedPageWithShortcode(title: string, shortcode: string): { id: number; url: string } {
    const existing = wpCli(`post list --post_type=page --title="${title}" --field=ID`).trim();
    for (const id of existing.split(/\s+/).filter(Boolean)) {
        wpCli(`post delete ${id} --force`);
    }
    const created = wpCli(
        `post create --post_type=page --post_status=publish --post_title="${title}" --post_content="${shortcode}" --porcelain`
    ).trim();
    const id = Number(created.split(/\s+/).pop());
    const url = wpCli(`post url ${id}`).trim().split(/\s+/).pop() as string;
    return { id, url };
}

/**
 * Ensure a subscriber-role user exists with a known password; returns the login.
 * Idempotent: reuses the user when present (password reset to the given one).
 */
export function seedUser(login: string, email: string, password: string): string {
    try {
        wpCli(`user get ${login} --field=ID`);
        wpCli(`user update ${login} --user_pass='${password}' --skip-email`);
    } catch {
        wpCli(`user create ${login} ${email} --role=subscriber --user_pass='${password}'`);
    }
    return login;
}
