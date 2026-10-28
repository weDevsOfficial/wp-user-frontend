import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    display: false,
    notices: [],
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case ACTION_TYPES.ADD_NOTICE:
            return {
                ...state,
                notices: [...state.notices, action.notice],
                display: true,
            };
        case ACTION_TYPES.REMOVE_NOTICE:
            const newNotices = [...state.notices];
            // Validate index
            if (!Number.isInteger(action.index) || action.index < 0 || action.index >= newNotices.length) {
                return state;
            }
            newNotices.splice(action.index, 1);
            return {
                ...state,
                notices: newNotices,
                display: newNotices.length > 0,
            };
        default:
            return state;
    }
}
