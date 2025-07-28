import type { PlaywrightTestConfig } from '@playwright/test';
import { devices } from '@playwright/test';

const config: PlaywrightTestConfig = {
    testDir: './tests',

    timeout: 60000,

    expect: {timeout: 30000},

    fullyParallel: false,

    forbidOnly: false,

    retries: process.env.CI ? 0 : 0,

    workers: process.env.CI ? 1 : 1,

    reporter: process.env.CI
        ? [
            ['list', { printSteps: true }],
            ['json', { outputFile: './test-results/results.json' , open: 'never'}],
            ['html', { outputFolder: './playwright-report', open: 'never' }]
        ]
        : [
            ['json', { outputFile: './test-results/results.json' , open: 'never'}],
            ['html', { outputFolder: './playwright-report', open: 'never' }],
        ],
    use: {

        actionTimeout: 0,

        headless: true,

        viewport: { width: 1280, height: 720 },

        ignoreHTTPSErrors: true,

        trace: 'retain-on-failure',

        screenshot: 'only-on-failure',

        video: 'retain-on-failure',

    },

    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],
};

export default config;