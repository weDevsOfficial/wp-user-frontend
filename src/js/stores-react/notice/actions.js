import { ACTION_TYPES } from './constants';

export function addNotice(notice) {
    return ({ dispatch, select }) => {
        dispatch({
            type: ACTION_TYPES.ADD_NOTICE,
            notice,
        });

        // Auto-hide after 3 seconds
        setTimeout(() => {
            const notices = select.getNotices();
            // We rely on object reference equality here, similar to the Vue implementation
            const index = notices.indexOf(notice);
            if (index !== -1) {
                dispatch.removeNotice(index);
            }
        }, 3000);
    };
}

export function removeNotice(index) {
    return {
        type: ACTION_TYPES.REMOVE_NOTICE,
        index,
    };
}
