/**
 * CSS classes that would hide fields on the frontend.
 * These are filtered out in the builder so fields remain visible.
 */
const HIDDEN_CLASSES = [ 'hidden', 'wpuf_hidden_field', 'screen-reader-text' ];

/**
 * Filter CSS classes to prevent hiding fields in the builder.
 *
 * @param {string} cssClasses Space-separated CSS class names
 * @return {string}
 */
export function filterBuilderCssClasses( cssClasses ) {
    if ( ! cssClasses || typeof cssClasses !== 'string' ) {
        return '';
    }

    return cssClasses
        .split( /\s+/ )
        .filter( ( cls ) => cls && ! HIDDEN_CLASSES.includes( cls.toLowerCase() ) )
        .join( ' ' );
}

/**
 * Check if field has CSS classes that would hide it on the frontend.
 *
 * @param {string} cssClasses Space-separated CSS class names
 * @return {boolean}
 */
export function hasHiddenCssClass( cssClasses ) {
    if ( ! cssClasses || typeof cssClasses !== 'string' ) {
        return false;
    }

    const classes = cssClasses.toLowerCase().split( /\s+/ );
    return HIDDEN_CLASSES.some( ( hidden ) => classes.includes( hidden ) );
}
