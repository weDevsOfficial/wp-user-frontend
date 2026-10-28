/**
 * JSON body parsing that survives stray output.
 *
 * Another plugin's deprecation notice (display_errors on), whitespace after
 * a closing PHP tag or a stray echo can land in front of a JSON answer. The
 * server drops it for `wpuf/v1` and `wpuf_*` AJAX answers
 * (`Platform\Http\JsonOutputGuard`); this is the client-side half for what
 * still gets through (output printed before the plugin loads, another
 * site's REST answer).
 */

/**
 * How many `{` / `[` positions are tried when looking for the JSON tail.
 */
export const MAX_JSON_CANDIDATES = 64;

/**
 * Parse a JSON document, or the JSON document at the end of noisy text.
 *
 * @param {string} text Response body.
 *
 * @return {*} Parsed value.
 * @throws {SyntaxError} When the text holds no JSON document.
 */
export function parseJsonBody( text ) {
    const body = String( text ?? '' );

    try {
        return JSON.parse( body );
    } catch ( error ) {
        let tries = 0;

        for ( let at = 0; at < body.length && tries < MAX_JSON_CANDIDATES; at++ ) {
            const char = body[ at ];

            if ( '{' !== char && '[' !== char ) {
                continue;
            }

            tries++;

            try {
                return JSON.parse( body.slice( at ) );
            } catch ( ignored ) {
                // Not the start of the document; try the next brace.
            }
        }

        throw error;
    }
}

/**
 * Whether a value looks like a fetch Response (status + text()).
 *
 * @param {*} value Value.
 *
 * @return {boolean} Whether it is response-like.
 */
export function isResponseLike( value ) {
    return !! value && 'object' === typeof value && 'number' === typeof value.status && 'function' === typeof value.text;
}

/**
 * Read a Response body as JSON, tolerating stray output in front of it.
 *
 * An empty body or a 204 gives null (as `@wordpress/api-fetch`).
 *
 * @param {Response} response Response.
 *
 * @return {Promise<*>} Parsed body.
 * @throws {{code: string, message: string}} `invalid_json` when the body holds no JSON.
 */
export async function readJsonResponse( response ) {
    if ( 204 === response.status ) {
        return null;
    }

    const text = await response.text();

    if ( '' === text.trim() ) {
        return null;
    }

    try {
        return parseJsonBody( text );
    } catch ( error ) {
        throw { code: 'invalid_json', message: 'The response is not a valid JSON response.', status: response.status };
    }
}
