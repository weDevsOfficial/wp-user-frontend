<div class="wrap">
    <h2><?php _e( 'Transactions', 'wpuf' ); ?></h2>

    <?php
        global $wpdb;
        $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE status = 'completed'" );
        $month_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE YEAR(`created`) = YEAR(NOW()) AND MONTH(`created`) = MONTH(NOW()) AND status = 'completed'" );
    ?>

    <ul>
        <li>
            <strong><?php _e( 'Total Income:', 'wpuf' ); ?></strong> <?php echo wpuf_format_price( $total_income ); ?><br />
        </li>
        <li>
            <strong><?php _e( 'This Month:', 'wpuf' ); ?></strong> <?php echo wpuf_format_price( $month_income ); ?>
        </li>
    </ul>

    <form method="post">
        <input type="hidden" name="page" value="transactions">
        <?php
            $this->transactions_list_table_obj->prepare_items();
            $this->transactions_list_table_obj->views();
            $this->transactions_list_table_obj->display();
        ?>
    </form>
</div>
