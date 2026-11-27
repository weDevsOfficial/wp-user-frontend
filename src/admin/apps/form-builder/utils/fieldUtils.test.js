import { createField, isPhpTruthy, isRichEditor } from './fieldUtils';

/**
 * Field names as the Vue builder generated them (repeat field check, 1.9).
 */
const settings = {
    text_field: { field_props: { template: 'text_field', label: 'Text', name: '', is_meta: 'yes' } },
    section_break: { field_props: { template: 'section_break', label: 'Section Break', name: '', is_meta: 'no' } },
};

describe( 'createField names', () => {
    test( 'top level: label slug, then _<count> per same template', () => {
        expect( createField( 'text_field', settings, [] ).name ).toBe( 'text' );
        expect( createField( 'text_field', settings, [ { template: 'text_field' } ] ).name ).toBe( 'text_1' );
    } );

    test( 'inner field: meta fields always get a random suffix', () => {
        expect( createField( 'text_field', settings, [], { innerField: true } ).name ).toMatch( /^text_\d+$/ );
    } );

    test( 'inner field: non-meta fields get no generated name', () => {
        expect( createField( 'section_break', settings, [], { innerField: true } ).name ).toBe( '' );
    } );
} );

describe( 'isRichEditor', () => {
    it( 'matches the frontend: editor only for yes / teeny', () => {
        expect( isRichEditor( 'yes' ) ).toBe( true );
        expect( isRichEditor( 'teeny' ) ).toBe( true );
        expect( isRichEditor( 'no' ) ).toBe( false );
        expect( isRichEditor( undefined ) ).toBe( false );
        expect( isRichEditor( '' ) ).toBe( false );
        expect( isRichEditor( true ) ).toBe( false );
    } );
} );

describe( 'isPhpTruthy', () => {
    it( 'reads stored values like PHP', () => {
        [ false, null, undefined, '', '0', 0, [], {} ].forEach( ( value ) => expect( isPhpTruthy( value ) ).toBe( false ) );
        [ true, 'yes', '1', 1, [ 'true' ], { a: 1 }, 'no' ].forEach( ( value ) => expect( isPhpTruthy( value ) ).toBe( true ) );
    } );
} );
