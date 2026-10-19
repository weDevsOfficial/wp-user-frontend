<?php
/**
 * The `[wpuf_dashboard]` posts list: query, rows, columns and actions
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Dashboard;

use WeDevs\Wpuf\Frontend\Account\Account_Service;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;
use WP_Post;
use WP_Query;

/**
 * Everything the React dashboard shows, built the way the classic
 * `Frontend_Dashboard::post_listing()` and `templates/dashboard.php` build it:
 * the same shortcode attributes, the same `wpuf_dashboard_query` filter, the
 * same columns, the same settings (post count, edit / delete). The blocks
 * around the list (header, cards) come from Dashboard_Overview.
 *
 * The attributes come from the shortcode the site owner wrote and may widen
 * the query (`author__in`, any `WP_Query` key), so the REST routes only take
 * them back with the signature the page was rendered with (sign() / verify()).
 *
 * @since WPUF_SINCE
 */
class Dashboard_Service {

    /**
     * The shortcode's own attributes and their defaults.
     *
     * @since WPUF_SINCE
     */
    const DEFAULTS = [
        'form_id'        => 'off',
        'post_type'      => 'post',
        'category'       => 'off',
        'featured_image' => 'default',
        'meta'           => 'off',
        'excerpt'        => 'off',
        'payment_column' => 'on',
        'post_status'    => 'draft,future,pending,publish,private',
        'layout'         => 'full',
    ];

    /**
     * Print-to-page hooks of the classic dashboard. A site that uses any of
     * them keeps the classic list, where their output still shows.
     *
     * @since WPUF_SINCE
     */
    const CLASSIC_HOOKS = [
        'wpuf_dashboard_top',
        'wpuf_dashboard_bottom',
        'wpuf_dashboard_nopost',
        'wpuf_dashboard_head_col',
        'wpuf_dashboard_row_col',
        'wpuf_account_posts_head_col',
        'wpuf_account_posts_row_col',
        'wpuf_dashboard_post_taxonomy',
    ];

    /**
     * The shared posts row builder.
     *
     * @var Account_Service
     */
    private $account;

    /**
     * @since WPUF_SINCE
     *
     * @param Account_Service $account Account service
     */
    public function __construct( Account_Service $account ) {
        $this->account = $account;
    }

    /**
     * The attributes the classic shortcode works with: its own keys with
     * their defaults, plus any other key the owner wrote (passed to the query).
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Shortcode attributes
     *
     * @return array
     */
    public function attributes( $atts ) {
        $atts = is_array( $atts ) ? $atts : [];

        return array_merge( shortcode_atts( self::DEFAULTS, $atts ), $atts );
    }

    /**
     * Whether the shortcode asks for the posts card only (`layout="list"`),
     * as the Dokan, WC Vendors and WCMp dashboards do.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes (attributes())
     *
     * @return bool
     */
    public function list_only( array $atts ) {
        return isset( $atts['layout'] ) && 'list' === $atts['layout'];
    }

    /**
     * Whether a site hooks the classic list's printed output, so the classic
     * list must render.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function classic_only() {
        foreach ( self::CLASSIC_HOOKS as $hook ) {
            if ( has_filter( $hook ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * The attributes as the JSON string the page sends back, and its
     * signature for the current user.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes (attributes())
     *
     * @return array [ 'atts' => string, 'sig' => string ]
     */
    public function sign( array $atts ) {
        $json = (string) wp_json_encode( $atts );

        return [
            'atts' => $json,
            'sig'  => $this->signature( $json ),
        ];
    }

