<?php
/**
 * SET0006 helper: Pro settings and the Feature_Lock gating reach WPUF REST
 * routes without a request URL (plain permalinks, internal dispatch), once per
 * request (task 1.22, B30). Pass `invalid` to simulate an inactive license.
 * Prints JSON.
 *
 * Usage: wp eval-file check-pro-rest-boot.php [invalid]
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// An inactive license makes Feature_Lock deactivate every module: put them back after.
$wpuf_modules = get_option( 'wpuf_pro_active_modules', [] );

if ( isset( $args[0] ) && 'invalid' === $args[0] ) {
    add_filter(
        'pre_option_wpuf_license',
        function () {
            return [
                'key'    => 'set6',
                'status' => 'deactivate',
            ];
        }
    );
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/wpuf_subscription' ) );
$wpuf_locked = (bool) has_filter( 'wpuf_settings_user_roles', '__return_empty_array' );

$wpuf_data = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) )->get_data();
rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) );
$wpuf_ids = wp_list_pluck( $wpuf_data['data']['sections'], 'id' );

update_option( 'wpuf_pro_active_modules', $wpuf_modules );

echo wp_json_encode(
    [
        'pro_section' => in_array( 'wpuf_content_restriction', $wpuf_ids, true ),
        'locked'      => $wpuf_locked,
        'sections'    => count( $wpuf_ids ) === count( array_unique( $wpuf_ids ) ),
    ]
);
