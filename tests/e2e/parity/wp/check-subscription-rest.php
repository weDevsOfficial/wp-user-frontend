<?php
/**
 * SEC0004 helper: subscription REST only acts on subscription packs, single-row
 * edits only change an allowed field/value, term ids are filtered. Cleans up.
 *
 * Usage: wp eval-file check-subscription-rest.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

$wpuf_pack  = wp_insert_post( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'SEC4 pack' ] );
$wpuf_other = wp_insert_post( [ 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'SEC4 post' ] );

$wpuf_call = function ( $method, $route, $params = [] ) {
    $request = new WP_REST_Request( $method, '/wpuf/v1/wpuf_subscription' . $route );
    foreach ( $params as $key => $value ) {
        $request->set_param( $key, $value );
    }
    $response = rest_do_request( $request );
    return [ $response->get_status(), $response->get_data() ];
};

$wpuf_out = [];

list( , $wpuf_data )         = $wpuf_call( 'DELETE', '/' . $wpuf_other );
$wpuf_out['delete_other']     = [ ! empty( $wpuf_data['success'] ), null !== get_post( $wpuf_other ) ];

list( , $wpuf_data )         = $wpuf_call( 'PUT', '/' . $wpuf_other, [ 'subscription' => [ 'ID' => $wpuf_other, 'edit_single_row' => true, 'edit_row_name' => 'post_status', 'edit_row_value' => 'publish' ] ] );
$wpuf_out['edit_other']       = [ ! empty( $wpuf_data['success'] ), get_post_status( $wpuf_other ) ];

list( , $wpuf_data )         = $wpuf_call( 'PUT', '/' . $wpuf_pack, [ 'subscription' => [ 'ID' => $wpuf_pack, 'edit_single_row' => true, 'edit_row_name' => 'post_author', 'edit_row_value' => '999' ] ] );
$wpuf_out['edit_author']      = [ ! empty( $wpuf_data['success'] ), (int) get_post_field( 'post_author', $wpuf_pack ) ];

list( , $wpuf_data )         = $wpuf_call( 'PUT', '/' . $wpuf_pack, [ 'subscription' => [ 'ID' => $wpuf_pack, 'edit_single_row' => true, 'edit_row_name' => 'post_status', 'edit_row_value' => 'draft' ] ] );
$wpuf_out['edit_status']      = [ ! empty( $wpuf_data['success'] ), get_post_status( $wpuf_pack ) ];

list( , $wpuf_data )         = $wpuf_call( 'POST', '', [ 'subscription' => [ 'ID' => $wpuf_other, 'post_title' => 'SEC4 hijack', 'meta_value' => [] ] ] );
$wpuf_out['update_other']     = [ ! empty( $wpuf_data['success'] ), get_the_title( $wpuf_other ) ];

list( , $wpuf_data )         = $wpuf_call( 'POST', '', [ 'subscription' => [ 'ID' => $wpuf_pack, 'post_title' => 'SEC4 pack', 'meta_value' => [ '_sub_view_allowed_term_ids' => [ '12', 7, '3 OR 1=1', '<b>', -4 ] ] ] ] );
$wpuf_out['term_ids']         = [ ! empty( $wpuf_data['success'] ), get_post_meta( $wpuf_pack, '_sub_view_allowed_term_ids', true ) ];

wp_delete_post( $wpuf_pack, true );
wp_delete_post( $wpuf_other, true );

echo wp_json_encode( $wpuf_out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
