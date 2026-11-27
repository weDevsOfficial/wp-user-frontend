<?php
/**
 * Subscriber store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Contracts\DataStore;

/**
 * The `wpuf_subscribers` table: one row per pack a user took (free, paid,
 * recurring), with its status, gateway and transaction. Every SQL of the
 * plugin against that table lives here.
 *
 * @since WPUF_SINCE
 */
class SubscriberStore implements DataStore {

    /**
     * Columns a list may sort by.
     *
     * @since WPUF_SINCE
     */
    const ORDERBY = [ 'id', 'user_id', 'subscribtion_id', 'subscribtion_status', 'starts_from', 'expire' ];

    /**
     * Columns of a row, in table order.
     */
    const COLUMNS = [ 'user_id', 'name', 'subscribtion_id', 'subscribtion_status', 'gateway', 'transaction_id', 'starts_from', 'expire' ];

    /**
     * The table name with its prefix.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function table() {
        global $wpdb;

        return $wpdb->prefix . 'wpuf_subscribers';
    }

    /**
     * Whether a row exists.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row id
     *
     * @return bool
     */
    public function exists( $id ) {
        return null !== $this->read( $id );
    }

    /**
     * A row.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row id
     *
     * @return object|null
     */
    public function read( $id ) {
        global $wpdb;

        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpuf_subscribers WHERE id = %d", absint( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return is_object( $row ) ? $row : null;
    }

    /**
     * A row (rows have no model).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Row id
     *
     * @return object|null
     */
    public function find( $id ) {
        return $this->read( $id );
    }

    /**
     * Rows matching the filters.
     *
     * @since WPUF_SINCE
     *
     * @param array $args { @type int $pack_id Pack id @type string $status Status @type int $user_id User id
     *                     @type string $orderby One of self::ORDERBY (default id) @type string $order ASC|DESC (default DESC)
     *                     @type int $number Rows per page (0 = all) @type int $offset Offset }
     *
     * @return object[]
     */
    public function query( array $args = [] ) {
        global $wpdb;

        list( $where, $values ) = $this->where( $args );

        $orderby = isset( $args['orderby'] ) && in_array( $args['orderby'], self::ORDERBY, true ) ? $args['orderby'] : 'id';
        $order   = isset( $args['order'] ) && 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';
        $number  = isset( $args['number'] ) ? absint( $args['number'] ) : 0;
        $sql     = "SELECT * FROM {$wpdb->prefix}wpuf_subscribers{$where} ORDER BY {$orderby} {$order}";

        if ( $number > 0 ) {
            $sql     .= ' LIMIT %d OFFSET %d';
            $values[] = $number;
            $values[] = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;
        }

        return (array) ( $values ? $wpdb->get_results( $wpdb->prepare( $sql, $values ) ) : $wpdb->get_results( $sql ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- placeholders only; sort column and order allowlisted.
    }

    /**
     * Rows matching the filters.
     *
     * @since WPUF_SINCE
     *
     * @param array $args As query()
     *
     * @return int
     */
    public function count( array $args = [] ) {
        global $wpdb;

        list( $where, $values ) = $this->where( $args );
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wpuf_subscribers{$where}";

        return (int) ( $values ? $wpdb->get_var( $wpdb->prepare( $sql, $values ) ) : $wpdb->get_var( $sql ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- $where holds placeholders only.
    }

    /**
     * Distinct user ids of the rows matching a pack and / or a status, in row order.
     *
     * @since WPUF_SINCE
     *
     * @param int|string    $pack_id Pack id ('' for any)
     * @param string        $status  Status ('' for any)
     *
     * @return int[]
     */
    public function user_ids( $pack_id = '', $status = '' ) {
        $ids = [];

        foreach ( $this->query( [ 'pack_id' => $pack_id, 'status' => $status ] ) as $row ) {
            if ( ! in_array( (int) $row->user_id, $ids, true ) ) {
                $ids[] = (int) $row->user_id;
            }
        }

        return $ids;
    }

    /**
     * Add a row (the columns of COLUMNS, missing ones empty).
     *
     * @since WPUF_SINCE
     *
     * @param array $row Column => value
     *
     * @return int|false Row id, false when the insert failed
     */
    public function insert( array $row ) {
        global $wpdb;

        $data = [];

        foreach ( self::COLUMNS as $column ) {
            $data[ $column ] = isset( $row[ $column ] ) ? $row[ $column ] : '';
        }

        return $wpdb->insert( $this->table(), $data ) ? (int) $wpdb->insert_id : false; // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Mark a user's rows of a pack and transaction cancelled.
     *
     * @since WPUF_SINCE
     *
     * @param int    $user_id        User id
     * @param int    $pack_id        Pack id
     * @param string $transaction_id Transaction id ('Free' for a free pack)
     *
     * @return int|false Rows changed, false on error
     */
    public function cancel( $user_id, $pack_id, $transaction_id ) {
        global $wpdb;

        return $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $this->table(),
            [ 'subscribtion_status' => 'cancel' ],
            [
                'user_id'         => $user_id,
                'subscribtion_id' => $pack_id,
                'transaction_id'  => $transaction_id,
            ]
        );
    }

    /**
     * WHERE clause and values of the filters.
     *
     * @param array $args Filters
     *
     * @return array [ string, array ]
     */
    private function where( array $args ) {
        $clauses = [];
        $values  = [];

        if ( ! empty( $args['pack_id'] ) ) {
            $clauses[] = 'subscribtion_id = %d';
            $values[]  = (int) $args['pack_id'];
        }

        if ( ! empty( $args['status'] ) ) {
            $clauses[] = 'subscribtion_status = %s';
            $values[]  = (string) $args['status'];
        }

        if ( ! empty( $args['user_id'] ) ) {
            $clauses[] = 'user_id = %d';
            $values[]  = (int) $args['user_id'];
        }

        return [ $clauses ? ' WHERE ' . implode( ' AND ', $clauses ) : '', $values ];
    }
}
