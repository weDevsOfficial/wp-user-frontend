import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config();

/**
 * Playwright configuration for SETUP phase
 * This runs loginAndSetupTest.spec.ts with a single worker
 */
export default defineConfig({
    testDir: './tests',
    /* Maximum time one test can run for. */
    timeout: 60000, //10 sec
    expect: {
        /**
         * Maximum time expect() should wait for the condition to be met.
         * For example in `await expect(locator).toHaveText();`
         */
        timeout: 30000, //10 sec
    },
    /* Run tests in files in parallel */
    fullyParallel: false,
    /* Fail the build on CI if you accidentally left test.only in the source code. */
    forbidOnly: false,
    /* Retry on CI only */
    retries: process.env.CI ? 0 : 0,
    /* Single worker for setup phase */
    workers: process.env.CI ? 1 : 1,
    /* Reporter to use. See https://playwright.dev/docs/test-reporters */
    reporter: process.env.CI
        ? [
            ['list', { printSteps: true }],
            ['json', { outputFile: './setup/setup-results.json' }],
            ['html', { outputFolder: './playwright-report/setup-report', open: 'never' }]
        ]
        : [
            ['json', { outputFile: './setup/setup-results.json' }],
            ['html', { outputFolder: './playwright-report/setup-report', open: 'never' }],
        ],
    /* Shared settings for all the projects below. */
    use: {
        /* Maximum time each action such as `click()` can take. Defaults to 0 (no limit). */
        actionTimeout: 0,
        /* Base URL to use in actions like `await page.goto('/')`. */
        // baseURL: 'http://localhost:3000',

        /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
        trace: 'off',

        // Browser options
        headless: true,

        // Context options
        viewport: { width: 1280, height: 720 },

        // Artifacts
        screenshot: 'only-on-failure',

        video: 'off',
        ignoreHTTPSErrors: true,
    },

    /* Configure projects for major browsers */
    projects: [
        {
            name: 'setup',
            testMatch: 'tests/alphaSetupTest.spec.ts',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],
}); 