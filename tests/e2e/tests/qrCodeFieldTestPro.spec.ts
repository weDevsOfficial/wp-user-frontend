import { Browser, BrowserContext, Page, expect, test, chromium } from '@playwright/test';
import { WpufApi } from '../pages/api/WpufApi';
import { createAdminAppPassword, wpCli } from '../utils/wpEnvCli';
import { Urls } from '../utils/testData';
import { configureSpecFailFast } from '../utils/specFailFast';
import { aiWp } from '../pages/aiFormBuilder';
import { wpLoginForm } from '../utils/wpLoginForm';

/**
 * QR Code field (Pro module) from the frontend: the posted value is stored and
 * the post page shows it encoded.
 *
 * Pro 4.2.18 / 4.2.19 read the posted QR field with sanitize_text_field() on the
 * array the field posts, so nothing was stored and the post page drew
 * quickchart.io's "missing variable text" image (QA story 11).
 *
 * @TestScenario : [QR Code field]
 * @Test_QR0001 : Admin seeds a post form with Post Title + QR Code, its page and an author
 * @Test_QR0002 : A URL QR submitted from the frontend is stored as { type, type_param }
 * @Test_QR0003 : The post page shows the QR image with the URL encoded
 * @Test_QR0004 : A Text QR with quotes, a tag, & and ? is stored sanitized and shown encoded (no markup)
 */

let browser: Browser;
let context: BrowserContext;
let page: Page;
let api: WpufApi;

const stamp = Date.now();
let formId = 0;
let pageUrl = '';
let cfShowFront = '';
// A throwaway author posts from the frontend (no dependency on the admin password).
const author = { login: `qr_e2e_${ stamp }`, pass: `Qr-${ stamp }-${ Math.random().toString( 36 ).slice( 2 ) }` };
let authorId = '';
let pageId = '';
// Set when this spec switched the QR Code module on (switched off again after).
let qrModuleTurnedOn = false;
const QR_MODULE = 'qr-code-field/wpuf-qr-code.php';

const field = (extra: Record<string, unknown>) => ( {
    required: 'no',
    width: 'large',
    css: '',
    placeholder: '',
    default: '',
    size: 40,
    help: '',
    wpuf_cond: { condition_status: 'no', cond_field: [], cond_operator: [ '=' ], cond_option: [ '- Select -' ], cond_logic: 'all' },
    wpuf_visibility: { selected: 'everyone', choices: [] },
    show_icon: 'no',
    field_icon: '',
    icon_position: 'left_label',
    ...extra,
} );

// wp-env prints status lines around wp-cli's output: take the value line.
const cliValue = (out: string, re: RegExp): string => ( out.split( '\n' ).map( (l) => l.trim() ).find( (l) => re.test( l ) ) || '' );

const postIdByTitle = (title: string): number => parseInt( cliValue( wpCli( `post list --post_type=post --post_status=any --title="${ title }" --field=ID --posts_per_page=1` ), /^\d+$/ ), 10 );

const qrMeta = (postId: number) => JSON.parse( cliValue( wpCli( `post meta get ${ postId } qr_code --format=json` ), /^[[{]/ ) );

/** Fill the form page: title, then a QR type and its value (the value input comes by AJAX). */
async function submitQr(title: string, type: 'url' | 'text', value: string): Promise<number> {
    await page.goto( pageUrl, { waitUntil: 'load' } );
    await page.locator( 'input[name="post_title"]' ).fill( title );
    await page.locator( 'select.qr_code_type_class' ).selectOption( type );
    const input = page.locator( `.qr_code_wrap [name="qr_code[type_param][${ type }]"]` ).first();
    await input.waitFor( { timeout: 15000 } );
    await input.fill( value );
    await page.locator( 'form.wpuf-form-add input.wpuf-submit-button' ).first().click();
    // Redirect to the new post ("Newly created post").
    await page.waitForURL( (u) => ! u.toString().startsWith( pageUrl ) && ! u.toString().includes( 'page_id=' ), { timeout: 30000 } );
    return postIdByTitle( title );
}

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext();
    page = await context.newPage();
    api = await WpufApi.create( createAdminAppPassword() );
});

test.afterAll(async () => {
    if ( qrModuleTurnedOn ) {
        try {
            aiWp( [ 'eval', `wpuf_pro_deactivate_module( '${ QR_MODULE }' );` ], true );
        } catch ( e ) {
            // Leave it on.
        }
    }
    // Put "Show custom fields in post" back as the run found it.
    try {
        if ( cfShowFront ) {
            wpCli( `option patch update wpuf_frontend_posting cf_show_front ${ cfShowFront }` );
        } else {
            wpCli( 'option patch delete wpuf_frontend_posting cf_show_front' );
        }
    } catch ( e ) {
        // Option missing: nothing to restore.
    }
    // The seeded form and page.
    // The form's field rows (wpuf_input children) first: `post delete` leaves them.
    if ( formId ) {
        try {
            const fields = cliValue( wpCli( `post list --post_type=wpuf_input --post_parent=${ formId } --post_status=any --format=ids` ), /^[\d ]+$/ );
            if ( fields ) {
                wpCli( `post delete ${ fields } --force` );
            }
        } catch ( e ) {
            // No fields.
        }
    }
    for ( const id of [ pageId, formId ? String( formId ) : '' ] ) {
        if ( id ) {
            try {
                wpCli( `post delete ${ id } --force` );
            } catch ( e ) {
                // Already gone.
            }
        }
    }
    // The author and the posts it made.
    if ( authorId ) {
        try {
            wpCli( `user delete ${ authorId } --yes` );
        } catch ( e ) {
            // Already gone.
        }
    }
    await api?.dispose();
    await browser?.close();
});

