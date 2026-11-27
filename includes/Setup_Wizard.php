<?php
/**
 * Old setup wizard (retired)
 *
 * @package WP_User_Frontend
 */

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Admin\Onboarding;

/**
 * The three step setup wizard was replaced by the onboarding wizard
 * (Admin\Onboarding). This class stays so its address, its container service
 * (`wpuf()->setup_wizard`) and its `safe_style_css` filter keep working:
 *
 * - `index.php?page=wpuf-setup` opens the onboarding wizard;
 * - an activation no longer sends an existing site into a wizard (a first
 *   install is sent to onboarding by Admin\Onboarding);
 * - `display` stays an allowed inline style for `wp_kses`.
 *
 * @since 2.9.2
 * @since WPUF_SINCE Retired: forwards to the onboarding wizard.
 */
class Setup_Wizard {

    /**
     * Page slug of the old wizard.
     *
     * @since WPUF_SINCE
     */
    const PAGE_SLUG = 'wpuf-setup';

    /**
     * Hook in.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menus' ] );
        add_action( 'admin_head', [ $this, 'hide_menu_link' ] );
        add_action( 'admin_init', [ $this, 'setup_wizard' ], 99 );
        add_action( 'admin_init', [ $this, 'redirect_to_page' ], 9999 );
        add_filter( 'safe_style_css', [ $this, 'wpuf_safe_style_css' ] );
    }

    /**
     * Keep the old address registered, so WordPress lets it through to the
     * redirect.
     *
     * @return void
     */
    public function admin_menus() {
        add_dashboard_page( 'WPUF Setup', 'WPUF Setup', 'manage_options', self::PAGE_SLUG, '__return_null' );
    }

    /**
     * The page has no menu entry.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function hide_menu_link() {
        remove_submenu_page( 'index.php', self::PAGE_SLUG );
    }

    /**
     * The old wizard's address opens the onboarding wizard.
     *
     * @since 2.9.2
     * @since WPUF_SINCE Redirects instead of rendering the old wizard.
     *
     * @return void
     */
    public function setup_wizard() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check.
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

        if ( self::PAGE_SLUG !== $page || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'index.php?page=' . Onboarding::PAGE_SLUG ) );
        exit;
    }

    /**
     * Activation no longer opens the old wizard. The marker Installer sets is
     * cleared so nothing acts on it later.
     *
     * @since 2.9.2
     * @since WPUF_SINCE No redirect: Admin\Onboarding sends first installs to onboarding.
     *
     * @return void
     */
    public function redirect_to_page() {
        if ( get_transient( 'wpuf_activation_redirect' ) ) {
            delete_transient( 'wpuf_activation_redirect' );
        }
    }

    /**
     * Allow the `display` inline style in `wp_kses`.
     *
     * @param string[] $styles Allowed inline styles.
     *
     * @return string[]
     */
    public function wpuf_safe_style_css( $styles ) {
        $styles[] = 'display';

        return $styles;
    }
}
