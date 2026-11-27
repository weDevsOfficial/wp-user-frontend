import {
    TOP,
    canDrop,
    columnContainer,
    getList,
    isNoopMove,
    locateField,
    moveField,
    parseContainer,
    repeatContainer,
} from './dndTree';

const text = ( id, extra = {} ) => ( { id, template: 'text_field', name: 'text_' + id, label: 'Text ' + id, ...extra } );

const form = () => [
    text( 1 ),
    {
        id: 2,
        template: 'column_field',
        input_type: 'column_field',
        columns: '2',
        inner_fields: { 'column-1': [ text( 21 ) ], 'column-2': [ text( 22 ), text( 23 ) ] },
    },
    { id: 3, template: 'repeat_field', input_type: 'repeat', inner_fields: [ text( 31 ), text( 32 ) ] },
    text( 4 ),
];

const ids = ( list ) => list.map( ( field ) => field.id );

describe( 'dndTree containers', () => {
    it( 'parses container ids', () => {
        expect( parseContainer( TOP ) ).toEqual( { type: 'top' } );
        expect( parseContainer( columnContainer( 2, 'column-2' ) ) ).toEqual( { type: 'column', fieldId: '2', column: 'column-2' } );
        expect( parseContainer( repeatContainer( 3 ) ) ).toEqual( { type: 'repeat', fieldId: '3' } );
    } );

    it( 'reads every list', () => {
        const fields = form();

        expect( ids( getList( fields, TOP ) ) ).toEqual( [ 1, 2, 3, 4 ] );
        expect( ids( getList( fields, columnContainer( 2, 'column-2' ) ) ) ).toEqual( [ 22, 23 ] );
        expect( ids( getList( fields, columnContainer( 2, 'column-3' ) ) ) ).toEqual( [] );
        expect( ids( getList( fields, repeatContainer( 3 ) ) ) ).toEqual( [ 31, 32 ] );
        expect( getList( fields, repeatContainer( 99 ) ) ).toEqual( [] );
    } );

    it( 'locates fields at every level', () => {
        const fields = form();

        expect( locateField( fields, 4 ) ).toEqual( { container: TOP, index: 3 } );
        expect( locateField( fields, 23 ) ).toEqual( { container: columnContainer( 2, 'column-2' ), index: 1 } );
        expect( locateField( fields, 32 ) ).toEqual( { container: repeatContainer( 3 ), index: 1 } );
        expect( locateField( fields, 999 ) ).toBeNull();
    } );
} );

describe( 'dndTree moveField', () => {
    it( 'reorders the stage with insertion indexes', () => {
        const fields = form();

        expect( ids( moveField( fields, { container: TOP, index: 0 }, { container: TOP, index: 4 } ) ) ).toEqual( [ 2, 3, 4, 1 ] );
        expect( ids( moveField( fields, { container: TOP, index: 3 }, { container: TOP, index: 0 } ) ) ).toEqual( [ 4, 1, 2, 3 ] );
    } );

    it( 'returns the same array for a no-op', () => {
        const fields = form();

        expect( isNoopMove( { container: TOP, index: 1 }, { container: TOP, index: 2 } ) ).toBe( true );
        expect( moveField( fields, { container: TOP, index: 1 }, { container: TOP, index: 1 } ) ).toBe( fields );
        expect( moveField( fields, { container: TOP, index: 1 }, { container: TOP, index: 2 } ) ).toBe( fields );
        expect( moveField( fields, { container: TOP, index: 9 }, { container: TOP, index: 0 } ) ).toBe( fields );
    } );

    it( 'moves a stage field into a column cell, keeping the object as is', () => {
        const fields = form();
        const moved = moveField( fields, { container: TOP, index: 0 }, { container: columnContainer( 2, 'column-1' ), index: 1 } );

        expect( ids( moved ) ).toEqual( [ 2, 3, 4 ] );
        expect( ids( getList( moved, columnContainer( 2, 'column-1' ) ) ) ).toEqual( [ 21, 1 ] );
        expect( getList( moved, columnContainer( 2, 'column-1' ) )[ 1 ] ).toBe( fields[ 0 ] );
    } );

    it( 'stores a moved field in develop\'s shape for its new list, never renaming it', () => {
        const fields = [ text( 1, { show_icon: 'no', field_icon: '', icon_position: 'left_label' } ), text( 5, { show_icon: 'yes', field_icon: 'fa fa-user', icon_position: 'left_label' } ), form()[ 1 ] ];
        const into = moveField( fields, { container: TOP, index: 0 }, { container: columnContainer( 2, 'column-1' ), index: 0 } );
        const inner = getList( into, columnContainer( 2, 'column-1' ) )[ 0 ];

        expect( inner ).toEqual( text( 1 ) );
        expect( inner.name ).toBe( 'text_1' );

        const withIcon = moveField( fields, { container: TOP, index: 1 }, { container: columnContainer( 2, 'column-1' ), index: 0 } );

        expect( getList( withIcon, columnContainer( 2, 'column-1' ) )[ 0 ] ).toEqual( fields[ 1 ] );

        const out = moveField( form(), { container: columnContainer( 2, 'column-1' ), index: 0 }, { container: TOP, index: 0 } );

        expect( out[ 0 ] ).toEqual( text( 21, { show_icon: 'no', field_icon: '', icon_position: 'left_label' } ) );

        const across = moveField( form(), { container: columnContainer( 2, 'column-2' ), index: 0 }, { container: columnContainer( 2, 'column-1' ), index: 0 } );

        expect( getList( across, columnContainer( 2, 'column-1' ) )[ 0 ] ).toEqual( text( 22 ) );
    } );

    it( 'moves a column field out to the stage and between cells', () => {
        const fields = form();
        const out = moveField( fields, { container: columnContainer( 2, 'column-2' ), index: 0 }, { container: TOP, index: 0 } );

        expect( ids( out ) ).toEqual( [ 22, 1, 2, 3, 4 ] );
        expect( ids( getList( out, columnContainer( 2, 'column-2' ) ) ) ).toEqual( [ 23 ] );

        const across = moveField( fields, { container: columnContainer( 2, 'column-2' ), index: 1 }, { container: columnContainer( 2, 'column-1' ), index: 0 } );

        expect( ids( getList( across, columnContainer( 2, 'column-1' ) ) ) ).toEqual( [ 23, 21 ] );
        expect( ids( getList( across, columnContainer( 2, 'column-2' ) ) ) ).toEqual( [ 22 ] );
    } );

    it( 'reorders inside a repeat field', () => {
        const moved = moveField( form(), { container: repeatContainer( 3 ), index: 0 }, { container: repeatContainer( 3 ), index: 2 } );

        expect( ids( getList( moved, repeatContainer( 3 ) ) ) ).toEqual( [ 32, 31 ] );
    } );

    it( 'creates a missing cell list when moving into it', () => {
        const moved = moveField( form(), { container: TOP, index: 3 }, { container: columnContainer( 2, 'column-3' ), index: 0 } );

        expect( ids( getList( moved, columnContainer( 2, 'column-3' ) ) ) ).toEqual( [ 4 ] );
    } );
} );

