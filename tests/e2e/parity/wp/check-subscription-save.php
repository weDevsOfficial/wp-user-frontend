<?php
/**
 * PAR0011 helper: saves one pack through the REST route and one through the
 * classic metabox handler, records the pack hooks that fire and the stored
 * expiry/price meta (task 1.21). Deletes the packs. Prints JSON.
 *
 * Usage: wp eval-file check-subscription-save.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

$wpuf_fired = [];
foreach ( [ 'wpuf_before_update_subscription_pack', 'wpuf_before_update_subscription_pack_meta', 'wpuf_after_update_subscription_pack_meta', 'wpuf_update_subscription_pack' ] as $wpuf_hook ) {
    add_action(
        $wpuf_hook,
        function () use ( &$wpuf_fired, $wpuf_hook ) {
            $wpuf_fired[] = $wpuf_hook;
        }
    );
}

$wpuf_meta = function ( $id ) {
    return [
        '_billing_amount'         => get_post_meta( $id, '_billing_amount', true ),
        '_post_expiration_time'   => get_post_meta( $id, '_post_expiration_time', true ),
        '_post_expiration_number' => get_post_meta( $id, '_post_expiration_number', true ),
        '_post_expiration_period' => get_post_meta( $id, '_post_expiration_period', true ),
        'postnum_rollback'        => get_post_meta( $id, 'postnum_rollback_on_delete', true ),
    ];
};

// REST save, as the subscriptions screen posts it.
$wpuf_request = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
$wpuf_request->set_param(
    'subscription',
    [
        'post_title'  => 'PAR0011 rest',
        'post_status' => 'publish',
        'meta_value'  => [
            '_billing_amount'             => '9.99',
            '_enable_post_expiration'     => 'on',
            '_post_expiration_number'     => '7',
            '_post_expiration_period'     => 'day',
            'postnum_rollback_on_delete'  => 'yes',
        ],
    ]
);
rest_do_request( $wpuf_request );
$wpuf_rest_hooks = $wpuf_fired;
$wpuf_rest_id    = (int) get_posts( [ 'post_type' => 'wpuf_subscription', 'title' => 'PAR0011 rest', 'post_status' => 'any', 'fields' => 'ids', 'numberposts' => 1 ] )[0];

// Classic metabox save.
$wpuf_fired   = [];
$wpuf_classic = wp_insert_post( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'PAR0011 classic' ] );
$wpuf_fired   = [];
$_POST        = [
    'meta_box_nonce'           => wp_create_nonce( 'subs_meta_box_nonce' ),
    'billing_amount'           => '9.99',
    'expiration_period'        => 'day',
    'post_type_name'           => [],
    'additional_cpt_options'   => [],
    'post_expiration_settings' => [
        'enable_post_expiration' => 'on',
        'expiration_time_value'  => '7',
        'expiration_time_type'   => 'day',
    ],
    'postnum_rollback_on_delete' => 'yes',
];
wpuf()->subscription->save_form_meta( $wpuf_classic, get_post( $wpuf_classic ) );
$wpuf_classic_hooks = $wpuf_fired;

echo wp_json_encode(
    [
        'rest'          => $wpuf_meta( $wpuf_rest_id ),
        'rest_hooks'    => $wpuf_rest_hooks,
        'classic'       => $wpuf_meta( $wpuf_classic ),
        'classic_hooks' => $wpuf_classic_hooks,
    ]
);

wp_delete_post( $wpuf_rest_id, true );
wp_delete_post( $wpuf_classic, true );
