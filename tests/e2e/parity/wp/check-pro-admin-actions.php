<?php
/**
 * SEC0006 helper (run with WP_ADMIN defined, Pro active): the legacy tax save
 * and the registration create-from-template action need an admin, not just a
 * nonce. Restores what it changes. Prints JSON.
 *
 * Usage: wp --exec='define("WP_ADMIN",true);' eval-file check-pro-admin-actions.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'wpuf_pro' ) ) {
    echo wp_json_encode( [ 'skipped' => 'pro inactive' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$wpuf_rates_backup = get_option( 'wpuf_tax_rates' );
$wpuf_admins       = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
$wpuf_admin        = (int) reset( $wpuf_admins );
$wpuf_sub          = wp_insert_user( [ 'user_login' => 'sec6_' . wp_rand(), 'user_pass' => wp_generate_password(), 'role' => 'subscriber' ] );
$wpuf_out          = [];

// Tax save.
update_option( 'wpuf_tax_rates', [ [ 'country' => 'US', 'rate' => '5' ] ] );
foreach ( [ 'subscriber' => $wpuf_sub, 'admin' => $wpuf_admin ] as $wpuf_role => $wpuf_user ) {
    wp_set_current_user( $wpuf_user );
    $_REQUEST = [ 'option_page' => 'wpuf_payment_tax', '_wpnonce' => wp_create_nonce( 'wpuf_payment_tax-options' ), 'wpuf_tax_rates' => [ [ 'country' => 'GB', 'rate' => '20' ] ] ];
    wpuf_pro()->tax->save_legacy_tax_options();
    $wpuf_out[ 'tax_' . $wpuf_role ] = get_option( 'wpuf_tax_rates' )[0]['country'];
    update_option( 'wpuf_tax_rates', [ [ 'country' => 'US', 'rate' => '5' ] ] );
}

// Create registration form from template (stop at wp_die / redirect).
add_filter( 'wp_die_handler', function () { return function () { throw new Exception( 'died' ); }; } );
add_filter( 'wp_redirect', function () { throw new Exception( 'redirected' ); } );
$wpuf_handler = new \WeDevs\Wpuf\Pro\Admin\Forms\Profile_Form_Template();
$wpuf_known   = get_posts( [ 'post_type' => 'wpuf_profile', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] );
foreach ( [ 'subscriber' => $wpuf_sub, 'admin' => $wpuf_admin ] as $wpuf_role => $wpuf_user ) {
    wp_set_current_user( $wpuf_user );
    $wpuf_before = (int) wp_count_posts( 'wpuf_profile' )->publish + (int) wp_count_posts( 'wpuf_profile' )->draft;
    $_GET        = [ 'template' => 'simple_user_signup_template', '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ) ];
    $_REQUEST    = $_GET;
    try {
        $wpuf_handler->create_profile_form_from_template();
        $wpuf_stop = 'returned';
    } catch ( Exception $e ) {
        $wpuf_stop = $e->getMessage();
    }
    wp_cache_delete( _count_posts_cache_key( 'wpuf_profile' ), 'counts' );
    $wpuf_after                          = (int) wp_count_posts( 'wpuf_profile' )->publish + (int) wp_count_posts( 'wpuf_profile' )->draft;
    $wpuf_out[ 'template_' . $wpuf_role ] = [ $wpuf_stop, $wpuf_after - $wpuf_before ];
}

foreach ( get_posts( [ 'post_type' => 'wpuf_profile', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'exclude' => $wpuf_known ] ) as $wpuf_created ) {
    foreach ( get_children( [ 'post_parent' => $wpuf_created, 'post_type' => 'wpuf_input', 'fields' => 'ids' ] ) as $wpuf_child ) {
        wp_delete_post( $wpuf_child, true );
    }
    wp_delete_post( $wpuf_created, true );
}

wp_set_current_user( $wpuf_admin );
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user( $wpuf_sub );
false === $wpuf_rates_backup ? delete_option( 'wpuf_tax_rates' ) : update_option( 'wpuf_tax_rates', $wpuf_rates_backup );

echo wp_json_encode( $wpuf_out ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
