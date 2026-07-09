import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    modifierFields: {},
    hiddenFields: [],
    modifierFieldStatus: {},
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case ACTION_TYPES.SET_MODIFIER_FIELDS:
            return {
                ...state,
                modifierFields: action.fields,
            };
        case ACTION_TYPES.SET_HIDDEN_FIELDS:
            return {
                ...state,
                hiddenFields: action.fields,
            };
        case ACTION_TYPES.SET_MODIFIER_FIELD_STATUS:
            return {
                ...state,
                modifierFieldStatus: action.status,
            };
        case ACTION_TYPES.ADD_DEPENDENT_FIELDS:
            // Merge dependent fields into modifierFields
            const newModifierFields = { ...state.modifierFields };
            for (const dependentField in action.dependentFields) {
                if (newModifierFields.hasOwnProperty(dependentField)) {
                    newModifierFields[dependentField] = {
                        ...newModifierFields[dependentField],
                        ...action.dependentFields[dependentField],
                    };
                } else {
                    newModifierFields[dependentField] = action.dependentFields[dependentField];
                }
            }
            return {
                ...state,
                modifierFields: newModifierFields,
            };
        default:
            return state;
    }
}
