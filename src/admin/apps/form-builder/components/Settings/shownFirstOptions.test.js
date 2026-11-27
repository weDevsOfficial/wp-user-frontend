import { firstOptionValue, shownFirstOption, findSettingDef, withShownFirstOptions } from './shownFirstOptions';

/**
 * Selects develop always stored with their first option (QA stories 11 and 15).
 */
describe( 'shownFirstOptions', () => {
    const items = {
        form_settings: {
            general: { section: { after_sign_up_settings: { fields: {
                reg_redirect_to: { type: 'select', options: { same: 'Same page', page: 'To a page', url: 'To a custom URL' } },
                profile_redirect_to: { type: 'select', options: { same: 'Same page', page: 'To a page', url: 'To a custom URL' } },
            } } } },
            display: { section: { form_style: { fields: {
                label_position: { type: 'select', options: { above: 'Above Element', left: 'Left', right: 'Right', hidden: 'Hidden' } },
            } } } },
        },
        modules: { mailpoet: { mailpoet_3_list: { type: 'select', options: { 3: 'Newsletter mailing list', 7: 'Other' } } } },
    };

    test( 'first option of a select, skipping an empty "-- Select --" key', () => {
        expect( firstOptionValue( { type: 'select', options: { '': '-- Select --', same: 'Same page' } } ) ).toBe( 'same' );
        expect( firstOptionValue( { type: 'note' } ) ).toBe( '' );
    } );

    test( 'shown value: static for known keys, the field first option for site lists', () => {
        expect( shownFirstOption( 'label_position', {} ) ).toBe( 'above' );
        expect( shownFirstOption( 'mailpoet_3_list', items.modules.mailpoet.mailpoet_3_list ) ).toBe( '3' );
        expect( shownFirstOption( 'post_permission', {} ) ).toBeUndefined();
        expect( shownFirstOption( 'mailpoet_3_list', { type: 'note' } ) ).toBeUndefined();
    } );

    test( 'finds a definition anywhere in the settings items', () => {
        expect( findSettingDef( items, 'profile_redirect_to' ).options.same ).toBe( 'Same page' );
        expect( findSettingDef( items, 'nope' ) ).toBeNull();
    } );

    test( 'the save fills missing keys only; stored values and absent definitions stay', () => {
        const out = withShownFirstOptions( { reg_redirect_to: 'url', foo: 'bar' }, items );

        expect( out.reg_redirect_to ).toBe( 'url' );
        expect( out.profile_redirect_to ).toBe( 'same' );
        expect( out.label_position ).toBe( 'above' );
        expect( out.mailpoet_3_list ).toBe( '3' );
        expect( out.foo ).toBe( 'bar' );
        // No Choose Payment Option definition on a registration form: not added.
        expect( out ).not.toHaveProperty( 'choose_payment_option' );
        // Nested expiration keys are the server's (Normalizers::post_expiration).
        expect( out ).not.toHaveProperty( 'expiration_settings' );
    } );
} );
