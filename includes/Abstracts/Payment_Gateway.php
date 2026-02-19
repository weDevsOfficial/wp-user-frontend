<?php

namespace WeDevs\Wpuf\Abstracts;

/**
 * Abstract Payment Gateway Class
 *
 * Provides a base structure for all payment gateways following Dokan's architecture pattern.
 * All payment gateways should extend this class.
 *
 * @since WPUF_PRO_SINCE
 */
abstract class Payment_Gateway {

    /**
     * Gateway ID
     *
     * @var string
     */
    protected $id = '';

    /**
     * Gateway title for admin
     *
     * @var string
     */
    protected $admin_label = '';

    /**
     * Gateway title for checkout
     *
     * @var string
     */
    protected $checkout_label = '';

    /**
     * Gateway icon URL
     *
     * @var string
     */
    protected $icon = '';

    /**
     * Whether this gateway supports subscriptions
     *
     * @var bool
     */
    protected $supports_subscription = false;

    /**
     * Hook priority for settings rendering
     *
     * @var int
     */
    protected $hook_priority = 10;

    /**
     * Constructor
     *
     * @since WPUF_PRO_SINCE
     */
    public function __construct() {
        $this->init();
        $this->setup_hooks();
    }

    /**
     * Initialize gateway properties
     *
     * This method should be overridden by child classes to set gateway-specific properties
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    abstract protected function init();

    /**
     * Setup WordPress hooks
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    protected function setup_hooks() {
        add_action( "wpuf_gateway_{$this->id}", [ $this, 'process_payment' ] );
        add_filter( 'wpuf_options_payment', [ $this, 'register_settings' ], $this->hook_priority );
        add_action( "wpuf_cancel_payment_{$this->id}", [ $this, 'cancel_payment' ] );
    }

    /**
     * Get gateway ID
     *
     * @since WPUF_PRO_SINCE
     *
     * @return string
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Get admin label
     *
     * @since WPUF_PRO_SINCE
     *
     * @return string
     */
    public function get_admin_label() {
        return $this->admin_label;
    }

    /**
     * Get checkout label
     *
     * @since WPUF_PRO_SINCE
     *
     * @return string
     */
    public function get_checkout_label() {
        return $this->checkout_label;
    }

    /**
     * Get icon URL
     *
     * @since WPUF_PRO_SINCE
     *
     * @return string
     */
    public function get_icon() {
        return $this->icon;
    }

    /**
     * Check if gateway supports subscriptions
     *
     * @since WPUF_PRO_SINCE
     *
     * @return bool
     */
    public function supports_subscription() {
        return $this->supports_subscription;
    }

    /**
     * Get gateway configuration array
     *
     * @since WPUF_PRO_SINCE
     *
     * @return array
     */
    public function get_config() {
        return [
            'admin_label'           => $this->get_admin_label(),
            'checkout_label'        => $this->get_checkout_label(),
            'icon'                  => $this->get_icon(),
            'supports_subscription' => $this->supports_subscription(),
        ];
    }

    /**
     * Process payment
     *
     * This method should be overridden by child classes to handle payment processing
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $payment_data Payment data including user info, amount, item details
     *
     * @return void
     */
    abstract public function process_payment( $payment_data );

    /**
     * Register gateway settings
     *
     * This method should be overridden by child classes to add gateway-specific settings
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $options Existing payment options
     *
     * @return array Modified options array
     */
    public function register_settings( $options ) {
        return $options;
    }

    /**
     * Cancel payment/subscription
     *
     * This method can be overridden by child classes to handle payment cancellation
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $data Cancellation data
     *
     * @return void
     */
    public function cancel_payment( $data ) {
        // Default implementation - can be overridden
    }

    /**
     * Validate payment data
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $data Payment data to validate
     *
     * @return bool|\WP_Error True if valid, WP_Error if invalid
     */
    protected function validate_payment_data( $data ) {
        if ( empty( $data['price'] ) || $data['price'] <= 0 ) {
            return new \WP_Error( 'invalid_amount', __( 'Invalid payment amount', 'wp-user-frontend' ) );
        }

        if ( empty( $data['user_info']['email'] ) ) {
            return new \WP_Error( 'invalid_email', __( 'Invalid user email', 'wp-user-frontend' ) );
        }

        return true;
    }

    /**
     * Log gateway activity
     *
     * @since WPUF_PRO_SINCE
     *
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     *
     * @return void
     */
    protected function log( $message, $level = 'info' ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( sprintf( '[WPUF Gateway %s] [%s] %s', $this->id, strtoupper( $level ), $message ) );
        }
    }
}
