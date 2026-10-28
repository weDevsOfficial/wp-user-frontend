import {
    getPost,
    getFormFields,
    getPanelSections,
    getFieldSettings,
    getNotifications,
    getSettings,
    getIntegrations,
    getCurrentPanel,
    getEditingFieldId,
    getEditingField,
    getEditingFieldConfig,
    getIndexToInsert,
    getIsDirty,
    getIsProActive,
    getFormType,
    getI18n,
} from './selectors';
import { DEFAULT_STATE } from './reducer';

describe( 'selectors', () => {
    describe( 'basic getters', () => {
        it( 'getPost returns post', () => {
            const state = { ...DEFAULT_STATE, post: { ID: 1, post_title: 'Test' } };
            expect( getPost( state ) ).toEqual( { ID: 1, post_title: 'Test' } );
        } );

        it( 'getFormFields returns formFields', () => {
            const fields = [ { id: 1 } ];
            expect( getFormFields( { ...DEFAULT_STATE, formFields: fields } ) ).toBe( fields );
        } );

        it( 'getCurrentPanel returns currentPanel', () => {
            expect( getCurrentPanel( DEFAULT_STATE ) ).toBe( 'form-fields-v4-1' );
        } );

        it( 'getIsDirty returns isDirty', () => {
            expect( getIsDirty( DEFAULT_STATE ) ).toBe( false );
        } );

        it( 'getIsProActive returns isProActive', () => {
            expect( getIsProActive( { ...DEFAULT_STATE, isProActive: true } ) ).toBe( true );
        } );

        it( 'getFormType returns formType', () => {
            expect( getFormType( { ...DEFAULT_STATE, formType: 'wpuf_forms' } ) ).toBe( 'wpuf_forms' );
        } );

        it( 'getI18n returns i18n', () => {
            const i18n = { save: 'Save' };
            expect( getI18n( { ...DEFAULT_STATE, i18n } ) ).toEqual( i18n );
        } );

        it( 'getIndexToInsert returns indexToInsert', () => {
            expect( getIndexToInsert( { ...DEFAULT_STATE, indexToInsert: 3 } ) ).toBe( 3 );
        } );
    } );

    describe( 'getEditingField', () => {
        it( 'returns null when editingFieldId is 0', () => {
            expect( getEditingField( DEFAULT_STATE ) ).toBeNull();
        } );

        it( 'finds top-level field by id', () => {
            const field = { id: 10, template: 'text_field', label: 'Name' };
            const state = {
                ...DEFAULT_STATE,
                formFields: [ field ],
                editingFieldId: 10,
            };
            expect( getEditingField( state ) ).toEqual( field );
        } );

        it( 'finds field inside column inner_fields (object format)', () => {
            const innerField = { id: 50, template: 'text_field', label: 'Inner' };
            const colField = {
                id: 100,
                template: 'column_field',
                inner_fields: {
                    'column-1': [ innerField ],
                    'column-2': [],
                    'column-3': [],
                },
            };
            const state = {
                ...DEFAULT_STATE,
                formFields: [ colField ],
                editingFieldId: 50,
            };
            expect( getEditingField( state ) ).toEqual( innerField );
        } );

        it( 'finds field inside repeat inner_fields (array format)', () => {
            const innerField = { id: 60, template: 'text_field', label: 'Repeat Inner' };
            const repeatField = {
                id: 200,
                template: 'repeat_field',
                inner_fields: [ innerField ],
            };
            const state = {
                ...DEFAULT_STATE,
                formFields: [ repeatField ],
                editingFieldId: 60,
            };
            expect( getEditingField( state ) ).toEqual( innerField );
        } );

        it( 'returns null when field id not found anywhere', () => {
            const state = {
                ...DEFAULT_STATE,
                formFields: [ { id: 1, template: 'text_field' } ],
                editingFieldId: 999,
            };
            expect( getEditingField( state ) ).toBeNull();
        } );
    } );

    describe( 'getEditingFieldConfig', () => {
        it( 'returns field settings for the editing field template', () => {
            const fieldSettings = {
                text_field: {
                    field_props: { label: 'Text' },
                    settings: [ { name: 'label', type: 'text' } ],
                },
            };
            const state = {
                ...DEFAULT_STATE,
                formFields: [ { id: 1, template: 'text_field' } ],
                editingFieldId: 1,
                fieldSettings,
            };

            const config = getEditingFieldConfig( state );
            expect( config ).toEqual( fieldSettings.text_field );
        } );

        it( 'returns null when no editing field', () => {
            expect( getEditingFieldConfig( DEFAULT_STATE ) ).toBeNull();
        } );

        it( 'returns null when template not in fieldSettings', () => {
            const state = {
                ...DEFAULT_STATE,
                formFields: [ { id: 1, template: 'unknown_type' } ],
                editingFieldId: 1,
                fieldSettings: {},
            };
            expect( getEditingFieldConfig( state ) ).toBeNull();
        } );
    } );
} );
