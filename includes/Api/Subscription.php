<?php

namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use Exception;
use WP_REST_Server;

class Subscription extends WP_REST_Controller {
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
    protected $base = 'wpuf_subscription';

    /**
     * Constructor.
     *
     * @since 4.7.0
     *
     * @param string $post_type Post type.
     */
    public function __construct( $post_type = 'wpuf_subscription' ) {
        $this->post_type = $post_type;

        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

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
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( true ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->base . '/(?P<subscription_id>\w+)',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'edit_item' ],
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

        $subscriptions = ( new \WeDevs\Wpuf\Admin\Subscription() )->get_subscriptions( $args );

        if ( ! $subscriptions ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Something went wrong', 'wp-user-frontend' ),
                ]
            );
        }

        return new WP_REST_Response(
            [
                'success'       => true,
                'subscriptions' => $subscriptions,
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
     * @return WP_REST_Response|\WP_Error
     */
    public function edit_item( $request ) {
        $subscription = ! empty( $request['subscription'] ) ? $request['subscription'] : '';

        if ( empty( $subscription ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Something went wrong', 'wp-user-frontend' ),
                ]
            );
        }

        $id         = ! empty( $request['subscription_id'] ) ? (int) $request['subscription_id'] : 0;
        $name       = ! empty( $subscription['planName'] ) ? sanitize_text_field( $subscription['planName'] ) : '';
        $mm         = ! empty( $subscription['mm'] ) ? (int) sanitize_text_field( $subscription['mm'] ) : 0;
        $jj         = ! empty( $subscription['jj'] ) ? (int) sanitize_text_field( $subscription['jj'] ) : 0;
        $aa         = ! empty( $subscription['aa'] ) ? (int) sanitize_text_field( $subscription['aa'] ) : 0;
        $hh         = ! empty( $subscription['hh'] ) ? (int) sanitize_text_field( $subscription['hh'] ) : 0;
        $mn         = ! empty( $subscription['mn'] ) ? (int) sanitize_text_field( $subscription['mn'] ) : 0;
        $ss         = ! empty( $subscription['ss'] ) ? (int) sanitize_text_field( $subscription['ss'] ) : 0;
        $status = ! empty( $subscription['isPrivate'] ) ? 'private' : 'publish';
        $time       = '';

        // error if plan name contains #. PayPal doesn't allow # in package name
        if ( strpos( $name, '#' ) !== false ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription name cannot contain #', 'wp-user-frontend' ),
                ]
            );
        }

        // check if the minute, hour and second is valid
        if ( $mn > 59 || $hh > 23 || $ss > 59 ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Invalid time', 'wp-user-frontend' ),
                ]
            );
        }

        if ( $mm && $jj && $aa ) {
            $time = $aa . '-' . $mm . '-' . $jj . ' ' . $hh . ':' . $mn . ':' . $ss;
        }

        try {
            $result = wp_update_post(
                [
                    'ID'            => $id, // ID of the post to update
                    'post_date'     => $time,
                    'post_title'    => $name,
                    'post_status'   => $status,
                    'post_date_gmt' => get_gmt_from_date( $time ),
                ]
            );

            if ( is_wp_error( $result ) ) {
                return new WP_REST_Response(
                    [
                        'success' => false,
                        'message' => $result->get_error_message(),
                    ]
                );
            }

            return rest_ensure_response(
                [
                    'success' => true,
                    'message' => __( 'Subscription updated successfully', 'wp-user-frontend' ),
                ]
            );
        } catch ( Exception $e ) {
            return new \WP_Error(
                'something_went_wrong',
                $e->getMessage(),
                [ 'status' => 422 ]
            );
        }
    }

    /**
     * Check permission for API request
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function permission_check() {
        // todo:: check permission properly
        return true;
        return current_user_can( 'manage_options' );
    }
}
