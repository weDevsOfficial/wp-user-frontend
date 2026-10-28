import { dispatch, select } from '@wordpress/data';
import { api } from '@wpuf/frontend-kit';

const { STORE_NAME } = require( './store' );

const page = ( n, titles ) => ( { page: n, pages: 2, total: 3, items: titles.map( ( title, i ) => ( { id: n * 10 + i, title } ) ) } );
const signed = { atts: '{"post_type":"post"}', sig: 'abc' };

describe( 'wpuf/dashboard', () => {
    test( 'keeps one slice per mount, starting from the boot page', () => {
        dispatch( STORE_NAME ).init( 'one', page( 1, [ 'A', 'B' ] ) );
        dispatch( STORE_NAME ).init( 'two', page( 1, [ 'X' ] ), 'Post Deleted' );

        expect( select( STORE_NAME ).getList( 'one' ).data.items ).toHaveLength( 2 );
        expect( select( STORE_NAME ).getList( 'two' ).notice ).toBe( 'Post Deleted' );
        expect( select( STORE_NAME ).getList( 'none' ).data ).toBeNull();
    } );

    test( 'loads a page with the signed attributes', async () => {
        api.getDashboardPosts.mockResolvedValueOnce( page( 2, [ 'C' ] ) );

        await dispatch( STORE_NAME ).loadPage( 'one', signed, 2 );

        expect( api.getDashboardPosts ).toHaveBeenCalledWith( signed, 2, { status: '', search: '', type: '', overview: '' } );
        expect( select( STORE_NAME ).getList( 'one' ).data.items[ 0 ].title ).toBe( 'C' );
        expect( select( STORE_NAME ).getList( 'one' ).loading ).toBe( false );
    } );

    test( 'deleting the last row of a page reloads the page before it', async () => {
        api.deleteDashboardPost.mockResolvedValueOnce( { deleted: true } );
        api.getDashboardPosts.mockResolvedValueOnce( page( 1, [ 'A', 'B' ] ) );

        const ok = await dispatch( STORE_NAME ).deletePost( 'one', signed, 20 );

        expect( ok ).toBe( true );
        expect( api.deleteDashboardPost ).toHaveBeenCalledWith( signed, 20 );
        // The page before, with the overview (the counts change).
        expect( api.getDashboardPosts ).toHaveBeenLastCalledWith( signed, 1, { status: '', search: '', type: '', overview: 1 } );
        expect( select( STORE_NAME ).getList( 'one' ).notice ).toBe( 'Post Deleted' );
        expect( select( STORE_NAME ).getList( 'one' ).deleting ).toBe( 0 );
    } );

    test( 'filters go to the server and the overview from a reload replaces the old one', async () => {
        dispatch( STORE_NAME ).init( 'three', page( 1, [ 'A' ] ), '', { stats: [ { status: 'publish', count: 1 } ] } );
        api.getDashboardPosts.mockResolvedValueOnce( { ...page( 1, [ 'B' ] ), filters: { status: 'draft', search: 'b', type: '' } } );

        await dispatch( STORE_NAME ).filter( 'three', signed, { status: 'draft', search: 'b' } );

        expect( api.getDashboardPosts ).toHaveBeenLastCalledWith( signed, 1, { status: 'draft', search: 'b', type: '', overview: '' } );
        expect( select( STORE_NAME ).getList( 'three' ).filters.status ).toBe( 'draft' );
        expect( select( STORE_NAME ).getList( 'three' ).overview.stats[ 0 ].count ).toBe( 1 );

        api.getDashboardPosts.mockResolvedValueOnce( { ...page( 1, [ 'B' ] ), overview: { stats: [ { status: 'publish', count: 0 } ] } } );
        await dispatch( STORE_NAME ).loadPage( 'three', signed, 1, true );

        expect( select( STORE_NAME ).getList( 'three' ).overview.stats[ 0 ].count ).toBe( 0 );
        expect( select( STORE_NAME ).getList( 'three' ).data.overview ).toBeUndefined();
    } );

    test( 'a refused delete shows the server message and keeps the list', async () => {
        api.deleteDashboardPost.mockRejectedValueOnce( new Error( 'Deleting posts is turned off.' ) );

        const ok = await dispatch( STORE_NAME ).deletePost( 'one', signed, 10 );

        expect( ok ).toBe( false );
        expect( select( STORE_NAME ).getList( 'one' ).error ).toBe( 'Deleting posts is turned off.' );
        expect( select( STORE_NAME ).getList( 'one' ).data.items ).toHaveLength( 2 );
    } );
} );
