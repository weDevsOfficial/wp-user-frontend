import { execSync } from 'child_process';

/**
 * Run a wp-cli command inside the wp-env "tests-cli" container and return stdout.
 *
 * Works both locally and in CI because both bring the environment up with
 * `wp-env` (see .github/workflows/e2e-wpuf.yml). `--skip-plugins --skip-themes`
 * keeps the call fast and avoids the dokan-lite CLI fatal. Use this only for
 * verifying side effects a plugin writes to the DB that have no stable UI
 * surface (e.g. MailPoet subscribers, whose admin UI is gated by onboarding).
 */
export function wpCli(command: string): string {
    const full = `npx wp-env run tests-cli wp --skip-plugins --skip-themes ${command}`;
    return execSync(full, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] });
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
