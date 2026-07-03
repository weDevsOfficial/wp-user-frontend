import { test } from '@playwright/test';
import { faker } from '@faker-js/faker';
import { WpufApi } from '../../pages/api/WpufApi';
import { createAdminAppPassword } from '../../utils/wpEnvCli';

/**
 * REST API tests for the WPUF `wpuf/v1` namespace (no browser).
 *
 * @Test_Scenarios : [REST API — wpuf/v1]
 * @Test_AP0001 : Unauthenticated requests are blocked (401) on all admin routes
 * @Test_AP0002 : GET /wpuf_form returns the form list with expected schema
 * @Test_AP0003 : GET /wpuf_subscription/count returns counts
 * @Test_AP0004 : GET /subscription-settings returns settings
 * @Test_AP0005 : Subscription CRUD round-trip (create -> read -> delete)
 * @Test_AP0006 : POST /wpuf_subscription with a bad payload is rejected
 */

let api: WpufApi;
let unauth: WpufApi;

test.beforeAll(async () => {
    // Mint an admin Application Password for Basic auth; second client is anonymous.
    const appPassword = createAdminAppPassword();
    api = await WpufApi.create(appPassword);
    unauth = await WpufApi.create();
});

test.afterAll(async () => {
    await api?.dispose();
    await unauth?.dispose();
});

test.describe('WPUF REST API (wpuf/v1)', () => {
    // Guarded admin routes — all use permission_callback current_user_can(wpuf_admin_role()).
    const guardedRoutes: Array<[string, string]> = [
        ['GET', '/wpuf_form'],
        ['GET', '/wpuf_subscription'],
        ['POST', '/wpuf_subscription'],
        ['GET', '/wpuf_subscription/count'],
        ['GET', '/wpuf_subscription/count/all'],
        ['GET', '/wpuf_subscription/subscribers'],
        ['GET', '/subscription-settings'],
        ['POST', '/subscription-settings'],
    ];

    test('AP0001 : Unauthenticated requests are blocked on all wpuf/v1 admin routes', { tag: ['@Lite', '@API', '@Test_AP0001'] }, async () => {
        for (const [method, path] of guardedRoutes) {
            await unauth.validateUnauthorizedBlocked(method, path);
        }
    });

    test('AP0002 : GET /wpuf_form returns the form list with expected schema', { tag: ['@Lite', '@API', '@Test_AP0002'] }, async () => {
        await api.validateFormsList();
    });

    test('AP0003 : GET /wpuf_subscription/count returns counts', { tag: ['@Lite', '@API', '@Test_AP0003'] }, async () => {
        await api.validateSubscriptionCount();
    });

    test('AP0004 : GET /subscription-settings returns settings', { tag: ['@Lite', '@API', '@Test_AP0004'] }, async () => {
        await api.validateSubscriptionSettings();
    });

    test('AP0005 : Subscription CRUD round-trip (create -> read -> delete)', { tag: ['@Lite', '@API', '@Test_AP0005'] }, async () => {
        await api.validateSubscriptionCrudRoundTrip('API QA Pack ' + faker.string.alphanumeric(6));
    });

    test('AP0006 : POST /wpuf_subscription with a bad payload is rejected', { tag: ['@Lite', '@API', '@Test_AP0006'] }, async () => {
        await api.validateBadCreatePayloadRejected();
    });
});
