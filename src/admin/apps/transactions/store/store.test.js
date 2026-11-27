import { dispatch, select } from '@wordpress/data';
import { request } from '@wpuf/api';

import { STORE_NAME } from './index';

const page = ( items ) => ( { items, counts: { all: items.length }, per_page: 20 } );

describe( 'wpuf/transactions', () => {
    beforeEach( () => request.mockReset() );

    test( 'loads a page and returns the answer', async () => {
        request.mockResolvedValueOnce( page( [ { key: 'o1' } ] ) );

        const answer = await dispatch( STORE_NAME ).fetchTransactions( { status: 'all', page: 1 } );

        expect( request.mock.calls[ 0 ][ 0 ] ).toBe( '/wpuf/v1/admin/transactions?status=all&page=1' );
        expect( answer.per_page ).toBe( 20 );
        expect( select( STORE_NAME ).getResult().items ).toEqual( [ { key: 'o1' } ] );
        expect( select( STORE_NAME ).isLoading() ).toBe( false );
        expect( select( STORE_NAME ).getError() ).toBeNull();
    } );

    test( 'drops an older answer', async () => {
        let slow;

        request
            .mockImplementationOnce( () => new Promise( ( resolve ) => ( slow = resolve ) ) )
            .mockResolvedValueOnce( page( [ { key: 'new' } ] ) );

        const first = dispatch( STORE_NAME ).fetchTransactions( { search: 'old' } );
        await dispatch( STORE_NAME ).fetchTransactions( { search: 'new' } );

        slow( page( [ { key: 'old' } ] ) );
        await first;

        expect( select( STORE_NAME ).getResult().items ).toEqual( [ { key: 'new' } ] );
    } );

    test( 'a failure keeps the last rows and the error; a cancelled load is no error', async () => {
        request.mockRejectedValueOnce( new Error( 'offline' ) );
        expect( await dispatch( STORE_NAME ).fetchTransactions( {} ) ).toBeNull();

        expect( select( STORE_NAME ).getError().message ).toBe( 'offline' );
        expect( select( STORE_NAME ).getResult().items ).toEqual( [ { key: 'new' } ] );

        const controller = new AbortController();

        controller.abort();
        request.mockRejectedValueOnce( Object.assign( new Error( 'aborted' ), { code: 'timeout' } ) );
        await dispatch( STORE_NAME ).fetchTransactions( {}, controller.signal );

        expect( select( STORE_NAME ).getError() ).toBeNull();
        expect( select( STORE_NAME ).isLoading() ).toBe( false );
    } );

    test( 'row actions post the kind and id of each row', async () => {
        request.mockResolvedValueOnce( { done: 1, message: 'Deleted' } );

        const response = await dispatch( STORE_NAME ).runAction( 'delete', [ { kind: 'transaction', id: 4, key: 't4', amount: 9 } ] );

        expect( response.message ).toBe( 'Deleted' );
        expect( request ).toHaveBeenCalledWith( '/wpuf/v1/admin/transactions/delete', {
            method: 'POST',
            data: { items: [ { kind: 'transaction', id: 4 } ] },
        } );
    } );
} );
