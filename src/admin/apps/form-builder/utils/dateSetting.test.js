import { fromPicker, toPicker } from './dateSetting';

describe( 'form builder date settings', () => {
    it( 'reads what develop stored and typed values', () => {
        expect( toPicker( '2026-10-20' ) ).toBe( '2026-10-20 00:00:00' );
        expect( toPicker( '2026/10/01 09:30' ) ).toBe( '2026-10-01 09:30:00' );
        expect( toPicker( '2026-1-5 7:05' ) ).toBe( '2026-01-05 07:05:00' );
        expect( toPicker( '' ) ).toBe( '' );
        expect( toPicker( 'next monday' ) ).toBe( '' );
    } );

    it( 'stores develop\'s YYYY-MM-DD without a time, adds the time when one is picked', () => {
        expect( fromPicker( '2026-10-15 00:00:00' ) ).toBe( '2026-10-15' );
        expect( fromPicker( '2026-12-31 18:00:00' ) ).toBe( '2026-12-31 18:00' );
        expect( fromPicker( '' ) ).toBe( '' );
    } );

    it( 'round-trips develop values unchanged', () => {
        [ '2026-10-20', '2026-12-31 18:00' ].forEach( ( value ) => expect( fromPicker( toPicker( value ) ) ).toBe( value ) );
    } );
} );
