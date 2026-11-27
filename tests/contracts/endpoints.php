<?php
/**
 * Endpoint / admin page snapshot (contract snapshots, task 0.3).
 *
 * Usage: wp eval-file tests/contracts/endpoints.php --user=admin
 * Output: sections "# rest", "# ajax", "# pages", one item per line, sorted.
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_plugin_re = '#/plugins/(wp-user-frontend|wpuf-pro)/#';

/**
 * Returns the file a callback is defined in.
 *
 * @param callable|array $callback Callback.
 *
 * @return string
 */
function wpuf_contract_callback_file( $callback ) {
    try {
        if ( is_array( $callback ) ) {
            return ( new ReflectionMethod( $callback[0], $callback[1] ) )->getFileName();
        }

        if ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
            return ( new ReflectionMethod( $callback ) )->getFileName();
        }

        return ( new ReflectionFunction( $callback ) )->getFileName();
    } catch ( ReflectionException $e ) {
        return '';
    }
}

// REST.
$wpuf_lines = [];

foreach ( rest_get_server()->get_routes() as $wpuf_route => $wpuf_handlers ) {
    foreach ( $wpuf_handlers as $wpuf_handler ) {
        $wpuf_file = isset( $wpuf_handler['callback'] ) ? wpuf_contract_callback_file( $wpuf_handler['callback'] ) : '';

        if ( ! preg_match( $wpuf_plugin_re, $wpuf_file ) ) {
            continue;
        }

        $wpuf_perm = isset( $wpuf_handler['permission_callback'] ) ? $wpuf_handler['permission_callback'] : null;
        $wpuf_perm = '__return_true' === $wpuf_perm ? 'public' : ( $wpuf_perm ? 'callback' : 'none' );

        $wpuf_lines[] = $wpuf_route . ' ' . implode( ',', array_keys( $wpuf_handler['methods'] ) ) . ' perm=' . $wpuf_perm . ' args=' . implode( ',', array_keys( (array) $wpuf_handler['args'] ) );
    }
}

sort( $wpuf_lines );
echo "# rest\n" . implode( "\n", array_unique( $wpuf_lines ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// AJAX: every wp_ajax_* hook with a WPUF callback.
global $wp_filter;

$wpuf_lines = [];

foreach ( $wp_filter as $wpuf_name => $wpuf_hook ) {
    if ( 0 !== strpos( $wpuf_name, 'wp_ajax_' ) ) {
        continue;
    }

    foreach ( $wpuf_hook->callbacks as $wpuf_callbacks ) {
        foreach ( $wpuf_callbacks as $wpuf_cb ) {
            if ( preg_match( $wpuf_plugin_re, wpuf_contract_callback_file( $wpuf_cb['function'] ) ) ) {
                $wpuf_lines[] = $wpuf_name;
            }
        }
    }
}

$wpuf_lines = array_unique( $wpuf_lines );
sort( $wpuf_lines );
echo "# ajax\n" . implode( "\n", $wpuf_lines ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

// Admin pages.
require_once ABSPATH . 'wp-admin/includes/admin.php';
set_current_screen( 'dashboard' );
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
do_action( 'admin_menu' );

global $menu, $submenu, $_registered_pages;

$wpuf_lines = [];

foreach ( (array) $menu as $wpuf_item ) {
    if ( preg_match( '/wpuf|user-frontend/i', $wpuf_item[2] ) ) {
        $wpuf_lines[] = 'menu ' . $wpuf_item[2] . ' cap=' . $wpuf_item[1];
    }
}

foreach ( (array) $submenu as $wpuf_parent => $wpuf_items ) {
    foreach ( $wpuf_items as $wpuf_item ) {
        if ( preg_match( '/wpuf|user-frontend/i', $wpuf_parent . ' ' . $wpuf_item[2] ) ) {
            $wpuf_lines[] = 'submenu ' . $wpuf_parent . ' > ' . $wpuf_item[2] . ' cap=' . $wpuf_item[1];
        }
    }
}

foreach ( array_keys( (array) $_registered_pages ) as $wpuf_page ) {
    if ( preg_match( '/wpuf|user-frontend/i', $wpuf_page ) ) {
        $wpuf_lines[] = 'registered ' . $wpuf_page;
    }
}

$wpuf_lines = array_unique( $wpuf_lines );
sort( $wpuf_lines );
echo "# pages\n" . implode( "\n", $wpuf_lines ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
