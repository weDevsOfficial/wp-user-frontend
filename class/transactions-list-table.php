<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class WPUF_Transactions_List_Table extends WP_List_Table {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => __( 'transaction', 'wpuf' ),
            'plural'   => __( 'transactions', 'wpuf' ),
            'ajax'     => false
        ) );
    }

    /**
     * Render the bulk edit checkbox.
     *
     * @param array $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-items[]" value="%s" />', $item->id
        );
    }

    /**
     * Get a list of columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'id'             => __( 'ID', 'wpuf' ),
            'status'         => __( 'Status', 'wpuf' ),
            'user'           => __( 'User', 'wpuf' ),
            'cost'           => __( 'Cost', 'wpuf' ),
            'tax'            => __( 'Tax', 'wpuf' ),
            'post_id'        => __( 'Post ID', 'wpuf' ),
            'pack_id'        => __( 'Pack ID', 'wpuf' ),
            'payment_type'   => __( 'Gateway', 'wpuf' ),
            'payer'          => __( 'Payer', 'wpuf' ),
            'payer_email'    => __( 'Email', 'wpuf' ),
            'transaction_id' => __( 'Trans ID', 'wpuf' ),
            'created'        => __( 'Date', 'wpuf' ),
        );

        return $columns;
    }

    /**
     * Get a list of sortable columns.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'id'      => array( 'id', false ),
            'status'  => array( 'status', false ),
            'created' => array( 'created', false ),
        );

        return $sortable_columns;
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links = array();
        $base_link    = admin_url( 'admin.php?page=wpuf_transaction' );

        $transactions_count         = wpuf_get_transactions( array( 'count' => true ) );
        $transactions_pending_count = wpuf_get_pending_transactions( array( 'count' => true ) );

        $status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';

        $status_links['all']     = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), ( $status == 'all' ) ? 'current' : '', __( 'All', 'wpuf' ), $transactions_count );
        $status_links['pending'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'pending' ), $base_link ), ( $status == 'pending' ) ? 'current' : '', __( 'Pending', 'wpuf' ), $transactions_pending_count );

        return $status_links;
    }

    /**
     * Method for id column.
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    public function column_id( $item ) {
        $id = $item->id;

        $delete_nonce = wp_create_nonce( 'wpuf-delete-transaction' );
        $title        = '<strong>#' . $id . '</strong>';

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'pending' ) {
            $accept_nonce = wp_create_nonce( 'wpuf-accept-transaction' );
            $reject_nonce = wp_create_nonce( 'wpuf-reject-transaction' );

            $actions = array(
                'accept' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'accept', absint( $id ), $accept_nonce, __( 'Accept', 'wpuf' ) ),
                'reject' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'reject', absint( $id ), $reject_nonce, __( 'Reject', 'wpuf' ) )
            );
        } else {
            $actions = array(
                'delete' => sprintf( '<a href="?page=%s&action=%s&id=%d&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $id ), $delete_nonce, __( 'Delete', 'wpuf' ) )
            );
        }

        return $title . $this->row_actions( $actions );
    }

    /**
     * Define each column of the table.
     *
     * @param  array  $item
     * @param  string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {

        switch( $column_name ) {
            case 'status':
                return ( $item->status == 'completed' ) ? '<span class="wpuf-status-completed" title="Completed"></span>' : '<span class="wpuf-status-processing" title="Processing"></span>';
            case 'user':
                $user = get_user_by( 'id', $item->user_id );
                $post_author_id =  get_post_field( 'post_author', $item->post_id ) ;
                $post_author    =  get_the_author_meta( 'nickname', $post_author_id);
                return ! empty( $user ) ? sprintf( '<a href="%s">%s</a>', admin_url( 'user-edit.php?user_id=' . $item->user_id ), $user->user_nicename ) : $post_author ;
            case 'cost':
                return wpuf_format_price( $item->cost );
            case 'tax':
                return wpuf_format_price( $item->tax );
            case 'post_id':
                return ! empty( $item->post_id ) ? sprintf( '<a href="%s">%s</a>', admin_url( 'post.php?post=' . $item->post_id . '&action=edit' ), $item->post_id ) : '-';
            case 'pack_id':
                return ! empty( $item->pack_id ) ? sprintf( '<a href="%s">%s</a>', admin_url( 'post.php?post=' . $item->pack_id . '&action=edit' ), $item->pack_id ) : '-';
            case 'payer':
                return ! empty( $item->payer_first_name ) ? $item->payer_first_name . ' ' . $item->payer_last_name : '-';
            case 'created':
                return ! empty( $item->created ) ? date( 'd-m-Y', strtotime( $item->created ) ) : '-';
            default:
                return ! empty( $item->{$column_name} ) ? $item->{$column_name} : '-';
                break;
        }
    }

    /**
     * Message to be displayed when there are no items.
     *
     * @return void
     */
    public function no_items() {
        _e( 'No transactions found.', 'wpuf' );
    }

    /**
     * Set the bulk actions.
     *
     * @return array
     */
    public function get_bulk_actions() {

        if ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == 'pending' ) {
            $actions = array(
                'bulk-accept' => __( 'Accept', 'wpuf' ),
                'bulk-reject' => __( 'Reject', 'wpuf' ),
            );
        } else {
            $actions = array(
                'bulk-delete' => __( 'Delete', 'wpuf' ),
            );
        }

        return $actions;
    }

    /**
     * Prepares the list of items for displaying.
     *
     * @return void
     */
    public function prepare_items() {
        $per_page     = $this->get_items_per_page( 'transactions_per_page', 20 );
        $current_page = $this->get_pagenum();

        $status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';

        if ( $status == 'pending' ) {
            $total_items = wpuf_get_pending_transactions( array( 'count' => true ) );
        } else {
            $total_items = wpuf_get_transactions( array( 'count' => true ) );
        }

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ) );

        $this->_column_headers = $this->get_column_info();

        $this->process_actions();

        $offset = ( $current_page - 1 ) * $per_page;

        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        if ( $status == 'pending' ) {
            $this->items = wpuf_get_pending_transactions( $args );
        } else {
            $this->items = wpuf_get_transactions( $args );
        }
    }

    /**
     * Process the actions
     *
     * @return void
     */
    private function process_actions() {
        global $wpdb;

        $page_url = menu_page_url( 'wpuf_transaction', false );

        // Delete Transaction
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'delete' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-delete-transaction' ) ) {
                return false;
            }

            $id = absint( esc_sql( $_REQUEST['id'] ) );

            $wpdb->delete( $wpdb->prefix . 'wpuf_transaction', array( 'id' => $id ), array( '%d' ) );

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Delete Transactions
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-delete' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-transactions' ) ) {
                return false;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $id = absint( $id );

                $wpdb->delete( $wpdb->prefix . 'wpuf_transaction', array( 'id' => $id ), array( '%d' ) );
            }

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Reject Transaction
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'reject' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'reject' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-reject-transaction' ) ) {
                return false;
            }

            $id      = absint( esc_sql( $_REQUEST['id'] ) );
            $info    = get_post_meta( $id, '_data', true );
            $gateway = $info['post_data']['wpuf_payment_method'];

            do_action( "wpuf_{$gateway}_bank_order_reject", $id );
            wp_delete_post( $id, true );

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Reject Transactions
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-reject' )
             || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-reject' )
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-transactions' ) ) {
                return false;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $id      = absint( $id );
                $info    = get_post_meta( $id, '_data', true );
                $gateway = $info['post_data']['wpuf_payment_method'];

                do_action( "wpuf_{$gateway}_bank_order_reject", $id );

                wp_delete_post( $id, true );
            }

            // Redirect
            wp_redirect( $page_url );
            exit;
        }

        // Accept Transaction
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'accept' ) 
            || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'accept' ) 
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-accept-transaction' ) ) {
                return false;
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $id   = absint( $_REQUEST['id'] );
            $info = get_post_meta( $id, '_data', true );

            if ( $info ) {
                switch ( $info['type'] ) {
                    case 'post':
                        $post_id = $info['item_number'];
                        $pack_id = 0;
                        break;

                    case 'pack':
                        $post_id = 0;
                        $pack_id = $info['item_number'];
                        break;
                }

                $payer_address = wpuf_get_user_address();

                $transaction = array(
                    'user_id'          => $info['user_info']['id'],
                    'status'           => 'completed',
                    'subtotal'         => $info['subtotal'],
                    'tax'              => $info['tax'],
                    'cost'             => $info['price'],
                    'post_id'          => $post_id,
                    'pack_id'          => $pack_id,
                    'payer_first_name' => $info['user_info']['first_name'],
                    'payer_last_name'  => $info['user_info']['last_name'],
                    'payer_address'    => $payer_address,
                    'payer_email'      => $info['user_info']['email'],
                    'payment_type'     => 'Bank/Manual',
                    'transaction_id'   => $id,
                    'created'          => current_time( 'mysql' )
                );

                do_action( 'wpuf_gateway_bank_order_complete', $transaction, $id );

                WPUF_Payment::insert_payment( $transaction );

                $coupon_id = $info['post_data']['coupon_id'];

                if ( $coupon_id ) {
                    $pre_usage = get_post_meta( $coupon_id, '_coupon_used', true );
                    $pre_usage = (empty( $pre_usage )) ? 0 : $pre_usage;
                    $new_use   = $pre_usage + 1;

                    update_post_meta( $coupon_id, '_coupon_used', $new_use );
                }

                wp_delete_post( $id, true );
            }

            wp_redirect( $page_url );
            exit;
        }

        // Bulk Accept Transaction
        if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk-accept' ) 
            || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-accept' ) 
        ) {
            if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-transactions' ) ) {
                return false;
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $ids = esc_sql( $_REQUEST['bulk-items'] );

            foreach ( $ids as $id ) {
                $id = absint( $id );

                $info = get_post_meta( $id, '_data', true );

                if ( $info ) {
                    switch ( $info['type'] ) {
                        case 'post':
                            $post_id = $info['item_number'];
                            $pack_id = 0;
                            break;

                        case 'pack':
                            $post_id = 0;
                            $pack_id = $info['item_number'];
                            break;
                    }

                    $transaction = array(
                        'user_id'          => $info['user_info']['id'],
                        'status'           => 'completed',
                        'subtotal'         => $info['subtotal'],
                        'tax'              => $info['tax'], 
                        'cost'             => $info['price'],
                        'post_id'          => $post_id,
                        'pack_id'          => $pack_id,
                        'payer_first_name' => $info['user_info']['first_name'],
                        'payer_last_name'  => $info['user_info']['last_name'],
                        'payer_email'      => $info['user_info']['email'],
                        'payment_type'     => 'Bank/Manual',
                        'transaction_id'   => $id,
                        'created'          => current_time( 'mysql' )
                    );

                    do_action( 'wpuf_gateway_bank_order_complete', $transaction, $id );

                    WPUF_Payment::insert_payment( $transaction );
                    wp_delete_post( $id, true );
                }
            }
            
            wp_redirect( $page_url );
            exit;
        }

    }

}
