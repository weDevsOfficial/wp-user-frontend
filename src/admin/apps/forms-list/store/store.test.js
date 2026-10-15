import { dispatch, select } from '@wordpress/data';
import { request } from '@wpuf/api';

import { STORE_NAME } from './index';

const answer = ( rows, counts ) => ( {
    success: true,
    result: rows,
    counts,
    pagination: { total_items: String( rows.length ), total_pages: 1 },
} );

describe( 'wpuf/forms-list', () => {
    beforeEach( () => request.mockReset() );

    test( 'loads a page per post type and keeps the counts', async () => {
        request.mockResolvedValueOnce( answer( [ { ID: 1 }, { ID: 2 } ], { any: 2, trash: 0 } ) );

        await dispatch( STORE_NAME ).fetchForms( 'wpuf_forms', 1, 'any', '', 10 );

        expect( request ).toHaveBeenCalledWith( '/wpuf/v1/wpuf_form', { query: { page: 1, per_page: 10, status: 'any', post_type: 'wpuf_forms' } } );
        expect( select( STORE_NAME ).getForms( 'wpuf_forms' ) ).toEqual( [ { ID: 1 }, { ID: 2 } ] );
        expect( select( STORE_NAME ).getCounts( 'wpuf_forms' ) ).toEqual( { any: 2, trash: 0 } );
        expect( select( STORE_NAME ).getPagination( 'wpuf_forms' ) ).toEqual( { total_items: 2, total_pages: 1, current_page: 1, per_page: 10 } );
        expect( select( STORE_NAME ).isLoading( 'wpuf_forms' ) ).toBe( false );
        expect( select( STORE_NAME ).getForms( 'wpuf_profile' ) ).toEqual( [] );
    } );

    test( 'sends the search term and drops an older answer', async () => {
        let slow;

        request
            .mockImplementationOnce( () => new Promise( ( resolve ) => ( slow = resolve ) ) )
            .mockResolvedValueOnce( answer( [ { ID: 9 } ] ) );

        const first = dispatch( STORE_NAME ).fetchForms( 'wpuf_forms', 1, 'any', 'old', 10 );
        await dispatch( STORE_NAME ).fetchForms( 'wpuf_forms', 1, 'any', 'new', 10 );

        slow( answer( [ { ID: 5 } ] ) );
        await first;

        expect( request.mock.calls[ 1 ][ 1 ].query.s ).toBe( 'new' );
        expect( select( STORE_NAME ).getForms( 'wpuf_forms' ) ).toEqual( [ { ID: 9 } ] );
    } );

    test( 'a failed request empties the list and keeps the error', async () => {
        request.mockRejectedValueOnce( new Error( 'offline' ) );

        await dispatch( STORE_NAME ).fetchForms( 'wpuf_profile', 2, 'trash', '', 20 );

        expect( select( STORE_NAME ).getError( 'wpuf_profile' ).message ).toBe( 'offline' );
        expect( select( STORE_NAME ).getForms( 'wpuf_profile' ) ).toEqual( [] );
        expect( select( STORE_NAME ).getPagination( 'wpuf_profile' ).per_page ).toBe( 20 );
    } );
} );
