import { __ } from '@wordpress/i18n';

export const RULE_OPTIONS = [
    { label: __( 'All', 'wp-user-frontend' ), value: 'all' },
    { label: __( 'Any', 'wp-user-frontend' ), value: 'any' },
];

export const OPERATORS = {
    radio: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'any selection', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'no selection', 'wp-user-frontend' ), value: '==empty' },
    ],
    text: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'contains', 'wp-user-frontend' ), value: '==contains' },
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
    ],
    number: [
        { label: __( 'is', 'wp-user-frontend' ), value: '=' },
        { label: __( 'is not', 'wp-user-frontend' ), value: '!=' },
        { label: __( 'contains', 'wp-user-frontend' ), value: '==contains' },
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
        { label: __( 'value is greater than', 'wp-user-frontend' ), value: 'greater' },
        { label: __( 'value is less than', 'wp-user-frontend' ), value: 'less' },
    ],
    others: [
        { label: __( 'has any value', 'wp-user-frontend' ), value: '!=empty' },
        { label: __( 'has no value', 'wp-user-frontend' ), value: '==empty' },
    ],
};

/**
 * Get operators for a given input type.
 *
 * @param {string} inputType
 * @return {Array}
 */
export function getOperatorsForType( inputType ) {
    switch ( inputType ) {
        case 'select':
        case 'radio':
        case 'category':
        case 'taxonomy':
        case 'checkbox':
            return OPERATORS.radio;
        case 'text':
        case 'textarea':
        case 'email':
        case 'url':
        case 'password':
            return OPERATORS.text;
        case 'numeric_text':
            return OPERATORS.number;
        default:
            return OPERATORS.others;
    }
}

/**
 * Map field template to input type string.
 *
 * @param {string} template
 * @return {string}
 */
export function templateToInputType( template ) {
    switch ( template ) {
        case 'radio_field':
            return 'radio';
        case 'checkbox_field':
            return 'checkbox';
        case 'dropdown_field':
            return 'select';
        case 'text_field':
            return 'text';
        case 'textarea_field':
            return 'textarea';
        case 'email_address':
            return 'email';
        case 'numeric_text_field':
            return 'numeric_text';
        case 'website_url':
            return 'url';
        case 'password':
            return 'password';
        default:
            return template ? template.replace( '_field', '' ) : '';
    }
}

/**
 * Whether the operator makes the value input unnecessary.
 *
 * @param {string} operator
 * @return {boolean}
 */
export function isEmptyOperator( operator ) {
    return operator === '==empty' || operator === '!=empty';
}

/**
 * Whether the input type should show a dropdown (has discrete options).
 *
 * @param {string} inputType
 * @return {boolean}
 */
export function isDropdownType( inputType ) {
    return [ 'select', 'radio', 'category', 'taxonomy', 'checkbox' ].includes( inputType );
}

/**
 * Get options for a field (from field.options object).
 *
 * @param {Object} field
 * @return {Array<{value: string, label: string}>}
 */
export function getFieldOptions( field ) {
    if ( ! field || ! field.options ) {
        return [];
    }

    const options = [];

    if ( typeof field.options === 'object' && ! Array.isArray( field.options ) ) {
        for ( const key in field.options ) {
            if ( field.options.hasOwnProperty( key ) ) {
                options.push( { value: key, label: field.options[ key ] } );
            }
        }
    }

    return options;
}
