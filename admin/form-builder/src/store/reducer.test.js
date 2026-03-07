import reducer, { DEFAULT_STATE, generateFieldId } from './reducer';
import {
    SET_FORM_FIELDS,
    ADD_FIELD,
    REMOVE_FIELD,
    MOVE_FIELD,
    CLONE_FIELD,
    UPDATE_FIELD,
    ADD_COLUMN_FIELD,
    REMOVE_COLUMN_FIELD,
    MOVE_COLUMN_FIELD,
    CLONE_COLUMN_FIELD,
    MERGE_COLUMN_FIELDS,
    ADD_REPEAT_FIELD,
    REMOVE_REPEAT_FIELD,
    CLONE_REPEAT_FIELD,
    SET_CURRENT_PANEL,
    SET_EDITING_FIELD,
    OPEN_FIELD_SETTINGS,
    SET_INDEX_TO_INSERT,
    TOGGLE_PANEL_SECTION,
    SET_PANEL_SECTIONS,
    SET_FORM_SETTINGS,
    UPDATE_FORM_SETTING,
    ADD_NOTIFICATION,
    REMOVE_NOTIFICATION,
    CLONE_NOTIFICATION,
    UPDATE_NOTIFICATION,
    UPDATE_NOTIFICATION_PROPERTY,
    UPDATE_INTEGRATION,
    MARK_DIRTY,
    MARK_CLEAN,
} from './constants';

// Helper to create a basic field
function makeField( overrides = {} ) {
    return {
        id: generateFieldId(),
        template: 'text_field',
        name: 'test_field',
        label: 'Test Field',
        required: 'no',
        read_only: '',
        is_meta: 'yes',
        ...overrides,
    };
}

// Helper to create a column field
function makeColumnField( overrides = {} ) {
    return {
        id: generateFieldId(),
        template: 'column_field',
        input_type: 'column_field',
        name: 'column_field',
        inner_fields: {
            'column-1': [],
            'column-2': [],
            'column-3': [],
        },
        ...overrides,
    };
}

// Helper to create a repeat field
function makeRepeatField( overrides = {} ) {
    return {
        id: generateFieldId(),
        template: 'repeat_field',
        name: 'repeat_field',
        inner_fields: [],
        ...overrides,
    };
}

