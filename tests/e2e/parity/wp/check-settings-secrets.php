<?php
/**
 * SEC0003 helper: the settings REST payload must not contain secrets, a masked
 * secret posted back must keep the stored value, a new value must replace it,
 * and the AI connection test must use the stored key when given the mask.
 * Restores the touched options afterwards. Prints JSON.
 *
 * Usage: wp eval-file check-settings-secrets.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_backup = [ 'n8n' => get_option( 'n8n' ), 'wpuf_ai' => get_option( 'wpuf_ai' ) ];
$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

update_option( 'n8n', [ 'basic_auth_password' => 'SEC3-N8N-SECRET-99' ] );
update_option( 'wpuf_ai', [ 'ai_provider' => 'openai', 'openai_api_key' => 'sk-SEC3-REALKEY-123456' ] );

$wpuf_get  = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) )->get_data();
$wpuf_json = wp_json_encode( $wpuf_get );
$wpuf_n8n  = isset( $wpuf_get['data']['values']['n8n']['basic_auth_password'] ) ? $wpuf_get['data']['values']['n8n']['basic_auth_password'] : null;
$wpuf_ai   = isset( $wpuf_get['data']['extra']['ai']['keys']['openai'] ) ? $wpuf_get['data']['extra']['ai']['keys']['openai'] : null;

$wpuf_post = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
$wpuf_post->set_param( 'settings', [ 'n8n' => [ 'basic_auth_password' => $wpuf_n8n ] ] );
$wpuf_post->set_param( 'extra', [ 'ai' => [ 'keys' => [ 'openai' => $wpuf_ai ] ] ] );
rest_do_request( $wpuf_post );
$wpuf_kept = [ get_option( 'n8n' )['basic_auth_password'], get_option( 'wpuf_ai' )['openai_api_key'] ];

$wpuf_auth = '';
add_filter(
    'pre_http_request',
    function ( $pre, $args ) use ( &$wpuf_auth ) {
        $wpuf_auth = isset( $args['headers']['Authorization'] ) ? $args['headers']['Authorization'] : '';
        return new WP_Error( 'wpuf_test_blocked', 'blocked' );
    },
    10,
    2
);
$wpuf_test = new WP_REST_Request( 'POST', '/wpuf/v1/ai-form-builder/test' );
$wpuf_test->set_param( 'provider', 'openai' );
$wpuf_test->set_param( 'api_key', $wpuf_ai );
$wpuf_test->set_param( 'model', 'gpt-4o-mini' );
rest_do_request( $wpuf_test );

$wpuf_post2 = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
$wpuf_post2->set_param( 'settings', [ 'n8n' => [ 'basic_auth_password' => 'SEC3-NEW' ] ] );
$wpuf_post2->set_param( 'extra', [ 'ai' => [ 'keys' => [ 'openai' => 'sk-SEC3-NEW' ] ] ] );
rest_do_request( $wpuf_post2 );
$wpuf_new = [ get_option( 'n8n' )['basic_auth_password'], get_option( 'wpuf_ai' )['openai_api_key'] ];

foreach ( $wpuf_backup as $wpuf_key => $wpuf_value ) {
    false === $wpuf_value ? delete_option( $wpuf_key ) : update_option( $wpuf_key, $wpuf_value );
}

echo wp_json_encode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    [
        'payload_has_secret' => false !== strpos( $wpuf_json, 'SEC3-N8N-SECRET-99' ) || false !== strpos( $wpuf_json, 'sk-SEC3-REALKEY-123456' ),
        'masked'             => [ $wpuf_n8n, $wpuf_ai ],
        'kept'               => $wpuf_kept,
        'test_used_stored'   => 'Bearer sk-SEC3-REALKEY-123456' === $wpuf_auth,
        'new'                => $wpuf_new,
    ]
);
