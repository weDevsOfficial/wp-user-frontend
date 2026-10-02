import reducer, { DEFAULT_STATE, generateFieldId } from './reducer';
import { ADD_FIELD, UPDATE_FIELD, ADD_COLUMN_FIELD, ADD_REPEAT_FIELD } from './constants';

describe( 'serialization format', () => {
    describe( 'field property values use string format', () => {
        it( 'required uses "yes"/"no" strings', () => {
            const field = {
                id: 1,
                template: 'text_field',
                name: 'test',
                required: 'yes',
                read_only: '',
            };
            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field,
                index: 0,
            } );

            expect( typeof state.formFields[ 0 ].required ).toBe( 'string' );
            expect( state.formFields[ 0 ].required ).toBe( 'yes' );
        } );

        it( 'setting required to "no" keeps string format', () => {
            const field = {
                id: 1,
                template: 'text_field',
                name: 'test',
                required: 'yes',
                read_only: '',
                is_new: true,
            };
            const initial = {
                ...DEFAULT_STATE,
                formFields: [ field ],
            };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'required',
                value: 'no',
            } );

            expect( state.formFields[ 0 ].required ).toBe( 'no' );
            expect( typeof state.formFields[ 0 ].required ).toBe( 'string' );
        } );

        it( 'is_meta uses "yes"/"no" strings', () => {
            const field = {
                id: 1,
                template: 'text_field',
                name: 'test',
                is_meta: 'yes',
                required: 'no',
                read_only: '',
            };
            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field,
                index: 0,
            } );

            expect( typeof state.formFields[ 0 ].is_meta ).toBe( 'string' );
        } );
    } );

    describe( 'wpuf_cond structure preserved', () => {
        it( 'stores conditional logic in correct format', () => {
            const field = {
                id: 1,
                template: 'text_field',
                name: 'test',
                required: 'no',
                read_only: '',
                is_new: true,
                wpuf_cond: {
                    condition_status: 'yes',
                    cond_logic: 'any',
                    conditions: [
                        { name: 'dropdown_field', operator: '=', option: 'option_1' },
                    ],
                },
            };

            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field,
                index: 0,
            } );

            const cond = state.formFields[ 0 ].wpuf_cond;
            expect( cond ).toBeDefined();
            expect( cond.condition_status ).toBe( 'yes' );
            expect( cond.cond_logic ).toBe( 'any' );
            expect( cond.conditions ).toHaveLength( 1 );
            expect( cond.conditions[ 0 ] ).toEqual( {
                name: 'dropdown_field',
                operator: '=',
                option: 'option_1',
            } );
        } );

        it( 'preserves wpuf_cond when updating other field properties', () => {
            const field = {
                id: 1,
                template: 'text_field',
                name: 'test',
                required: 'no',
                read_only: '',
                is_new: true,
                wpuf_cond: {
                    condition_status: 'yes',
                    cond_logic: 'all',
                    conditions: [
                        { name: 'field_a', operator: '!=', option: 'val' },
                    ],
                },
            };
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'label',
                value: 'New Label',
            } );

            expect( state.formFields[ 0 ].wpuf_cond ).toEqual( field.wpuf_cond );
        } );
    } );

    describe( 'column inner_fields uses object format', () => {
        it( 'inner_fields keys are column-1, column-2, column-3', () => {
            const colField = {
                id: 100,
                template: 'column_field',
                input_type: 'column_field',
                name: 'col',
                inner_fields: {
                    'column-1': [],
                    'column-2': [],
                    'column-3': [],
                },
                required: 'no',
                read_only: '',
            };
            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field: colField,
                index: 0,
            } );

            const inner = state.formFields[ 0 ].inner_fields;
            expect( typeof inner ).toBe( 'object' );
            expect( Array.isArray( inner ) ).toBe( false );
            expect( inner ).toHaveProperty( 'column-1' );
            expect( inner ).toHaveProperty( 'column-2' );
            expect( inner ).toHaveProperty( 'column-3' );
        } );

        it( 'adding to column preserves object format', () => {
            const colField = {
                id: 100,
                template: 'column_field',
                name: 'col',
                inner_fields: {
                    'column-1': [],
                    'column-2': [],
                    'column-3': [],
                },
                required: 'no',
                read_only: '',
            };
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: ADD_COLUMN_FIELD,
                columnFieldId: 100,
                column: 'column-1',
                index: 0,
                field: { id: 50, template: 'text_field', name: 'inner', required: 'no', read_only: '' },
            } );

            expect( Array.isArray( state.formFields[ 0 ].inner_fields ) ).toBe( false );
            expect( typeof state.formFields[ 0 ].inner_fields ).toBe( 'object' );
        } );
    } );

    describe( 'repeat inner_fields uses array format', () => {
        it( 'inner_fields is an array', () => {
            const repeatField = {
                id: 200,
                template: 'repeat_field',
                name: 'repeat',
                inner_fields: [],
                required: 'no',
                read_only: '',
            };
            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field: repeatField,
                index: 0,
            } );

            expect( Array.isArray( state.formFields[ 0 ].inner_fields ) ).toBe( true );
        } );

        it( 'adding to repeat preserves array format', () => {
            const repeatField = {
                id: 200,
                template: 'repeat_field',
                name: 'repeat',
                inner_fields: [],
                required: 'no',
                read_only: '',
            };
            const initial = { ...DEFAULT_STATE, formFields: [ repeatField ] };

            const state = reducer( initial, {
                type: ADD_REPEAT_FIELD,
                repeatFieldId: 200,
                index: 0,
                field: { id: 50, template: 'text_field', name: 'inner', required: 'no', read_only: '' },
            } );

            expect( Array.isArray( state.formFields[ 0 ].inner_fields ) ).toBe( true );
        } );
    } );

    describe( 'ID generation', () => {
        it( 'generates numeric IDs', () => {
            for ( let i = 0; i < 20; i++ ) {
                const id = generateFieldId();
                expect( typeof id ).toBe( 'number' );
                expect( Number.isInteger( id ) ).toBe( true );
            }
        } );

        it( 'generates IDs in expected range', () => {
            for ( let i = 0; i < 50; i++ ) {
                const id = generateFieldId();
                expect( id ).toBeGreaterThanOrEqual( 999999 );
                expect( id ).toBeLessThanOrEqual( 9999999999 );
            }
        } );
    } );
} );
