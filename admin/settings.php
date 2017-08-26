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

        if ( ! class_exists( 'WeDevs_Settings_API' ) ) {
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
        // Translation issue: Hook name change due to translate menu title
        add_menu_page( __( 'WP User Frontend', 'wpuf' ), __( 'User Frontend', '' ), $capability, 'wp-user-frontend', array($this, 'wpuf_post_forms_page'), 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' ), 55 );

        add_submenu_page( 'wp-user-frontend', __( 'Post Forms', 'wpuf' ), __( 'Post Forms', 'wpuf' ), $capability, 'wpuf-post-forms', array( $this, 'wpuf_post_forms_page' ) );
        remove_submenu_page( 'wp-user-frontend', 'wp-user-frontend' );

        /**
         * @since 2.3
         */
        do_action( 'wpuf_admin_menu_top' );

        if ( !class_exists( 'WeForms' ) ) {
            add_submenu_page( 'wp-user-frontend', __( 'weForms', 'wpuf' ), __( 'Contact Form', 'wpuf' ), $capability, 'wpuf_weforms', array($this, 'weforms_page') );
        }

        add_submenu_page( 'wp-user-frontend', __( 'Subscriptions', 'wpuf' ), __( 'Subscriptions', 'wpuf' ), $capability, 'edit.php?post_type=wpuf_subscription' );

        do_action( 'wpuf_admin_menu' );

        $transactions_page = add_submenu_page( 'wp-user-frontend', __( 'Transactions', 'wpuf' ), __( 'Transactions', 'wpuf' ), $capability, 'wpuf_transaction', array($this, 'transactions_page') );
        add_submenu_page( 'wp-user-frontend', __( 'Add-ons', 'wpuf' ), __( 'Add-ons', 'wpuf' ), $capability, 'wpuf_addons', array($this, 'addons_page') );
        add_submenu_page( 'wp-user-frontend', __( 'Tools', 'wpuf' ), __( 'Tools', 'wpuf' ), $capability, 'wpuf_tools', array($this, 'tools_page') );
        add_submenu_page( 'wp-user-frontend', __( 'Support', 'wpuf' ), __( 'Support', 'wpuf' ), $capability, 'wpuf-support', array($this, 'support_page') );
        add_submenu_page( 'wp-user-frontend', __( 'Settings', 'wpuf' ), __( 'Settings', 'wpuf' ), $capability, 'wpuf-settings', array($this, 'plugin_page') );

        add_action( "load-$transactions_page", array( $this, 'transactions_screen_option' ) );
        add_action( "load-$transactions_page", array( $this, 'enqueue_styles' ) );
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

    function transactions_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/transactions.php';
    }

    /**
     * Callback method for Post Forms submenu
     *
     * @since 2.5
     *
     * @return void
     */
    function wpuf_post_forms_page() {
        $action           = isset( $_GET['action'] ) ? $_GET['action'] : null;
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' );

        switch ( $action ) {
            case 'edit':
                require_once WPUF_ROOT . '/views/post-form.php';
                break;

            case 'add-new':
                require_once WPUF_ROOT . '/views/post-form.php';
                break;

            default:
                require_once WPUF_ROOT . '/admin/post-forms-list-table-view.php';
                break;
        }
    }

    function subscription_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/subscription.php';
    }

    function weforms_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/weforms.php';
    }

    function addons_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/add-ons.php';
    }

    function tools_page() {
        include dirname( dirname( __FILE__ ) ) . '/admin/tools.php';
    }

    function support_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/support.php';
    }

    /**
     * highlight the proper top level menu
     *
     * @link http://wordpress.org/support/topic/moving-taxonomy-ui-to-another-main-menu?replies=5#post-2432769
     *
     * @global obj $current_screen
     *
     * @param string $parent_file
     *
     * @return string
     */
    function fix_parent_menu( $parent_file ) {
        global $current_screen;

        $post_types = array( 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon');

        if ( in_array( $current_screen->post_type, $post_types ) ) {
            $parent_file = 'wp-user-frontend';
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

    /**
     * Screen options.
     *
     * @return void
     */
    public function transactions_screen_option() {
        $option = 'per_page';
        $args   = array(
            'label'   => __( 'Number of items per page:', 'wpuf' ),
            'default' => 20,
            'option'  => 'transactions_per_page'
        );

        add_screen_option( $option, $args );

        if ( ! class_exists( 'WPUF_Transactions_List_Table' ) ) {
            require_once WPUF_ROOT . '/class/transactions-list-table.php';
        }

        $this->transactions_list_table_obj = new WPUF_Transactions_List_Table();
    }

    /**
     * Enqueue styles
     *
     * @return void
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'wpuf-admin', WPUF_ASSET_URI . '/css/admin.css' );
    }
}
