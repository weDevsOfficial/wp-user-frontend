<?php

namespace WeDevs\Wpuf\Lib\Gateway;

use WeDevs\Wpuf\Abstracts\Payment_Gateway;

/**
 * Bank Payment Gateway
 *
 * Handles bank transfer payment processing using the new gateway architecture.
 *
 * @since WPUF_PRO_SINCE
 */
class Bank_Gateway extends Payment_Gateway {

    /**
     * Initialize gateway properties
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    protected function init() {
        $this->id                    = 'bank';
        $this->admin_label           = __( 'Bank Payment', 'wp-user-frontend' );
        $this->checkout_label        = __( 'Bank Payment', 'wp-user-frontend' );
        $this->icon                  = '';
        $this->supports_subscription = false;
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

        // Bank-specific hooks
        add_action( 'wpuf_gateway_bank_order_submit', [ $this, 'order_notify_admin' ] );
        add_action( 'wpuf_gateway_bank_order_complete', [ $this, 'order_notify_user' ], 10, 2 );
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
        // Delegate to the existing Bank class for now
        // This maintains backward compatibility while we transition
        $legacy_bank = isset( wpuf()->bank ) ? wpuf()->bank : null;
        if ( $legacy_bank && method_exists( $legacy_bank, 'prepare_to_send' ) ) {
            $legacy_bank->prepare_to_send( $payment_data );
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
        // Delegate to the existing Bank class for now
        $legacy_bank = isset( wpuf()->bank ) ? wpuf()->bank : null;
        if ( $legacy_bank && method_exists( $legacy_bank, 'payment_options' ) ) {
            return $legacy_bank->payment_options( $options );
        }

        return $options;
    }

    /**
     * Send payment received mail to admin
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $info Payment information
     *
     * @return void
     */
    public function order_notify_admin( $info ) {
        $legacy_bank = isset( wpuf()->bank ) ? wpuf()->bank : null;
        if ( $legacy_bank && method_exists( $legacy_bank, 'order_notify_admin' ) ) {
            $legacy_bank->order_notify_admin( $info );
        }
    }

    /**
     * Send payment confirm mail to the user
     *
     * @since WPUF_PRO_SINCE
     *
     * @param array $transaction Transaction data
     * @param int   $order_id    Order ID
     *
     * @return void
     */
    public function order_notify_user( $transaction, $order_id ) {
        $legacy_bank = isset( wpuf()->bank ) ? wpuf()->bank : null;
        if ( $legacy_bank && method_exists( $legacy_bank, 'order_notify_user' ) ) {
            $legacy_bank->order_notify_user( $transaction, $order_id );
        }
    }
}
