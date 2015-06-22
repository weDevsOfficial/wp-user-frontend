<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
class WPUF_Admin_Settings {

    private $settings_api;
    private static $_instance;

    function __construct() {

        if ( !class_exists( 'WeDevs_Settings_API' ) ) {
            require_once dirname( dirname( __FILE__ ) ) . '/lib/class.settings-api.php';
        }

        $this->settings_api = new WeDevs_Settings_API();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );

        add_filter( 'parent_file', array($this, 'fix_parent_menu' ) );

        add_action( 'admin_init', array($this, 'handle_tools_action') );
    }

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Register the admin menu
     *
     * @since 1.0
     */
    function admin_menu() {
        $capability = wpuf_admin_role();

        add_menu_page( __( 'WP User Frontend', 'wpuf' ), __( 'User Frontend', 'wpuf' ), $capability, 'wpuf-admin-opt', array($this, 'plugin_page'), 'dashicons-exerpt-view', 55 );

        /**
         * @since 2.3
         */
        do_action( 'wpuf_admin_menu_top' );

        add_submenu_page( 'wpuf-admin-opt', __( 'Subscription', 'wpuf' ), __( 'Subscription', 'wpuf' ), $capability, 'edit.php?post_type=wpuf_subscription' );

        do_action( 'wpuf_admin_menu' );

        add_submenu_page( 'wpuf-admin-opt', __( 'Transaction', 'wpuf' ), __( 'Transaction', 'wpuf' ), $capability, 'wpuf_transaction', array($this, 'transaction_page') );
        add_submenu_page( 'wpuf-admin-opt', __( 'Add-ons', 'wpuf' ), __( 'Add-ons', 'wpuf' ), $capability, 'wpuf_addons', array($this, 'addons_page') );
        add_submenu_page( 'wpuf-admin-opt', __( 'Tools', 'wpuf' ), __( 'Tools', 'wpuf' ), $capability, 'wpuf_tools', array($this, 'tools_page') );
        add_submenu_page( 'wpuf-admin-opt', __( 'Settings', 'wpuf' ), __( 'Settings', 'wpuf' ), $capability, 'wpuf-settings', array($this, 'plugin_page') );
    }

    /**
     * WPUF Settings sections
     *
     * @since 1.0
     * @return array
     */
    function get_settings_sections() {
        return wpuf_settings_sections();
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        return wpuf_settings_fields();
    }

    function plugin_page() {
        ?>
        <div class="wrap">

            <?php screen_icon( 'options-general' ); ?>

            <?php
            settings_errors();

            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();
            ?>

        </div>
        <?php
    }

    function transaction_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/transaction.php';
    }

    function subscription_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/subscription.php';
    }

    function addons_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/add-ons.php';
    }

    function tools_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/tools.php';
    }

    /**
     * highlight the proper top level menu
     *
     * @link http://wordpress.org/support/topic/moving-taxonomy-ui-to-another-main-menu?replies=5#post-2432769
     * @global obj $current_screen
     * @param string $parent_file
     * @return string
     */
    function fix_parent_menu( $parent_file ) {
        global $current_screen;

        $post_types = array( 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon');

        if ( in_array( $current_screen->post_type, $post_types ) ) {
            $parent_file = 'wpuf-admin-opt';
        }

        return $parent_file;
    }

    /**
     * Hanlde tools page action
     *
     * @return void
     */
    function handle_tools_action() {
        if ( ! isset( $_GET['wpuf_action'] ) ) {
            return;
        }

        check_admin_referer( 'wpuf-tools-action' );

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;

        $action  = $_GET['wpuf_action'];
        $message = 'del_forms';

        switch ($action) {
            case 'clear_settings':
                delete_option( 'wpuf_general' );
                delete_option( 'wpuf_dashboard' );
                delete_option( 'wpuf_profile' );
                delete_option( 'wpuf_payment' );
                delete_option( '_wpuf_page_created' );

                $message = 'settings_cleared';
                break;

            case 'del_post_forms':
                $this->delete_post_type( 'wpuf_forms' );
                break;

            case 'del_pro_forms':
                $this->delete_post_type( 'wpuf_profile' );
                break;

            case 'del_subs':
                $this->delete_post_type( 'wpuf_subscription' );
                break;

            case 'del_coupon':
                $this->delete_post_type( 'wpuf_coupon' );
                break;

            case 'clear_transaction':
                $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wpuf_transaction");

                $message = 'del_trans';
                break;

            default:
                # code...
                break;
        }

        wp_redirect( add_query_arg( array( 'msg' => $message ), admin_url( 'admin.php?page=wpuf_tools&action=tools' ) ) );
        exit;
    }

    /**
     * Delete all posts by a post type
     *
     * @param  string $post_type
     * @return void
     */
    function delete_post_type( $post_type ) {
        $query = new WP_Query( array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft', 'pending', 'trash' )
        ) );

        $posts = $query->get_posts();

        if ( $posts ) {
            foreach ($posts as $item) {
                wp_delete_post( $item->ID, true );
            }
        }
    }
}