import { APIRequestContext, APIResponse, expect, request as pwRequest } from '@playwright/test';
import { Urls } from '../../utils/testData';

/**
 * REST API client + assertions for the WPUF `wpuf/v1` namespace.
 *
 * Mirrors the Page Object Model for the UI: raw request helpers + `validate*`
 * methods that hold the `expect()` assertions, so specs only orchestrate.
 *
 * Auth: an admin WordPress Application Password sent as HTTP Basic auth (see
 * `createAdminAppPassword` in utils/wpEnvCli.ts). The auth header is attached
 * per-request, never as a context default, so `validateUnauthorizedBlocked`
 * can issue genuinely unauthenticated calls from the same client.
 */
export class WpufApi {
    private ctx: APIRequestContext;
    private authHeader: Record<string, string>;
    readonly nsBase = '/wp-json/wpuf/v1';

    private constructor(ctx: APIRequestContext, authHeader: Record<string, string>) {
        this.ctx = ctx;
        this.authHeader = authHeader;
    }

    /**
     * Build a request context bound to the site. Pass an admin Application
     * Password to authenticate; omit it for an unauthenticated client.
     */
    static async create(appPassword?: string): Promise<WpufApi> {
        const ctx = await pwRequest.newContext({ baseURL: Urls.baseUrl, ignoreHTTPSErrors: true });
        const authHeader = appPassword
            ? { Authorization: 'Basic ' + Buffer.from(`admin:${appPassword}`).toString('base64') }
            : {};
        return new WpufApi(ctx, authHeader);
    }

    async dispose() {
        await this.ctx.dispose();
    }

    /* ------------------------------ raw requests ------------------------------ */

    private url(path: string) {
        return this.nsBase + path;
    }

    async get(path: string, auth = true): Promise<APIResponse> {
        return this.ctx.get(this.url(path), { headers: auth ? this.authHeader : {} });
    }

    async post(path: string, data: unknown, auth = true): Promise<APIResponse> {
        return this.ctx.post(this.url(path), {
            headers: { ...(auth ? this.authHeader : {}), 'Content-Type': 'application/json' },
            data: data as Record<string, unknown>,
        });
    }

    async del(path: string, auth = true): Promise<APIResponse> {
        return this.ctx.delete(this.url(path), { headers: auth ? this.authHeader : {} });
    }

    private async findSubscriptionIdByTitle(title: string): Promise<number | null> {
        const res = await this.get('/wpuf_subscription');
        expect(res.status()).toBe(200);
        const body = await res.json();
        const items = (body.subscriptions || body.result || []) as Array<Record<string, unknown>>;
        const match = items.find( ( s ) => String( s.post_title ) === title );
        return match ? Number( match.ID ?? match.id ) : null;
    }

    /* ------------------------------- validate* -------------------------------- */

    /**
     * Every admin route must reject unauthenticated access with 401
     * `rest_forbidden` (permission_callback = current_user_can(wpuf_admin_role)).
     */
    async validateUnauthorizedBlocked(method: string, path: string) {
        const res = await this.ctx.fetch(this.url(path), { method });
        expect(res.status(), `${method} ${path} without auth should be 401`).toBe(401);
        const body = await res.json();
        expect(body.code, `${method} ${path} error code`).toBe('rest_forbidden');
        console.log('\x1b[32m%s\x1b[0m', `✅ ${method} ${path} blocked unauthenticated (401 rest_forbidden)`);
    }

    // GET /wpuf_form -> 200, success, array of forms with the documented shape.
    async validateFormsList() {
        const res = await this.get('/wpuf_form');
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success).toBe(true);
        expect(Array.isArray(body.result)).toBe(true);
        if (body.result.length) {
            const form = body.result[0];
            for (const key of ['ID', 'post_title', 'form_status', 'post_status']) {
                expect(form, `form field ${key}`).toHaveProperty(key);
            }
        }
        console.log('\x1b[32m%s\x1b[0m', `✅ GET /wpuf_form -> 200, ${body.result.length} forms, schema ok`);
        return body.result;
    }

    // GET /wpuf_subscription/count -> 200 with a count map (at least "all").
    async validateSubscriptionCount() {
        const res = await this.get('/wpuf_subscription/count');
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success).toBe(true);
        expect(body.count).toHaveProperty('all');
        console.log('\x1b[32m%s\x1b[0m', `✅ GET /wpuf_subscription/count -> 200 (all=${body.count.all})`);
        return body.count;
    }

    // GET /subscription-settings -> 200 with the settings object.
    async validateSubscriptionSettings() {
        const res = await this.get('/subscription-settings');
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body).toHaveProperty('button_color');
        console.log('\x1b[32m%s\x1b[0m', '✅ GET /subscription-settings -> 200');
        return body;
    }

    /**
     * Full CRUD round-trip via the API: create a subscription pack, confirm it
     * appears in the list, delete it, and confirm it is gone. Self-cleaning.
     */
    async validateSubscriptionCrudRoundTrip(title: string) {
        const createRes = await this.post('/wpuf_subscription', {
            subscription: {
                post_title: title,
                post_status: 'publish',
                post_content: 'created via API e2e',
                billing_amount: '9',
                _billing_amount: '9',
                _cycle_period: 'month',
                _billing_cycle_number: '1',
            },
        });
        expect(createRes.status(), 'create status').toBe(200);
        expect((await createRes.json()).success, 'create success').toBe(true);

        const id = await this.findSubscriptionIdByTitle(title);
        expect(id, `created pack "${title}" should appear in the list`).toBeTruthy();

        const delRes = await this.del('/wpuf_subscription/' + id);
        expect(delRes.status(), 'delete status').toBe(200);
        expect((await delRes.json()).success, 'delete success').toBe(true);

        const goneId = await this.findSubscriptionIdByTitle(title);
        expect(goneId, `deleted pack "${title}" should be gone`).toBeNull();
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription CRUD round-trip ok (create → read → delete): "${title}" (id ${id})`);
    }

    /**
     * Input validation: a malformed create payload (missing `subscription`) is
     * rejected by the handler (authorized, so 200 with success:false) rather than
     * creating a bogus pack.
     */
    async validateBadCreatePayloadRejected() {
        const res = await this.post('/wpuf_subscription', { not_a_subscription: true });
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success, 'bad payload should not succeed').toBe(false);
        console.log('\x1b[32m%s\x1b[0m', '✅ POST /wpuf_subscription with bad payload rejected (success:false)');
    }
}
