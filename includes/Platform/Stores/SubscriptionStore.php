<?php
/**
 * Subscription pack store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use Exception;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Platform\Classic\Hook_Fields;
use WeDevs\Wpuf\Platform\Contracts\DataStore;
use WeDevs\Wpuf\Platform\Models\SubscriptionPack;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Writes subscription packs (post + meta) for the React screen (REST) and the
 * classic pack editor. Each path keeps the values and hook order it had, so
 * stored bytes do not change; the writes now live in one place.
 *
 * @since WPUF_SINCE
 */
class SubscriptionStore implements DataStore {

    use QueriesPosts;

    /**
     * Subscription post type
     */
    const POST_TYPE = 'wpuf_subscription';

    /**
     * Whether an id belongs to a subscription pack.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Post id
     *
     * @return bool
     */
    public function is_subscription( $id ) {
        return self::POST_TYPE === get_post_type( absint( $id ) );
    }

    /**
     * Whether the id is a subscription pack (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Id
     *
     * @return bool
     */
    public function exists( $id ) {
        return $this->is_subscription( $id );
    }

    /**
     * A pack with its meta as `Admin\Subscription::get_subscription_meta()`
     * returns it (DataStore). Read only.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Pack id
     *
     * @return array|null `post`, `meta`
     */
    public function read( $id ) {
        if ( ! $this->is_subscription( $id ) ) {
            return null;
        }

        $post = get_post( absint( $id ) );

        return [
            'post' => $post,
            'meta' => Subscription::get_subscription_meta( $post->ID, $post ),
        ];
    }

    /**
     * A pack as a model (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Pack id
     *
     * @return SubscriptionPack|null
     */
    public function find( $id ) {
        $read = $this->read( $id );

        return null === $read ? null : SubscriptionPack::from_read( $read );
    }

    /**
     * Packs as models (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param array $args Query args
     *
     * @return SubscriptionPack[]
     */
    public function query( array $args = [] ) {
        return array_values( array_filter( array_map( [ $this, 'find' ], $this->query_ids( self::POST_TYPE, $args ) ) ) );
    }

    /**
     * Number of packs matching the args (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param array $args Query args
     *
     * @return int
     */
    public function count( array $args = [] ) {
        return $this->count_posts( self::POST_TYPE, $args );
    }

    /**
     * Keep only term ids from a submitted list, leaving each id's type as sent
     * so stored values keep the shape the screen has always written.
     *
     * @since WPUF_SINCE
     *
     * @param mixed $ids Submitted ids
     *
     * @return array
     */
    public function sanitize_term_ids( $ids ) {
        if ( ! is_array( $ids ) ) {
            return [];
        }

        return array_values(
            array_filter(
                $ids,
                function ( $id ) {
                    return ( is_int( $id ) || is_string( $id ) ) && (string) absint( $id ) === (string) $id && absint( $id ) > 0;
                }
            )
        );
    }

    /**
     * Create or update a pack from the React screen's `subscription` payload.
     * The caller has checked the id (an existing pack) and the name (no `#`).
     *
     * @since WPUF_SINCE
     *
     * @param array           $subscription Payload (`ID`, post fields, `meta_value`)
     * @param WP_REST_Request $request      Request, handed to the hooks (term ids
     *                                      in it are sanitized for listeners)
     *
     * @return int|WP_Error Pack id; `wpuf_subscription_not_saved` when the post
     *                      could not be written, `wpuf_subscription_error` when a
     *                      listener threw
     */
    public function save_from_rest( $subscription, WP_REST_Request $request ) {
        $id     = ! empty( $subscription['ID'] ) ? (int) $subscription['ID'] : 0;
        $is_new = empty( $id );
        $name   = ! empty( $subscription['post_title'] ) ? sanitize_text_field( $subscription['post_title'] ) : '';
        $values = $this->rest_values( $subscription );
        $fields = wpuf()->platform()->get( Hook_Fields::class );
        $extra  = $fields->clean( $request->get_param( 'extra' ) );

        // Fields other plugins printed into the classic pack form (legacy_fields())
        // read their values from $_POST while the pack saves, as before.
        return $fields->with_post_data(
            $extra,
            function () use ( $subscription, $request, $id, $is_new, $name, $values, $extra ) {
                return $this->write_from_rest( $subscription, $request, $id, $is_new, $name, $values, $extra );
            }
        );
    }

