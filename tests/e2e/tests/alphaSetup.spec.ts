import { test } from '@playwright/test';
import loginAndSetupTest from './loginAndSetupTest.spec';
import resetSite from './resetSite.spec';

/**
 * Setup phase for both Lite and Pro versions
 * Runs with 1 worker to prepare the environment
 */
test.describe('Setup Phase', () => {
    // Reset site if not in CI
    if (process.env.CI == 'false') {
        test.describe('Reset Site', resetSite);
    }
    
    // Login and setup tests
    test.describe('Login and Setup', loginAndSetupTest);
}); 