<?php
/**
 * Free/Pro version guard
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

use WeDevs\Wpuf\Platform\Contracts\Hookable;

/**
 * Free updates from wordpress.org before Pro (D10). An older Pro (before
 * 4.3.0, no `WPUF_PRO_PLATFORM_VERSION`) is built on code this version no
 * longer has, so it is not started: free runs as the free plugin
 * (wpuf_is_pro_active() is false) and one notice asks to update Pro. Pro
 * 4.3.0+ declares its platform version when its file loads.
 *
 * @since WPUF_SINCE
 */
class VersionGuard implements Hookable {

    /**
     * Pro platform version this free version needs.
     */
    const REQUIRED_PRO_PLATFORM = '1.0';

    /**
     * Pro version that brings that platform (shown in the notice).
     */
    const REQUIRED_PRO_VERSION = '4.3.0';

    /**
     * Scripts an older Pro enqueues for the Vue form builder.
     */
    const LEGACY_PRO_BUILDER_SCRIPTS = [
        'wpuf-form-builder-components-pro',
        'wpuf-form-builder-field-option-data-pro',
        'wpuf-form-builder-mixins-pro',
        'wpuf-form-builder-wpuf-forms-pro',
        'wpuf-form-builder-wpuf-profile',
        'wpuf-vue',
        'wpuf-vuex',
    ];

    /**
     * Hook the guard.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'skip_legacy_pro_builder' ], 999 );
        add_action( 'admin_notices', [ $this, 'pro_update_notice' ] );
    }

    /**
     * Whether an active Pro is older than this version needs: its main
     * class is loaded but it declares no (or an older) platform version.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function pro_needs_update() {
        return self::is_outdated_pro(
            class_exists( 'WP_User_Frontend_Pro', false ) ? ( defined( 'WPUF_PRO_VERSION' ) ? WPUF_PRO_VERSION : '' ) : null,
            defined( 'WPUF_PRO_PLATFORM_VERSION' ) ? WPUF_PRO_PLATFORM_VERSION : null
        );
    }

    /**
     * Whether a Pro with these versions is too old for this version.
     *
     * @since WPUF_SINCE
     *
     * @param string|null $pro_version          Pro version ('' when not known yet), null when Pro is not active.
     * @param string|null $pro_platform_version Pro platform version, null when Pro does not declare one.
     *
     * @return bool
     */
    public static function is_outdated_pro( $pro_version, $pro_platform_version ) {
        if ( null === $pro_version ) {
            return false;
        }

        return null === $pro_platform_version
            || version_compare( $pro_platform_version, self::REQUIRED_PRO_PLATFORM, '<' );
    }

    /**
     * Do not start an older Pro: its `plugins_loaded` boot is removed before
     * it runs (free's own `plugins_loaded` callbacks run first: free loads
     * before Pro). Called from free's loader.
     *
     * @since WPUF_SINCE
     *
     * @return bool Whether an older Pro was stopped
     */
    public static function stop_outdated_pro() {
        if ( ! self::pro_needs_update() || ! function_exists( 'wpuf_pro' ) || defined( 'WPUF_PRO_VERSION' ) ) {
            return false;
        }

        return remove_action( 'plugins_loaded', [ wpuf_pro(), 'init_plugin' ] );
    }

    /**
     * Whether the current admin page is a WPUF form builder or forms list.
     *
     * @return bool
     */
    private function is_builder_screen() {
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- screen detection only.

        // The admin app page opens the builders as routes.
        return in_array( $page, [ 'wpuf-post-forms', 'wpuf-profile-forms' ], true ) || wpuf_is_admin_app();
    }

    /**
     * When an older Pro started anyway (it loaded before free), its Vue
     * builder scripts are not loaded on the React builder.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function skip_legacy_pro_builder() {
        if ( ! self::pro_needs_update() || ! $this->is_builder_screen() ) {
            return;
        }

        foreach ( self::LEGACY_PRO_BUILDER_SCRIPTS as $handle ) {
            wp_dequeue_script( $handle );
        }
    }

    /**
     * One notice on WPUF admin pages and the Plugins screen while Pro needs
     * an update.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function pro_update_notice() {
        if ( ! self::pro_needs_update() || ! current_user_can( wpuf_admin_role() ) ) {
            return;
        }

        global $pagenow;

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- screen detection only.

        if ( 0 !== strpos( $page, 'wpuf' ) && ! wpuf_is_admin_app() && 'plugins.php' !== $pagenow ) {
            return;
        }

        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html(
                sprintf(
                    /* translators: 1: WP User Frontend version, 2: required WP User Frontend Pro version */
                    __( 'WP User Frontend %1$s needs WP User Frontend Pro %2$s or newer. Pro features are off until you update WP User Frontend Pro.', 'wp-user-frontend' ),
                    WPUF_VERSION,
                    self::REQUIRED_PRO_VERSION
                )
            )
        );
    }
}
