import { test, expect } from '@playwright/test';
import { ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured, parityProActive, parityWp } from '../utils/paritySites';
import { BUILDER_URL, builderFormId } from '../utils/builderUrl';

/**
 * The React form template picker ("Add New" on the forms lists): every card
 * has a real screenshot, the hover / focus preview scrolls through it, and a
 * template still creates its form.
 */
test.describe('Form template picker', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    const screens = [
        { id: 'TPL0001', slug: 'wpuf-post-forms', template: 'post_form_template_post', postType: 'wpuf_forms', pro: false },
        { id: 'TPL0002', slug: 'wpuf-profile-forms', template: 'simple_user_signup_template', postType: 'wpuf_profile', pro: true },
    ];

    for (const screen of screens) {
        test(`${screen.id} : ${screen.slug} picker shows screenshots, previews them and creates a form`, { tag: ['@Parity', `@Test_${screen.id}`] }, async ({ browser }) => {
            test.skip(screen.pro && !parityProActive(), 'registration forms need Pro');

            const branch = paritySite('branch');
            const admin = await ParitySitePage.doOpen(browser, branch);
            const page = admin.page;
            const errors: string[] = [];
            page.on('pageerror', (error) => errors.push(String(error)));

            await page.goto(`/wp-admin/admin.php?page=${screen.slug}`);
            await page.locator('.new-wpuf-form').first().click();
            const picker = page.locator('.wpuf-template-picker');
            await expect(picker).toBeVisible();
            await expect(picker.getByPlaceholder('Search Templates')).toBeFocused();
            // The PHP + jQuery modal is not printed any more.
            await expect(page.locator('.wpuf-form-template-modal')).toHaveCount(0);

            // Every template card has an image that loads.
            const cards = picker.locator('.wpuf-template-card');
            await picker.locator('.wpuf-template-card img[loading]').last().scrollIntoViewIfNeeded();
            await page.waitForLoadState('networkidle');
            const images = await cards.evaluateAll((list) => list
                .filter((card) => !['blank', 'ai_form'].includes((card as HTMLElement).dataset.template || ''))
                .map((card) => ({ key: (card as HTMLElement).dataset.template, width: (card.querySelector('img[loading]') as HTMLImageElement | null)?.naturalWidth || 0 })));
            expect(images.length).toBeGreaterThan(0);
            expect(images.filter((image) => image.width < 600)).toEqual([]);

            // The "All" count is the number of cards.
            await expect(picker.locator('[data-category="all"]')).toContainText(String(await cards.count()));

            // Mouse: the preview opens beside the card and scrolls by itself.
            const card = picker.locator(`[data-template="${screen.template}"]`);
            await card.scrollIntoViewIfNeeded();
            await card.hover();
            const area = page.locator('.wpuf-template-preview > div').last();
            await expect(area).toBeVisible();
            await expect.poll(() => area.evaluate((element) => element.scrollTop), { timeout: 5000 }).toBeGreaterThan(20);

            // Keyboard: focus opens it, the arrow keys take the scroll over.
            await page.mouse.move(2, 400);
            await expect(page.locator('.wpuf-template-preview')).toHaveCount(0);
            await card.locator('a').focus();
            await expect(area).toBeVisible();
            await page.keyboard.press('ArrowDown');
            const held = await area.evaluate((element) => element.scrollTop);
            await page.waitForTimeout(900);
            expect(await area.evaluate((element) => element.scrollTop)).toBe(held);

            // Search and Escape.
            await picker.getByPlaceholder('Search Templates').fill('no such template');
            await expect(picker.getByRole('status')).toBeVisible();
            await picker.getByPlaceholder('Search Templates').fill('');
            await page.keyboard.press('Escape');
            await expect(picker).toHaveCount(0);

            // The template still creates its form and opens the builder.
            await page.locator('.new-wpuf-form').first().click();
            await picker.locator(`[data-template="${screen.template}"] a`).click({ force: true });
            await page.waitForURL(BUILDER_URL);
            const formId = builderFormId(page.url());
            expect(parityWp(branch, ['post', 'get', formId, '--field=post_type']).trim()).toBe(screen.postType);
            // The form and its fields go again.
            const fields = parityWp(branch, ['post', 'list', '--post_type=wpuf_input', `--post_parent=${formId}`, '--format=ids']).trim();
            parityWp(branch, ['post', 'delete', ...fields.split(/\s+/).filter(Boolean), formId, '--force']);
            await admin.doClose();

            expect(errors).toEqual([]);
        });
    }
});
