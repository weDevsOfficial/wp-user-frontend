import * as dotenv from 'dotenv';
import { execFileSync } from 'child_process';
import * as path from 'path';
import { fileURLToPath } from 'url';

dotenv.config({ quiet: true });

/**
 * The two sites the parity suite compares: `develop` (the reference build) and
 * `branch` (the build under test). Each needs a URL and a local WordPress root
 * for WP-CLI. Configure in `.env`:
 *
 *   PARITY_DEVELOP_URL=http://wpuf-vue-baseline.test
 *   PARITY_DEVELOP_PATH=/path/to/wpuf-vue-baseline
 *   PARITY_BRANCH_URL=http://wpuf-react-combo.test
 *   PARITY_BRANCH_PATH=/path/to/wpuf-react-combo
 *
 * Admin credentials come from QA_ADMIN_USERNAME / QA_ADMIN_PASSWORD (same user on both).
 */
export type ParitySiteName = 'develop' | 'branch';

export interface ParitySite {
    name: ParitySiteName;
    url: string;
    wpPath: string;
}

function requireEnv(key: string): string {
    const value = process.env[key];
    if (!value) {
        throw new Error(`Parity suite: ${key} is not set (see utils/paritySites.ts)`);
    }
    return value.replace(/\/$/, '');
}

export function paritySite(name: ParitySiteName): ParitySite {
    const prefix = name === 'develop' ? 'PARITY_DEVELOP' : 'PARITY_BRANCH';
    return { name, url: requireEnv(`${prefix}_URL`), wpPath: requireEnv(`${prefix}_PATH`) };
}

export function paritySitesConfigured(): boolean {
    return ['PARITY_DEVELOP_URL', 'PARITY_DEVELOP_PATH', 'PARITY_BRANCH_URL', 'PARITY_BRANCH_PATH']
        .every((key) => !!process.env[key]);
}

/** Directory holding the parity PHP helpers and fixtures. */
export const parityDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', 'parity');

/**
 * Run WP-CLI against one parity site and return stdout. Arguments are passed as
 * an array (no shell), so values never need quoting.
 */
export function parityWp(site: ParitySite, args: string[]): string {
    return execFileSync('wp', [`--path=${site.wpPath}`, ...args], {
        encoding: 'utf-8',
        stdio: ['pipe', 'pipe', 'pipe'],
    });
}

/**
 * Whether WPUF Pro is active on the branch site. The parity suite is written
 * with Pro on; a free-only run (Pro deactivated on both sites) folds or skips
 * what only exists with Pro.
 */
export function parityProActive(): boolean {
    try {
        execFileSync('wp', [`--path=${paritySite('branch').wpPath}`, 'plugin', 'is-active', 'wpuf-pro'], { stdio: ['pipe', 'pipe', 'pipe'] });
        return true;
    } catch {
        return false;
    }
}
