/**
 * Settings options as an ordered list for the Select wrapper. PHP sends
 * `options` as a JSON object; JavaScript lists integer-like keys (page ids,
 * term ids) before other keys, which moved develop's leading "-- Select --"
 * (key '') after the pages. The empty key goes first again, as develop
 * rendered it; the other options keep their order.
 *
 * @param {Object|Array} options Field options.
 * @return {Array<{value: string, label: string}>|Array} Ordered options.
 */
export function orderedOptions( options ) {
    if ( ! options || Array.isArray( options ) || 'object' !== typeof options ) {
        return options || [];
    }

    const entries = Object.keys( options ).map( ( key ) => ( { value: key, label: String( options[ key ] ) } ) );
    const empty = entries.filter( ( entry ) => '' === entry.value );

    return [ ...empty, ...entries.filter( ( entry ) => '' !== entry.value ) ];
}

/**
 * Whether the options have an empty-key item ("-- Select --"): a stored ''
 * then shows that item, else the "- Select -" placeholder.
 *
 * @param {Object|Array} options Field options.
 * @return {boolean} Has an option with the value ''.
 */
export function hasEmptyOption( options ) {
    return orderedOptions( options ).some( ( option ) => '' === String( option && 'object' === typeof option ? option.value : option ) );
}
