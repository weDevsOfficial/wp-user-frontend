<?php
/**
 * Onboarding screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Admin\Onboarding as Onboarding_Page;
use WeDevs\Wpuf\Platform\Onboarding\Wizard;
use WeDevs\Wpuf\Platform\Caps;

/**
 * The setup wizard (`index.php?page=wpuf-onboarding`, Admin\Onboarding) as the
 * full-screen admin app route `#/onboarding/:step`, like FlyHR's: React app
 * src/admin/apps/onboarding on the shared components, steps saved through
 * wpuf/v1/onboarding.
 *
 * @since WPUF_SINCE
 */
class Onboarding extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return Onboarding_Page::PAGE_SLUG;
    }

    /**
     * Who may run it: as the wizard page.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function capability() {
        return Caps::capability( Caps::MANAGE_SITE );
    }

    /**
     * Admin app routes: the wizard, and one step of it.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        $route = [
            'title'       => __( 'Set up User Frontend', 'wp-user-frontend' ),
            'app'         => 'onboarding',
            'boot'        => 'onboarding',
            'in_app'      => true,
            'container'   => 'wpuf-onboarding-root',
            'bodyClasses' => [ 'wpuf-onboarding-open' ],
        ];

        return [
            array_merge(
                $route,
                [
                    'id'   => 'onboarding',
                    'path' => '/onboarding',
                    'page' => 'index.php?page=' . Onboarding_Page::PAGE_SLUG,
                ]
            ),
            array_merge(
                $route,
                [
                    'id'   => 'onboarding-step',
                    'path' => '/onboarding/:step',
                    'page' => 'index.php?page=' . Onboarding_Page::PAGE_SLUG . '&step=:step',
                ]
            ),
        ];
    }

    /**
     * `?step=` opens that step.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route selection.
        $step = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '';

        return $step ? '/onboarding/' . $step : '/onboarding';
    }

    /**
     * "Run Onboarding Again" (Tools) wipes the last run before the hop.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_before_redirect() {
        $page = wpuf()->admin->onboarding;

        if ( $page instanceof Onboarding_Page ) {
            $page->maybe_restart();
        }
    }

    /**
     * On the app page: the wizard app and its sheet.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-onboarding-screen' );
        wp_enqueue_script( 'wpuf-onboarding-screen' );
    }

    /**
     * Window globals: the wizard state when the page opens on it (else the
     * app fetches it from wpuf/v1/onboarding when the route opens).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only route selection.
        $route = isset( $_GET['wpuf_route'] ) ? sanitize_text_field( wp_unslash( $_GET['wpuf_route'] ) ) : '';

        return [
            'wpufOnboarding' => [
                'state' => 0 === strpos( $route, '/onboarding' ) ? wpuf()->platform()->get( Wizard::class )->get_state() : null,
            ],
        ];
    }
}
