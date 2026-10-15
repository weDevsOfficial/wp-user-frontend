/**
 * REST client of the classic scripts: the forms rendered from their
 * templates (theme overrides, the post form block, Elementor widgets), the
 * Login widget, the billing section, the subscription pages and the admin
 * notices. Every call goes to a `wpuf/v1` route with the `wp_rest` nonce,
 * from the `rest` object the script was localized with
 * (wpuf_rest_script_data(): `{ root, nonce }`). No dependencies (fetch).
 *
 * @since WPUF_SINCE
 */
( function ( window ) {
    'use strict';

    /**
     * Call a `wpuf/v1` route.
     *
     * @param {Object}                  rest            `{ root, nonce }`
     * @param {string}                  path            Route under `wpuf/v1/`, e.g. `forms/12/drafts`
     * @param {Object}                  [options]
     * @param {string}                  [options.method] HTTP method (GET)
     * @param {Object|string|FormData}  [options.data]   Body: an object (sent as JSON), a serialized
     *                                                   form string, or FormData; GET sends an object
     *                                                   as the query string
     * @return {Promise<*>} Resolves with the response body, rejects with `{ code, message, data }`
     */
    function request( rest, path, options ) {
        var settings = options || {};
        var method = ( settings.method || 'GET' ).toUpperCase();
        var url = rest.root + String( path ).replace( /^\//, '' );
        var headers = { 'X-WP-Nonce': rest.nonce, Accept: 'application/json' };
        var body;
        var data = settings.data;

        if ( 'GET' === method ) {
            if ( data && 'object' === typeof data ) {
                url += ( -1 === url.indexOf( '?' ) ? '?' : '&' ) + new window.URLSearchParams( data ).toString();
            }
        } else if ( 'string' === typeof data ) {
            body = data;
            headers[ 'Content-Type' ] = 'application/x-www-form-urlencoded; charset=UTF-8';
        } else if ( data instanceof window.FormData ) {
            body = data;
        } else if ( data ) {
            body = JSON.stringify( data );
            headers[ 'Content-Type' ] = 'application/json';
        }

        return window.fetch( url, { method: method, headers: headers, body: body, credentials: 'same-origin' } ).then( function ( response ) {
            return response.text().then( function ( text ) {
                var parsed = null;

                try {
                    parsed = text ? JSON.parse( text ) : null;
                } catch ( e ) {
                    parsed = null;
                }

                if ( response.ok ) {
                    return parsed;
                }

                throw parsed && parsed.message ? parsed : {
                    code: 'http_' + response.status,
                    message: response.statusText || 'The request failed.',
                    data: { status: response.status },
                };
            } );
        } );
    }

    /**
     * A serialized form for a REST route: the form's own nonce (`_wpnonce`,
     * which REST reads as its cookie nonce) travels as `wpuf_nonce`, and the
     * admin-ajax `action` field is dropped.
     *
     * @param {string} serialized A form serialized as a query string
     * @return {string} Body
     */
    function formBody( serialized ) {
        return String( serialized || '' )
            .split( '&' )
            .filter( function ( pair ) {
                return pair && 0 !== pair.indexOf( 'action=' );
            } )
            .map( function ( pair ) {
                return 0 === pair.indexOf( '_wpnonce=' ) ? 'wpuf_nonce=' + pair.slice( 9 ) : pair;
            } )
            .join( '&' );
    }

    window.wpufRest = {
        request: request,
        formBody: formBody,
    };
} )( window );
