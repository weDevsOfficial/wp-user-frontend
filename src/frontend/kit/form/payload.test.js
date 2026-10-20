import { afterSubmit, buildPayload } from './payload';

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

    test( 'a read-only field posts its value (the classic form serialized it too)', () => {
        const fields = [ { name: 'member_code', template: 'text_field', read_only: 'yes' } ];
        const body = buildPayload( schema, { member_code: 'A-17' }, new Set( [ 'member_code' ] ), fields );

        expect( body.member_code ).toBe( 'A-17' );
    } );

    test( 'a new post form says wpuf_form_status=new; an edit or a profile form does not', () => {
        const fields = [ { name: 'post_title', template: 'post_title' } ];
        const visible = new Set( [ 'post_title' ] );

        expect( buildPayload( { ...schema, mode: 'create', form_type: 'post' }, { post_title: 'A' }, visible, fields ).wpuf_form_status ).toBe( 'new' );
        expect( buildPayload( { ...schema, mode: 'edit', form_type: 'post', post: { id: 9 } }, { post_title: 'A' }, visible, fields ).wpuf_form_status ).toBeUndefined();
        expect( buildPayload( { ...schema, mode: 'create', form_type: 'registration' }, { post_title: 'A' }, visible, fields ).wpuf_form_status ).toBeUndefined();
    } );
} );

describe( 'afterSubmit', () => {
    const original = global.window;

    afterEach( () => {
        global.window = original;
    } );

    test( 'reports whether the page is leaving, so the form stays busy until it unloads', () => {
        const assign = jest.fn();

        global.window = { location: { href: 'https://example.com/form/', assign } };

        const shown = jest.fn();

        expect( afterSubmit( { show_message: true, message: 'Saved' }, schema, shown ) ).toBe( false );
        expect( shown ).toHaveBeenCalledWith( 'Saved' );
        expect( assign ).not.toHaveBeenCalled();

        expect( afterSubmit( { redirect_to: 'https://example.com/post/' }, schema, shown ) ).toBe( true );
        expect( assign ).toHaveBeenCalledWith( 'https://example.com/post/' );
    } );

    test( 'a registration that logged the member in shows the message, then opens the page again', () => {
        jest.useFakeTimers();

        const assign = jest.fn();
        const shown = jest.fn();

        global.window = { location: { href: 'https://example.com/join/', assign }, setTimeout };

        expect( afterSubmit( { show_message: true, message: 'Registration successful', logged_in: true, redirect_to: 'https://example.com/join/?msg=profile_update' }, schema, shown ) ).toBe( true );
        expect( shown ).toHaveBeenCalledWith( 'Registration successful' );
        expect( assign ).not.toHaveBeenCalled();

        jest.advanceTimersByTime( 1500 );

        expect( assign ).toHaveBeenCalledWith( 'https://example.com/join/?msg=profile_update' );

        jest.useRealTimers();
    } );
} );
