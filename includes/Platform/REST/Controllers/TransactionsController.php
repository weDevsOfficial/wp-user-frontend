<?php
/**
 * Transactions REST controller
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Transactions\TransactionService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/transactions`: the list (status tab, search, gateway, date
 * range, sort, page) for the subscription managers, and accept / reject /
 * delete for site administrators (the classic page's accept already needed
 * `manage_options`; reject and delete had no check).
 *
 * @since WPUF_SINCE
 */
class TransactionsController extends RestController {

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'admin/transactions';

    /**
     * Resource name in error codes.
     *
     * @var string
     */
    protected $resource = 'transactions';

    /**
     * The transactions.
     *
     * @var TransactionService
     */
    private $transactions;

    /**
     * Constructor.
     *
     * @since WPUF_SINCE
     *
     * @param TransactionService|null $transactions The transactions
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
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => $this->permission( Caps::MANAGE_SUBSCRIPTIONS ),
                    'args'                => [
                        'status'  => [
                            'type'    => 'string',
                            'enum'    => TransactionService::STATUSES,
                            'default' => 'all',
                        ],
                        'gateway' => [
                            'type'              => 'string',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'from'    => [
                            'type'    => 'string',
                            'default' => '',
                            'pattern' => '^(\d{4}-\d{2}-\d{2})?$',
                        ],
                        'to'      => [
                            'type'    => 'string',
                            'default' => '',
                            'pattern' => '^(\d{4}-\d{2}-\d{2})?$',
                        ],
                    ] + $this->collection_args( TransactionService::ORDERBY, 'created', [ 'per_page' => null ] ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<action>accept|reject|delete)',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'run_action' ],
                    'permission_callback' => [ $this, 'can_manage_site' ],
                    'args'                => [
                        'items' => [
                            'type'     => 'array',
                            'required' => true,
                            'minItems' => 1,
                            'maxItems' => 200,
                            'items'    => [
                                'type'       => 'object',
                                'properties' => [
                                    'kind' => [
                                        'type' => 'string',
                                        'enum' => [ TransactionService::KIND_TRANSACTION, TransactionService::KIND_ORDER ],
                                    ],
                                    'id'   => [
                                        'type'    => 'integer',
                                        'minimum' => 1,
                                    ],
                                ],
                                'required'   => [ 'kind', 'id' ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Who may accept, reject and delete.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_manage_site() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! Caps::can( Caps::MANAGE_SITE ) ) {
            return new WP_Error( 'wpuf_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'wp-user-frontend' ), [ 'status' => 403 ] );
        }

        return true;
    }

    /**
     * A page of rows, the tab counts and totals. A `per_page` sent is saved
     * for the user.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function get_items( $request ) {
        $per_page = $request['per_page'];

        if ( $per_page ) {
            $this->transactions->save_per_page( $per_page );
        } else {
            $per_page = $this->transactions->per_page();
        }

        $result = $this->transactions->query(
            [
                'status'   => $request['status'],
                'search'   => $request['search'],
                'gateway'  => $request['gateway'],
                'from'     => $request['from'],
                'to'       => $request['to'],
                'orderby'  => $request['orderby'],
                'order'    => $request['order'],
                'page'     => $request['page'],
                'per_page' => $per_page,
            ]
        );

        return rest_ensure_response(
            [
                'items'    => $result['items'],
                'total'    => $result['total'],
                'per_page' => (int) $per_page,
                'counts'   => $result['counts'],
            ]
        );
    }

    /**
     * Accept, reject or delete rows.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function run_action( $request ) {
        $result = $this->transactions->run( $request['action'], (array) $request['items'] );

        $messages = [
            /* translators: %d: number of payments */
            'accept' => _n( '%d payment accepted.', '%d payments accepted.', $result['done'], 'wp-user-frontend' ),
            /* translators: %d: number of payments */
            'reject' => _n( '%d payment rejected.', '%d payments rejected.', $result['done'], 'wp-user-frontend' ),
            /* translators: %d: number of transactions */
            'delete' => _n( '%d transaction deleted.', '%d transactions deleted.', $result['done'], 'wp-user-frontend' ),
        ];

        $result['message'] = sprintf( $messages[ $request['action'] ], $result['done'] );
        $result['counts']  = $this->transactions->counts();

        return rest_ensure_response( $result );
    }
}
