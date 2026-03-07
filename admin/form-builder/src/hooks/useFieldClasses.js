import { useCallback } from '@wordpress/element';

const INPUT_CLASSES = {
    upload_btn: 'file-selector wpuf-rounded-md wpuf-btn-secondary',
    radio: '!wpuf-mt-0 !wpuf-mr-2 wpuf-radio !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:checked:!wpuf-shadow-none focus:wpuf-shadow-primary',
    checkbox: '!wpuf-mt-0 !wpuf-mr-2 wpuf-h-4 wpuf-w-4 !wpuf-shadow-none checked:!wpuf-shadow-none focus:checked:!wpuf-shadow-primary focus:checked:!wpuf-shadow-none !wpuf-border-gray-300 checked:!wpuf-border-primary before:checked:!wpuf-bg-white hover:checked:!wpuf-bg-primary focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent focus:checked:!wpuf-bg-primary focus:wpuf-shadow-primary checked:focus:!wpuf-bg-primary checked:hover:wpuf-bg-primary checked:!wpuf-bg-primary before:!wpuf-content-none wpuf-rounded',
    dropdown: 'wpuf-block wpuf-w-full wpuf-min-w-full !wpuf-bg-white !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 wpuf-font-normal !wpuf-leading-none !wpuf-shadow-sm !wpuf-border !wpuf-border-solid !wpuf-border-gray-300 !wpuf-rounded-md focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:!wpuf-text-gray-700 !wpuf-text-base',
    default: 'wpuf-block wpuf-min-w-full !wpuf-bg-white !wpuf-m-0 !wpuf-leading-none !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 !wpuf-border !wpuf-border-solid !wpuf-border-gray-300 !wpuf-rounded-md wpuf-max-w-full focus:!wpuf-ring-transparent',
};

/**
 * Hook that provides field CSS class utilities.
 * Replaces the Vue form-field.js mixin.
 *
 * @param {Object} field   The field object
 * @param {number} formId  The form post ID
 * @return {Object}
 */
export function useFieldClasses( field, formId ) {
    const requiredClass = field.required === 'yes' ? 'required' : '';

    const builderClassNames = useCallback(
        ( typeClass ) => {
            const commonClasses = INPUT_CLASSES[ typeClass ] || INPUT_CLASSES.default;
            return [
                typeClass,
                `wpuf_${ field.name }_${ formId }`,
                commonClasses,
            ]
                .filter( Boolean )
                .join( ' ' );
        },
        [ field.name, formId ]
    );

    return {
        requiredClass,
        builderClassNames,
    };
}

/**
 * Check if a field is a Pro feature.
 *
 * @param {Object} field         The field object
 * @param {Object} fieldSettings The full fieldSettings from the store
 * @return {boolean}
 */
export function isProFeature( field, fieldSettings ) {
    if ( ! field || ! field.template ) {
        return false;
    }
    const config = fieldSettings[ field.template ];
    return !! ( config && config.pro_feature );
}

/**
 * Format a price value to 2 decimal places.
 *
 * @param {*} price
 * @return {string}
 */
export function formatPrice( price ) {
    if ( price === null || price === undefined || price === '' ) {
        return '0.00';
    }
    const num = parseFloat( price );
    if ( isNaN( num ) ) {
        return '0.00';
    }
    return num.toFixed( 2 );
}
