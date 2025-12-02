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
