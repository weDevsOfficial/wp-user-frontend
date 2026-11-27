<?php
/**
 * Saves or restores the WPUF state of a test site around a test that walks a
 * wizard: every `wpuf%` option and the ids of pages, forms and menu items.
 *
 * Usage: WPUF_STATE_MODE=save|restore WPUF_STATE_FILE=/path wp eval-file site-state.php
 *
 * @package WP_User_Frontend
 */

global $wpdb;

$wpuf_file  = getenv( 'WPUF_STATE_FILE' );
$wpuf_types = [ 'page', 'wpuf_forms', 'wpuf_profile', 'wpuf_input', 'nav_menu_item', 'wpuf_subscription' ];
$wpuf_in    = "'" . implode( "','", $wpuf_types ) . "'";

// phpcs:disable WordPress.DB
$wpuf_options = [];
foreach ( $wpdb->get_results( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'wpuf%' OR option_name IN ( 'n8n' )" ) as $wpuf_row ) {
    $wpuf_options[ $wpuf_row->option_name ] = $wpuf_row->option_value;
}
$wpuf_posts = array_map( 'intval', $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( {$wpuf_in} )" ) );
// phpcs:enable

if ( 'save' === getenv( 'WPUF_STATE_MODE' ) ) {
    file_put_contents( $wpuf_file, wp_json_encode( [ 'options' => $wpuf_options, 'posts' => $wpuf_posts ] ) ); // phpcs:ignore
    echo 'saved';

    return;
}

$wpuf_saved = json_decode( file_get_contents( $wpuf_file ), true ); // phpcs:ignore

foreach ( array_diff( $wpuf_posts, $wpuf_saved['posts'] ) as $wpuf_id ) {
    wp_delete_post( $wpuf_id, true );
}

foreach ( $wpuf_options as $wpuf_name => $wpuf_value ) {
    if ( ! array_key_exists( $wpuf_name, $wpuf_saved['options'] ) ) {
        delete_option( $wpuf_name );
    }
}

foreach ( $wpuf_saved['options'] as $wpuf_name => $wpuf_value ) {
    if ( ! isset( $wpuf_options[ $wpuf_name ] ) || $wpuf_options[ $wpuf_name ] !== $wpuf_value ) {
        update_option( $wpuf_name, maybe_unserialize( $wpuf_value ) );
    }
}

wp_cache_flush();
echo 'restored';
