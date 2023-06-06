<?php

namespace Wp\User\Frontend;

/**
 * The class to handle all the AJAX operations
 */
class Ajax {
    public function __construct() {
        wpuf()->add_to_container( 'address_form_ajax', new Ajax\Address_Form_Ajax() );
        wpuf()->add_to_container( 'admin_form_builder_ajax', new Ajax\Admin_Form_Builder_Ajax() );
        wpuf()->add_to_container( 'upload_ajax', new Ajax\Upload_Ajax() );

        $this->register_ajax( 'submit_post', [ new Ajax\Frontend_Form_Ajax(), 'submit_post' ] );
        $this->register_ajax( 'file_del', [ wpuf()->upload_ajax, 'delete_file' ] );
        $this->register_ajax( 'upload_file', [ wpuf()->upload_ajax, 'upload_file' ] );
        $this->register_ajax( 'insert_image', [ wpuf()->upload_ajax, 'insert_image' ] );
    }

    /**
     * Register ajax into action hook
     *
     * Usage:
     * add_ajax( 'action', 'action_callback' ); // for logged-in and logged-out users
     * add_ajax( 'action', 'action_callback', [ 'nopriv' => false ] ); // for logged in only
     * add_ajax( 'action', 'action_callback', [ 'nopriv' => true, 'priv' => false ] ); // for logged out only
     *
     * @param string $action
     * @param callable $callback
     * @param array $args
     *
     * @return void
     */
    private function register_ajax( $action, callable $callback, $args = [] ) {
        $default = [
            'prefix' => '_wpuf_', // usually this is going to be our plugin slug
            'nopriv' => true,
            'priv'   => true,
        ];

        $args = wp_parse_args( $default, $args );

        if ( $args['priv'] ) {
            add_action( 'wp_ajax' . $args['prefix'] . $action, $callback );
        }

        if ( $args['nopriv'] ) {
            add_action( 'wp_ajax_nopriv' . $args['prefix'] . $action, $callback );
        }
    }

    /**
     * Send json error message
     *
     * @since WPUF_SINCE
     *
     * @param string $error
     */
    public function send_error( $error ) {
        wp_send_json_error(
            [
                'success' => false,
                'error'   => $error,
            ]
        );
    }
}
