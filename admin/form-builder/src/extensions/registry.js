const fieldPreviews = {};
const fieldSettingInputs = {};
const fieldValidators = {};

/**
 * Register a field preview component for a given template name.
 *
 * @param {string}   template  Field template name (e.g. 'text_field', 'price_field')
 * @param {Function} Component React component for rendering the preview
 */
export function registerFieldPreview( template, Component ) {
    fieldPreviews[ template ] = Component;
}

/**
 * Register a settings input component for a given input type.
 *
 * @param {string}   type      Input type name (e.g. 'text', 'checkbox', 'option-data')
 * @param {Function} Component React component for rendering the input
 */
export function registerFieldSettingInput( type, Component ) {
    fieldSettingInputs[ type ] = Component;
}

/**
 * Get a field preview component by template name.
 *
 * @param {string} template
 * @return {Function|null}
 */
export function getFieldPreview( template ) {
    return fieldPreviews[ template ] || null;
}

/**
 * Get a settings input component by type.
 *
 * @param {string} type
 * @return {Function|null}
 */
export function getFieldSettingInput( type ) {
    return fieldSettingInputs[ type ] || null;
}

/**
 * Get all registered field preview templates.
 *
 * @return {Object}
 */
export function getAllFieldPreviews() {
    return { ...fieldPreviews };
}

/**
 * Get all registered settings input types.
 *
 * @return {Object}
 */
export function getAllFieldSettingInputs() {
    return { ...fieldSettingInputs };
}

/**
 * Register a field validator callback.
 *
 * @param {string}   callbackName Validator callback name (e.g. 'has_gmap_api_key')
 * @param {Function} fn           Function that returns true if valid, false if not
 */
export function registerFieldValidator( callbackName, fn ) {
    fieldValidators[ callbackName ] = fn;
}

/**
 * Get all registered field validators.
 *
 * @return {Object}
 */
export function getFieldValidators() {
    return fieldValidators;
}
