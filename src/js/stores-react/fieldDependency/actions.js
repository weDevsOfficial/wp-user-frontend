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

export function toggleDependentFields(fieldId, status) {
    return ({ dispatch, select }) => {
        const modifierFields = select.getModifierFields();
        const modifierFieldStatus = select.getModifierFieldStatus();

        // Update the status of the current modifier field
        dispatch.setModifierFieldStatus({
            ...modifierFieldStatus,
            [fieldId]: status,
        });

        // Reset hiddenFields array
        let hiddenFields = [];

        // Loop through all modifier fields and their dependent fields
        for (const modifierFieldName in modifierFields) {
            // Get the current status of this modifier field
            const modifierStatus = modifierFieldStatus[modifierFieldName] || false;

            // For each dependent field of this modifier
            for (const dependentField in modifierFields[modifierFieldName]) {
                const expectedValue = modifierFields[modifierFieldName][dependentField];

                // If status doesn't match the expected value, add to hiddenFields
                if (modifierStatus !== expectedValue) {
                    // Only add if not already in the array
                    if (!hiddenFields.includes(dependentField)) {
                        hiddenFields.push(dependentField);
                    }
                }
            }
        }

        // Update the hiddenFields in the store
        dispatch.setHiddenFields(hiddenFields);
    };
}
