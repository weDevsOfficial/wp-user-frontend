import { test } from '@playwright/test';
import { faker } from '@faker-js/faker';
import { WpufApi } from '../../pages/api/WpufApi';
import { createAdminAppPassword, createUserAppPassword, getWpufOptionKey, seedUser, setWpufOptionKey, wpCli } from '../../utils/wpEnvCli';

/**
 * REST checks for rules found in the business QA round (qa-stories business BS-17 / BS-18).
 * Self-contained: each test seeds the user / form it needs and removes it.
 *
 * @Test_Scenarios : [REST API — business QA rules]
 * @Test_AP0016 : Every post form template stores its own key (EDD stored the Events Calendar key)
 * @Test_AP0017 : The account's logout link is a plain URL (no &amp;) with action + _wpnonce
 * @Test_AP0018 : A registration logged in by auto login answers logged_in and sets the login cookie (Pro)
 */

const TEMPLATES = [
    'post_form_template_post',
    'post_form_template_video',
    'post_form_template_paid_guest_post',
    'post_form_template_guest_post_recurring',
    'post_form_template_woocommerce',
    'post_form_template_edd',
    'post_form_template_events_calendar',
    'post_form_template_press_release',
    'post_form_template_professional_video',
    'post_form_template_artwork',
    'post_form_template_portfolio',
    'post_form_template_volunteer',
    'post_form_template_testimonial',
];

let admin: WpufApi;

test.beforeAll(async () => {
    admin = await WpufApi.create(createAdminAppPassword());
});

test.afterAll(async () => {
    await admin?.dispose();
});

test.describe('Business QA rules (wpuf/v1)', () => {
    test('AP0016 : Every post form template stores its own key', { tag: ['@Lite', '@API', '@Test_AP0016'] }, async () => {
        await admin.validateTemplatesStoreTheirOwnKey(TEMPLATES);
    });

    test('AP0017 : The account logout link is a plain URL', { tag: ['@Lite', '@API', '@Test_AP0017'] }, async () => {
        const login = 'qa_logout_' + faker.string.alphanumeric(6).toLowerCase();

        seedUser(login, `${login}@example.test`, faker.internet.password({ length: 16 }));

        const member = await WpufApi.create(createUserAppPassword(login), login);

        try {
            await member.validateAccountLogoutUrlIsPlain();
        } finally {
            await member.dispose();
            wpCli(`user delete ${login} --yes`);
        }
    });

    test('AP0018 : Auto login after registration answers logged_in', { tag: ['@Pro', '@API', '@Test_AP0018'] }, async () => {
        const created = await admin.post('/admin/forms', { type: 'wpuf_profile', template: 'simple_user_signup_template' });

        test.skip(!created.ok(), 'Registration forms need WP User Frontend Pro');

        const formId = Number((await created.json()).data.id);
        const before = getWpufOptionKey('wpuf_profile', 'autologin_after_registration');
        const login = 'qa_auto_' + faker.string.alphanumeric(6).toLowerCase();
        const guest = await WpufApi.create();

        // Auto login runs for approved sign-ups only; the template starts as pending.
        wpCli(`post meta patch update ${formId} wpuf_form_settings wpuf_user_status approved`);
        setWpufOptionKey('wpuf_profile', 'autologin_after_registration', 'on');

        try {
            await guest.validateRegistrationAutoLoginAnswer(formId, login, faker.internet.password({ length: 16 }));
        } finally {
            await guest.dispose();
            await admin.deleteForm(formId);
            setWpufOptionKey('wpuf_profile', 'autologin_after_registration', before || 'on');
            try {
                wpCli(`user delete ${login} --yes`);
            } catch {
                // The registration failed before the user was made.
            }
        }
    });
});
