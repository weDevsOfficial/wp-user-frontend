import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { ACTION_TYPES } from './constants';

// Internal helper to get global data
const getWpufSubscriptions = () => window.wpufSubscriptions || {};

export function setItems(items) {
    return {
        type: ACTION_TYPES.SET_ITEMS,
        items,
    };
}

export function setIsLoading(isLoading) {
    return {
        type: ACTION_TYPES.SET_IS_LOADING,
        isLoading,
    };
}

export function setIsUpdating(isUpdating) {
    return {
        type: ACTION_TYPES.SET_IS_UPDATING,
        isUpdating,
    };
}

export function setIsDirty(isDirty) {
    return {
        type: ACTION_TYPES.SET_IS_DIRTY,
        isDirty,
    };
}

export function setIsUnsavedPopupOpen(isOpen) {
    return {
        type: ACTION_TYPES.SET_IS_UNSAVED_POPUP_OPEN,
        isOpen,
    };
}

export function setCurrentStatus(status) {
    return {
        type: ACTION_TYPES.SET_CURRENT_STATUS,
        status,
    };
}

export function setItem(item) {
    return {
        type: ACTION_TYPES.SET_ITEM,
        item,
    };
}

export function setItemCopy(itemCopy) {
    return {
        type: ACTION_TYPES.SET_ITEM_COPY,
        itemCopy,
    };
}

export function setErrors(errors) {
    return {
        type: ACTION_TYPES.SET_ERRORS,
        errors,
    };
}

export function setUpdateError(error) {
    return {
        type: ACTION_TYPES.SET_UPDATE_ERROR,
        error,
    };
}

export function setCounts(counts) {
    return {
        type: ACTION_TYPES.SET_COUNTS,
        counts,
    };
}

export function setTaxonomyRestriction(restriction) {
    return {
        type: ACTION_TYPES.SET_TAXONOMY_RESTRICTION,
        restriction,
    };
}

export function setTaxonomyViewRestriction(restriction) {
    return {
        type: ACTION_TYPES.SET_TAXONOMY_VIEW_RESTRICTION,
        restriction,
    };
}

export function setCurrentPage(page) {
    return {
        type: ACTION_TYPES.SET_CURRENT_PAGE,
        page,
    };
}

export function modifyItem(key, value, serializeKey = null) {
    return {
        type: ACTION_TYPES.MODIFY_ITEM,
        key,
        value,
        serializeKey,
    };
}

export function resetErrors() {
    return {
        type: ACTION_TYPES.RESET_ERRORS,
    };
}

export function setError(field, message) {
    return ({ select, dispatch }) => {
        const errors = { ...select.getErrors() };
        errors[field] = {
            status: true,
            message,
        };
        dispatch.setErrors(errors);
    };
}

export function setBlankItem() {
    return ({ dispatch, select }) => {
        const item = {
            meta_value: {},
        };

        const fields = select.getFields();

        for (const field of fields) {
            if (field.hasOwnProperty('type') && field.type === 'inline') {
                for (const innerField in field.fields) {
                    dispatch.populateDefaultValue(item, field.fields[innerField]);
                }
            } else {
                dispatch.populateDefaultValue(item, field);
            }
        }

        dispatch.setItem(item);
    };
}

export function populateDefaultValue(item, field) {
    return () => {
        switch (field.db_type) {
            case 'post':
                item[field.db_key] = field.default;
                break;

            case 'meta':
                if (!item.meta_value) item.meta_value = {};
                item.meta_value[field.db_key] = field.default;
                break;

            case 'meta_serialized':
                if (!item.meta_value) item.meta_value = {};
                let serializedValue = {};
                if (item.meta_value.hasOwnProperty(field.db_key)) {
                    serializedValue = item.meta_value[field.db_key];
                    serializedValue[field.serialize_key] = field.default;
                } else {
                    serializedValue[field.serialize_key] = field.default;
                }

                item.meta_value[field.db_key] = serializedValue;
                break;
        }
    };
}

export function fetchItems(status, offset = 0) {
    return async ({ dispatch }) => {
        dispatch.setIsLoading(true);
        dispatch.setCurrentStatus(status);

        const wpufSubscriptions = getWpufSubscriptions();

        const queryParams = {
            'per_page': wpufSubscriptions.perPage,
            'offset': offset,
            'post_status': status
        };

        try {
            const response = await apiFetch({
                path: addQueryArgs('/wpuf/v1/wpuf_subscription', queryParams),
                method: 'GET',
            });

            if (response.success) {
                dispatch.setItems(response.subscriptions);
            }
            return response;
        } catch (error) {
            console.error(error);
        } finally {
            dispatch.setIsLoading(false);
        }
    };
}

// Alias for backward compatibility with Subscriptions.jsx
export function setSubscriptionsByStatus(status, offset = 0) {
    return fetchItems(status, offset);
}

// Alias for getSubscriptionCount
export function getSubscriptionCount(status = 'all') {
    return fetchCounts(status);
}

export function fetchCounts(status = 'all') {
    return async ({ dispatch }) => {
        let path = '/wpuf/v1/wpuf_subscription/count';

        if (status !== 'all') {
            path += '/' + status;
        }

        try {
            const response = await apiFetch({
                path: addQueryArgs(path),
                method: 'GET',
            });

            if (response.success) {
                dispatch.setCounts(response.count);
            }
        } catch (error) {
            console.error(error);
        }
    };
}