    /**
     * The writes of save_from_rest().
     *
     * @param array           $subscription Payload
     * @param WP_REST_Request $request      Request
     * @param int             $id           Pack id (0: new)
     * @param bool            $is_new       New pack
     * @param string          $name         Pack name
     * @param array           $values       rest_values()
     * @param array           $extra        The classic form fields' values
     *
     * @return int|WP_Error
     */
    private function write_from_rest( $subscription, WP_REST_Request $request, $id, $is_new, $name, array $values, array $extra ) {
        try {
            $current_time = wpuf_current_datetime();

            $post_arr = [
                'post_type'         => self::POST_TYPE,
                'post_date'         => $values['date'],
                'post_date_gmt'     => get_gmt_from_date( $values['date'] ),
                'post_content'      => $values['post_content'],
                'post_title'        => $name,
                'post_status'       => $values['status'],
                'post_modified'     => $current_time->format( 'Y-m-d H:i:s' ),
                'post_modified_gmt' => get_gmt_from_date( $current_time->format( 'Y-m-d H:i:s' ) ),
            ];

            if ( ! empty( $id ) ) {
                $post_arr['ID'] = $id;
            }

            $id = wp_insert_post( $post_arr );

            if ( empty( $id ) || is_wp_error( $id ) ) {
                return new WP_Error( 'wpuf_subscription_not_saved', __( 'Failed to insert post', 'wp-user-frontend' ) );
            }

            // Fired once, with the saved pack id (a new pack has no id before
            // the insert), before the pack meta is written.
            do_action( 'wpuf_before_update_subscription_pack', $id, $request, $post_arr );

            // Listeners (pro taxonomy restriction) read the term ids from the
            // request, so hand them the filtered lists.
            $request_subscription = $request->get_param( 'subscription' );

            foreach ( [ '_sub_allowed_term_ids', '_sub_view_allowed_term_ids' ] as $term_key ) {
                if ( isset( $request_subscription['meta_value'][ $term_key ] ) ) {
                    $request_subscription['meta_value'][ $term_key ] = $this->sanitize_term_ids( $request_subscription['meta_value'][ $term_key ] );
                }
            }

            // The pack GET returns an empty posting restriction for every pack; on an
            // existing pack an empty list that was never stored stays absent on save
            // (untouched save = no change). A new pack stores the empty list like
            // develop's REST save did (G2, task 4.1b).
            if (
                ! $is_new
                && isset( $request_subscription['meta_value'] )
                && array_key_exists( '_sub_allowed_term_ids', (array) $request_subscription['meta_value'] )
                && empty( $request_subscription['meta_value']['_sub_allowed_term_ids'] )
                && ! metadata_exists( 'post', $id, '_sub_allowed_term_ids' )
            ) {
                unset( $request_subscription['meta_value']['_sub_allowed_term_ids'] );
            }

            $request->set_param( 'subscription', $request_subscription );

            do_action( 'wpuf_before_update_subscription_pack_meta', $id, $request );

            foreach ( $values['meta'] as $meta_key => $meta_value ) {
                update_post_meta( $id, $meta_key, $meta_value );
            }

            do_action( 'wpuf_after_update_subscription_pack_meta', $id, $request );

            // The classic pack screen fired this after saving; listeners (pro
            // postnum rollback) read the classic field names.
            $pack_data = [];
            foreach ( (array) ( isset( $subscription['meta_value'] ) ? $subscription['meta_value'] : [] ) as $meta_key => $meta_value ) {
                $pack_data[ ltrim( $meta_key, '_' ) ] = $meta_value;
            }
            $pack_data['post_title']                 = $name;
            $pack_data['postnum_rollback_on_delete'] = $values['meta']['postnum_rollback_on_delete'];
            // The classic save handed the whole posted form, the other plugins' fields too.
            $pack_data = array_merge( $extra, $pack_data );

            do_action( 'wpuf_update_subscription_pack', $id, $pack_data );

            // A pack with view restrictions turns the global restriction on.
            if ( ! empty( $values['meta']['_sub_view_allowed_term_ids'] ) ) {
                update_option( 'wpuf_taxonomy_view_restrictions_enabled', 'yes' );
            }

            return $id;
        } catch ( Exception $e ) {
            return new WP_Error( 'wpuf_subscription_error', $e->getMessage() );
        }
    }

