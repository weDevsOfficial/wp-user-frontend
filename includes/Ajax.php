<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\Http\Ajax_Abort;
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
        $this->register_ajax( 'wpuf_submit_post', [ $this->service( Ajax\Frontend_Form_Ajax::class ), 'submit_post' ] );
        $this->register_ajax( 'wpuf_file_del', [ $this->service( Ajax\Upload_Ajax::class ), 'delete_file' ] );
        $this->register_ajax( 'wpuf_upload_file', [ $this->service( Ajax\Upload_Ajax::class ), 'upload_file' ] );
        $this->register_ajax( 'wpuf_insert_image', [ $this->service( Ajax\Upload_Ajax::class ), 'insert_image' ] );
        $promotion = $this->admin_service( 'promotion', Admin\Promotion::class );
        $this->register_ajax( 'wpuf_dismiss_promotional_offer_notice', [ $promotion, 'dismiss_promotional_offer' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_dismiss_review_notice', [ $promotion, 'dismiss_review_notice' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_ajax_tag_search', 'wpuf_ajax_tag_search' );
        $this->register_ajax( 'wpuf_dismiss_notice_acf', [ $this->service( Integrations\WPUF_ACF_Compatibility::class ), 'dismiss_notice' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_compatibility_acf', [ $this->service( Integrations\WPUF_ACF_Compatibility::class ), 'maybe_compatible' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_migrate_acf', [ $this->service( Integrations\WPUF_ACF_Compatibility::class ), 'migrate_cf_data' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_ajax_login', [ $this->service( Login_Widget::class ), 'ajax_login' ], $this->logged_out_only );
        $this->register_ajax( 'wpuf_lost_password', [ $this->service( Login_Widget::class ), 'ajax_reset_pass' ], $this->logged_out_only );
        $this->register_ajax( 'wpuf_ajax_logout', [ $this->service( Login_Widget::class ), 'ajax_logout' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_make_media_embed_code', [ $this->service( Frontend\Frontend_Form::class ), 'make_media_embed_code' ] );
        $this->register_ajax( 'wpuf_draft_post', [ $this->service( Frontend\Frontend_Form::class ), 'draft_post' ] );
        $this->register_ajax( 'wpuf_delete_user_package', [ $this->admin_service( 'admin_subscription', Admin\Admin_Subscription::class ), 'delete_user_package' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_address_ajax_action', [ $this->service( Ajax\Address_Form_Ajax::class ), 'ajax_form_action' ] );
        $frontend_account = $this->service( Frontend\Frontend_Account::class );
        $this->register_ajax( 'wpuf_account_update_profile', [ $frontend_account, 'update_profile' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_account_change_password', [ $frontend_account, 'change_password' ], $this->logged_in_only );
        $this->register_ajax( 'wpuf_get_child_cat', 'wpuf_get_child_cats' );
        $this->register_ajax( 'wpuf_ajax_address', 'wpuf_ajax_get_states_field' );
        $this->register_ajax( 'wpuf_update_billing_address', 'wpuf_update_billing_address' );
        $this->register_ajax( 'wpuf_clear_schedule_lock', 'wpuf_clear_schedule_lock', $this->logged_in_only );
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
