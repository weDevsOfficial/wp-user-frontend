import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { ParitySitePage } from '../pages/parity';
import { paritySite, paritySitesConfigured, parityWp, type ParitySite } from '../utils/paritySites';
import { BUILDER_URL, builderFormId } from '../utils/builderUrl';

/**
 * PAR0040: the AI form builder creates the same form on develop (Vue) and the
 * branch (React). Both sites get the mock provider (tests/e2e/wp/wpuf-ai-mock.php)
 * and its key, run the same flow (generate, one accepted chat change, Edit with
 * Builder), then the stored fields and AI meta are compared.
 */
const MOCK = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..', 'wp', 'wpuf-ai-mock.php');
const KEY = 'sk-wpuf-e2e-mock';

function install(site: ParitySite): string {
    const target = path.join(site.wpPath, 'wp-content', 'mu-plugins', 'wpuf-ai-mock.php');
    fs.mkdirSync(path.dirname(target), { recursive: true });
    fs.copyFileSync(MOCK, target);

    let before = '';
    try {
        before = parityWp(site, ['option', 'get', 'wpuf_ai', '--format=json']).trim();
    } catch {
        before = '';
    }
    parityWp(site, ['option', 'update', 'wpuf_ai', JSON.stringify({ ai_provider: 'openai', ai_model: 'gpt-4o-mini', openai_api_key: KEY, temperature: '0.7' }), '--format=json']);
    return before;
}

function restore(site: ParitySite, before: string) {
    if (before) {
        parityWp(site, ['option', 'update', 'wpuf_ai', before, '--format=json']);
    } else {
        parityWp(site, ['option', 'delete', 'wpuf_ai']);
    }
}

function stored(site: ParitySite, formId: number) {
    const out = parityWp(site, ['eval', `$strip = function ( $f ) { unset( $f['id'] ); ksort( $f ); return $f; }; echo wp_json_encode( [ 'type' => get_post_type( ${formId} ), 'title' => get_the_title( ${formId} ), 'ai' => get_post_meta( ${formId}, 'wpuf_ai_generated', true ), 'fields' => array_map( $strip, wpuf_get_form_fields( ${formId} ) ) ] );`]);
    return JSON.parse(out.trim());
}

test.describe('Parity AI form builder', () => {
    test.skip(!paritySitesConfigured(), 'PARITY_* sites not configured');

    test('PAR0040 : generate, accept a chat change and Edit with Builder store the same form on develop and the branch', { tag: ['@Parity', '@Test_PAR0040'] }, async ({ browser }) => {
        test.setTimeout(240000);
        const results: Record<string, unknown> = {};

        for (const name of ['develop', 'branch'] as const) {
            const site = paritySite(name);
            const before = install(site);

            try {
                const admin = await ParitySitePage.doOpen(browser, site);
                const page = admin.page;

                await page.goto('/wp-admin/admin.php?page=wpuf-post-forms');
                await page.getByRole('button', { name: /AI Form Builder/ }).first().click();
                await expect(page.getByText('Create Form with AI').first()).toBeVisible({ timeout: 30000 });
                await page.locator('#wpuf-ai-form-builder textarea').first().fill('Create a contact form');
                await page.getByRole('button', { name: /Generate Form/ }).click();
                await expect(page.getByText('Generate forms instantly with AI assistance')).toBeVisible({ timeout: 30000 });

                const chat = page.locator('#wpuf-ai-form-builder textarea').last();
                await chat.fill('Add a website field');
                await chat.press('Enter');
                await page.getByRole('button', { name: 'Accept' }).click();
                await expect(page.getByText('Changes accepted & checkpoint saved')).toBeVisible();

                await page.getByRole('button', { name: /Edit with Builder/ }).first().click();
                await page.waitForURL(BUILDER_URL, { timeout: 30000 });
                const formId = parseInt(builderFormId(page.url()) || '0', 10);
                results[name] = stored(site, formId);
                await admin.doClose();
            } finally {
                restore(site, before);
            }
        }

        await test.info().attach('stored.json', { body: JSON.stringify(results, null, 1), contentType: 'application/json' });
        expect(results.branch, 'same stored form').toEqual(results.develop);
        expect((results.branch as { fields: { label: string }[] }).fields.map((field) => field.label)).toEqual(['Title', 'Message', 'Email', 'Topic', 'Website']);
    });
});
