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
    static async create(appPassword?: string, username = 'admin'): Promise<WpufApi> {
        const ctx = await pwRequest.newContext({ baseURL: Urls.baseUrl, ignoreHTTPSErrors: true });
        const authHeader = appPassword
            ? { Authorization: 'Basic ' + Buffer.from(`${username}:${appPassword}`).toString('base64') }
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

    // Find the first subscription whose title *contains* the token; returns the
    // matched record (with its stored title) so callers can assert on it.
    private async findSubscriptionByTitleContains(token: string): Promise<Record<string, unknown> | null> {
        const res = await this.get('/wpuf_subscription');
        expect(res.status()).toBe(200);
        const body = await res.json();
        const items = (body.subscriptions || body.result || []) as Array<Record<string, unknown>>;
        return items.find( ( s ) => String( s.post_title ).includes( token ) ) ?? null;
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

    /**
     * Pagination contract: `GET /wpuf_form?per_page=1&page=1` returns a pagination
     * block echoing the request and caps the page size.
     */
    async validateFormsPagination() {
        const res = await this.get('/wpuf_form?per_page=1&page=1');
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success).toBe(true);
        expect(body).toHaveProperty('pagination');
        for (const key of ['total_items', 'total_pages', 'current_page', 'per_page']) {
            expect(body.pagination, `pagination.${key}`).toHaveProperty(key);
        }
        expect(body.pagination.per_page, 'per_page echoed').toBe(1);
        expect(body.pagination.current_page, 'current_page echoed').toBe(1);
        expect(body.result.length, 'per_page=1 caps result size').toBeLessThanOrEqual(1);
        console.log('\x1b[32m%s\x1b[0m', `✅ GET /wpuf_form pagination ok (total_items=${body.pagination.total_items})`);
    }

    /**
     * Search: a term that matches nothing returns an empty result set with a zero
     * total — proves the `s` param is actually applied.
     */
    async validateFormsSearchNoMatch(token: string) {
        const res = await this.get('/wpuf_form?s=' + encodeURIComponent(token));
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success).toBe(true);
        expect(body.result.length, 'no-match search returns empty result').toBe(0);
        expect(body.pagination.total_items, 'no-match total is 0').toBe(0);
        console.log('\x1b[32m%s\x1b[0m', `✅ GET /wpuf_form?s=${token} -> empty (search applied)`);
    }

    /**
     * Authorization: an authenticated NON-admin (this client is built with a
     * subscriber's Application Password) must be rejected with 403 `rest_forbidden`
     * on the admin-guarded routes — the capability gate, not just the login gate.
     */
    async validateForbiddenForRole(method: string, path: string) {
        const res = await this.ctx.fetch(this.url(path), { method, headers: this.authHeader });
        expect(res.status(), `${method} ${path} as non-admin should be 403`).toBe(403);
        const body = await res.json();
        expect(body.code, `${method} ${path} error code`).toBe('rest_forbidden');
        console.log('\x1b[32m%s\x1b[0m', `✅ ${method} ${path} forbidden for non-admin (403 rest_forbidden)`);
    }

    /**
     * Business rule: a pack name containing `#` is rejected (PayPal disallows `#`
     * in package names). Authorized call → 200 with success:false + a message.
     */
    async validateSubscriptionNameWithHashRejected() {
        const res = await this.post('/wpuf_subscription', {
            subscription: { post_title: 'Bad#Name', post_status: 'publish' },
        });
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success, 'name with # should not succeed').toBe(false);
        expect(String(body.message)).toContain('#');
        console.log('\x1b[32m%s\x1b[0m', '✅ POST /wpuf_subscription with "#" in name rejected');
    }

    /**
     * Update path: create a pack, edit it via `POST /wpuf_subscription/{id}`
     * (WP_REST EDITABLE → edit_item), and confirm the new title replaced the old.
     * Self-cleaning.
     */
    async validateSubscriptionEditRoundTrip(titleA: string, titleB: string) {
        const createRes = await this.post('/wpuf_subscription', {
            subscription: { post_title: titleA, post_status: 'publish', post_content: 'edit round-trip' },
        });
        expect(createRes.status(), 'create status').toBe(200);
        expect((await createRes.json()).success, 'create success').toBe(true);

        const id = await this.findSubscriptionIdByTitle(titleA);
        expect(id, `created pack "${titleA}" should exist`).toBeTruthy();

        const editRes = await this.post('/wpuf_subscription/' + id, {
            subscription: { ID: id, post_title: titleB, post_status: 'publish' },
        });
        expect(editRes.status(), 'edit status').toBe(200);
        expect((await editRes.json()).success, 'edit success').toBe(true);

        expect(await this.findSubscriptionIdByTitle(titleB), `renamed pack "${titleB}" should exist`).toBeTruthy();
        expect(await this.findSubscriptionIdByTitle(titleA), `old title "${titleA}" should be gone`).toBeNull();

        const delId = await this.findSubscriptionIdByTitle(titleB);
        await this.del('/wpuf_subscription/' + delId);
        console.log('\x1b[32m%s\x1b[0m', `✅ Subscription edit round-trip ok ("${titleA}" → "${titleB}", id ${id})`);
    }

    /**
     * Validation: `POST /subscription-settings` with a non-hex `button_color`
     * returns a 400 `invalid_color` WP_Error and does not persist.
     */
    async validateInvalidColorRejected() {
        const res = await this.post('/subscription-settings', { button_color: 'not-a-hex-color' });
        expect(res.status(), 'invalid color should be 400').toBe(400);
        const body = await res.json();
        expect(body.code, 'error code').toBe('invalid_color');
        console.log('\x1b[32m%s\x1b[0m', '✅ POST /subscription-settings with bad color rejected (400 invalid_color)');
    }

    /**
     * Guard: deleting with an invalid id (0) is rejected with success:false rather
     * than deleting anything.
     */
    async validateDeleteInvalidIdRejected() {
        const res = await this.del('/wpuf_subscription/0');
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success, 'delete id=0 should not succeed').toBe(false);
        console.log('\x1b[32m%s\x1b[0m', '✅ DELETE /wpuf_subscription/0 rejected (success:false)');
    }

    // GET /wpuf_subscription/count/{status} -> 200 with a count for that status.
    async validateSubscriptionCountByStatus(status = 'publish') {
        const res = await this.get('/wpuf_subscription/count/' + status);
        expect(res.status()).toBe(200);
        const body = await res.json();
        expect(body.success, 'count-by-status success').toBe(true);
        expect(body, 'count present').toHaveProperty('count');
        console.log('\x1b[32m%s\x1b[0m', `✅ GET /wpuf_subscription/count/${status} -> 200`);
    }

    /**
     * Security: an XSS payload in the pack title is sanitized on store
     * (sanitize_text_field strips tags) — the persisted title contains no
     * `<script>`. Self-cleaning.
     */
    async validateXssTitleSanitized(token: string) {
        const payloadTitle = `<script>alert('xss')</script>${token}`;
        const res = await this.post('/wpuf_subscription', {
            subscription: { post_title: payloadTitle, post_status: 'publish' },
        });
        expect(res.status()).toBe(200);
        expect((await res.json()).success, 'create success').toBe(true);

        const stored = await this.findSubscriptionByTitleContains(token);
        expect(stored, `stored pack with token "${token}" should exist`).toBeTruthy();
        const storedTitle = String(stored?.post_title);
        expect(storedTitle.toLowerCase(), 'stored title must not contain a script tag').not.toContain('<script');

        const delId = Number(stored?.ID ?? stored?.id);
        if (delId) {
            await this.del('/wpuf_subscription/' + delId);
        }
        console.log('\x1b[32m%s\x1b[0m', `✅ XSS payload sanitized on store (title="${storedTitle}")`);
    }
}
