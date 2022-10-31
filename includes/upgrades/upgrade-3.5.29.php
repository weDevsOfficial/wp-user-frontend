<?php

function wpuf_upgrade_3_5_29_migration() {
    $args = [
        'post_type' => 'wpuf_input',
    ];

    $input_fields = get_posts( $args );

    if ( empty( $input_fields ) ) {
        return;
    }

    foreach ( $input_fields as $field ) {
        if ( empty( $field->post_content ) ) {
            continue;
        }

        $content = maybe_unserialize( $field->post_content );

        if ( ! empty( $content['input_type'] ) && 'textarea' === $content['input_type'] ) {
            $content['text_editor_control'] = [];

            $field->post_content = $content;

            // wpuf_update_post( $field );
        }
    }
}

wpuf_upgrade_3_5_29_migration();
