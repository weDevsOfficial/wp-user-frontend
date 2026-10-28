<?php
/**
 * PAR0014 helper (G3 for subscriptions): a pack saved through the REST route
 * (the subscriptions screen's shape) is loaded through the REST GET and posted
 * back unchanged, as the screen does on an untouched save. Prints the meta
 * keys whose stored value changed. Deletes the pack.
 *
 * Usage: wp eval-file check-subscription-noop.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

// Created through the REST route, so it holds the shape the subscriptions screen stores.
$wpuf_create = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
$wpuf_create->set_param(
    'subscription',
    [
        'post_title'   => 'PAR0014 pack',
        'post_content' => 'PAR0014 description',
        'post_status'  => 'publish',
        'meta_value'   => [
            '_billing_amount'         => '12.5',
            '_expiration_number'      => '1',
            '_expiration_period'      => 'month',
            '_post_type_name'         => [ 'post' => '5', 'page' => '0' ],
            '_enable_post_expiration' => 'on',
            '_post_expiration_number' => '7',
            '_post_expiration_period' => 'day',
            '_total_feature_item'     => '2',
        ],
    ]
);
rest_do_request( $wpuf_create );
$wpuf_pack = (int) get_posts( [ 'post_type' => 'wpuf_subscription', 'title' => 'PAR0014 pack', 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1 ] )[0];

$wpuf_snapshot = function () use ( $wpuf_pack ) {
    $post = get_post( $wpuf_pack );
    $meta = get_post_meta( $wpuf_pack );
    ksort( $meta );
    return [
        'post' => [ $post->post_title, $post->post_content, $post->post_status ],
        'meta' => $meta,
    ];
};

$wpuf_before = $wpuf_snapshot();
$wpuf_item   = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/wpuf_subscription/' . $wpuf_pack ) )->get_data();
$wpuf_post   = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
$wpuf_post->set_param( 'subscription', json_decode( wp_json_encode( $wpuf_item['subscription'] ), true ) );
$wpuf_saved  = rest_do_request( $wpuf_post )->get_data();
$wpuf_after  = $wpuf_snapshot();

$wpuf_changed = [];
foreach ( array_unique( array_merge( array_keys( $wpuf_before['meta'] ), array_keys( $wpuf_after['meta'] ) ) ) as $wpuf_key ) {
    $wpuf_old = $wpuf_before['meta'][ $wpuf_key ] ?? null;
    $wpuf_new = $wpuf_after['meta'][ $wpuf_key ] ?? null;
    if ( $wpuf_old !== $wpuf_new ) {
        $wpuf_changed[ $wpuf_key ] = [ $wpuf_old, $wpuf_new ];
    }
}

echo wp_json_encode(
    [
        'saved'   => ! empty( $wpuf_saved['success'] ),
        'post'    => $wpuf_before['post'] === $wpuf_after['post'],
        'changed' => $wpuf_changed,
    ]
);

wp_delete_post( $wpuf_pack, true );
