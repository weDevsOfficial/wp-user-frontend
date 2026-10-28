<?php

namespace WeDevs\Wpuf\Admin;

/**
 * The welcome class after install
 *
 * @since 2.6.0
 */
class Admin_Welcome {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_head', [ $this, 'hide_menu' ] );
    }

    /**
     * Register the admin menu to setup the welcome message
     *
     * @return void
     */
    public function register_menu() {
        $hook = add_dashboard_page(
            __( 'Welcome to WP User Frontend', 'wp-user-frontend' ),
            __( 'Welcome to WP User Frontend', 'wp-user-frontend' ), 'manage_options', 'wpuf-welcome',
            [ $this, 'welcome_page' ]
        );

        // The page opens the admin app route #/welcome (Admin\Screens\Welcome).
        if ( $hook ) {
            add_action(
                'load-' . $hook,
                function () {
                    wpuf()->platform()->get( Screens\Registry::class )->load( Screens\Welcome::SLUG );
                }
            );
        }
    }

    /**
     * Hide the menu as we don't want to show the welcome page in admin menu
     *
     * @return void
     */
    public function hide_menu() {
        remove_submenu_page( 'index.php', 'wpuf-welcome' );
    }

    /**
     * Render the welcome page: the admin app route #/welcome opens instead
     * (Admin\Screens\Welcome).
     *
     * @since WPUF_SINCE Renders nothing; was views/welcome-page.php
     *
     * @return void
     */
    public function welcome_page() {
        // The welcome screen is the admin app route #/welcome: its load hook
        // redirects there before WordPress calls this page callback.
    }
}
