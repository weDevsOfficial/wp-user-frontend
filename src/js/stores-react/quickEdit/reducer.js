import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    isQuickEdit: false,
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case ACTION_TYPES.SET_QUICK_EDIT_STATUS:
            return {
                ...state,
                isQuickEdit: action.status,
            };
        default:
            return state;
    }
}
