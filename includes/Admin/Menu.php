<?php

namespace WeDevs\Wpuf\Admin;

class Menu {
    protected $all_submenu_hooks = [];

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

        add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', 'wp-user-frontend' ), $capability, $this->parent_slug, [ $this, 'wpuf_post_forms_page' ], $wpuf_icon, '54.2' );

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

        if ( 'on' === wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            // $subscription_hook = add_submenu_page( $this->parent_slug, __( 'Subscriptions', 'wp-user-frontend' ), __( 'Subscriptions', 'wp-user-frontend' ), $capability, 'edit.php?post_type=wpuf_subscription' );

            $subscription_hook = add_submenu_page(
                $this->parent_slug,
                __( 'Subscriptions', 'wp-user-frontend' ),
                __( 'Subscriptions', 'wp-user-frontend' ),
                $capability,
                'wpuf_subscription',
                [ $this, 'subscription_menu_page' ]
            );

            $this->all_submenu_hooks['subscription_hook'] = $subscription_hook;
            add_action( 'load-' . $subscription_hook, [ $this, 'subscription_menu_action' ] );

            $transactions_page = add_submenu_page( $this->parent_slug, __( 'Transactions', 'wp-user-frontend' ), __( 'Transactions', 'wp-user-frontend' ), $capability, 'wpuf_transaction', [ $this, 'transactions_page' ] );

            add_action( 'load-' . $transactions_page, [ $this, 'transactions_screen_option' ] );
        }

        $tools_hook = add_submenu_page( $this->parent_slug, __( 'Tools', 'wp-user-frontend' ), __( 'Tools', 'wp-user-frontend' ), $capability, 'wpuf_tools', [ $this, 'tools_page' ] );
        $this->all_submenu_hooks['tools'] = $tools_hook;

        add_action( 'load-' . $tools_hook, [ $this, 'enqueue_tools_script' ] );

        do_action( 'wpuf_admin_menu' );

        add_action( 'load-' . $post_forms_hook, [ $this, 'post_form_menu_action' ] );

        do_action( 'wpuf_admin_menu_bottom' );

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $premium_hook = add_submenu_page( $this->parent_slug, __( 'Premium', 'wp-user-frontend' ), __( 'Premium', 'wp-user-frontend' ), $capability, 'wpuf_premium', [ $this, 'premium_page' ] );

