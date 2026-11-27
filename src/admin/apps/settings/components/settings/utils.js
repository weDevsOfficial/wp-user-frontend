/**
 * Shared helpers for the settings screen.
 */

/**
 * Strip HTML tags from a string — used for labels/titles that may embed a Pro
 * badge (`<span class="pro-icon">…`). Returns plain trimmed text; non-strings
 * return an empty string.
 *
 * @param {*} str Value to strip.
 * @return {string} Plain text.
 */
export const stripTags = ( str ) => ( typeof str === 'string' ? str.replace( /<[^>]*>/g, '' ).trim() : '' );
