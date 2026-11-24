import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import { wpCliAvailable, wpCli, getPostIdByTitle, getPostMeta } from '../utils/wpEnvCli';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll( async () => {
    browser = await chromium.launch();
    context = await browser.newContext();
    page = await context.newPage();

    page.on( 'dialog', async ( dialog ) => {
        await dialog.accept().catch( () => {} );
    } );
} );

test.describe( 'Post-Form-Display-Advanced', () => {
    configureSpecFailFast();

    /**------------- DISPLAY / ADVANCED / INTEGRATIONS (§8.2–8.5) -------------**
     *
     * @TestScenario : [Display Settings, Advanced, Post Expiration siblings]
     *
     * Display
     * @Test_PFG0010 : Use Theme CSS off keeps WPUF's own form styling
     * @Test_PFG0011 : Use Theme CSS on hands styling to the theme
     * @Test_PFG0012 : Label Position = Above Element
     * @Test_PFG0013 : Label Position = Left / Right
     * @Test_PFG0014 : Label Position = Hidden
     * @Test_PFG0015 : Form style selection persists and reaches the frontend (@Pro)
     *
     * Advanced — form scheduling
     * @Test_PFG0020 : Inside the schedule window the form is submittable
     * @Test_PFG0021 : Before the window opens the pending notice shows
     * @Test_PFG0022 : After the window closes the expired notice shows
     * @Test_PFG0023 : Scheduling off restores the form
     *
     * Integrations
     * @Test_PFG0040 : AI Review enabled writes a review record (env-gated)
     * @Test_PFG0041 : AI Review disabled writes nothing
     * @Test_PFG0042 : N8N enabled still lets the submission through
     * @Test_PFG0043 : N8N with an unreachable webhook is not fatal
     * @Test_PFG0044 : Modules panel shows its empty state and link
     *
     */

    const formName = `DA Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `da-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const pendingMessage = `Not open yet ${ faker.string.alphanumeric( 5 ) }`;
    const expiredMessage = `Closed already ${ faker.string.alphanumeric( 5 ) }`;

    let formId: string;

    const isoDate = ( offsetDays: number ): string => {
        const date = new Date();
        date.setDate( date.getDate() + offsetDays );

        return date.toISOString().slice( 0, 10 );
    };

    test( 'PFG0010 : Setup + Use Theme CSS off keeps WPUF form styling', { tag: [ '@Lite', '@Test_PFG0010' ] }, async () => {
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();
        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doSaveForm();

        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        await editor.validateThemeCssUsed( pageSlug, false );
    } );

    test( 'PFG0011 : Use Theme CSS on hands styling to the theme', { tag: [ '@Lite', '@Test_PFG0011' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doToggleSetting( 'use_theme_css', true );
        await editor.doSaveForm();

        await editor.validateThemeCssUsed( pageSlug, true );

        // Put it back — the label-position assertions below read the WPUF markup.
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doToggleSetting( 'use_theme_css', false );
        await editor.doSaveForm();
    } );

    test( 'PFG0012 : Label Position = Above Element', { tag: [ '@Lite', '@Test_PFG0012' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doSelectSetting( 'label_position', 'above' );
        await editor.doSaveForm();

        await editor.validateLabelPosition( pageSlug, 'above' );
    } );

    test( 'PFG0013 : Label Position = Left / Right', { tag: [ '@Lite', '@Test_PFG0013' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        for ( const position of [ 'left', 'right' ] ) {
            await editor.openFormEditor( formId );
            await editor.doOpenSettingsSection( 'Display Settings' );
            await editor.doSelectSetting( 'label_position', position );
            await editor.doSaveForm();
            await editor.validateLabelPosition( pageSlug, position );
        }
    } );

    test( 'PFG0014 : Label Position = Hidden', { tag: [ '@Lite', '@Test_PFG0014' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doSelectSetting( 'label_position', 'hidden' );
        await editor.doSaveForm();

        await editor.validateLabelPosition( pageSlug, 'hidden' );
        // With labels hidden the field label must not be painted on screen.
        await expect( page.locator( '//form[contains(@class,"wpuf-form")]//label[normalize-space()="Post Title"]' ).first() ).toBeHidden();

        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doSelectSetting( 'label_position', 'above' );
        await editor.doSaveForm();
    } );

    test( 'PFG0015 : Form style selection persists and reaches the frontend', { tag: [ '@Pro', '@Test_PFG0015' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        // The layout picker ships with wpuf-pro.
        test.skip( ! await editor.isSettingAvailable( 'form_layout' ), 'Form style picker requires wpuf-pro' );

        await editor.doPickRadioSetting( 'form_layout', 'layout2' );
        await editor.doSaveForm();
        await page.reload();
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.validateRadioSettingPicked( 'form_layout', 'layout2' );

        await editor.openFrontendForm( pageSlug );
        await editor.validateFormLayout( 'layout2' );

        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doPickRadioSetting( 'form_layout', 'layout1' );
        await editor.doSaveForm();
    } );

    /**------------------------- FORM SCHEDULING --------------------------**/

    test( 'PFG0020 : Inside the schedule window the form is submittable', { tag: [ '@Lite', '@Test_PFG0020' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doScheduleForm( isoDate( -1 ), isoDate( 1 ), pendingMessage, expiredMessage );

        await editor.validateFormSubmittable( pageSlug );
    } );

    test( 'PFG0021 : Before the window opens the pending notice shows', { tag: [ '@Lite', '@Test_PFG0021' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doScheduleForm( isoDate( 2 ), isoDate( 9 ), pendingMessage, expiredMessage );

        await editor.validateFormScheduleNotice( pageSlug, pendingMessage );
    } );

    test( 'PFG0022 : After the window closes the expired notice shows', { tag: [ '@Lite', '@Test_PFG0022' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doScheduleForm( isoDate( -9 ), isoDate( -2 ), pendingMessage, expiredMessage );

        await editor.validateFormScheduleNotice( pageSlug, expiredMessage );
    } );

    test( 'PFG0023 : Scheduling off restores the form', { tag: [ '@Lite', '@Test_PFG0023' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doUnscheduleForm();

        await editor.validateFormSubmittable( pageSlug );
    } );

    /**--------------------- AI REVIEW / N8N / MODULES ---------------------**/

    test( 'PFG0040 : AI Review enabled writes a review record', { tag: [ '@Pro', '@Test_PFG0040' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'AI Review' );
        test.skip( ! await editor.isSettingAvailable( 'ai_review_enabled' ), 'AI Review requires wpuf-pro' );
        test.skip( ! wpCliAvailable(), 'Reading the review meta needs wp-cli' );

        // The service refuses to review unless a provider + key are configured in
        // WPUF → Settings → AI (option `wpuf_ai`), so this is env-gated.
        const aiConfigured = ( () => {
            try {
                return wpCli( 'option get wpuf_ai --format=json' ).includes( 'ai_provider' );
            } catch {
                return false;
            }
        } )();
        test.skip( ! aiConfigured, 'AI review needs a configured provider in the wpuf_ai option' );

        await editor.doToggleSetting( 'ai_review_enabled', true );
        await editor.doSaveForm();

        const title = `AI Review ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );
        await expect
            .poll( () => getPostMeta( postId, 'wpuf_ai_post_review' ), { timeout: 60000 } )
            .not.toBe( '' );
    } );

    // BUG (see BUGS-FOUND.md): the AI review runs on every submission regardless
    // of the form's `ai_review_enabled` setting, stamping `wpuf_ai_post_review`
    // (status "failed", "AI service not configured") onto posts from forms that
    // never opted in. test.fail() keeps the expectation on record.
    test( 'PFG0041 : AI Review disabled writes nothing', { tag: [ '@Pro', '@Test_PFG0041' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'AI Review' );
        test.skip( ! await editor.isSettingAvailable( 'ai_review_enabled' ), 'AI Review requires wpuf-pro' );
        test.skip( ! wpCliAvailable(), 'Reading the review meta needs wp-cli' );
        test.fail();

        await editor.doToggleSetting( 'ai_review_enabled', false );
        await editor.doSaveForm();

        const title = `No AI Review ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );

        const postId = getPostIdByTitle( title );
        expect( postId ).toBeGreaterThan( 0 );
        expect( getPostMeta( postId, 'wpuf_ai_post_review' ) ).toBe( '' );
    } );

    test( 'PFG0042 : N8N enabled still lets the submission through', { tag: [ '@Lite', '@Test_PFG0042' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'N8N' );
        test.skip( ! await editor.isSettingAvailable( 'enable_n8n' ), 'N8N integration is not available on this build' );

        await editor.doToggleSetting( 'enable_n8n', true );
        // QA_N8N_WEBHOOK_URL lets a CI job point this at a real receiver and
        // assert the payload there; without it the webhook target is inert and
        // only the "submission survives the POST attempt" half is proven.
        await editor.doFillSetting( 'n8n_webhook_url', process.env.QA_N8N_WEBHOOK_URL || 'https://example.test/wpuf-n8n' );
        await editor.doSaveForm();
        await page.reload();
        await editor.doOpenSettingsSection( 'N8N' );
        await editor.validateSettingChecked( 'enable_n8n', true );

        const title = `N8N Post ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );
        await editor.validatePostCreated( title );
    } );

    test( 'PFG0043 : N8N with an unreachable webhook is not fatal', { tag: [ '@Lite', '@Test_PFG0043' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'N8N' );
        test.skip( ! await editor.isSettingAvailable( 'enable_n8n' ), 'N8N integration is not available on this build' );

        await editor.doFillSetting( 'n8n_webhook_url', 'http://127.0.0.1:9/never-listens' );
        await editor.doSaveForm();

        const title = `N8N Dead ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await editor.doSubmitFrontendPost( title, faker.lorem.paragraph() );
        await editor.validatePostCreated( title );
        await expect( page.locator( 'body' ) ).not.toContainText( 'Fatal error' );

        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'N8N' );
        await editor.doToggleSetting( 'enable_n8n', false );
        await editor.doSaveForm();
    } );

    test( 'PFG0044 : Modules panel shows its empty state and link', { tag: [ '@Lite', '@Test_PFG0044' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenTab( 'Settings' );

        // The sidebar only renders clickable <li> entries for a group's `sub_items`.
        // Pro registers the Modules group with `sub_items => apply_filters( ..., [] )`,
        // so with no module contributing one the group is a heading with nothing to
        // click, and the empty-state panel (gated on Lite or an empty modules item)
        // is not rendered at all.
        const modulesNavItem = page.locator( '//div[@id="wpuf-form-builder-settings"]//li//a[normalize-space()="Modules"]' ).first();

        if ( await modulesNavItem.isVisible().catch( () => false ) ) {
            await editor.doOpenSettingsSection( 'Modules' );

            const emptyState = page.locator( '//*[contains(normalize-space(),"No modules have been activated yet")]' ).first();

            if ( await emptyState.isVisible().catch( () => false ) ) {
                const modulesLink = page.locator( '//a[normalize-space()="Go To Module Page"]' ).first();
                await expect( modulesLink ).toBeVisible();
                expect( await modulesLink.getAttribute( 'href' ) ).toContain( 'wpuf-modules' );
            } else {
                // Modules are active — the panel lists them instead.
                await expect( page.locator( '//div[@id="wpuf-form-builder-settings"]' ) ).toBeVisible();
            }

            return;
        }

        // No clickable entry: the Modules group must still be present as a heading.
        await expect(
            page.locator( '//div[@id="wpuf-form-builder-settings"]//h2[.//span[normalize-space()="Modules"]]' ).first()
        ).toBeVisible();
    } );
} );
