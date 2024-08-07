<?php

function wpuf_upgrade_4_0_8_migration() {
    global $wpdb;

    $metas = $wpdb->get_results(
        $wpdb->prepare( "SELECT * FROM $wpdb->postmeta where meta_key = %s", '_post_expiration_time' )
    );

    $success = [];
    $failed = [];

    foreach ( $metas as $meta ) {
        if ( empty( $meta->meta_value ) ) {
            continue;
        }

        $regex = '/^(\d+)\s+subscription\.php([^\s]+)$/i';

        preg_match( $regex, $meta->meta_value, $matches );
        if ( ! empty( $matches ) ) {
            $number = ! empty( $matches[1] ) ? $matches[1] : -1;
            $period = ! empty( $matches[2] ) ? $matches[2] : 'forever';

            update_post_meta( $meta->post_id, '_post_expiration_number', $number );
            update_post_meta( $meta->post_id, '_post_expiration_period', $period );
        } else {
            $failed[] = $meta->post_id;
        }
    }

    if ( ! empty( $failed ) ) {
        update_option( 'wpuf_failed_post_expiration_time', $failed );
    }
}

wpuf_upgrade_4_0_8_migration();
