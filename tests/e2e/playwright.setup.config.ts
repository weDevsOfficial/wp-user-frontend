import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config({ quiet: true });

export default defineConfig({
    testDir: './tests',

    timeout: 60000,

    expect: {
        timeout: 30000,
    },

    fullyParallel: false,

    forbidOnly: false,

    retries: process.env.CI ? 0 : 0,

    workers: process.env.CI ? 1 : 1,

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

    use: {

        actionTimeout: 0,

        headless: true,

        // Context options
        viewport: { width: 1280, height: 720 },

        trace: 'retain-on-failure',

        screenshot: 'only-on-failure',

        video: 'off',

        ignoreHTTPSErrors: true,
    },

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