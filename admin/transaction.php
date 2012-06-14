<?php

function wpuf_transaction() {
    global $wpdb;

    $date = date( 'd-m-Y H:i:s', time() );
    $month = date( 'm', time() );
    $year = date( 'Y', time() );

    //var_dump( $date, $month, $year);
    $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction" );
    $month_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE YEAR(`created`) = YEAR(NOW()) AND MONTH(`created`) = MONTH(NOW())" );
    $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_transaction ORDER BY `created` DESC LIMIT 0, 60", OBJECT );
    ?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2>WP User Frontend: Payments Received</h2>

        Total Income: <?php echo get_option( 'wpuf_sub_currency_sym' ) . $total_income; ?><br />
        This Month: <?php echo get_option( 'wpuf_sub_currency_sym' ) . $month_income; ?>

        <hr />

        <table class="widefat meta" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">User ID</th>
                    <th scope="col">Status</th>
                    <th scope="col">Cost</th>
                    <th scope="col">Post ID</th>
                    <th scope="col">Pack ID</th>
                    <th scope="col">Payer</th>
                    <th scope="col">Email</th>
                    <th scope="col">Type</th>
                    <th scope="col">Transaction ID</th>
                    <th scope="col">Created</th>
                </tr>
            </thead>
            <?php
            if ( $wpdb->num_rows > 0 ) {
                $count = 0;
                foreach ($fields as $row) {
                    //var_dump( $row );
                    ?>
                    <tr valign="top" <?php echo ( ($count % 2) == 0) ? 'class="alternate"' : ''; ?>>
                        <td><?php echo stripslashes( htmlspecialchars( $row->id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->user_id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->status ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->cost ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->post_id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->pack_id ) ); ?></td>
                        <td><?php echo $row->payer_first_name . ' ' . $row->payer_last_name; ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->payer_email ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->payment_type ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->transaction_id ) ); ?></td>
                        <td><?php echo stripslashes( htmlspecialchars( $row->created ) ); ?></td>

                    </tr>
                    <?php $count++;
                } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="5">Nothing Found</td>
                </tr>
            <?php } ?>

        </table>
    </div>
    <?php
}
