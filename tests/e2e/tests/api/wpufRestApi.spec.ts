import { test } from '@playwright/test';
import { faker } from '@faker-js/faker';
import { WpufApi } from '../../pages/api/WpufApi';
import { createAdminAppPassword, createUserAppPassword } from '../../utils/wpEnvCli';
import { Users } from '../../utils/testData';

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
 * @Test_AP0007 : Authenticated non-admin (subscriber) is forbidden (403) on admin routes
 * @Test_AP0008 : POST /wpuf_subscription with "#" in the name is rejected
 * @Test_AP0009 : Subscription update round-trip via POST /wpuf_subscription/{id}
 * @Test_AP0010 : POST /subscription-settings with a bad hex color is rejected (400)
 * @Test_AP0011 : DELETE /wpuf_subscription/{invalid-id} is rejected
 * @Test_AP0012 : GET /wpuf_subscription/count/{status} returns a count
 * @Test_AP0013 : XSS payload in a pack title is sanitized on store
 * @Test_AP0014 : GET /wpuf_form pagination contract (per_page/page echoed, capped)
 * @Test_AP0015 : GET /wpuf_form?s=<no-match> returns an empty result (search applied)
 */

let api: WpufApi;
let unauth: WpufApi;
let subscriber: WpufApi;

test.beforeAll(async () => {
    // Mint an admin Application Password for Basic auth; second client is anonymous;
    // third authenticates as the non-admin test user (created in setup) to prove the
    // capability gate (403), not just the login gate (401).
    const appPassword = createAdminAppPassword();
    api = await WpufApi.create(appPassword);
    unauth = await WpufApi.create();
    const subPassword = createUserAppPassword(Users.userName);
    subscriber = await WpufApi.create(subPassword, Users.userName);
});

test.afterAll(async () => {
    await api?.dispose();
    await unauth?.dispose();
    await subscriber?.dispose();
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

    test('AP0007 : Authenticated non-admin is forbidden (403) on admin routes', { tag: ['@Lite', '@API', '@Test_AP0007'] }, async () => {
        // Subset that guards reads + writes; the capability check runs before the handler.
        const routes: Array<[string, string]> = [
            ['GET', '/wpuf_form'],
            ['GET', '/wpuf_subscription'],
            ['POST', '/wpuf_subscription'],
            ['GET', '/subscription-settings'],
        ];
        for (const [method, path] of routes) {
            await subscriber.validateForbiddenForRole(method, path);
        }
    });

    test('AP0008 : POST /wpuf_subscription with "#" in the name is rejected', { tag: ['@Lite', '@API', '@Test_AP0008'] }, async () => {
        await api.validateSubscriptionNameWithHashRejected();
    });

    test('AP0009 : Subscription update round-trip via POST /wpuf_subscription/{id}', { tag: ['@Lite', '@API', '@Test_AP0009'] }, async () => {
        const suffix = faker.string.alphanumeric(6);
        await api.validateSubscriptionEditRoundTrip('API Edit A ' + suffix, 'API Edit B ' + suffix);
    });

    test('AP0010 : POST /subscription-settings with a bad hex color is rejected (400)', { tag: ['@Lite', '@API', '@Test_AP0010'] }, async () => {
        await api.validateInvalidColorRejected();
    });

    test('AP0011 : DELETE /wpuf_subscription/{invalid-id} is rejected', { tag: ['@Lite', '@API', '@Test_AP0011'] }, async () => {
        await api.validateDeleteInvalidIdRejected();
    });

    test('AP0012 : GET /wpuf_subscription/count/{status} returns a count', { tag: ['@Lite', '@API', '@Test_AP0012'] }, async () => {
        await api.validateSubscriptionCountByStatus('publish');
    });

    test('AP0013 : XSS payload in a pack title is sanitized on store', { tag: ['@Lite', '@API', '@Test_AP0013'] }, async () => {
        await api.validateXssTitleSanitized('XSS' + faker.string.alphanumeric(6));
    });

    test('AP0014 : GET /wpuf_form pagination contract', { tag: ['@Lite', '@API', '@Test_AP0014'] }, async () => {
        await api.validateFormsPagination();
    });

    test('AP0015 : GET /wpuf_form search with no match returns empty', { tag: ['@Lite', '@API', '@Test_AP0015'] }, async () => {
        await api.validateFormsSearchNoMatch('zznomatch' + faker.string.alphanumeric(8));
    });
});
