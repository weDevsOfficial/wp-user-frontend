<?php
/**
 * PAR0008 helper: the forms list endpoint only lists form post types, sorts
 * newest first and bounds paging; a subscription save fires
 * wpuf_before_update_subscription_pack once, with the saved id (task 1.13).
 * Cleans up after itself. Prints JSON.
 *
 * Usage: wp eval-file check-list-and-pack-hooks.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

$wpuf_list = function ( $params ) {
    $request = new WP_REST_Request( 'GET', '/wpuf/v1/wpuf_form' );
    foreach ( $params as $key => $value ) {
        $request->set_param( $key, $value );
    }
    $response = rest_do_request( $request );
    return [ $response->get_status(), $response->get_data() ];
};

$wpuf_forms = [
    wp_insert_post( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'PAR0008 a' ] ),
    wp_insert_post( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'PAR0008 b' ] ),
];

list( $wpuf_status_post ) = $wpuf_list( [ 'post_type' => 'post' ] );
list( $wpuf_status_profile ) = $wpuf_list( [ 'post_type' => 'wpuf_profile' ] );
list( , $wpuf_data ) = $wpuf_list( [ 'post_type' => 'wpuf_forms', 'per_page' => 2, 'status' => 'bogus' ] );
$wpuf_first_ids = wp_list_pluck( $wpuf_data['result'], 'ID' );
list( , $wpuf_big ) = $wpuf_list( [ 'post_type' => 'wpuf_forms', 'per_page' => 5000 ] );

$wpuf_calls = [];
add_action(
    'wpuf_before_update_subscription_pack',
    function ( $id ) use ( &$wpuf_calls ) {
        $wpuf_calls[] = $id;
    }
);

$wpuf_request = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
$wpuf_request->set_param( 'subscription', [ 'post_title' => 'PAR0008 pack', 'post_status' => 'draft', 'meta_value' => [] ] );
$wpuf_saved = rest_do_request( $wpuf_request )->get_data();
$wpuf_pack  = isset( $wpuf_saved['subscription']['ID'] ) ? (int) $wpuf_saved['subscription']['ID'] : (int) end( $wpuf_calls );

echo wp_json_encode(
    [
        'post_rejected'   => $wpuf_status_post,
        'profile_ok'      => $wpuf_status_profile,
        'newest_first'    => array_map( 'intval', $wpuf_first_ids ) === [ $wpuf_forms[1], $wpuf_forms[0] ],
        'per_page_capped' => count( $wpuf_big['result'] ) <= 100,
        'hook_calls'      => count( $wpuf_calls ),
        'hook_id_saved'   => ! empty( $wpuf_calls ) && $wpuf_calls[0] === $wpuf_pack && $wpuf_pack > 0,
    ]
);

foreach ( array_merge( $wpuf_forms, [ $wpuf_pack ] ) as $wpuf_id ) {
    wp_delete_post( $wpuf_id, true );
}
