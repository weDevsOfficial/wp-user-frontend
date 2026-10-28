import { dispatch, select } from '@wordpress/data';

import { STORE_NAME } from './store';

const initial = { title: 'Hello', tags: [] };

describe( 'wpuf/frontend-form', () => {
    test( 'a form has no slice until its first change, then starts from its initial values', () => {
        expect( select( STORE_NAME ).getForm( 'a' ) ).toBeNull();

        dispatch( STORE_NAME ).setValue( 'a', 'tags', [ '3' ], initial );

        expect( select( STORE_NAME ).getForm( 'a' ) ).toEqual( { values: { title: 'Hello', tags: [ '3' ] }, errors: {}, touched: {}, step: 0 } );
    } );

    test( 'setting a value clears that field error only', () => {
        dispatch( STORE_NAME ).setErrors( 'b', { title: 'Required', body: 'Required' }, initial );
        dispatch( STORE_NAME ).setValue( 'b', 'title', 'Fixed', initial );

        expect( select( STORE_NAME ).getForm( 'b' ).errors ).toEqual( { body: 'Required' } );
    } );

    test( 'values, errors and step take React-style updaters', () => {
        dispatch( STORE_NAME ).setValues( 'c', ( current ) => ( { ...current, extra: 1 } ), initial );
        dispatch( STORE_NAME ).setErrors( 'c', ( current ) => ( { ...current, title: 'Too short' } ), initial );
        dispatch( STORE_NAME ).setStep( 'c', ( current ) => current + 2, initial );
        dispatch( STORE_NAME ).touch( 'c', 'title', initial );

        const form = select( STORE_NAME ).getForm( 'c' );

        expect( form.values ).toEqual( { title: 'Hello', tags: [], extra: 1 } );
        expect( form.errors ).toEqual( { title: 'Too short' } );
        expect( form.step ).toBe( 2 );
        expect( form.touched ).toEqual( { title: true } );
    } );

    test( 'forms stay apart and a removed form is gone', () => {
        dispatch( STORE_NAME ).setValue( 'd', 'title', 'D', initial );
        dispatch( STORE_NAME ).setValue( 'e', 'title', 'E', initial );
        dispatch( STORE_NAME ).remove( 'd' );

        expect( select( STORE_NAME ).getForm( 'd' ) ).toBeNull();
        expect( select( STORE_NAME ).getForm( 'e' ).values.title ).toBe( 'E' );
    } );
} );