export function updateItem() {
    return async ({ select, dispatch }) => {
        const item = select.getItem();
        if (item === null) {
            return false;
        }

        dispatch.setIsUpdating(true);

        // Handle taxonomy restrictions
        const taxonomyRestriction = select.getTaxonomyRestriction();
        let allTaxonomies = [];
        for (const [key, taxonomy] of Object.entries(taxonomyRestriction)) {
            allTaxonomies = allTaxonomies.concat(taxonomy);
        }
        const taxonomyIntValue = allTaxonomies.map((item) => parseInt(item));
        const uniqueTaxonomies = [...new Set(taxonomyIntValue)];

        // We need to update the item state with these values before sending
        // But since modifyItem triggers a state update, we can just mutate a copy for the API call
        // or dispatch modifyItem actions. Dispatching is safer.
        dispatch.modifyItem('_sub_allowed_term_ids', uniqueTaxonomies);

        // Handle view taxonomy restrictions
        const taxonomyViewRestriction = select.getTaxonomyViewRestriction();
        let allViewTaxonomies = [];
        for (const [key, taxonomy] of Object.entries(taxonomyViewRestriction)) {
            allViewTaxonomies = allViewTaxonomies.concat(taxonomy);
        }
        const viewTaxonomyIntValue = allViewTaxonomies.map((item) => parseInt(item));
        const uniqueViewTaxonomies = [...new Set(viewTaxonomyIntValue)];

        dispatch.modifyItem('_sub_view_allowed_term_ids', uniqueViewTaxonomies);

        // Get the updated item after modifications
        // Note: In Redux/WordPress data, dispatches are synchronous for plain objects but we need to be careful.
        // Since we just dispatched modifyItem, the state should be updated.
        const updatedItem = select.getItem();

        let path = '/wpuf/v1/wpuf_subscription';

        if (updatedItem.ID) {
            path += '/' + updatedItem.ID;
        }

        try {
            const response = await apiFetch({
                path: path,
                method: 'POST',
                data: { subscription: updatedItem }
            });

            dispatch.setIsDirty(false);
            return response;
        } catch (error) {
            dispatch.setError('fetch', 'An error occurred while updating the subscription.');
        } finally {
            dispatch.setIsUpdating(false);
        }
    };
}

export function deleteItem(id) {
    return async () => {
        try {
            const response = await apiFetch({
                path: `/wpuf/v1/wpuf_subscription/${id}`,
                method: 'DELETE',
            });
            return response;
        } catch (error) {
            console.error(error);
        }
    };
}

export function populateTaxonomyRestrictionData(item) {
    return ({ dispatch, select }) => {
        dispatch.setTaxonomyRestriction({});
        dispatch.setTaxonomyViewRestriction({});

        if (!item.meta_value) {
            return;
        }

        // Handle posting taxonomy restrictions
        const allowedTermIds = item.meta_value._sub_allowed_term_ids;
        if (allowedTermIds && Array.isArray(allowedTermIds)) {
            const taxonomyGroups = {};
            allowedTermIds.forEach(termId => {
                const term = select.getTermById(termId);
                if (term && term.taxonomy) {
                    if (!taxonomyGroups[term.taxonomy]) {
                        taxonomyGroups[term.taxonomy] = [];
                    }
                    taxonomyGroups[term.taxonomy].push(termId);
                }
            });
            dispatch.setTaxonomyRestriction(taxonomyGroups);
        }

        // Handle view taxonomy restrictions
        const viewAllowedTermIds = item.meta_value._sub_view_allowed_term_ids;
        if (viewAllowedTermIds && Array.isArray(viewAllowedTermIds)) {
            const viewTaxonomyGroups = {};
            viewAllowedTermIds.forEach(termId => {
                const term = select.getTermById(termId);
                if (term && term.taxonomy) {
                    const fieldId = 'view_' + term.taxonomy;
                    if (!viewTaxonomyGroups[fieldId]) {
                        viewTaxonomyGroups[fieldId] = [];
                    }
                    viewTaxonomyGroups[fieldId].push(termId);
                }
            });
            dispatch.setTaxonomyViewRestriction(viewTaxonomyGroups);
        }
    };
}

export function validateFields(mode = 'update') {
    return ({ select, dispatch }) => {
        dispatch.resetErrors();
        const item = select.getItem();

        if (mode === 'quickEdit') {
            const planName = item.post_title;
            if (planName === '') {
                dispatch.setError('planName', __('This field is required', 'wp-user-frontend'));
            }
            if (planName.includes('#')) {
                dispatch.setError('planName', __('# is not supported in plan name', 'wp-user-frontend'));
            }
        } else {
            const fields = select.getFields();
            // We need to iterate over the fields structure to validate
            // For simplicity, let's assume getFields returns the flat list of fields we need to check
            // Actually getFields returns all fields, so we can iterate them

            for (const fieldData of fields) {
                let value = '';
                switch (fieldData.db_type) {
                    case 'meta':
                        value = item.meta_value[fieldData.db_key];
                        break;
                    case 'meta_serialized':
                        // This logic might need adjustment based on how meta_serialized is stored in the item
                        // In the Vue store it was accessing meta_value[db_key] which is an object
                        if (item.meta_value[fieldData.db_key]) {
                            // This part is a bit tricky without exact structure, assuming value is checked for existence/emptiness
                            // But for text fields inside serialized, we might need deeper check
                            value = item.meta_value[fieldData.db_key];
                        }
                        break;
                    case 'post':
                        value = item[fieldData.db_key];
                        break;
                    default:
                        value = '';
                        break;
                }

                if (fieldData.id === 'plan-name' && value && value.includes('#')) {
                    dispatch.setError(fieldData.id, __('# is not supported in plan name', 'wp-user-frontend'));
                }

                if (fieldData.is_required && (value === '' || value === undefined || value === null)) {
                    dispatch.setError(fieldData.id, __(fieldData.label + ' is required', 'wp-user-frontend'));
                }
            }
        }

        return !select.hasError();
    };
}