test.describe('QR Code field', () => {
    configureSpecFailFast();

    test('QR0001 : Admin seeds a post form with Post Title + QR Code, its page and an author', { tag: [ '@Pro', '@Test_QR0001' ] }, async () => {
        // The setup's module tests end with Deactivate All: switch the module on the
        // way the Modules page does (activation hooks run), and off again after.
        if ( ! wpCli( 'option get wpuf_pro_active_modules --format=json' ).includes( 'qr-code-field' ) ) {
            aiWp( [ 'eval', `wpuf_pro_activate_module( '${ QR_MODULE }' );` ], true );
            qrModuleTurnedOn = true;
        }
        expect( wpCli( 'option get wpuf_pro_active_modules --format=json' ) ).toContain( 'qr-code-field' );

        const created = await api.post( '/admin/forms', { type: 'wpuf_forms' } );
        expect( created.status() ).toBe( 201 );
        formId = ( await created.json() ).data.id;

        const title = `QR e2e ${ stamp }`;
        const saved = await api.post( `/admin/forms/${ formId }`, {
            form_data: new URLSearchParams( { wpuf_form_id: String( formId ), form_settings_key: 'wpuf_form_settings', post_title: title } ).toString(),
            form_fields: JSON.stringify( [
                field( { template: 'post_title', name: 'post_title', label: 'Post Title', required: 'yes', is_meta: 'no', input_type: 'text', restriction_type: 'character', restriction_to: 'max' } ),
                field( { template: 'qr_code', name: 'qr_code', label: 'QR Code', is_meta: 'yes', input_type: 'qr_code', show_in_post: 'yes', hide_field_label: 'no', qr_type: [ 'url', 'text' ] } ),
            ] ),
            notifications: '[]',
            settings: JSON.stringify( { post_type: 'post', post_status: 'publish', redirect_to: 'post', submit_text: 'Create Post', post_permission: 'everyone' } ),
        } );
        expect( saved.ok() ).toBeTruthy();

        pageId = cliValue( wpCli( `post create --post_type=page --post_status=publish --post_title="${ title }" --post_content='[wpuf_form id="${ formId }"]' --porcelain` ), /^\d+$/ );
        pageUrl = `${ Urls.baseUrl }/?page_id=${ pageId }`;

        try {
            cfShowFront = cliValue( wpCli( 'option pluck wpuf_frontend_posting cf_show_front' ), /^(on|off|yes|no)$/ );
        } catch ( e ) {
            cfShowFront = '';
        }
        try {
            wpCli( 'option patch update wpuf_frontend_posting cf_show_front on' );
        } catch ( e ) {
            wpCli( 'option patch insert wpuf_frontend_posting cf_show_front on' );
        }

        authorId = cliValue( wpCli( `user create ${ author.login } ${ author.login }@example.com --role=author --user_pass=${ author.pass } --porcelain` ), /^\d+$/ );
        await wpLoginForm( page, Urls.baseUrl, author.login, author.pass );
        // Logged in: the form renders (logged-out visitors get a login prompt instead).
        await page.goto( pageUrl, { waitUntil: 'load' } );
        await expect( page.locator( 'form.wpuf-form-add select.qr_code_type_class' ) ).toHaveCount( 1 );
    });

    test('QR0002 : A URL QR submitted from the frontend is stored as { type, type_param }', { tag: [ '@Pro', '@Test_QR0002' ] }, async () => {
        const postId = await submitQr( `QR e2e url ${ stamp }`, 'url', 'https://example.com/qr-e2e?a=1&b=2' );
        expect( postId ).toBeGreaterThan( 0 );
        expect( qrMeta( postId ) ).toEqual( { type: 'url', type_param: { url: 'https://example.com/qr-e2e?a=1&b=2' } } );
    });

    test('QR0003 : The post page shows the QR image with the URL encoded', { tag: [ '@Pro', '@Test_QR0003' ] }, async () => {
        const img = page.locator( 'ul.wpuf_customs li', { hasText: 'QR Code' } ).locator( 'img' );
        await expect( img ).toHaveCount( 1 );
        await expect( img ).toHaveAttribute( 'src', /quickchart\.io\/qr\?text=https%3A%2F%2Fexample\.com%2Fqr-e2e%3Fa%3D1%26b%3D2&/ );
    });

    test('QR0004 : A Text QR with quotes, a tag, & and ? is stored sanitized and shown encoded (no markup)', { tag: [ '@Pro', '@Test_QR0004' ] }, async () => {
        const postId = await submitQr( `QR e2e text ${ stamp }`, 'text', 'QR "q" <b>x</b> & ?a=1' );
        // sanitize_text_field() strips the tag and keeps the rest.
        expect( qrMeta( postId ) ).toEqual( { type: 'text', type_param: { text: 'QR "q" x & ?a=1' } } );

        const row = page.locator( 'ul.wpuf_customs li', { hasText: 'QR Code' } );
        await expect( row.locator( 'b' ) ).toHaveCount( 0 );
        await expect( row.locator( 'img' ) ).toHaveAttribute( 'src', /\?text=QR%20%22q%22%20x%20%26%20%3Fa%3D1&/ );
    });
});
