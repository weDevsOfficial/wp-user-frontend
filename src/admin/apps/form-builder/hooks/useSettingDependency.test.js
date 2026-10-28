import { meetsDependencies } from './useSettingDependency';

/**
 * Option rows show only when every dependency holds (task 1.18).
 */
describe( 'meetsDependencies', () => {
    test( 'no dependencies always shows', () => {
        expect( meetsDependencies( {}, null ) ).toBe( true );
        expect( meetsDependencies( {}, {} ) ).toBe( true );
    } );

    test( 'single value must match', () => {
        expect( meetsDependencies( { woo_attr: 'yes' }, { woo_attr: 'yes' } ) ).toBe( true );
        expect( meetsDependencies( { woo_attr: '' }, { woo_attr: 'yes' } ) ).toBe( false );
    } );

    test( 'an array match does not skip the next dependency', () => {
        const deps = { rich: [ 'yes', 'teeny' ], woo_attr: 'yes' };

        expect( meetsDependencies( { rich: 'yes', woo_attr: 'yes' }, deps ) ).toBe( true );
        expect( meetsDependencies( { rich: 'yes', woo_attr: '' }, deps ) ).toBe( false );
        expect( meetsDependencies( { rich: 'no', woo_attr: 'yes' }, deps ) ).toBe( false );
    } );
} );
