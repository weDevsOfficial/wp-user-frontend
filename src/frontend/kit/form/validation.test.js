import { validateField } from './validation';

jest.mock( '@wordpress/i18n', () => ( {
    __: ( text ) => text,
    sprintf: ( format, ...args ) => {
        let i = 0;
        return format.replace( /%(\d+\$)?s/g, ( m, pos ) => String( pos ? args[ parseInt( pos, 10 ) - 1 ] : args[ i++ ] ) );
    },
} ) );

describe( 'numeric range', () => {
    const field = { template: 'numeric_text_field', label: 'Hours per week', min_value_field: '1', max_value_field: '40' };

    it( 'refuses values outside min / max with a visible message', () => {
        expect( validateField( field, '50' ) ).toBe( 'Hours per week must be at most 40' );
        expect( validateField( field, '0' ) ).toBe( 'Hours per week must be at least 1' );
    } );

    it( 'accepts values in range, empty optional values and a 0 max (no maximum)', () => {
        expect( validateField( field, '20' ) ).toBe( '' );
        expect( validateField( field, '' ) ).toBe( '' );
        expect( validateField( { ...field, max_value_field: '0' }, '500' ) ).toBe( '' );
    } );
} );
