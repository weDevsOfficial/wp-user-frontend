<?php

namespace WeDevs\Wpuf\Lib\Gateway;

use WeDevs\Wpuf\Abstracts\Payment_Gateway;
use WeDevs\Wpuf\Frontend\Payment;

/**
 * PayPal Payment Gateway
 *
 * Handles PayPal payment processing using the new gateway architecture.
 *
 * @since WPUF_PRO_SINCE
 */
class Paypal_Gateway extends Payment_Gateway {

    /**
     * PayPal gateway URL
     *
     * @var string
     */
    private $gateway_url;

    /**
     * Test mode flag
     *
     * @var bool
     */
    private $test_mode;

    /**
     * Webhook ID
     *
     * @var string
     */
    private $webhook_id;

    /**
     * API version
     *
     * @var string
     */
    private $api_version = '2.0';

    /**
     * Settings URL
     *
     * @var string
     */
    private $settings_url;

    /**
     * Setup guide URL
     *
     * @var string
     */
    private $setup_guide_url;

    /**
     * Initialize gateway properties
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    protected function init() {
        $this->id                    = 'paypal';
        $this->admin_label           = __( 'PayPal', 'wp-user-frontend' );
        $this->checkout_label        = __( 'PayPal', 'wp-user-frontend' );
        $this->icon                  = apply_filters( 'wpuf_paypal_checkout_icon', WPUF_ASSET_URI . '/images/paypal.png' );
        $this->supports_subscription = true;

        // Initialize PayPal-specific properties
        $this->gateway_url       = 'https://www.paypal.com/webscr/?';
        $this->test_mode         = 'test' === wpuf_get_option( 'paypal_test_mode', 'wpuf_payment' );
        $this->webhook_id        = wpuf_get_option( 'paypal_webhook_id', 'wpuf_payment' );
        $this->settings_url      = admin_url( 'admin.php?page=wpuf-settings&tab=wpuf_payment' );
        $this->setup_guide_url   = 'https://wedevs.com/docs/wp-user-frontend-pro/settings/paypal-payement-gateway/';
    }

    /**
     * Setup WordPress hooks
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    protected function setup_hooks() {
        parent::setup_hooks();

        // PayPal-specific hooks
        add_action( 'init', [ $this, 'check_paypal_return' ], 20 );
        add_action( 'init', [ $this, 'handle_pending_payment' ] );
        add_action( 'init', [ $this, 'register_webhook_endpoint' ] );
        add_action( 'wpuf_cancel_subscription_paypal', [ $this, 'cancel_subscription' ] );
        add_action( 'wpuf_paypal_webhook', [ $this, 'process_webhook' ] );
        add_action( 'template_redirect', [ $this, 'handle_webhook_request' ] );

        // Admin hooks
        add_action( 'admin_notices', [ $this, 'paypal_settings_update_notice' ] );
        add_action( 'wp_ajax_wpuf_dismiss_paypal_notice', [ $this, 'dismiss_paypal_notice' ] );
        add_action( 'admin_head', [ $this, 'inject_webhook_css' ] );
    }

    /**
     * Process payment
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $payment_data Payment data
     *
     * @return void
     */
    public function process_payment( $payment_data ) {
        // Delegate to the existing Paypal class for now
        // This maintains backward compatibility while we transition
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'prepare_to_send' ) ) {
            $legacy_paypal->prepare_to_send( $payment_data );
        }
    }

    /**
     * Register gateway settings
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $options Existing payment options
     *
     * @return array Modified options array
     */
    public function register_settings( $options ) {
        // Delegate to the existing Paypal class for now
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'payment_options' ) ) {
            return $legacy_paypal->payment_options( $options );
        }

        return $options;
    }

    /**
     * Cancel subscription
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $data Cancellation data
     *
     * @return void
     */
    public function cancel_subscription( $data ) {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'cancel_subscription' ) ) {
            $legacy_paypal->cancel_subscription( $data );
        }
    }

    /**
     * Check PayPal return
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function check_paypal_return() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'check_paypal_return' ) ) {
            $legacy_paypal->check_paypal_return();
        }
    }

    /**
     * Handle pending payment
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function handle_pending_payment() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'handle_pending_payment' ) ) {
            $legacy_paypal->handle_pending_payment();
        }
    }

    /**
     * Register webhook endpoint
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function register_webhook_endpoint() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'register_webhook_endpoint' ) ) {
            $legacy_paypal->register_webhook_endpoint();
        }
    }

    /**
     * Process webhook
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function process_webhook() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'process_webhook' ) ) {
            $legacy_paypal->process_webhook();
        }
    }

    /**
     * Handle webhook request
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function handle_webhook_request() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'handle_webhook_request' ) ) {
            $legacy_paypal->handle_webhook_request();
        }
    }

    /**
     * PayPal settings update notice
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function paypal_settings_update_notice() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'paypal_settings_update_notice' ) ) {
            $legacy_paypal->paypal_settings_update_notice();
        }
    }

    /**
     * Dismiss PayPal notice
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function dismiss_paypal_notice() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'dismiss_paypal_notice' ) ) {
            $legacy_paypal->dismiss_paypal_notice();
        }
    }

    /**
     * Inject webhook CSS
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function inject_webhook_css() {
        $legacy_paypal = isset( wpuf()->paypal ) ? wpuf()->paypal : null;
        if ( $legacy_paypal && method_exists( $legacy_paypal, 'inject_webhook_css' ) ) {
            $legacy_paypal->inject_webhook_css();
        }
    }
}
