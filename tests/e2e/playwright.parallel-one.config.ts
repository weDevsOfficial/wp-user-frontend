import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config();

/**
 * Playwright configuration for PARALLEL LITE ONE phase
 */
export default defineConfig({
    testDir: './tests',
    timeout: 60000,
    expect: { timeout: 30000 },
    fullyParallel: false,
    forbidOnly: false,
    retries: process.env.CI ? 0 : 0,
    workers: process.env.CI ? 2 : 2,
    reporter: process.env.CI
        ? [
            ['list', { printSteps: true }],
            ['json', { outputFile: './parallel-one/parallel-one-results.json' }],
            ['html', { outputFolder: './playwright-report/parallel-one-report', open: 'never' }]
        ]
        : [
            ['json', { outputFile: './parallel-one/parallel-one-results.json' }],
            ['html', { outputFolder: './playwright-report/parallel-one-report', open: 'never' }],
        ],
    use: {
        actionTimeout: 0,
        trace: 'off',
        headless: true,
        viewport: { width: 1280, height: 720 },
        screenshot: 'only-on-failure',
        video: 'off',
        ignoreHTTPSErrors: true,
    },
    projects: [
        {
            name: 'parallel-one',
            testMatch: [
                'tests/postFormTest.spec.ts',
                'tests/regFormTestPro.spec.ts',
            ],
            use: { ...devices['Desktop Chrome'] },
        },
    ],
}); 