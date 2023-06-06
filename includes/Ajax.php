<?php

namespace Wp\User\Frontend;

use Wp\User\Frontend\Ajax\Admin_Form_Builder_Ajax;

/**
 * The class to handle all the AJAX operations
 */
class Ajax {
    public function __construct() {
        wpuf()->add_to_container( 'address_form_ajax', new Ajax\Address_Form_Ajax() );
        wpuf()->add_to_container( 'admin_form_builder_ajax', new Ajax\Admin_Form_Builder_Ajax() );

        $this->register_ajax( 'submit_post', [ new Ajax\Frontend_Form_Ajax(), 'submit_post' ] );
        $this->register_ajax( 'file_del', [ new Ajax\Upload_Ajax(), 'delete_file' ] );
        $this->register_ajax( 'upload_file', [ new Ajax\Upload_Ajax(), 'upload_file' ] );
        $this->register_ajax( 'insert_image', [ new Ajax\Upload_Ajax(), 'insert_image' ] );
        $this->register_ajax( 'form_builder_save_form', [ new Admin_Form_Builder_Ajax(), 'save_form' ], [ 'nopriv' => false ] );
        $this->register_ajax( 'form_setting_post', [ new Admin_Form_Builder_Ajax(), 'wpuf_get_post_taxonomies' ], [ 'nopriv' => false ] );

        $this->register_ajax( 'clear_schedule_lock', [ $this, 'clear_schedule_lock' ], [ 'nopriv' => false ] );
    }

    /**
     * Clear Schedule lock
     *
     * @since 3.0.2
     */
    public function clear_schedule_lock() {
        check_ajax_referer( 'wpuf_nonce', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : '';

        if ( ! empty( $post_id ) ) {
            update_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', '' );
            update_post_meta( $post_id, '_wpuf_lock_editing_post', 'no' );
        }
        exit;
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
