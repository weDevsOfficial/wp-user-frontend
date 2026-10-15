import { ACTION_TYPES } from './constants';

export function setQuickEditStatus(status) {
    return {
        type: ACTION_TYPES.SET_QUICK_EDIT_STATUS,
        status,
    };
}
