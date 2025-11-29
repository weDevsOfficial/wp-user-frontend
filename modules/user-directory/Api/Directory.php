<?php
/**
 * User Directory REST API Controller
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Modules\User_Directory\Api;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\Wpuf\Modules\User_Directory\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Directory REST API Class
 *
 * Handles REST API endpoints for User Directory CRUD operations.
 *
 * @since 4.3.0
 */
class Directory extends WP_REST_Controller {

    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'wpuf/v1';

    /**
     * Route base
     *
     * @var string
     */
    protected $rest_base = 'user_directory';

    /**
     * Register REST routes
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function register_routes() {
        // Get all / Create
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
            ]
        );

        // Get single / Update / Delete
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
            ]
        );

        // Public search endpoint (no auth required for frontend AJAX)
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/search',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'search_users' ],
                    'permission_callback' => '__return_true', // Public endpoint
                ],
            ]
        );

        // User count endpoint
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/user_count',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_user_count' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
            ]
        );
    }

    /**
     * Check permissions for API access
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return bool|WP_Error
     */
    public function permissions_check( $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                'rest_forbidden',
                __( 'You do not have permission to access this resource.', 'wp-user-frontend' ),
                [ 'status' => rest_authorization_required_code() ]
            );
        }

        return true;
    }

    /**
     * Get all directories
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_items( $request ) {
        $per_page = ! empty( $request['per_page'] ) ? absint( $request['per_page'] ) : 10;
        $page     = ! empty( $request['page'] ) ? absint( $request['page'] ) : 1;
        $search   = ! empty( $request['s'] ) ? sanitize_text_field( $request['s'] ) : '';
        $offset   = ( $page - 1 ) * $per_page;

        $query_args = [
            'post_type'      => User_Directory::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => $per_page,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'DESC',
        ];

        if ( $search ) {
            $query_args['s'] = $search;
        }

        $query = new \WP_Query( $query_args );

        $directories = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $directories[] = [
                    'ID'           => get_the_ID(),
                    'post_title'   => get_the_title(),
                    'post_status'  => get_post_status(),
                    'post_content' => get_the_content(),
                ];
            }
        }

        wp_reset_postdata();

        $total_items = $query->found_posts;
        $total_pages = $per_page > 0 ? (int) ceil( $total_items / $per_page ) : 1;

        return rest_ensure_response( [
            'success'    => true,
            'result'     => $directories,
            'pagination' => [
                'total_pages'  => $total_pages,
                'total_items'  => $total_items,
                'per_page'     => $per_page,
                'current_page' => $page,
            ],
        ] );
    }

    /**
     * Get single directory
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_item( $request ) {
        $id   = absint( $request['id'] );
        $post = get_post( $id );

        if ( ! $post || User_Directory::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'not_found',
                __( 'Directory not found.', 'wp-user-frontend' ),
                [ 'status' => 404 ]
            );
        }

        return rest_ensure_response( [
            'success' => true,
            'result'  => [
                'ID'           => $post->ID,
                'post_title'   => $post->post_title,
                'post_status'  => $post->post_status,
                'post_content' => $post->post_content,
            ],
        ] );
    }

    /**
     * Create a new directory
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_item( $request ) {
        // Check free limit
        if ( User_Directory::has_reached_limit() ) {
            return new WP_Error(
                'limit_reached',
                __( 'You can only create 1 directory in the free version. Upgrade to Pro for unlimited directories.', 'wp-user-frontend' ),
                [ 'status' => 403 ]
            );
        }

        $params = $request->get_json_params();

        // Sanitize title
        $post_title = ! empty( $params['directory_title'] )
            ? sanitize_text_field( $params['directory_title'] )
            : __( 'Unnamed Directory', 'wp-user-frontend' );

        // Build settings array
        $settings = $this->sanitize_settings( $params );

        // Force free version defaults
        $settings = $this->enforce_free_limits( $settings );

        // Insert post
        $post_id = wp_insert_post( [
            'post_title'   => $post_title,
            'post_type'    => User_Directory::POST_TYPE,
            'post_status'  => 'publish',
            'post_content' => wp_json_encode( $settings ),
        ] );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        if ( ! $post_id ) {
            return new WP_Error(
                'save_failed',
                __( 'Failed to save directory.', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }

        // Flush rewrite rules for pretty URLs
        flush_rewrite_rules();

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Directory created successfully.', 'wp-user-frontend' ),
            'post_id' => $post_id,
        ] );
    }

    /**
     * Update a directory
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_item( $request ) {
        $id   = absint( $request['id'] );
        $post = get_post( $id );

        if ( ! $post || User_Directory::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'not_found',
                __( 'Directory not found.', 'wp-user-frontend' ),
                [ 'status' => 404 ]
            );
        }

        $params = $request->get_json_params();

        // Sanitize title
        $post_title = ! empty( $params['directory_title'] )
            ? sanitize_text_field( $params['directory_title'] )
            : $post->post_title;

        // Build settings array
        $settings = $this->sanitize_settings( $params );

        // Force free version defaults
        $settings = $this->enforce_free_limits( $settings );

        // Update post
        $updated = wp_update_post( [
            'ID'           => $id,
            'post_title'   => $post_title,
            'post_content' => wp_json_encode( $settings ),
        ] );

        if ( is_wp_error( $updated ) ) {
            return $updated;
        }

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Directory updated successfully.', 'wp-user-frontend' ),
            'post_id' => $id,
        ] );
    }

    /**
     * Delete a directory
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function delete_item( $request ) {
        $id   = absint( $request['id'] );
        $post = get_post( $id );

        if ( ! $post || User_Directory::POST_TYPE !== $post->post_type ) {
            return new WP_Error(
                'not_found',
                __( 'Directory not found.', 'wp-user-frontend' ),
                [ 'status' => 404 ]
            );
        }

        $deleted = wp_delete_post( $id, true );

        if ( ! $deleted ) {
            return new WP_Error(
                'delete_failed',
                __( 'Failed to delete directory.', 'wp-user-frontend' ),
                [ 'status' => 500 ]
            );
        }

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Directory deleted successfully.', 'wp-user-frontend' ),
        ] );
    }

    /**
     * Get user count based on filters
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function get_user_count( $request ) {
        $roles         = $request->get_param( 'roles' );
        $exclude_users = $request->get_param( 'exclude_users' );
        $max_item      = $request->get_param( 'max_item' );

        $args = [ 'count_total' => true ];

        // Handle roles - skip filter if 'all' or empty
        if ( $roles && 'all' !== strtolower( $roles ) ) {
            $args['role__in'] = array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', $roles ) ) );
        }

        // Handle excluded users
        if ( $exclude_users ) {
            $excluded_user_ids = array_map( 'intval', array_map( 'trim', explode( ',', $exclude_users ) ) );
            $excluded_user_ids = array_filter( $excluded_user_ids ); // Remove zeros/false values
            if ( ! empty( $excluded_user_ids ) ) {
                $args['exclude'] = $excluded_user_ids;
            }
        }

        $user_query = new \WP_User_Query( $args );
        $count      = isset( $user_query->total_users ) ? (int) $user_query->total_users : 0;

        // Apply max_item limit if set and greater than 0
        if ( $max_item && intval( $max_item ) > 0 ) {
            $count = min( $count, intval( $max_item ) );
        }

        return rest_ensure_response( [
            'success' => true,
            'count'   => $count,
        ] );
    }

    /**
     * Search users for frontend AJAX
     *
     * @since 4.3.0
     *
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response
     */
    public function search_users( $request ) {
        $directory_id = ! empty( $request['directory_id'] ) ? absint( $request['directory_id'] ) : 0;
        $search       = ! empty( $request['search'] ) ? sanitize_text_field( $request['search'] ) : '';
        $page         = ! empty( $request['page'] ) ? absint( $request['page'] ) : 1;
        $orderby      = ! empty( $request['orderby'] ) ? sanitize_text_field( $request['orderby'] ) : 'ID';
        $order        = ! empty( $request['order'] ) ? strtoupper( sanitize_text_field( $request['order'] ) ) : 'DESC';
        $max_item     = ! empty( $request['max_item'] ) ? absint( $request['max_item'] ) : 12;
        $avatar_size  = ! empty( $request['avatar_size'] ) ? absint( $request['avatar_size'] ) : 128;
        $base_url     = ! empty( $request['base_url'] ) ? sanitize_text_field( $request['base_url'] ) : '';

        // Get directory settings
        $settings = [];
        if ( $directory_id ) {
            $post = get_post( $directory_id );
            if ( $post && User_Directory::POST_TYPE === $post->post_type ) {
                $settings = json_decode( $post->post_content, true ) ?: [];
            }
        }

        // Merge with defaults
        $settings = wp_parse_args( $settings, User_Directory::get_default_settings() );

        // Build user query args - check users_per_page first (admin setting), then per_page
        $settings_per_page = absint( $settings['users_per_page'] ?? ( $settings['per_page'] ?? 12 ) );
        $per_page = $max_item > 0 ? $max_item : $settings_per_page;
        $offset   = ( $page - 1 ) * $per_page;

        // Map orderby values
        $orderby_map = [
            'id'              => 'ID',
            'username'        => 'user_login',
            'email'           => 'user_email',
            'display_name'    => 'display_name',
            'user_registered' => 'user_registered',
        ];
        $orderby = isset( $orderby_map[ strtolower( $orderby ) ] ) ? $orderby_map[ strtolower( $orderby ) ] : 'ID';

        $args = [
            'number'  => $per_page,
            'offset'  => $offset,
            'orderby' => $orderby,
            'order'   => in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'DESC',
        ];

        // Roles filter
        if ( ! empty( $settings['roles'] ) && is_array( $settings['roles'] ) ) {
            $args['role__in'] = array_map( 'sanitize_text_field', $settings['roles'] );
        }

        // Exclude users
        if ( ! empty( $settings['exclude_users'] ) ) {
            $exclude_ids = array_map( 'absint', explode( ',', $settings['exclude_users'] ) );
            $args['exclude'] = $exclude_ids;
        }

        // Search
        if ( $search ) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = [ 'user_login', 'user_nicename', 'display_name', 'user_email' ];
        }

        // Execute query
        $user_query = new \WP_User_Query( $args );
        $users      = $user_query->get_results();
        $total      = $user_query->get_total();
        $max_pages  = (int) ceil( $total / $per_page );

        // Build rows HTML
        $rows_html = '';
        if ( ! empty( $users ) ) {
            ob_start();
            // Set avatar size for row template (variable name must match what row-3.php expects)
            $avatar_size = $avatar_size > 0 ? $avatar_size : absint( $settings['avatar_size'] ?? 192 );
            foreach ( $users as $user ) {
                // Build all_data for template
                $all_data = [
                    'id'                => $directory_id,
                    'directory_id'      => $directory_id,
                    'profile_permalink' => $settings['profile_base'] ?? 'username',
                    'base_url'          => $base_url,
                    'is_shortcode'      => true,
                    'current_page'      => $page,
                    'orderby'           => strtolower( $orderby === 'ID' ? 'id' : $orderby ),
                    'order'             => strtolower( $order ),
                    'search'            => $search,
                ];
                include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/row-3.php';
            }
            $rows_html = ob_get_clean();
        }

        // Build pagination HTML
        $pagination_html = '';
        if ( $max_pages > 1 ) {
            $pagination = [
                'total_pages'  => $max_pages,
                'current_page' => $page,
                'per_page'     => $per_page,
                'total_items'  => $total,
            ];

            // Build query args for pagination - always include orderby and order
            $query_args = [];
            if ( $search ) {
                $query_args['search'] = $search;
            }
            // Always include orderby and order so dropdowns stay in sync
            $query_args['orderby'] = strtolower( $orderby === 'ID' ? 'id' : $orderby );
            $query_args['order'] = strtolower( $order );

            $layout = 'layout-3';

            ob_start();
            include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/pagination-shortcode.php';
            $pagination_html = ob_get_clean();
        }

        return rest_ensure_response( [
            'success'         => true,
            'usercount'       => count( $users ),
            'total'           => $total,
            'max_pages'       => $max_pages,
            'current_page'    => $page,
            'rows_html'       => $rows_html,
            'pagination_html' => $pagination_html,
            'announce'        => sprintf(
                /* translators: %d: number of users found */
                _n( '%d user found', '%d users found', $total, 'wp-user-frontend' ),
                $total
            ),
        ] );
    }

    /**
     * Sanitize settings from request
     *
     * @since 4.3.0
     *
     * @param array $params Request parameters.
     *
     * @return array
     */
    private function sanitize_settings( $params ) {
        $settings = [];

        // Text fields
        $text_fields = [
            'directory_layout', 'profile_layout', 'profile_base',
            'avatar_size', 'profile_size', 'orderby', 'order',
            'exclude_users', 'search_placeholder',
        ];

        foreach ( $text_fields as $field ) {
            if ( isset( $params[ $field ] ) ) {
                $settings[ $field ] = sanitize_text_field( $params[ $field ] );
            }
        }

        // Integer fields (use absint for positive-only integers)
        $int_fields = [ 'per_page', 'users_per_row', 'users_per_page', 'max_users' ];

        foreach ( $int_fields as $field ) {
            if ( isset( $params[ $field ] ) ) {
                $settings[ $field ] = absint( $params[ $field ] );
            }
        }

        // Handle max_item specially: can be -1 (all users), null, or a positive integer
        if ( array_key_exists( 'max_item', $params ) ) {
            $max_item_value = $params['max_item'];
            if ( null === $max_item_value || '' === $max_item_value ) {
                // Empty/null means unlimited - store as null (same as Pro)
                $settings['max_item'] = null;
            } else {
                // Store as integer (preserves -1 for unlimited, or positive value for limit)
                $settings['max_item'] = intval( $max_item_value );
            }
        }

        // Boolean fields
        $bool_fields = [ 'enable_search', 'enable_pagination' ];

        foreach ( $bool_fields as $field ) {
            if ( isset( $params[ $field ] ) ) {
                $settings[ $field ] = filter_var( $params[ $field ], FILTER_VALIDATE_BOOLEAN );
            }
        }

        // Array fields
        if ( isset( $params['roles'] ) && is_array( $params['roles'] ) ) {
            $settings['roles'] = array_map( 'sanitize_text_field', $params['roles'] );
        }

        if ( isset( $params['searchable_fields'] ) && is_array( $params['searchable_fields'] ) ) {
            $settings['searchable_fields'] = array_map( 'sanitize_text_field', $params['searchable_fields'] );
        }

        // Profile tabs (complex array)
        if ( isset( $params['profile_tabs'] ) && is_array( $params['profile_tabs'] ) ) {
            $settings['profile_tabs'] = $params['profile_tabs'];
        }

        if ( isset( $params['profile_tabs_order'] ) && is_array( $params['profile_tabs_order'] ) ) {
            $settings['profile_tabs_order'] = array_map( 'sanitize_text_field', $params['profile_tabs_order'] );
        }

        // Handle excluded_users array (like Pro version) - convert to exclude_users string
        if ( isset( $params['excluded_users'] ) && is_array( $params['excluded_users'] ) ) {
            $settings['excluded_users'] = $params['excluded_users'];
            // Also store as exclude_users string for backward compatibility
            $user_ids = $this->extract_user_ids_from_excluded_users( $params['excluded_users'] );
            $settings['exclude_users'] = $user_ids;
        }

        return $settings;
    }

    /**
     * Enforce free version limits on settings
     *
     * @since 4.3.0
     *
     * @param array $settings Settings array.
     *
     * @return array
     */
    private function enforce_free_limits( $settings ) {
        if ( wpuf_is_pro_active() ) {
            return $settings;
        }

        // Force free layout
        $settings['directory_layout'] = User_Directory::FREE_DIRECTORY_LAYOUT;
        $settings['profile_layout']   = User_Directory::FREE_PROFILE_LAYOUT;

        // Force free orderby (User ID only)
        $settings['orderby'] = 'ID';

        // Force free avatar size
        if ( ! in_array( $settings['avatar_size'] ?? '', [ '192', '128' ], true ) ) {
            $settings['avatar_size'] = '192';
        }

        // Force free profile size
        if ( ! in_array( $settings['profile_size'] ?? '', [ 'thumbnail', 'medium' ], true ) ) {
            $settings['profile_size'] = 'medium';
        }

        // Profile tabs: Don't set profile_tabs in Free version to avoid breaking Pro
        // Pro will use its own defaults when profile_tabs is not set

        return $settings;
    }

    /**
     * Extract user IDs from excluded_users array
     *
     * @since 4.3.0
     *
     * @param array $excluded_users Array of user objects or user IDs.
     *
     * @return string Comma-separated user IDs.
     */
    private function extract_user_ids_from_excluded_users( $excluded_users ) {
        if ( ! is_array( $excluded_users ) || empty( $excluded_users ) ) {
            return '';
        }

        $user_ids = [];

        foreach ( $excluded_users as $user ) {
            if ( is_array( $user ) && isset( $user['id'] ) ) {
                $user_ids[] = absint( $user['id'] );
            } elseif ( is_numeric( $user ) ) {
                $user_ids[] = absint( $user );
            }
        }

        return implode( ',', array_unique( array_filter( $user_ids ) ) );
    }
}
