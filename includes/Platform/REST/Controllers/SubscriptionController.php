<?php

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class SubscriptionController extends RestController {
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
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
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

        // Fields other plugins print into the classic pack form (SubscriptionStore::legacy_fields()).
        register_rest_route(
            $this->namespace, '/' . $this->base . '/fields', [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_legacy_fields' ],
                'permission_callback' => [ $this, 'permission_check' ],
                'args'                => [
                    'id' => [
                        'type'              => 'integer',
                        'default'           => 0,
                        'sanitize_callback' => 'absint',
                    ],
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

        // Register subscription settings endpoints
        register_rest_route(
            $this->namespace,
            '/subscription-settings', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_subscription_settings' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'update_subscription_settings' ],
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

        if ( ! $this->is_subscription( $subscription_id ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription not found', 'wp-user-frontend' ),
                ],
                404
            );
        }

        if ( ! Stores::subscriptions()->delete( $subscription_id ) ) {
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
     * Get a single subscription by ID
     *
     * @since 4.2.7
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response
     */
    public function get_item( $request ) {
        $subscription_id = ! empty( $request['subscription_id'] ) ? (int) sanitize_text_field( $request['subscription_id'] ) : 0;

        if ( ! $subscription_id ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription ID is required', 'wp-user-frontend' ),
                ]
            );
        }

        // One pack by ID: no _sort_order join, so a pack without that meta still loads.
        $args = [
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'p'              => $subscription_id,
            'meta_key'       => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- empty: removes the join.
            'orderby'        => 'ID',
        ];

        $subscriptions = wpuf()->subscription->get_subscriptions( $args );

        if ( ! is_array( $subscriptions ) || empty( $subscriptions ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription not found', 'wp-user-frontend' ),
                ]
            );
        }

        return new WP_REST_Response(
            [
                'success'      => true,
                'subscription' => $subscriptions[0],
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

        // The admin cards list the plan's features as the frontend plan card
        // does (read only; a save reads the post fields and `meta_value`).
        $coupons = $this->coupons_by_pack();

        foreach ( $subscriptions as $subscription ) {
            if ( is_object( $subscription ) ) {
                $subscription->card_features = array_values( array_map( 'wp_strip_all_tags', array_map( 'strval', wpuf_subscription_pack_features( $subscription ) ) ) );

                // Subscribers split (no request per card) and the coupons for this pack.
                $subscription->subscriber_counts = [
                    'total'     => count( wpuf()->subscription->subscription_pack_users( $subscription->ID ) ),
                    'cancelled' => count( Stores::subscribers()->user_ids( (int) $subscription->ID, 'Cancel' ) ),
                ];
                $subscription->card_coupons      = [
                    'only' => isset( $coupons['packs'][ (int) $subscription->ID ] ) ? $coupons['packs'][ (int) $subscription->ID ] : [],
                    'all'  => $coupons['all'],
                ];
            }
        }

        return new WP_REST_Response(
            [
                'success'       => true,
                'subscriptions' => $subscriptions,
            ]
        );
    }

    /**
     * Published coupons (Pro) per pack: `_package` lists pack ids, or `all`.
     * Read only, for the pack cards; empty without the coupon post type.
     *
     * @since WPUF_SINCE
     *
     * @return array { packs: { pack id: [ { id, code } ] }, all: int }
     */
    private function coupons_by_pack() {
        $out = [
            'packs' => [],
            'all'   => 0,
        ];

        if ( ! post_type_exists( 'wpuf_coupon' ) || ! current_user_can( wpuf_admin_role() ) ) {
            return $out;
        }

        $ids = get_posts(
            [
                'post_type'      => 'wpuf_coupon',
                'post_status'    => 'publish',
                'posts_per_page' => 100,
                'fields'         => 'ids',
            ]
        );

        foreach ( $ids as $coupon_id ) {
            $packs = (array) get_post_meta( $coupon_id, '_package', true );

            if ( in_array( 'all', $packs, true ) ) {
                ++$out['all'];
                continue;
            }

            foreach ( array_filter( array_map( 'absint', $packs ) ) as $pack_id ) {
                $out['packs'][ $pack_id ][] = [
                    'id'   => (int) $coupon_id,
                    'code' => wp_strip_all_tags( get_the_title( $coupon_id ) ),
                ];
            }
        }

        return $out;
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

        if ( ! $this->is_subscription( $id ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription not found', 'wp-user-frontend' ),
                ],
                404
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

            $result = Stores::subscriptions()->update_single_row( $id, $row, $value, $request );

            if ( is_wp_error( $result ) ) {
                $data = $result->get_error_data();

                return new WP_REST_Response(
                    [
                        'success' => false,
                        'message' => $result->get_error_message(),
                    ],
                    isset( $data['status'] ) ? $data['status'] : 200
                );
            }

            return rest_ensure_response(
                [
                    'success' => true,
                    'message' => __( 'Subscription updated successfully', 'wp-user-frontend' ),
                ]
            );
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

        if ( $id && ! $this->is_subscription( $id ) ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription not found', 'wp-user-frontend' ),
                ],
                404
            );
        }

        // error if plan name contains #. PayPal doesn't allow # in package name
        if ( strpos( $name, '#' ) !== false ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => __( 'Subscription name cannot contain #', 'wp-user-frontend' ),
                ]
            );
        }

        $invalid = $this->invalid_plan_values( $name, $subscription );

        if ( $invalid ) {
            return new WP_REST_Response(
                [
                    'success' => false,
                    'message' => $invalid,
                ]
            );
        }

        // The subscription store writes the pack and its meta and fires the
        // pack hooks (task 2.4b); the responses stay as they were.
        $saved = Stores::subscriptions()->save_from_rest( $subscription, $request );

        if ( is_wp_error( $saved ) ) {
            return rest_ensure_response(
                [
                    'success' => false,
                    'message' => $saved->get_error_message(),
                ]
            );
        }

        return rest_ensure_response(
            [
                'success' => true,
                'message' => $id ? __( 'Subscription updated successfully', 'wp-user-frontend' ) : __( 'Subscription added successfully', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Plan values the editor refuses, checked again for direct REST calls: a
     * name, no negative price, and for a recurring plan a price above 0 and a
     * billing cycle of at least 1 (a 0 cycle reached PayPal as a 0 interval).
     *
     * @since WPUF_SINCE
     *
     * @param string $name         Sanitized plan name.
     * @param array  $subscription Posted plan.
     *
     * @return string Message, or '' when the values are fine
     */
    private function invalid_plan_values( $name, $subscription ) {
        if ( '' === trim( $name ) ) {
            return __( 'Plan name is required.', 'wp-user-frontend' );
        }

        $meta = isset( $subscription['meta_value'] ) && is_array( $subscription['meta_value'] ) ? $subscription['meta_value'] : [];

        if ( ! $meta ) {
            return '';
        }

        // The editor writes the `_` keys; older payloads carry the plain ones.
        $get = function ( $key ) use ( $meta ) {
            if ( isset( $meta[ '_' . $key ] ) ) {
                return $meta[ '_' . $key ];
            }

            return isset( $meta[ $key ] ) ? $meta[ $key ] : '';
        };

        $amount    = $get( 'billing_amount' );
        $recurring = in_array( $get( 'recurring_pay' ), [ true, 1, 'on', 'yes', 'true', '1' ], true );

        if ( '' !== (string) $amount && ( ! is_numeric( $amount ) || (float) $amount < 0 ) ) {
            return __( 'Enter a price of 0 or more.', 'wp-user-frontend' );
        }

        if ( $recurring && (float) $amount <= 0 ) {
            return __( 'A recurring plan needs a price above 0.', 'wp-user-frontend' );
        }

        if ( $recurring && (int) $get( 'billing_cycle_number' ) < 1 ) {
            return __( 'Enter a billing cycle of 1 or more.', 'wp-user-frontend' );
        }

        return '';
    }

    /**
     * The classic pack form's fields from other plugins, for a pack (0: new).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return WP_REST_Response|\WP_Error
     */
    public function get_legacy_fields( $request ) {
        $id = (int) $request['id'];

        if ( $id && ! $this->is_subscription( $id ) ) {
            return new \WP_Error( 'wpuf_subscription_not_found', __( 'Subscription not found', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        return rest_ensure_response( Stores::subscriptions()->legacy_fields( $id ) );
    }

    /**
     * Get subscription settings
     *
     * @since 4.1.8
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_subscription_settings( $request ) {
        $settings = [
            // Empty string means use Tailwind's wpuf-bg-primary class
            'button_color' => Stores::settings()->get( 'wpuf_subscription_settings', 'button_color', '' ),
        ];

        return rest_ensure_response( $settings );
    }

    /**
     * Update subscription settings
     *
     * @since 4.1.8
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|\WP_Error
     */
    public function update_subscription_settings( $request ) {
        $params = $request->get_params();
        $settings = [];

        // Handle button_color - empty means use Tailwind primary
        if ( isset( $params['button_color'] ) && ! empty( $params['button_color'] ) ) {
            $sanitized_color = sanitize_hex_color( $params['button_color'] );

            // Validate that the color was properly sanitized
            if ( $sanitized_color === null || $sanitized_color === '' ) {
                return new \WP_Error(
                    'invalid_color',
                    __( 'Invalid color format. Please provide a valid hex color (e.g., #FF0000).', 'wp-user-frontend' ),
                    [ 'status' => 400 ]
                );
            }

            $settings['button_color'] = $sanitized_color;
        } else {
            // Empty string means use default Tailwind primary color
            $settings['button_color'] = '';
        }

        Stores::settings()->write_section( 'wpuf_subscription_settings', $settings );

        return rest_ensure_response(
            [
                'success'  => true,
                'settings' => $settings,
            ]
        );
    }

    /**
     * Whether an id belongs to a subscription pack.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Post id.
     *
     * @return bool
     */
    protected function is_subscription( $id ) {
        return Stores::subscriptions()->is_subscription( $id );
    }

    /**
     * Keep only term ids from a submitted list, leaving each id's type as sent
     * so stored values keep the shape the screen has always written.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $ids Submitted ids.
     *
     * @return array
     */
    protected function sanitize_term_ids( $ids ) {
        return Stores::subscriptions()->sanitize_term_ids( $ids );
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
