import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

/**
 * Persistent-session helper.
 *
 * The e2e suite creates a fresh browser context per spec and logs in through the
 * UI on every `basicLogin()` call (often several times per spec, switching roles).
 * That is slow and flaky. This module lets `BasicLoginPage` cache a logged-in
 * session (cookies + storage) to disk once per role and re-inject it on later
 * logins instead of re-typing credentials.
 *
 * Sessions live under `tests/e2e/.auth/<role-slug>.json` — a Playwright
 * `storageState` file. `.auth/` is gitignored, so nothing here is committed.
 */

const currentDir = path.dirname(fileURLToPath(import.meta.url));

// tests/e2e/.auth  (resolve from this file so cwd doesn't matter)
export const AUTH_DIR = path.resolve(currentDir, '..', '.auth');

/**
 * Turn a role identifier (username or email) into a stable, filesystem-safe slug
 * so `admin` and `Testuser0001@yopmail.com` map to distinct session files.
 */
function slugify(identifier: string): string {
    const slug = identifier
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    return slug || 'user';
}

/**
 * Parallel-worker suffix. The parallel configs run `workers: 3`, so every worker
 * would otherwise read/write the SAME `.auth/<role>.json` concurrently — a torn
 * write could corrupt it. Playwright sets `TEST_PARALLEL_INDEX` (0..workers-1)
 * per worker; two workers running at the same time always have distinct indices,
 * so keying the file by it gives each worker its own contention-free cache while
 * still reusing the session across every spec that worker runs. Absent (single
 * worker / setup suite) → no suffix, so behaviour is unchanged.
 */
function workerSuffix(): string {
    const idx = process.env.TEST_PARALLEL_INDEX;
    return idx !== undefined && idx !== '' ? `-p${idx}` : '';
}

/** Absolute path to the saved-session file for a role. */
export function authFileFor(identifier: string): string {
    return path.join(AUTH_DIR, `${slugify(identifier)}${workerSuffix()}.json`);
}

/**
 * Master switch. Set `WPUF_DISABLE_SESSION_REUSE=1` to turn the whole feature
 * off — logins then behave exactly as before (UI login every time, nothing
 * cached). Handy for A/B debugging or if a stale cache is ever suspected.
 */
export function sessionReuseEnabled(): boolean {
    const flag = process.env.WPUF_DISABLE_SESSION_REUSE;
    return !(flag === '1' || flag === 'true');
}

/** Create the `.auth/` directory if it does not exist yet. */
export function ensureAuthDir(): void {
    try {
        fs.mkdirSync(AUTH_DIR, { recursive: true });
    } catch {
        /* best-effort — a failure here just means we fall back to UI login */
    }
}

/**
 * Read saved cookies for a role, or `null` when no (valid) session file exists.
 * Only cookies are returned — WordPress auth is cookie-based, so that is all we
 * need to re-inject into a fresh context.
 */
export function readSavedCookies(identifier: string): any[] | null {
    try {
        const file = authFileFor(identifier);
        if (!fs.existsSync(file)) {
            return null;
        }
        const state = JSON.parse(fs.readFileSync(file, 'utf-8'));
        return Array.isArray(state?.cookies) && state.cookies.length > 0 ? state.cookies : null;
    } catch {
        return null;
    }
}

/** Delete a stale/invalid saved session so the next login re-creates it fresh. */
export function clearSavedSession(identifier: string): void {
    try {
        const file = authFileFor(identifier);
        if (fs.existsSync(file)) {
            fs.unlinkSync(file);
        }
    } catch {
        /* best-effort */
    }
}
