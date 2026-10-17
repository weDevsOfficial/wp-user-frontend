<?php
/**
 * Transactions data store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Contracts\DataStore;

/**
 * Rows of the Transactions screen: completed payments (the `wpuf_transaction`
 * table) and pending bank orders (`wpuf_order` posts) as one list. Every SQL
 * of the screen lives here; Platform\Transactions\TransactionService formats
 * the rows and runs the actions.
 *
 * @since WPUF_SINCE
 */
class TransactionStore implements DataStore {

    /**
     * Row kind: a completed payment row.
     */
    const KIND_TRANSACTION = 'transaction';

    /**
     * Row kind: a pending bank order (`wpuf_order` post).
     */
    const KIND_ORDER = 'order';

    /**
     * Status filters.
     */
    const STATUSES = [ 'all', 'completed', 'pending' ];

    /**
     * Sort columns.
     */
    const ORDERBY = [ 'created', 'id' ];

    /**
     * The gateway label of bank payments.
     */
    const BANK_LABEL = 'Bank/Manual';

    /**
     * Columns of the transaction table a single-value read may ask for.
     *
     * @since WPUF_SINCE
     */
    const COLUMNS = [ 'id', 'user_id', 'status', 'subtotal', 'discount', 'coupon_id', 'tax', 'cost', 'post_id', 'pack_id', 'payer_first_name', 'payer_last_name', 'payer_email', 'payment_type', 'payer_address', 'transaction_id', 'created' ];

    /**
     * Whether a payment row exists.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row ID
     *
     * @return bool
     */
    public function exists( $id ) {
        return null !== $this->read( $id );
    }

    /**
     * A payment row.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row ID
     *
     * @return object|null
     */
    public function read( $id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE id = %d", absint( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return is_object( $row ) ? $row : null;
    }

    /**
     * A payment row (rows have no model).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row ID
     *
     * @return object|null
     */
    public function find( $id ) {
        return $this->read( $id );
    }

