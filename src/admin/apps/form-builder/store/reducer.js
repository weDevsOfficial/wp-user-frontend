import {
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
    MOVE_REPEAT_FIELD,
    MOVE_FIELD_TO,
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
import { moveField as moveInTree } from '../utils/dndTree';

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
 * Give column / repeat inner fields an id when they have none.
 *
 * Inner fields are stored inside their column / repeat field without an id (the
 * save drops it), so after a reload every inner field had `id` null and the
 * builder could not open one for editing (develop had the same bug: the Field
 * Options panel stayed empty). A fresh id per load is enough: it only lives in
 * the builder session.
 *
 * @param {Array} fields Form fields.
 * @return {Array} The same fields, inner fields with ids.
 */
export function withInnerFieldIds( fields ) {
    if ( ! Array.isArray( fields ) ) {
        return fields;
    }

    // Marked so the save sends the inner field as stored (stripSessionIds()).
    const ensure = ( field ) => ( field && ! field.id ? { ...field, id: generateFieldId(), _wpuf_session_id: true } : field );

    return fields.map( ( field ) => {
        if ( ! field || ! field.inner_fields ) {
            return field;
        }

        if ( Array.isArray( field.inner_fields ) ) {
            return field.inner_fields.some( ( inner ) => inner && ! inner.id )
                ? { ...field, inner_fields: field.inner_fields.map( ensure ) }
                : field;
        }

        if ( 'object' === typeof field.inner_fields ) {
            const columns = Object.entries( field.inner_fields );

            if ( ! columns.some( ( [ , list ] ) => Array.isArray( list ) && list.some( ( inner ) => inner && ! inner.id ) ) ) {
                return field;
            }

            return {
                ...field,
                inner_fields: Object.fromEntries( columns.map( ( [ key, list ] ) => [ key, Array.isArray( list ) ? list.map( ensure ) : list ] ) ),
            };
        }

        return field;
    } );
}

/**
 * The fields as they are saved: inner fields drop the id withInnerFieldIds()
 * gave them for this builder session, so an untouched save stores the same
 * bytes (the stored inner fields had no id).
 *
 * @param {Array} fields Form fields.
 * @return {Array} Fields without session ids.
 */
export function stripSessionIds( fields ) {
    if ( ! Array.isArray( fields ) ) {
        return fields;
    }

    const strip = ( field ) => {
        if ( ! field || ! field._wpuf_session_id ) {
            return field;
        }

        const { id, _wpuf_session_id: marker, ...rest } = field; // eslint-disable-line no-unused-vars

        return rest;
    };

    return fields.map( ( field ) => {
        if ( ! field || ! field.inner_fields ) {
            return field;
        }

        if ( Array.isArray( field.inner_fields ) ) {
            return { ...field, inner_fields: field.inner_fields.map( strip ) };
        }

        if ( 'object' === typeof field.inner_fields ) {
            return {
                ...field,
                inner_fields: Object.fromEntries( Object.entries( field.inner_fields ).map( ( [ key, list ] ) => [ key, Array.isArray( list ) ? list.map( strip ) : list ] ) ),
            };
        }

        return field;
    } );
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
 * Apply one field edit. Typing a label on a new meta field also rewrites its
 * meta key from the label, as develop's field-text-meta followed the label
 * (saved fields keep their key).
 *
 * @param {Object} field
 * @param {string} fieldName
 * @param {*}      value
 * @return {Object} Updated field
 */
function applyFieldUpdate( field, fieldName, value ) {
    const updated = applyMutualExclusivity( field, fieldName, value );

    if ( 'label' === fieldName && field.is_new && 'yes' === field.is_meta && 'string' === typeof value ) {
        return { ...updated, name: value.replace( /\W/g, '_' ).toLowerCase() };
    }

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
            return applyFieldUpdate( field, fieldName, value );
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
                        return applyFieldUpdate( innerField, fieldName, value );
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
                    return applyFieldUpdate( innerField, fieldName, value );
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

        case INITIALIZE_STATE:
            return {
                ...state,
                ...action.payload,
                ...( action.payload && action.payload.formFields ? { formFields: withInnerFieldIds( action.payload.formFields ) } : {} ),
            };

        case SET_FORM_FIELDS:
            return {
                ...state,
                formFields: withInnerFieldIds( action.formFields ),
            };

        case ADD_FIELD: {
            const newFields = [ ...state.formFields ];
            const field = {
                ...action.field,
                // Only missing values get a default (a stored '' stays), as develop.
                show_icon: action.field.show_icon ?? 'no',
                field_icon: action.field.field_icon ?? '',
                icon_position: action.field.icon_position ?? 'left_label',
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
                if ( parseInt( field.id ) !== parseInt( action.columnFieldId ) ) {
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
                if ( parseInt( field.id ) !== parseInt( action.columnFieldId ) ) {
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
                if ( parseInt( field.id ) !== parseInt( action.columnFieldId ) ) {
                    return field;
                }
                const innerFields = {};
                for ( const col in field.inner_fields ) {
                    innerFields[ col ] = [ ...field.inner_fields[ col ] ];
                }

                const movedField = innerFields[ action.fromColumn ][ action.fromIndex ];
                innerFields[ action.toColumn ] = innerFields[ action.toColumn ] || [];

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
                if ( parseInt( field.id ) !== parseInt( action.columnFieldId ) ) {
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
                if ( parseInt( field.id ) !== parseInt( action.columnFieldId ) ) {
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
                if ( parseInt( field.id ) !== parseInt( action.repeatFieldId ) ) {
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
                if ( parseInt( field.id ) !== parseInt( action.repeatFieldId ) ) {
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

        case MOVE_REPEAT_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( parseInt( field.id ) !== parseInt( action.repeatFieldId ) || ! Array.isArray( field.inner_fields ) ) {
                    return field;
                }
                const innerFields = [ ...field.inner_fields ];
                const [ moved ] = innerFields.splice( action.fromIndex, 1 );
                innerFields.splice( action.toIndex, 0, moved );
                return { ...field, inner_fields: innerFields };
            } );
            return { ...state, formFields: newFields, isDirty: true };
        }

        case MOVE_FIELD_TO: {
            const moved = moveInTree( state.formFields, action.from, action.to );

            if ( moved === state.formFields ) {
                return state;
            }

            return { ...state, formFields: moved, isDirty: true };
        }

        case CLONE_REPEAT_FIELD: {
            const newFields = state.formFields.map( ( field ) => {
                if ( parseInt( field.id ) !== parseInt( action.repeatFieldId ) ) {
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
