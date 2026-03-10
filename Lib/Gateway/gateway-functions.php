<?php

/**
 * Gateway Helper Functions
 *
 * Provides convenient functions for working with payment gateways.
 *
 * @package WPUF
 * @since WPUF_PRO_SINCE
 */

/**
 * Get the gateway manager instance
 *
 * @since WPUF_PRO_SINCE
 *
 * @return \WeDevs\Wpuf\Lib\Gateway\Gateway_Manager|null
 */
function wpuf_get_gateway_manager() {
    if ( function_exists( 'wpuf' ) && isset( wpuf()->gateway_manager ) ) {
        return wpuf()->gateway_manager;
    }
    return null;
}

/**
 * Get a specific payment gateway instance
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return \WeDevs\Wpuf\Abstracts\Payment_Gateway|null
 */
function wpuf_get_gateway( $gateway_id ) {
    $manager = wpuf_get_gateway_manager();
    return $manager ? $manager->get_gateway( $gateway_id ) : null;
}

/**
 * Check if a gateway is registered
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return bool
 */
function wpuf_is_gateway_registered( $gateway_id ) {
    $manager = wpuf_get_gateway_manager();
    return $manager ? $manager->is_gateway_registered( $gateway_id ) : false;
}

/**
 * Get all registered gateway IDs
 *
 * @since WPUF_PRO_SINCE
 *
 * @return array
 */
function wpuf_get_gateway_ids() {
    $manager = wpuf_get_gateway_manager();
    return $manager ? $manager->get_gateway_ids() : [];
}

/**
 * Register a custom payment gateway
 *
 * This is a convenience function for registering gateways outside of the
 * wpuf_register_payment_gateways action hook.
 *
 * @since WPUF_PRO_SINCE
 *
 * @param \WeDevs\Wpuf\Abstracts\Payment_Gateway $gateway Gateway instance
 *
 * @return bool True if registered successfully, false otherwise
 */
function wpuf_register_gateway( $gateway ) {
    $manager = wpuf_get_gateway_manager();

    if ( ! $manager ) {
        return false;
    }

    if ( ! $gateway instanceof \WeDevs\Wpuf\Abstracts\Payment_Gateway ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'wpuf_register_gateway: Gateway must extend Payment_Gateway' );
        }
        return false;
    }

    return $manager->register_gateway( $gateway );
}

/**
 * Unregister a payment gateway
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return bool True if unregistered successfully, false otherwise
 */
function wpuf_unregister_gateway( $gateway_id ) {
    $manager = wpuf_get_gateway_manager();
    return $manager ? $manager->unregister_gateway( $gateway_id ) : false;
}

/**
 * Get gateway configuration
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return array|null Gateway configuration or null if not found
 */
function wpuf_get_gateway_config( $gateway_id ) {
    $gateway = wpuf_get_gateway( $gateway_id );
    return $gateway ? $gateway->get_config() : null;
}

/**
 * Check if a gateway supports subscriptions
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return bool
 */
function wpuf_gateway_supports_subscription( $gateway_id ) {
    $gateway = wpuf_get_gateway( $gateway_id );
    return $gateway ? $gateway->supports_subscription() : false;
}

/**
 * Get active payment gateways with full configuration
 *
 * Returns only the gateways that are enabled in settings with their full config.
 *
 * @since WPUF_PRO_SINCE
 *
 * @return array Array of gateway configurations keyed by gateway ID
 */
function wpuf_get_active_gateway_configs() {
    $active_gateways = wpuf_get_option( 'active_gateways', 'wpuf_payment' );
    $active_gateways = is_array( $active_gateways ) ? $active_gateways : [];

    $manager = wpuf_get_gateway_manager();
    if ( ! $manager ) {
        return [];
    }

    $configs = [];
    foreach ( array_keys( $active_gateways ) as $gateway_id ) {
        $gateway = $manager->get_gateway( $gateway_id );
        if ( $gateway ) {
            $configs[ $gateway_id ] = $gateway->get_config();
        }
    }

    return $configs;
}

/**
 * Get gateway label for display
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 * @param string $context    Context: 'admin' or 'checkout'
 *
 * @return string Gateway label or gateway ID if not found
 */
function wpuf_get_gateway_label( $gateway_id, $context = 'checkout' ) {
    $gateway = wpuf_get_gateway( $gateway_id );

    if ( ! $gateway ) {
        return $gateway_id;
    }

    return ( 'admin' === $context ) ? $gateway->get_admin_label() : $gateway->get_checkout_label();
}

/**
 * Get gateway icon URL
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id Gateway ID
 *
 * @return string Icon URL or empty string if not found
 */
function wpuf_get_gateway_icon( $gateway_id ) {
    $gateway = wpuf_get_gateway( $gateway_id );
    return $gateway ? $gateway->get_icon() : '';
}

/**
 * Process payment through a specific gateway
 *
 * This is a convenience function that triggers the gateway's payment processing.
 *
 * @since WPUF_PRO_SINCE
 *
 * @param string $gateway_id   Gateway ID
 * @param array  $payment_data Payment data
 *
 * @return void
 */
function wpuf_process_gateway_payment( $gateway_id, $payment_data ) {
    /**
     * Process payment through gateway
     *
     * @since 0.8
     * @since WPUF_PRO_SINCE Updated to work with new gateway architecture
     *
     * @param array $payment_data Payment data
     */
    do_action( "wpuf_gateway_{$gateway_id}", $payment_data );
}

/**
 * Check if new gateway architecture is available
 *
 * Useful for backward compatibility checks in extensions.
 *
 * @since WPUF_PRO_SINCE
 *
 * @return bool
 */
function wpuf_has_gateway_manager() {
    return null !== wpuf_get_gateway_manager();
}
