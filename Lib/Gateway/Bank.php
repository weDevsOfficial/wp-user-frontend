<?php

namespace WeDevs\Wpuf\Lib\Gateway;

use WeDevs\Wpuf\Pro\Coupons;

/**
 * WP User Frotnend Bank gateway
 *
 * @since 2.1.4
 */
class Bank {
    public function __construct() {
        add_action( 'wpuf_gateway_bank', [$this, 'prepare_to_send'] );
        add_filter( 'wpuf_options_payment', [ $this, 'payment_options' ] );
        add_action( 'wpuf_gateway_bank_order_submit', [$this, 'order_notify_admin'] );
        add_action( 'wpuf_gateway_bank_order_complete', [$this, 'order_notify_user'], 10, 2 );
    }

    /**
     * Adds bank specific options to the admin panel
     *
     * @param array $options
     *
     * @return array
     */
    public function payment_options( $options ) {
        $pages = wpuf_get_pages();

        $options[] = [
            'name'    => 'gate_instruct_bank',
            'label'   => __( 'Bank Instruction', 'wp-user-frontend' ),
            'type'    => 'wysiwyg',
            'default' => 'Make your payment directly into our bank account.',
        ];

        $options[] = [
            'name'    => 'bank_success',
            'label'   => __( 'Bank Payment Success Page', 'wp-user-frontend' ),
            'desc'    => __( 'After payment users will be redirected here', 'wp-user-frontend' ),
            'type'    => 'select',
            'options' => $pages,
        ];

        return $options;
    }

    /**
     * Prepare the payment form and send to paypal
     *
     * @since 0.8
     *
     * @param array $data payment info
     */
    public function prepare_to_send( $data ) {
        $order_id = wp_insert_post( [
            'post_type'   => 'wpuf_order',
            'post_status' => 'publish',
            'post_title'  => 'WPUF Bank Order',
        ] );

        $data['price'] = isset( $data['price'] ) ? empty( $data['price'] ) ? 0 : $data['price'] : 0;

        if ( isset( $_POST['coupon_id'] ) && !empty( $_POST['coupon_id'] ) ) {
            $data['price'] = (new Coupons())->discount( $data['price'], $_POST['coupon_id'], $data['item_number'] );
        }

        $post_id = isset( $data['item_number'] ) && $data['type'] === 'post' ? $data['item_number'] : 0;

        // Check if pricing fields payment is enabled and update price accordingly
        if ( $post_id && $data['type'] === 'post' ) {
            $form_id = get_post_meta( $post_id, '_wpuf_form_id', true );
            if ( $form_id ) {
                $form_settings = wpuf_get_form_settings( $form_id );
                $pricing_enabled = isset( $form_settings['enable_pricing_payment'] ) && wpuf_is_checkbox_or_toggle_on( $form_settings['enable_pricing_payment'] );
                if ( $pricing_enabled ) {
                    $pricing_cost = get_post_meta( $post_id, '_wpuf_pricing_field_cost', true );
                    if ( $pricing_cost && is_numeric( $pricing_cost ) ) {
                        $data['price'] = floatval( $pricing_cost );
                    }
                }
            }
        }

        $data['cost']     = apply_filters( 'wpuf_payment_amount', $data['price'], $post_id ); //price with tax from pro
        $data['tax']      = floatval( $data['cost'] ) -  floatval( $data['price'] );
        $data['subtotal'] = $data['price'];

        if ( $order_id ) {
            update_post_meta( $order_id, '_data', $data );
        }

        do_action( 'wpuf_gateway_bank_order_submit', $data, $order_id );

        $success = wpuf_payment_success_page( $data );
        wp_redirect( $success );
        exit;
    }

    /**
     * Send payment received mail
     *
     * @param array $info payment information
     */
    public function order_notify_admin() {
        $subject  = sprintf(
            // translators: %s is site name
            __( '[%s] New Bank Order Received', 'wp-user-frontend' ), get_bloginfo( 'name' ) );
        $msg      = sprintf(
            // translators: %1$s is site name url and %2$s is wpuf transaction url
            __( 'New bank order received at %1$s, please check it out: %2$s', 'wp-user-frontend' ), get_bloginfo( 'name' ), admin_url( 'admin.php?page=wpuf_transaction' ) );

        $receiver = get_bloginfo( 'admin_email' );
        $subject  = apply_filters( 'wpuf_mail_bank_admin_subject', $subject );
        $msg      = apply_filters( 'wpuf_mail_bank_admin_body', $msg );

        wp_mail( $receiver, $subject, $msg );
    }

    /**
     * Send payment confirm mail to the user
     *
     * @param array $info payment information
     */
    public function order_notify_user( $transaction, $order_id ) {
        // If Pro is active, do not send this email (invoice email will be sent)
        if ( class_exists( 'WeDevs\Wpuf\Pro\Admin\Invoice' ) ) {
            wp_delete_post( $order_id, true );
            return;
        }

        $user = get_user_by( 'id', $transaction['user_id'] );

        if ( !$user ) {
            return;
        }

        $subject = sprintf(
            // translators: %s is site name
            __( '[%s] Payment Received', 'wp-user-frontend' ), get_bloginfo( 'name' ) );
        $msg     = sprintf(
            // translators: %s is displayname
            __( 'Hello %s,', 'wp-user-frontend' ), $user->display_name ) . "\r\n";
        // translators: %s is the payment amount
        $msg .= sprintf( __( 'We have received your payment amount of %s through bank . ', 'wp-user-frontend' ), $transaction['cost'] ) . "\r\n\r\n";
        $msg .= __( 'Thanks for being with us.', 'wp-user-frontend' ) . "\r\n";

        $subject = apply_filters( 'wpuf_mail_bank_user_subject', $subject );
        $msg     = apply_filters( 'wpuf_mail_bank_user_body', $msg );

        wp_mail( $user->user_email, $subject, $msg );

        // finally delete the order post
        wp_delete_post( $order_id, true );
    }
}
