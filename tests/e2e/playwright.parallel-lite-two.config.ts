import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config();

/**
 * Playwright configuration for PARALLEL LITE TWO phase
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
            ['json', { outputFile: './parallel-two/parallel-lite-two-results.json' }],
            ['html', { outputFolder: './playwright-report/parallel-lite-two-report', open: 'never' }]
        ]
        : [
            ['json', { outputFile: './parallel-two/parallel-lite-two-results.json' }],
            ['html', { outputFolder: './playwright-report/parallel-lite-two-report', open: 'never' }],
        ],
    use: {
        actionTimeout: 0,
        trace: 'off',
        headless: true,
        viewport: { width: 1280, height: 720 },
        screenshot: 'only-on-failure',
        video: 'off',
    },
    projects: [
        {
            name: 'parallel-lite-two',
            testMatch: [
                'tests/postFormSettingsTest.spec.ts',
                'tests/regFormSettingsTestPro.spec.ts',
            ],
            use: { ...devices['Desktop Chrome'] },
        },
    ],
}); 