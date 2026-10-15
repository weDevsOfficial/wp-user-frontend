<?php
/**
 * Transient-backed rate limit for the public frontend REST routes
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST;

use WeDevs\Wpuf\Platform\Contracts\Rate_Limiter;

/**
 * Counts hits per key (a guest's IP, a user id) inside a sliding window.
 * The guest submit and upload routes refuse once the window is full, so a
 * scraped form nonce cannot be replayed without limit.
 *
 * @since WPUF_SINCE
 */
class Rate_Limit implements Rate_Limiter {

    /**
     * Transient prefix.
     *
     * @since WPUF_SINCE
     */
    const PREFIX = 'wpuf_rl_';

    /**
     * Whether one more hit on this key is allowed; counts the hit when it is.
     *
     * @since WPUF_SINCE
     *
     * @param string $key    What is limited, e.g. 'submit'
     * @param int    $limit  Hits allowed per window
     * @param int    $window Window in seconds
     *
     * @return bool
     */
    public function allow( $key, $limit = 20, $window = MINUTE_IN_SECONDS ) {
        $limit  = (int) apply_filters( 'wpuf_rate_limit', $limit, $key );
        $window = (int) apply_filters( 'wpuf_rate_limit_window', $window, $key );

        if ( $limit <= 0 ) {
            return true;
        }

        $name  = self::PREFIX . md5( $key . '|' . $this->actor() );
        $state = get_transient( $name );
        $now   = time();

        if ( ! is_array( $state ) || empty( $state['until'] ) || $state['until'] <= $now ) {
            $state = [
                'count' => 0,
                'until' => $now + $window,
            ];
        }

        if ( $state['count'] >= $limit ) {
            return false;
        }

        ++$state['count'];
        set_transient( $name, $state, max( 1, $state['until'] - $now ) );

        return true;
    }

    /**
     * Who is hitting: the user id, or the client address for a visitor.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function actor() {
        if ( is_user_logged_in() ) {
            return 'user:' . get_current_user_id();
        }

        $address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        return 'ip:' . (string) apply_filters( 'wpuf_rate_limit_client_address', $address );
    }
}
