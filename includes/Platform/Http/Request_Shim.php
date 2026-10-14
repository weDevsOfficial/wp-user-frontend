<?php
/**
 * Runs a legacy request handler with the request superglobals filled from a service input
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Http;

/**
 * The legacy submit handlers (free post form, Pro registration and profile)
 * and dozens of their listeners read `$_POST` directly. A service that wants
 * to run them for a REST request fills `$_POST` / `$_REQUEST` from its input
 * for the duration of the call, slashed the way PHP delivers a form post,
 * and restores them afterwards. The REST payload is never trusted more than
 * a form post was: the same nonce, sanitising and capability checks run.
 *
 * @since WPUF_SINCE
 */
trait Request_Shim {

    /**
     * Run a handler with the service input in place of the request superglobals.
     *
     * REST clients send the form nonce as `wpuf_nonce` (`_wpnonce` is the REST
     * cookie nonce); the legacy handlers read `_wpnonce`, so it is copied.
     *
     * @since WPUF_SINCE
     *
     * @param array    $input   Input
     * @param bool     $slashed Whether $input already carries PHP's slashes ($_POST itself)
     * @param callable $handler Handler
     *
     * @return mixed What the handler returned
     */
    protected function with_input( array $input, $slashed, callable $handler ) {
        $post    = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $data = $slashed ? $input : wp_slash( $input );

        if ( isset( $data['wpuf_nonce'] ) && ! isset( $data['_wpnonce'] ) ) {
            $data['_wpnonce'] = $data['wpuf_nonce'];
        }

        $_POST    = $data;
        $_REQUEST = array_merge( $request, $data );

        // The legacy handlers answer errors through wpuf()->ajax, which the
        // plugin only builds for admin-ajax requests.
        if ( ! isset( wpuf()->ajax ) ) {
            wpuf()->init_ajax();
        }

        try {
            return $handler();
        } finally {
            $_POST    = $post;
            $_REQUEST = $request;
        }
    }

    /**
     * The `wpuf_form_add` nonce of the current input.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    protected function nonce_ok() {
        $nonce = '';

        foreach ( [ '_wpnonce', '_ajax_nonce', 'wpuf_nonce' ] as $key ) {
            if ( ! empty( $_REQUEST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- this is the nonce check.
                $nonce = sanitize_key( wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                break;
            }
        }

        return (bool) wp_verify_nonce( $nonce, 'wpuf_form_add' );
    }

    /**
     * The error for a missing or stale nonce.
     *
     * @since WPUF_SINCE
     *
     * @return \WP_Error
     */
    protected function expired() {
        return new \WP_Error(
            'wpuf_submit_expired',
            __( 'Your session has expired. Please reload the page and try again.', 'wp-user-frontend' ),
            [ 'status' => 403 ]
        );
    }
}
