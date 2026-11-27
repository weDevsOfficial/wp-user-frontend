<?php
/**
 * SEC0005 helper (run with WP_ADMIN defined): the profile subscription update
 * assigns packs only to the user being saved, and only for users who may edit
 * that user. Creates and removes its own users and pack. Prints JSON.
 *
 * Usage: wp --exec='define("WP_ADMIN",true);' eval-file check-profile-subscription.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
$wpuf_a      = wp_insert_user( [ 'user_login' => 'sec5a_' . wp_rand(), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ] );
$wpuf_b      = wp_insert_user( [ 'user_login' => 'sec5b_' . wp_rand(), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ] );
$wpuf_pack   = wp_insert_post( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'SEC5 pack' ] );

foreach ( [ '_expiration_number' => 0, '_expiration_period' => 'day', '_billing_amount' => 0, '_recurring_pay' => 'no' ] as $wpuf_key => $wpuf_value ) {
    update_post_meta( $wpuf_pack, $wpuf_key, $wpuf_value );
}

$wpuf_pack_of = function ( $user_id ) {
    $pack = get_user_meta( $user_id, '_wpuf_subscription_pack', true );
    return is_array( $pack ) && isset( $pack['pack_id'] ) ? (int) $pack['pack_id'] : 0;
};

// Admin saves user A, with a tampered user_id pointing at user B.
wp_set_current_user( (int) reset( $wpuf_admins ) );
$_POST    = [ 'wpuf-subscription-nonce' => wp_create_nonce( 'update-profile_' . $wpuf_a ), 'pack_id' => (string) $wpuf_pack, 'user_id' => (string) $wpuf_b ];
$_REQUEST = $_POST;
do_action( 'edit_user_profile_update', $wpuf_a );
$wpuf_admin_result = [ $wpuf_pack_of( $wpuf_a ), $wpuf_pack_of( $wpuf_b ) ];

// Subscriber B tries to assign the pack to user A.
delete_user_meta( $wpuf_a, '_wpuf_subscription_pack' );
wp_set_current_user( $wpuf_b );
$_POST    = [ 'wpuf-subscription-nonce' => wp_create_nonce( 'update-profile_' . $wpuf_a ), 'pack_id' => (string) $wpuf_pack ];
$_REQUEST = $_POST;
do_action( 'edit_user_profile_update', $wpuf_a );
$wpuf_subscriber_result = $wpuf_pack_of( $wpuf_a );

wp_set_current_user( (int) reset( $wpuf_admins ) );
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user( $wpuf_a );
wp_delete_user( $wpuf_b );
wp_delete_post( $wpuf_pack, true );

echo wp_json_encode( [ 'admin' => $wpuf_admin_result, 'subscriber' => $wpuf_subscriber_result, 'pack' => $wpuf_pack ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
