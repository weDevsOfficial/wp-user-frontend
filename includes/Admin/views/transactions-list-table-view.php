<div class="wrap">
    <h2><?php esc_html_e( 'Transactions', 'wp-user-frontend' ); ?></h2>

    <?php
        global $wpdb;
        $total_income = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}wpuf_transaction WHERE status = 'completed'" );
        $total_tax = $wpdb->get_var( "SELECT SUM(tax) FROM {$wpdb->prefix}wpuf_transaction WHERE status = 'completed'" );
    ?>

    <form method="post">
        <input type="hidden" name="page" value="transactions">
        <?php
            wpuf()->admin->transaction_list_table->prepare_items();
            wpuf()->admin->transaction_list_table->views();
            wpuf()->admin->transaction_list_table->display();
        ?>
    </form>
</div>
