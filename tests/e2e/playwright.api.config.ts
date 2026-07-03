import { defineConfig } from '@playwright/test';
import * as dotenv from 'dotenv';

dotenv.config({ quiet: true });

/**
 * Playwright configuration for the REST API test layer (wpuf/v1).
 *
 * No browser is launched — specs use Playwright's request context via the
 * WpufApi client. Independent of the stateful UI suite, so it can run on its own.
 */
export default defineConfig({
    testDir: './tests/api',
    testMatch: '**/*.spec.ts',
    timeout: 60000,
    expect: { timeout: 15000 },
    fullyParallel: false,
    forbidOnly: false,
    retries: 0,
    workers: 1,
    reporter: process.env.CI
        ? [
            ['list', { printSteps: true }],
            ['json', { outputFile: './api/api-results.json' }],
            ['html', { outputFolder: './playwright-report/api-report', open: 'never' }],
        ]
        : [
            ['list'],
            ['html', { outputFolder: './playwright-report/api-report', open: 'never' }],
        ],
    use: {
        ignoreHTTPSErrors: true,
    },
});
