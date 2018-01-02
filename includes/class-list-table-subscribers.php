<?php

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */
class WPUF_List_Table_Subscribers extends \WP_List_Table {

    function __construct() {
        parent::__construct( array(
            'singular' => 'subscriber',
            'plural'   => 'subscribers',
            'ajax'     => false
        ) );
    }

    function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    function no_items() {
        _e( 'No subscribers found', 'wpuf' );
    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb'                    => '<input type="checkbox" />',
            'id'                    => __( 'User ID', 'wpuf' ),
            'name'                  => __( 'User Name', 'wpuf' ),
            'subscription_id'       => __( 'Subscription ID', 'wpuf' ),
            'status'                => __( 'Status', 'wpuf' ),
            'gateway'               => __( 'Gateway', 'wpuf' ),
            'transaction_id'        => __( 'Transaction ID', 'wpuf' ),
            'starts_from'           => __( 'Starts from', 'wpuf' ),
            'expire'                => __( 'Expire date', 'wpuf' ),
        );
        return $columns;
    }

    /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
                return $item->user_id;
            case 'name':
                return $item->name;
            case 'subscription_id':
                return $item->subscribtion_id;
            case 'status':
                return $item->subscribtion_status;
            case 'gateway':
                return $item->gateway;
            case 'transaction_id':
                return $item->transaction_id;
            case 'starts_from':
                return $item->starts_from;
            case 'expire':
                return $item->expire;

            default:
                return isset( $item->$column_name ) ? $item->$column_name : '';
        }
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'id'        => array( 'id', true ),
        );

        return $sortable_columns;
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="subscriber_id[]" value="%d" />', $_REQUEST['post_ID']
        );
    }

    /**
     * Set the views
     *
     * @return array
     */
    public function get_views() {
        $status_links = [];
        $base_link    = admin_url( 'admin.php?page=wpuf_subscribers&pack='.$_REQUEST['post_ID'] );

        $subscribers_count         = count( $users = WPUF_Subscription::init()->subscription_pack_users( $_REQUEST['post_ID'] ) );
        $subscriptions_active_count = count( $users = WPUF_Subscription::init()->subscription_pack_users( $_REQUEST['post_ID'] ) );
        $subscriptions_cancle_count = count( $users = WPUF_Subscription::init()->subscription_pack_users( $_REQUEST['post_ID'] ) );

        $status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'all';

        $status_links['all']     = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'all' ), $base_link ), ( $status == 'all' ) ? 'current' : '', __( 'All', 'wpuf' ), $subscribers_count );
        $status_links['Completed'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'Completed' ), $base_link ), ( $status == 'pending' ) ? 'current' : '', __( 'Completed', 'wpuf' ), $subscriptions_active_count );
        $status_links['Cancel'] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => 'Cancel' ), $base_link ), ( $status == 'Cancel' ) ? 'current' : '', __( 'Cancel', 'wpuf' ), $subscriptions_cancle_count );

        return $status_links;
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {
        global $wpdb;

        $columns               = $this->get_columns();
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 20;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page -1 ) * $per_page;
        $this->page_status     = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if ( isset( $_REQUEST['orderby'] ) && isset( $_REQUEST['order'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order']   = $_REQUEST['order'] ;
        }

        $sql = 'SELECT * FROM ' . $wpdb->prefix . 'wpuf_subscribers';
        $sql .= isset( $_REQUEST['post_ID'] ) ? ' WHERE subscribtion_id = ' . $_REQUEST['post_ID'] : '';
        $sql .= isset( $_REQUEST['status'] ) ? ' AND subscribtion_status = "' . sanitize_text_field( $_REQUEST['status'] ) . '"' : '';

        $this->items  = $wpdb->get_results( $sql, OBJECT );

        $this->set_pagination_args( array(
            'total_items' => count( $this->items ),
            'per_page'    => $per_page
        ) );
    }
}
