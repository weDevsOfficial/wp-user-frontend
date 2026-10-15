<?php
/**
 * Rate limiter contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

/**
 * Counts requests per actor (user or IP) and key in a time window, for the
 * public REST routes. Resolved from the container by this interface.
 *
 * @since WPUF_SINCE
 */
interface Rate_Limiter {

    /**
     * Count one request; false once the actor passed the limit.
     *
     * @since WPUF_SINCE
     *
     * @param string $key    What is limited
     * @param int    $limit  Requests allowed in the window
     * @param int    $window Window in seconds
     *
     * @return bool
     */
    public function allow( $key, $limit = 20, $window = MINUTE_IN_SECONDS );

    /**
     * Who is counted: the user id, or a hash of the IP.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function actor();
}