    /**
     * Payment rows by ID, keyed by ID.
     *
     * @since WPUF_SINCE
     *
     * @param int[] $ids Row IDs
     *
     * @return object[]
     */
    public function read_many( array $ids ) {
        global $wpdb;

        $ids = array_values( array_filter( array_map( 'absint', $ids ) ) );

        if ( ! $ids ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $rows         = [];

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.DirectDatabaseQuery
        foreach ( (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE id IN ({$placeholders})", $ids ) ) as $row ) {
            $rows[ (int) $row->id ] = $row;
        }

        return $rows;
    }

    /**
     * One page of the list: `kind` + `id` of each row, in list order.
     *
     * @since WPUF_SINCE
     *
     * @param array $args status, search, gateway, from, to, orderby, order, page, per_page
     *
     * @return object[]
     */
    public function query( array $args = [] ) {
        global $wpdb;

        $args  = $this->args( $args );
        $union = $this->union( $args );

        if ( '' === $union ) {
            return [];
        }

        $sort  = 'id' === $args['orderby'] ? 'id' : 'sort_date';
        $order = $args['order'];

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- filter values are prepared in filters(); sort column and order are allowlisted.
        return (array) $wpdb->get_results( $wpdb->prepare( "SELECT kind, id FROM ({$union}) AS wpuf_rows ORDER BY {$sort} {$order}, id {$order} LIMIT %d, %d", $args['offset'], $args['per_page'] ) );
    }

    /**
     * Rows matching the filters (every page).
     *
     * @since WPUF_SINCE
     *
     * @param array $args As query()
     *
     * @return int
     */
    public function count( array $args = [] ) {
        global $wpdb;

        $union = $this->union( $this->args( $args ) );

        if ( '' === $union ) {
            return 0;
        }

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM ({$union}) AS wpuf_rows" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- filter values are prepared in filters().
    }

    /**
     * Totals of the screen's cards and tabs.
     *
     * @since WPUF_SINCE
     *
     * @return array all, completed, pending (ints), income, tax (raw sums)
     */
    public function counts() {
        global $wpdb;

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $tx      = $wpdb->get_row( "SELECT COUNT(*) AS rows_all, SUM( status = 'completed' ) AS completed, SUM( CASE WHEN status = 'completed' THEN cost + 0 ELSE 0 END ) AS income, SUM( CASE WHEN status = 'completed' THEN tax + 0 ELSE 0 END ) AS tax FROM {$wpdb->prefix}wpuf_transaction" );
        $pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpuf_order' AND post_status IN ('publish','pending')" );
        // phpcs:enable

        $tx = is_object( $tx ) ? $tx : (object) [
            'rows_all'  => 0,
            'completed' => 0,
            'income'    => 0,
            'tax'       => 0,
        ];

        return [
            'all'       => (int) $tx->rows_all + $pending,
            'completed' => (int) $tx->completed,
            'pending'   => $pending,
            'income'    => $tx->income,
            'tax'       => $tx->tax,
        ];
    }

    /**
     * Gateways that have payment rows.
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    public function gateways() {
        global $wpdb;

        return (array) $wpdb->get_col( "SELECT DISTINCT payment_type FROM {$wpdb->prefix}wpuf_transaction WHERE payment_type <> '' ORDER BY payment_type" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Delete a payment row.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row ID
     *
     * @return bool
     */
    public function delete( $id ) {
        global $wpdb;

        return (bool) $wpdb->delete( $wpdb->prefix . 'wpuf_transaction', [ 'id' => absint( $id ) ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * The newest payment row of a user for a pack (null when none).
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     * @param int $pack_id Pack id
     *
     * @return object|null
     */
    public function latest_for_pack( $user_id, $pack_id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d AND pack_id = %d ORDER BY id DESC LIMIT 1", absint( $user_id ), absint( $pack_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return is_object( $row ) ? $row : null;
    }

    /**
     * The transaction id of a user's payment for a pack (the first row the
     * table gives, as the cancel handler read it), null when there is no row.
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     * @param int $pack_id Pack id
     *
     * @return string|null
     */
    public function transaction_id_for_pack( $user_id, $pack_id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT transaction_id FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d AND pack_id = %d LIMIT 1", absint( $user_id ), absint( $pack_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return is_object( $row ) ? (string) $row->transaction_id : null;
    }

    /**
     * The gateway of a user's newest completed payment ('' when none).
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     *
     * @return string
     */
    public function last_completed_gateway( $user_id ) {
        global $wpdb;

        return (string) $wpdb->get_var( $wpdb->prepare( "SELECT payment_type FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %s AND status = 'completed' ORDER BY created DESC", $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Empty the transactions table (Tools > Transactions).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function truncate() {
        global $wpdb;

        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- the tool empties the plugin's own table.
    }

    /**
     * Defaults and allowlists of the list arguments.
     *
     * @param array $args Raw arguments
     *
     * @return array
     */
    private function args( array $args ) {
        $args = wp_parse_args(
            $args,
            [
                'status'   => 'all',
                'search'   => '',
                'gateway'  => '',
                'from'     => '',
                'to'       => '',
                'user_id'  => 0,
                'pack_id'  => 0,
                'coupon_id' => 0,
                'orderby'  => 'created',
                'order'    => 'desc',
                'page'     => 1,
                'per_page' => 20,
            ]
        );

        $args['status']   = in_array( $args['status'], self::STATUSES, true ) ? $args['status'] : 'all';
        $args['orderby']  = in_array( $args['orderby'], self::ORDERBY, true ) ? $args['orderby'] : 'created';
        $args['order']    = 'asc' === strtolower( (string) $args['order'] ) ? 'ASC' : 'DESC';
        $args['per_page'] = max( 1, min( 100, absint( $args['per_page'] ) ) );
        $args['user_id']  = absint( $args['user_id'] );
        $args['pack_id']  = absint( $args['pack_id'] );
        $args['coupon_id'] = absint( $args['coupon_id'] );
        $args['offset']   = ( max( 1, absint( $args['page'] ) ) - 1 ) * $args['per_page'];

        return $args;
    }

    /**
     * The UNION of payment rows and pending orders for the filters, or '' for none.
     *
     * @param array $args Parsed arguments (see args())
     *
     * @return string
     */
    private function union( array $args ) {
        global $wpdb;

        list( $tx_sql, $order_sql ) = $this->filters( $args );

        $parts = [];

        if ( 'pending' !== $args['status'] ) {
            $parts[] = "SELECT 'transaction' AS kind, id, created AS sort_date FROM {$wpdb->prefix}wpuf_transaction WHERE 1=1 {$tx_sql}" . ( 'completed' === $args['status'] ? " AND status = 'completed'" : '' );
        }

        if ( 'completed' !== $args['status'] && null !== $order_sql ) {
            $parts[] = "SELECT 'order' AS kind, ID AS id, post_date AS sort_date FROM {$wpdb->posts} WHERE post_type = 'wpuf_order' AND post_status IN ('publish','pending') {$order_sql}";
        }

        return $parts ? '(' . implode( ') UNION ALL (', $parts ) . ')' : '';
    }

    /**
     * SQL conditions for the search, gateway and date filters: one for the
     * transaction table, one for orders (null: no order matches).
     *
     * @param array $args Query args
     *
     * @return array [ string, string|null ]
     */
    private function filters( array $args ) {
        global $wpdb;

        $tx    = '';
        $order = '';

        $search = trim( (string) $args['search'] );

        if ( '' !== $search ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $tx  .= $wpdb->prepare(
                ' AND ( payer_email LIKE %s OR payer_first_name LIKE %s OR payer_last_name LIKE %s OR transaction_id LIKE %s OR CAST(id AS CHAR) = %s )',
                $like,
                $like,
                $like,
                $like,
                $search
            );
            // An order keeps the payer in its serialized `_data` meta.
            $order .= $wpdb->prepare(
                " AND ( CAST(ID AS CHAR) = %s OR EXISTS ( SELECT 1 FROM {$wpdb->postmeta} m WHERE m.post_id = ID AND m.meta_key = '_data' AND m.meta_value LIKE %s ) )",
                $search,
                $like
            );
        }

        $gateway = (string) $args['gateway'];

        if ( '' !== $gateway ) {
            $tx .= $wpdb->prepare( ' AND payment_type = %s', $gateway );

            // Orders are bank payments waiting for approval.
            if ( self::BANK_LABEL !== $gateway ) {
                $order = null;
            }
        }

        // One user's / pack's / coupon's payments (links from Subscribers, coupons,
        // packs and the user profile). A pending bank order belongs to its author;
        // its pack and coupon are inside serialized data, so those filters leave orders out.
        if ( ! empty( $args['user_id'] ) ) {
            $tx .= $wpdb->prepare( ' AND user_id = %d', $args['user_id'] );

            if ( null !== $order ) {
                $order .= $wpdb->prepare( ' AND post_author = %d', $args['user_id'] );
            }
        }

        foreach ( [ 'pack_id', 'coupon_id' ] as $column ) {
            if ( ! empty( $args[ $column ] ) ) {
                $tx   .= $wpdb->prepare( " AND {$column} = %d", $args[ $column ] ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- column from the fixed list above.
                $order = null;
            }
        }

        foreach ( [
			'from' => '>=',
			'to' => '<=',
		] as $key => $operator ) {
            $date = (string) $args[ $key ];

            if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
                continue;
            }

            $value = $date . ( 'to' === $key ? ' 23:59:59' : ' 00:00:00' );
            $tx   .= $wpdb->prepare( " AND created {$operator} %s", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- operator is from the fixed map above.

            if ( null !== $order ) {
                $order .= $wpdb->prepare( " AND post_date {$operator} %s", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- operator is from the fixed map above.
            }
        }

        return [ $tx, $order ];
    }

    /**
     * One column of the user's latest pack transaction (pack_id != 0), any status.
     *
     * @since WPUF_SINCE
     *
     * @param int    $user_id User id
     * @param string $column  A column of self::COLUMNS
     *
     * @return string|null Null when the column is unknown or the user has no pack transaction
     */
    public function latest_pack_value( $user_id, $column ) {
        global $wpdb;

        if ( ! $user_id || ! in_array( $column, self::COLUMNS, true ) ) {
            return null;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- allowlisted column
        return $wpdb->get_var( $wpdb->prepare( "SELECT {$column} FROM {$wpdb->prefix}wpuf_transaction WHERE pack_id != 0 AND user_id = %d ORDER BY id DESC LIMIT 1", $user_id ) );
    }

    /**
     * Every transaction row of a user, newest first (privacy export, invoices).
     *
     * @since WPUF_SINCE
     *
     * @param int      $user_id User id
     * @param string[] $columns Columns to select (self::COLUMNS), empty = all
     * @param int      $number  Rows (0 = all)
     * @param int      $offset  Offset
     * @param bool     $assoc   Rows as arrays instead of objects
     *
     * @return array
     */
    public function rows_for_user( $user_id, array $columns = [], $number = 0, $offset = 0, $assoc = false ) {
        global $wpdb;

        $columns = array_values( array_intersect( $columns, self::COLUMNS ) );
        $select  = $columns ? implode( ', ', $columns ) : '*';
        $sql     = "SELECT {$select} FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d ORDER BY id DESC";
        $values  = [ (int) $user_id ];

        if ( (int) $number > 0 ) {
            $sql     .= ' LIMIT %d OFFSET %d';
            $values[] = (int) $number;
            $values[] = max( 0, (int) $offset );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- columns allowlisted, values prepared
        return (array) $wpdb->get_results( $wpdb->prepare( $sql, $values ), $assoc ? ARRAY_A : OBJECT );
    }

    /**
     * How many transaction rows a user has.
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     *
     * @return int
     */
    public function count_for_user( $user_id ) {
        global $wpdb;

        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d", (int) $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * The row carrying a gateway transaction id (invoices).
     *
     * @since WPUF_SINCE
     *
     * @param string $transaction_id Gateway transaction id
     *
     * @return object|null
     */
    public function find_by_transaction_id( $transaction_id ) {
        global $wpdb;

        if ( '' === (string) $transaction_id ) {
            return null;
        }

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE transaction_id = %s ORDER BY id DESC LIMIT 1", (string) $transaction_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return $row ? $row : null;
    }

    /**
     * The pending order's payment info (`_data` of a wpuf_order post).
     *
     * @since WPUF_SINCE
     *
     * @param int $order_id Order post id
     *
     * @return array|null Null when the post has none
     */
    public function order_info( $order_id ) {
        $info = get_post_meta( $order_id, '_data', true );

        return is_array( $info ) ? $info : null;
    }

    /**
     * Count one more use of a coupon (the coupon post's `_coupon_used`).
     *
     * @since WPUF_SINCE
     *
     * @param int $coupon_id Coupon post id
     *
     * @return int The new count
     */
    public function record_coupon_use( $coupon_id ) {
        $used = (int) get_post_meta( $coupon_id, '_coupon_used', true ) + 1;
        update_post_meta( $coupon_id, '_coupon_used', $used );

        return $used;
    }

    /**
     * Every row of the transaction table, sorted and paged (the legacy completed-transactions helper).
     *
     * @since WPUF_SINCE
     *
     * @param string $orderby One of id, status, created
     * @param string $order   ASC|DESC
     * @param int    $offset  Offset
     * @param int    $number  Rows
     *
     * @return object[]
     */
    public function all_rows( $orderby = 'id', $order = 'DESC', $offset = 0, $number = 20 ) {
        global $wpdb;

        $orderby = in_array( $orderby, [ 'id', 'status', 'created' ], true ) ? $orderby : 'id';
        $order   = 'ASC' === strtoupper( (string) $order ) ? 'ASC' : 'DESC';

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- sort column and order allowlisted
        return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_transaction ORDER BY `{$orderby}` {$order} LIMIT %d, %d", absint( $offset ), absint( $number ) ) );
    }

    /**
     * How many rows the transaction table has.
     *
     * @since WPUF_SINCE
     *
     * @return int
     */
    public function count_all() {
        global $wpdb;

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * When the user's latest completed transaction was created (the account page).
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     *
     * @return string|null
     */
    public function last_completed_date( $user_id ) {
        global $wpdb;

        return $wpdb->get_var( $wpdb->prepare( "SELECT created FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d AND status = 'completed' ORDER BY created DESC LIMIT 1", (int) $user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }
}
