/**
 * wpuf/v1/onboarding requests of the setup wizard.
 *
 * @since WPUF_SINCE
 */
import { request, restPath } from '@wpuf/api';

/**
 * GET (no data) or POST (data) on a wpuf/v1 path.
 *
 * @param {string} path   Route under wpuf/v1, e.g. `/onboarding/features`.
 * @param {Object} [data] POST body.
 *
 * @return {Promise<Object>} Response.
 */
export const api = ( path, data ) => request( restPath( 'wpuf/v1', path ), data ? { method: 'POST', data } : {} );

/**
 * Install and activate one companion plugin.
 *
 * @param {string} slug Recommended plugin slug.
 *
 * @return {Promise<Object>} { slug, installed }
 */
export const installPlugin = ( slug ) => api( '/onboarding/plugins/install', { slug } );
