<?php
/**
 * Plugin Name: WPUF parity fixture: third-party settings
 * Description: Test-only (SET0012). Adds a settings field rendered by its own PHP callback and a section hook that prints a form control.
 */

function wpuf_parity_tp_field_render( $args ) {
    echo '<input type="text" name="wpuf_general[tp_custom]" value="">';
}

add_filter(
    'wpuf_settings_fields',
    function ( $fields ) {
        $fields['wpuf_general'][] = [
            'name'     => 'tp_custom',
            'label'    => 'Third Party Custom',
            'callback' => 'wpuf_parity_tp_field_render',
        ];

        return $fields;
    }
);

add_action(
    'wsa_form_bottom_wpuf_dashboard',
    function () {
        echo '<p>Third party box <input type="text" name="tp_box" value=""></p>';
    }
);
