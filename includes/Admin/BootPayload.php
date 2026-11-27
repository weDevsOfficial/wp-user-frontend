<?php
/**
 * Admin boot payload
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin;

/**
 * The shared `window.wpufAdmin` object each React admin screen boots from:
 * the common runtime values the apps (and Pro / add-ons) need, filtered per
 * screen. The legacy globals (`wpuf_forms_list`, `wpuf_form_builder`,
 * `wpufSubscriptions`, `wpuf_settings`, …) stay printed with their keys.
 *
 * @since WPUF_SINCE
 */
class BootPayload {

    /**
     * Screens already printed (one object per page).
     *
     * @var array
     */
    private $printed = [];

    /**
     * The payload for a screen.
     *
     * @since WPUF_SINCE
     *
     * @param string $screen Screen key (`post_forms`, `form_builder`, `subscriptions`, `settings`; Pro: `registration_forms`)
     *
     * @return array
     */
    public function data( $screen ) {
        $data = [
            'screen'     => $screen,
            'restUrl'    => esc_url_raw( rest_url() ),
            'restNonce'  => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'adminUrl'   => admin_url(),
            'assetUrl'   => WPUF_ASSET_URI,
            'version'    => WPUF_VERSION,
            'proVersion' => defined( 'WPUF_PRO_VERSION' ) ? WPUF_PRO_VERSION : '',
            'isPro'      => wpuf_is_pro_active(),
            'plan'       => function_exists( 'wpuf_pro_current_plan' ) ? (string) wpuf_pro_current_plan() : '',
            'canManage'  => current_user_can( wpuf_admin_role() ),
        ];

        /**
         * The boot payload of every WPUF React admin screen.
         *
         * @since WPUF_SINCE
         *
         * @param array  $data   Payload.
         * @param string $screen Screen key.
         */
        $data = (array) apply_filters( 'wpuf_admin_boot', $data, $screen );

        /**
         * The boot payload of one WPUF React admin screen
         * (`wpuf_admin_boot_post_forms`, `_form_builder`, `_subscriptions`, `_settings`).
         *
         * @since WPUF_SINCE
         *
         * @param array $data Payload.
         */
        return (array) apply_filters( 'wpuf_admin_boot_' . $screen, $data );
    }

    /**
     * Print the payload before a screen's app script (once per screen).
     *
     * @since WPUF_SINCE
     *
     * @param string $screen Screen key
     * @param string $handle App script handle
     *
     * @return void
     */
    public function attach( $screen, $handle ) {
        // On the admin app page the app prints every screen's data once
        // (attach_app()); the shell hands each route its screen's data.
        if ( isset( $this->printed[ $screen ] ) || isset( $this->printed['app'] ) || ( function_exists( 'wpuf_is_admin_app' ) && wpuf_is_admin_app() ) ) {
            return;
        }

        $this->printed[ $screen ] = true;

        wp_add_inline_script( $handle, 'window.wpufAdmin = ' . wp_json_encode( $this->data( $screen ) ) . ';', 'before' );
    }

    /**
     * Print the admin app's boot data before its shell: the app data plus every
     * screen's boot data (`wpuf_admin_boot` / `wpuf_admin_boot_{screen}` fire
     * as on the screens' own pages). The shell sets `window.wpufAdmin` to the
     * active route's screen data on every route change.
     *
     * @since WPUF_SINCE
     *
     * @param string $handle Script printed before every screen bundle (the runtime)
     * @param array  $app    Routes, initial route, globals, styles, page URL
     *
     * @return void
     */
    public function attach_app( $handle, array $app ) {
        if ( isset( $this->printed['app'] ) ) {
            return;
        }

        $this->printed['app'] = true;

        $screens = [];

        foreach ( $app['routes'] as $route ) {
            $id = isset( $route['boot'] ) ? $route['boot'] : $route['screen'];

            if ( 'app' === $route['mode'] && ! isset( $screens[ $id ] ) ) {
                $screens[ $id ] = $this->data( $id );
            }
        }

        $data            = $this->data( 'app' );
        $data['app']     = $app;
        $data['screens'] = $screens;

        wp_add_inline_script( $handle, 'window.wpufAdmin = ' . wp_json_encode( $data ) . ';', 'before' );
    }
}
