<?php
/**
 * SET0004 helper: the first save of a settings section stores missing field
 * defaults like the legacy screen's std, and field `desc` HTML reaches the
 * React screen filtered by wp_kses_post (task 1.15). Restores wpuf_general.
 * Prints JSON.
 *
 * Usage: wp eval-file check-settings-defaults.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_backup = get_option( 'wpuf_general', null );
$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

add_filter(
    'wpuf_settings_fields',
    function ( $fields ) {
        $fields['wpuf_general'][] = [
            'name'    => 'wpuf_set4_default',
            'label'   => 'SET4 default',
            'desc'    => 'See <a href="https://example.com">docs</a><script>alert(1)</script>',
            'type'    => 'text',
            'default' => 'set4-std',
        ];
        $fields['wpuf_general'][] = [
            'name'  => 'wpuf_set4_edited',
            'label' => 'SET4 edited',
            'type'  => 'text',
        ];
        return $fields;
    }
);

$wpuf_stored = (array) get_option( 'wpuf_general', [] );
unset( $wpuf_stored['wpuf_set4_default'], $wpuf_stored['wpuf_set4_edited'] );
update_option( 'wpuf_general', $wpuf_stored );

$wpuf_get  = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) )->get_data();
$wpuf_desc = '';
foreach ( $wpuf_get['data']['fields']['wpuf_general'] as $wpuf_field ) {
    if ( isset( $wpuf_field['name'] ) && 'wpuf_set4_default' === $wpuf_field['name'] ) {
        $wpuf_desc = $wpuf_field['desc'];
    }
}

$wpuf_post = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
$wpuf_post->set_param( 'settings', [ 'wpuf_general' => [ 'wpuf_set4_edited' => 'x' ] ] );
rest_do_request( $wpuf_post );
$wpuf_after = (array) get_option( 'wpuf_general', [] );

echo wp_json_encode(
    [
        'default_stored' => isset( $wpuf_after['wpuf_set4_default'] ) ? $wpuf_after['wpuf_set4_default'] : null,
        'edited'         => isset( $wpuf_after['wpuf_set4_edited'] ) ? $wpuf_after['wpuf_set4_edited'] : null,
        'desc'           => $wpuf_desc,
    ]
);

if ( null === $wpuf_backup ) {
    delete_option( 'wpuf_general' );
} else {
    update_option( 'wpuf_general', $wpuf_backup );
}
