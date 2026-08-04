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
 * Same, but WITH plugins loaded. Needed whenever the command has to run WPUF's
 * own code — firing `wpuf_remove_expired_post_hook`, reading a WPUF helper —
 * because `wpCli()`'s `--skip-plugins` means those hooks are never registered.
 * Slower, so prefer `wpCli()` for plain DB reads/writes.
 */
export function wpCliFull(command: string): string {
    const full = `npx wp-env run tests-cli wp ${command}`;
    return execSync(full, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] });
}

let cliProbe: boolean | null = null;

/**
 * True when a wp-env CLI container is reachable. The suite also runs against
 * non-wp-env sites (QA_BASE_URL pointing at a local WP install), where the CLI
 * side-effect checks have to self-skip instead of failing the spec.
 */
export function wpCliAvailable(): boolean {
    if (cliProbe === null) {
        try {
            wpCli('option get siteurl');
            cliProbe = true;
        } catch {
            cliProbe = false;
        }
    }

    return cliProbe;
}

/**
 * Delete every WPUF form (post or registration) carrying the given title.
 *
 * Specs that build a form under a fixed name ("MailPoet Reg", "RF Settings")
 * accumulate duplicates whenever the suite runs without the destructive setup
 * phase, and every later "open the form called X" locator then dies on a
 * strict-mode violation. Same self-cleaning idea as `seedPageWithShortcode`.
 * No-op when no wp-env CLI is reachable, so non-wp-env runs are unaffected.
 */
export function deleteFormsByTitle( title: string ): void {
    if ( ! wpCliAvailable() ) {
        return;
    }

    try {
        const ids = wpCli(
            `post list --post_type=wpuf_forms,wpuf_profile --post_status=any --title="${title}" --field=ID`
        ).trim();

        for ( const id of ids.split( /\s+/ ).filter( Boolean ) ) {
            wpCli( `post delete ${id} --force` );
        }
    } catch {
        // Nothing to clean up — leave the site as it is.
    }
}

/**
 * Install a throwaway mu-plugin that echoes `marker` on `hookName`, so an
 * Action Hook field can be proven to actually fire instead of only proving the
 * hook name is not leaked. Returns false when no wp-env CLI is reachable.
 */
export function installHookProbe( hookName: string, marker: string ): boolean {
    if ( ! wpCliAvailable() ) {
        return false;
    }

    const php = `<?php add_action( '${hookName}', function() { echo '${marker}'; } );`;
    // base64 keeps the PHP body free of quotes, which would otherwise have to
    // survive both the local shell and the container's shell.
    const encoded = Buffer.from( php, 'utf-8' ).toString( 'base64' );

    try {
        wpCli(
            `eval 'file_put_contents( WPMU_PLUGIN_DIR . "/qa-hook-probe.php", base64_decode( "${encoded}" ) );'`
        );
        return true;
    } catch {
        return false;
    }
}

/** Remove the mu-plugin installed by `installHookProbe`. */
export function removeHookProbe(): void {
    if ( ! wpCliAvailable() ) {
        return;
    }

    try {
        wpCli( `eval '@unlink( WPMU_PLUGIN_DIR . "/qa-hook-probe.php" );'` );
    } catch {
        // nothing to remove
    }
}

/**
 * Current subscription-pack counts on the site, by post status.
 *
 * The subscription spec tracks the packs it creates, but other specs seed packs
 * too, so its counters have to start from the real baseline instead of zero.
 * Returns all-zero when no wp-env CLI is reachable.
 */
export function countSubscriptionPacks(): { all: number; publish: number; draft: number; trash: number } {
    const zero = { all: 0, publish: 0, draft: 0, trash: 0 };

    if ( ! wpCliAvailable() ) {
        return zero;
    }

    const count = ( status: string ): number => {
        try {
            const out = wpCli(
                `post list --post_type=wpuf_subscription --post_status=${status} --format=count`
            ).trim();
            return Number( out.split( /\s+/ ).filter( Boolean ).pop() || 0 );
        } catch {
            return 0;
        }
    };

    // "All" in the WPUF list excludes trashed packs, matching WP's own list tables.
    const publish = count( 'publish' );
    const draft = count( 'draft' );

    return { all: publish + draft, publish, draft, trash: count( 'trash' ) };
}

/** Post id for an exact title, or 0 when no post matches. */
export function getPostIdByTitle(title: string, postType = 'post'): number {
    const out = wpCli(`post list --post_type=${postType} --title="${title}" --field=ID --posts_per_page=1`).trim();

    return Number(out.split(/\s+/).filter(Boolean).pop() || 0);
}

/** One field of a post (post_status, post_author, …). */
export function getPostField(postId: number, field: string): string {
    return wpCli(`post get ${postId} --field=${field}`).trim();
}

/** One meta value of a post; empty string when unset. */
export function getPostMeta(postId: number, key: string): string {
    try {
        return wpCli(`post meta get ${postId} ${key}`).trim();
    } catch {
        return '';
    }
}

export function setPostMeta(postId: number, key: string, value: string): void {
    wpCli(`post meta update ${postId} ${key} '${value}'`);
}

export function deletePostMeta(postId: number, key: string): void {
    try {
        wpCli(`post meta delete ${postId} ${key}`);
    } catch {
        // meta was not set — nothing to remove
    }
}

/** Create (or reuse) a user with an explicit role and known password. */
export function seedUserWithRole(login: string, email: string, password: string, role: string): string {
    try {
        wpCli(`user get ${login} --field=ID`);
        wpCli(`user update ${login} --user_pass='${password}' --role=${role} --skip-email`);
    } catch {
        wpCli(`user create ${login} ${email} --role=${role} --user_pass='${password}'`);
    }

    return login;
}

/**
 * Give a user a completed subscription pack without walking the checkout UI.
 * WPUF reads the pack from the `_wpuf_subscription_pack` user meta, so seeding
 * it is enough for view-control / restriction assertions. Payment flows have
 * their own specs — this shortcut is only for "user owns pack X" preconditions.
 */
export function seedUserSubscriptionPack(userLogin: string, packId: number): void {
    const pack = JSON.stringify({
        pack_id: String(packId),
        status: 'completed',
        posts: [],
        recurring: 'no',
    });

    wpCli(`user meta update ${userLogin} _wpuf_subscription_pack '${pack}' --format=json`);
}

/**
 * Encrypt a value exactly the way WPUF does for guest-post verification links
 * (`wpuf_encryption`). Lets the guest-verification test build the link WPUF
 * would have emailed, so the flow can be proven without an SMTP stack.
 */
export function wpufEncrypt(value: string | number): string {
    return wpCliFull(`eval 'echo wpuf_encryption("${value}");'`).trim();
}

/** Run a WordPress cron hook now (plugins loaded, so WPUF's callbacks fire). */
export function runCronHook(hook: string): void {
    wpCliFull(`cron event run ${hook}`);
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
