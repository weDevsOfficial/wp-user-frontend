<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\Http\Ajax_Abort;

/**
 * The class to handle all the AJAX operations
 */
class Ajax {
    /**
     * A predefined array to use when we need to create AJAX actions only for logged in users
     *
     * @var array
     */
    protected $logged_in_only = [ 'nopriv' => false ];

    /**
     * A predefined array to use when we need to create AJAX actions only for logged out users
     *
     * @var array
     */
    protected $logged_out_only = [ 'priv' => false ];

    public function __construct() {
        $frontend_account = $this->service( Frontend\Frontend_Account::class );
        $this->register_ajax( 'wpuf_account_update_profile', [ $frontend_account, 'update_profile' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_account_change_password', [ $frontend_account, 'change_password' ], $this->logged_in_only );
    }

    /**
     * A frontend class as the platform container shares it (the frontend layer's
     * instance when that is loaded), so its constructor hooks are registered once.
     *
     * @since WPUF_SINCE
     *
     * @param string $class_name Class (see Platform\Providers\FrontendServiceProvider)
     *
     * @return object
     */
    private function service( $class_name ) {
        if ( function_exists( 'wpuf' ) && wpuf()->platform()->has( $class_name ) ) {
            return wpuf()->platform()->get( $class_name );
        }

        return new $class_name();
    }

    /**
     * An admin service as the admin layer built it, so its constructor hooks are
     * not registered a second time; a new one when the admin layer is not loaded.
     *
     * @since WPUF_SINCE
     *
     * @param string $key        Key in wpuf()->admin
     * @param string $class_name Class to build otherwise
     *
     * @return object
     */
    private function admin_service( $key, $class_name ) {
        $admin = function_exists( 'wpuf' ) ? wpuf()->admin : null;

        return is_object( $admin ) && $admin->{$key} instanceof $class_name ? $admin->{$key} : new $class_name();
    }

    /**
     * Register ajax into action hook
     *
     * Usage:
     * register_ajax( 'action', 'action_callback' ); // for logged-in and logged-out users
     * register_ajax( 'action', 'action_callback', [ 'nopriv' => false ] ); // for logged-in users only
     * register_ajax( 'action', 'action_callback', [ 'nopriv' => true, 'priv' => false ] ); // for logged-out users only
     *
     * @param string $action
     * @param callable|string $callback
     * @param array $args
     *
     * @return void
     */
    public function register_ajax( $action, $callback, $args = [] ) {
        $default = [
            'nopriv'        => true,
            'priv'          => true,
            'priority'      => 10,
            'accepted_args' => 1,
        ];

        // Caller args override the defaults (they were swapped, so logged_in_only /
        // logged_out_only were ignored and every action was registered both ways).
        $args = wp_parse_args( $args, $default );

        if ( $args['priv'] ) {
            add_action( 'wp_ajax_' . $action, $callback, $args['priority'], $args['accepted_args'] );
        }

        if ( $args['nopriv'] ) {
            add_action( 'wp_ajax_nopriv_' . $action, $callback, $args['priority'], $args['accepted_args'] );
        }
    }

    /**
     * Send json error message
     *
     * @since 4.0.0
     *
     * @param string $error
     */
    public function send_error( $error ) {
        Ajax_Abort::send_error(
            [
                'success' => false,
                'error'   => $error,
            ]
        );
    }
}
