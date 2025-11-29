<?php

/**
 * Free Modules Functions
 *
 * Functions to handle Free version modules similar to Pro
 *
 * @package WPUF
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get available Free modules
 *
 * @since 4.3.0
 *
 * @return array
 */
function wpuf_free_get_modules() {
    return [
        'user_directory' => [
            'id'          => 'user_directory',
            'name'        => __( 'User Directory', 'wp-user-frontend' ),
            'description' => __( 'Handle user listing and user profile in frontend', 'wp-user-frontend' ),
            'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-directory/',
            'thumbnail'   => 'wpuf-ul.png',
            'class'       => 'WeDevs\\Wpuf\\Modules\\User_Directory\\User_Directory',
        ],
    ];
}

/**
 * Get the meta key to store the active module list
 *
 * @since 4.3.0
 *
 * @return string
 */
function wpuf_free_active_module_key() {
    return 'wpuf_free_active_modules';
}

/**
 * Get active modules
 *
 * @since 4.3.0
 *
 * @return array
 */
function wpuf_free_get_active_modules() {
    return get_option( wpuf_free_active_module_key(), [ 'user_directory' ] ); // User directory is active by default
}

/**
 * Check if a module is active
 *
 * @since 4.3.0
 *
 * @param string $module Module id (e.g., 'user_directory')
 *
 * @return bool
 */
function wpuf_free_is_module_active( $module ) {
    return in_array( $module, wpuf_free_get_active_modules(), true );
}

/**
 * Check if a module is inactive
 *
 * @since 4.3.0
 *
 * @param string $module Module id
 *
 * @return bool
 */
function wpuf_free_is_module_inactive( $module ) {
    return ! wpuf_free_is_module_active( $module );
}

/**
 * Activate a module
 *
 * @since 4.3.0
 *
 * @param string $module Module id
 *
 * @return WP_Error|null WP_Error on invalid module or null on success
 */
function wpuf_free_activate_module( $module ) {
    $modules = wpuf_free_get_modules();

    if ( ! isset( $modules[ $module ] ) ) {
        return new WP_Error( 'invalid-module', __( 'The module is invalid', 'wp-user-frontend' ) );
    }

    $current = wpuf_free_get_active_modules();

    // Activate if inactive
    if ( wpuf_free_is_module_inactive( $module ) ) {
        $current[] = $module;
        sort( $current );
        update_option( wpuf_free_active_module_key(), $current );
    }

    return null;
}

/**
 * Deactivate a module
 *
 * @since 4.3.0
 *
 * @param string $module Module id
 *
 * @return bool
 */
function wpuf_free_deactivate_module( $module ) {
    $current = wpuf_free_get_active_modules();

    if ( wpuf_free_is_module_active( $module ) ) {
        $key = array_search( $module, $current, true );

        if ( false !== $key ) {
            unset( $current[ $key ] );
            $current = array_values( $current ); // Re-index array
        }

        update_option( wpuf_free_active_module_key(), $current );
    }

    return true;
}
