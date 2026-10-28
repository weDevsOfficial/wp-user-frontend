<?php
/**
 * REST route contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

/**
 * A platform service that registers REST routes. The REST manager (task 2.2)
 * calls register_routes() on `rest_api_init`.
 *
 * @since WPUF_SINCE
 */
interface RestRoute {

    /**
     * Register the service's routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes();
}
