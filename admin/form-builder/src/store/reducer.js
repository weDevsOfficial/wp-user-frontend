import {
    SET_POST,
    INITIALIZE_STATE,
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
    SET_PANEL_SECTION_FIELDS,
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

export const DEFAULT_STATE = {
    post: {},
    formFields: [],
    panelSections: [],
    fieldSettings: {},
    notifications: [],
    settings: {},
    integrations: {},
    currentPanel: 'form-fields-v4-1',
    editingFieldId: 0,
    indexToInsert: 0,
    isDirty: false,
    isProActive: false,
    formType: '',
    showCustomFieldTooltip: true,
    i18n: {},
};

/**
 * Deep clone a value using JSON round-trip.
 *
 * @param {*} value
 * @return {*}
 */
function deepClone( value ) {
    return JSON.parse( JSON.stringify( value ) );
}

/**
 * Generate a random numeric field ID.
 *
 * @return {number}
 */
export function generateFieldId() {
    return Math.floor( Math.random() * ( 9999999999 - 999999 + 1 ) ) + 999999;
}

/**
 * Apply mutual exclusivity between read_only and required.
 *
 * @param {Object} field
 * @param {string} fieldName
 * @param {*}      value
 * @return {Object} Updated field
 */
function applyMutualExclusivity( field, fieldName, value ) {
    const updated = { ...field };

    if ( fieldName === 'read_only' && ( value === true || value === 'yes' ) ) {
        if ( updated.required === 'yes' ) {
            updated.required = 'no';
        }
    }

    if ( fieldName === 'required' && value === 'yes' ) {
        if ( updated.read_only === true || updated.read_only === 'yes' ) {
            updated.read_only = '';
        }
    }

    updated[ fieldName ] = value;
    return updated;
}

/**
 * Update a field by ID within the formFields array.
 * Searches top-level, column inner_fields (object), and repeat inner_fields (array).
 *
 * @param {Array}  fields
 * @param {number} fieldId
 * @param {string} fieldName
 * @param {*}      value
 * @return {Array} New fields array
 */
function updateFieldInArray( fields, fieldId, fieldName, value ) {
    return fields.map( ( field ) => {
        // Top-level match
        if ( parseInt( field.id ) === parseInt( fieldId ) ) {
            // Don't modify existing meta key unless field is new
            if ( fieldName === 'name' && ! field.is_new ) {
                return field;
            }
            return applyMutualExclusivity( field, fieldName, value );
        }

        // Column field inner_fields (object: { 'column-1': [], 'column-2': [], 'column-3': [] })
        if ( field.template === 'column_field' && field.inner_fields ) {
            let changed = false;
            const newInnerFields = {};

            for ( const col in field.inner_fields ) {
                if ( ! field.inner_fields.hasOwnProperty( col ) ) {
                    continue;
                }

                newInnerFields[ col ] = field.inner_fields[ col ].map( ( innerField ) => {
                    if ( parseInt( innerField.id ) === parseInt( fieldId ) ) {
                        if ( fieldName === 'name' && ! innerField.is_new ) {
                            return innerField;
                        }
                        changed = true;
                        return applyMutualExclusivity( innerField, fieldName, value );
                    }
                    return innerField;
                } );
            }

            if ( changed ) {
                return { ...field, inner_fields: newInnerFields };
            }
        }

        // Repeat field inner_fields (array)
        if ( field.template === 'repeat_field' && Array.isArray( field.inner_fields ) ) {
            let changed = false;
            const newInnerFields = field.inner_fields.map( ( innerField ) => {
                if ( parseInt( innerField.id ) === parseInt( fieldId ) ) {
                    if ( fieldName === 'name' && ! innerField.is_new ) {
                        return innerField;
                    }
                    changed = true;
                    return applyMutualExclusivity( innerField, fieldName, value );
                }
                return innerField;
            } );

            if ( changed ) {
                return { ...field, inner_fields: newInnerFields };
            }
        }

        return field;
    } );
}

export default function reducer( state = DEFAULT_STATE, action ) {
    switch ( action.type ) {
        case SET_POST:
            return {
                ...state,
                post: action.post,
            };

        case INITIALIZE_STATE:
            return {
                ...state,
                ...action.payload,
            };

        case SET_FORM_FIELDS:
            return {
                ...state,
                formFields: action.formFields,
            };

        case ADD_FIELD: {
            const newFields = [ ...state.formFields ];
            const field = {
                ...action.field,
                show_icon: action.field.show_icon || 'no',
                field_icon: action.field.field_icon || '',
                icon_position: action.field.icon_position || 'left_label',
            };
            newFields.splice( action.index, 0, field );
            return {
                ...state,
                formFields: newFields,
                isDirty: true,
            };
        }

        case REMOVE_FIELD: {
            const newFields = [ ...state.formFields ];
            newFields.splice( action.index, 1 );
            return {
                ...state,
                formFields: newFields,
                currentPanel: 'form-fields-v4-1',
                editingFieldId: 0,
                isDirty: true,
            };
        }

        case MOVE_FIELD: {
            const newFields = [ ...state.formFields ];
            const [ moved ] = newFields.splice( action.fromIndex, 1 );
            newFields.splice( action.toIndex, 0, moved );
            return {
                ...state,
                formFields: newFields,
                isDirty: true,
            };
        }

        case CLONE_FIELD: {
            const sourceField = state.formFields.find(
                ( f ) => parseInt( f.id ) === parseInt( action.fieldId )
            );
            if ( ! sourceField ) {
                return state;
            }
            const clone = deepClone( sourceField );
            clone.id = action.newId;
            clone.name = clone.name + '_copy';
            clone.is_new = true;

            // Clone inner fields for column fields with new IDs
            if ( clone.template === 'column_field' && clone.inner_fields ) {
                for ( const col in clone.inner_fields ) {
                    if ( clone.inner_fields.hasOwnProperty( col ) ) {
                        clone.inner_fields[ col ] = clone.inner_fields[ col ].map( ( f ) => ( {
                            ...f,
                            id: generateFieldId(),
                            name: f.name + '_copy',
                            is_new: true,
                        } ) );
                    }
                }
            }

            const insertIndex = state.formFields.findIndex(
                ( f ) => parseInt( f.id ) === parseInt( action.fieldId )
            ) + 1;

            const newFields = [ ...state.formFields ];
            newFields.splice( insertIndex, 0, clone );
            return {
                ...state,
                formFields: newFields,
                isDirty: true,
            };
        }

        case UPDATE_FIELD:
            return {
                ...state,
                formFields: updateFieldInArray(
                    state.formFields,
                    action.fieldId,
                    action.fieldName,
                    action.value
                ),
                isDirty: true,
            };

        // Column field actions
        case ADD_COLUMN_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.columnFieldId ) {
                    return field;
                }
                const innerFields = { ...field.inner_fields };
                if ( ! innerFields[ action.column ] ) {
                    innerFields[ action.column ] = [ action.field ];
                } else {
                    innerFields[ action.column ] = [ ...innerFields[ action.column ] ];
                    innerFields[ action.column ].splice( action.index, 0, action.field );
                }
                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        case REMOVE_COLUMN_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.columnFieldId ) {
                    return field;
                }
                const innerFields = { ...field.inner_fields };
                innerFields[ action.column ] = [ ...innerFields[ action.column ] ];
                innerFields[ action.column ].splice( action.index, 1 );
                return { ...field, inner_fields: innerFields };
            } );
            return {
                ...state,
                formFields: newFields,
                currentPanel: 'form-fields-v4-1',
                isDirty: true,
            };
        }

        case MOVE_COLUMN_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.columnFieldId ) {
                    return field;
                }
                const innerFields = {};
                for ( const col in field.inner_fields ) {
                    innerFields[ col ] = [ ...field.inner_fields[ col ] ];
                }

                const movedField = innerFields[ action.fromColumn ][ action.fromIndex ];

                if ( action.fromColumn !== action.toColumn ) {
                    innerFields[ action.toColumn ].splice( action.toIndex, 0, movedField );
                    innerFields[ action.fromColumn ].splice( action.fromIndex, 1 );
                } else {
                    innerFields[ action.toColumn ].splice( action.fromIndex, 1 );
                    innerFields[ action.toColumn ].splice( action.toIndex, 0, movedField );
                }

                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        case CLONE_COLUMN_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.columnFieldId ) {
                    return field;
                }
                const innerFields = { ...field.inner_fields };
                innerFields[ action.column ] = [ ...innerFields[ action.column ] ];

                const source = innerFields[ action.column ].find(
                    ( f ) => parseInt( f.id ) === parseInt( action.innerFieldId )
                );
                if ( ! source ) {
                    return field;
                }

                const clone = deepClone( source );
                clone.id = action.newId;
                clone.name = clone.name + '_copy';
                clone.is_new = true;

                const sourceIndex = innerFields[ action.column ].findIndex(
                    ( f ) => parseInt( f.id ) === parseInt( action.innerFieldId )
                );
                innerFields[ action.column ].splice( sourceIndex + 1, 0, clone );

                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        case MERGE_COLUMN_FIELDS: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.columnFieldId ) {
                    return field;
                }

                const innerFields = {};
                for ( const col in field.inner_fields ) {
                    innerFields[ col ] = [ ...field.inner_fields[ col ] ];
                }

                const mergedFields = [];
                const columns = Object.keys( innerFields );

                columns.forEach( ( column ) => {
                    if ( action.moveTo === 'column-1' ) {
                        mergedFields.push( ...innerFields[ column ] );
                        innerFields[ column ] = [];
                    } else if ( action.moveTo === 'column-2' ) {
                        if ( column === 'column-2' || column === 'column-3' ) {
                            mergedFields.push( ...innerFields[ column ] );
                            innerFields[ column ] = [];
                        }
                    }
                } );

                innerFields[ action.moveTo ] = [
                    ...mergedFields,
                    ...innerFields[ action.moveTo ],
                ];

                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        // Repeat field actions
        case ADD_REPEAT_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.repeatFieldId ) {
                    return field;
                }
                const innerFields = Array.isArray( field.inner_fields )
                    ? [ ...field.inner_fields ]
                    : [];
                innerFields.splice( action.index, 0, action.field );
                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        case REMOVE_REPEAT_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.repeatFieldId ) {
                    return field;
                }
                const innerFields = [ ...field.inner_fields ];
                innerFields.splice( action.index, 1 );
                return { ...field, inner_fields: innerFields };
            } );
            return {
                ...state,
                formFields: newFields,
                currentPanel: 'form-fields-v4-1',
                isDirty: true,
            };
        }

        case CLONE_REPEAT_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( field.id !== action.repeatFieldId ) {
                    return field;
                }
                const innerFields = [ ...field.inner_fields ];
                const source = innerFields[ action.index ];
                if ( ! source ) {
                    return field;
                }
                const clone = deepClone( source );
                clone.id = action.newId;
                clone.name = clone.name + '_copy';
                clone.is_new = true;
                innerFields.splice( action.index + 1, 0, clone );
                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        // Panel / UI
        case SET_CURRENT_PANEL: {
            let editingFieldId = state.editingFieldId;

            if (
                state.currentPanel !== 'field-options' &&
                action.panel === 'field-options' &&
                state.formFields.length
            ) {
                editingFieldId = state.formFields[ 0 ].id;
            }

            if ( action.panel === 'form-fields-v4-1' ) {
                editingFieldId = 0;
            }

            return {
                ...state,
                currentPanel: action.panel,
                editingFieldId,
            };
        }

        case SET_EDITING_FIELD:
            return {
                ...state,
                editingFieldId: action.fieldId,
            };

        case OPEN_FIELD_SETTINGS:
            if (
                state.currentPanel === 'field-options' &&
                state.editingFieldId === action.fieldId
            ) {
                return state;
            }
            return {
                ...state,
                currentPanel: 'field-options',
                editingFieldId: action.fieldId,
            };

        case SET_INDEX_TO_INSERT:
            return {
                ...state,
                indexToInsert: action.index,
            };

        case TOGGLE_PANEL_SECTION: {
            const newSections = state.panelSections.map( ( section, i ) => {
                if ( i === action.index ) {
                    return { ...section, show: ! section.show };
                }
                return section;
            } );
            return { ...state, panelSections: newSections };
        }

        case SET_PANEL_SECTIONS:
            return {
                ...state,
                panelSections: action.sections,
            };

        case SET_PANEL_SECTION_FIELDS: {
            const updatedSections = state.panelSections.map( ( section ) => {
                if ( section.id === action.sectionId ) {
                    return { ...section, fields: action.fields };
                }
                return section;
            } );
            return { ...state, panelSections: updatedSections };
        }

        // Settings
        case SET_FORM_SETTINGS:
            return {
                ...state,
                settings: action.settings,
            };

        case UPDATE_FORM_SETTING:
            return {
                ...state,
                settings: {
                    ...state.settings,
                    [ action.key ]: action.value,
                },
                isDirty: true,
            };

        // Notifications
        case ADD_NOTIFICATION:
            return {
                ...state,
                notifications: [ ...state.notifications, action.notification ],
                isDirty: true,
            };

        case REMOVE_NOTIFICATION: {
            const newNotifications = [ ...state.notifications ];
            newNotifications.splice( action.index, 1 );
            return {
                ...state,
                notifications: newNotifications,
                isDirty: true,
            };
        }

        case CLONE_NOTIFICATION: {
            const clone = deepClone( state.notifications[ action.index ] );
            const newNotifications = [ ...state.notifications ];
            newNotifications.splice( action.index + 1, 0, clone );
            return {
                ...state,
                notifications: newNotifications,
                isDirty: true,
            };
        }

        case UPDATE_NOTIFICATION: {
            const newNotifications = [ ...state.notifications ];
            newNotifications[ action.index ] = action.value;
            return {
                ...state,
                notifications: newNotifications,
                isDirty: true,
            };
        }

        case UPDATE_NOTIFICATION_PROPERTY: {
            const newNotifications = [ ...state.notifications ];
            newNotifications[ action.index ] = {
                ...newNotifications[ action.index ],
                [ action.property ]: action.value,
            };
            return {
                ...state,
                notifications: newNotifications,
                isDirty: true,
            };
        }

        // Integrations
        case UPDATE_INTEGRATION:
            return {
                ...state,
                integrations: {
                    ...state.integrations,
                    [ action.index ]: action.value,
                },
                isDirty: true,
            };

        // Dirty state
        case MARK_DIRTY:
            return { ...state, isDirty: true };

        case MARK_CLEAN:
            return { ...state, isDirty: false };

        default:
            return state;
    }
}
