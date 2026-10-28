import { multiSelectChoices, normalizeOptions, numberKeyAllowed, selectShownValue } from './values';

test( 'number inputs block "-" unless negatives are allowed', () => {
    expect( numberKeyAllowed( '-', false ) ).toBe( false );
    expect( numberKeyAllowed( '-', true ) ).toBe( true );
    expect( numberKeyAllowed( '5', false ) ).toBe( true );
} );

test( 'select shows a stored value only when it is an option', () => {
    const options = normalizeOptions( { day: 'Day', month: 'Month', 0: 'Zero' } );
    expect( selectShownValue( 'month', options ) ).toBe( 'month' );
    expect( selectShownValue( 0, options ) ).toBe( '0' );
    expect( selectShownValue( 'year', options ) ).toBeNull();
    expect( selectShownValue( '', options ) ).toBeNull();
    expect( selectShownValue( undefined, options ) ).toBeNull();
} );

test( 'options normalize from lists and maps', () => {
    expect( normalizeOptions( [ 'a', { value: 2, label: 'Two' } ] ) ).toEqual( [ { value: 'a', label: 'a' }, { value: '2', label: 'Two' } ] );
    expect( normalizeOptions( { x: 'X' } ) ).toEqual( [ { value: 'x', label: 'X' } ] );
    expect( normalizeOptions( null ) ).toEqual( [] );
} );

import { dateToYmd, isChecked, toList, ymdToDate } from './values';

test( 'two-state controls read the caller shape', () => {
    expect( isChecked( 'on', 'on' ) ).toBe( true );
    expect( isChecked( 'off', 'on' ) ).toBe( false );
    expect( isChecked( undefined, 'on' ) ).toBe( false );
    expect( isChecked( 'yes', 'yes' ) ).toBe( true );
    expect( isChecked( true, true ) ).toBe( true );
    expect( isChecked( 'true', true ) ).toBe( true );
    expect( isChecked( false, true ) ).toBe( false );
} );

test( 'lists normalize, falsy -> []', () => {
    expect( toList( [ 'link', 2 ] ) ).toEqual( [ 'link', '2' ] );
    expect( toList( '' ) ).toEqual( [] );
    expect( toList( null ) ).toEqual( [] );
    expect( toList( 'a' ) ).toEqual( [ 'a' ] );
} );

test( 'dates round-trip without a timezone shift', () => {
    expect( dateToYmd( ymdToDate( '2026-01-31' ) ) ).toBe( '2026-01-31' );
    expect( dateToYmd( ymdToDate( '2026-03-29 23:30:00' ) ) ).toBe( '2026-03-29' );
    expect( ymdToDate( '' ) ).toBeUndefined();
    expect( ymdToDate( 'nope' ) ).toBeUndefined();
    expect( dateToYmd( undefined ) ).toBe( '' );
} );

describe( 'multiSelectChoices', () => {
    it( 'shows a placeholder-only list as a note, with nothing to tick', () => {
        expect( multiSelectChoices( [ { value: 'no_packs', label: 'No subscription packs available' } ] ) )
            .toEqual( { choices: [], empty: 'No subscription packs available' } );
    } );

    it( 'keeps real choices and drops a placeholder next to them', () => {
        const packs = [ { value: '12', label: 'Gold' }, { value: '13', label: 'Silver' } ];

        expect( multiSelectChoices( packs ) ).toEqual( { choices: packs, empty: '' } );
        expect( multiSelectChoices( [ ...packs, { value: 'no_packs', label: 'x' } ] ) ).toEqual( { choices: packs, empty: '' } );
    } );

    it( 'has no note for an empty list', () => {
        expect( multiSelectChoices( [] ) ).toEqual( { choices: [], empty: '' } );
    } );
} );
