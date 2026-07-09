/**
 * Find a field by ID, searching top-level fields, column inner_fields (object),
 * and repeat inner_fields (array).
 *
 * @param {Object} state
 * @param {number} fieldId
 * @return {Object|null}
 */
function findFieldById( state, fieldId ) {
    const id = parseInt( fieldId );

    for ( const field of state.formFields ) {
        if ( parseInt( field.id ) === id ) {
            return field;
        }

        // Column field: inner_fields is object { 'column-1': [], ... }
        if ( field.template === 'column_field' && field.inner_fields ) {
            for ( const col in field.inner_fields ) {
                if ( ! field.inner_fields.hasOwnProperty( col ) ) {
                    continue;
                }
                for ( const innerField of field.inner_fields[ col ] ) {
                    if ( parseInt( innerField.id ) === id ) {
                        return innerField;
                    }
                }
            }
        }

        // Repeat field: inner_fields is array
        if ( field.template === 'repeat_field' && Array.isArray( field.inner_fields ) ) {
            for ( const innerField of field.inner_fields ) {
                if ( parseInt( innerField.id ) === id ) {
                    return innerField;
                }
            }
        }
    }

    return null;
}

export function getPost( state ) {
    return state.post;
}

export function getFormFields( state ) {
    return state.formFields;
}

export function getPanelSections( state ) {
    return state.panelSections;
}

export function getFieldSettings( state ) {
    return state.fieldSettings;
}

export function getNotifications( state ) {
    return state.notifications;
}

export function getSettings( state ) {
    return state.settings;
}

export function getIntegrations( state ) {
    return state.integrations;
}

export function getCurrentPanel( state ) {
    return state.currentPanel;
}

export function getEditingFieldId( state ) {
    return state.editingFieldId;
}

export function getEditingField( state ) {
    if ( ! state.editingFieldId ) {
        return null;
    }
    return findFieldById( state, state.editingFieldId );
}

export function getEditingFieldConfig( state ) {
    const field = getEditingField( state );
    if ( ! field || ! field.template ) {
        return null;
    }
    return state.fieldSettings[ field.template ] || null;
}

export function getIndexToInsert( state ) {
    return state.indexToInsert;
}

export function getIsDirty( state ) {
    return state.isDirty;
}

export function getIsProActive( state ) {
    return state.isProActive;
}

export function getFormType( state ) {
    return state.formType;
}

export function getI18n( state ) {
    return state.i18n;
}

export function getShowCustomFieldTooltip( state ) {
    return state.showCustomFieldTooltip;
}