    /**
     * The attributes of a signed JSON string, or an error when the signature
     * does not match (changed attributes, another user).
     *
     * @since WPUF_SINCE
     *
     * @param string $json Attributes as rendered
     * @param string $sig  Signature as rendered
     *
     * @return array|WP_Error
     */
    public function verify( $json, $sig ) {
        $json = (string) $json;
        $atts = json_decode( $json, true );

        if ( ! is_array( $atts ) || ! hash_equals( $this->signature( $json ), (string) $sig ) ) {
            return new WP_Error( 'wpuf_dashboard_bad_request', __( 'This list is out of date. Reload the page and try again.', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        return $this->attributes( $atts );
    }

    /**
     * One page of the dashboard: title, columns, rows, paging, the filters in use.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts    Attributes (attributes())
     * @param int   $page    Page number
     * @param array $filters The member's filters: status, search, type (filters())
     *
     * @return array
     */
    public function page( array $atts, $page = 1, array $filters = [] ) {
        $page       = max( 1, (int) $page );
        $types      = $this->post_types( $atts );
        $filters    = $this->filters( $atts, $filters );
        $query      = new WP_Query( $this->query_args( $atts, $page, $filters ) );
        $columns    = $this->columns( $atts );
        $user       = wp_get_current_user();
        $labels     = [];
        $type_names = [];

        foreach ( $types as $type ) {
            $object = get_post_type_object( $type );

            if ( $object ) {
                $labels[]     = (string) $object->label;
                $type_names[] = $type;
            }
        }

        $items = [];

        foreach ( $query->posts as $post ) {
            if ( $post instanceof WP_Post ) {
                $items[] = $this->row( $post, $atts, $columns );
            }
        }

        return [
            'title'      => sprintf(
                // translators: %s is displayname
                __( "%s's Dashboard", 'wp-user-frontend' ),
                $user->display_name
            ),
            'post_types' => $type_names,
            'labels'     => $labels,
            'show_count' => 'on' === Stores::settings()->get( 'wpuf_dashboard', 'show_post_count', 'on' ),
            'columns'    => $columns,
            'items'      => $items,
            'total'      => (int) $query->found_posts,
            'pages'      => (int) $query->max_num_pages,
            'page'       => $page,
            'filters'    => $filters,
        ];
    }

    /**
     * Trash one of the user's posts from the list, under the rules of the
     * classic Delete link: deleting allowed in the settings, the author (or
     * someone who may delete others' pages), a post type the list shows.
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id Post ID
     * @param array $atts    Attributes (attributes())
     *
     * @return true|WP_Error
     */
    public function delete_post( $post_id, array $atts ) {
        $post = get_post( absint( $post_id ) );

        if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, $this->post_types( $atts ), true ) ) {
            return new WP_Error( 'wpuf_dashboard_post_not_found', __( 'Post not found.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        if ( 'yes' !== Stores::settings()->get( 'wpuf_dashboard', 'enable_post_del', 'yes' ) ) {
            return new WP_Error( 'wpuf_dashboard_delete_disabled', __( 'Deleting posts is turned off.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        if ( (int) $post->post_author !== get_current_user_id() && ! current_user_can( 'delete_others_pages' ) ) {
            return new WP_Error( 'wpuf_dashboard_forbidden', __( 'You are not the post author. Cheating huh!', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        if ( ! wp_trash_post( $post->ID ) ) {
            return new WP_Error( 'wpuf_dashboard_delete_failed', __( 'The post could not be deleted.', 'wp-user-frontend' ), [ 'status' => 500 ] );
        }

        return true;
    }

    /**
     * The member's filters, kept only where they narrow the owner's list: a
     * status the list shows, a post type of the list, a search string.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts    Attributes
     * @param array $filters Raw filters (status, search, type)
     *
     * @return array status, search, type ('' when not set)
     */
    public function filters( array $atts, array $filters ) {
        $status = isset( $filters['status'] ) ? sanitize_key( (string) $filters['status'] ) : '';
        $type   = isset( $filters['type'] ) ? sanitize_key( (string) $filters['type'] ) : '';
        $search = isset( $filters['search'] ) ? sanitize_text_field( (string) $filters['search'] ) : '';

        return [
            'status' => in_array( $status, $this->statuses( $atts ), true ) ? $status : '',
            'type'   => in_array( $type, $this->post_types( $atts ), true ) ? $type : '',
            'search' => function_exists( 'mb_substr' ) ? mb_substr( $search, 0, 100 ) : substr( $search, 0, 100 ),
        ];
    }

    /**
     * The statuses the list shows (`post_status="publish,pending"`).
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return string[]
     */
    public function statuses( array $atts ) {
        $status = $atts['post_status'] ? $atts['post_status'] : self::DEFAULTS['post_status'];
        $status = is_array( $status ) ? $status : explode( ',', (string) $status );

        return array_values( array_filter( array_map( 'trim', $status ) ) );
    }

    /**
     * The post types of the list (`post_type="post,product"`).
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return string[]
     */
    public function post_types( array $atts ) {
        $types = is_array( $atts['post_type'] ) ? $atts['post_type'] : explode( ',', (string) $atts['post_type'] );

        return array_values( array_filter( array_map( 'sanitize_key', array_map( 'trim', $types ) ) ) );
    }

    /**
     * The `WP_Query` arguments, as `Frontend_Dashboard::post_listing()` builds them.
     *
     * @since WPUF_SINCE
     *
     * @param array $atts    Attributes
     * @param int   $page    Page number
     * @param array $filters Checked filters (filters()), optional
     *
     * @return array
     */
    public function query_args( array $atts, $page, array $filters = [] ) {
        $attributes = $atts;

        unset( $attributes['post_type'], $attributes['post_status'], $attributes['layout'] );

        $args = [
            'author'         => get_current_user_id(),
            'post_status'    => $this->statuses( $atts ),
            'post_type'      => $this->post_types( $atts ),
            'posts_per_page' => (int) Stores::settings()->get( 'wpuf_dashboard', 'per_page', 10 ),
            'paged'          => max( 1, (int) $page ),
        ];

        if ( isset( $attributes['form_id'] ) && 'off' !== $attributes['form_id'] ) {
            $args['meta_query'] = [
                [
                    'key'     => '_wpuf_form_id',
                    'value'   => $attributes['form_id'],
                    'compare' => 'IN',
                ],
            ];
        }

        if ( isset( $attributes['category__in'] ) ) {
            $taxonomy = [ 'category' ];

            if ( class_exists( 'WooCommerce' ) ) {
                $taxonomy[] = 'product_cat';
            }

            $attributes['category__in'] = get_terms(
                [
                    'name'     => explode( ',', $attributes['category__in'] ),
                    'taxonomy' => $taxonomy,
                    'fields'   => 'ids',
                ]
            );
        }

        if ( isset( $attributes['author__in'] ) ) {
            $attributes['author__in'] = get_users(
                [
                    'nicename__in' => explode( ',', $attributes['author__in'] ),
                    'fields'       => 'ids',
                ]
            );
            unset( $args['author'] );
        }

        $args = array_merge( $args, $attributes );

        if ( ! empty( $filters['status'] ) ) {
            $args['post_status'] = [ $filters['status'] ];
        }

        if ( ! empty( $filters['type'] ) ) {
            $args['post_type'] = [ $filters['type'] ];
        }

        if ( ! empty( $filters['search'] ) ) {
            $args['s'] = $filters['search'];
        }

        // The classic list's filter (Frontend_Dashboard::post_listing()), same arguments.
        return apply_filters( 'wpuf_dashboard_query', $args, $attributes );
    }

    /**
     * Which columns the list shows (the classic table's rules).
     *
     * @since WPUF_SINCE
     *
     * @param array $atts Attributes
     *
     * @return array
     */
    public function columns( array $atts ) {
        $thumbnail = Stores::settings()->get( 'wpuf_dashboard', 'show_ft_image' );

        // The shortcode's `featured_image` (on / off) wins over the setting; `default` keeps it.
        if ( in_array( $atts['featured_image'], [ 'on', 'off' ], true ) ) {
            $thumbnail = $atts['featured_image'];
        }

        return [
            'thumbnail' => 'on' === $thumbnail,
            'category'  => 'on' === $atts['category'],
            'excerpt'   => 'on' === $atts['excerpt'],
            'meta'      => $this->meta_columns( $atts['meta'] ),
            'payment'   => 'on' === Stores::settings()->get( 'wpuf_payment', 'enable_payment' ) && 'off' !== $atts['payment_column'],
        ];
    }

    /**
     * `meta="key1,key2"`: custom fields marked as meta in a published post
     * form, in the order asked for, labelled as in the form.
     *
     * @since WPUF_SINCE
     *
     * @param string $meta Comma separated meta keys
     *
     * @return array[] [ [ 'key' => ..., 'label' => ... ] ]
     */
    public function meta_columns( $meta ) {
        $keys = 'off' === $meta || '' === (string) $meta ? [] : array_filter( array_map( 'trim', explode( ',', (string) $meta ) ) );

        if ( ! $keys ) {
            return [];
        }

        $labels = [];
        $forms  = get_posts(
            [
                'post_type'      => 'wpuf_forms',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            ]
        );

        foreach ( $forms as $form_id ) {
            foreach ( (array) wpuf_get_form_fields( $form_id ) as $field ) {
                if ( isset( $field['is_meta'], $field['name'], $field['label'] ) && 'yes' === $field['is_meta'] && ! isset( $labels[ $field['name'] ] ) ) {
                    $labels[ $field['name'] ] = (string) $field['label'];
                }
            }
        }

        $columns = [];

        foreach ( $keys as $key ) {
            if ( isset( $labels[ $key ] ) ) {
                $columns[] = [
                    'key'   => $key,
                    'label' => $labels[ $key ],
                ];
            }
        }

        return $columns;
    }

    /**
     * One row: the account row plus the dashboard's own columns.
     *
     * @since WPUF_SINCE
     *
     * @param WP_Post $post    Post
     * @param array   $atts    Attributes
     * @param array   $columns Columns (columns())
     *
     * @return array
     */
    private function row( WP_Post $post, array $atts, array $columns ) {
        $row = $this->account->post_row( $post );

        // The list shows the chosen featured image column even where the
        // account setting is off, so the thumbnail is read here too.
        if ( $columns['thumbnail'] && '' === $row['thumbnail'] ) {
            $thumb            = get_the_post_thumbnail_url( $post, Stores::settings()->get( 'wpuf_dashboard', 'ft_img_size', 'thumbnail' ) );
            $row['thumbnail'] = $thumb ? (string) $thumb : '';
        }

        if ( $columns['category'] ) {
            $row['terms'] = [];

            foreach ( get_object_taxonomies( $post->post_type, 'objects' ) as $slug => $taxonomy ) {
                if ( ! $taxonomy->hierarchical ) {
                    continue;
                }

                foreach ( (array) get_the_terms( $post->ID, $slug ) as $term ) {
                    if ( $term instanceof \WP_Term ) {
                        $link          = get_term_link( $term );
                        $row['terms'][] = [
                            'name' => (string) $term->name,
                            'url'  => is_wp_error( $link ) ? '' : (string) $link,
                        ];
                    }
                }
            }
        }

        if ( $columns['meta'] ) {
            $row['meta'] = [];

            foreach ( $columns['meta'] as $column ) {
                $value = get_post_meta( $post->ID, $column['key'], true );

                $row['meta'][ $column['key'] ] = is_array( $value ) ? implode( ', ', array_filter( array_map( 'strval', $value ) ) ) : (string) $value;
            }
        }

        if ( $columns['excerpt'] ) {
            $row['excerpt'] = wp_strip_all_tags( get_the_excerpt( $post ) );
        }

        return $row;
    }

    /**
     * HMAC of the attributes JSON, bound to the current user.
     *
     * @param string $json Attributes JSON
     *
     * @return string
     */
    private function signature( $json ) {
        return hash_hmac( 'sha256', $json . '|' . get_current_user_id(), wp_salt( 'nonce' ) );
    }
}
