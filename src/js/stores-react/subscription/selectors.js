import { __ } from '@wordpress/i18n';

// Internal helper to get global data
const getWpufSubscriptions = () => window.wpufSubscriptions || {};

export function getItems(state) {
    return state.items;
}

export function getItem(state) {
    return state.item;
}

export function getItemCopy(state) {
    return state.itemCopy;
}

export function getCounts(state) {
    return state.counts;
}

export function getErrors(state) {
    return state.errors;
}

export function getUpdateError(state) {
    return state.updateError;
}

export function getTaxonomyRestriction(state) {
    return state.taxonomyRestriction;
}

export function getTaxonomyViewRestriction(state) {
    return state.taxonomyViewRestriction;
}

export function getCurrentPage(state) {
    return state.currentPage;
}

export function isLoading(state) {
    return state.isLoading;
}

export function isUpdating(state) {
    return state.isUpdating;
}

export function isDirty(state) {
    return state.isDirty;
}

export function isUnsavedPopupOpen(state) {
    return state.isUnsavedPopupOpen;
}

export function getCurrentStatus(state) {
    return state.currentStatus;
}

export function getFieldNames(state) {
    const wpufSubscriptions = getWpufSubscriptions();
    const sections = wpufSubscriptions.fields;
    const names = [];

    for (const section in sections) {
        if (!sections.hasOwnProperty(section)) {
            continue;
        }
        for (const subsection in sections[section]) {
            if (!sections[section].hasOwnProperty(subsection)) {
                continue;
            }
            for (const field in sections[section][subsection]) {
                names.push(field);
            }
        }
    }

    return names;
}

export function getFields(state) {
    const wpufSubscriptions = getWpufSubscriptions();
    const sections = wpufSubscriptions.fields;
    const fields = [];

    for (const section in sections) {
        if (!sections.hasOwnProperty(section)) {
            continue;
        }
        for (const subsection in sections[section]) {
            if (!sections[section].hasOwnProperty(subsection)) {
                continue;
            }
            for (const field in sections[section][subsection]) {
                fields.push(sections[section][subsection][field]);
            }
        }
    }

    return fields;
}

export function isRecurring(state, subscription) {
    // If subscription is not passed, use current item
    const item = subscription || state.item;
    if (!item || !item.meta_value) {
        return false;
    }
    return item.meta_value.recurring_pay === 'on' || item.meta_value.recurring_pay === 'yes';
}

export function getReadableBillingAmount(state, subscription, returnAsHtml = false) {
    // If subscription is not passed, use current item
    const item = subscription || state.item;
    if (!item || !item.meta_value) {
        return '';
    }

    const wpufSubscriptions = getWpufSubscriptions();
    const currencySymbol = wpufSubscriptions.currencySymbol || '$';

    if (isRecurring(state, item)) {
        const cyclePeriod = item.meta_value.cycle_period === '' ? __('day', 'wp-user-frontend') : item.meta_value.cycle_period;
        const expireAfter = (parseInt(item.meta_value._billing_cycle_number) === 0 || parseInt(item.meta_value._billing_cycle_number) === 1) ? '' : ' ' + item.meta_value._billing_cycle_number + ' ';

        if (returnAsHtml) {
            return currencySymbol + item.meta_value.billing_amount + ' <span class="wpuf-text-sm wpuf-text-gray-500">per ' + expireAfter + ' ' + cyclePeriod + '(s)</span>';
        } else {
            return currencySymbol + item.meta_value.billing_amount + ' every ' + expireAfter + ' ' + cyclePeriod + '(s)';
        }
    } else {
        if (parseInt(item.meta_value.billing_amount) === 0 || item.meta_value.billing_amount === '') {
            return __('Free', 'wp-user-frontend');
        } else {
            return currencySymbol + item.meta_value.billing_amount;
        }
    }
}

export function hasError(state) {
    for (const item in state.errors) {
        if (state.errors[item] && state.errors[item].status) {
            return true;
        }
    }
    return false;
}

export function getTermById(state, termId) {
    const wpufSubscriptions = getWpufSubscriptions();

    if (wpufSubscriptions.fields && wpufSubscriptions.fields.advanced_configuration) {
        // Check taxonomy_restriction section
        if (wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction) {
            for (const taxonomyName in wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction) {
                const termFields = wpufSubscriptions.fields.advanced_configuration.taxonomy_restriction[taxonomyName].term_fields;
                const term = termFields.find(t => t.value == termId);
                if (term) {
                    return { ...term, taxonomy: taxonomyName };
                }
            }
        }

        // Check taxonomy_view_restriction section
        if (wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction) {
            for (const taxonomyName in wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction) {
                const termFields = wpufSubscriptions.fields.advanced_configuration.taxonomy_view_restriction[taxonomyName].term_fields;
                const term = termFields.find(t => t.value == termId);
                if (term) {
                    return { ...term, taxonomy: taxonomyName };
                }
            }
        }
    }

    return null;
}
