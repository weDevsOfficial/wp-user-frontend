<?php
/**
 * SET0005 helper: the React settings load applies the hooks the legacy screen
 * applies (task 1.16, B26): wsa_form_top|bottom_{section} output (kses'd),
 * wpuf_get_tax_rates and wpuf_settings_user_roles. Prints JSON.
 *
 * Usage: wp eval-file check-settings-legacy-hooks.php
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_admins = get_users( [ 'role' => 'administrator', 'number' => 1, 'fields' => 'ids' ] );
wp_set_current_user( (int) reset( $wpuf_admins ) );

add_action(
    'wsa_form_top_wpuf_general',
    function () {
        echo '<p class="set5">SET5 top</p><input name="wpuf_general[set5]" value="1">';
    }
);
add_action(
    'wsa_form_bottom_wpuf_general',
    function () {
        echo '<p>SET5 bottom</p>';
    }
);
add_filter(
    'wpuf_get_tax_rates',
    function () {
        return [ [ 'country' => 'SET5', 'state' => '', 'rate' => '5' ] ];
    }
);
add_filter(
    'wpuf_settings_user_roles',
    function () {
        return [ 'set5' => 'SET5 role' ];
    },
    20
);

$wpuf_data = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) )->get_data();
$wpuf_data = $wpuf_data['data'];

echo wp_json_encode(
    [
        'top'          => isset( $wpuf_data['section_html']['wpuf_general']['top'] ) ? $wpuf_data['section_html']['wpuf_general']['top'] : null,
        'bottom'       => isset( $wpuf_data['section_html']['wpuf_general']['bottom'] ) ? $wpuf_data['section_html']['wpuf_general']['bottom'] : null,
        'fired_other'  => did_action( 'wsa_form_top_wpuf_profile' ) > 0,
        'tax_country'  => isset( $wpuf_data['extra']['tax']['rates'][0]['country'] ) ? $wpuf_data['extra']['tax']['rates'][0]['country'] : null,
        'roles'        => isset( $wpuf_data['extra']['profile_role_forms']['roles'] ) ? $wpuf_data['extra']['profile_role_forms']['roles'] : null,
    ]
);
