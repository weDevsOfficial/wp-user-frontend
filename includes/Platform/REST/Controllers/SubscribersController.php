<?php
/**
 * Subscribers REST controller
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Admin\Subscriptions\User_Profile;
use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Transactions\TransactionService;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/subscriptions/{id}/subscribers`: the subscribers of one pack
 * (status tab, sort, page) for the subscription managers. It replaces the
 * classic Subscribers list table (List_Table_Subscribers) with the same rows,
 * filters and counts, read through the subscribers store.
 *
 * @since WPUF_SINCE
 */
class SubscribersController extends RestController {

    /**
     * Status tabs of the classic list (stored values).
     */
    const STATUSES = [ 'all', 'Completed', 'Cancel' ];

    /**
     * Resource name in error codes.
     *
     * @var string
     */
    protected $resource = 'subscribers';

    /**
     * The transactions service (gateway marks, as on the Transactions list).
     *
     * @var TransactionService
     */
    private $transactions;

    /**
     * Constructor.
     *
     * @since WPUF_SINCE
     *
     * @param TransactionService|null $transactions The transactions service
     */
    public function __construct( $transactions = null ) {
        $this->transactions = $transactions ? $transactions : new TransactionService();
    }

    /**
     * Register the routes.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        $this->route(
            '/admin/subscriptions/(?P<id>\d+)/subscribers',
            WP_REST_Server::READABLE,
            'get_items',
            $this->permission( Caps::MANAGE_SUBSCRIPTIONS ),
            [
                'id'       => [
                    'type'     => 'integer',
                    'required' => true,
                ],
                'status'   => [
                    'type'    => 'string',
                    'enum'    => self::STATUSES,
                    'default' => 'all',
                ],
            ] + $this->collection_args( [ 'id', 'user_id', 'starts_from', 'expire' ], 'id', [ 'search' => '' ] )
        );
        $this->route(
            '/admin/users/(?P<user_id>\d+)/pack',
            WP_REST_Server::DELETABLE,
            'delete_user_pack',
            $this->permission( Caps::MANAGE_SUBSCRIPTIONS ),
            [
                'user_id' => [
                    'type'     => 'integer',
                    'required' => true,
                    'minimum'  => 1,
                ],
                'pack_id' => [
                    'type'    => 'integer',
                    'default' => 0,
                    'minimum' => 0,
                ],
            ]
        );
    }

    /**
     * The subscribers of a pack, with the per-status counts of the tabs.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items( $request ) {
        $pack_id = absint( $request['id'] );
        $pack    = get_post( $pack_id );

        if ( ! $pack || 'wpuf_subscription' !== $pack->post_type ) {
            return $this->error( 'not_found', __( 'Subscription pack not found.', 'wp-user-frontend' ), 404 );
        }

        $status   = (string) $request['status'];
        $per_page = (int) $request['per_page'];
        $page     = (int) $request['page'];
        $filter   = [ 'pack_id' => $pack_id ];

        if ( 'all' !== $status ) {
            $filter = $this->status_filter( $pack_id, $status );
        }

        $store = Stores::subscribers();
        $total = $store->count( $filter );
        $rows  = $store->query(
            array_merge(
                $filter,
                [
                    'orderby' => (string) $request['orderby'],
                    'order'   => (string) $request['order'],
                    'number'  => $per_page,
                    'offset'  => ( $page - 1 ) * $per_page,
                ]
            )
        );

        $items = array_map( [ $this, 'prepare_row' ], $rows );

        // Usage (used / remaining of the pack's limits) on each user's current
        // row of this pack: the newest row of a user whose pack is this one.
        $current = [];

        foreach ( array_unique( wp_list_pluck( $items, 'user_id' ) ) as $user_id ) {
            $user_pack = $user_id ? Stores::user_packs()->read( $user_id ) : null;

            if ( ! is_array( $user_pack ) || (int) ( isset( $user_pack['pack_id'] ) ? $user_pack['pack_id'] : 0 ) !== $pack_id ) {
                continue;
            }

            $latest = $store->query(
                [
                    'pack_id' => $pack_id,
                    'user_id' => $user_id,
                    'orderby' => 'id',
                    'order'   => 'DESC',
                    'number'  => 1,
                ]
            );

            if ( $latest ) {
                $current[ (int) $latest[0]->id ] = $this->usage( $pack_id, $user_pack, $user_id, (string) $latest[0]->starts_from );
            }
        }

        foreach ( $items as $index => $item ) {
            $items[ $index ]['usage'] = isset( $current[ $item['id'] ] ) ? $current[ $item['id'] ] : null;
        }

        return rest_ensure_response(
            [
                'pack'   => [
                    'id'    => $pack_id,
                    'title' => get_the_title( $pack ),
                ],
                'items'  => $items,
                'total'  => $total,
                'pages'  => (int) ceil( $total / max( 1, $per_page ) ),
                'counts' => [
                    'all'       => $store->count( [ 'pack_id' => $pack_id ] ),
                    'Completed' => $store->count( $this->status_filter( $pack_id, 'Completed' ) ),
                    'Cancel'    => $store->count( $this->status_filter( $pack_id, 'Cancel' ) ),
                ],
            ]
        );
    }

    /**
     * One row: the classic list's columns as plain strings, the dates in the
     * site's date format (as the Transactions list; "unlimited" and other
     * non-dates as stored), the gateway mark, plus the user's profile link
     * for the admins who may edit that user.
     *
     * @since WPUF_SINCE
     *
     * @param object $row Subscribers table row
     *
     * @return array
     */
    public function prepare_row( $row ) {
        $user_id = isset( $row->user_id ) ? (int) $row->user_id : 0;
        $gateway = isset( $row->gateway ) ? sanitize_text_field( (string) $row->gateway ) : '';

        return [
            'id'              => isset( $row->id ) ? (int) $row->id : 0,
            'user_id'         => $user_id,
            'name'            => isset( $row->name ) ? sanitize_text_field( (string) $row->name ) : '',
            'subscription_id' => isset( $row->subscribtion_id ) ? sanitize_text_field( (string) $row->subscribtion_id ) : '',
            'status'          => isset( $row->subscribtion_status ) ? sanitize_text_field( (string) $row->subscribtion_status ) : '',
            'gateway'         => $gateway,
            'gateway_logo'    => '' !== $gateway ? esc_url_raw( $this->transactions->gateway_logo( $gateway ) ) : '',
            'transaction_id'  => isset( $row->transaction_id ) ? sanitize_text_field( (string) $row->transaction_id ) : '',
            'starts_from'     => $this->format_date( isset( $row->starts_from ) ? (string) $row->starts_from : '' ),
            'expire'          => $this->format_date( isset( $row->expire ) ? (string) $row->expire : '' ),
            'edit_url'        => $user_id && current_user_can( 'edit_user', $user_id ) ? esc_url_raw( get_edit_user_link( $user_id ) ) : '',
            // The user's posts in wp-admin (row menu), for who may see others' posts.
            'posts_url'       => $user_id && current_user_can( 'edit_others_posts' ) ? esc_url_raw( admin_url( 'edit.php?author=' . $user_id ) ) : '',
        ];
    }

