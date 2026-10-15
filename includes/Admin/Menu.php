<?php

namespace WeDevs\Wpuf\Admin;

class Menu {
    protected $all_submenu_hooks = [];

    public $parent_slug = 'wp-user-frontend';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );

        add_filter( 'parent_file', [ $this, 'fix_parent_menu' ] );
        add_filter( 'script_loader_tag', [ $this , 'add_async_attribute' ], 10, 3 );
        add_action( 'admin_head', [ $this, 'premium_menu_styles' ] );
    }

    public function admin_menu() {
        global $_registered_pages;

        $capability = wpuf_admin_role();
        $wpuf_icon  = 'data:image/svg+xml;base64,' . base64_encode( '<svg width="83px" height="76px" viewBox="0 0 83 76" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="wpuf-icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g id="ufp" fill-rule="nonzero" fill="#9EA3A8"><path d="M49.38,51.88 C49.503348,56.4604553 45.8999295,60.2784694 41.32,60.42 C36.7400705,60.2784694 33.136652,56.4604553 33.26,51.88 L33.26,40.23 L19,40.23 L19,51.88 C19,64.77 29,75.25 41.36,75.26 L41.36,75.26 C47.3622079,75.2559227 53.0954073,72.7693647 57.2,68.39 C61.4213559,63.9375842 63.7575868,58.0253435 63.72,51.89 L63.72,40.23 L49.38,40.23 L49.38,51.88 Z" id="Shape"></path><polygon id="Shape" points="32.96 0.59 0 0.59 3.77 16.68 32.96 16.68"></polygon><path d="M68,0 L49.75,0 L49.75,16.1 L68,16.1 C68.74,16.1 69.39,17.1 69.39,18.24 C69.39,19.38 68.74,20.38 68,20.38 L49.75,20.38 L49.75,36.5 L68,36.5 C76,36.5 82.5,28.31 82.5,18.25 C82.5,8.19 76,0 68,0 Z" id="Shape"></path><polygon id="Shape" points="32.96 20.41 5.31 20.41 9.07 36.5 32.96 36.5"></polygon></g></g></svg>' );

        $app_hook = add_menu_page( __( 'WP User Frontend', 'wp-user-frontend' ), __( 'User Frontend', 'wp-user-frontend' ), $capability, $this->parent_slug, [ $this, 'wpuf_post_forms_page' ], $wpuf_icon, '54.2' );

        // The single React admin app runs on the top-level page (task 5d).
        $this->all_submenu_hooks['app'] = $app_hook;
        wpuf()->platform()->get( App\AppPage::class )->register( $app_hook );

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

        if ( ! wpuf_is_pro_active() ) {
            $premium_hook = add_submenu_page( $this->parent_slug, __( 'Premium', 'wp-user-frontend' ), $this->get_premium_menu_title(), $capability, 'wpuf_premium', [ $this, 'premium_page' ] );

            $this->all_submenu_hooks['premium'] = $premium_hook;

            add_action( 'load-' . $premium_hook, [ $this, 'enqueue_premium_script' ] );
        }

        $help_hook = add_submenu_page( $this->parent_slug, __( 'Help', 'wp-user-frontend' ), sprintf( '<span style="color:#f18500">%s</span>', __( 'Help', 'wp-user-frontend' ) ), $capability, 'wpuf-support', [ $this, 'support_page' ] );
        $this->all_submenu_hooks['help'] = $help_hook;

        add_action( 'load-' . $help_hook, [ $this, 'enqueue_help_script' ] );

        $subscribers_page_hook = add_submenu_page( 'edit.php?post_type=wpuf_subscription', __( 'Subscribers', 'wp-user-frontend' ), __( 'Subscribers', 'wp-user-frontend' ), $capability, 'wpuf_subscribers', [ $this, 'subscribers_page' ] );
        //phpcs:ignore
        $_registered_pages['user-frontend_page_wpuf_subscribers'] = true; // hack to work the nested subscribers page. WPUF > Subscriptions > Subscribers

        $this->all_submenu_hooks['subscribers_hook'] = $subscribers_page_hook;

        // The old Subscribers URL opens the pack's React Subscribers view.
        add_action( 'load-' . $subscribers_page_hook, [ $this, 'subscribers_menu_action' ] );

        $settings_page_hook = add_submenu_page( $this->parent_slug, __( 'Settings', 'wp-user-frontend' ), __( 'Settings', 'wp-user-frontend' ), $capability, 'wpuf-settings', [ $this, 'plugin_settings_page' ] );

        $this->all_submenu_hooks['settings_hook'] = $settings_page_hook;

        add_action( 'load-' . $settings_page_hook, [ $this, 'enqueue_settings_page_scripts' ] );
    }

    /**
     * The admin screen registry (Admin\Screens).
     *
     * @since WPUF_SINCE
     *
     * @return Screens\Registry
     */
    protected function screens() {
        return wpuf()->platform()->get( Screens\Registry::class );
    }

    /**
     * Build the Premium submenu title with its crown icon.
     *
     * WordPress prints submenu titles unescaped, so markup is allowed here. The
     * crown is inlined rather than enqueued so it costs no extra request, and the
     * glow itself is handled in CSS — see premium_menu_styles().
     *
     * @since 4.3.11
     *
     * @return string
     */
    public function get_premium_menu_title() {
        $crown = '<svg class="wpuf-premium-menu__crown" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 16L3 6l5.5 4L12 4l3.5 6L21 6l-2 10H5zm14 3a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-1h14v1z"/></svg>';

        return '<span class="wpuf-premium-menu">' . esc_html__( 'Premium', 'wp-user-frontend' ) . $crown . '</span>';
    }

    /**
     * Print the glow styles for the Premium submenu item.
     *
     * Lives in admin_head because the admin menu renders on every screen, while
     * the plugin stylesheet is only enqueued on WPUF pages.
     *
     * @since 4.3.11
     *
     * @return void
     */
    public function premium_menu_styles() {
        if ( wpuf_is_pro_active() ) {
            return;
        }
        ?>
        <style id="wpuf-premium-menu-style">
            #adminmenu .wpuf-premium-menu {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-weight: 600;
                color: #ffb900;
            }

            #adminmenu .wpuf-premium-menu__crown {
                width: 14px;
                height: 14px;
                flex-shrink: 0;
                fill: currentColor;
                animation: wpuf-premium-glow 2.4s ease-in-out infinite;
            }

            #adminmenu a:hover .wpuf-premium-menu,
            #adminmenu a:focus .wpuf-premium-menu,
            #adminmenu .current .wpuf-premium-menu {
                color: #ffc83d;
            }

            @keyframes wpuf-premium-glow {
                0%, 100% {
                    filter: drop-shadow( 0 0 0 rgba( 255, 185, 0, 0 ) );
                    transform: scale( 1 );
                }

                50% {
                    filter: drop-shadow( 0 0 5px rgba( 255, 185, 0, .9 ) );
                    transform: scale( 1.12 );
                }
            }

            @media ( prefers-reduced-motion: reduce ) {
                #adminmenu .wpuf-premium-menu__crown {
                    animation: none;
                    filter: drop-shadow( 0 0 3px rgba( 255, 185, 0, .7 ) );
                }
            }
        </style>
        <?php
    }

    /**
     * The content of the Post Form page.
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
        // The top-level page is the React admin app when it is on (task 5d).
        if ( wpuf_is_admin_app() ) {
            wpuf()->platform()->get( App\AppPage::class )->render();

            return;
        }

        // Rendered by Admin\Screens\PostFormsList (task 2.5a).
        $this->screens()->render( 'wpuf-post-forms' );
    }

    /**
    * Mark headway as async. Because nothing depends on it, it can run at any time
    */
    public function add_async_attribute( $tag, $handle, $src ) {
        if ( 'wpuf-headway-script' === $handle ) {
            return str_replace( ' src', ' async src', $tag );
        }

        return $tag;
    }

    /**
     * The action to run just after the menu is created.
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function post_form_menu_action() {
        // Fires wpuf_load_post_forms (Admin\Screens\PostFormsList, task 2.5a).
        $this->screens()->load( 'wpuf-post-forms' );
    }

    public function subscription_menu_action() {
        // Fires wpuf_load_subscription_page (Admin\Screens\Subscriptions, task 2.5a).
        $this->screens()->load( 'wpuf_subscription' );
    }

    /**
     * The content of the Subscription page.
     *
     * @since WPUF_VERSION
     *
     * @return void
     */
    public function subscription_menu_page() {
        // Rendered by Admin\Screens\Subscriptions (task 2.5a).
        $this->screens()->render( 'wpuf_subscription' );
    }

    /**
     * Screen options.
     *
     * @return void
     */
    public function transactions_screen_option() {
        // Admin\Screens\Transactions (task 5b.6).
        $this->screens()->load( 'wpuf_transaction' );
    }

    public function transactions_page() {
        // Admin\Screens\Transactions (task 5b.6).
        $this->screens()->render( 'wpuf_transaction' );
    }

    /**
     * Load step of the old Subscribers page: redirects to the subscriptions
     * app's Subscribers view (Admin\Screens\Subscribers).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function subscribers_menu_action() {
        $this->screens()->load( 'wpuf_subscribers' );
    }

    /**
     * The subscribers page content
     *
     * @param $post_ID
     *
     * @return void
     */
    public function subscribers_page( $post_ID ) {
        // Admin\Screens\Subscribers (task 5b.6).
        $this->screens()->render( 'wpuf_subscribers' );
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
     * Enqueue scripts required for tools page
     *
     * @return void
     */
    public function enqueue_tools_script() {
        // Fires wpuf_load_tools (Admin\Screens\Tools, task 5b.6).
        $this->screens()->load( 'wpuf_tools' );
    }

    /**
     * The User Frontend > Tools page content
     *
     * @return void
     */
    public function tools_page() {
        // Admin\Screens\Tools (task 5b.6).
        $this->screens()->render( 'wpuf_tools' );
    }

    /**
     * Load necessary scripts for User Frontend > Settings page
     *
     * @return void
     */
    public function enqueue_settings_page_scripts() {
        // Enqueued by Admin\Screens\Settings (task 2.5a).
        $this->screens()->load( 'wpuf-settings' );
    }

    /**
     * The User Frontend > Settings page content
     *
     * @return void
     */
    public function plugin_settings_page() {
        // Rendered by Admin\Screens\Settings (task 2.5a).
        $this->screens()->render( 'wpuf-settings' );
    }

    /**
     * Load styles for the User Frontend > Premium page
     *
     * @since 4.3.9
     *
     * @return void
     */
    public function enqueue_premium_script() {
        // Admin\Screens\Premium (task 5b.6).
        $this->screens()->load( 'wpuf_premium' );
    }

    /**
     * The User Frontend > Premium page content
     *
     * @return void
     */
    public function premium_page() {
        // Admin\Screens\Premium (task 5b.6).
        $this->screens()->render( 'wpuf_premium' );
    }

    /**
     * The User Frontend > Support page content
     *
     * @return void
     */
    public function support_page() {
        // Admin\Screens\Help (task 5b.6).
        $this->screens()->render( 'wpuf-support' );
    }

    /**
     * The User Frontend > Help page scripts
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_help_script() {
        // Admin\Screens\Help (task 5b.6).
        $this->screens()->load( 'wpuf-support' );
    }
}
