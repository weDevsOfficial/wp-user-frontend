import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config({ quiet: true });

/**
 * Single Playwright config for the whole suite.
 *
 * Replaces the old per-phase configs (playwright.setup / .parallel / .api). The
 * phases are now `projects`, selected from the CLI:
 *
 *   npx playwright test --project=setup                  # site reset + config (run first)
 *   npx playwright test --project=e2e --shard=1/3        # sharded UI suite
 *   npx playwright test --project=api                    # REST API layer (no browser)
 *
 * `npm run test:sharded` chains these: `--project=setup`, then the three
 * `--project=e2e --shard=i/3` invocations sequentially. We deliberately do NOT
 * use `dependencies: ['setup']` — under `--shard` a setup dependency reruns the
 * heavy, destructive site reset once per shard. Keeping setup as its own script
 * step preserves the "reset once, then shard" semantics from a single config.
 *
 * `SHARD_INDEX` (passed with `--shard=i/n`) and `E2E_PHASE` (`setup`|`api`) only
 * pick per-phase report/output paths so sequential invocations don't clobber each
 * other and `utils/sharded-summary.js` finds each phase's JSON. They do not affect
 * which tests run — `--project` and `--shard` decide that.
 */

const shardIndex = process.env.SHARD_INDEX && process.env.SHARD_INDEX !== ''
    ? process.env.SHARD_INDEX
    : '';
const phase = process.env.E2E_PHASE || '';

// CI toggles longer timeouts and forbids `test.only`.
const isCI = process.env.CI === 'true';

// JSON report path — consumed by utils/sharded-summary.js for setup + each shard.
const jsonOutput =
    phase === 'setup' ? './setup/setup-results.json'
    : phase === 'api' ? './api/api-results.json'
    : shardIndex ? `./parallel-results/shard-${shardIndex}-results.json`
    : './test-results/results.json';

// HTML report folder, kept separate per phase/shard.
const htmlOutput =
    phase === 'setup' ? './playwright-report/setup-report'
    : phase === 'api' ? './playwright-report/api-report'
    : shardIndex ? `./playwright-report/parallel-${shardIndex}-report`
    : './playwright-report';

// Artifact (trace/screenshot) dir — per shard so sequential shards don't clobber.
const artifactDir = shardIndex ? `./test-results/shard-${shardIndex}` : './test-results';

export default defineConfig({
    testDir: './tests',

    // WordPress admin can render slowly on loaded CI runners; give a test room.
    timeout: isCI ? 180000 : 120000,

    expect: { timeout: 30000 },

    // Sequential — never run two stateful specs against the shared site at once.
    fullyParallel: false,

    forbidOnly: isCI,

    retries: 0,

    workers: 1,

    outputDir: artifactDir,

    reporter: [
        ['list', { printSteps: false }],
        ['json', { outputFile: jsonOutput }],
    ],

    // Shared defaults. CLI `--headed` overrides `headless` per-invocation.
    use: {
        actionTimeout: 0,
        headless: true,
        viewport: { width: 1280, height: 720 },
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'off',
        ignoreHTTPSErrors: true,
    },

    projects: [
        // Site reset + config. Run first, once (never as a shard dependency).
        {
            name: 'setup',
            testMatch: 'tests/alphaSetupTest.spec.ts',
            use: { ...devices['Desktop Chrome'] },
        },
        // Stateful UI suite. Split via native `--shard=i/n`. Everything under
        // tests/ except the setup spec and the browserless API layer.
        {
            name: 'e2e',
            testDir: './tests',
            testMatch: '**/*.spec.ts',
            testIgnore: ['**/alphaSetupTest.spec.ts', '**/api/**'],
            use: { ...devices['Desktop Chrome'] },
        },
        // REST layer (wpuf/v1) — no browser launched.
        {
            name: 'api',
            testDir: './tests/api',
            testMatch: '**/*.spec.ts',
        },
    ],
});
