import { ACTION_TYPES } from './constants';

export function removeNotice(index) {
    return {
        type: ACTION_TYPES.REMOVE_NOTICE,
        index,
    };
}
