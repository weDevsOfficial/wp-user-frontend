<?php

namespace Wp\User\Frontend\Admin;

class Menu {
    private $all_submenu_hooks = [];

    public $parent_slug = 'wp-user-frontend';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );

        add_filter( 'parent_file', [ $this, 'fix_parent_menu' ] );
        add_filter( 'submenu_file', [ $this, 'fix_submenu_file' ] );
    }

    public function admin_menu() {
        global $_registered_pages;

        $capability = wpuf_admin_role();
        $wpuf_icon  = 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' );

        add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', 'wp-user-frontend' ), $capability, $this->parent_slug, [ $this, '\wpuf_post_forms_page' ], $wpuf_icon, '54.2' );
        $post_forms_hook = add_submenu_page(
            $this->parent_slug,
            __( 'Post Forms', 'wp-user-frontend' ),
            __( 'Post Forms', 'wp-user-frontend' ),
            $capability,
            'wpuf-post-forms',
            [ $this, 'wpuf_post_forms_page' ]
        );
        $this->all_submenu_hooks['post_forms'] = $post_forms_hook;

        // remove the toplevel menu item
        remove_submenu_page( 'wp-user-frontend', 'wp-user-frontend' );

        /*
         * @since 2.3
         */
        do_action( 'wpuf_admin_menu_top' );

        if ( 'on' === wpuf_get_option( 'enable_payment', 'Wp\User\Frontend\WPUF_Payment', 'on' ) ) {
            $subscription_hook = add_submenu_page( $this->parent_slug, __( 'Subscriptions', 'wp-user-frontend' ), __( 'Subscriptions', 'wp-user-frontend' ), $capability, 'edit.php?post_type=wpuf_subscription' );

            $this->all_submenu_hooks['subscription_hook'] = "load-$subscription_hook";

            $transactions_page = add_submenu_page( $this->parent_slug, __( 'Transactions', 'wp-user-frontend' ), __( 'Transactions', 'wp-user-frontend' ), $capability, 'wpuf_transaction', [ $this, 'transactions_page' ] );

            add_action( "load-$transactions_page", [ $this, 'transactions_screen_option' ] );
        }

        do_action( 'wpuf_admin_menu' );

        add_action( "load-$post_forms_hook", [ $this, 'post_form_menu_action' ] );

        do_action( 'wpuf_admin_menu_bottom' );

        $subscribers_page_hook = add_submenu_page( 'edit.php?post_type=wpuf_subscription', __( 'Subscribers', 'wp-user-frontend' ), __( 'Subscribers', 'wp-user-frontend' ), $capability, 'wpuf_subscribers', [ $this, 'subscribers_page' ] );
        //phpcs:ignore
        $_registered_pages['user-frontend_page_wpuf_subscribers'] = true; // hack to work the nested subscribers page. WPUF > Subscriptions > Subscribers

        $this->all_submenu_hooks['subscribers_hook'] = "load-$subscribers_page_hook";
    }

    /**
     * The content of the Post Form page.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
        // phpcs:ignore WordPress.Security.NonceVerification
        $action           = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' );

        switch ( $action ) {
            case 'edit':
            case 'add-new':
                require_once WPUF_INCLUDES . '/Admin/views/post-form.php';
                break;

            default:
                require_once WPUF_INCLUDES . '/Admin/views/post-forms-list-table-view.php';
                break;
        }
    }

    /**
     * The action to run just after the menu is created.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function post_form_menu_action() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_post_forms' );
    }

    /**
     * Screen options.
     *
     * @return void
     */
    public function transactions_screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => __( 'Number of items per page:', 'wp-user-frontend' ),
            'default' => 20,
            'option'  => 'transactions_per_page',
        ];

        add_screen_option( $option, $args );

        if ( ! class_exists( 'Wp\User\Frontend\Admin\WPUF_Transactions_List_Table' ) ) {
            require_once WPUF_ROOT . '/class/transactions-list-table.php';
        }

        $this->transactions_list_table_obj = new WPUF_Transactions_List_Table();
    }

    public function transactions_page() {
        require_once WPUF_INCLUDES . '/Admin/views/transactions-list-table-view.php';
    }

    /**
     * The subscribers page content
     *
     * @param $post_ID
     *
     * @return void
     */
    public function subscribers_page( $post_ID ) {
        include dirname( WPUF_INCLUDES ) . '/admin/subscribers.php';
    }

    public function get_all_submenu_hooks() {
        return $this->all_submenu_hooks;
    }

    public function add_submenu_hooks( $key, $hook ) {
        $this->all_submenu_hooks[ $key ] = $hook;
    }

    /**
     * Highlight the proper top level menu
     *
     * @see http://wordpress.org/support/topic/moving-taxonomy-ui-to-another-main-menu?replies=5#post-2432769
     *
     * @global $current_screen
     *
     * @param string $parent_file
     *
     * @return string
     */
    public function fix_parent_menu( $parent_file ) {
        $current_screen = get_current_screen();

        $post_types = [ 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon' ];

        if ( in_array( $current_screen->post_type, $post_types, true ) ) {
            $parent_file = 'wp-user-frontend';
        }

        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_the-slug' ) {
            $parent_file = 'wp-user-frontend';
        }

        return $parent_file;
    }

    /**
     * Fix the submenu class in admin menu
     *
     * @since 2.6.0
     *
     * @param string $submenu_file
     *
     * @return string
     */
    public function fix_submenu_file( $submenu_file ) {
        $current_screen = get_current_screen();

        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_wpuf_subscribers' ) {
            $submenu_file = 'edit.php?post_type=wpuf_subscription';
        }

        return $submenu_file;
    }

}
