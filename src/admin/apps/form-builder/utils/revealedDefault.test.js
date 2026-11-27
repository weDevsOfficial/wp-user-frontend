import { revealedDefault } from './revealedDefault';

const position = { name: 'icon_position', type: 'select', default: 'left_label' };
const icon = { name: 'field_icon', type: 'icon_selector', default: 'fas fa-user' };

describe( 'revealedDefault', () => {
    beforeEach( () => {
        // Node test environment: the builder's localized data on a bare window.
        global.window = { wpuf_form_builder: { defaultIcons: { text_field: 'fa-solid fa-font' } } };
    } );

    it( 'gives a missing setting the default the input shows', () => {
        expect( revealedDefault( position, { template: 'text_field' } ) ).toBe( 'left_label' );
    } );

    it( 'keeps a stored value, also an empty one', () => {
        expect( revealedDefault( position, { icon_position: 'right_label' } ) ).toBeUndefined();
        expect( revealedDefault( position, { icon_position: '' } ) ).toBeUndefined();
    } );

    it( 'stores nothing for a setting without a default', () => {
        expect( revealedDefault( { name: 'placeholder', type: 'text' }, {} ) ).toBeUndefined();
        expect( revealedDefault( { name: 'help', type: 'text', default: '' }, {} ) ).toBeUndefined();
    } );

    it( 'picks the field type icon as develop did, for an empty or placeholder icon', () => {
        expect( revealedDefault( icon, { template: 'text_field' } ) ).toBe( 'fa-solid fa-font' );
        expect( revealedDefault( icon, { template: 'text_field', field_icon: '' } ) ).toBe( 'fa-solid fa-font' );
        expect( revealedDefault( icon, { template: 'text_field', field_icon: 'fas fa-0' } ) ).toBe( 'fa-solid fa-font' );
        expect( revealedDefault( icon, { template: 'email_address' } ) ).toBe( 'fa-solid fa-circle' );
        expect( revealedDefault( icon, { template: 'text_field', field_icon: 'fa fa-star' } ) ).toBeUndefined();
    } );
} );
