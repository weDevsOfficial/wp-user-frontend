import { proMessageText } from './proMessage';

describe( 'proMessageText', () => {
    it( 'drops develop\'s wrapper markup', () => {
        expect( proMessageText( '<p class="wpuf-text-gray-500 wpuf-font-medium wpuf-text-xl">Please upgrade to the Pro version to unlock all these awesome features</p>' ) )
            .toBe( 'Please upgrade to the Pro version to unlock all these awesome features' );
    } );

    it( 'keeps plain text and handles empty values', () => {
        expect( proMessageText( 'Plain' ) ).toBe( 'Plain' );
        expect( proMessageText( '' ) ).toBe( '' );
        expect( proMessageText( undefined ) ).toBe( '' );
    } );
} );
