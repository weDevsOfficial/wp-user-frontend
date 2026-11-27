<?php
/**
 * DESCRIPTION: Keeps WPUF Elementor widgets out of Elementor's element cache.
 *
 * @package WPUF\Elementor
 */

namespace WeDevs\Wpuf\Integrations\Elementor;

/**
 * For widgets whose output depends on the visitor (nonces, login state, data only some roles see)
 *
 * @since WPUF_SINCE
 */
trait Per_User_Output {

    /**
     * Always render on the request, never from Elementor's element cache
     *
     * Elementor caches a widget's HTML when its "Cache Settings" is set to Active. A cached
     * WPUF widget would show the first visitor's output (an admin's e-mail column or form
     * nonce) to everyone after, so the widget always renders as a shortcode at request time.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    protected function should_render_shortcode() {
        return (bool) apply_filters( 'elementor/element/should_render_shortcode', false );
    }
}
