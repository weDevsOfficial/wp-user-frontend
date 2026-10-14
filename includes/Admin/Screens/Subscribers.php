<?php
/**
 * Subscribers screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

/**
 * Subscription > Subscribers (the subscribers of a pack). The page runs in
 * the subscriptions app (`/subscriptions?action=subscribers&id=N`, read from
 * `wpuf/v1/admin/subscriptions/{id}/subscribers`); the old URL
 * (`page=wpuf_subscribers&post_ID=N`) redirects there. The classic list
 * table (List_Table_Subscribers, views/subscribers.php) is gone.
 *
 * @since WPUF_SINCE
 */
class Subscribers extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_subscribers';
    }

    /**
     * Nothing to print: the old URL always redirects to the app route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {}

    /**
     * App route of an old Subscribers request: the pack's Subscribers view,
     * or the subscriptions list without a pack.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view state.
        $pack_id = isset( $_GET['post_ID'] ) ? absint( wp_unslash( $_GET['post_ID'] ) ) : 0;

        if ( ! $pack_id ) {
            return '/subscriptions';
        }

        $query       = [ 'action' => 'subscribers' ];
        $query['id'] = $pack_id;

        return '/subscriptions?' . http_build_query( $query );
    }
}