    /**
     * What a subscriber used of the pack and what is left: per post type the
     * pack's limit (`_post_type_name` + `additional_cpt_options`) against the
     * remaining count in the user's pack meta, plus featured items and the
     * expiry. -1 is unlimited: then "used" counts the user's posts of the type
     * since the subscription started (only public types; internal ones such
     * as templates show only when the pack limits them).
     *
     * @since WPUF_SINCE
     *
     * @param int   $pack_id   Pack ID
     * @param array  $user_pack The user's `_wpuf_subscription_pack` meta
     * @param int    $user_id   User ID
     * @param string $since     Subscription start (Y-m-d H:i:s)
     *
     * @return array { items: [ { type, label, limit, used, remaining } ], featured, expire }
     */
    private function usage( $pack_id, array $user_pack, $user_id, $since ) {
        $limits = array_merge(
            (array) maybe_unserialize( get_post_meta( $pack_id, '_post_type_name', true ) ),
            (array) maybe_unserialize( get_post_meta( $pack_id, 'additional_cpt_options', true ) )
        );
        $left   = isset( $user_pack['posts'] ) && is_array( $user_pack['posts'] ) ? $user_pack['posts'] : [];
        $items  = [];

        foreach ( $limits as $type => $limit ) {
            $limit = (int) $limit;
            $type  = sanitize_key( (string) $type );

            // 0: the pack does not allow the type. Types no longer registered are left out.
            if ( 0 === $limit || ! post_type_exists( $type ) ) {
                continue;
            }

            $object    = get_post_type_object( $type );
            $remaining = isset( $left[ $type ] ) ? (int) $left[ $type ] : $limit;
            $line      = $this->usage_line( $type, $object ? $object->labels->name : $type, $limit, $remaining );

            if ( -1 === $line['limit'] ) {
                if ( ! $object || ! $object->public ) {
                    continue;
                }

                $line['used'] = $this->posts_since( $user_id, $type, $since );
            }

            $items[] = $line;
        }

        $featured = (int) get_post_meta( $pack_id, '_total_feature_item', true );

        return [
            'items'    => $items,
            'featured' => 0 !== $featured
                ? $this->usage_line( 'featured', __( 'Featured items', 'wp-user-frontend' ), $featured, isset( $user_pack['total_feature_item'] ) ? (int) $user_pack['total_feature_item'] : $featured )
                : null,
            'expire'   => isset( $user_pack['expire'] ) ? $this->format_date( (string) $user_pack['expire'] ) : '',
        ];
    }

