/**
 * The Pro field alert's message as text: the builder config's
 * `pro_feature_msg` comes wrapped in develop's `<p class="wpuf-...">` markup,
 * which the shared dialog would print as text.
 *
 * @since WPUF_SINCE
 *
 * @param {string} message Configured message (may contain markup).
 *
 * @return {string} Plain text.
 */
export function proMessageText( message ) {
    return String( message || '' ).replace( /<[^>]*>/g, '' ).trim();
}
