<?php
/**
 * PAR0006 helper (run with Pro skipped): a builder save without Pro keeps the
 * custom taxonomy fields the builder hid, and still deletes a field the user
 * removed (task 1.11). Prints JSON and deletes the test form.
 *
 * Usage: wp eval-file check-hidden-taxonomy.php --skip-plugins=wpuf-pro
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_form = wp_insert_post( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'PAR0006' ] );
$wpuf_ids  = [];

foreach (
    [
        [ 'template' => 'post_title', 'input_type' => 'text', 'name' => 'post_title', 'label' => 'Title' ],
        [ 'template' => 'taxonomy', 'input_type' => 'taxonomy', 'name' => 'wpuf_par0006_genre', 'label' => 'Genre' ],
        [ 'template' => 'text_field', 'input_type' => 'text', 'name' => 'removed', 'label' => 'Removed' ],
    ] as $wpuf_order => $wpuf_field
) {
    $wpuf_ids[] = wpuf_insert_form_field( $wpuf_form, $wpuf_field, null, $wpuf_order );
}

// What the builder sends without Pro: the taxonomy field was never loaded, the text field was removed.
\WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder::save_form(
    [
        'form_id'           => $wpuf_form,
        'post_title'        => 'PAR0006',
        'form_fields'       => [ [ 'id' => $wpuf_ids[0], 'template' => 'post_title', 'input_type' => 'text', 'name' => 'post_title', 'label' => 'Title' ] ],
        'form_settings'     => [],
        'form_settings_key' => 'wpuf_form_settings',
        'notifications'     => [],
    ]
);

$wpuf_result = [
    'pro_active' => wpuf_is_pro_active(),
    'kept'       => array_map( 'boolval', array_map( 'get_post', $wpuf_ids ) ),
];

wp_delete_post( $wpuf_form, true );
foreach ( $wpuf_ids as $wpuf_id ) {
    wp_delete_post( $wpuf_id, true );
}

echo wp_json_encode( $wpuf_result );
