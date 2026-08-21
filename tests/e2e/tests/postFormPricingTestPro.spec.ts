import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { PostFormPage } from '../pages/postForm';
import { PostFormGapsPage } from '../pages/postFormGaps';
import { Users } from '../utils/testData';
import { faker } from '@faker-js/faker';
import { configureSpecFailFast } from '../utils/specFailFast';
import { waitForSiteReady } from '../utils/siteReady';
import { wpCliAvailable, getPostIdByTitle, getPostField, getPostMeta } from '../utils/wpEnvCli';

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

test.describe( 'Post-Form-Pricing-Payment', () => {
    configureSpecFailFast();

    /**------------------ PRICING FIELDS PAYMENT (§8.4) ------------------**
     *
     * @TestScenario : [Settings → Payment Settings → Enable Pricing Fields Payment]
     *
     * Pay-per-post already has coverage; what did not was the *pricing fields*
     * branch, where the charge is the sum of the priced options the visitor
     * picked rather than a flat per-post cost.
     *
     * @Test_PFG0030 : The setting persists and the pricing rows render
     * @Test_PFG0031 : Cart Total sums the selected pricing options
     * @Test_PFG0032 : The charge matches the cart and the post waits for payment
     * @Test_PFG0033 : With pricing payment off the post is created directly
     *
     */

    const formName = `PP Form ${ faker.string.alphanumeric( 5 ) }`;
    const pageSlug = `pp-form-${ faker.string.alphanumeric( 5 ) }`.toLowerCase();

    let formId: string;

    const cartTotalValue = async (): Promise<number> => {
        const text = await page
            .locator( '//form[contains(@class,"wpuf-form")]//*[contains(@class,"wpuf-cart-total") or contains(@class,"cart-total")]' )
            .first()
            .innerText()
            .catch( () => '0' );

        return Number( ( text.match( /[\d.]+/ ) || [ '0' ] )[ 0 ] );
    };

    test( 'PFG0030 : The setting persists and the pricing rows render', { tag: [ '@Pro', '@Test_PFG0030' ] }, async () => {
        await waitForSiteReady( page, 15000 );
        await new BasicLoginPage( page ).basicLoginAndPluginVisit( Users.adminUsername, Users.adminPassword );

        const postForm = new PostFormPage( page );
        await postForm.createBlankFormPostForm( formName );

        const editor = new PostFormGapsPage( page );
        formId = await editor.getFormId();
        test.skip( ! await editor.isFieldAvailable( 'Price' ), 'Pricing fields require wpuf-pro' );

        await editor.doAddField( 'Post Title' );
        await editor.doAddField( 'Post Content' );
        await editor.doAddField( 'Price' );
        await editor.doAddField( 'Pricing Checkbox' );
        await editor.doAddField( 'Cart Total' );
        await editor.doSaveForm();

        await editor.doOpenSettingsSection( 'Payment Settings' );
        test.skip( ! await editor.isSettingAvailable( 'enable_pricing_payment' ), 'This build has no pricing-fields payment option' );

        // `payment_options` (pay-per-post) and `enable_pricing_payment` (pricing
        // fields) are mutually exclusive by design — wpuf-form-builder.js clears
        // one when the other is switched on. Enabling both would silently save
        // `payment_options: off`, so only the pricing path is turned on here.
        await editor.doToggleSetting( 'enable_pricing_payment', true );
        await editor.doSaveForm();

        await page.reload();
        await editor.doOpenSettingsSection( 'Payment Settings' );
        await editor.validateSettingChecked( 'enable_pricing_payment', true );
        await editor.validateSettingChecked( 'payment_options', false );

        await postForm.createPageWithShortcode( `[wpuf_form id="${ formId }"]`, pageSlug );
        await editor.openFrontendForm( pageSlug );
        await editor.validateFrontendRow( 'Price', 'input' );
        await editor.validateFrontendRow( 'Pricing Checkbox', 'input[type="checkbox"]' );
    } );

    test( 'PFG0031 : Cart Total sums the selected pricing options', { tag: [ '@Pro', '@Test_PFG0031' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFrontendForm( pageSlug );
        test.skip( ! await editor.checkFrontendRow( 'Price' ), 'Pricing fields did not render on this environment' );

        const before = await cartTotalValue();

        await page.locator( `${ editor.frontendRow( 'Price' ) }//input` ).first().fill( '25' );
        await page.locator( `${ editor.frontendRow( 'Price' ) }//input` ).first().blur();
        await page.locator( `${ editor.frontendRow( 'Pricing Checkbox' ) }//input[@type="checkbox"]` ).first().check( { force: true } );
        await page.waitForTimeout( 800 );

        await expect.poll( async () => cartTotalValue(), { timeout: 15000 } ).toBeGreaterThan( before );
    } );

    // Set by PFG0032 so PFG0032a can look the same submission up.
    let heldPostId = 0;

    test( 'PFG0032 : The charge matches the cart and the post waits for payment', { tag: [ '@Pro', '@Test_PFG0032' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        test.skip( ! wpCliAvailable(), 'Reading the held post needs wp-cli' );

        await editor.openFrontendForm( pageSlug );
        test.skip( ! await editor.checkFrontendRow( 'Price' ), 'Pricing fields did not render on this environment' );

        const title = `PP Post ${ faker.string.alphanumeric( 5 ) }`;
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );
        await page.locator( `${ editor.frontendRow( 'Price' ) }//input` ).first().fill( '25' );
        await page.locator( `${ editor.frontendRow( 'Price' ) }//input` ).first().blur();
        await page.waitForTimeout( 800 );

        const total = await cartTotalValue();
        await editor.doSubmitFrontendForm();
        await page.waitForTimeout( 3000 );

        // WPUF hands the submitter to the payment page with the cart amount.
        const onPaymentPage = /wpuf_pay|payment/i.test( page.url() )
            || await page.locator( '//*[contains(normalize-space(),"Payment")]' ).first().isVisible().catch( () => false );
        expect( onPaymentPage, `expected the payment step, landed on ${ page.url() }` ).toBeTruthy();

        if ( total > 0 ) {
            await expect( page.locator( 'body' ) ).toContainText( String( total ) );
        }

        heldPostId = getPostIdByTitle( title );
        expect( heldPostId ).toBeGreaterThan( 0 );
        // The cost the payment page will charge is stored on the post.
        expect( getPostMeta( heldPostId, '_wpuf_pricing_field_cost' ) ).toBe( String( total ) );
    } );

    // BUG (see BUGS-FOUND.md): Pricing_Field_Payment::redirect_to_payment() sends
    // the submitter to the payment page but never changes the post status, so a
    // pricing-field submission is public before a cent is paid. test.fail() keeps
    // the expectation on record until the product holds the post.
    test( 'PFG0032a : An unpaid pricing submission is not public', { tag: [ '@Pro', '@Test_PFG0032a' ] }, async () => {
        test.skip( ! heldPostId, 'PFG0032 did not produce a submission to check' );
        test.fail();
        expect( getPostField( heldPostId, 'post_status' ) ).not.toBe( 'publish' );
    } );

    test( 'PFG0033 : With pricing payment off the post is created directly', { tag: [ '@Pro', '@Test_PFG0033' ] }, async () => {
        const editor = new PostFormGapsPage( page );
        await editor.openFormEditor( formId );
        await editor.doOpenSettingsSection( 'Payment Settings' );
        await editor.doToggleSetting( 'enable_pricing_payment', false );
        await editor.doToggleSetting( 'payment_options', false );
        await editor.doSaveForm();

        const title = `PP Free ${ faker.string.alphanumeric( 5 ) }`;
        await editor.openFrontendForm( pageSlug );
        await page.locator( '//form[contains(@class,"wpuf-form")]//input[@name="post_title"]' ).fill( title );
        await editor.doFillPostContent( faker.lorem.paragraph() );
        await editor.doSubmitFrontendForm();
        await page.waitForTimeout( 2000 );

        await editor.validatePostCreated( title );
    } );
} );
