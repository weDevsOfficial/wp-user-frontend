<?php
/**
 * Prints the settings schema (sections, fields, kinds, option labels) as JSON.
 * Usage: wp eval-file dump-settings-schema.php --exec='define("WP_ADMIN",true);'
 *
 * @package WP_User_Frontend
 */

$wpuf_out = [];

foreach ( wpuf_settings_fields() as $wpuf_section => $wpuf_fields ) {
    foreach ( (array) $wpuf_fields as $wpuf_field ) {
        if ( empty( $wpuf_field['name'] ) ) {
            continue;
        }

        $wpuf_kind = isset( $wpuf_field['type'] ) ? $wpuf_field['type'] : 'text';

        if ( isset( $wpuf_field['callback'] ) ) {
            $wpuf_kind = 'cb:' . ( is_string( $wpuf_field['callback'] ) ? $wpuf_field['callback'] : 'array' );
        }

        $wpuf_options = [];

        if ( isset( $wpuf_field['options'] ) && is_array( $wpuf_field['options'] ) ) {
            foreach ( $wpuf_field['options'] as $wpuf_key => $wpuf_option ) {
                $wpuf_label = is_array( $wpuf_option )
                    ? ( isset( $wpuf_option['label'] ) ? $wpuf_option['label'] : ( isset( $wpuf_option['name'] ) ? $wpuf_option['name'] : $wpuf_key ) )
                    : $wpuf_option;

                $wpuf_options[] = [
                    'key'   => (string) $wpuf_key,
                    'label' => trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( (string) $wpuf_label ), ENT_QUOTES, 'UTF-8' ) ) ),
                ];
            }
        }

        $wpuf_out[] = [
            'section' => $wpuf_section,
            'name'    => $wpuf_field['name'],
            'kind'    => $wpuf_kind,
            'label'   => trim( wp_strip_all_tags( isset( $wpuf_field['label'] ) ? (string) $wpuf_field['label'] : '' ) ),
            'options' => $wpuf_options,
            'pro'     => ! empty( $wpuf_field['is_pro_preview'] ),
        ];
    }
}

echo wp_json_encode( $wpuf_out );
