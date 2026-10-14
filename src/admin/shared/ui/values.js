/**
 * Value rules of the shared/ui controls (kept pure for tests). Stored values
 * stay as typed: no `||` fallbacks, `''` is a real value (components.md).
 */

/**
 * Whether a key press may go into a number input that does not allow
 * negative numbers (develop blocks `-`).
 *
 * @param {string}  key           KeyboardEvent.key.
 * @param {boolean} allowNegative Negative numbers allowed.
 *
 * @return {boolean} Allowed.
 */
export function numberKeyAllowed( key, allowNegative ) {
    return allowNegative || '-' !== key;
}

/**
 * The value a Select shows: the stored value when it is one of the options,
 * else nothing (the placeholder). The stored value is never rewritten here.
 *
 * @param {*}     value   Stored value.
 * @param {Array} options [{ value, label }].
 *
 * @return {string|null} Option value or null.
 */
export function selectShownValue( value, options ) {
    if ( null === value || undefined === value ) {
        return null;
    }

    const wanted = String( value );

    return ( options || [] ).some( ( option ) => String( option.value ) === wanted ) ? wanted : null;
}

/**
 * Options in one shape: arrays of { value, label } or a { value: label } map.
 *
 * @param {Array|Object} options Options.
 *
 * @return {Array} [{ value: string, label: string, disabled?: boolean }]
 */
export function normalizeOptions( options ) {
    if ( Array.isArray( options ) ) {
        return options.map( ( option ) => ( 'object' === typeof option && null !== option
            ? { ...option, value: String( option.value ), label: String( option.label ?? option.value ) }
            : { value: String( option ), label: String( option ) } ) );
    }

    return Object.keys( options || {} ).map( ( key ) => ( { value: key, label: String( options[ key ] ) } ) );
}

/**
 * Whether a two-state control is on. The stored shape differs per screen
 * (`'on'/'off'`, `'yes'/''`, `true/false`, key absent), so the caller says
 * which value means on.
 *
 * @param {*} value        Stored value.
 * @param {*} checkedValue The value that means on.
 *
 * @return {boolean} On.
 */
export function isChecked( value, checkedValue ) {
    return value === checkedValue || ( true === checkedValue && 'true' === value );
}

/**
 * A list value as an array of strings (falsy -> []), for MultiSelect.
 *
 * @param {*} value Stored value.
 *
 * @return {string[]} Values.
 */
export function toList( value ) {
    if ( ! value ) {
        return [];
    }

    return ( Array.isArray( value ) ? value : [ value ] ).map( String );
}

/**
 * `YYYY-MM-DD` (optionally with a time part) -> local Date at noon; anything else -> undefined.
 *
 * @param {string} value Stored date.
 *
 * @return {Date|undefined} Date.
 */
export function ymdToDate( value ) {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec( String( value || '' ) );

    if ( ! match ) {
        return undefined;
    }

    // Local noon: formatting in the site timezone (up to +/-12h away) keeps the same day.
    const date = new Date( Number( match[ 1 ] ), Number( match[ 2 ] ) - 1, Number( match[ 3 ] ), 12 );

    return Number.isNaN( date.getTime() ) ? undefined : date;
}

/**
 * Local Date -> `YYYY-MM-DD` ('' when none). No timezone shift.
 *
 * @param {Date|undefined} date Date.
 *
 * @return {string} Date string.
 */
export function dateToYmd( date ) {
    if ( ! ( date instanceof Date ) || Number.isNaN( date.getTime() ) ) {
        return '';
    }

    const pad = ( n ) => String( n ).padStart( 2, '0' );

    return `${ date.getFullYear() }-${ pad( date.getMonth() + 1 ) }-${ pad( date.getDate() ) }`;
}

/**
 * Option keys a setting sends as an "empty list" message, not as a choice
 * (Pro View Control's `no_packs` when no subscription pack exists).
 *
 * @since WPUF_SINCE
 */
export const PLACEHOLDER_OPTION_KEYS = [ 'no_packs' ];

/**
 * Splits a multiselect's options into the real choices and the message of a
 * placeholder-only list ("No subscription packs available"), which is shown
 * as a note, never ticked or stored.
 *
 * @since WPUF_SINCE
 *
 * @param {Array} options [{ value, label }].
 *
 * @return {{ choices: Array, empty: string }} Choices, and the note ('' when none).
 */
export function multiSelectChoices( options ) {
    const list = options || [];
    const choices = list.filter( ( option ) => ! PLACEHOLDER_OPTION_KEYS.includes( String( option.value ) ) );
    const placeholder = list.find( ( option ) => PLACEHOLDER_OPTION_KEYS.includes( String( option.value ) ) );

    return { choices, empty: ! choices.length && placeholder ? String( placeholder.label ) : '' };
}
