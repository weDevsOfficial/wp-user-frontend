/**
 * Settings API
 * Centralizes apiFetch calls for the React settings screen.
 */
import apiFetch from '@wordpress/api-fetch';

const NAMESPACE = '/wpuf/v1';
const ENDPOINT = `${ NAMESPACE }/settings`;

/**
 * Fetch the full settings payload (schema, values, caps, modules).
 *
 * @return {Promise<Object>} API response.
 */
export const fetchSettings = async () => {
    return apiFetch( {
        path: ENDPOINT,
        method: 'GET',
    } );
};

/**
 * Save settings. Values are keyed by section id → field name → value, matching
 * the storage contract of the legacy settings screen.
 *
 * @param {Object} values Section-keyed values.
 * @param {Object} extra  Custom own-option payload (tax rates, role templates).
 *
 * @return {Promise<Object>} API response.
 */
export const saveSettings = async ( values, extra = {} ) => {
    return apiFetch( {
        path: ENDPOINT,
        method: 'POST',
        data: { settings: values, extra },
    } );
};
