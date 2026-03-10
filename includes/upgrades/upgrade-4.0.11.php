<?php

function wpuf_upgrade_4_0_11_migration() {
    global $wpdb;

    // get all the post meta from postmeta table where meta_key is 'wpuf_form_settings'
    $form_settings = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key = 'wpuf_form_settings'" );

    if ( empty( $form_settings ) ) {
        return;
    }

    $search  = [
        '%username%',
        '%user_email%',
        '%display_name%',
        '%user_status%',
        '%pending_users%',
        '%approved_users%',
        '%denied_users%',
        '%sitename%',
        '%post_title%',
        '%post_content%',
        '%post_excerpt%',
        '%tags%',
        '%category%',
        '%author%',
        '%author_email%',
        '%author_bio%',
        '%siteurl%',
        '%permalink%',
        '%editlink%',
        '%link%',
    ];
    $replace = [
        '{username}',
        '{user_email}',
        '{display_name}',
        '{user_status}',
        '{pending_users}',
        '{approved_users}',
        '{denied_users}',
        '{sitename}',
        '{post_title}',
        '{post_content}',
        '{post_excerpt}',
        '{tags}',
        '{category}',
        '{author}',
        '{author_email}',
        '{author_bio}',
        '{siteurl}',
        '{permalink}',
        '{editlink}',
        '{link}',
    ];

    foreach ( $form_settings as $form_setting ) {
        $unserilized = maybe_unserialize( $form_setting->meta_value );

        if ( isset( $unserilized['notification']['new_body'] ) ) {
            $new_body         = $unserilized['notification']['new_body'];
            $updated_new_body = str_replace( $search, $replace, $new_body );

            $unserilized['notification']['new_body'] = $updated_new_body;
        }

        if ( isset( $unserilized['notification']['edit_body'] ) ) {
            $edit_body         = $unserilized['notification']['edit_body'];
            $updated_edit_body = str_replace( $search, $replace, $edit_body );

            $unserilized['notification']['edit_body'] = $updated_edit_body;
        }

        update_post_meta( $form_setting->post_id, 'wpuf_form_settings', $unserilized );
    }
}

wpuf_upgrade_4_0_11_migration();
