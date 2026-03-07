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

// Post
export function setPost( post ) {
    return { type: SET_POST, post };
}

// Bulk initialization for fields not covered by individual setters
export function initializeState( payload ) {
    return { type: INITIALIZE_STATE, payload };
}

// Field actions
export function setFormFields( formFields ) {
    return { type: SET_FORM_FIELDS, formFields };
}

export function addField( field, index ) {
    return { type: ADD_FIELD, field, index };
}

export function removeField( index ) {
    return { type: REMOVE_FIELD, index };
}

export function moveField( fromIndex, toIndex ) {
    return { type: MOVE_FIELD, fromIndex, toIndex };
}

export function cloneField( fieldId, newId ) {
    return { type: CLONE_FIELD, fieldId, newId };
}

export function updateField( fieldId, fieldName, value ) {
    return { type: UPDATE_FIELD, fieldId, fieldName, value };
}

// Column field actions
export function addColumnField( columnFieldId, column, index, field ) {
    return { type: ADD_COLUMN_FIELD, columnFieldId, column, index, field };
}

export function removeColumnField( columnFieldId, column, index ) {
    return { type: REMOVE_COLUMN_FIELD, columnFieldId, column, index };
}

export function moveColumnField( columnFieldId, fromColumn, fromIndex, toColumn, toIndex ) {
    return { type: MOVE_COLUMN_FIELD, columnFieldId, fromColumn, fromIndex, toColumn, toIndex };
}

export function cloneColumnField( columnFieldId, column, innerFieldId, newId ) {
    return { type: CLONE_COLUMN_FIELD, columnFieldId, column, innerFieldId, newId };
}

export function mergeColumnFields( columnFieldId, moveTo ) {
    return { type: MERGE_COLUMN_FIELDS, columnFieldId, moveTo };
}

// Repeat field actions
export function addRepeatField( repeatFieldId, index, field ) {
    return { type: ADD_REPEAT_FIELD, repeatFieldId, index, field };
}

export function removeRepeatField( repeatFieldId, index ) {
    return { type: REMOVE_REPEAT_FIELD, repeatFieldId, index };
}

export function cloneRepeatField( repeatFieldId, index, newId ) {
    return { type: CLONE_REPEAT_FIELD, repeatFieldId, index, newId };
}

// Panel / UI actions
export function setCurrentPanel( panel ) {
    return { type: SET_CURRENT_PANEL, panel };
}

export function setEditingField( fieldId ) {
    return { type: SET_EDITING_FIELD, fieldId };
}

export function openFieldSettings( fieldId ) {
    return { type: OPEN_FIELD_SETTINGS, fieldId };
}

export function setIndexToInsert( index ) {
    return { type: SET_INDEX_TO_INSERT, index };
}

export function togglePanelSection( index ) {
    return { type: TOGGLE_PANEL_SECTION, index };
}

export function setPanelSections( sections ) {
    return { type: SET_PANEL_SECTIONS, sections };
}

export function setPanelSectionFields( sectionId, fields ) {
    return { type: SET_PANEL_SECTION_FIELDS, sectionId, fields };
}

// Settings
export function setFormSettings( settings ) {
    return { type: SET_FORM_SETTINGS, settings };
}

export function updateFormSetting( key, value ) {
    return { type: UPDATE_FORM_SETTING, key, value };
}

// Notifications
export function addNotification( notification ) {
    return { type: ADD_NOTIFICATION, notification };
}

export function removeNotification( index ) {
    return { type: REMOVE_NOTIFICATION, index };
}

export function cloneNotification( index ) {
    return { type: CLONE_NOTIFICATION, index };
}

export function updateNotification( index, value ) {
    return { type: UPDATE_NOTIFICATION, index, value };
}

export function updateNotificationProperty( index, property, value ) {
    return { type: UPDATE_NOTIFICATION_PROPERTY, index, property, value };
}

// Integrations
export function updateIntegration( index, value ) {
    return { type: UPDATE_INTEGRATION, index, value };
}

// Dirty state
export function markDirty() {
    return { type: MARK_DIRTY };
}

export function markClean() {
    return { type: MARK_CLEAN };
}