describe( 'dndTree canDrop', () => {
    const palette = ( template ) => ( { kind: 'palette', template } );
    const field = ( template, fieldId, container ) => ( { kind: 'field', template, fieldId, container } );
    const cell = columnContainer( 2, 'column-1' );

    it( 'applies develop\'s palette rules', () => {
        expect( canDrop( palette( 'text_field' ), TOP ) ).toBe( true );
        expect( canDrop( palette( 'column_field' ), TOP ) ).toBe( true );
        expect( canDrop( palette( 'text_field' ), cell ) ).toBe( true );
        expect( canDrop( palette( 'column_field' ), cell ) ).toBe( false );
        expect( canDrop( palette( 'custom_hidden_field' ), cell ) ).toBe( false );
        expect( canDrop( palette( 'step_start' ), cell ) ).toBe( false );
        expect( canDrop( palette( 'email_address' ), repeatContainer( 3 ) ) ).toBe( true );
        expect( canDrop( palette( 'image_upload' ), repeatContainer( 3 ) ) ).toBe( false );
    } );

    it( 'lets canvas fields move between the stage and column cells', () => {
        expect( canDrop( field( 'text_field', 1, TOP ), TOP ) ).toBe( true );
        expect( canDrop( field( 'text_field', 1, TOP ), cell ) ).toBe( true );
        expect( canDrop( field( 'text_field', 21, cell ), TOP ) ).toBe( true );
        expect( canDrop( field( 'text_field', 21, cell ), columnContainer( 2, 'column-2' ) ) ).toBe( true );
        expect( canDrop( field( 'column_field', 2, TOP ), cell ) ).toBe( false );
        expect( canDrop( field( 'column_field', 2, TOP ), columnContainer( 5, 'column-1' ) ) ).toBe( false );
        expect( canDrop( field( 'repeat_field', 3, TOP ), cell ) ).toBe( false );
        expect( canDrop( field( 'step_start', 7, TOP ), cell ) ).toBe( false );
    } );

    it( 'keeps repeat lists closed except for their own fields', () => {
        expect( canDrop( field( 'text_field', 1, TOP ), repeatContainer( 3 ) ) ).toBe( false );
        expect( canDrop( field( 'text_field', 21, cell ), repeatContainer( 3 ) ) ).toBe( false );
        expect( canDrop( field( 'text_field', 31, repeatContainer( 3 ) ), repeatContainer( 3 ) ) ).toBe( true );
        expect( canDrop( field( 'text_field', 31, repeatContainer( 3 ) ), TOP ) ).toBe( false );
        expect( canDrop( field( 'text_field', 31, repeatContainer( 3 ) ), repeatContainer( 8 ) ) ).toBe( false );
    } );
} );
