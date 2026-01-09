<?php

namespace WeDevs\Wpuf\Lib\Gateway;

use WeDevs\Wpuf\Abstracts\Payment_Gateway;

/**
 * Payment Gateway Manager
 *
 * Manages registration and initialization of payment gateways.
 * Follows Dokan's architecture pattern for better extensibility.
 *
 * @since WPUF_PRO_SINCE
 */
class Gateway_Manager {

    /**
     * Registered gateway instances
     *
     * @var Payment_Gateway[]
     */
    private $gateways = [];

    /**
     * Constructor
     *
     * @since WPUF_PRO_SINCE
     */
    public function __construct() {
        $this->init_gateways();
        $this->setup_hooks();
    }

    /**
     * Initialize all payment gateways
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    private function init_gateways() {
        // Initialize core gateways
        $this->register_gateway( new Paypal_Gateway() );
        $this->register_gateway( new Bank_Gateway() );

        /**
         * Allow third-party plugins to register custom gateways
         *
         * @since WPUF_PRO_SINCE
         *
         * @param Gateway_Manager $manager Gateway manager instance
         */
        do_action( 'wpuf_register_payment_gateways', $this );
    }

    /**
     * Setup WordPress hooks
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    private function setup_hooks() {
        add_filter( 'wpuf_payment_gateways', [ $this, 'get_registered_gateways' ], 5 );
    }

    /**
     * Register a payment gateway
     *
     * @since WPUF_PRO_SINCE
     *
     * @param Payment_Gateway $gateway Gateway instance
     *
     * @return bool True if registered successfully, false otherwise
     */
    public function register_gateway( Payment_Gateway $gateway ) {
        $gateway_id = $gateway->get_id();

        if ( empty( $gateway_id ) ) {
            $this->log_error( 'Attempted to register gateway without ID' );
            return false;
        }

        if ( isset( $this->gateways[ $gateway_id ] ) ) {
            $this->log_error( sprintf( 'Gateway "%s" is already registered', $gateway_id ) );
            return false;
        }

        $this->gateways[ $gateway_id ] = $gateway;

        return true;
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
    public function unregister_gateway( $gateway_id ) {
        if ( ! isset( $this->gateways[ $gateway_id ] ) ) {
            return false;
        }

        unset( $this->gateways[ $gateway_id ] );

        return true;
    }

    /**
     * Get a specific gateway instance
     *
     * @since WPUF_PRO_SINCE
     *
     * @param string $gateway_id Gateway ID
     *
     * @return Payment_Gateway|null Gateway instance or null if not found
     */
    public function get_gateway( $gateway_id ) {
        return isset( $this->gateways[ $gateway_id ] ) ? $this->gateways[ $gateway_id ] : null;
    }

    /**
     * Get all registered gateways
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $gateways Existing gateways array (for filter compatibility)
     *
     * @return array Gateway configurations
     */
    public function get_registered_gateways( $gateways = [] ) {
        $registered = [];

        foreach ( $this->gateways as $gateway_id => $gateway ) {
            $registered[ $gateway_id ] = $gateway->get_config();
        }

        // Merge with any gateways added via the old filter system for backward compatibility
        return array_merge( $gateways, $registered );
    }

    /**
     * Get all gateway instances
     *
     * @since WPUF_PRO_SINCE
     *
     * @return Payment_Gateway[]
     */
    public function get_gateway_instances() {
        return $this->gateways;
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
    public function is_gateway_registered( $gateway_id ) {
        return isset( $this->gateways[ $gateway_id ] );
    }

    /**
     * Get gateway IDs
     *
     * @since WPUF_PRO_SINCE
     *
     * @return array
     */
    public function get_gateway_ids() {
        return array_keys( $this->gateways );
    }

    /**
     * Log error message
     *
     * @since WPUF_PRO_SINCE
     *
     * @param string $message Error message
     *
     * @return void
     */
    private function log_error( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( sprintf( '[WPUF Gateway Manager] %s', $message ) );
        }
    }
}
