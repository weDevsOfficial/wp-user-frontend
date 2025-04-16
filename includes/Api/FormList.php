<?php

namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
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
        $per_page    = ! empty( $request['per_page'] ) ? (int) sanitize_text_field( $request['per_page'] ) : 10;
        $page        = ! empty( $request['page'] ) ? (int) sanitize_text_field( $request['page'] ) : 1;
        $status      = ! empty( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'any'; // Default to 'any'
        $search_term = ! empty( $request['s'] ) ? sanitize_text_field( $request['s'] ) : '';
        $post_type   = ! empty( $request['post_type'] ) ? sanitize_text_field( $request['post_type'] ) : 'wpuf_forms';
        $offset      = ( $page - 1 ) * $per_page;

        // Base query args
        $query_args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            'posts_per_page' => $per_page,
            'offset'         => $offset,
            'order'        => 'ID',
            'orderby'          => 'DESC',
        ];

        // Add search term if present
        if ( ! empty( $search_term ) ) {
            $query_args['s'] = $search_term;
        }

        // Prepare args for the total count query
        $total_query_args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            'posts_per_page' => $per_page,
            'fields'         => 'ids',
        ];

        if ( ! empty( $search_term ) ) {
            $total_query_args['s'] = $search_term;
        }

        // Get total count for pagination based on status and search
        $total_query = new \WP_Query( $total_query_args );
        $total_posts = $total_query->found_posts;
        $total_pages = ceil( $total_posts / $per_page );

        // Execute the main query
        $query = new \WP_Query( $query_args );

        $forms = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get form settings
                $settings = get_post_meta( $post_id, 'wpuf_form_settings', true );

                $forms[] = [
                    'ID'                  => $post_id,
                    'post_title'          => get_the_title(),
                    'post_status'         => ! empty( $settings['post_status'] ) ? $settings['post_status'] : '',
                    'settings_post_type'  => ! empty( $settings['post_type'] ) ? $settings['post_type'] : '',
                    'settings_guest_post' => ! empty( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'],
                    'settings_user_role'  => ! empty( $settings['role'] ) ? $settings['role'] : '',
                ];
            }
        }

        wp_reset_postdata();

        return new WP_REST_Response(
            [
                'success' => true,
                'result'  => $forms,
                'pagination' => [
                    'total_items'  => $total_posts,
                    'total_pages'  => $total_pages,
                    'current_page' => $page,
                    'per_page'     => $per_page,
                ],
            ]
        );
    }

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
