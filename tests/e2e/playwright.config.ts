import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config({ quiet: true });

export default defineConfig({
    testDir: './tests',

    timeout: 120000,

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
        
        // Ignore SSL certificate errors for local development
        ignoreHTTPSErrors: true,

        trace: 'retain-on-failure',

        screenshot: 'only-on-failure',

        video: 'off',

    },

    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
            },
        },
    ],
});