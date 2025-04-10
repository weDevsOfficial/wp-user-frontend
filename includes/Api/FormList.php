<?php

namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use Exception;
use WP_REST_Server;

class FormList extends WP_REST_Controller {
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
    protected $base = 'wpuf_form';

    /**
     * Register the routes for the objects of the controller.
     *
     * @since WPUF_SINCE
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_or_update_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/(?P<subscription_id>\d+)',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'edit_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/subscribers', [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'get_subscribers_count' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/count/(?P<status>\w+)', [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'total_subscriptions_count_by_status' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/count', [
                [
                    'methods'             => 'GET',
                    'callback'            => [ $this, 'total_subscriptions_count' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }

    /**
     * Get subscriptions count
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function total_subscriptions_count( $request ) {
        $count = wpuf()->subscription->total_subscriptions_count_array();

        if ( is_null( $count ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Failed to get subscriptions count', 'wp-user-frontend' ),
                ]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'count'   => $count,
            ]
        );
    }

    /**
     * Get subscriptions count based on status
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function total_subscriptions_count_by_status( $request ) {
        $status = ! empty( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'all';

        $count = wpuf()->subscription->total_subscriptions_count_by_status( $status );

        if ( is_null( $count ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Failed to get subscriptions count', 'wp-user-frontend' ),
                ]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true,
                'count'   => $count,
            ]
        );
    }

    /**
     * Delete an existing item
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function delete_item( $request ) {
        $subscription_id = ! empty( $request['subscription_id'] ) ? (int) sanitize_text_field( $request['subscription_id'] ) : 0;

        if ( ! $subscription_id ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription ID is required', 'wp-user-frontend' ),
                ]
            );
        }

        $result = wp_delete_post( $subscription_id, true );

        if ( ! $result ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Failed to delete subscription', 'wp-user-frontend' ),
                ]
            );
        } else {
            return new WP_REST_Response(
                [
                    'success' => true,
                    'message' => __( 'Subscription deleted successfully', 'wp-user-frontend' ),
                ]
            );
        }
    }

    /**
     * Get subscribers count based on subscription id
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_subscribers_count( $request ) {
        $subscription_id = ! empty( $request['subscription_id'] ) ? (int) sanitize_text_field( $request['subscription_id'] ) : 0;

        if ( ! $subscription_id ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription ID is required', 'wp-user-frontend' ),
                ]
            );
        }

        $subscribers = count( wpuf()->subscription->subscription_pack_users( $subscription_id ) );

        return new WP_REST_Response(
            [
                'success'    => true,
                'subscribers' => $subscribers,
            ]
        );
    }

    /**
     * Retrieves a collection of posts.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {
        $per_page = ! empty( $request['per_page'] ) ? (int) sanitize_text_field( $request['per_page'] ) : 10;
        $offset   = ! empty( $request['offset'] ) ? (int) sanitize_text_field( $request['offset'] ) : 0;

        $args = [
            'post_status'    => 'draft, publish, future, pending, private',
            'posts_per_page' => $per_page,
            'offset'         => $offset,
        ];

        $defauls = [
            'post_status' => 'any',
            'orderby'     => 'DESC',
            'order'       => 'ID',
        ];

        $args = shortcode_atts( $args, $request->get_params() );

        $query = new \WP_Query( $args );

        $forms = [];

        if ( $query->have_posts() ) {
            $i = 0;

            while ( $query->have_posts() ) {
                $query->the_post();

                $form = $query->posts[ $i ];

                $settings = get_post_meta( get_the_ID(), 'wpuf_form_settings', true );

                $forms[ $i ] = [
                    'ID'                    => $form->ID,
                    'post_title'            => $form->post_title,
                    'post_status'           => $form->post_status,
                    'settings_post_type'    => isset( $settings['post_type'] ) ? $settings['post_type'] : '',
                    'settings_post_status'  => isset( $settings['post_status'] ) ? $settings['post_status'] : '',
                    'settings_guest_post'   => isset( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'] ? 'guest_post' === $settings['post_permission'] : '',
                ];

                $i++;
            }
        }

        $forms = apply_filters( 'wpuf_post_forms_list_table_query_results', $forms, $query, $args );

        wp_reset_postdata();


        return new WP_REST_Response(
            [
                'success' => true,
                'result'  => $forms,
            ]
        );
    }

    /**
     * Edit an existing item
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function edit_item( $request ) {}

    /**
     * Create a new item
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function create_or_update_item( $request ) {}

    /**
     * Check permission for API request
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }
}
