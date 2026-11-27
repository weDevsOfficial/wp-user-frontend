/**
 * The builder's save checks (develop's refusals): QA stories 08, 10, 14.
 */
import { validatePaymentSettings, validateRequiredFields } from './saveValidation';

const field = ( template, extra = {} ) => ( { template, id: template, ...extra } );

describe( 'validateRequiredFields', () => {
    beforeEach( () => {
        // The builder's localized strings (node test environment: no window of its own).
        global.window = { wpuf_form_builder: { i18n: { any_of_three_needed: 'three', email_needed: 'email' } } };
    } );

    it( 'refuses a post form without a title, body or excerpt', () => {
        expect( validateRequiredFields( [ field( 'text_field' ) ], 'wpuf_forms' ) ).toBe( 'three' );
        expect( validateRequiredFields( [ field( 'post_excerpt' ) ], 'wpuf_forms' ) ).toBeNull();
    } );

    it( 'finds a post title inside a column', () => {
        const column = field( 'column_field', { inner_fields: { 'column-1': [ field( 'post_title' ) ] } } );

        expect( validateRequiredFields( [ column ], 'wpuf_forms' ) ).toBeNull();
    } );

    it( 'refuses a registration form without User Email, and finds it inside a column', () => {
        expect( validateRequiredFields( [ field( 'user_login' ) ], 'wpuf_profile' ) ).toBe( 'email' );
        expect( validateRequiredFields( [ field( 'user_email' ) ], 'wpuf_profile' ) ).toBeNull();

        const column = field( 'column_field', { inner_fields: { 'column-1': [ field( 'user_email' ) ] } } );
        expect( validateRequiredFields( [ column ], 'wpuf_profile' ) ).toBeNull();
    } );
} );

describe( 'validatePaymentSettings', () => {
    it( 'passes when payment is off', () => {
        expect( validatePaymentSettings( { payment_options: 'off' } ) ).toBeNull();
    } );

    it( 'refuses pay per post without a cost', () => {
        expect( validatePaymentSettings( { payment_options: 'on', choose_payment_option: 'enable_pay_per_post', pay_per_post_cost: '0' } ) ).toMatch( /Charge for each post is required/ );
        expect( validatePaymentSettings( { payment_options: 'on', choose_payment_option: 'enable_pay_per_post', pay_per_post_cost: '2' } ) ).toBeNull();
    } );

    it( 'refuses the pack fallback without a cost', () => {
        expect( validatePaymentSettings( { payment_options: 'on', choose_payment_option: 'force_pack_purchase', fallback_ppp_enable: 'on', fallback_ppp_cost: '' } ) ).toMatch( /Cost for each additional post/ );
        expect( validatePaymentSettings( { payment_options: 'on', choose_payment_option: 'force_pack_purchase', fallback_ppp_enable: 'off' } ) ).toBeNull();
    } );
} );

describe( 'validateFieldMetaKeys', () => {
    const { validateFieldMetaKeys } = require( './saveValidation' );

    test( 'a custom field without a meta key, or two sharing one, is refused', () => {
        expect( validateFieldMetaKeys( [ { template: 'text_field', is_meta: 'yes', name: '', label: 'Phone' } ] ) ).toContain( 'Phone has no meta key.' );
        expect( validateFieldMetaKeys( [
            { template: 'text_field', is_meta: 'yes', name: 'phone', label: 'Phone' },
            { template: 'column_field', is_meta: 'no', inner_fields: { 'column-1': [ { template: 'text_field', is_meta: 'yes', name: 'phone', label: 'Mobile' } ] } },
        ] ) ).toContain( 'Phone, Mobile use the same meta key' );
        expect( validateFieldMetaKeys( [
            { template: 'date_field', is_meta: 'yes', name: '_EventStartDate', label: 'Start' },
            { template: 'text_field', is_meta: 'yes', name: '_eventstartdate', label: 'Text' },
        ] ) ).toContain( 'Start, Text use the same meta key' );
    } );

    test( 'post fields, distinct keys and repeat-field inner keys are fine', () => {
        expect( validateFieldMetaKeys( [
            { template: 'post_title', is_meta: 'no', name: 'post_title' },
            { template: 'text_field', is_meta: 'yes', name: 'phone', label: 'Phone' },
            { template: 'repeat_field', is_meta: 'yes', name: 'rows', label: 'Rows', inner_fields: [ { template: 'text_field', is_meta: 'yes', name: 'phone', label: 'Row phone' } ] },
        ] ) ).toBeNull();
    } );
} );