    /**
     * What other plugins print into the classic pack form: the
     * `wpuf_admin_subscription_detail` / `wpuf_admin_subscription_post_restriction`
     * rows and the `wpuf_admin_subs_nav_tab` / `wpuf_admin_subs_nav_content`
     * tabs, fired with the classic arguments (global `$post` set to the pack).
     * The app shows them under "More options" and sends their inputs back on
     * save (`extra`), so those fields keep working.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Pack id (0: new)
     *
     * @return array { rows: table rows, html: other markup } (sanitized)
     */
    public function legacy_fields( $id ) {
        $id     = absint( $id );
        $pack   = $id ? get_post( $id ) : null;
        $post   = $pack instanceof WP_Post ? $pack : new WP_Post(
            (object) [
                'ID'           => 0,
                'post_type'    => self::POST_TYPE,
                'post_content' => '',
                'post_title'   => '',
            ]
        );
        $meta   = Subscription::get_subscription_meta( $id, $post );
        $admin  = is_object( wpuf()->admin ) ? wpuf()->admin->admin_subscription : null;
        $fields = wpuf()->platform()->get( Hook_Fields::class );

        $hidden_recurring_class = ! wpuf_is_option_on( isset( $meta['_recurring_pay'] ) ? $meta['_recurring_pay'] : '' ) ? 'none' : '';
        $hidden_trial_class     = ! wpuf_is_option_on( isset( $meta['_trial_status'] ) ? $meta['_trial_status'] : '' ) ? 'none' : '';

        return [
            'rows' => $fields->render(
                $post,
                function () use ( $meta, $hidden_recurring_class, $hidden_trial_class, $admin, $post ) {
                    /** Classic pack form: rows of the payment settings. */
                    do_action( 'wpuf_admin_subscription_detail', $meta, $hidden_recurring_class, $hidden_trial_class, $admin );

                    /** Classic pack form: rows of the posting restriction (since 2.7.0). */
                    do_action( 'wpuf_admin_subscription_post_restriction', $meta, $post, $admin );
                }
            ),
            'html' => $fields->render(
                $post,
                function () use ( $post ) {
                    /** Classic pack form: extra tab links. */
                    do_action( 'wpuf_admin_subs_nav_tab', $post );

                    /** Classic pack form: extra tab panes. */
                    do_action( 'wpuf_admin_subs_nav_content', $post );
                }
            ),
        ];
    }

    /**
     * Change one post field of a pack from the list screens (status toggle).
     * Only fields and values allowed by `wpuf_subscription_single_row_fields`.
     *
     * @since WPUF_SINCE
     *
     * @param int             $id      Pack id (an existing pack)
     * @param string          $row     Post field
     * @param string          $value   New value
     * @param WP_REST_Request $request Request, handed to the hooks
     *
     * @return true|WP_Error `wpuf_subscription_field_not_editable` (status 400)
     *                       or `wpuf_subscription_not_updated`
     */
    public function update_single_row( $id, $row, $value, WP_REST_Request $request ) {
        /**
         * Post fields a single-row subscription edit may change, with the
         * values each accepts. The list screens only toggle the status.
         *
         * @since WPUF_SINCE
         *
         * @param array $fields Field name => allowed values.
         */
        $editable = (array) apply_filters(
            'wpuf_subscription_single_row_fields',
            [
                'post_status' => [ 'publish', 'draft', 'pending', 'private', 'trash' ],
            ]
        );

        if ( ! isset( $editable[ $row ] ) || ! in_array( $value, (array) $editable[ $row ], true ) ) {
            return new WP_Error( 'wpuf_subscription_field_not_editable', __( 'Failed to update', 'wp-user-frontend' ), [ 'status' => 400 ] );
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
            return new WP_Error( 'wpuf_subscription_not_updated', __( 'Failed to update subscription', 'wp-user-frontend' ) );
        }

        return true;
    }

    /**
     * Packs as `Admin\Subscription::get_subscriptions()` lists them: posts
     * ordered by `_sort_order` with their meta in `meta_value`; a pack
     * without a sort order gets 1 stored on the way.
     *
     * @since WPUF_SINCE
     *
     * @param array $args get_posts() arguments on top of the defaults
     *
     * @return \WP_Post[]
     */
    public function ordered( array $args = [] ) {
        $posts = get_posts(
            wp_parse_args(
                $args,
                [
                    'post_type'      => 'wpuf_subscription',
                    'posts_per_page' => -1,
                    'post_status'    => 'publish',
                    'meta_key'       => '_sort_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- the packs' order, as before.
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                ]
            )
        );

        foreach ( $posts as $post ) {
            $post->meta_value = Subscription::get_subscription_meta( $post->ID, $posts );

            if ( empty( $post->meta_value['_sort_order'] ) ) {
                update_post_meta( $post->ID, '_sort_order', 1 );
                $post->meta_value['_sort_order'] = 1;
            }
        }

        return $posts;
    }

