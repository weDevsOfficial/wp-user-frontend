<?php
if ( empty( $message ) ) {
    $msg = '<div class="wpuf-message">' . sprintf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( get_permalink(), false ) ) . '</div>';
    echo apply_filters( 'wpuf_account_unauthorized', $msg );
} else {
    echo $message;
}
