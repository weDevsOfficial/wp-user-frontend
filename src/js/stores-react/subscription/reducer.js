import { ACTION_TYPES } from './constants';

const DEFAULT_STATE = {
    items: [],
    isUpdating: false,
    isLoading: false,
    isDirty: false,
    isUnsavedPopupOpen: false,
    currentStatus: 'all',
    itemCopy: null,
    item: null,
    errors: {},
    updateError: {
        status: false,
        message: '',
    },
    counts: {},
    taxonomyRestriction: {},
    taxonomyViewRestriction: {},
    currentPage: 1,
};

export default function reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
        case ACTION_TYPES.SET_ITEMS:
            return {
                ...state,
                items: action.items,
            };
        case ACTION_TYPES.SET_IS_LOADING:
            return {
                ...state,
                isLoading: action.isLoading,
            };
        case ACTION_TYPES.SET_IS_UPDATING:
            return {
                ...state,
                isUpdating: action.isUpdating,
            };
        case ACTION_TYPES.SET_IS_DIRTY:
            return {
                ...state,
                isDirty: action.isDirty,
            };
        case ACTION_TYPES.SET_IS_UNSAVED_POPUP_OPEN:
            return {
                ...state,
                isUnsavedPopupOpen: action.isOpen,
            };
        case ACTION_TYPES.SET_CURRENT_STATUS:
            return {
                ...state,
                currentStatus: action.status,
            };
        case ACTION_TYPES.SET_ITEM:
            return {
                ...state,
                item: action.item,
            };
        case ACTION_TYPES.SET_ITEM_COPY:
            return {
                ...state,
                itemCopy: action.itemCopy,
            };
        case ACTION_TYPES.SET_ERRORS:
            return {
                ...state,
                errors: action.errors,
            };
        case ACTION_TYPES.SET_UPDATE_ERROR:
            return {
                ...state,
                updateError: action.error,
            };
        case ACTION_TYPES.SET_COUNTS:
            return {
                ...state,
                counts: action.counts,
            };
        case ACTION_TYPES.SET_TAXONOMY_RESTRICTION:
            return {
                ...state,
                taxonomyRestriction: action.restriction,
            };
        case ACTION_TYPES.SET_TAXONOMY_VIEW_RESTRICTION:
            return {
                ...state,
                taxonomyViewRestriction: action.restriction,
            };
        case ACTION_TYPES.SET_CURRENT_PAGE:
            return {
                ...state,
                currentPage: action.page,
            };
        case ACTION_TYPES.MODIFY_ITEM:
            // Deep clone to avoid mutation issues
            const newItem = JSON.parse(JSON.stringify(state.item));
            const { key, value, serializeKey } = action;

            if (serializeKey === null) {
                if (newItem.hasOwnProperty(key)) {
                    newItem[key] = value;
                } else {
                    // Ensure meta_value exists
                    if (!newItem.meta_value) {
                        newItem.meta_value = {};
                    }
                    newItem.meta_value[key] = value;
                }
            } else {
                if (!newItem.meta_value) {
                    newItem.meta_value = {};
                }

                if (!newItem.meta_value.hasOwnProperty(key)) {
                    // If key doesn't exist in meta_value, initialize it
                    if (key === 'additional_cpt_options') {
                        newItem.meta_value[key] = {};
                    } else {
                        // Default behavior for other keys if needed, or just return
                    }
                }

                // Handle the specific case for additional_cpt_options where it might be a string
                if (typeof newItem.meta_value[key] === 'string' && key === 'additional_cpt_options') {
                    newItem.meta_value[key] = {};
                }

                if (typeof newItem.meta_value[key] === 'object') {
                    newItem.meta_value[key][serializeKey] = value;
                }
            }

            return {
                ...state,
                item: newItem,
                isDirty: true,
            };
        case ACTION_TYPES.RESET_ERRORS:
            return {
                ...state,
                errors: {},
            };
        default:
            return state;
    }
}
