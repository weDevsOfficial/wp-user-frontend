<?php
/**
 * Notice wrapper for screens
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * Prints the notices the registry captured for this screen.
 *
 * @since WPUF_SINCE
 */
trait PrintsNotices {

    /**
     * Print captured notices (nothing when none were captured).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function print_notices() {
        wpuf()->platform()->get( Registry::class )->print_notices();
    }
}
