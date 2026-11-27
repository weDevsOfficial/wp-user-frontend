import * as dotenv from 'dotenv';
dotenv.config({ quiet: true });
import { execFileSync, execSync } from 'child_process';
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Selectors } from './selectors';
import { wpCli } from '../utils/wpEnvCli';
import { BUILDER_URL, builderFormId } from '../utils/builderUrl';

/** The key the mock provider (tests/e2e/wp/wpuf-ai-mock.php) answers. */
export const AI_MOCK_KEY = 'sk-wpuf-e2e-mock';

/**
 * WP-CLI on the site under test: wp-env by default, or a local WordPress root
 * when WPUF_E2E_WP_PATH is set (e.g. a Herd site with the mock in mu-plugins).
 */
export function aiWp(args: string[], loadPlugins = false): string {
    const wpPath = process.env.WPUF_E2E_WP_PATH;

    if (wpPath) {
        return execFileSync('wp', [`--path=${wpPath}`, ...args], { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'] });
    }

    const quoted = args.map((arg) => `'${arg.replace(/'/g, `'\\''`)}'`).join(' ');

    if (!loadPlugins) {
        return wpCli(quoted);
    }

    // Reads that need WPUF (and the integrations) loaded; wpCli() skips plugins.
    const container = (process.env.QA_BASE_URL || '').includes(':8888') ? 'cli' : 'tests-cli';
    return execSync(`npx @wordpress/env run ${container} wp ${quoted}`, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'], cwd: process.env.WPUF_E2E_WP_ENV_DIR || process.cwd() });
}

/** The JSON a `wp eval` printed (wp-env adds its own status lines around it). */
function evalJson<T>(out: string): T {
    const line = out.split('\n').map((row) => row.trim()).find((row) => row.startsWith('[') || row.startsWith('{')) || 'null';
    return JSON.parse(line.replace(/✔.*$/, '').trim()) as T;
}

/**
 * Page Object for the AI form builder (React, `#wpuf-ai-form-builder`).
 *
 * Provider calls are answered by the mock mu-plugin when the stored OpenAI key
 * is AI_MOCK_KEY: prompts with "mock-error" / "not-a-form" / "pro-fields" pick
 * its error, refusal and Pro-field answers; chat messages with "website",
 * "date" or "remove the message" change the current form.
 */
export class AiFormBuilderPage extends Base {
    private saved: string | null = null;

    constructor(page: Page) {
        super(page);
    }

    get S() {
        return Selectors.aiFormBuilder;
    }

    /** Store the mock provider settings (the previous `wpuf_ai` is kept for restore()). */
    configureMock(key = AI_MOCK_KEY, provider: 'openai' | 'google' = 'openai') {
        if (null === this.saved) {
            try {
                this.saved = aiWp(['option', 'get', 'wpuf_ai', '--format=json']).trim();
            } catch {
                this.saved = '';
            }
        }

        const model = 'google' === provider ? 'gemini-mock-flash' : 'gpt-4o-mini';
        const value = JSON.stringify({ ai_provider: provider, ai_model: model, [`${provider}_api_key`]: key, temperature: '0.7' });
        aiWp(['option', 'update', 'wpuf_ai', value, '--format=json']);
        aiWp(['option', 'update', 'wpuf_ai_mock_calls', '0']);
        aiWp(['transient', 'delete', 'wpuf_ai_models_cache']);
    }

    /** Put `wpuf_ai` back as it was before configureMock(). */
    restore() {
        if (null === this.saved) {
            return;
        }

        if ('' === this.saved) {
            aiWp(['option', 'delete', 'wpuf_ai']);
        } else {
            aiWp(['option', 'update', 'wpuf_ai', this.saved, '--format=json']);
        }

        this.saved = null;
    }

    /** Provider requests the mock answered since configureMock(). */
    mockCalls(): number {
        return parseInt(aiWp(['option', 'get', 'wpuf_ai_mock_calls']).trim(), 10) || 0;
    }

    /** Whether WPUF Pro is active on the site under test. */
    proActive(): boolean {
        // By class, not folder: a dev checkout is `wpuf-pro`, the release zip `wp-user-frontend-pro`.
        try {
            return /PRO_ACTIVE/.test(aiWp(['eval', "echo class_exists( 'WP_User_Frontend_Pro' ) ? 'PRO_ACTIVE' : 'PRO_OFF';"], true));
        } catch {
            return false;
        }
    }

    /** Fields stored for a form: [ { template, label, required } ]. */
    storedFields(formId: number): { template: string; label: string; required: string }[] {
        const out = aiWp(['eval', `echo PHP_EOL, wp_json_encode( array_map( function ( $f ) { return [ 'template' => $f['template'], 'label' => $f['label'], 'required' => $f['required'] ?? '' ]; }, wpuf_get_form_fields( ${formId} ) ) ), PHP_EOL;`], true);
        return evalJson(out);
    }

    /** Model ids the AI settings offer (cached list, Google models included after a fetch). */
    modelIds(): string[] {
        const out = aiWp(['eval', 'echo PHP_EOL, wp_json_encode( array_keys( \\WeDevs\\Wpuf\\AI\\Config::get_models() ) ), PHP_EOL;'], true);
        return evalJson(out);
    }

    /** Post type and AI meta of a form. */
    storedForm(formId: number): { post_type: string; ai: string; title: string } {
        const out = aiWp(['eval', `$p = get_post( ${formId} ); echo PHP_EOL, wp_json_encode( [ 'post_type' => $p->post_type, 'title' => $p->post_title, 'ai' => get_post_meta( ${formId}, 'wpuf_ai_generated', true ) ] ), PHP_EOL;`], true);
        return evalJson(out);
    }

    /** Open the post forms list (or the registration list) and click "AI Form Builder". */
    async openFromList(list: 'post' | 'profile' = 'post') {
        const slug = 'profile' === list ? 'wpuf-profile-forms' : 'wpuf-post-forms';
        await this.navigateToURL(`${this.wpAdminPage}admin.php?page=${slug}`);
        await this.page.locator(this.S.listButton).first().click();
    }

    /** Open the AI builder and wait for the input stage. */
    async open(list: 'post' | 'profile' = 'post') {
        await this.openFromList(list);
        await expect(this.page.locator(this.S.inputHeading)).toBeVisible({ timeout: 30000 });
    }

    /** Type a description and generate; waits for the success stage. */
    async generate(description: string) {
        await this.page.locator(this.S.description).fill(description);
        await this.page.locator(this.S.generateButton).click();
        await expect(this.page.locator(this.S.processingHeading)).toBeVisible();
        await expect(this.page.locator(this.S.successHeading)).toBeVisible({ timeout: 30000 });
    }

    /** Send a chat message and wait for the assistant's reply. */
    async chat(message: string) {
        const replies = await this.page.locator(this.S.aiMessages).count();
        await this.page.locator(this.S.chatInput).fill(message);
        await this.page.locator(this.S.sendButton).click();
        await expect(this.page.locator(this.S.aiMessages)).toHaveCount(replies + 1, { timeout: 30000 });
        await expect(this.page.locator(this.S.aiMessages).last().locator('svg[role="img"]')).toHaveCount(0, { timeout: 30000 });
    }

    /** Integration ids the server reports as active for a form type (REST, same as the app). */
    activeIntegrations(formType: 'post' | 'profile'): string[] {
        const out = aiWp(['eval', `wp_set_current_user( 1 ); $r = new WP_REST_Request( 'GET', '/wpuf/v1/ai-form-builder/integrations' ); $r->set_param( 'form_type', '${formType}' ); $d = rest_do_request( $r )->get_data(); echo PHP_EOL, wp_json_encode( array_values( array_map( function ( $i ) { return $i['id']; }, array_filter( $d['integrations'] ?? [], function ( $i ) { return ! empty( $i['enabled'] ); } ) ) ) ), PHP_EOL;`], true);
        return evalJson(out);
    }

    /** Pick an integration in the "Form Type (Optional)" select by its label. */
    async selectIntegration(label: string) {
        await this.page.locator(this.S.integrationSelect).click();
        await this.page.locator(this.S.selectItem, { hasText: label }).first().click();
        await expect(this.page.locator(this.S.integrationSelect)).toContainText(label);
    }

    /** Labels of the prompt template buttons. */
    async promptLabels(): Promise<string[]> {
        return this.page.locator(this.S.promptButtons).allInnerTexts();
    }

    /** Labels of the preview fields, in order. */
    async previewLabels(): Promise<string[]> {
        return this.page.locator(this.S.previewFields).evaluateAll((nodes) => nodes.map((node) => (node.firstElementChild?.textContent || '').replace('*', '').trim()));
    }

    /** "Edit with Builder" and wait for the builder of the new form; returns its id. */
    async editInBuilder(): Promise<number> {
        await this.page.locator(this.S.editInBuilderButton).click();
        await this.page.waitForURL(BUILDER_URL, { timeout: 30000 });
        return parseInt(builderFormId(this.page.url()) || '0', 10);
    }
}
