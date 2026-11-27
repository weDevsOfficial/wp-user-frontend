/**
 * Settings API
 * Centralizes the REST calls of the React settings screen; they go through
 * the shared request layer (`@wpuf/api`: nonce and REST root from core,
 * timeout, GET retry on a 5xx, JSON parsing that skips stray PHP output).
 */
import { request } from '@wpuf/api';

const NAMESPACE = '/wpuf/v1';
const ENDPOINT = `${ NAMESPACE }/settings`;

/**
 * Fetch the full settings payload (schema, values, caps, modules).
 *
 * @return {Promise<Object>} API response.
 */
export const fetchSettings = async () => {
    return request( ENDPOINT, {
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
    return request( ENDPOINT, {
        method: 'POST',
        data: { settings: values, extra },
    } );
};
