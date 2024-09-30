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
     * @since 4.0.11
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route name
     *
     * @since 4.0.11
     *
     * @var string
     */
    protected $base = 'wpuf_subscription';

    /**
     * Register the routes for the objects of the controller.
     *
     * @since 4.0.11
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
     * @since 4.0.11
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
     * @since 4.0.11
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
     * @since 4.0.11
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
     * @since 4.0.11
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
     * @since 4.0.11
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

        $args = shortcode_atts( $args, $request->get_params() );

        if ( 'all' === $args['post_status'] ) {
            $args['post_status'] = 'draft, publish, future, pending, private';
        }

        $subscriptions = wpuf()->subscription->get_subscriptions( $args );

        if ( ! is_array( $subscriptions ) ) {
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
     * @since 4.0.11
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function edit_item( $request ) {
        $subscription = ! empty( $request['subscription'] ) ? $request['subscription'] : '';
        $edit_single  = ! empty( $subscription['edit_single_row'] ) ? $subscription['edit_single_row'] : false;

        if ( empty( $subscription ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Something went wrong', 'wp-user-frontend' ),
                ]
            );
        }

        $id = ! empty( $subscription['ID'] ) ? (int) $subscription['ID'] : 0;

        if ( empty( $id ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription ID is required', 'wp-user-frontend' ),
                ]
            );
        }

        if ( $edit_single ) {
            $row   = ! empty( $subscription['edit_row_name'] ) ? sanitize_text_field( $subscription['edit_row_name'] ) : '';
            $value = ! empty( $subscription['edit_row_value'] ) ? sanitize_text_field( $subscription['edit_row_value'] ) : '';

            if ( empty( $row ) || empty( $value ) ) {
                return new WP_REST_Response(
                    [
                        'success' => false,
                        'message' => __( 'Failed to update', 'wp-user-frontend' ),
                    ]
                );
            }

            do_action( 'wpuf_before_update_subscription_single_row', $id, $request );
            $result = wp_update_post(
                [
                    'ID' => $id,
                    $row => $value,
                ]
            );
            do_action( 'wpuf_after_update_subscription_single_row', $id, $request );

            if ( empty( $result ) || is_wp_error( $result ) ) {
                return new WP_REST_Response(
                    [
                        'success' => false,
                        'message' => __( 'Failed to update subscription', 'wp-user-frontend' ),
                    ]
                );
            } else {
                return rest_ensure_response(
                    [
                        'success' => true,
                        'message' => __( 'Subscription updated successfully', 'wp-user-frontend' ),
                    ]
                );
            }
        }

        return $this->create_or_update_item( $request );
    }

    /**
     * Create a new item
     *
     * @since 4.0.11
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function create_or_update_item( $request ) {
        $subscription = ! empty( $request['subscription'] ) ? $request['subscription'] : '';

        if ( empty( $subscription ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Something went wrong', 'wp-user-frontend' ),
                ]
            );
        }

        $id   = ! empty( $subscription['ID'] ) ? (int) $subscription['ID'] : 0;
        $name = ! empty( $subscription['post_title'] ) ? sanitize_text_field( $subscription['post_title'] ) : '';

        // error if plan name contains #. PayPal doesn't allow # in package name
        if ( strpos( $name, '#' ) !== false ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription name cannot contain #', 'wp-user-frontend' ),
                ]
            );
        }
        $status                 = ! empty( $subscription['post_status'] ) ? sanitize_text_field(
            $subscription['post_status']
        ) : 'publish';
        $date                   = ! empty( $subscription['post_date'] ) ? sanitize_text_field(
            $subscription['post_date']
        ) : '';
        $post_content           = ! empty( $subscription['post_content'] ) ? sanitize_textarea_field(
            $subscription['post_content']
        ) : '';
        $billing_amount         = ! empty( $subscription['meta_value']['_billing_amount'] ) ? floatval( $subscription['meta_value']['_billing_amount'] ) : 0;
        $expiration_number      = ! empty( $subscription['meta_value']['_expiration_number'] ) ? (int) $subscription['meta_value']['_expiration_number'] : 0;
        $expiration_period      = ! empty( $subscription['meta_value']['_expiration_period'] ) ? sanitize_text_field(
            $subscription['meta_value']['_expiration_period']
        ) : 'day';
        $recurring_pay          = ! empty( $subscription['meta_value']['_recurring_pay'] ) ? sanitize_text_field(
            $subscription['meta_value']['_recurring_pay']
        ) : 'no';
        $billing_cycle_number   = ! empty( $subscription['meta_value']['_billing_cycle_number'] ) ? (int) $subscription['meta_value']['_billing_cycle_number'] : 0;
        $cycle_period           = ! empty( $subscription['meta_value']['_cycle_period'] ) ? sanitize_text_field(
            $subscription['meta_value']['_cycle_period']
        ) : '';
        $enable_billing_limit   = ! empty( $subscription['meta_value']['_enable_billing_limit'] ) ? sanitize_text_field(
            $subscription['meta_value']['_enable_billing_limit']
        ) : '';
        $billing_limit          = ! empty( $subscription['meta_value']['_billing_limit'] ) ? sanitize_text_field(
            $subscription['meta_value']['_billing_limit']
        ) : '';
        $trial_status           = ! empty( $subscription['meta_value']['_trial_status'] ) ? sanitize_text_field(
            $subscription['meta_value']['_trial_status']
        ) : 'no';
        $trial_duration         = ! empty( $subscription['meta_value']['_trial_duration'] ) ? (int) $subscription['meta_value']['_trial_duration'] : 0;
        $trial_duration_type    = ! empty( $subscription['meta_value']['_trial_duration_type'] ) ? sanitize_text_field(
            $subscription['meta_value']['_trial_duration_type']
        ) : 0;
        $post_type_name         = ! empty( $subscription['meta_value']['_post_type_name'] ) ? array_map(
            'sanitize_text_field', $subscription['meta_value']['_post_type_name']
        ) : '';
        $additional_cpt_options = ! empty( $subscription['meta_value']['additional_cpt_options'] ) ? array_map(
            'sanitize_text_field', $subscription['meta_value']['additional_cpt_options']
        ) : '';
        $enable_post_expir      = ! empty( $subscription['meta_value']['_enable_post_expiration'] ) ? sanitize_text_field(
            $subscription['meta_value']['_enable_post_expiration']
        ) : 'no';
        $post_expiration_number = ! empty( $subscription['meta_value']['_post_expiration_number'] ) ? (int) $subscription['meta_value']['_post_expiration_number'] : '';
        $post_expiration_period = ! empty( $subscription['meta_value']['_post_expiration_period'] ) ? sanitize_text_field(
            $subscription['meta_value']['_post_expiration_period']
        ) : '';
        $expire_post_status     = ! empty( $subscription['meta_value']['_expired_post_status'] ) ? sanitize_text_field(
            $subscription['meta_value']['_expired_post_status']
        ) : 'draft';
        $mail_after_expire      = ! empty( $subscription['meta_value']['_enable_mail_after_expired'] ) ? sanitize_text_field(
            $subscription['meta_value']['_enable_mail_after_expired']
        ) : 'no';
        $post_expire_msg        = ! empty( $subscription['meta_value']['_post_expiration_message'] ) ? wp_kses_post(
            $subscription['meta_value']['_post_expiration_message']
        ) : '';
        $total_feature_item     = ! empty( $subscription['meta_value']['_total_feature_item'] ) ? (int) $subscription['meta_value']['_total_feature_item'] : 0;
        $remove_feature_item    = ! empty( $subscription['meta_value']['_remove_feature_item'] ) ? sanitize_text_field(
            $subscription['meta_value']['_remove_feature_item']
        ) : '';

        if ( $recurring_pay !== 'no' && empty( $cycle_period ) ) {
            $cycle_period = 'day';
        }

        try {
            $current_time = wpuf_current_datetime();

            $post_arr = [
                'post_type'         => 'wpuf_subscription',
                'post_date'         => $date,
                'post_date_gmt'     => get_gmt_from_date( $date ),
                'post_content'      => $post_content,
                'post_title'        => $name,
                'post_status'       => $status,
                'post_modified'     => $current_time,
                'post_modified_gmt' => get_gmt_from_date( $current_time->format( 'Y-m-d H:i:s' ) ),
            ];

            if ( ! empty( $id ) ) {
                // update mode
                $post_arr['ID']  = $id; // ID of the post to update
                $success_message = __( 'Subscription updated successfully', 'wp-user-frontend' );
            } else {
                $success_message = __( 'Subscription added successfully', 'wp-user-frontend' );
            }

            do_action( 'wpuf_before_update_subscription_pack', $id, $request, $post_arr );
            $id = wp_insert_post( $post_arr );
            do_action( 'wpuf_before_update_subscription_pack', $id, $request, $post_arr );

            if ( empty( $id ) || is_wp_error( $id ) ) {
                return new WP_REST_Response(
                    [
                        'success' => false,
                        'message' => __( 'Failed to insert post', 'wp-user-frontend' ),
                    ]
                );
            }

            do_action( 'wpuf_before_update_subscription_pack_meta', $id, $request );

            update_post_meta( $id, '_billing_amount', $billing_amount );
            update_post_meta( $id, '_expiration_number', $expiration_number );
            update_post_meta( $id, '_expiration_period', $expiration_period );
            update_post_meta( $id, '_recurring_pay', $recurring_pay );
            update_post_meta( $id, '_billing_cycle_number', $billing_cycle_number );
            update_post_meta( $id, '_cycle_period', $cycle_period );
            update_post_meta( $id, '_enable_billing_limit', $enable_billing_limit );
            update_post_meta( $id, '_billing_limit', $billing_limit );
            update_post_meta( $id, '_trial_status', $trial_status );
            update_post_meta( $id, '_trial_duration', $trial_duration );
            update_post_meta( $id, '_trial_duration_type', $trial_duration_type );
            update_post_meta( $id, '_post_type_name', $post_type_name );
            update_post_meta( $id, 'additional_cpt_options', $additional_cpt_options );
            update_post_meta( $id, '_enable_post_expiration', $enable_post_expir );
            update_post_meta( $id, '_post_expiration_number', $post_expiration_number );
            update_post_meta( $id, '_post_expiration_period', $post_expiration_period );
            update_post_meta( $id, '_expired_post_status', $expire_post_status );
            update_post_meta( $id, '_enable_mail_after_expired', $mail_after_expire );
            update_post_meta( $id, '_post_expiration_message', $post_expire_msg );
            update_post_meta( $id, '_total_feature_item', $total_feature_item );
            update_post_meta( $id, '_remove_feature_item', $remove_feature_item );

            do_action( 'wpuf_after_update_subscription_pack_meta', $id, $request );

            return rest_ensure_response(
                [
                    'success' => true,
                    'message' => $success_message,
                ]
            );
        } catch ( Exception $e ) {
            return rest_ensure_response(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Check permission for API request
     *
     * @since 4.0.11
     *
     * @return bool
     */
    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }
}
