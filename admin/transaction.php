<?php
global $wpdb;

$base_url = admin_url( 'admin.php?page=wpuf_transaction' );

if ( isset( $_GET['action'] ) && $_GET['action'] == 'order_accept' ) {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $order_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    $info     = get_post_meta( $order_id, '_data', true );

    if ( $info ) {

        switch ($info['type']) {
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
            'cost'             => $info['price'],
            'post_id'          => $post_id,
            'pack_id'          => $pack_id,
            'payer_first_name' => $info['user_info']['first_name'],
            'payer_last_name'  => $info['user_info']['last_name'],
            'payer_email'      => $info['user_info']['email'],
            'payment_type'     => 'Bank/Manual',
            'transaction_id'   => $order_id,
            'created'          => current_time( 'mysql' )
        );

        do_action( 'wpuf_gateway_bank_order_complete', $transaction, $order_id );

        WPUF_Payment::insert_payment( $transaction );
        wp_delete_post( $order_id, true );
    }
}

if ( isset( $_GET['action'] ) && $_GET['action'] == 'order_reject' ) {
    $order_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
    do_action( 'wpuf_gateway_bank_order_reject', $order_id );
    wp_delete_post( $order_id, true );
}

if ( isset( $_POST['delete_selected'] ) ) {

    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_delete_transactions' ) ) {
        wp_die("Cheating?");
    }

    if ( isset( $_POST['tr_id'] ) ) {
        foreach ($_POST['tr_id'] as $tr_id) {
            $wpdb->delete( $wpdb->prefix . 'wpuf_transaction', array('id' => $tr_id), array('%d') );
        }

        $transaction_deleted = true;
    }
}

$total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE status = 'completed'" );
$month_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE YEAR(`created`) = YEAR(NOW()) AND MONTH(`created`) = MONTH(NOW()) AND status = 'completed'" );
$transactions = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_transaction ORDER BY `created` DESC LIMIT 0, 60", OBJECT );
?>
<div class="wrap">
    <?php screen_icon( 'options-general' ); ?>
    <h2><?php _e( 'WP User Frontend: Payments Received', 'wpuf' ); ?></h2>

    <ul>
        <li>
            <strong><?php _e( 'Total Income:', 'wpuf' ); ?></strong> <?php echo get_option( 'wpuf_sub_currency_sym' ) . $total_income; ?><br />
        </li>
        <li>
            <strong><?php _e( 'This Month:', 'wpuf' ); ?></strong> <?php echo get_option( 'wpuf_sub_currency_sym' ) . $month_income; ?>
        </li>
    </ul>

    <?php if ( isset( $transaction_deleted ) && $transaction_deleted == true ) { ?>
        <div class="updated">
            <p><strong><?php _e( 'Transaction(s) deleted', 'wpuf' ); ?></strong></p>
        </div>
    <?php } ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'wpuf_delete_transactions' ); ?>

        <table class="widefat meta" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
                    <th scope="col"><?php _e( 'User ID', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Status', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Cost', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Post', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Pack ID', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Payer', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Email', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Type', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Transaction ID', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Created', 'wpuf' ); ?></th>
                </tr>
            </thead>
            <?php
            if ( $transactions ) {
                $count = 0;
                foreach ($transactions as $row) {
                    ?>
                    <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                        <th scope="row" class="check-column">
                            <input id="cb-select-8231" type="checkbox" name="tr_id[]" value="<?php echo $row->id; ?>">
                        </th>
                        <td><?php echo stripslashes( htmlspecialchars( $row->user_id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->status ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->cost ) ); ?></td>
                        <td>
                            <?php
                            if ( $row->post_id ) {
                                $post = WPUF_Subscription::post_by_orderid( $row->post_id );
                                if ( $post) {
                                    printf( '<a href="%s">%s</a>', get_permalink( $post->ID ), $post->post_title );
                                }
                            } else {
                                echo $row->post_id;
                            }
                            ?>
                        </td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->pack_id ) ); ?></td>
                        <td><?php echo $row->payer_first_name . ' ' . $row->payer_last_name; ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->payer_email ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->payment_type ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->transaction_id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->created ) ); ?></td>

                    </tr>
                    <?php
                    $count++;
                }
                ?>
            <?php } else { ?>
                <tr>
                    <td colspan="11"><?php _e( 'Nothing Found', 'wpuf' ); ?></td>
                </tr>
            <?php } ?>

        </table>

        <br>
        <input type="submit" class="button" name="delete_selected" value="<?php esc_attr_e( 'Delete Selected', 'wpuf' ); ?>">
    </form>


    <h2 style="margin-top: 30px;"><?php _e( 'Pending Orders', 'wpuf' ); ?></h2>
    <?php
    $args = array(
        'post_type'      => 'wpuf_order',
        'post_status'    => array( 'publish', 'pending' ),
        'posts_per_page' => -1
    );
    $wpuf_order_query = new WP_Query( apply_filters( 'wpuf_order_query', $args ) );
    $orders = $wpuf_order_query->get_posts();

    if ( $orders ) {
    ?>

        <table class="widefat meta" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'ID', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'User', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Type', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Cost', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Item Details', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Date', 'wpuf' ); ?></th>
                    <th scope="col"><?php _e( 'Action', 'wpuf' ); ?></th>
                </tr>
            </thead>
            <?php
            if ( $orders ) {
                $count = 0;
                foreach ($orders as $order) {
                    $data = get_post_meta( $order->ID, '_data', true );
                    // var_dump( $data );
                    ?>
                    <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                        <td>#<?php echo $order->ID; ?></td>
                        <td><?php printf('<a href="%s">[%d] %s %s</a>', admin_url( 'edit-user.php?id=' . $data['user_info']['id'] ), $data['user_info']['id'], $data['user_info']['first_name'], $data['user_info']['last_name'] ); ?></td>
                        <td><?php echo ucfirst( $data['type'] ); ?></td>
                        <td><?php echo $data['price'] . ' ' . $data['currency']; ?></td>
                        <td><?php echo $data['item_name']; ?></td>
                        <td><?php echo $data['date']; ?></td>
                        <td>
                            <a class="button" onclick="return confirm('Are you sure?');" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'order_accept', 'id' => $order->ID ), $base_url), 'wpuf_order_accept' ); ?>"><?php _e( 'Accept', 'wpuf' ); ?></a>
                            <a class="button" onclick="return confirm('Are you sure?');" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'order_reject', 'id' => $order->ID ), $base_url), 'wpuf_order_accept' ); ?>"><?php _e( 'Reject', 'wpuf' ); ?></a>
                        </td>

                    </tr>
                    <?php
                    $count++;
                }
                ?>
            <?php } else { ?>
                <tr>
                    <td colspan="11"><?php _e( 'Nothing Found', 'wpuf' ); ?></td>
                </tr>
            <?php } ?>

        </table>
    <?php } else { ?>

        <h3><?php _e( 'No pending orders found', 'wpuf' ); ?></h3>

    <?php } ?>
</div>