    /**
     * The user's posts of a type since a date (any status but trash / auto draft).
     *
     * @param int    $user_id User ID
     * @param string $type    Post type
     * @param string $since   Y-m-d H:i:s ('' or not a date: all time)
     *
     * @return int
     */
    private function posts_since( $user_id, $type, $since ) {
        $args = [
            'post_type'      => $type,
            'author'         => (int) $user_id,
            'post_status'    => [ 'publish', 'pending', 'draft', 'future', 'private' ],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
        ];

        if ( false !== strtotime( $since ) ) {
            $args['date_query'] = [ [ 'after' => $since, 'inclusive' => true ] ];
        }

        return (int) ( new \WP_Query( $args ) )->found_posts;
    }

    /**
     * One usage line (-1 = unlimited; used never below 0).
     *
     * @param string $type      Key
     * @param string $label     Label
     * @param int    $limit     Pack limit
     * @param int    $remaining Remaining for the user
     *
     * @return array
     */
    private function usage_line( $type, $label, $limit, $remaining ) {
        $unlimited = -1 === $limit || -1 === $remaining;

        return [
            'type'      => $type,
            'label'     => wp_strip_all_tags( (string) $label ),
            'limit'     => $unlimited ? -1 : $limit,
            'remaining' => $unlimited ? -1 : max( 0, $remaining ),
            'used'      => $unlimited ? null : max( 0, $limit - $remaining ),
        ];
    }

    /**
     * Store filter of one pack and status.
     *
     * @since WPUF_SINCE
     *
     * @param int    $pack_id Pack ID
     * @param string $status  Stored status
     *
     * @return array
     */
    private function status_filter( $pack_id, $status ) {
        $filter           = [ 'pack_id' => $pack_id ];
        $filter['status'] = $status;

        return $filter;
    }

    /**
     * A stored date in the site's date format; a value that is not a date
     * ("unlimited", empty) comes back as stored.
     *
     * @since WPUF_SINCE
     *
     * @param string $value Stored value
     *
     * @return string
     */
    private function format_date( $value ) {
        $value = sanitize_text_field( $value );

        if ( '' === $value || false === strtotime( $value ) || 0 === strpos( $value, '0000-00-00' ) ) {
            return $value;
        }

        return mysql2date( Stores::settings()->wp_option( 'date_format' ), $value );
    }

    /**
     * DELETE /admin/users/{user_id}/pack: remove a user's pack (the pack box
     * on the WP user profile screen).
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_user_pack( WP_REST_Request $request ) {
        $user_id = (int) $request['user_id'];

        if ( ! get_userdata( $user_id ) ) {
            return $this->error( 'not_found', __( 'User not found.', 'wp-user-frontend' ), 404 );
        }

        return rest_ensure_response( [ 'deleted' => User_Profile::remove_user_pack( $user_id, (int) $request['pack_id'] ) ] );
    }
}
