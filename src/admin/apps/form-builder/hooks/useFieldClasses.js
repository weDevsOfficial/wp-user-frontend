import { useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../store/constants';

const INPUT_CLASSES = {
    upload_btn: 'file-selector rounded-md wpuf-btn-secondary px-3! py-1.5! text-[13px]! leading-5!',
    radio: 'mt-0! mr-2! wpuf-radio shadow-none! checked:shadow-none! focus:checked:shadow-primary! border-gray-300! checked:border-primary! checked:bg-primary! checked:before:bg-white! hover:checked:bg-primary! focus:ring-transparent! focus:checked:ring-transparent! hover:checked:ring-transparent! focus:checked:bg-primary! focus:checked:shadow-none! focus:shadow-primary',
    checkbox: 'mt-0! mr-2! h-4 w-4 shadow-none! checked:shadow-none! focus:checked:shadow-primary! focus:checked:shadow-none! border-gray-300! checked:border-primary! checked:before:bg-white! hover:checked:bg-primary! focus:ring-transparent! focus:checked:ring-transparent! hover:checked:ring-transparent! focus:checked:bg-primary! focus:shadow-primary checked:focus:bg-primary! checked:hover:bg-primary checked:bg-primary! before:content-none! rounded-sm',
    dropdown: 'block w-full min-w-full bg-white! py-2! px-3! text-gray-700 font-normal leading-none! shadow-xs! border! border-solid! border-gray-300! rounded-md! focus:ring-transparent! focus:checked:ring-transparent! hover:checked:ring-transparent! hover:text-gray-700! text-sm!',
    default: 'block min-w-full bg-white! m-0! leading-none! py-2! px-3! text-sm! text-gray-700 shadow-xs! placeholder:text-gray-400 border! border-solid! border-gray-300! rounded-md! max-w-full focus:ring-transparent!',
};

/**
 * Hook that provides field CSS class utilities.
 * Replaces the Vue form-field.js mixin.
 *
 * @param {Object} field   The field object
 * @param {number} formId  The form post ID; defaults to the form being edited,
 *                         as the Vue mixin's `wpuf_<name>_<form id>` class did
 * @return {Object}
 */
export function useFieldClasses( field, formId ) {
    const postId = useSelect( ( select ) => select( STORE_NAME ).getPost()?.ID, [] );
    formId = formId || postId;
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
