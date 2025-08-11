import { test } from '@playwright/test';

/**
 * Configure fail-fast behavior for a spec file
 * This will stop running tests within the same spec when one fails
 */
export function configureSpecFailFast() {
    test.describe.configure({ mode: 'serial', retries: 0 });
}