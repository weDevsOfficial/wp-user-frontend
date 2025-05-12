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
     * @since 4.1.4
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route name
     *
     * @since 4.1.4
     *
     * @var string
     */
    protected $base = 'wpuf_form';

    /**
     * Register the routes for the objects of the controller.
     *
     * @since 4.1.4
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }

    /**
     * Retrieves a collection of posts.
     *
     * @since 4.1.4
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

                $post    = get_post();
                $post_id = $post->ID;

                // Get form settings
                $settings = get_post_meta( $post_id, 'wpuf_form_settings', true );

                // Get post count for this form
                $post_count = $this->get_form_post_count( $post_id, $settings );

                $forms[] = [
                    'ID'                  => $post_id,
                    'post_title'          => get_the_title(),
                    'form_status'         => ! empty( $post->post_status ) ? $post->post_status : '',
                    'post_status'         => ! empty( $settings['post_status'] ) ? $settings['post_status'] : '',
                    'settings_post_type'  => ! empty( $settings['post_type'] ) ? $settings['post_type'] : '',
                    'settings_guest_post' => ! empty( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'],
                    'settings_user_role'  => ! empty( $settings['role'] ) ? $settings['role'] : '',
                    'post_count'          => $post_count,
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
     * Get post count for a form
     *
     * @since 4.1.4
     *
     * @param int   $form_id  Form ID
     * @param array $settings Form settings
     *
     * @return int
     */
    private function get_form_post_count($form_id, $settings) {
        $post_type = !empty($settings['post_type']) ? $settings['post_type'] : 'post';

        $args = [
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_wpuf_form_id',
                    'value'   => $form_id,
                    'compare' => '='
                ]
            ]
        ];

        $query = new \WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Check permission for API request
     *
     * @since 4.1.4
     *
     * @return bool
     */
    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }
}
