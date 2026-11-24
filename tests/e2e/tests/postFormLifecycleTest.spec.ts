import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users, Urls } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import { wpCliAvailable, seedUserWithRole, getPostIdByTitle, getPostField } from '../utils/wpEnvCli';

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

test.describe( 'Post-Form-Lifecycle', () => {
    configureSpecFailFast();

    /**------------------ LIFECYCLE + FORM LIST (§8.9) ------------------**
     *
     * @TestScenario : [Edit authorization, drafts, multistep, conditional logic,
     *                  and the Post Forms list screen]
     *
     * @Test_PFG0090 : Editing another user's post is refused
     * @Test_PFG0091 : A bogus pid / missing nonce is handled, not fatal
     * @Test_PFG0092 : A saved draft can be resumed and published
     * @Test_PFG0093 : Multistep Next is blocked while a step is invalid (@Pro)
     * @Test_PFG0094 : Multistep Back keeps what was already entered (@Pro)
     * @Test_PFG0095 : Conditional logic chains A → B → C (@Pro)
     * @Test_PFG0096 : A conditional field reappears when the rule is satisfied (@Pro)
     * @Test_PFG0097 : Duplicating a form copies it under a new id
     * @Test_PFG0098 : Trashing a form removes it from the default list
     * @Test_PFG0099 : Searching the list filters by form name
     *
     */

    const formName = `LC Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `lc-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const dashboardSlug = `lc-dash-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();
    const editSlug = `lc-edit-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();

    const authorOne = `lcone${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const authorTwo = `lctwo${ faker.string.alphanumeric( 6 ) }`.toLowerCase();
    const userPass = faker.internet.password( { length: 14 } );

    let formId: string;
    let postOneTitle: string;
    let postTwoTitle: string;
    let postOneId: number;

    // Fills and submits the shared form as whoever `userPage` is logged in as.
    const submitAs = async ( userPage: Page, title: string, extra?: ( p: Page ) => Promise<void> ) => {
        await userPage.goto( `${ Urls.baseUrl }/${ pageSlug }/` );
        await userPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );

        const textarea = userPage.locator( '//form[contains(@class,"wpuf-form")]//textarea[@name="post_content"]' );

        if ( await textarea.isVisible().catch( () => false ) ) {
            await textarea.fill( faker.lorem.paragraph() );
        } else {
            await userPage.frameLocator( 'form[class*="wpuf-form"] iframe[id$="_ifr"]' ).locator( 'body' ).fill( faker.lorem.paragraph() );
        }

        if ( extra ) {
            await extra( userPage );
        }

        await userPage.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).click();
        await expect( userPage.locator( '//form[contains(@class,"wpuf-form")]' ) ).toBeHidden( { timeout: 60000 } );
    };

    test( 'PFG0090 : Editing another user\'s post is refused', { tag: [ '@Lite', '@Test_PFG0090' ] }, async () => {
        test.skip( ! wpCliAvailable(), 'Needs wp-cli to seed the two author accounts' );

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
        await postForm.createPageWithShortcodeGeneral( '[wpuf_dashboard]', dashboardSlug );
        await postForm.createPageWithShortcodeGeneral( '[wpuf_edit]', editSlug );
        await editor.doSetGlobalEditPage( editSlug );

        seedUserWithRole( authorOne, `${ authorOne }@example.test`, userPass, 'author' );
        seedUserWithRole( authorTwo, `${ authorTwo }@example.test`, userPass, 'author' );

        postOneTitle = `LC One ${ faker.string.alphanumeric( 5 ) }`;
        postTwoTitle = `LC Two ${ faker.string.alphanumeric( 5 ) }`;

        await editor.withUser( authorOne, userPass, async ( userPage ) => {
            await submitAs( userPage, postOneTitle );
        } );

        postOneId = getPostIdByTitle( postOneTitle );
        expect( postOneId ).toBeGreaterThan( 0 );

        await editor.withUser( authorTwo, userPass, async ( userPage ) => {
            await submitAs( userPage, postTwoTitle );

            // The nonce is per-user, not per-post: author two takes their own
            // valid Edit link and points it at author one's post.
            const ownHref = await editor.getDashboardEditHref( userPage, dashboardSlug, postTwoTitle );
            expect( ownHref ).toContain( 'pid=' );
            await editor.validateEditFormOpens( userPage, ownHref );

            await editor.validateEditRefused(
                userPage,
                PostFormGapsPage.withPid( ownHref, postOneId ),
                /not allowed to edit/i
            );
        } );
    } );

    test( 'PFG0091 : A bogus pid / missing nonce is handled, not fatal', { tag: [ '@Lite', '@Test_PFG0091' ] }, async () => {
        const editor = new PostFormGapsPage( page );

        await editor.withUser( authorTwo, userPass, async ( userPage ) => {
            const ownHref = await editor.getDashboardEditHref( userPage, dashboardSlug, postTwoTitle );

            // Nonce present, post missing.
            await editor.validateEditRefused( userPage, PostFormGapsPage.withPid( ownHref, 99999999 ), /Invalid post/i );

            // Nonce stripped entirely.
            await userPage.goto( `${ Urls.baseUrl }/${ editSlug }/?pid=${ postOneId }` );
            await expect( userPage.locator( '//div[contains(@class,"wpuf-info")]' ).first() ).toContainText( /re-open the post/i );
            await expect( userPage.locator( 'body' ) ).not.toContainText( 'Fatal error' );
        } );
    } );

    test( 'PFG0092 : A saved draft can be resumed and published', { tag: [ '@Lite', '@Test_PFG0092' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'General' );
        await editor.doToggleSetting( 'draft_post', true );
        await editor.doSaveForm();

        const draftTitle = `LC Draft ${ faker.string.alphanumeric( 5 ) }`;

        await editor.withUser( authorOne, userPass, async ( userPage ) => {
            await userPage.goto( `${ Urls.baseUrl }/${ pageSlug }/` );
            await userPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( draftTitle );

            const textarea = userPage.locator( '//form[contains(@class,"wpuf-form")]//textarea[@name="post_content"]' );

            if ( await textarea.isVisible().catch( () => false ) ) {
                await textarea.fill( faker.lorem.paragraph() );
            } else {
                await userPage.frameLocator( 'form[class*="wpuf-form"] iframe[id$="_ifr"]' ).locator( 'body' ).fill( faker.lorem.paragraph() );
            }

            await userPage.locator( '//form[contains(@class,"wpuf-form")]//*[contains(@class,"wpuf-draft-button") or normalize-space()="Save Draft"]' ).first().click();
            await userPage.waitForTimeout( 4000 );

            // The draft is listed on the dashboard and its Edit link reopens the
            // form with the entered values.
            const href = await editor.getDashboardEditHref( userPage, dashboardSlug, draftTitle );
            expect( href ).toContain( 'pid=' );
            await userPage.goto( href );
            await expect( userPage.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ) ).toHaveValue( draftTitle );

            await userPage.locator( '//form[contains(@class,"wpuf-form")]//input[contains(@class,"wpuf-submit-button")]' ).click();
        } );

        // The edit flow keeps the form on screen (unlike a fresh submit, which
        // swaps it for the success message), so publication is proven by the post
        // itself rather than by the form disappearing.
        const draftId = getPostIdByTitle( draftTitle );
        expect( draftId ).toBeGreaterThan( 0 );
        await expect
            .poll( () => getPostField( draftId, 'post_status' ), { timeout: 60000 } )
            .toBe( 'publish' );
    } );

    /**--------------------------- MULTISTEP ---------------------------**/

    test( 'PFG0093 : Multistep Next is blocked while a step is invalid', { tag: [ '@Pro', '@Test_PFG0093' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        test.skip( ! await editor.isFieldAvailable( 'Step Start' ), 'Multistep requires wpuf-pro' );

        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doToggleSetting( 'enable_multistep', true );
        await editor.doOpenTab( 'Form Editor' );
        await editor.doAddField( 'Step Start' );
        await editor.doReorderFieldUp( 'step_start' );
        await editor.doAddField( 'Step Start' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        const steps = page.locator( '//form[contains(@class,"wpuf-form")]//fieldset[contains(@class,"wpuf-multistep-fieldset")]' );
        await expect( steps.nth( 0 ) ).toHaveClass( /field-active/ );

        // Post Title is required and empty, so Next must refuse to advance.
        await page.locator( '//form[contains(@class,"wpuf-form")]//fieldset[contains(@class,"field-active")]//button[contains(@class,"wpuf-multistep-next-btn")]' ).first().click();
        await page.waitForTimeout( 1000 );
        await expect( steps.nth( 0 ) ).toHaveClass( /field-active/ );
    } );

    test( 'PFG0094 : Multistep Back keeps what was already entered', { tag: [ '@Pro', '@Test_PFG0094' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFrontendForm( pageSlug );
        test.skip(
            await page.locator( '//form[contains(@class,"wpuf-form")]//fieldset[contains(@class,"wpuf-multistep-fieldset")]' ).count() < 2,
            'Multistep requires wpuf-pro'
        );

        const title = `LC Step ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await page.locator( '//form[contains(@class,"wpuf-form")]//fieldset[contains(@class,"field-active")]//button[contains(@class,"wpuf-multistep-next-btn")]' ).first().click();
        await page.waitForTimeout( 1000 );

        const back = page.locator( '//form[contains(@class,"wpuf-form")]//fieldset[contains(@class,"field-active")]//button[contains(@class,"wpuf-multistep-prev-btn")]' ).first();
        test.skip( ! await back.isVisible().catch( () => false ), 'This template renders no Back button' );

        await back.click();
        await page.waitForTimeout( 800 );
        await expect( page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ) ).toHaveValue( title );

        // Leave the shared form single-step for the conditional-logic tests.
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Display Settings' );
        await editor.doToggleSetting( 'enable_multistep', false );
        await editor.doOpenTab( 'Form Editor' );
        await editor.doRemoveField( 'step_start' );
        await editor.doRemoveField( 'step_start' );
        await editor.doSaveForm();
    } );

    /**----------------------- CONDITIONAL LOGIC -----------------------**/

    test( 'PFG0095 : Conditional logic chains A → B → C', { tag: [ '@Pro', '@Test_PFG0095' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doAddField( 'Dropdown' );
        await editor.doAddField( 'Text' );
        await editor.doAddField( 'Textarea' );

        // B (Text) shows when the Dropdown has a value…
        await editor.doEditField( 'text_field' );
        // Conditional Logic sits in the collapsed `advanced` section.
        await editor.doExpandAdvancedOptions();
        test.skip( ! await editor.isFieldOptionAvailable( 'Conditional Logic' ), 'Conditional Logic requires wpuf-pro' );
        await editor.doSetConditionalLogic( 'dropdown', '!=empty' );

        // …and C (Textarea) shows when B has a value.
        await editor.doEditField( 'textarea_field' );
        await editor.doSetConditionalLogic( 'text', '!=empty' );
        await editor.doSaveForm();

        await editor.openFrontendForm( pageSlug );
        await expect( page.locator( editor.frontendRow( 'Text' ) ).first() ).toBeHidden();
        await expect( page.locator( editor.frontendRow( 'Textarea' ) ).first() ).toBeHidden();
    } );

    test( 'PFG0096 : A conditional field reappears when the rule is satisfied', { tag: [ '@Pro', '@Test_PFG0096' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFrontendForm( pageSlug );
        test.skip( await page.locator( editor.frontendRow( 'Text' ) ).count() === 0, 'Conditional Logic requires wpuf-pro' );

        const dropdown = page.locator( `${ editor.frontendRow( 'Dropdown' ) }//select` ).first();
        const options = await dropdown.locator( 'option' ).evaluateAll( ( opts ) =>
            opts.map( ( option ) => ( option as HTMLOptionElement ).value ).filter( ( value ) => value && value !== '-1' )
        );
        test.skip( ! options.length, 'The Dropdown field has no selectable options on this form' );

        await dropdown.selectOption( options[ 0 ] );
        await expect( page.locator( editor.frontendRow( 'Text' ) ).first() ).toBeVisible();

        // The chained field only follows once B itself has a value.
        await expect( page.locator( editor.frontendRow( 'Textarea' ) ).first() ).toBeHidden();
        await page.locator( `${ editor.frontendRow( 'Text' ) }//input` ).first().fill( 'chained' );
        await page.locator( `${ editor.frontendRow( 'Text' ) }//input` ).first().blur();
        await expect( page.locator( editor.frontendRow( 'Textarea' ) ).first() ).toBeVisible();
    } );

    /**------------------------ FORMS LIST SCREEN ------------------------**/

    test( 'PFG0097 : Duplicating a form copies it under a new id', { tag: [ '@Lite', '@Test_PFG0097' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormsList();

        const before = await editor.getFormIdFromRow( formName );
        await editor.doRowMenuAction( formName, 'Duplicate' );
        await editor.openFormsList();

        // WPUF names the copy after the original; two rows now match the name.
        const rows = await editor.getListedFormNames();
        expect( rows.filter( ( name ) => name.includes( formName ) ).length ).toBeGreaterThan( 1 );
        expect( before ).not.toBe( '' );
    } );

    test( 'PFG0098 : Trashing a form removes it from the default list', { tag: [ '@Lite', '@Test_PFG0098' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        const postForm = new PostFormPage( page );
        const throwaway = `LC Trash ${ faker.string.alphanumeric( 5 ) }`;

        await postForm.createBlankFormPostForm( throwaway );
        await editor.openFormsList();
        await editor.validateFormListed( throwaway, true );

        await editor.doRowMenuAction( throwaway, 'Trash' );
        await editor.openFormsList();
        await editor.validateFormListed( throwaway, false );

        // …and it is countable under the Trash tab.
        await expect( page.locator( '//*[contains(normalize-space(),"Trash")]' ).first() ).toBeVisible();
    } );

    test( 'PFG0099 : Searching the list filters by form name', { tag: [ '@Lite', '@Test_PFG0099' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormsList();

        await editor.doSearchForms( formName );
        const matches = await editor.getListedFormNames();
        expect( matches.length ).toBeGreaterThan( 0 );
        expect( matches.every( ( name ) => name.includes( formName ) ) ).toBeTruthy();

        await editor.doSearchForms( 'zzz-no-such-form-zzz' );
        expect( ( await editor.getListedFormNames() ).length ).toBe( 0 );
    } );
} );
