<?php
/**
 * Hookable contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

/**
 * A platform service that adds WordPress hooks. The bootstrap calls
 * register_hooks() once; constructors add no hooks.
 *
 * @since WPUF_SINCE
 */
interface Hookable {

    /**
     * Add the service's actions and filters.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks();
}