describe( 'reducer', () => {
    it( 'returns default state for unknown action', () => {
        const state = reducer( undefined, { type: 'UNKNOWN' } );
        expect( state ).toEqual( DEFAULT_STATE );
    } );

    // ------- Field CRUD -------

    describe( 'SET_FORM_FIELDS', () => {
        it( 'replaces all form fields', () => {
            const fields = [ makeField(), makeField() ];
            const state = reducer( DEFAULT_STATE, {
                type: SET_FORM_FIELDS,
                formFields: fields,
            } );
            expect( state.formFields ).toEqual( fields );
        } );
    } );

    describe( 'ADD_FIELD', () => {
        it( 'inserts field at specified index', () => {
            const existing = makeField( { id: 1, name: 'first' } );
            const newField = makeField( { id: 2, name: 'second' } );
            const initial = { ...DEFAULT_STATE, formFields: [ existing ] };

            const state = reducer( initial, {
                type: ADD_FIELD,
                field: newField,
                index: 0,
            } );

            expect( state.formFields ).toHaveLength( 2 );
            expect( state.formFields[ 0 ].id ).toBe( 2 );
            expect( state.formFields[ 1 ].id ).toBe( 1 );
            expect( state.isDirty ).toBe( true );
        } );

        it( 'initializes icon defaults on added field', () => {
            const field = makeField( { id: 10 } );
            delete field.show_icon;
            delete field.field_icon;
            delete field.icon_position;

            const state = reducer( DEFAULT_STATE, {
                type: ADD_FIELD,
                field,
                index: 0,
            } );

            expect( state.formFields[ 0 ].show_icon ).toBe( 'no' );
            expect( state.formFields[ 0 ].field_icon ).toBe( '' );
            expect( state.formFields[ 0 ].icon_position ).toBe( 'left_label' );
        } );
    } );

    describe( 'REMOVE_FIELD', () => {
        it( 'removes field at index and resets panel', () => {
            const fields = [ makeField( { id: 1 } ), makeField( { id: 2 } ) ];
            const initial = {
                ...DEFAULT_STATE,
                formFields: fields,
                currentPanel: 'field-options',
                editingFieldId: 1,
            };

            const state = reducer( initial, { type: REMOVE_FIELD, index: 0 } );

            expect( state.formFields ).toHaveLength( 1 );
            expect( state.formFields[ 0 ].id ).toBe( 2 );
            expect( state.currentPanel ).toBe( 'form-fields-v4-1' );
            expect( state.editingFieldId ).toBe( 0 );
            expect( state.isDirty ).toBe( true );
        } );
    } );

    describe( 'MOVE_FIELD', () => {
        it( 'reorders fields from fromIndex to toIndex', () => {
            const fields = [
                makeField( { id: 1 } ),
                makeField( { id: 2 } ),
                makeField( { id: 3 } ),
            ];
            const initial = { ...DEFAULT_STATE, formFields: fields };

            const state = reducer( initial, {
                type: MOVE_FIELD,
                fromIndex: 0,
                toIndex: 2,
            } );

            expect( state.formFields.map( ( f ) => f.id ) ).toEqual( [ 2, 3, 1 ] );
            expect( state.isDirty ).toBe( true );
        } );
    } );

    describe( 'CLONE_FIELD', () => {
        it( 'clones a field and inserts after original', () => {
            const field = makeField( { id: 100, name: 'my_field' } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: CLONE_FIELD,
                fieldId: 100,
                newId: 200,
            } );

            expect( state.formFields ).toHaveLength( 2 );
            expect( state.formFields[ 1 ].id ).toBe( 200 );
            expect( state.formFields[ 1 ].name ).toBe( 'my_field_copy' );
            expect( state.formFields[ 1 ].is_new ).toBe( true );
        } );

        it( 'clones column field with new inner field IDs', () => {
            const innerField = makeField( { id: 50, name: 'inner' } );
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ innerField ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: CLONE_FIELD,
                fieldId: 100,
                newId: 200,
            } );

            const cloned = state.formFields[ 1 ];
            expect( cloned.id ).toBe( 200 );
            expect( cloned.inner_fields[ 'column-1' ] ).toHaveLength( 1 );
            expect( cloned.inner_fields[ 'column-1' ][ 0 ].id ).not.toBe( 50 );
            expect( cloned.inner_fields[ 'column-1' ][ 0 ].name ).toBe( 'inner_copy' );
        } );
    } );

    describe( 'UPDATE_FIELD', () => {
        it( 'updates a top-level field property', () => {
            const field = makeField( { id: 1, label: 'Old' } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'label',
                value: 'New',
            } );

            expect( state.formFields[ 0 ].label ).toBe( 'New' );
            expect( state.isDirty ).toBe( true );
        } );

        it( 'does not update name on existing fields (no is_new flag)', () => {
            const field = makeField( { id: 1, name: 'existing_meta' } );
            delete field.is_new;
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'name',
                value: 'new_meta',
            } );

            expect( state.formFields[ 0 ].name ).toBe( 'existing_meta' );
        } );

        it( 'allows name update on new fields', () => {
            const field = makeField( { id: 1, name: 'old', is_new: true } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'name',
                value: 'new_name',
            } );

            expect( state.formFields[ 0 ].name ).toBe( 'new_name' );
        } );

        it( 'updates field inside column inner_fields', () => {
            const innerField = makeField( { id: 50, label: 'Old' } );
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ innerField ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 50,
                fieldName: 'label',
                value: 'Updated',
            } );

            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ][ 0 ].label ).toBe( 'Updated' );
        } );

        it( 'updates field inside repeat inner_fields', () => {
            const innerField = makeField( { id: 50, label: 'Old' } );
            const repeatField = makeRepeatField( { id: 100 } );
            repeatField.inner_fields = [ innerField ];
            const initial = { ...DEFAULT_STATE, formFields: [ repeatField ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 50,
                fieldName: 'label',
                value: 'Updated',
            } );

            expect( state.formFields[ 0 ].inner_fields[ 0 ].label ).toBe( 'Updated' );
        } );
    } );

    // ------- Mutual Exclusivity -------

    describe( 'mutual exclusivity (read_only / required)', () => {
        it( 'setting read_only clears required', () => {
            const field = makeField( { id: 1, required: 'yes', read_only: '' } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'read_only',
                value: true,
            } );

            expect( state.formFields[ 0 ].read_only ).toBe( true );
            expect( state.formFields[ 0 ].required ).toBe( 'no' );
        } );

        it( 'setting required clears read_only', () => {
            const field = makeField( { id: 1, required: 'no', read_only: true } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'required',
                value: 'yes',
            } );

            expect( state.formFields[ 0 ].required ).toBe( 'yes' );
            expect( state.formFields[ 0 ].read_only ).toBe( '' );
        } );

        it( 'no-op when setting required=no with read_only=true', () => {
            const field = makeField( { id: 1, required: 'yes', read_only: true } );
            const initial = { ...DEFAULT_STATE, formFields: [ field ] };

            const state = reducer( initial, {
                type: UPDATE_FIELD,
                fieldId: 1,
                fieldName: 'required',
                value: 'no',
            } );

            // read_only should remain true since required is being set to 'no'
            expect( state.formFields[ 0 ].required ).toBe( 'no' );
            expect( state.formFields[ 0 ].read_only ).toBe( true );
        } );
    } );

    // ------- Column Field Actions -------

    describe( 'ADD_COLUMN_FIELD', () => {
        it( 'adds a field to a column', () => {
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ makeField( { id: 1 } ) ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };
            const newInner = makeField( { id: 2 } );

            const state = reducer( initial, {
                type: ADD_COLUMN_FIELD,
                columnFieldId: 100,
                column: 'column-1',
                index: 1,
                field: newInner,
            } );

            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ] ).toHaveLength( 2 );
            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ][ 1 ].id ).toBe( 2 );
        } );
    } );

    describe( 'REMOVE_COLUMN_FIELD', () => {
        it( 'removes a field from a column', () => {
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ makeField( { id: 1 } ), makeField( { id: 2 } ) ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: REMOVE_COLUMN_FIELD,
                columnFieldId: 100,
                column: 'column-1',
                index: 0,
            } );

            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ] ).toHaveLength( 1 );
            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ][ 0 ].id ).toBe( 2 );
            expect( state.currentPanel ).toBe( 'form-fields-v4-1' );
        } );
    } );

    describe( 'MOVE_COLUMN_FIELD', () => {
        it( 'moves field within same column', () => {
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [
                makeField( { id: 1 } ),
                makeField( { id: 2 } ),
            ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: MOVE_COLUMN_FIELD,
                columnFieldId: 100,
                fromColumn: 'column-1',
                fromIndex: 0,
                toColumn: 'column-1',
                toIndex: 1,
            } );

            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ][ 0 ].id ).toBe( 2 );
            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ][ 1 ].id ).toBe( 1 );
        } );

        it( 'moves field across columns', () => {
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ makeField( { id: 1 } ) ];
            colField.inner_fields[ 'column-2' ] = [];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: MOVE_COLUMN_FIELD,
                columnFieldId: 100,
                fromColumn: 'column-1',
                fromIndex: 0,
                toColumn: 'column-2',
                toIndex: 0,
            } );

            expect( state.formFields[ 0 ].inner_fields[ 'column-1' ] ).toHaveLength( 0 );
            expect( state.formFields[ 0 ].inner_fields[ 'column-2' ] ).toHaveLength( 1 );
            expect( state.formFields[ 0 ].inner_fields[ 'column-2' ][ 0 ].id ).toBe( 1 );
        } );
    } );

    describe( 'CLONE_COLUMN_FIELD', () => {
        it( 'clones an inner field within a column', () => {
            const inner = makeField( { id: 50, name: 'inner_field' } );
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ inner ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: CLONE_COLUMN_FIELD,
                columnFieldId: 100,
                column: 'column-1',
                innerFieldId: 50,
                newId: 51,
            } );

            const col1 = state.formFields[ 0 ].inner_fields[ 'column-1' ];
            expect( col1 ).toHaveLength( 2 );
            expect( col1[ 1 ].id ).toBe( 51 );
            expect( col1[ 1 ].name ).toBe( 'inner_field_copy' );
            expect( col1[ 1 ].is_new ).toBe( true );
        } );
    } );

    describe( 'MERGE_COLUMN_FIELDS', () => {
        it( 'merges all columns into column-1', () => {
            const colField = makeColumnField( { id: 100 } );
            colField.inner_fields[ 'column-1' ] = [ makeField( { id: 1 } ) ];
            colField.inner_fields[ 'column-2' ] = [ makeField( { id: 2 } ) ];
            colField.inner_fields[ 'column-3' ] = [ makeField( { id: 3 } ) ];
            const initial = { ...DEFAULT_STATE, formFields: [ colField ] };

            const state = reducer( initial, {
                type: MERGE_COLUMN_FIELDS,
                columnFieldId: 100,
                moveTo: 'column-1',
            } );

            const inner = state.formFields[ 0 ].inner_fields;
            expect( inner[ 'column-1' ] ).toHaveLength( 3 );
            expect( inner[ 'column-2' ] ).toHaveLength( 0 );
            expect( inner[ 'column-3' ] ).toHaveLength( 0 );
        } );
    } );

    // ------- Repeat Field Actions -------

    describe( 'ADD_REPEAT_FIELD', () => {
        it( 'adds field to repeat inner_fields array', () => {
            const repeatField = makeRepeatField( { id: 100 } );
            const initial = { ...DEFAULT_STATE, formFields: [ repeatField ] };
            const newInner = makeField( { id: 50 } );

            const state = reducer( initial, {
                type: ADD_REPEAT_FIELD,
                repeatFieldId: 100,
                index: 0,
                field: newInner,
            } );

            expect( Array.isArray( state.formFields[ 0 ].inner_fields ) ).toBe( true );
            expect( state.formFields[ 0 ].inner_fields ).toHaveLength( 1 );
            expect( state.formFields[ 0 ].inner_fields[ 0 ].id ).toBe( 50 );
        } );
    } );

    describe( 'REMOVE_REPEAT_FIELD', () => {
        it( 'removes field from repeat inner_fields', () => {
            const repeatField = makeRepeatField( { id: 100 } );
            repeatField.inner_fields = [ makeField( { id: 1 } ), makeField( { id: 2 } ) ];
            const initial = { ...DEFAULT_STATE, formFields: [ repeatField ] };

            const state = reducer( initial, {
                type: REMOVE_REPEAT_FIELD,
                repeatFieldId: 100,
                index: 0,
            } );

            expect( state.formFields[ 0 ].inner_fields ).toHaveLength( 1 );
            expect( state.formFields[ 0 ].inner_fields[ 0 ].id ).toBe( 2 );
            expect( state.currentPanel ).toBe( 'form-fields-v4-1' );
        } );
    } );

    describe( 'CLONE_REPEAT_FIELD', () => {
        it( 'clones an inner field in repeat', () => {
            const inner = makeField( { id: 50, name: 'rep_inner' } );
            const repeatField = makeRepeatField( { id: 100 } );
            repeatField.inner_fields = [ inner ];
            const initial = { ...DEFAULT_STATE, formFields: [ repeatField ] };

            const state = reducer( initial, {
                type: CLONE_REPEAT_FIELD,
                repeatFieldId: 100,
                index: 0,
                newId: 51,
            } );

            expect( state.formFields[ 0 ].inner_fields ).toHaveLength( 2 );
            expect( state.formFields[ 0 ].inner_fields[ 1 ].id ).toBe( 51 );
            expect( state.formFields[ 0 ].inner_fields[ 1 ].name ).toBe( 'rep_inner_copy' );
        } );
    } );

    // ------- Panel / UI -------

    describe( 'SET_CURRENT_PANEL', () => {
        it( 'switches to field-options and auto-selects first field', () => {
            const field = makeField( { id: 1 } );
            const initial = {
                ...DEFAULT_STATE,
                formFields: [ field ],
                currentPanel: 'form-fields-v4-1',
            };

            const state = reducer( initial, {
                type: SET_CURRENT_PANEL,
                panel: 'field-options',
            } );

            expect( state.currentPanel ).toBe( 'field-options' );
            expect( state.editingFieldId ).toBe( 1 );
        } );

        it( 'resets editingFieldId when switching to form-fields-v4-1', () => {
            const initial = {
                ...DEFAULT_STATE,
                currentPanel: 'field-options',
                editingFieldId: 5,
            };

            const state = reducer( initial, {
                type: SET_CURRENT_PANEL,
                panel: 'form-fields-v4-1',
            } );

            expect( state.editingFieldId ).toBe( 0 );
        } );
    } );

    describe( 'SET_EDITING_FIELD', () => {
        it( 'sets the editing field id', () => {
            const state = reducer( DEFAULT_STATE, {
                type: SET_EDITING_FIELD,
                fieldId: 42,
            } );
            expect( state.editingFieldId ).toBe( 42 );
        } );
    } );

    describe( 'OPEN_FIELD_SETTINGS', () => {
        it( 'opens field-options panel with the given field', () => {
            const state = reducer( DEFAULT_STATE, {
                type: OPEN_FIELD_SETTINGS,
                fieldId: 10,
            } );

            expect( state.currentPanel ).toBe( 'field-options' );
            expect( state.editingFieldId ).toBe( 10 );
        } );

        it( 'returns same state if already editing same field', () => {
            const initial = {
                ...DEFAULT_STATE,
                currentPanel: 'field-options',
                editingFieldId: 10,
            };

            const state = reducer( initial, {
                type: OPEN_FIELD_SETTINGS,
                fieldId: 10,
            } );

            expect( state ).toBe( initial );
        } );
    } );

    describe( 'SET_INDEX_TO_INSERT', () => {
        it( 'updates indexToInsert', () => {
            const state = reducer( DEFAULT_STATE, {
                type: SET_INDEX_TO_INSERT,
                index: 5,
            } );
            expect( state.indexToInsert ).toBe( 5 );
        } );
    } );

    describe( 'TOGGLE_PANEL_SECTION', () => {
        it( 'toggles section show property', () => {
            const initial = {
                ...DEFAULT_STATE,
                panelSections: [
                    { id: 'a', show: true },
                    { id: 'b', show: false },
                ],
            };

            const state = reducer( initial, {
                type: TOGGLE_PANEL_SECTION,
                index: 0,
            } );

            expect( state.panelSections[ 0 ].show ).toBe( false );
            expect( state.panelSections[ 1 ].show ).toBe( false );
        } );
    } );

    describe( 'SET_PANEL_SECTIONS', () => {
        it( 'replaces panel sections', () => {
            const sections = [ { id: 'new', show: true } ];
            const state = reducer( DEFAULT_STATE, {
                type: SET_PANEL_SECTIONS,
                sections,
            } );
            expect( state.panelSections ).toEqual( sections );
        } );
    } );

    // ------- Settings -------

    describe( 'SET_FORM_SETTINGS', () => {
        it( 'replaces all settings', () => {
            const settings = { redirect_to: 'same', message: 'Done' };
            const state = reducer( DEFAULT_STATE, {
                type: SET_FORM_SETTINGS,
                settings,
            } );
            expect( state.settings ).toEqual( settings );
        } );
    } );

    describe( 'UPDATE_FORM_SETTING', () => {
        it( 'updates a single setting key', () => {
            const initial = { ...DEFAULT_STATE, settings: { redirect_to: 'same' } };
            const state = reducer( initial, {
                type: UPDATE_FORM_SETTING,
                key: 'redirect_to',
                value: 'page',
            } );
            expect( state.settings.redirect_to ).toBe( 'page' );
            expect( state.isDirty ).toBe( true );
        } );
    } );

    // ------- Notifications -------

    describe( 'ADD_NOTIFICATION', () => {
        it( 'appends a notification', () => {
            const notif = { type: 'email', to: 'admin', active: 'true' };
            const state = reducer( DEFAULT_STATE, {
                type: ADD_NOTIFICATION,
                notification: notif,
            } );
            expect( state.notifications ).toHaveLength( 1 );
            expect( state.notifications[ 0 ] ).toEqual( notif );
        } );
    } );

    describe( 'REMOVE_NOTIFICATION', () => {
        it( 'removes notification at index', () => {
            const initial = {
                ...DEFAULT_STATE,
                notifications: [ { id: 1 }, { id: 2 } ],
            };
            const state = reducer( initial, {
                type: REMOVE_NOTIFICATION,
                index: 0,
            } );
            expect( state.notifications ).toHaveLength( 1 );
            expect( state.notifications[ 0 ].id ).toBe( 2 );
        } );
    } );

    describe( 'CLONE_NOTIFICATION', () => {
        it( 'clones notification at index', () => {
            const initial = {
                ...DEFAULT_STATE,
                notifications: [ { to: 'admin', subject: 'Test' } ],
            };
            const state = reducer( initial, {
                type: CLONE_NOTIFICATION,
                index: 0,
            } );
            expect( state.notifications ).toHaveLength( 2 );
            expect( state.notifications[ 1 ].subject ).toBe( 'Test' );
            // Ensure deep clone
            state.notifications[ 1 ].subject = 'Changed';
            expect( state.notifications[ 0 ].subject ).toBe( 'Test' );
        } );
    } );

    describe( 'UPDATE_NOTIFICATION', () => {
        it( 'replaces notification at index', () => {
            const initial = {
                ...DEFAULT_STATE,
                notifications: [ { to: 'admin' } ],
            };
            const state = reducer( initial, {
                type: UPDATE_NOTIFICATION,
                index: 0,
                value: { to: 'user', subject: 'New' },
            } );
            expect( state.notifications[ 0 ].to ).toBe( 'user' );
        } );
    } );

    describe( 'UPDATE_NOTIFICATION_PROPERTY', () => {
        it( 'updates a single property on a notification', () => {
            const initial = {
                ...DEFAULT_STATE,
                notifications: [ { to: 'admin', active: 'true' } ],
            };
            const state = reducer( initial, {
                type: UPDATE_NOTIFICATION_PROPERTY,
                index: 0,
                property: 'active',
                value: 'false',
            } );
            expect( state.notifications[ 0 ].active ).toBe( 'false' );
            expect( state.notifications[ 0 ].to ).toBe( 'admin' );
        } );
    } );

    // ------- Integrations -------

    describe( 'UPDATE_INTEGRATION', () => {
        it( 'updates integration at index', () => {
            const state = reducer( DEFAULT_STATE, {
                type: UPDATE_INTEGRATION,
                index: 'mailchimp_0',
                value: { enabled: true, list_id: 'abc' },
            } );
            expect( state.integrations.mailchimp_0 ).toEqual( {
                enabled: true,
                list_id: 'abc',
            } );
        } );
    } );

    // ------- Dirty State -------

    describe( 'MARK_DIRTY / MARK_CLEAN', () => {
        it( 'MARK_DIRTY sets isDirty to true', () => {
            const state = reducer( DEFAULT_STATE, { type: MARK_DIRTY } );
            expect( state.isDirty ).toBe( true );
        } );

        it( 'MARK_CLEAN sets isDirty to false', () => {
            const initial = { ...DEFAULT_STATE, isDirty: true };
            const state = reducer( initial, { type: MARK_CLEAN } );
            expect( state.isDirty ).toBe( false );
        } );
    } );

    // ------- ID Generation -------

    describe( 'generateFieldId', () => {
        it( 'produces numeric IDs', () => {
            const id = generateFieldId();
            expect( typeof id ).toBe( 'number' );
            expect( id ).toBeGreaterThanOrEqual( 999999 );
            expect( id ).toBeLessThanOrEqual( 9999999999 );
        } );
    } );
} );
