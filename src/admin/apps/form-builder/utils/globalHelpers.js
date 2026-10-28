import { generateFieldId } from '../store/reducer';

/**
 * Get a random numeric ID for fields.
 * Matches the pattern from Vue global mixin.
 *
 * @return {number}
 */
export function getRandomId() {
    return generateFieldId();
}

/**
 * Check if reCAPTCHA API keys are configured.
 *
 * @return {boolean}
 */
export function hasRecaptchaApiKeys() {
    const data = window.wpuf_form_builder || {};
    return !! ( data.recaptcha_site && data.recaptcha_secret );
}

/**
 * Check if Turnstile API keys are configured.
 *
 * @return {boolean}
 */
export function hasTurnstileApiKeys() {
    const data = window.wpuf_form_builder || {};
    return !! ( data.turnstile_site && data.turnstile_secret );
}

/**
 * Validate a field template before adding.
 * Checks if the field_settings has a validator callback.
 *
 * @param {string} template      Field template name
 * @param {Object} fieldSettings Field settings object
 * @param {Object} validators    Object mapping validator callback names to functions
 * @return {boolean} True if validation failed
 */
export function isFailedToValidate( template, fieldSettings, validators = {} ) {
    const config = fieldSettings[ template ];

    if ( ! config || ! config.validator || ! config.validator.callback ) {
        return false;
    }

    const callbackName = config.validator.callback;

    if ( typeof validators[ callbackName ] === 'function' ) {
        return ! validators[ callbackName ]();
    }

    return false;
}
