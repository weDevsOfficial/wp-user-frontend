<?php
/**
 * Transactions screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\Transactions\TransactionService;

/**
 * User Frontend > Transactions: the admin app route `#/transactions` on
 * Platform\REST\Controllers\TransactionsController. The old URL redirects
 * there; an action link of develop's list table still runs first, through
 * TransactionService.
 *
 * @since WPUF_SINCE
 */
class Transactions extends Screen {

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf_transaction';
    }

    /**
     * The admin app route.
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'             => 'transactions',
                'path'           => '/transactions',
                'title'          => __( 'Transactions', 'wp-user-frontend' ),
                'app'            => 'transactions',
                'boot'           => 'transactions',
                'in_app'         => true,
                'menuLink'       => true,
                'notices'        => true,
                'container'      => 'wpuf-transactions-root',
                'containerClass' => 'px-[20px]',
            ],
        ];
    }

    /**
     * The route the old URL opens (its status tab).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view state.
        $status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

        return '/transactions' . ( in_array( $status, [ 'completed', 'pending' ], true ) ? '?status=' . $status : '' );
    }

    /**
     * Assets of the app route.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        wp_enqueue_style( 'wpuf-admin-pages' );
        wp_enqueue_script( 'wpuf-transactions' );
    }

    /**
     * What the route needs up front (rows load from REST).
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        $service = wpuf()->platform()->get( TransactionService::class );

        return [
            'wpufTransactions' => [
                'canManage' => Caps::can( Caps::MANAGE_SITE ),
                'gateways'  => $service->gateways(),
                'perPage'   => $service->per_page(),
            ],
        ];
    }
}
