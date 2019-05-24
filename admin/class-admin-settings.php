<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
class WPUF_Admin_Settings {

    /**
     * Settings API
     *
     * @var \WeDevs_Settings_API
     */
    private $settings_api;

    /**
     * Static instance of this class
     *
     * @var \self
     */
    private static $_instance;

    /**
     * public instance of this class
     *
     * @var \self
     */
    public $subscribers_list_table_obj;

    /**
     * The menu page hooks
     *
     * Used for checking if any page is under WPUF menu
     *
     * @var array
     */
    private $menu_pages = array();

    public function __construct() {

        if ( ! class_exists( 'WeDevs_Settings_API' ) ) {
            require_once dirname( dirname( __FILE__ ) ) . '/lib/class.settings-api.php';
        }

        $this->settings_api = new WeDevs_Settings_API();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );

        add_filter( 'parent_file', array($this, 'fix_parent_menu' ) );
        add_filter( 'submenu_file', array($this, 'fix_submenu_file' ) );

        add_action( 'admin_init', array($this, 'handle_tools_action') );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
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
        global $_registered_pages;

        $capability = wpuf_admin_role();

        // Translation issue: Hook name change due to translate menu title
        $this->menu_pages[] = add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', '' ), $capability, 'wp-user-frontend', array($this, 'wpuf_post_forms_page'), 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' ), 55 );

        $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Post Forms', 'wp-user-frontend' ), __( 'Post Forms', 'wp-user-frontend' ), $capability, 'wpuf-post-forms', array( $this, 'wpuf_post_forms_page' ) );
        remove_submenu_page( 'wp-user-frontend', 'wp-user-frontend' );

        /**
         * @since 2.3
         */
        do_action( 'wpuf_admin_menu_top' );

        if ( !class_exists( 'WeForms' ) ) {
            $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'weForms', 'wp-user-frontend' ), __( 'Contact Form', 'wp-user-frontend' ), $capability, 'wpuf_weforms', array($this, 'weforms_page') );
        }
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Subscriptions', 'wp-user-frontend' ), __( 'Subscriptions', 'wp-user-frontend' ), $capability, 'edit.php?post_type=wpuf_subscription' );
        }

            do_action( 'wpuf_admin_menu' );

        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $transactions_page  = add_submenu_page( 'wp-user-frontend', __( 'Transactions', 'wp-user-frontend' ), __( 'Transactions', 'wp-user-frontend' ), $capability, 'wpuf_transaction', array($this, 'transactions_page') );
        }

        $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Tools', 'wp-user-frontend' ), __( 'Tools', 'wp-user-frontend' ), $capability, 'wpuf_tools', array($this, 'tools_page') );

        do_action( 'wpuf_admin_menu_bottom' );

        if ( !class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Premium', 'wp-user-frontend' ), __( 'Premium', 'wp-user-frontend' ), $capability, 'wpuf_premium', array($this, 'premium_page') );
        }
        $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Help', 'wp-user-frontend' ), __( '<span style="color:#f18500">Help</span>', 'wp-user-frontend' ), $capability, 'wpuf-support', array($this, 'support_page') );
        $this->menu_pages[] = add_submenu_page( 'wp-user-frontend', __( 'Settings', 'wp-user-frontend' ), __( 'Settings', 'wp-user-frontend' ), $capability, 'wpuf-settings', array($this, 'plugin_page') );

        $this->menu_pages[] = add_submenu_page( 'edit.php?post_type=wpuf_subscription', __( 'Subscribers', 'wp-user-frontend' ), __( 'Subscribers', 'wp-user-frontend' ), $capability, 'wpuf_subscribers', array($this, 'subscribers_page') );
        $_registered_pages['user-frontend_page_wpuf_subscribers'] = true; // hack to work the nested subscribers page

        // manually add subsription page
        $this->menu_pages[] = 'edit-wpuf_subscription';
        $this->menu_pages[] = 'wpuf_subscribers';
        $this->menu_pages[] = 'user-frontend_page_wpuf_transaction';
        if ( 'on' == wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            add_action( "load-$transactions_page", array( $this, 'transactions_screen_option' ) );
            // add_action( "load-wpuf_subscribers", array( $this, 'subscribers_screen_option' ) );
        }
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

            <h2 style="margin-bottom: 15px;"><?php _e( 'Settings', 'wp-user-frontend' ) ?></h2>
            <div class="wpuf-settings-wrap">
                <?php
                settings_errors();

                $this->settings_api->show_navigation();
                $this->settings_api->show_forms();
                ?>
            </div>
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

    function subscribers_page($post_ID) {
        include dirname( dirname( __FILE__ ) ) . '/admin/subscribers.php';
    }

    function weforms_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/weforms.php';
    }

    function premium_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/premium.php';
    }

    function tools_page() {
        include dirname( dirname( __FILE__ ) ) . '/admin/tools.php';
    }

    function support_page() {
        require_once dirname( dirname( __FILE__ ) ) . '/admin/html/support.php';
    }

    /**
     * Check if the current page is a settings/menu page
     *
     * @param  string  $screen_id
     *
     * @return boolean
     */
    public function is_admin_menu_page( $screen ) {
        if ( $screen && in_array( $screen->id, $this->menu_pages ) ) {
            return true;
        }

        return false;
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
        $current_screen = get_current_screen();

        $post_types = array( 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon');

        if ( in_array( $current_screen->post_type, $post_types ) ) {
            $parent_file = 'wp-user-frontend';
        }

        if ( 'wpuf_subscription' == $current_screen->post_type && $current_screen->base == 'admin_page_the-slug' ) {
            $parent_file = 'wp-user-frontend';
        }

        return $parent_file;
    }

    /**
     * Fix the submenu class in admin menu
     *
     * @since 2.6.0
     *
     * @param  string $submenu_file
     *
     * @return string
     */
    public function fix_submenu_file( $submenu_file ) {
        $current_screen = get_current_screen();

        if ( 'wpuf_subscription' == $current_screen->post_type && $current_screen->base == 'admin_page_wpuf_subscribers' ) {
            $submenu_file = 'edit.php?post_type=wpuf_subscription';
        }

        return $submenu_file;
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
            'label'   => __( 'Number of items per page:', 'wp-user-frontend' ),
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

        if ( ! $this->is_admin_menu_page( get_current_screen() ) && get_current_screen()->parent_base == 'edit'  ) {
            return;
        }

        wp_enqueue_style( 'wpuf-admin', WPUF_ASSET_URI . '/css/admin.css', false, WPUF_VERSION );
        wp_enqueue_script( 'wpuf-admin-script', WPUF_ASSET_URI . '/js/wpuf-admin.js', array( 'jquery' ), false, WPUF_VERSION );

        wp_localize_script( 'wpuf-admin-script', 'wpuf_admin_script', array(
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'wpuf_nonce' ),
            'cleared_schedule_lock' => __( 'Schedule lock has been cleared', 'wp-user-frontend' ),
        ) );
    }

}
