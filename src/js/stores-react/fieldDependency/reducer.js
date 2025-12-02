import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    modifierFields: {},
    hiddenFields: {},
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
        default:
            return state;
    }
}
