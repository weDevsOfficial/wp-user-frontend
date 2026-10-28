import { buildCondArrays, condDependencies, condRowsFromStored, conditionInputType, conditionFieldType, getFieldOptions, getOperatorsForType, readPath, writePath } from './conditionalUtils';

/**
 * Condition values a rule stores, matching the Vue builder (task 1.5, B11).
 */
describe( 'conditional logic shape', () => {
    test( 'input type comes from the field, else from the template', () => {
        expect( conditionInputType( { template: 'dropdown_field', input_type: 'select' } ) ).toBe( 'select' );
        expect( conditionInputType( { template: 'multiple_select', input_type: 'multiselect' } ) ).toBe( 'multiselect' );
        expect( conditionInputType( { template: 'radio_field' } ) ).toBe( 'radio' );
        expect( conditionInputType( undefined ) ).toBe( '' );
    } );

    test( 'field type: rich textarea, then field type, then input type', () => {
        expect( conditionFieldType( { template: 'textarea_field', input_type: 'textarea', rich: 'teeny' } ) ).toBe( 'teeny' );
        expect( conditionFieldType( { template: 'taxonomy', input_type: 'taxonomy', type: 'select' } ) ).toBe( 'select' );
        expect( conditionFieldType( { template: 'dropdown_field', input_type: 'select' } ) ).toBe( 'select' );
    } );

    test( 'options come from the field, or from the terms of a hierarchical taxonomy', () => {
        expect( getFieldOptions( { template: 'radio_field', options: { red: 'Red', blue: 'Blue' } } ) ).toEqual( [
            { value: 'red', label: 'Red', raw: 'red' },
            { value: 'blue', label: 'Blue', raw: 'blue' },
        ] );

        const postTypes = { post: { category: { hierarchical: true, terms: [ { term_id: 3, name: 'News' } ] } } };
        expect( getFieldOptions( { template: 'taxonomy', name: 'category' }, postTypes ) ).toEqual( [ { value: '3', label: 'News', raw: 3 } ] );
        expect( getFieldOptions( { template: 'taxonomy', name: 'post_tag' }, postTypes ) ).toEqual( [] );
    } );

    test( 'operators: none for a null input type, "others" for an unknown one', () => {
        expect( getOperatorsForType( null ) ).toEqual( [] );
        expect( getOperatorsForType( undefined )[ 0 ].value ).toBe( '!=empty' );
        expect( getOperatorsForType( 'numeric_text' ).map( ( op ) => op.value ) ).toContain( 'greater' );
    } );
} );

describe( 'field conditional logic rows (develop field-conditional-logic, 4.4d)', () => {
    const fields = [
        { template: 'text_field', name: 'city', label: 'City', input_type: 'text' },
        { template: 'radio_field', name: 'color', label: 'Color', input_type: 'radio', options: { red: 'Red' } },
        { template: 'text_field', name: 'nolabel', label: '', input_type: 'text' },
        { template: 'taxonomy', name: 'category', label: 'Category', input_type: 'taxonomy' },
        { template: 'column_field', name: 'col', inner_fields: { 'column-1': [ { template: 'text_field', name: 'inner', label: 'Inner', input_type: 'text' } ] } },
        { template: 'repeat_field', name: 'rep', label: 'Rep', inner_fields: [ { template: 'text_field', name: 'rep_in', label: 'In', input_type: 'text' } ] },
        { template: 'image_upload', name: 'img', label: 'Image', input_type: 'image_upload' },
    ];
    const supported = [ 'text_field', 'radio_field', 'category' ];

    test( 'dependencies: supported, labelled, column cells, taxonomies by name; not repeat inner fields, not the edited field', () => {
        expect( condDependencies( fields, supported, 'city' ).map( ( f ) => f.name ) ).toEqual( [ 'color', 'category', 'inner' ] );
    } );

    test( 'rows from storage keep rows with a field and an operator, else one blank row', () => {
        const stored = { condition_status: 'yes', cond_field: [ 'color', '', 'city' ], cond_operator: [ '=', '', '==contains' ], cond_option: [ 'red', '', 'Dh' ], input_type: [ 'radio', null, 'text' ], field_type: [ 'radio', null, 'text' ], option_title: [ 'Red', null, '' ] };

        expect( condRowsFromStored( stored ).map( ( r ) => r.name ) ).toEqual( [ 'color', 'city' ] );
        expect( condRowsFromStored( { condition_status: 'no', cond_field: [], cond_operator: [ '=' ], cond_option: [ '- Select -' ] } ) ).toEqual( [ { name: '', operator: '', option: '' } ] );
        // Saved before input_type existed: the top-level field's input type.
        expect( condRowsFromStored( { cond_field: [ 'city' ], cond_operator: [ '=' ], cond_option: [ 'x' ] }, fields )[ 0 ].input_type ).toBe( 'text' );
    } );

    test( 'arrays rebuilt from rows: other keys kept, empty-value operators store \'\', missing keys stay undefined (null in JSON)', () => {
        const cond = { condition_status: 'yes', cond_logic: 'any', cond_field: [ 'old' ] };
        const next = buildCondArrays( cond, [
            { name: 'city', operator: '!=empty', option: 'kept on screen', option_title: '', input_type: 'text', field_type: 'text' },
            { name: '', operator: '', option: '' },
        ] );

        expect( next.condition_status ).toBe( 'yes' );
        expect( next.cond_logic ).toBe( 'any' );
        expect( next.cond_field ).toEqual( [ 'city', '' ] );
        expect( next.cond_option ).toEqual( [ '', '' ] );
        expect( JSON.parse( JSON.stringify( next ) ).input_type ).toEqual( [ 'text', null ] );
        expect( buildCondArrays( undefined, [] ) ).toMatchObject( { condition_status: 'no', cond_logic: 'all' } );
    } );
} );

/**
 * Integration conditions live at a dot path of the form settings (4.5b).
 */
describe( 'integration condition paths', () => {
    const settings = { integrations: { mailchimp: { wpuf_cond: { condition_status: 'yes' } }, other: { keep: 1 } }, role: 'subscriber' };

    test( 'reads a nested value, undefined when a level is missing', () => {
        expect( readPath( settings, 'integrations.mailchimp.wpuf_cond' ) ).toEqual( { condition_status: 'yes' } );
        expect( readPath( settings, 'integrations.missing.wpuf_cond' ) ).toBeUndefined();
        expect( readPath( {}, 'integrations.mailchimp.wpuf_cond' ) ).toBeUndefined();
    } );

    test( 'writes the top-level key with siblings kept and nothing mutated', () => {
        const [ key, value ] = writePath( settings, 'integrations.mailchimp.wpuf_cond', { condition_status: 'no' } );
        expect( key ).toBe( 'integrations' );
        expect( value ).toEqual( { mailchimp: { wpuf_cond: { condition_status: 'no' } }, other: { keep: 1 } } );
        expect( settings.integrations.mailchimp.wpuf_cond ).toEqual( { condition_status: 'yes' } );
    } );

    test( 'creates missing levels (stored [] from PHP included)', () => {
        expect( writePath( { integrations: [] }, 'integrations.mailchimp.wpuf_cond', 1 ) ).toEqual( [ 'integrations', { mailchimp: { wpuf_cond: 1 } } ] );
        expect( writePath( {}, 'role', 'editor' ) ).toEqual( [ 'role', 'editor' ] );
    } );
} );
