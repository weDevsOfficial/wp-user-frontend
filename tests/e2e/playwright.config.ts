import type { PlaywrightTestConfig } from '@playwright/test';
import { devices } from '@playwright/test';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
// require('dotenv').config();

/**
 * See https://playwright.dev/docs/test-configuration.
 */
const config: PlaywrightTestConfig = {
    testDir: './tests',
    testMatch: ['**/*.spec.ts'],
    /* Maximum time one test can run for. */
    timeout: 60000, //60 sec
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
    /* Opt out of parallel tests on CI. */
    workers: process.env.CI ? 1 : undefined,
    /* Reporter to use. See https://playwright.dev/docs/test-reporters */
    reporter: process.env.CI
        ? [
            ['list', { printSteps: true }],
            ['json', { outputFile: './test-results/results.json' , open: 'never'}],
            ['html', { outputFolder: './playwright-report', open: 'never' }]
        ]
        : [
            ['html', { outputFolder: './playwright-report', open: 'never' }],
        ],
    /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
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

        //SlowMo
        launchOptions: {
            //slowMo: 1000,
        },
    },

    /* Configure projects for major browsers */
    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                storageState: 'state.json',
            },
        },
    ],
};

export default config;
