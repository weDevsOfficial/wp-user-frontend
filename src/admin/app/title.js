/**
 * The screen part of the document title ("Title ‹ Site ‹ WordPress"): the
 * admin app swaps it on every route.
 *
 * @since WPUF_SINCE
 *
 * @param {string} title Screen title.
 */
export default function setTitle( title ) {
    if ( title ) {
        document.title = [ title, ...document.title.split( ' ‹ ' ).slice( 1 ) ].join( ' ‹ ' );
    }
}