    /**
     * Packs per post status (auto-drafts left out) plus `all` (trash left out).
     *
     * @since WPUF_SINCE
     *
     * @return array status => count
     */
    public function counts_by_status() {
        global $wpdb;

        $rows   = $wpdb->get_results( $wpdb->prepare( "SELECT post_status, COUNT(*) AS count FROM {$wpdb->posts} WHERE post_type = %s AND post_status != %s GROUP BY post_status", 'wpuf_subscription', 'auto-draft' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $status = [];
        $total  = 0;

        foreach ( (array) $rows as $row ) {
            $status[ $row->post_status ] = (int) $row->count;
            $total                      += (int) $row->count;
        }

        $status['all'] = empty( $status['trash'] ) ? $total : $total - $status['trash'];

        return $status;
    }

    /**
     * Packs of one status, or every pack ('all'), as the pack class counted them.
     *
     * @since WPUF_SINCE
     *
     * @param string $status Post status or 'all'
     *
     * @return int
     */
    public function count_status( $status = 'all' ) {
        global $wpdb;

        if ( 'all' === $status ) {
            return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpuf_subscription'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        }

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpuf_subscription' AND post_status = %s", $status ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Delete a pack for good.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Pack id
     *
     * @return bool Whether it was deleted (false for a post that is not a pack)
     */
    public function delete( $id ) {
        if ( ! $this->is_subscription( $id ) ) {
            return false;
        }

        return (bool) wp_delete_post( $id, true );
    }

    /**
     * Delete every pack for good, any status, trash included (Tools > Delete Forms).
     *
     * @since WPUF_SINCE
     *
     * @return int Packs deleted
     */
    public function delete_all() {
        $deleted = 0;

        foreach ( $this->query_ids( 'wpuf_subscription', [ 'status' => [ 'publish', 'draft', 'pending', 'trash' ] ] ) as $id ) {
            if ( wp_delete_post( $id, true ) ) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Give every pack without a usable `_sort_order` (missing, empty or <= 0)
     * the default order 1 (the one-off migration Admin_Subscription runs).
     *
     * @since WPUF_SINCE
     *
     * @return int Packs updated
     */
    public function set_default_sort_order() {
        $ids = get_posts(
            [
                'post_type'      => 'wpuf_subscription',
                'posts_per_page' => -1,
                'post_status'    => [ 'publish', 'draft', 'private' ],
                'fields'         => 'ids',
                'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- one-off migration.
                    'relation' => 'OR',
                    [
                        'key'     => '_sort_order',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => '_sort_order',
                        'value'   => '',
                        'compare' => '=',
                    ],
                    [
                        'key'     => '_sort_order',
                        'value'   => 0,
                        'compare' => '<=',
                    ],
                ],
            ]
        );

        foreach ( $ids as $id ) {
            update_post_meta( $id, '_sort_order', 1 );
        }

        return count( $ids );
    }

    /**
     * Post fields and meta (in write order) the REST save stores.
     *
     * @param array $subscription Payload
     *
     * @return array { date, post_content, status, meta }
     */
    private function rest_values( $subscription ) {
        $meta_value = isset( $subscription['meta_value'] ) && is_array( $subscription['meta_value'] ) ? $subscription['meta_value'] : [];
        $text       = function ( $key, $fallback ) use ( $meta_value ) {
            return ! empty( $meta_value[ $key ] ) ? sanitize_text_field( $meta_value[ $key ] ) : $fallback;
        };
        $number     = function ( $key, $fallback ) use ( $meta_value ) {
            return ! empty( $meta_value[ $key ] ) ? (int) $meta_value[ $key ] : $fallback;
        };
        $texts      = function ( $key ) use ( $meta_value ) {
            return ! empty( $meta_value[ $key ] ) ? array_map( 'sanitize_text_field', $meta_value[ $key ] ) : '';
        };

        $recurring_pay          = $text( '_recurring_pay', 'no' );
        $cycle_period           = $text( '_cycle_period', '' );
        $post_expiration_number = $number( '_post_expiration_number', '' );
        $post_expiration_period = $text( '_post_expiration_period', '' );
        $sort_order             = $number( '_sort_order', 1 );

        // Packs saved before 4.0.11 only have the combined "30 day" value (the
        // 4.0.8 upgrade never split it): keep it instead of writing '' when the
        // two parts are not sent.
        if ( ! $post_expiration_number || ! $post_expiration_period ) {
            $stored = ! empty( $meta_value['_post_expiration_time'] ) ? $meta_value['_post_expiration_time'] : '';

            if ( ! $stored && ! empty( $subscription['ID'] ) ) {
                $stored = get_post_meta( (int) $subscription['ID'], '_post_expiration_time', true );
            }

            if ( is_string( $stored ) && preg_match( '/^\s*(\d+)\s+([a-z]+?)s?\s*$/i', $stored, $parts ) ) {
                $post_expiration_number = $post_expiration_number ? $post_expiration_number : (int) $parts[1];
                $post_expiration_period = $post_expiration_period ? $post_expiration_period : sanitize_text_field( strtolower( $parts[2] ) );
            }
        }

        if ( $sort_order < 1 ) {
            $sort_order = 1;
        }

        if ( 'no' !== $recurring_pay && empty( $cycle_period ) ) {
            $cycle_period = 'day';
        }

        return [
            'status'       => ! empty( $subscription['post_status'] ) ? sanitize_text_field( $subscription['post_status'] ) : 'publish',
            'date'         => ! empty( $subscription['post_date'] ) ? sanitize_text_field( $subscription['post_date'] ) : '',
            'post_content' => ! empty( $subscription['post_content'] ) ? sanitize_textarea_field( $subscription['post_content'] ) : '',
            'meta'         => [
                '_billing_amount'            => ! empty( $meta_value['_billing_amount'] ) ? floatval( $meta_value['_billing_amount'] ) : 0,
                '_expiration_number'         => $number( '_expiration_number', 0 ),
                '_expiration_period'         => $text( '_expiration_period', 'day' ),
                '_recurring_pay'             => $recurring_pay,
                '_billing_cycle_number'      => $number( '_billing_cycle_number', 0 ),
                '_cycle_period'              => $cycle_period,
                '_enable_billing_limit'      => $text( '_enable_billing_limit', '' ),
                '_billing_limit'             => $text( '_billing_limit', '' ),
                '_trial_status'              => $text( '_trial_status', 'no' ),
                '_trial_duration'            => $number( '_trial_duration', 0 ),
                '_trial_duration_type'       => $text( '_trial_duration_type', 0 ),
                '_post_type_name'            => $texts( '_post_type_name' ),
                'additional_cpt_options'     => $texts( 'additional_cpt_options' ),
                '_enable_post_expiration'    => $text( '_enable_post_expiration', 'no' ),
                '_post_expiration_number'    => $post_expiration_number,
                '_post_expiration_period'    => $post_expiration_period,
                // Readers (User_Subscription, the pack details) use the strtotime()
                // duration the classic editor stored, e.g. "7 day". The screen's
                // "no expiry" values (-1, unit "forever") store '' like the classic
                // editor does, not "-1 day" (expired at once if expiration is enabled).
                '_post_expiration_time'      => ( $post_expiration_number > 0 && $post_expiration_period && 'forever' !== $post_expiration_period ) ? $post_expiration_number . ' ' . $post_expiration_period : '',
                '_expired_post_status'       => $text( '_expired_post_status', 'draft' ),
                '_enable_mail_after_expired' => $text( '_enable_mail_after_expired', 'no' ),
                '_post_expiration_message'   => ! empty( $meta_value['_post_expiration_message'] ) ? wp_kses_post( $meta_value['_post_expiration_message'] ) : '',
                '_total_feature_item'        => $number( '_total_feature_item', 0 ),
                '_remove_feature_item'       => $text( '_remove_feature_item', '' ),
                '_sort_order'                => $sort_order,
                '_sub_view_allowed_term_ids' => ! empty( $meta_value['_sub_view_allowed_term_ids'] ) ? $this->sanitize_term_ids( $meta_value['_sub_view_allowed_term_ids'] ) : [],
                'postnum_rollback_on_delete' => $text( 'postnum_rollback_on_delete', '' ),
            ],
        ];
    }
}
