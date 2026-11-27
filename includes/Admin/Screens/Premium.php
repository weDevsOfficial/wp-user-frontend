<?php
/**
 * Premium screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * User Frontend > Premium (free only). Load and render moved from Admin\Menu, whose callbacks forward here.
 *
 * @since WPUF_SINCE
 */
class Premium extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_premium';
    }

    /**
     * Load the screen (moved from Admin\Menu).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {
        // Inter is self-hosted via @font-face inside premium.css; no CDN needed.
        wp_enqueue_style(
            'wpuf-premium',
            WPUF_ASSET_URI . '/css/admin/premium.css',
            [],
            WPUF_VERSION
        );

        wp_enqueue_script(
            'wpuf-premium',
            WPUF_ASSET_URI . '/js/admin/premium.js',
            [],
            WPUF_VERSION,
            true
        );
    }

    /**
     * Render the screen (moved from Admin\Menu).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        require_once WPUF_INCLUDES . '/Admin/views/premium.php';
    }
}
