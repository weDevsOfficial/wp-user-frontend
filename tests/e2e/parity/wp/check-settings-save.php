<?php
/**
 * SET0001 helper: a React settings save stores values in the shape the legacy
 * screen stored them (task 1.4, B3 to B7) and leaves untouched data alone.
 * Restores the touched options afterwards. Prints JSON.
 *
 * Usage: wp eval-file check-settings-save.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_names  = [ 'wpuf_general', 'wpuf_mails', 'wpuf_content_restriction', 'wpuf_tax_rates', 'wpuf_base_country_state', 'wpuf_ai', 'wpuf_profile' ];
$wpuf_backup = [];
foreach ( $wpuf_names as $wpuf_name ) {
    $wpuf_backup[ $wpuf_name ] = get_option( $wpuf_name, null );
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

/**
 * Post one React settings save.
 *
 * @param array $settings Section values.
 * @param array $extra    Own-option payload.
 */
$wpuf_save = function ( $settings, $extra = [] ) {
    $request = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
    $request->set_param( 'settings', $settings );
    $request->set_param( 'extra', $extra );
    return rest_do_request( $request )->get_data();
};

update_option( 'wpuf_general', [ 'show_admin_bar' => [ 'administrator' ], 'custom_css' => 'old', 'untouched' => 'keep\\me' ] );
update_option( 'wpuf_tax_rates', [ 3 => [ 'country' => 'US', 'state' => 'CA', 'rate' => '7.25' ] ] );
update_option( 'wpuf_base_country_state', [ 'country' => 'US', 'state' => 'CA' ] );
$wpuf_result = [];

$wpuf_save( [ 'wpuf_content_restriction' => [ 'filter_contents' => [ 'post_title' ] ] ] );
$wpuf_result['multicheck'] = get_option( 'wpuf_content_restriction' )['filter_contents'];

$wpuf_save( [ 'wpuf_content_restriction' => [ 'filter_contents' => [] ] ] );
$wpuf_result['multicheck_empty'] = get_option( 'wpuf_content_restriction' )['filter_contents'];

$wpuf_save(
    [
        'wpuf_general' => [
            'show_admin_bar' => [],
            'custom_css'     => 'ul > li { color: red; }',
        ],
        'wpuf_mails'   => [ 'footer_text' => '<p>Sent by <a href="https://example.com">C:\\Shop</a></p>' ],
    ]
);
$wpuf_general                    = get_option( 'wpuf_general' );
$wpuf_result['multiselect_empty'] = array_key_exists( 'show_admin_bar', $wpuf_general ) ? $wpuf_general['show_admin_bar'] : 'absent';
$wpuf_result['custom_css']        = $wpuf_general['custom_css'];
$wpuf_result['untouched']         = $wpuf_general['untouched'];
$wpuf_result['footer_text']       = get_option( 'wpuf_mails' )['footer_text'];

// The tax block comes back as loaded, apart from the base: rates keep their stored form.
$wpuf_save(
    [],
    [
        'tax' => [
            'base'  => [ 'country' => 'US', 'state' => 'NY' ],
            'rates' => [ [ 'country' => 'US', 'state' => 'CA', 'rate' => '7.25' ] ],
        ],
    ]
);
$wpuf_result['tax_base']        = get_option( 'wpuf_base_country_state' );
$wpuf_result['tax_rates_kept'] = get_option( 'wpuf_tax_rates' );

$wpuf_save( [], [ 'tax' => [ 'rates' => [ [ 'country' => 'GB', 'state' => '', 'rate' => '20' ], [ 'country' => 'DE', 'state' => '', 'rate' => '150' ] ] ] ] );
$wpuf_result['tax_rates_new'] = get_option( 'wpuf_tax_rates' );

foreach ( $wpuf_backup as $wpuf_name => $wpuf_value ) {
    if ( null === $wpuf_value ) {
        delete_option( $wpuf_name );
    } else {
        update_option( $wpuf_name, $wpuf_value );
    }
}

echo wp_json_encode( $wpuf_result );
