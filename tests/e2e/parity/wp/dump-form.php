<?php
/**
 * Dumps a WPUF form (post or registration) as JSON with every stored value and
 * its PHP type preserved, IDs replaced by positions, so two sites can be diffed.
 *
 * Usage: wp eval-file dump-form.php <form_id>
 *
 * Test helper for the parity suite (tests/e2e/parity). Never loaded by the plugin.
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_form_id = isset( $args[0] ) ? absint( $args[0] ) : 0;
$wpuf_form    = get_post( $wpuf_form_id );

if ( ! $wpuf_form || ! in_array( $wpuf_form->post_type, [ 'wpuf_forms', 'wpuf_profile' ], true ) ) {
    WP_CLI::error( 'Not a WPUF form: ' . $wpuf_form_id );
}

$wpuf_skip_meta = [ '_edit_lock', '_edit_last', '_wp_old_slug' ];

/**
 * Returns all meta of a post, unserialized, sorted by key.
 *
 * @param int   $post_id Post id.
 * @param array $skip    Keys to skip.
 *
 * @return array
 */
function wpuf_parity_meta( $post_id, $skip ) {
    $out = [];

    foreach ( get_post_meta( $post_id ) as $key => $values ) {
        if ( in_array( $key, $skip, true ) ) {
            continue;
        }

        $values      = array_map( 'maybe_unserialize', $values );
        $out[ $key ] = 1 === count( $values ) ? $values[0] : $values;
    }

    ksort( $out );

    return $out;
}

$wpuf_children = get_posts(
    [
        'post_type'   => 'wpuf_input',
        'post_parent' => $wpuf_form_id,
        'post_status' => 'any',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
    ]
);

$wpuf_fields = [];

foreach ( $wpuf_children as $wpuf_child ) {
    $wpuf_content = maybe_unserialize( $wpuf_child->post_content );

    // A field stores its own row id; replace it so two sites compare equal.
    if ( is_array( $wpuf_content ) && isset( $wpuf_content['id'] ) && (int) $wpuf_content['id'] === (int) $wpuf_child->ID ) {
        $wpuf_content['id'] = is_int( $wpuf_content['id'] ) ? '@self:int' : '@self:string';
    }

    $wpuf_fields[] = [
        'menu_order'   => (int) $wpuf_child->menu_order,
        'post_status'  => $wpuf_child->post_status,
        'post_content' => $wpuf_content,
        'meta'         => wpuf_parity_meta( $wpuf_child->ID, $wpuf_skip_meta ),
    ];
}

$wpuf_dump = [
    'post_type'   => $wpuf_form->post_type,
    'post_status' => $wpuf_form->post_status,
    'post_title'  => $wpuf_form->post_title,
    'meta'        => wpuf_parity_meta( $wpuf_form_id, $wpuf_skip_meta ),
    'fields'      => $wpuf_fields,
];

echo wp_json_encode( $wpuf_dump, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
