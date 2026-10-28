/**
 * The account's one history adapter: every section, page and back / forward
 * move goes through here, so the URL (`?section=`, `?pagenum=`) stays the
 * contract and the store's location always matches the address bar.
 *
 * @since WPUF_SINCE
 */

/**
 * @param {string} search   Query string.
 * @param {string} fallback Section when none is given.
 *
 * @return {string} Section slug.
 */
export const sectionOf = ( search, fallback ) => new URLSearchParams( search ).get( 'section' ) || fallback;

/**
 * @param {string} search Query string.
 *
 * @return {number} `?pagenum=`, 1 when missing.
 */
export const pageOf = ( search ) => Number( new URLSearchParams( search ).get( 'pagenum' ) || 1 );

/**
 * The account page URL of a section, with extra query args (empty ones
 * removed).
 *
 * @param {string} pageUrl Account page URL.
 * @param {string} section Section slug.
 * @param {Object} extra   Extra query args.
 *
 * @return {string} URL.
 */
export const urlFor = ( pageUrl, section, extra = {} ) => {
    const url = new URL( pageUrl, window.location.origin );

    url.searchParams.set( 'section', section );
    Object.entries( extra ).forEach( ( [ key, value ] ) => {
        if ( value ) {
            url.searchParams.set( key, value );
        } else {
            url.searchParams.delete( key );
        }
    } );

    return url.toString();
};

/** @return {string} The current query string. */
export const currentSearch = () => window.location.search;

/**
 * Add a history entry.
 *
 * @param {string} url URL.
 */
export const push = ( url ) => window.history.pushState( {}, '', url );

/**
 * Back / forward.
 *
 * @param {Function} callback Called with the new query string.
 *
 * @return {Function} Stops listening.
 */
export const listen = ( callback ) => {
    const onPop = () => callback( currentSearch() );

    window.addEventListener( 'popstate', onPop );

    return () => window.removeEventListener( 'popstate', onPop );
};
