import { findRecommendations } from './settingsRecommendations';

const site = { enabled: true, gateways: 1, payment_page: true, settings_url: 'pay', plans_url: 'plans' };
const keys = ( settings, data = { site_payments: site, subscriptions: [ { id: 1 } ] }, fields = [] ) => findRecommendations( { post_permission: 'role_base', ...settings }, data, fields ).map( ( item ) => item.key );

describe( 'findRecommendations', () => {
    it( 'asks who can post when Post Permission is empty', () => {
        const found = findRecommendations( { post_permission: '' }, { site_payments: site } );

        expect( found.map( ( item ) => item.key ) ).toEqual( [ 'post_permission' ] );
        expect( found[ 0 ].action.set ).toEqual( { post_permission: 'role_base' } );
    } );

    it( 'is quiet for a form that does not charge', () => {
        expect( keys( { payment_options: 'off', post_permission: 'guest_post' } ) ).toEqual( [] );
    } );

    it( 'flags pay per post without a cost (charging silently off)', () => {
        expect( keys( { payment_options: 'true', choose_payment_option: 'enable_pay_per_post', pay_per_post_cost: '0' } ) ).toEqual( [ 'cost' ] );
    } );

    it( 'asks for guest details when guests must pay', () => {
        const settings = { payment_options: 'on', choose_payment_option: 'enable_pay_per_post', pay_per_post_cost: '3', post_permission: 'guest_post' };

        expect( keys( settings ) ).toEqual( [ 'guest_details' ] );
        expect( keys( { ...settings, guest_details: 'on' } ) ).toEqual( [] );
        expect( findRecommendations( settings, { site_payments: site } )[ 0 ].action.set ).toEqual( { guest_details: 'on' } );
    } );

    it( 'points at the site payment setup', () => {
        const settings = { payment_options: 'on', choose_payment_option: 'enable_pay_per_post', pay_per_post_cost: '3', post_permission: 'role_base' };

        expect( keys( settings, { site_payments: { ...site, enabled: false } } ) ).toEqual( [ 'site_payments' ] );
        expect( keys( settings, { site_payments: { ...site, gateways: 0 } } ) ).toEqual( [ 'site_payments' ] );
        expect( keys( settings, { site_payments: { ...site, payment_page: false } } ) ).toEqual( [ 'site_payments' ] );
    } );

    it( 'needs a plan when a pack is forced', () => {
        const settings = { payment_options: 'on', choose_payment_option: 'force_pack_purchase', post_permission: 'role_base' };

        expect( keys( settings, { site_payments: site, subscriptions: [] } ) ).toEqual( [ 'plans' ] );
        expect( keys( settings ) ).toEqual( [] );
    } );

    it( 'asks to turn on Multi-Step when the form has Step Start fields', () => {
        const fields = [ { template: 'step_start' }, { template: 'post_title' } ];

        expect( keys( {}, { site_payments: site }, fields ) ).toEqual( [ 'multistep' ] );
        expect( keys( { enable_multistep: 'on' }, { site_payments: site }, fields ) ).toEqual( [] );
        expect( keys( {}, { site_payments: site }, [ { template: 'post_title' } ] ) ).toEqual( [] );
    } );
} );
