<?php
if ( 'off' == wpuf_get_option( 'show_billing_address', 'wpuf_my_account', 'on' ) ) {
	echo '<div class="wpuf-message">' . sprintf( __( "Billing address is not enabled. Please contact admin.", 'wpuf' ) ) . '</div>';
	exit;
} else {
	echo '<div class="wpuf-message">' . sprintf( __( "Billing address is not set. Please update your billing address.", 'wpuf' ) ) . '</div>';
	exit;
}