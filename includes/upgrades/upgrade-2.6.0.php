<?php

/**
 * Move 'Custom fields in post' option from global to individual field settings
 *
 * @return void
 */
function wpuf_upgrade_2_6_field_options() {
    
    $show_custom  = wpuf_get_option( 'cf_show_front', 'wpuf_general', 'no' );

    $input_fields = get_posts( array(
        'post_type'   => array( 'wpuf_input' ),
        'numberposts' => '-1'
    ) );

    if ( !$input_fields ) {
        return;
    }

    foreach ($input_fields as $key => $field) {
        $settings = maybe_unserialize($field->post_content);

        if ( isset($settings['is_meta'] ) && $settings['is_meta'] == 'yes' ) {
            $settings['show_in_post'] = $show_custom;
        }

        wp_update_post( array(
            'ID' => $field->ID,
            'post_content' => maybe_serialize( $settings )
        ) );
    }
    
}

wpuf_upgrade_2_6_field_options();