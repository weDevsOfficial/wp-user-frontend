<?php
/**
 * Creates a WPUF form from a dump-form.php JSON file, writing every stored value
 * byte for byte (serialized exactly as WordPress serializes it), and prints the
 * new form id. Replaces any earlier form with the same title and post type.
 * An optional second argument overrides the title, so tests that run at the
 * same time do not replace each other's forms.
 *
 * Usage: wp eval-file seed-form.php <dump.json> [title]
 *
 * Test helper for the parity suite (tests/e2e/parity). Never loaded by the plugin.
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$wpuf_file = isset( $args[0] ) ? $args[0] : '';

if ( ! $wpuf_file || ! is_readable( $wpuf_file ) ) {
    WP_CLI::error( 'Dump file not readable: ' . $wpuf_file );
}

// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
$wpuf_dump = json_decode( file_get_contents( $wpuf_file ), true );

if ( ! empty( $args[1] ) ) {
    $wpuf_dump['post_title'] = $args[1];
}

foreach ( get_posts( [ 'post_type' => $wpuf_dump['post_type'], 'title' => $wpuf_dump['post_title'], 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] ) as $wpuf_old ) {
    foreach ( get_children( [ 'post_parent' => $wpuf_old, 'post_type' => 'wpuf_input', 'fields' => 'ids' ] ) as $wpuf_old_child ) {
        wp_delete_post( $wpuf_old_child, true );
    }
    wp_delete_post( $wpuf_old, true );
}

/**
 * Writes meta exactly (raw insert, no slashing or filters).
 *
 * @param int   $post_id Post id.
 * @param array $meta    Meta.
 */
function wpuf_parity_write_meta( $post_id, $meta ) {
    global $wpdb;

    foreach ( $meta as $key => $value ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert(
            $wpdb->postmeta,
            [
                'post_id'    => $post_id,
                'meta_key'   => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'meta_value' => maybe_serialize( $value ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            ]
        );
    }

    wp_cache_delete( $post_id, 'post_meta' );
}

$wpuf_form_id = wp_insert_post(
    [
        'post_type'   => $wpuf_dump['post_type'],
        'post_status' => $wpuf_dump['post_status'],
        'post_title'  => $wpuf_dump['post_title'],
    ],
    true
);

if ( is_wp_error( $wpuf_form_id ) ) {
    WP_CLI::error( $wpuf_form_id->get_error_message() );
}

wpuf_parity_write_meta( $wpuf_form_id, $wpuf_dump['meta'] );

foreach ( $wpuf_dump['fields'] as $wpuf_field ) {
    $wpuf_child = wp_insert_post(
        [
            'post_type'   => 'wpuf_input',
            'post_status' => $wpuf_field['post_status'],
            'post_parent' => $wpuf_form_id,
            'menu_order'  => $wpuf_field['menu_order'],
        ],
        true
    );

    $wpuf_content = $wpuf_field['post_content'];

    // Restore the field's own row id (see dump-form.php).
    if ( is_array( $wpuf_content ) && isset( $wpuf_content['id'] ) && in_array( $wpuf_content['id'], [ '@self:int', '@self:string' ], true ) ) {
        $wpuf_content['id'] = '@self:int' === $wpuf_content['id'] ? (int) $wpuf_child : (string) $wpuf_child;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->update( $wpdb->posts, [ 'post_content' => maybe_serialize( $wpuf_content ) ], [ 'ID' => $wpuf_child ] );
    clean_post_cache( $wpuf_child );
    wpuf_parity_write_meta( $wpuf_child, $wpuf_field['meta'] );
}

echo (int) $wpuf_form_id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
