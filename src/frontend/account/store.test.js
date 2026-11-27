import { dispatch, select } from '@wordpress/data';
import { api } from '@wpuf/frontend-kit';

const location = { origin: 'https://site.test', search: '?section=posts&pagenum=2', href: 'https://site.test/account/?section=posts&pagenum=2' };

global.window = {
    location,
    history: {
        pushState: jest.fn( ( state, title, url ) => {
            const next = new URL( url, location.origin );

            location.search = next.search;
            location.href = next.toString();
        } ),
    },
    addEventListener: jest.fn(),
    removeEventListener: jest.fn(),
};

const { STORE_NAME } = require( './store' );
const { urlFor } = require( './router' );

describe( 'wpuf/account', () => {
    test( 'starts from the boot data and the first URL', () => {
        dispatch( STORE_NAME ).init( { profile: { display_name: 'Ana' }, stats: [ { key: 'posts', value: 3 } ] } );

        expect( select( STORE_NAME ).getSection( 'dashboard' ) ).toBe( 'posts' );
        expect( select( STORE_NAME ).getPage() ).toBe( 2 );
        expect( select( STORE_NAME ).getProfile().display_name ).toBe( 'Ana' );
    } );

    test( 'navigate pushes one history entry and moves the location', () => {
        dispatch( STORE_NAME ).navigate( urlFor( 'https://site.test/account/', 'edit-profile', { pagenum: '' } ) );

        expect( window.history.pushState ).toHaveBeenCalledWith( {}, '', 'https://site.test/account/?section=edit-profile' );
        expect( select( STORE_NAME ).getSection( 'dashboard' ) ).toBe( 'edit-profile' );
        expect( select( STORE_NAME ).getPage() ).toBe( 1 );
    } );

    test( 'back / forward sets the location without a new entry', () => {
        window.history.pushState.mockClear();
        dispatch( STORE_NAME ).setLocation( '?section=posts&pagenum=3' );

        expect( window.history.pushState ).not.toHaveBeenCalled();
        expect( select( STORE_NAME ).getPage() ).toBe( 3 );
    } );

    test( 'post lists per type, only the latest load writes', async () => {
        let slow;

        api.getAccountPosts
            .mockImplementationOnce( () => new Promise( ( resolve ) => ( slow = resolve ) ) )
            .mockResolvedValueOnce( { items: [ { id: 2 } ], page: 2 } );

        const first = dispatch( STORE_NAME ).loadPosts( 'post', 1 );
        await dispatch( STORE_NAME ).loadPosts( 'post', 2 );

        slow( { items: [ { id: 1 } ], page: 1 } );
        await first;

        expect( select( STORE_NAME ).getPosts( 'post' ).data.items ).toEqual( [ { id: 2 } ] );
        expect( select( STORE_NAME ).getPosts( 'page' ).loading ).toBe( true );
    } );

    test( 'delete reloads the page and the header; a failure shows on the list', async () => {
        api.deleteAccountPost.mockResolvedValueOnce( {} );
        api.getAccountPosts.mockResolvedValueOnce( { items: [], page: 1 } );
        api.getAccount.mockResolvedValueOnce( { profile: { display_name: 'Ana' }, stats: [ { key: 'posts', value: 2 } ] } );

        expect( await dispatch( STORE_NAME ).deletePost( 'post', 2, 1 ) ).toBe( true );
        await new Promise( ( resolve ) => setTimeout( resolve, 0 ) );

        expect( api.getAccountPosts ).toHaveBeenLastCalledWith( 'post', 1 );
        expect( select( STORE_NAME ).getStats() ).toEqual( [ { key: 'posts', value: 2 } ] );

        api.deleteAccountPost.mockRejectedValueOnce( new Error( 'Not allowed' ) );

        expect( await dispatch( STORE_NAME ).deletePost( 'post', 9, 1 ) ).toBe( false );
        expect( select( STORE_NAME ).getPosts( 'post' ).error ).toBe( 'Not allowed' );
    } );
} );
