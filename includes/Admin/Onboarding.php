<?php
/**
 * Onboarding wizard page
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Platform\Onboarding\Wizard;

/**
 * The admin side of the setup wizard: the hidden page that opens the admin
 * app route `#/onboarding/:step` (Admin\Screens\Onboarding, React app
 * src/admin/apps/onboarding), the one-time redirect of a first install, the
 * restart link of Tools, and the menus of the features the admin did not pick.
 *
 * What the wizard does lives in Platform\Onboarding\Wizard; its steps save
 * through wpuf/v1/onboarding (Platform\REST\Controllers\OnboardingController).
 *
 * @since WPUF_SINCE
 */
class Onboarding {

    /**
     * Page slug of the wizard
     */
    const PAGE_SLUG = 'wpuf-onboarding';

    /**
     * @since WPUF_SINCE
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_page' ] );
        add_action( 'admin_head', [ $this, 'hide_menu_link' ] );

        // Ahead of Setup_Wizard::redirect_to_page(), which runs at 9999.
        add_action( 'admin_init', [ $this, 'maybe_redirect_after_activation' ], 5 );

        // Late, so every menu this might hide is registered by the time it runs.
        add_action( 'admin_menu', [ $this, 'hide_menus_for_unpicked_features' ], 999 );
    }

    /**
     * The wizard.
     *
     * @since WPUF_SINCE
     *
     * @return Wizard
     */
    public function wizard() {
        return wpuf()->platform()->get( Wizard::class );
    }

    /**
     * Register the hidden page that hosts the wizard.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_page() {
        $hook = add_dashboard_page(
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            __( 'WPUF Onboarding', 'wp-user-frontend' ),
            'manage_options',
            self::PAGE_SLUG,
            '__return_null'
        );

        // The page opens the admin app route (Admin\Screens\Onboarding).
        if ( $hook ) {
            add_action(
                'load-' . $hook,
                function () {
                    wpuf()->platform()->get( Screens\Registry::class )->load( self::PAGE_SLUG );
                }
            );
        }
    }

    /**
     * Keep the hosting page out of the Dashboard submenu.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hide_menu_link() {
        remove_submenu_page( 'index.php', self::PAGE_SLUG );
    }

    /**
     * Send a brand new install to the wizard, once.
     *
     * Installer::install() sets the transient only when wpuf_installed was
     * unset, so updating or reactivating on a site in use changes nothing.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function maybe_redirect_after_activation() {
        $state = $this->wizard()->state();

        if ( ! $state->has_redirect() ) {
            return;
        }

        // A heartbeat, or a user who cannot manage the site, must not use up the
        // one-time redirect.
        if ( ! current_user_can( 'manage_options' ) || wp_doing_ajax() ) {
            return;
        }

        // The legacy wizard's transient too: the two never fight over one activation.
        $state->consume_redirect();

        // Single site only, and never mid bulk activation. The flag's presence is
        // all that is read; the consumed transient is what authorises this.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        // Pages for the post form and registration steps to pick from; the rest
        // wait for the settings step.
        wpuf()->platform()->get( Admin_Installer::class )->init_essential_pages();

        wp_safe_redirect( admin_url( 'index.php?page=' . self::PAGE_SLUG ) );

        exit;
    }

    /**
     * "Run Onboarding Again" (Tools): wipe the last run, then open step one.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function maybe_restart() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- checked below.
        if ( empty( $_GET['restart'] ) ) {
            return;
        }

        check_admin_referer( 'wpuf-onboarding-restart' );

        $wizard = $this->wizard();

        $wizard->restart();

        wp_safe_redirect( $wizard->step_link( 'features' ) );
        exit;
    }

    /**
     * Hide the menus of the features the admin said they do not need.
     *
     * Only once the first step was answered (a site that never ran the wizard
     * keeps every menu), and only the menu: the screens stay reachable by URL,
     * and ticking the feature again brings the menu back.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hide_menus_for_unpicked_features() {
        $wizard = $this->wizard();

        if ( ! is_array( $wizard->state()->features() ) ) {
            return;
        }

        $parent = isset( wpuf()->admin->menu->parent_slug ) ? wpuf()->admin->menu->parent_slug : 'wp-user-frontend';
        $picked = $wizard->get_features();

        foreach ( $wizard->get_feature_menus() as $feature => $slugs ) {
            if ( in_array( $feature, $picked, true ) || ! is_array( $slugs ) ) {
                continue;
            }

            foreach ( $slugs as $slug ) {
                remove_submenu_page( $parent, $slug );

                // Subscribers and coupons hang off their post types, not the plugin menu.
                remove_submenu_page( 'edit.php?post_type=wpuf_subscription', $slug );
                remove_submenu_page( 'edit.php?post_type=wpuf_coupon', $slug );
            }
        }
    }
}
