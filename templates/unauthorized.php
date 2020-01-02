<?php

if ( empty( $message ) ) {
    $msg = '<div class="wpuf-message">' . sprintf( __( 'This page is restricted. Please %s to view this page.', 'wp-user-frontend' ), wp_loginout( get_permalink(), false ) ) . '</div>';
    echo esc_html( apply_filters( 'wpuf_account_unauthorized', $msg ) );
} else {
    echo esc_html( $message );
}
