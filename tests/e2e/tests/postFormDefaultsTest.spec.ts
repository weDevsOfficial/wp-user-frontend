import { Browser, BrowserContext, Page, expect, test, chromium } from '@playwright/test';
import { WpufApi } from '../pages/api/WpufApi';
import { createAdminAppPassword, wpCli } from '../utils/wpEnvCli';
import { Urls } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';
import { wpLoginForm } from '../utils/wpLoginForm';

/**
 * Post form settings develop's builder always stored: an untouched save from the
 * React builder must give the frontend the same result (QA story 11: new forms
 * rendered their labels left because Label Position was not stored and the
 * frontend reads a missing value as left).
 *
 * @TestScenario : [Post form defaults]
 * @Test_PFD0001 : An untouched builder save stores Label Position "above" and Choose Payment Option "force_pack_purchase"
 * @Test_PFD0002 : The form renders its labels above the inputs on the frontend
 * @Test_PFD0003 : An untouched registration form save stores Label Position "above" and both redirects "same" (Pro, QA story 15)
 */

let browser: Browser;
let context: BrowserContext;
let page: Page;
let api: WpufApi;

const stamp = Date.now();
let formId = 0;
let regFormId = 0;
let pageId = '';
const user = { login: `pfd_e2e_${ stamp }`, pass: `Pfd-${ stamp }-${ Math.random().toString( 36 ).slice( 2 ) }` };
let userId = '';

// wp-env prints status lines around wp-cli's output: take the value line.
const cliValue = (out: string, re: RegExp): string => ( out.split( '\n' ).map( (l) => l.trim() ).find( (l) => re.test( l ) ) || '' );

// One stored form setting; a missing key reads as '' (wp-cli fails on it).
const setting = (key: string, id: number = formId): string => {
    try {
        return cliValue( wpCli( `post meta pluck ${ id } wpuf_form_settings ${ key }` ), /^[a-z_]+$/ );
    } catch ( e ) {
        return '';
    }
};

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext();
    page = await context.newPage();
    api = await WpufApi.create( createAdminAppPassword() );
});

test.afterAll(async () => {
    // The form's field rows (wpuf_input children) first: `post delete` leaves them.
    for ( const fid of [ formId, regFormId ].filter( Boolean ) ) {
        try {
            const fields = cliValue( wpCli( `post list --post_type=wpuf_input --post_parent=${ fid } --post_status=any --format=ids` ), /^[\d ]+$/ );
            if ( fields ) {
                wpCli( `post delete ${ fields } --force` );
            }
        } catch ( e ) {
            // No fields.
        }
    }
    for ( const id of [ pageId, formId ? String( formId ) : '', regFormId ? String( regFormId ) : '' ] ) {
        if ( id ) {
            try {
                wpCli( `post delete ${ id } --force` );
            } catch ( e ) {
                // Already gone.
            }
        }
    }
    if ( userId ) {
        try {
            wpCli( `user delete ${ userId } --yes` );
        } catch ( e ) {
            // Already gone.
        }
    }
    await api?.dispose();
    await browser?.close();
});

test.describe('Post form defaults', () => {
    configureSpecFailFast();

    test('PFD0001 : An untouched builder save stores Label Position "above" and Choose Payment Option "force_pack_purchase"', { tag: [ '@Lite', '@Test_PFD0001' ] }, async () => {
        const created = await api.post( '/admin/forms', { type: 'wpuf_forms' } );
        expect( created.status() ).toBe( 201 );
        formId = ( await created.json() ).data.id;

        // What the React builder sends for a blank form with only a Post Title added
        // (an edited save, so `touched`): untouched selects are not in the settings.
        const title = `PFD e2e ${ stamp }`;
        const saved = await api.post( `/admin/forms/${ formId }`, {
            form_data: new URLSearchParams( { wpuf_form_id: String( formId ), form_settings_key: 'wpuf_form_settings', post_title: title } ).toString(),
            form_fields: JSON.stringify( [ {
                template: 'post_title', name: 'post_title', label: 'Post Title', required: 'yes', is_meta: 'no', input_type: 'text',
                width: 'large', css: '', placeholder: '', default: '', size: 40, help: '', restriction_type: 'character', restriction_to: 'max',
                wpuf_cond: { condition_status: 'no', cond_field: [], cond_operator: [ '=' ], cond_option: [ '- Select -' ], cond_logic: 'all' },
                wpuf_visibility: { selected: 'everyone', choices: [] }, show_icon: 'no', field_icon: '', icon_position: 'left_label',
            } ] ),
            notifications: '[]',
            settings: JSON.stringify( { post_type: 'post', post_status: 'publish', redirect_to: 'post', submit_text: 'Create Post' } ),
            touched: '1',
        } );
        expect( saved.ok() ).toBeTruthy();

        const label = setting( 'label_position' );
        const payment = setting( 'choose_payment_option' );
        expect( label ).toBe( 'above' );
        expect( payment ).toBe( 'force_pack_purchase' );

        pageId = cliValue( wpCli( `post create --post_type=page --post_status=publish --post_title="${ title }" --post_content='[wpuf_form id="${ formId }"]' --porcelain` ), /^\d+$/ );
    });

    test('PFD0002 : The form renders its labels above the inputs on the frontend', { tag: [ '@Lite', '@Test_PFD0002' ] }, async () => {
        // A throwaway subscriber: the form is shown to logged-in users only.
        userId = cliValue( wpCli( `user create ${ user.login } ${ user.login }@example.com --role=subscriber --user_pass=${ user.pass } --porcelain` ), /^\d+$/ );
        await wpLoginForm( page, Urls.baseUrl, user.login, user.pass );

        await page.goto( `${ Urls.baseUrl }/?page_id=${ pageId }`, { waitUntil: 'load' } );
        await expect( page.locator( 'form.wpuf-form-add ul.wpuf-form' ) ).toHaveClass( /form-label-above/ );
    });
    test('PFD0003 : An untouched registration form save stores Label Position "above" and both redirects "same"', { tag: [ '@Pro', '@Test_PFD0003' ] }, async () => {
        // What the React builder sends for a registration form with only an E-mail
        // field added and no setting touched (Normalizers::registration_form_selects).
        const created = await api.post( '/admin/forms', { type: 'wpuf_profile' } );
        test.skip( 400 === created.status(), 'Pro is not active' );
        expect( created.status() ).toBe( 201 );
        regFormId = ( await created.json() ).data.id;

        const saved = await api.post( `/admin/forms/${ regFormId }`, {
            form_data: new URLSearchParams( { wpuf_form_id: String( regFormId ), form_settings_key: 'wpuf_form_settings', post_title: `PFD reg e2e ${ stamp }` } ).toString(),
            form_fields: JSON.stringify( [ {
                template: 'user_email', name: 'user_email', label: 'E-mail', required: 'yes', is_meta: 'no', input_type: 'email',
                width: 'large', css: '', placeholder: '', default: '', size: 40, help: '',
                wpuf_cond: { condition_status: 'no', cond_field: [], cond_operator: [ '=' ], cond_option: [ '- Select -' ], cond_logic: 'all' },
                wpuf_visibility: { selected: 'everyone', choices: [] },
            } ] ),
            notifications: '[]',
            settings: JSON.stringify( { role: 'subscriber' } ),
            touched: '1',
        } );
        expect( saved.ok() ).toBeTruthy();

        expect( setting( 'label_position', regFormId ) ).toBe( 'above' );
        expect( setting( 'reg_redirect_to', regFormId ) ).toBe( 'same' );
        expect( setting( 'profile_redirect_to', regFormId ) ).toBe( 'same' );
    });
});
