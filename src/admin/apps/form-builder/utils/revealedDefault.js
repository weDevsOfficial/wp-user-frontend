/**
 * The value a setting gets when the user reveals it (switches on the setting it
 * depends on) and the field has none stored: what the input shows. The icon is
 * the field type's default icon, as develop's icon selector picked it.
 *
 * @param {Object} optionField Setting definition.
 * @param {Object} field       The field.
 * @since WPUF_SINCE
 *
 * @return {*} The value, or undefined to store nothing.
 */
export function revealedDefault( optionField, field ) {
    const value = field[ optionField.name ];

    if ( 'icon_selector' === optionField.type ) {
        if ( value && 'fas fa-0' !== value ) {
            return undefined;
        }

        const defaultIcons = window.wpuf_form_builder?.defaultIcons || {};

        return defaultIcons[ field.template || field.input_type || 'text' ] || 'fa-solid fa-circle';
    }

    if ( undefined !== value || undefined === optionField.default || '' === optionField.default ) {
        return undefined;
    }

    return optionField.default;
}
