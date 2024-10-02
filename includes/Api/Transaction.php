<?php

namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Transaction extends WP_REST_Controller {

    /**
     * The namespace of this controller's route.
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route name
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    protected $base = 'transactions';

    /**
     * Register the routes for the objects of the controller.
     *
     * @since WPUF_SINCE
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_transactions' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }

    /**
     * Get transactions
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_transactions( $request ) {
        $params   = $request->get_params();
        $time     = ! empty( $params['time'] ) ? $params['time'] : 'all';
        $per_page = ! empty( $params['per_page'] ) ? $params['per_page'] : 10;

        $args = [
            'time'     => $time,
            'per_page' => $per_page,
        ];

        $default = [
            'post_type' => 'wpuf_transaction',
            'per_page'  => 10,
        ];

        $args         = wp_parse_args( $args, $default );
        $transactions = new \WeDevs\Wpuf\Admin\Transaction();
        $summary      = $transactions->get_transaction_summary( $args );

        return new WP_REST_Response(
            [
                'success' => true,
                'result'  => $summary,
                'message' => __( 'Transactions fetched successfully', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Check permission for the request
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }
}
