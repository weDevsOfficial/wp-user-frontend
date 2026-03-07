/**
 * Custom hook for fetching forms from the REST API.
 *
 * @since WPUF_SINCE
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * Parse JSON from a response that may contain PHP notices mixed in.
 *
 * @param {string} responseText Raw response body text.
 *
 * @return {Object} Parsed JSON object.
 */
const parseJsonFromResponse = ( responseText ) => {
    try {
        return JSON.parse( responseText );
    } catch ( initialError ) {
        // Extract JSON from HTML response with error notices
        const lines = responseText.split( '\n' );

        // Find complete JSON line
        for ( let i = lines.length - 1; i >= 0; i-- ) {
            const line = lines[ i ].trim();
            if ( line.startsWith( '{' ) && line.endsWith( '}' ) ) {
                return JSON.parse( line );
            }
        }

        // Fallback: extract by brace counting
        let startIndex = -1;
        let braceCount = 0;

        for ( let i = 0; i < responseText.length; i++ ) {
            if ( responseText[ i ] === '{' ) {
                if ( startIndex === -1 ) {
                    startIndex = i;
                }
                braceCount++;
            } else if ( responseText[ i ] === '}' ) {
                braceCount--;
                if ( braceCount === 0 && startIndex !== -1 ) {
                    return JSON.parse( responseText.substring( startIndex, i + 1 ) );
                }
            }
        }

        throw new Error( 'Invalid JSON response from server' );
    }
};

/**
 * Hook to fetch and manage forms list data.
 *
 * @param {Object} options
 * @param {string} options.postType Post type slug. Default 'wpuf_forms'.
 *
 * @return {Object} { forms, loading, pagination, fetchForms }
 */
const useFormsFetch = ( { postType = 'wpuf_forms' } = {} ) => {
    const [ forms, setForms ] = useState( [] );
    const [ loading, setLoading ] = useState( true );
    const [ pagination, setPagination ] = useState( {
        total_pages: 0,
        current_page: 1,
    } );
    const [ error, setError ] = useState( null );

    const fetchForms = useCallback( async ( page = 1, status = 'any', search = '' ) => {
        try {
            setLoading( true );
            setError( null );

            const restApiRoot = ( wpuf_forms_list.rest_url || '' ).replace( /\/$/, '' );
            const params = new URLSearchParams( {
                page: page.toString(),
                per_page: '10',
                status,
                post_type: postType,
            } );

            if ( search ) {
                params.append( 's', search );
            }

            // rest_url may already include 'wpuf/v1' namespace (Pro) or be a bare root (free).
            const hasNamespace = restApiRoot.indexOf( '/wpuf/v1' ) !== -1;
            const apiUrl = hasNamespace
                ? `${ restApiRoot }/wpuf_form?${ params.toString() }`
                : `${ restApiRoot }/wpuf/v1/wpuf_form?${ params.toString() }`;

            const response = await fetch( apiUrl, {
                headers: {
                    'X-WP-Nonce': wpuf_forms_list.rest_nonce,
                },
            } );

            if ( ! response.ok ) {
                throw new Error( `HTTP error! status: ${ response.status }` );
            }

            const responseText = await response.text();
            const data = parseJsonFromResponse( responseText );

            if ( data.success && data.result ) {
                setForms( data.result );
                setPagination( {
                    total_pages: data.pagination?.total_pages || 0,
                    current_page: page,
                } );
            } else {
                setForms( [] );
                setPagination( { total_pages: 0, current_page: 1 } );
            }
        } catch ( err ) {
            setError( err );
            setForms( [] );
            setPagination( { total_pages: 0, current_page: 1 } );
        } finally {
            setLoading( false );
        }
    }, [ postType ] );

    return { forms, loading, pagination, error, fetchForms };
};

export default useFormsFetch;
