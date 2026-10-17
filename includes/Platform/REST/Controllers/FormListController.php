<?php

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Stores\Stores;

use WP_Error;
use WeDevs\Wpuf\Platform\REST\RestController;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class FormListController extends RestController {

    /**
     * Sortable columns of the forms lists (REST name => WP_Query orderby).
     */
    const FORM_ORDERBY = [
        'id'    => 'ID',
        'title' => 'title',
        'date'  => 'date',
    ];

    /**
     * Sortable columns of a form's submissions.
     */
    const SUBMISSION_ORDERBY = [
        'date'  => 'date',
        'title' => 'title',
    ];

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
        // Form lists: newest first by default, sortable by title or date.
        $this->route(
            '/' . $this->base,
            WP_REST_Server::READABLE,
            'get_items',
            [ $this, 'permission_check' ],
            [
                'post_type' => [
                    'type'              => 'string',
                    'default'           => 'wpuf_forms',
                    'sanitize_callback' => 'sanitize_key',
                ],
                'status'    => [
                    'type'              => 'string',
                    'default'           => 'any',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ] + $this->collection_args( array_keys( self::FORM_ORDERBY ), 'id', [ 'per_page' => 10, 'search' => 's' ] )
        );

        $this->route(
            '/' . $this->base . '/(?P<id>[\d]+)/submissions',
            WP_REST_Server::READABLE,
            'get_submissions',
            [ $this, 'permission_check' ],
            [
                'id'     => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
                'status' => [
                    'type'              => 'string',
                    'default'           => 'any',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ] + $this->collection_args( array_keys( self::SUBMISSION_ORDERBY ), 'date', [ 'per_page' => 10, 'search' => 's' ] )
        );
    }

    /**
     * WP_Query order of a request: the column from the allowlist, ASC / DESC.
     *
     * @param WP_REST_Request $request Request
     * @param array           $columns REST column => WP_Query orderby
     * @param string          $default Default REST column
     *
     * @return array orderby, order
     */
    private function query_order( $request, array $columns, $default ) {
        $column = isset( $columns[ $request['orderby'] ] ) ? $request['orderby'] : $default;

        return [
            'orderby' => $columns[ $column ],
            'order'   => 'asc' === $request['order'] ? 'ASC' : 'DESC',
        ];
    }

    /**
     * The posts submitted through one post form (the `_wpuf_form_id` meta),
     * newest first, for the forms list's Submissions table.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_submissions( $request ) {
        $form_id = absint( $request['id'] );

        if ( ! $form_id || 'wpuf_forms' !== get_post_type( $form_id ) ) {
            return new WP_Error( 'wpuf_invalid_form', __( 'Form not found.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        $settings  = wpuf_get_form_settings( $form_id );
        $post_type = ! empty( $settings['post_type'] ) ? sanitize_key( $settings['post_type'] ) : 'post';
        $per_page  = min( 100, max( 1, absint( $request['per_page'] ) ) );
        $page      = max( 1, absint( $request['page'] ) );
        $status    = sanitize_key( $request['status'] );
        $search    = sanitize_text_field( $request['s'] );
        $tabs      = [ 'publish', 'pending', 'draft', 'future', 'private' ];

        if ( ! in_array( $status, $tabs, true ) ) {
            $status = 'any';
        }

        $by_form = [
            [
                'key'   => '_wpuf_form_id',
                'value' => $form_id,
            ],
        ];

        $args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'meta_query'     => $by_form,
        ] + $this->query_order( $request, self::SUBMISSION_ORDERBY, 'date' );

        if ( '' !== $search ) {
            $args['s'] = $search;
        }

        $query = new \WP_Query( $args );

        // Count per status tab (search applied, like the list's tabs).
        $counts = [ 'any' => 0 ];

        foreach ( $tabs as $tab ) {
            $count_args = [
                'post_type'      => $post_type,
                'post_status'    => $tab,
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => $by_form,
            ];

            if ( '' !== $search ) {
                $count_args['s'] = $search;
            }

            $count_query    = new \WP_Query( $count_args );
            $counts[ $tab ] = (int) $count_query->found_posts;
            $counts['any'] += $counts[ $tab ];
        }

        $statuses = get_post_statuses();
        $items    = [];

        // The columns WordPress's own posts list shows for the post type: the
        // taxonomies with an admin column, and Comments when it has comments.
        $taxonomies = array_filter(
            get_object_taxonomies( $post_type, 'objects' ),
            function ( $taxonomy ) {
                return ! empty( $taxonomy->show_admin_column );
            }
        );
        $columns    = [];

        foreach ( $taxonomies as $taxonomy ) {
            $columns[] = [
                'key'   => $taxonomy->name,
                'label' => $taxonomy->labels->name,
            ];
        }

        $comments = post_type_supports( $post_type, 'comments' );

        /**
         * Extra columns of a post form's Submissions page (after the taxonomy
         * columns), e.g. Pro's AI Review when the form has it on. Each column:
         * `key`, `label`. Fill each row's value with
         * `wpuf_form_submissions_item` (`$item['extra'][ $key ]` = [ 'text',
         * 'tone' (green|yellow|red|gray|blue|orange), 'title' ]).
         *
         * @since WPUF_SINCE
         *
         * @param array $extra_columns Columns.
         * @param int   $form_id       Form ID.
         * @param array $settings      Form settings.
         */
        $extra_columns = (array) apply_filters( 'wpuf_form_submissions_columns', [], $form_id, $settings );

        // One query for the page's authors; terms come from the cache WP_Query primed.
        cache_users( array_unique( array_map( 'intval', wp_list_pluck( $query->posts, 'post_author' ) ) ) );

        foreach ( $query->posts as $post ) {
            $author = get_userdata( $post->post_author );
            $terms  = [];

            foreach ( $taxonomies as $taxonomy ) {
                $list                     = get_the_terms( $post, $taxonomy->name );
                $terms[ $taxonomy->name ] = is_array( $list ) ? array_map( 'wp_strip_all_tags', wp_list_pluck( $list, 'name' ) ) : [];
            }

            if ( 'publish' === $post->post_status ) {
                $date_label = __( 'Published', 'wp-user-frontend' );
            } elseif ( 'future' === $post->post_status ) {
                $date_label = __( 'Scheduled', 'wp-user-frontend' );
            } else {
                $date_label = __( 'Last Modified', 'wp-user-frontend' );
            }

            $item = [
                'id'           => $post->ID,
                'title'        => wp_strip_all_tags( $post->post_title ),
                'status'       => $post->post_status,
                'status_label' => isset( $statuses[ $post->post_status ] ) ? $statuses[ $post->post_status ] : ucfirst( $post->post_status ),
                'author'       => $author ? $author->display_name : __( 'Guest', 'wp-user-frontend' ),
                'terms'        => $terms,
                'comments'     => $comments ? (int) $post->comment_count : null,
                'date_label'   => $date_label,
                'date'         => in_array( $post->post_status, [ 'publish', 'future' ], true )
                    ? get_the_date( '', $post )
                    : get_the_modified_date( '', $post ),
                'time'         => in_array( $post->post_status, [ 'publish', 'future' ], true )
                    ? get_the_time( '', $post )
                    : get_the_modified_time( '', $post ),
                'edit_url'     => current_user_can( 'edit_post', $post->ID ) ? get_edit_post_link( $post->ID, 'raw' ) : '',
                'view_url'     => 'publish' === $post->post_status ? get_permalink( $post ) : '',
                'extra'        => [],
            ];

            /**
             * One row of a post form's Submissions page.
             *
             * @since WPUF_SINCE
             *
             * @param array    $item    Row data (see `wpuf_form_submissions_columns` for `extra`).
             * @param \WP_Post $post    Submitted post.
             * @param int      $form_id Form ID.
             */
            $items[] = apply_filters( 'wpuf_form_submissions_item', $item, $post, $form_id );
        }

        return rest_ensure_response(
            [
                'success'    => true,
                'items'      => $items,
                'total'      => (int) $query->found_posts,
                'pages'      => (int) $query->max_num_pages,
                'page'       => $page,
                'per_page'   => $per_page,
                'counts'     => $counts,
                'columns'    => $columns,
                'extra_columns' => array_values( $extra_columns ),
                'comments'   => $comments,
                'post_type'  => $post_type,
                'form'       => [
                    'id'       => $form_id,
                    'title'    => wp_strip_all_tags( get_the_title( $form_id ) ),
                    'edit_url' => admin_url( 'admin.php?page=wpuf-post-forms&action=edit&id=' . $form_id ),
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
        $per_page    = ! empty( $request['per_page'] ) ? min( 100, max( 1, absint( $request['per_page'] ) ) ) : 10;
        $page        = ! empty( $request['page'] ) ? max( 1, absint( $request['page'] ) ) : 1;
        $status      = ! empty( $request['status'] ) ? sanitize_key( $request['status'] ) : 'any';
        $search_term = ! empty( $request['s'] ) ? sanitize_text_field( $request['s'] ) : '';
        $post_type   = ! empty( $request['post_type'] ) ? sanitize_key( $request['post_type'] ) : 'wpuf_forms';
        $offset      = ( $page - 1 ) * $per_page;

        /**
         * Post types the forms list endpoint may list.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $post_types Form post types.
         */
        $allowed_post_types = (array) apply_filters( 'wpuf_forms_list_post_types', [ 'wpuf_forms', 'wpuf_profile' ] );

        if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
            return new WP_Error( 'wpuf_invalid_post_type', __( 'Invalid form type.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        if ( ! in_array( $status, [ 'any', 'publish', 'draft', 'pending', 'private', 'future', 'trash' ], true ) ) {
            $status = 'any';
        }

        // Newest form first unless the list sorts on a column.
        $query_args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            'posts_per_page' => $per_page,
            'offset'         => $offset,
        ] + $this->query_order( $request, self::FORM_ORDERBY, 'id' );

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

        $forms    = [];
        $refs     = $this->settings_references();
        $wp_roles = wp_roles();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $post    = get_post();
                $post_id = $post->ID;

                // Get form settings
                $settings = Stores::forms()->read_settings( $post_id );

                // Get post count for this form
                $post_count    = $this->get_form_post_count( $post_id, $settings );
                $pending_count = $post_count ? $this->get_form_post_count( $post_id, $settings, 'pending' ) : 0;

                $forms[] = [
                    'ID'                  => $post_id,
                    'post_title'          => get_the_title(),
                    'form_status'         => ! empty( $post->post_status ) ? $post->post_status : '',
                    'post_status'         => ! empty( $settings['post_status'] ) ? $settings['post_status'] : '',
                    'settings_post_type'  => ! empty( $settings['post_type'] ) ? $settings['post_type'] : '',
                    'settings_guest_post' => ! empty( $settings['post_permission'] ) && 'guest_post' === $settings['post_permission'],
                    'settings_user_role'  => ! empty( $settings['role'] ) ? $settings['role'] : '',
                    'post_count'          => $post_count,
                    'pending_count'       => $pending_count,
                    // Read-only extras for the list's columns and badges (WPUF_SINCE).
                    'post_type_label'     => $this->post_type_label( $settings ),
                    'payment'             => $this->payment_summary( $settings ),
                    'settings_roles'      => isset( $refs[ $post_id ] ) ? $refs[ $post_id ] : [],
                    'approval_required'   => isset( $settings['wpuf_user_status'] ) && 'pending' === $settings['wpuf_user_status'],
                    'user_role_label'     => ! empty( $settings['role'] ) && isset( $wp_roles->role_names[ $settings['role'] ] ) ? translate_user_role( $wp_roles->role_names[ $settings['role'] ] ) : '',
                    'user_role_exists'    => empty( $settings['role'] ) || $wp_roles->is_role( $settings['role'] ),
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
                // The status tab counts, so the list can update them after an action.
                'counts'     => wpuf_get_forms_counts_with_status( $post_type ),
                // What the list should warn about (registration off, users to approve).
                'notices'    => $this->list_notices( $post_type ),
            ]
        );
    }

    /**
     * The label of a form's post type, null when the type is not registered
     * (its plugin was turned off: the form makes nothing).
     *
     * @param array $settings Form settings
     *
     * @return string|null
     */
    private function post_type_label( $settings ) {
        $type   = ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'post';
        $object = get_post_type_object( $type );

        return $object ? (string) $object->labels->name : null;
    }

    /**
     * How a post form charges: '' (free), 'pack' (subscription required) or
     * 'ppp' (pay per post, with its cost).
     *
     * @param array $settings Form settings
     *
     * @return array { mode, cost }
     */
    private function payment_summary( $settings ) {
        $on = isset( $settings['payment_options'] ) && wpuf_is_checkbox_or_toggle_on( $settings['payment_options'] );

        if ( ! $on ) {
            return [
                'mode' => '',
                'cost' => '',
            ];
        }

        $pack = isset( $settings['choose_payment_option'] ) && 'force_pack_purchase' === $settings['choose_payment_option'];

        return [
            'mode' => $pack ? 'pack' : 'ppp',
            'cost' => $pack ? '' : wp_strip_all_tags( html_entity_decode( wpuf_format_price( isset( $settings['pay_per_post_cost'] ) ? $settings['pay_per_post_cost'] : 0 ), ENT_QUOTES ) ),
        ];
    }

    /**
     * The forms that settings point at: form id => [ { key, label, section } ].
     *
     * @return array
     */
    private function settings_references() {
        $refs  = [];
        $add   = function ( $form_id, $key, $label, $section ) use ( &$refs ) {
            $form_id = absint( $form_id );

            if ( $form_id ) {
                $refs[ $form_id ][] = [
                    'key'     => $key,
                    'label'   => $label,
                    'section' => $section,
                ];
            }
        };
        $store = Stores::settings();

        $add( $store->get( 'wpuf_frontend_posting', 'default_post_form', 0 ), 'default_post_form', __( 'Default post form', 'wp-user-frontend' ), 'wpuf_frontend_posting' );
        $add( $store->get( 'wpuf_my_account', 'post_submission_form', 0 ), 'post_submission_form', __( 'Account page form', 'wp-user-frontend' ), 'wpuf_my_account' );
        $add( $store->get( 'wpuf_my_account', 'edit_profile_form', 0 ), 'edit_profile_form', __( 'Edit profile form', 'wp-user-frontend' ), 'wpuf_my_account' );

        $profile = $store->read( 'wpuf_profile' );
        $roles   = is_array( $profile ) && isset( $profile['roles'] ) && is_array( $profile['roles'] ) ? $profile['roles'] : [];
        $names   = wp_roles()->role_names;
        $by_form = [];

        foreach ( $roles as $role => $form_id ) {
            if ( absint( $form_id ) ) {
                $by_form[ absint( $form_id ) ][] = isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : $role;
            }
        }

        foreach ( $by_form as $form_id => $role_labels ) {
            /* translators: %s: comma separated role names */
            $add( $form_id, 'profile_form_roles', sprintf( __( 'Profile form for: %s', 'wp-user-frontend' ), implode( ', ', $role_labels ) ), 'wpuf_profile' );
        }

        return $refs;
    }

    /**
     * Warnings for the forms list: registration closed in WordPress and users
     * waiting for approval (registration forms only).
     *
     * @param string $post_type Form post type
     *
     * @return array
     */
    private function list_notices( $post_type ) {
        if ( 'wpuf_profile' !== $post_type ) {
            return (object) [];
        }

        $open = is_multisite()
            ? in_array( get_site_option( 'registration' ), [ 'user', 'all' ], true )
            : (bool) get_option( 'users_can_register' );

        $pending = 0;

        if ( current_user_can( 'list_users' ) ) {
            $query   = new \WP_User_Query(
                [
                    'meta_key'    => 'wpuf_user_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one count for the list notice.
                    'meta_value'  => 'pending', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- one count for the list notice.
                    'fields'      => 'ID',
                    'number'      => 1,
                    'count_total' => true,
                ]
            );
            $pending = (int) $query->get_total();
        }

        return [
            'registration_open'  => $open,
            'registration_url'   => is_multisite() ? network_admin_url( 'settings.php#registration' ) : admin_url( 'options-general.php#users_can_register' ),
            'can_open'           => is_multisite() ? current_user_can( 'manage_network_options' ) : current_user_can( 'manage_options' ),
            'pending_users'      => $pending,
            'pending_users_url'  => admin_url( 'users.php?wpuf_user_approve_filter-top=pending' ),
        ];
    }

    /**
     * Get post count for a form
     *
     * @since 4.1.4
     *
     * @since WPUF_SINCE Added the `$status` parameter.
     *
     * @param int    $form_id  Form ID
     * @param array  $settings Form settings
     * @param string $status   Post status to count (default any).
     *
     * @return int
     */
    private function get_form_post_count( $form_id, $settings, $status = 'any' ) {
        $post_type = ! empty( $settings['post_type'] ) ? $settings['post_type'] : 'post';

        $args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            // Only found_posts is read: fetch one id, not every post.
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => [
                [
                    'key'     => '_wpuf_form_id',
                    'value'   => $form_id,
                    'compare' => '=',
                ],
            ],
        ];

        $query = new \WP_Query( $args );
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
