<?php
function wpuf_upgrade_2_9_0_option_changes() {
    if ( 'yes' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account' ) ) {
        wpuf_update_option( 'show_subscriptions', 'wpuf_my_account', 'on' );
    } elseif ( 'no' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account' ) ) {
        wpuf_update_option( 'show_subscriptions', 'wpuf_my_account', 'off' );
    }
    if ( 'yes' == wpuf_get_option( 'enable_invoices', 'wpuf_payment_invoices' ) ) {
        wpuf_update_option( 'enable_invoices', 'wpuf_payment_invoices', 'on' );
    } elseif ( 'no' == wpuf_get_option( 'enable_invoices', 'wpuf_payment_invoices' ) ) {
        wpuf_update_option( 'enable_invoices', 'wpuf_payment_invoices', 'off' );
    }
    if ( 'yes' == wpuf_get_option( 'show_invoices', 'wpuf_payment_invoices' ) ) {
        wpuf_update_option( 'show_invoices', 'wpuf_payment_invoices', 'on' );
    } elseif ( 'no' == wpuf_get_option( 'show_invoices', 'wpuf_payment_invoices' ) ) {
        wpuf_update_option( 'show_invoices', 'wpuf_payment_invoices', 'off' );
    }
}

function wpuf_upgrade_2_9_0_change_address_meta() {
	global $current_user;

    if ( metadata_exists( 'user', $current_user->ID, 'address_fields') ) {
        $address_fields = get_user_meta( $current_user->ID, 'address_fields', true );
        update_user_meta( $current_user->ID, 'wpuf_address_fields', $address_fields );
    }
}

function wpuf_upgrade_2_9_0_transaction_table_update() {
	global $wpdb;

	$wpdb->query("ALTER TABLE {$wpdb->prefix}wpuf_transaction ADD COLUMN `subtotal` varchar(255) NOT NULL DEFAULT '', ADD COLUMN `tax` varchar(255) NOT NULL DEFAULT ''");
}

wpuf_upgrade_2_9_0_option_changes();
wpuf_upgrade_2_9_0_change_address_meta();
wpuf_upgrade_2_9_0_transaction_table_update();