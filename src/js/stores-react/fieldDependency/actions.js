import { ACTION_TYPES } from './constants';

export function setModifierFields(fields) {
    return {
        type: ACTION_TYPES.SET_MODIFIER_FIELDS,
        fields,
    };
}

export function setHiddenFields(fields) {
    return {
        type: ACTION_TYPES.SET_HIDDEN_FIELDS,
        fields,
    };
}

export function setModifierFieldStatus(status) {
    return {
        type: ACTION_TYPES.SET_MODIFIER_FIELD_STATUS,
        status,
    };
}

export function addDependentFields(dependentFields) {
    return {
        type: ACTION_TYPES.ADD_DEPENDENT_FIELDS,
        dependentFields,
    };
}

/**
 * Recalculates which fields should be hidden based on current modifier statuses.
 *
 * @param {Object} modifierFields   Map of modifier field -> dependent fields with expected values
 * @param {Object} currentStatus    Current on/off status of each modifier field
 *
 * @return {Array} List of field IDs that should be hidden
 */
function calculateHiddenFields(modifierFields, currentStatus) {
    let hiddenFields = [];

    for (const modifierFieldName in modifierFields) {
        const modifierStatus = currentStatus[modifierFieldName] || false;

        for (const dependentField in modifierFields[modifierFieldName]) {
            const expectedValue = modifierFields[modifierFieldName][dependentField];

            if (modifierStatus !== expectedValue) {
                if (!hiddenFields.includes(dependentField)) {
                    hiddenFields.push(dependentField);
                }
            }
        }
    }

    return hiddenFields;
}

export function toggleDependentFields(fieldId, status) {
    return ({ dispatch, select }) => {
        const modifierFields = select.getModifierFields();
        const modifierFieldStatus = select.getModifierFieldStatus();

        // Update the status of the current modifier field
        const updatedStatus = {
            ...modifierFieldStatus,
            [fieldId]: status,
        };
        dispatch.setModifierFieldStatus(updatedStatus);

        // Recalculate hidden fields using the UPDATED status
        const hiddenFields = calculateHiddenFields(modifierFields, updatedStatus);

        dispatch.setHiddenFields(hiddenFields);
    };
}

export function initializeFieldVisibility(initialStatuses) {
    return ({ dispatch, select }) => {
        const modifierFields = select.getModifierFields();

        dispatch.setModifierFieldStatus(initialStatuses);

        const hiddenFields = calculateHiddenFields(modifierFields, initialStatuses);

        dispatch.setHiddenFields(hiddenFields);
    };
}
