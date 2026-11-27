import { dispatch, select } from '@wordpress/data';
import { request } from '@wpuf/api';

import { STORE_NAME } from './index';

describe( 'wpuf/tools', () => {
    beforeEach( () => request.mockReset() );

    test( 'loads the export forms per form type', async () => {
        request.mockResolvedValueOnce( [ { id: 1, title: 'Post' } ] );

        expect( select( STORE_NAME ).getForms( 'wpuf_forms' ) ).toBeNull();
        expect( await dispatch( STORE_NAME ).loadForms( 'wpuf_forms' ) ).toBeNull();

        expect( request ).toHaveBeenCalledWith( '/wpuf/v1/admin/tools/forms?type=wpuf_forms' );
        expect( select( STORE_NAME ).getForms( 'wpuf_forms' ) ).toEqual( [ { id: 1, title: 'Post' } ] );
        expect( select( STORE_NAME ).getForms( 'wpuf_profile' ) ).toBeNull();
    } );

    test( 'a failed form load leaves an empty list and returns the error', async () => {
        const failure = new Error( 'denied' );

        request.mockRejectedValueOnce( failure );

        expect( await dispatch( STORE_NAME ).loadForms( 'wpuf_profile' ) ).toBe( failure );
        expect( select( STORE_NAME ).getForms( 'wpuf_profile' ) ).toEqual( [] );
        expect( select( STORE_NAME ).getFormsError( 'wpuf_profile' ) ).toBe( failure );
    } );

    test( 'loads the shortcodes, keeping the error of a failed load', async () => {
        request.mockRejectedValueOnce( new Error( 'offline' ) );
        await dispatch( STORE_NAME ).loadShortcodes();

        expect( select( STORE_NAME ).getShortcodes() ).toBeNull();
        expect( select( STORE_NAME ).getShortcodesError().message ).toBe( 'offline' );

        request.mockResolvedValueOnce( [ { title: 'Forms' } ] );
        await dispatch( STORE_NAME ).loadShortcodes();

        expect( select( STORE_NAME ).getShortcodes() ).toEqual( [ { title: 'Forms' } ] );
        expect( select( STORE_NAME ).getShortcodesError() ).toBeNull();
    } );

    test( 'loads the onboarding entry', async () => {
        request.mockResolvedValueOnce( { show: true, url: '/x' } );
        await dispatch( STORE_NAME ).loadOnboarding();

        expect( request ).toHaveBeenCalledWith( '/wpuf/v1/admin/tools/onboarding' );
        expect( select( STORE_NAME ).getOnboarding() ).toEqual( { show: true, url: '/x' } );
    } );
} );
