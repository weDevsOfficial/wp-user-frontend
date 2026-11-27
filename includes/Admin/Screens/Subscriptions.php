<?php
/**
 * Subscriptions screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * User Frontend > Subscriptions (React). Notices stay removed there
 * (Admin_Subscription::remove_notices()).
 *
 * @since WPUF_SINCE
 */
class Subscriptions extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_subscription';
    }

    /**
     * The React subscriptions screen scopes its Tailwind utilities to this class.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function body_class() {
        return 'wpuf-admin-screen';
    }

    /**
     * Load step: the `wpuf_load_subscription_page` hook (assets, notices, footer).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_subscription_page' );
    }

    /**
     * Admin app route of the subscriptions screen (task 5d).
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'subscriptions',
                'path'           => '/subscriptions',
                'title'          => __( 'Subscriptions', 'wp-user-frontend' ),
                'app'            => 'subscriptions',
                'boot'           => 'subscriptions',
                'in_app'         => true,
                'menuLink'       => true,
                'container'      => 'wpuf-subscription-page',
                'containerClass' => 'px-[20px]',
                'page'           => 'admin.php?page=wpuf_subscription',
            ],
        ];
    }

    /**
     * App route of a subscriptions page request: `/subscriptions` with its
     * view (list status and page, edit or new).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        $query = [];

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view state.
        foreach ( [ 'action', 'id', 'post_status', 'p' ] as $key ) {
            if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
                $query[ $key ] = sanitize_key( wp_unslash( $_GET[ $key ] ) );
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return '/subscriptions' . ( $query ? '?' . http_build_query( $query ) : '' );
    }
}
