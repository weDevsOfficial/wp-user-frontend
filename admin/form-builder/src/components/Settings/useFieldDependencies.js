import { useMemo } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

/**
 * Field dependency map — mirrors FormDependencyHandler in form-builder.js.
 *
 * Each key is a field ID. `dependsOn` is an array of conditions (AND logic).
 * - field: the control field ID
 * - value: expected value. `true` = checkbox is checked, string = select/radio value.
 */
const FIELD_DEPENDENCIES = {
    // Redirect settings
    message: {
        dependsOn: [ { field: 'redirect_to', value: 'same' } ],
    },
    page_id: {
        dependsOn: [ { field: 'redirect_to', value: 'page' } ],
    },
    url: {
        dependsOn: [ { field: 'redirect_to', value: 'url' } ],
    },
    update_message: {
        dependsOn: [ { field: 'edit_redirect_to', value: 'same' } ],
    },
    edit_page_id: {
        dependsOn: [ { field: 'edit_redirect_to', value: 'page' } ],
    },
    edit_url: {
        dependsOn: [ { field: 'edit_redirect_to', value: 'url' } ],
    },

    // Guest posting
    guest_details: {
        dependsOn: [ { field: 'post_permission', value: 'guest_post' } ],
    },
    guest_email_verify: {
        dependsOn: [
            { field: 'post_permission', value: 'guest_post' },
            { field: 'guest_details', value: true },
        ],
    },
    name_label: {
        dependsOn: [
            { field: 'post_permission', value: 'guest_post' },
            { field: 'guest_details', value: true },
        ],
    },
    email_label: {
        dependsOn: [
            { field: 'post_permission', value: 'guest_post' },
            { field: 'guest_details', value: true },
        ],
    },
    roles: {
        dependsOn: [ { field: 'post_permission', value: 'role_base' } ],
    },
    message_restrict: {
        dependsOn: [ { field: 'post_permission', value: 'role_base' } ],
    },

    // Payment settings
    choose_payment_option: {
        dependsOn: [ { field: 'payment_options', value: true } ],
    },
    fallback_ppp_enable: {
        dependsOn: [
            { field: 'payment_options', value: true },
            { field: 'choose_payment_option', value: 'force_pack_purchase' },
        ],
    },
    fallback_ppp_cost: {
        dependsOn: [
            { field: 'payment_options', value: true },
            { field: 'choose_payment_option', value: 'force_pack_purchase' },
            { field: 'fallback_ppp_enable', value: true },
        ],
    },
    pay_per_post_cost: {
        dependsOn: [
            { field: 'payment_options', value: true },
            { field: 'choose_payment_option', value: 'enable_pay_per_post' },
        ],
    },
    ppp_payment_success_page: {
        dependsOn: [
            { field: 'payment_options', value: true },
            { field: 'choose_payment_option', value: 'enable_pay_per_post' },
        ],
    },

    // Notification settings
    new_to: {
        dependsOn: [ { field: 'new', value: true } ],
    },
    new_subject: {
        dependsOn: [ { field: 'new', value: true } ],
    },
    new_body: {
        dependsOn: [ { field: 'new', value: true } ],
    },

    // Schedule
    schedule_start: {
        dependsOn: [ { field: 'schedule_form', value: true } ],
    },
    form_pending_message: {
        dependsOn: [ { field: 'schedule_form', value: true } ],
    },
    form_expired_message: {
        dependsOn: [ { field: 'schedule_form', value: true } ],
    },

    // Limit entries
    limit_number: {
        dependsOn: [ { field: 'limit_entries', value: true } ],
    },
    limit_message: {
        dependsOn: [ { field: 'limit_entries', value: true } ],
    },

    // n8n webhook
    n8n_webhook_url: {
        dependsOn: [ { field: 'enable_n8n', value: true } ],
    },
};

/**
 * Check if a checkbox/toggle value is considered "on".
 */
function isChecked( val ) {
    return val === 'on' || val === 'yes' || val === true || val === '1';
}

/**
 * Check if a single dependency condition is met.
 */
function isConditionMet( dep, settings ) {
    const controlValue = settings[ dep.field ];

    if ( dep.value === true ) {
        // Boolean check — the control field should be checked/on
        return isChecked( controlValue );
    }

    if ( dep.value === false ) {
        return ! isChecked( controlValue );
    }

    // String comparison for select/radio values
    return controlValue === dep.value;
}

/**
 * Check if a field should be visible based on its dependencies and current settings.
 *
 * @param {string} fieldName - The field key/ID.
 * @param {Object} settings  - Current form settings from the store.
 * @return {boolean} Whether the field should be visible.
 */
export function isFieldVisible( fieldName, settings ) {
    const deps = FIELD_DEPENDENCIES[ fieldName ];

    if ( ! deps ) {
        // No dependency defined — always visible
        return true;
    }

    // ALL conditions must be met (AND logic)
    return deps.dependsOn.every( ( dep ) => isConditionMet( dep, settings ) );
}

/**
 * Hook that returns a visibility checker function bound to current settings.
 * Also allows Pro extensions to add dependencies via wp.hooks filter.
 *
 * @param {Object} settings - Current form settings from the store.
 * @return {Function} (fieldName) => boolean
 */
export function useFieldVisibility( settings ) {
    const deps = useMemo( () => {
        return applyFilters( 'wpuf.formBuilder.fieldDependencies', FIELD_DEPENDENCIES );
    }, [] );

    return useMemo( () => {
        return ( fieldName ) => {
            const fieldDeps = deps[ fieldName ];

            if ( ! fieldDeps ) {
                return true;
            }

            return fieldDeps.dependsOn.every( ( dep ) => isConditionMet( dep, settings ) );
        };
    }, [ deps, settings ] );
}

/**
 * Mutual exclusivity pairs — when one is turned on, the other must be turned off.
 * Mirrors the jQuery logic in form-builder.js lines 901-913.
 */
export const MUTUAL_EXCLUSIONS = {
    payment_options: 'enable_pricing_payment',
    enable_pricing_payment: 'payment_options',
};