            $this->all_submenu_hooks['premium'] = $premium_hook;
        }

        $help_hook = add_submenu_page( $this->parent_slug, __( 'Help', 'wp-user-frontend' ), sprintf( '<span style="color:#f18500">%s</span>', __( 'Help', 'wp-user-frontend' ) ), $capability, 'wpuf-support', [ $this, 'support_page' ] );
        $this->all_submenu_hooks['help'] = $help_hook;

        add_action( 'load-' . $help_hook, [ $this, 'enqueue_help_script' ] );

        $subscribers_page_hook = add_submenu_page( 'edit.php?post_type=wpuf_subscription', __( 'Subscribers', 'wp-user-frontend' ), __( 'Subscribers', 'wp-user-frontend' ), $capability, 'wpuf_subscribers', [ $this, 'subscribers_page' ] );
        //phpcs:ignore
        $_registered_pages['user-frontend_page_wpuf_subscribers'] = true; // hack to work the nested subscribers page. WPUF > Subscriptions > Subscribers

        $this->all_submenu_hooks['subscribers_hook'] = $subscribers_page_hook;

        $settings_page_hook = add_submenu_page( $this->parent_slug, __( 'Settings', 'wp-user-frontend' ), __( 'Settings', 'wp-user-frontend' ), $capability, 'wpuf-settings', [ $this, 'plugin_settings_page' ] );

        $this->all_submenu_hooks['settings_hook'] = $settings_page_hook;

        add_action( 'load-' . $settings_page_hook, [ $this, 'enqueue_settings_page_scripts' ] );
    }

    /**
     * The content of the Post Form page.
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
        add_action( 'admin_footer', [ $this, 'load_headway_badge' ] );
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
     * Load the Headway badge
     *
     * @since 4.0.5
     *
     * @return void
     */
    public function load_headway_badge() {
        ?>
        <script>
            const HW_config = {
                selector: '.headway-icon',
                account: 'JPqPQy',
                callbacks: {
                    onWidgetReady: function ( widget ) {
                        if ( widget.getUnseenCount() === 0 ) {
                            document.querySelector('.headway-header ul li.headway-icon span#HW_badge_cont.HW_visible')
                                .style = 'opacity: 0';
                        }
                    },
                    onHideWidget: function(){
                        document.querySelector('.headway-header ul li.headway-icon span#HW_badge_cont.HW_visible')
                            .style = 'opacity: 0';
                    }
                }
            };

        </script>
        <script async src="//cdn.headwayapp.co/widget.js"></script>
        <?php
    }

    /**
     * The action to run just after the menu is created.
     *
     * @since 4.0.0
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

    public function subscription_menu_action() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_subscription_page' );
    }

    /**
     * The content of the Subscription page.
     *
     * @since WPUF_VERSION
     *
     * @return void
     */
    public function subscription_menu_page() {
        $page = WPUF_INCLUDES . '/Admin/views/subscriptions.php';

        wpuf_require_once( $page );
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

        wpuf()->admin->transaction_list_table = new List_Table_Transactions();
    }

    public function transactions_page() {
        $page = WPUF_INCLUDES . '/Admin/views/transactions-list-table-view.php';

        wpuf_require_once( $page );
    }

    /**
     * The subscribers page content
     *
     * @param $post_ID
     *
     * @return void
     */
    public function subscribers_page( $post_ID ) {
        $page = WPUF_INCLUDES . '/Admin/views/subscribers.php';

        wpuf_require_once( $page );
    }

    /**
     * Get all the submenu hooks created by WPUF
     *
     * @since 4.0.0
     *
     * @return array
     */
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

    /**
     * Enqueue scripts required for tools page
     *
     * @return void
     */
    public function enqueue_tools_script() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_tools' );

        wp_enqueue_media(); // for uploading JSON

        wp_enqueue_script( 'wpuf-vue' );
        wp_enqueue_script( 'wpuf-admin-tools' );

        wp_localize_script(
            'wpuf-admin-tools',
            'wpuf_admin_tools',
            [
                'url'   => [
                    'ajax' => admin_url( 'admin-ajax.php' ),
                ],
                'nonce' => wp_create_nonce( 'wpuf_admin_tools' ),
                'i18n'  => [
                    'wpuf_import_forms'      => __( 'WPUF Import Forms', 'wp-user-frontend' ),
                    'add_json_file'          => __( 'Add JSON file', 'wp-user-frontend' ),
                    'could_not_import_forms' => __( 'Could not import forms.', 'wp-user-frontend' ),
                ],
            ]
        );
    }

    /**
     * The User Frontend > Tools page content
     *
     * @return void
     */
    public function tools_page() {
        wpuf()->admin->tools = new Admin_Tools();

        $tools_page = WPUF_INCLUDES . '/Admin/views/tools.php';

        wpuf_include_once( $tools_page );
    }

    /**
     * Load necessary scripts for User Frontend > Settings page
     *
     * @return void
     */
    public function enqueue_settings_page_scripts() {
        wp_enqueue_script( 'wpuf-subscriptions' );
        wp_enqueue_script( 'wpuf-settings' );

        add_action( 'admin_footer', [ $this, 'load_headway_badge' ] );
    }

    /**
     * The User Frontend > Settings page content
     *
     * @return void
     */
    public function plugin_settings_page() {
        ?>
        <div class="wrap">
            <h2 class="with-headway-icon">
                <span class="title-area">
                    <?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?>
                </span>
                <span class="flex-end">
                    <span class="headway-icon"></span>
                    <a class="canny-link" target="_blank" href="<?php echo esc_url( 'https://wpuf.canny.io/ideas' ); ?>">💡 <?php esc_html_e(
                    'Submit Ideas', 'wp-user-frontend'
                    ); ?></a>
                </span>
            </h2>
            <div class="wpuf-settings-wrap">
                <?php
                settings_errors();

                wpuf()->admin->settings->get_settings_api()->show_navigation();
                wpuf()->admin->settings->get_settings_api()->show_forms();
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * The User Frontend > Premium page content
     *
     * @return void
     */
    public function premium_page() {
        require_once WPUF_INCLUDES . '/Admin/views/premium.php';
    }

    /**
     * The User Frontend > Support page content
     *
     * @return void
     */
    public function support_page() {
        require_once WPUF_INCLUDES . '/Admin/views/support.php';
    }

    /**
     * The User Frontend > Help page scripts
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_help_script() {
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-admin' );
    }
}
