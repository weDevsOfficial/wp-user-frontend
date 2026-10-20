/**
 * Request layer for the WPUF React admin.
 *
 * A wrapper over `@wordpress/api-fetch`, not a global middleware: WordPress
 * core already sets the REST root and nonce on `wp-api-fetch`, and a global
 * `apiFetch.use()` would also change errors, timeouts and retries for core
 * and other plugins on the same page (design.md D24).
 *
 * - 30 s timeout per attempt (chained to the caller's signal);
 * - GET/HEAD retried on a 5xx (twice, short back-off), never a mutation;
 * - the body is read as text and parsed with `parseJsonBody()`, so stray
 *   output in front of the JSON (another plugin's notice) does not break it;
 * - every failure rejects with `{ code, message, status, data }`.
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import { isResponseLike, readJsonResponse } from './parse';

export const REQUEST_TIMEOUT_MS = 30000;
export const MAX_RETRIES = 2;

/**
 * @typedef {Object} ApiError
 * @property {string} code    Error code (`wpuf_*`, WP REST code, `timeout`, `network`).
 * @property {string} message Human readable message.
 * @property {number} status  HTTP status (0 when the request never got one).
 * @property {*}      [data]  Extra data from the response.
 */

/**
 * Normalize anything apiFetch can throw.
 *
 * @param {*} raw Thrown value.
 *
 * @return {ApiError} Normalized error.
 */
export function normalizeError( raw ) {
    if ( raw && 'object' === typeof raw && 'code' in raw && 'status' in raw && 'message' in raw && 'number' === typeof raw.status ) {
        return raw;
    }

    if ( raw && 'AbortError' === raw.name ) {
        // The browser's own text ("signal is aborted without reason") means nothing to a person.
        return { code: 'timeout', message: __( 'The server took too long to answer. Reload the page to try again.', 'wp-user-frontend' ), status: 0 };
    }

    const status = Number( raw?.data?.status ?? raw?.status ?? 0 ) || 0;

    return {
        code: String( raw?.code || ( status ? 'http_' + status : 'network' ) ),
        message: String( raw?.message || 'The request failed.' ),
        status,
        data: raw?.data,
    };
}

/**
 * Build a REST path: `restPath( 'wpuf/v1', '/forms', { page: 2 } )`.
 *
 * @param {string} namespace REST namespace.
 * @param {string} path      Route.
 * @param {Object} [query]   Query args.
 *
 * @return {string} Path for apiFetch.
 */
export function restPath( namespace, path, query ) {
    const base = '/' + String( namespace ).replace( /^\/+|\/+$/g, '' ) + '/' + String( path ).replace( /^\/+/, '' );

    return query ? addQueryArgs( base, query ) : base;
}

const wait = ( ms ) => new Promise( ( resolve ) => setTimeout( resolve, ms ) );

/**
 * Read a response the way apiFetch would, but through the tolerant parser.
 *
 * apiFetch is called with `parse: false`, so it hands back the Response and
 * throws it on a non-2xx status. A WP REST error body (`{ code, message,
 * data: { status } }`) is thrown as is; any other failed body becomes
 * `{ code: 'http_<status>', message, data: { status } }`.
 *
 * @param {Response} response  Response.
 * @param {boolean}  [failed]  Whether apiFetch threw it (non-2xx).
 *
 * @return {Promise<*>} Parsed body.
 */
async function readResponse( response, failed = false ) {
    let body;

    try {
        body = await readJsonResponse( response );
    } catch ( error ) {
        if ( ! failed ) {
            throw error;
        }

        body = null;
    }

    if ( ! failed ) {
        return body;
    }

    if ( body && 'object' === typeof body && 'code' in body ) {
        throw body;
    }

    throw {
        code: 'http_' + response.status,
        message: ( body && body.message ) || response.statusText || 'The request failed.',
        data: { status: response.status },
    };
}

/**
 * Call a REST route.
 *
 * @param {string} path              Route (see restPath()).
 * @param {Object} [options]         apiFetch options (method, data, body, headers, signal, parse).
 * @param {Object} [options.query]   Query args added to the path.
 * @param {boolean} [options.parse]  false hands back the raw Response (apiFetch's contract).
 *
 * @return {Promise<*>} Response body.
 */
export async function request( path, options = {} ) {
    const { query, signal: callerSignal, parse = true, ...rest } = options;
    const method = String( rest.method || 'GET' ).toUpperCase();
    const idempotent = 'GET' === method || 'HEAD' === method;
    const url = query ? addQueryArgs( path, query ) : path;

    const once = () => {
        const controller = new AbortController();
        const timer = setTimeout( () => controller.abort(), REQUEST_TIMEOUT_MS );

        if ( callerSignal ) {
            if ( callerSignal.aborted ) {
                controller.abort();
            } else {
                callerSignal.addEventListener( 'abort', () => controller.abort(), { once: true } );
            }
        }

        const call = Promise.resolve( apiFetch( { ...rest, method, path: url, signal: controller.signal, parse: false } ) )
            .then( ( response ) => ( parse && isResponseLike( response ) ? readResponse( response ) : response ) )
            .catch( ( raw ) => {
                if ( parse && isResponseLike( raw ) ) {
                    return readResponse( raw, true );
                }

                throw raw;
            } );

        return call.finally( () => clearTimeout( timer ) );
    };

    for ( let attempt = 0; ; attempt++ ) {
        try {
            return await once();
        } catch ( raw ) {
            const error = normalizeError( raw );
            const retry = idempotent && error.status >= 500 && attempt < MAX_RETRIES && ! callerSignal?.aborted;

            if ( ! retry ) {
                throw error;
            }

            await wait( 300 * ( attempt + 1 ) );
        }
    }
}
