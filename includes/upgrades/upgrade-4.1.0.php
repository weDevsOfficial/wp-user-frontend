<?php

function wpuf_upgrade_4_1_0_migration() {
    global $wpdb;

    // get all the post meta from postmeta table where meta_key is 'wpuf_form_settings'
    $form_settings = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = 'wpuf_form_settings'" );


    if ( empty( $form_settings ) ) {
        return;
    }

    foreach ( $form_settings as $form_setting ) {
        $unserilized = maybe_unserialize( $form_setting->meta_value );

        if ( empty( $unserilized['guest_post'] ) && empty( $unserilized['role_base'] ) ) {
            continue;
        }

        if ( isset( $unserilized['guest_post'] ) && 'true' === $unserilized['guest_post'] ) {
            $unserilized['post_permission'] = 'guest_post';
        }

        if ( isset( $unserilized['role_base'] ) && 'true' === $unserilized['role_base'] ) {
            $unserilized['post_permission'] = 'role_base';
        }

        update_post_meta( $form_setting->post_id, 'wpuf_form_settings', $unserilized );
    }
}

wpuf_upgrade_4_1_0_migration();
