<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Widgets\Login_Widget;

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
        $this->register_ajax( 'wpuf_submit_post', [ new Ajax\Frontend_Form_Ajax(), 'submit_post' ] );
        $this->register_ajax( 'wpuf_file_del', [ new Ajax\Upload_Ajax(), 'delete_file' ] );
        $this->register_ajax( 'wpuf_upload_file', [ new Ajax\Upload_Ajax(), 'upload_file' ] );
        $this->register_ajax( 'wpuf_insert_image', [ new Ajax\Upload_Ajax(), 'insert_image' ] );
        $this->register_ajax( 'wpuf_form_builder_save_form', [ new Ajax\Admin_Form_Builder_Ajax(), 'save_form' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_form_setting_post', [ new Ajax\Admin_Form_Builder_Ajax(), 'wpuf_get_post_taxonomies' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_dismiss_promotional_offer_notice', [ new Admin\Promotion(), 'dismiss_promotional_offer' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_dismiss_review_notice', [ new Admin\Promotion(), 'dismiss_review_notice' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_ajax_tag_search', 'wpuf_ajax_tag_search' );
        $this->register_ajax( 'wpuf_dismiss_notice_acf', [ new Integrations\WPUF_ACF_Compatibility(), 'dismiss_notice' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_compatibility_acf', [ new Integrations\WPUF_ACF_Compatibility(), 'maybe_compatible' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_migrate_acf', [ new Integrations\WPUF_ACF_Compatibility(), 'migrate_cf_data' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_ajax_login', [ new Login_Widget(), 'ajax_login' ], $this->logged_out_only );
        $this->register_ajax( 'wpuf_lost_password', [ new Login_Widget(), 'ajax_reset_pass' ], $this->logged_out_only );
        $this->register_ajax( 'wpuf_ajax_logout', [ new Login_Widget(), 'ajax_logout' ], $this->logged_out_only );
        $this->register_ajax( 'wpuf_form_preview', [ new Frontend\Frontend_Form(), 'preview_form' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_make_media_embed_code', [ new Frontend\Frontend_Form(), 'make_media_embed_code' ] );
        $this->register_ajax( 'wpuf_draft_post', [ new Frontend\Frontend_Form(), 'draft_post' ] );
        $this->register_ajax( 'wpuf_delete_user_package', [ new Admin\Admin_Subscription(), 'delete_user_package' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_address_ajax_action', [ new Ajax\Address_Form_Ajax(), 'ajax_form_action' ] );
        $this->register_ajax( 'wpuf_account_update_profile', [ new Frontend\Frontend_Account(), 'update_profile' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_import_forms', [ new Admin\Admin_Tools(), 'import_forms' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_get_child_cat', 'wpuf_get_child_cats' );
        $this->register_ajax( 'wpuf_ajax_address', 'wpuf_ajax_get_states_field' );
        $this->register_ajax( 'wpuf_update_billing_address', 'wpuf_update_billing_address' );
        $this->register_ajax( 'wpuf_clear_schedule_lock', 'wpuf_clear_schedule_lock', $this->logged_in_only );
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

        $args = wp_parse_args( $default, $args );

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
        wp_send_json_error(
            [
                'success' => false,
                'error'   => $error,
            ]
        );
    }
}
