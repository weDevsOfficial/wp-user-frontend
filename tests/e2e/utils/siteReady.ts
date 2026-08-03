import { Page } from '@playwright/test';
import { Urls } from './testData';

/**
 * Poll the site until it responds, instead of a fixed-length sleep.
 *
 * Replaces the old spec-start `waitForTimeout(15000/30000)` guards, which
 * existed to let the wp-env site settle after the destructive setup phase or a
 * previous heavy spec. Polling returns in ~1s when the site is already up
 * (the common case) while keeping the same worst-case ceiling.
 */
export async function waitForSiteReady(page: Page, maxMs: number = 30000): Promise<void> {
    const probeUrl = `${Urls.baseUrl}/wp-login.php`;
    const deadline = Date.now() + maxMs;
    while (Date.now() < deadline) {
        try {
            const res = await page.request.get(probeUrl, { timeout: 10000 });
            if (res.ok()) {
                return;
            }
        } catch {
            // site not up yet — keep polling
        }
        await page.waitForTimeout(1000);
    }
}
