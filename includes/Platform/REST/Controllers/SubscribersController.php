<?php
/**
 * Subscribers REST controller
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

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
                'orderby'  => [
                    'type'    => 'string',
                    'enum'    => [ 'id', 'user_id', 'starts_from', 'expire' ],
                    'default' => 'id',
                ],
                'order'    => [
                    'type'    => 'string',
                    'enum'    => [ 'asc', 'desc' ],
                    'default' => 'desc',
                ],
                'page'     => [
                    'type'    => 'integer',
                    'default' => 1,
                    'minimum' => 1,
                ],
                'per_page' => [
                    'type'    => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100,
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

        return rest_ensure_response(
            [
                'pack'   => [
                    'id'    => $pack_id,
                    'title' => get_the_title( $pack ),
                ],
                'items'  => array_map( [ $this, 'prepare_row' ], $rows ),
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

        return mysql2date( get_option( 'date_format' ), $value );
    }
}
