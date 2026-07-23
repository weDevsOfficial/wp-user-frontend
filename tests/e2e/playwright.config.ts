import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config({ quiet: true });

const isCI = !!process.env.CI;

/**
 * Unified Playwright config with Dokan-style logical sharding.
 *
 * CI splits the suite across independent machines with `--shard=i/N`. Each shard:
 *   - runs its own wp-env (isolated WordPress) — no shared server state between shards,
 *   - runs the `setup` project as a dependency (full plugin activation + license +
 *     WPUF setup), so every shard is self-contained,
 *   - runs serially (`workers: 1`) so concurrent admin sessions can't collide and
 *     intercept each other's clicks (the old forced-parallel race).
 *
 * `fullyParallel` stays off so sharding splits at the FILE level — each spec's serial
 * intra-file order (form created by test 1, consumed by test 2) is preserved on one shard.
 * Blob reports from every shard are merged with `playwright merge-reports`.
 */
export default defineConfig({
    testDir: './tests',

    // WordPress admin can render slowly on loaded CI runners; give a test room.
    timeout: isCI ? 180000 : 120000,

    expect: { timeout: 30000 },

    // Keep off: shard splits by file, preserving each spec's serial intra-file order.
    fullyParallel: false,

    forbidOnly: isCI,

    // Two retries on CI so a genuinely transient failure recovers on a fresh run.
    retries: isCI ? 2 : 0,

    // One worker per shard: serial execution against a single wp-env, no cross-file
    // admin-session race. Real parallelism comes from the CI shard matrix instead.
    workers: 1,

    // CI emits a blob per shard; a merge job combines them into one HTML report.
    reporter: isCI
        ? [
            ['blob'],
            ['list', { printSteps: true }],
        ]
        : [
            ['html', { outputFolder: './playwright-report', open: 'never' }],
            ['list', { printSteps: true }],
        ],

    use: {
        ...devices['Desktop Chrome'],

        // Finite action timeout: a click blocked by a first-run modal/overlay fails
        // fast (~30s) so a retry can recover, instead of hanging to the test timeout.
        actionTimeout: 30000,

        // Generous navigation: heavy wp-admin/product screens are slow on CI runners.
        navigationTimeout: 120000,

        headless: true,

        viewport: { width: 1280, height: 720 },

        trace: 'retain-on-failure',

        screenshot: 'only-on-failure',

        video: 'off',

        ignoreHTTPSErrors: true,
    },

    projects: [
        // Global setup: login, activate lite + pro, activate license, WPUF setup,
        // permalinks, base taxonomy. Runs in full on every shard as a dependency.
        {
            name: 'setup',
            testMatch: 'tests/alphaSetupTest.spec.ts',
        },

        // Actual e2e suite. Sharded across machines; depends on `setup` so each shard
        // stands up its own fully-configured site first.
        {
            name: 'e2e',
            testMatch: /.*\.spec\.ts/,
            testIgnore: 'tests/alphaSetupTest.spec.ts',
            dependencies: ['setup'],
        },
    ],
});
