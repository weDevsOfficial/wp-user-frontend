<?php
/**
 * Runs a form submission with the request the classic admin-ajax submit had
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Http;

use WP_Error;
use WP_Screen;

/**
 * The one boundary between the REST routes / services and the submission
 * engine (the post form handler, the account profile save, Pro's
 * registration and profile handlers).
 *
 * Those handlers, every field class (`prepare_entry()`, free, Pro and third
 * party field types) and the submit hooks other plugins listen on
 * (`wpuf_add_post_after_insert`, `wpuf_edit_post_after_update`,
 * `wpuf_after_register`, Dokan, WooCommerce, WC Vendors ...) read the posted
 * values from `$_POST` / `$_REQUEST` and expect the admin-ajax context the
 * classic forms submitted in (`is_admin()` and `wp_doing_ajax()` true, no
 * front-end request in `$wp`: Dokan saves `chosen_product_cat` only when
 * `is_admin()`, the guest verification link uses `home_url( $wp->request )`).
 * run() gives the handler exactly that request, built from the input, answers
 * the handler's JSON exits as a value (Ajax_Abort) and restores everything
 * afterwards, also when the handler throws.
 *
 * @since WPUF_SINCE
 */
final class Submission_Context {

    /**
     * Nonce action of the frontend forms.
     *
     * @since WPUF_SINCE
     */
    const NONCE_ACTION = 'wpuf_form_add';

    /**
     * Run a submission handler.
     *
     * @since WPUF_SINCE
     *
     * @param array    $input   The posted values (unslashed unless `slashed`)
     * @param callable $handler The handler: returns the payload, or exits through Ajax_Abort
     * @param array    $options `nonce`: the nonce action the input must carry (`_wpnonce`,
     *                          `_ajax_nonce` or `wpuf_nonce`), or false when the caller checked
     *                          its own; `slashed`: true when $input already carries PHP's slashes
     *
     * @return mixed|WP_Error The handler's payload, or the error it answered
     */
    public static function run( array $input, callable $handler, array $options = [] ) {
        $options = wp_parse_args(
            $options,
            [
                'nonce'   => self::NONCE_ACTION,
                'slashed' => false,
            ]
        );

        // The admin-side submission hooks are built for a non-admin request
        // only (boot_submission_hooks() returns inside the admin): build them first.
        wpuf()->boot_submission_hooks();

        // The handlers answer errors through wpuf()->ajax, which the plugin
        // builds for admin-ajax requests only.
        if ( ! isset( wpuf()->ajax ) ) {
            wpuf()->init_ajax();
        }

        return self::in_admin_ajax(
            function () use ( $input, $handler, $options ) {
                return self::with_input(
                    $input,
                    (bool) $options['slashed'],
                    function () use ( $handler, $options ) {
                        if ( $options['nonce'] && ! self::nonce_ok( $options['nonce'] ) ) {
                            return self::expired();
                        }

                        return Ajax_Abort::collect( $handler );
                    }
                );
            }
        );
    }

    /**
     * Whether the request carries a valid nonce for the action.
     *
     * @since WPUF_SINCE
     *
     * @param string $action Nonce action
     *
     * @return bool
     */
    public static function nonce_ok( $action = self::NONCE_ACTION ) {
        foreach ( [ '_wpnonce', '_ajax_nonce', 'wpuf_nonce' ] as $key ) {
            if ( ! empty( $_REQUEST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- this is the nonce check.
                return (bool) wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST[ $key ] ) ), $action ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
        }

        return false;
    }

    /**
     * The answer to an expired or missing nonce.
     *
     * @since WPUF_SINCE
     *
     * @return WP_Error
     */
    public static function expired() {
        return new WP_Error(
            'wpuf_submit_expired',
            __( 'Your session has expired. Please reload the page and try again.', 'wp-user-frontend' ),
            [ 'status' => 403 ]
        );
    }

    /**
     * Fill `$_POST` / `$_REQUEST` from the input for the call, restore them after.
     *
     * @since WPUF_SINCE
     *
     * @param array    $input   Input
     * @param bool     $slashed Whether the input is slashed already
     * @param callable $handler Handler
     *
     * @return mixed
     */
    private static function with_input( array $input, $slashed, callable $handler ) {
        $post    = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- saved to restore.
        $request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- saved to restore.
        $data    = $slashed ? $input : wp_slash( $input );

        // The REST forms send the nonce as `wpuf_nonce`; the handlers read `_wpnonce`.
        if ( isset( $data['wpuf_nonce'] ) && ! isset( $data['_wpnonce'] ) ) {
            $data['_wpnonce'] = $data['wpuf_nonce'];
        }

        // phpcs:disable WordPress.Security.NonceVerification -- the request the handler checks.
        $_POST    = $data;
        $_REQUEST = array_merge( $request, $data );
        // phpcs:enable WordPress.Security.NonceVerification

        try {
            return $handler();
        } finally {
            $_POST    = $post;
            $_REQUEST = $request;
        }
    }

    /**
     * Run a call with the admin-ajax flags and no front-end request; restore after.
     * Inside admin-ajax (the classic forms) it only runs the call.
     *
     * @since WPUF_SINCE
     *
     * @param callable $handler Handler
     *
     * @return mixed
     */
    private static function in_admin_ajax( callable $handler ) {
        if ( is_admin() ) {
            return $handler();
        }

        global $current_screen, $wp;

        if ( ! class_exists( 'WP_Screen' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
        }

        $previous = isset( $current_screen ) ? $current_screen : null;
        $ajax     = '__return_true';
        $request  = $wp instanceof \WP ? [ $wp->request, $wp->query_string ] : null;

        if ( $request ) {
            $wp->request      = '';
            $wp->query_string = '';
        }

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- scoped: restored in finally.
        $current_screen = WP_Screen::get( 'wpuf-form-submission' );
        add_filter( 'wp_doing_ajax', $ajax, 999 );

        try {
            return $handler();
        } finally {
            remove_filter( 'wp_doing_ajax', $ajax, 999 );

            if ( $request ) {
                $wp->request      = $request[0];
                $wp->query_string = $request[1];
            }

            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restores the request's own value.
            $current_screen = $previous;
        }
    }
}
