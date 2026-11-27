import { isReadOnly } from './Field';

describe( 'isReadOnly', () => {
    test( 'a value classic reads as set (! empty) makes the field read-only', () => {
        // The React builder stores true; older forms "yes" or [ "yes" ].
        [ true, 'yes', 1, '1', [ 'yes' ] ].forEach( ( value ) => {
            expect( isReadOnly( { read_only: value } ) ).toBe( true );
        } );
    } );

    test( 'an empty value or an explicit "no" keeps the field editable', () => {
        [ undefined, null, false, '', 0, '0', 'no', [] ].forEach( ( value ) => {
            expect( isReadOnly( { read_only: value } ) ).toBe( false );
        } );
    } );
} );
