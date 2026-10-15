import { buildPayload } from './payload';

const schema = { id: 7, page_id: 3, nonces: { submit: 'n' } };

describe( 'buildPayload', () => {
    test( 'a visible server-rendered field posts nothing itself (its inputs post through the form)', () => {
        const fields = [
            { name: 'post_title', template: 'post_title' },
            { name: 'qr_code', template: 'qr_code', server_rendered: true },
        ];
        const body = buildPayload( schema, { post_title: 'Hello', qr_code: '' }, new Set( [ 'post_title', 'qr_code' ] ), fields );

        expect( body.post_title ).toBe( 'Hello' );
        expect( Object.prototype.hasOwnProperty.call( body, 'qr_code' ) ).toBe( false );
    } );

    test( 'a hidden server-rendered field still posts what it holds', () => {
        const fields = [ { name: 'map', template: 'google_map', server_rendered: true, value: '23.8, 90.4' } ];
        const body = buildPayload( schema, { map: '23.8, 90.4' }, new Set(), fields );

        expect( body.map ).toBe( '23.8, 90.4' );
    } );
} );
