<?php
/**
 * Settings screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * User Frontend > Settings: the React settings app (the classic
 * WeDevs_Settings_API screen and its switch are gone).
 *
 * @since WPUF_SINCE
 */
class Settings extends Screen {

    use PrintsNotices;

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-settings';
    }

    /**
     * The React app shows notices in its wrapper.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function captures_notices() {
        return true;
    }

    /**
     * The React settings screen scopes its Tailwind utilities to this class.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function body_class() {
        return 'wpuf-admin-screen';
    }

    /**
     * Load step: enqueue the React app.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wp-components' );

        // Rich-text (wysiwyg) settings fields need the WordPress TinyMCE editor.
        wp_enqueue_editor();
        wp_enqueue_media();

        // Hide the WordPress admin footer text/version on the React settings page.
        add_filter( 'admin_footer_text', '__return_empty_string', 99 );
        add_filter( 'update_footer', '__return_empty_string', 99 );

        // Registered in the shared Assets registry (task 2.5b).
        wp_enqueue_style( 'wpuf-settings-screen' );

        $handle = 'wpuf-settings-screen';

        wp_enqueue_script( $handle );
        // The settings schema and its helpers (settings-options / settings-screen).
        Stores::settings()->load_schema();

        wpuf()->platform()->get( BootPayload::class )->attach( 'settings', $handle );

        /**
         * The React settings screen is loading: enqueue a bundle that adds field
         * types (`wpuf.settings.field.<type>` filter) or extends the screen.
         *
         * @since WPUF_SINCE
         *
         * @param string $handle The settings app's script handle (depend on it).
         */
        do_action( 'wpuf_settings_app_scripts', $handle );

        wp_localize_script(
            $handle,
            'wpuf_settings',
            [
                'rest_url'    => esc_url_raw( rest_url() ),
                'nonce'       => wp_create_nonce( 'wp_rest' ),
                'is_pro'      => wpuf_is_pro_active(),
                'asset_url'   => WPUF_ASSET_URI,
                // Quick links next to page / form selects (edit, add new).
                'admin_url'   => admin_url(),
                'version'     => WPUF_VERSION,
                'pro_version' => defined( 'WPUF_PRO_VERSION' ) ? WPUF_PRO_VERSION : '',
                'plan'        => function_exists( 'wpuf_pro_current_plan' ) ? wpuf_pro_current_plan() : '',
                'upgrade_url'   => 'https://wedevs.com/wp-user-frontend-pro/pricing/',
                'support_url'   => 'https://wedevs.com/docs/wp-user-frontend-pro/',
                // Settings of other plugins the React screen cannot show.
                'legacy_only'  => wpuf_settings_legacy_only_items(),
                // WordPress registration (registration forms need it open), with
                // where to change it; same check as the registration form.
                'registration' => [
                    'open' => is_multisite()
                        ? in_array( Stores::settings()->wp_option( 'registration', false, true ), [ 'user', 'all' ], true )
                        : 1 === (int) Stores::settings()->wp_option( 'users_can_register' ),
                    'url'  => is_multisite() ? network_admin_url( 'settings.php#registration' ) : admin_url( 'options-general.php#users_can_register' ),
                ],
            ]
        );
    }

    /**
     * Print the React mount.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        $this->print_notices();
        ?>
        <div id="wpuf-settings-root" class="px-[20px]">
            <noscript>
                <strong>
                    <?php esc_html_e( 'This page requires JavaScript. Please enable it to manage settings.', 'wp-user-frontend' ); ?>
                </strong>
            </noscript>
            <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
        </div>
        <?php
    }

    /**
     * Admin app route of the React settings screen (task 5d).
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'settings',
                'path'           => '/settings',
                'title'          => __( 'Settings', 'wp-user-frontend' ),
                'app'            => 'settings',
                'boot'           => 'settings',
                'in_app'         => true,
                'menuLink'       => true,
                'container'      => 'wpuf-settings-root',
                'containerClass' => 'px-[20px]',
                'notices'        => true,
                'page'           => 'admin.php?page=wpuf-settings',
            ],
        ];
    }

    /**
     * The React settings load only enqueues the screen and builds its data
     * (the one-time "new screen" notice included): nothing for the redirect
     * hop to do; the app page loads it.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_before_redirect() {}

    /**
     * App route of a settings page request: `/settings` with its tab and
     * sub-tab.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view state.
        $query = array_filter(
            [
                'tab' => isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '',
                'sub' => isset( $_GET['sub'] ) ? sanitize_key( wp_unslash( $_GET['sub'] ) ) : '',
            ]
        );
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return '/settings' . ( $query ? '?' . http_build_query( $query ) : '' );
    }
}
