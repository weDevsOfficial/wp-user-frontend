<?php

/**
 * WP User Frotnend Paypal gateway
 *
 * @since 0.8
 * @package WP User Frontend
 */
class WPUF_Paypal {

    private $gateway_url;
    private $gateway_cancel_url;
    private $test_mode;

    public function __construct() {
        $this->gateway_url        = 'https://www.paypal.com/webscr/?';
        $this->gateway_cancel_url = 'https://api-3t.paypal.com/nvp';
        $this->test_mode          = false;

        add_action( 'wpuf_gateway_paypal', array( $this, 'prepare_to_send' ) );
        add_action( 'wpuf_options_payment', array( $this, 'payment_options' ) );
        add_action( 'init', array( $this, 'check_response' ) );
        add_action( 'wpuf_paypal_ipn_success', array( $this, 'paypal_success' ) );
        add_action( 'wpuf_cancel_payment_paypal', array( $this, 'handle_cancel_subscription' ) );
        add_action( 'wpuf_cancel_subscription_paypal', array( $this, 'handle_cancel_subscription' ) );
    }

    public function subscription_cancel( $user_id ) {
        $sub_meta = 'cancel';
        wpuf_get_user( $user_id )->subscription()->update_meta( $sub_meta );
    }

    /**
     * Change PayPal recurring payment status
     *
     * @param  int $user_id
     * @param  string $status
     * @return void
     */
    public function recurring_change_status( $user_id, $status ) {
        global $wp_version;

        $sub_info          = get_user_meta( $user_id, '_wpuf_subscription_pack', true );
        $api_username      = wpuf_get_option( 'paypal_api_username', 'wpuf_payment' );
        $api_password      = wpuf_get_option( 'paypal_api_password', 'wpuf_payment' );
        $api_signature     = wpuf_get_option( 'paypal_api_signature', 'wpuf_payment' );
        $profile_id        = isset( $sub_info['profile_id'] ) ? $sub_info['profile_id'] : '';
        $new_status        = $status;
        $new_status_string = $status;

        $this->set_mode();

        $args = array(
            'USER'      => $api_username,
            'PWD'       => $api_password,
            'SIGNATURE' => $api_signature,
            'VERSION'   => '76.0',
            'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
            'PROFILEID' => $profile_id,
            'ACTION'    => ucfirst( $new_status ),
            'NOTE'      => sprintf( __( 'Subscription %s at %s', 'wp-user-frontend' ), $new_status_string, get_bloginfo( 'name' ) ),
        );

        // Send back post vars to paypal
        $params = array(
            'body'       => $args,
            'sslverify'  => true,
            'timeout'    => 30,
            'user-agent' => 'WP User Frontend Pro/' . WPUF_VERSION,
        );

        $response = wp_remote_post( $this->gateway_cancel_url, $params );

        parse_str( $response['body'], $parsed_response );

        if ( strtolower( $parsed_response['ACK'] ) == 'success' ) {
            $this->subscription_cancel( $user_id );
        }
    }

    /**
     * Adds paypal specific options to the admin panel
     *
     * @param type $options
     * @return string
     */
    public function payment_options( $options ) {

        $options[] = array(
            'name' => 'paypal_email',
            'label' => __( 'PayPal Email', 'wp-user-frontend' )
        );

        $options[] = array(
            'name' => 'gate_instruct_paypal',
            'label' => __( 'PayPal Instruction', 'wp-user-frontend' ),
            'type' => 'textarea',
            'default' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account"
        );

        $options[] = array(
            'name' => 'paypal_api_username',
            'label' => __( 'PayPal API username', 'wp-user-frontend' )
        );
        $options[] = array(
            'name' => 'paypal_api_password',
            'label' => __( 'PayPal API password', 'wp-user-frontend' )
        );
        $options[] = array(
            'name' => 'paypal_api_signature',
            'label' => __( 'PayPal API signature', 'wp-user-frontend' )
        );

        return $options;
    }

    /**
     * Prepare the payment form and send to paypal
     *
     * @since 0.8
     * @param array $data payment info
     */
    public function prepare_to_send( $data ) {

        $user_id          = $data['user_info']['id'];
        $listener_url     = add_query_arg   ( 'action', 'wpuf_paypal_success', home_url( '/' ) );
        $redirect_page_id = wpuf_get_option( 'payment_success', 'wpuf_payment' );

        if ( $redirect_page_id ) {
           $return_url   = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( $redirect_page_id ) );
        } else {
            $return_url  = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( wpuf_get_option( 'subscription_page', 'wpuf_payment' ) ) );
        }

        $billing_amount = empty( $data['price'] ) ? 0 : $data['price'];

        if ( isset( $_POST['coupon_id'] ) && !empty( $_POST['coupon_id'] ) ) {
            $billing_amount = WPUF_Coupons::init()->discount( $billing_amount, $_POST['coupon_id'], $data['item_number'] );

            $coupon_id = $_POST['coupon_id'];
        } else {
            $coupon_id = '';
        }

        $data['subtotal'] = $billing_amount;
        $billing_amount   = apply_filters( 'wpuf_payment_amount', $data['subtotal'] );
        $data['tax']      = $billing_amount - $data['subtotal'];

        if ( $billing_amount == 0 ) {
            wpuf_get_user( $user_id )->subscription()->add_pack( $data['item_number'], $profile_id = null, false,'free' );
            wp_redirect( $return_url );
            exit();
        }


        if ( $data['type'] == 'pack' && $data['custom']['recurring_pay'] == 'yes' ) {

            if( $data['custom']['cycle_period'] == "day")
                $period = "D";
            elseif( $data['custom']['cycle_period'] == "week")
                $period = "W";
            elseif( $data['custom']['cycle_period'] == "month")
                $period = "M";
            elseif( $data['custom']['cycle_period'] == "year")
                $period = "Y";

            if( $data['custom']['trial_duration_type'] == "day")
                $trial_period = "D";
            elseif( $data['custom']['trial_duration_type'] == "week")
                $trial_period = "W";
            elseif( $data['custom']['trial_duration_type'] == "month")
                $trial_period = "M";
            elseif( $data['custom']['trial_duration_type'] == "year")
                $trial_period = "Y";


            $paypal_args = array(
                'cmd'           =>  '_xclick-subscriptions',
                'business'      =>  wpuf_get_option('paypal_email', 'wpuf_payment'),
                'a3'            =>  $billing_amount,
                'p3'            =>  !empty( $data['custom']['billing_cycle_number'] ) ? $data['custom']['billing_cycle_number']: '0',
                't3'            =>  $period,
                'item_name'     =>  $data['custom']['post_title'],
                'custom'        =>  json_encode( array( 'billing_amount' => $billing_amount, 'type' => $data['type'], 'user_id' => $user_id, 'coupon_id' => $coupon_id, 'subtotal' => $data['subtotal'], 'tax' => $data['tax'] )),
                'no_shipping'   =>  '1',
                'shipping'      =>  '0',
                'no_note'       =>  '1',
                'currency_code' =>  $data['currency'],
                'item_number'   =>  $data['item_number'],
                'charset'       =>  'UTF-8',
                'rm'            =>  '2',
                'return'        =>  $return_url,
                'notify_url'    =>  $listener_url,
                'src'           =>  '1',
                'sra'           =>  '1',
                'srt'           =>  intval( $data['custom']['billing_limit'] )
            );

            if ( $data['custom']['trial_status'] == 'yes' ) {
                $paypal_args['p1'] = $data['custom']['trial_duration'];
                $paypal_args['t1'] = $trial_period;
            }

        } else {

            $paypal_args = array(
                'cmd'           => '_xclick',
                'business'      => wpuf_get_option('paypal_email', 'wpuf_payment'),
                'amount'        => $billing_amount,
                'item_name'     => isset( $data['custom']['post_title'] ) ? $data['custom']['post_title'] : $data['item_name'],
                'no_shipping'   => '1',
                'shipping'      => '0',
                'no_note'       => '1',
                'currency_code' => $data['currency'],
                'item_number'   => $data['item_number'],
                'charset'       => 'UTF-8',
                'rm'            => '2',
                'custom'        => json_encode( array( 'type' => $data['type'], 'user_id' => $user_id, 'coupon_id' => $coupon_id, 'subtotal' => $data['subtotal'], 'tax' => $data['tax'] ) ),
                'return'        => $return_url,
                'notify_url'    => $listener_url,
            );
        }
        $this->set_mode();

        $paypal_url =  $this->gateway_url . http_build_query( $paypal_args );

        wp_redirect( $paypal_url );
        exit;
    }

    /**
     * Set the payment mode to sandbox or live
     *
     * @since 0.8
     */
    public function set_mode() {
        if ( wpuf_get_option( 'sandbox_mode', 'wpuf_payment' ) == 'on' ) {
            $this->gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
            $this->gateway_cancel_url = 'https://api-3t.sandbox.paypal.com/nvp';
            $this->test_mode   = true;
        }
    }

    /**
     * Check for PayPal IPN Response.
     */
    public function check_response() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'wpuf_paypal_success' && $this->validateIpn() ) {
            do_action( 'wpuf_paypal_ipn_success');
        }
    }

    /**
     * Handle the payment info sent from paypal
     *
     * @since 0.8
     */
    public function paypal_success() {

        $postdata = $_POST;

        // when subscription expire
        if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_eot' ) ) {
            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $this->subscription_cancel( $custom->user_id );
            return;
        }

        // when subscription cancel
        if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_cancel' ) ) {
            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $this->subscription_cancel( $custom->user_id );
            return;
        }

        $insert_payment = false;

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'wpuf_paypal_success' ) {

            WP_User_Frontend::log( 'paypal-payment-info', print_r( $_POST, true ) );

            $postdata     = $_POST;
            $type         = $postdata['custom'];
            $item_number  = $postdata['item_number'];
            $amount       = $postdata['mc_gross'];
            $is_recurring = false;

            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $coupon_id = isset( $custom->coupon_id ) ? $custom->coupon_id : false;

            // check if recurring payment
            if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_payment' ) && ( strtolower( $postdata['payment_status'] ) == 'completed' ) ) {

                if ( $postdata['mc_gross'] == $custom->billing_amount ) {

                    $insert_payment     = true;
                    $post_id            = 0;
                    $pack_id            = $item_number;
                    $is_recurring       = true;
                    $status             = 'subscr_payment';

                    WP_User_Frontend::log( 'paypal-recurring', 'got subscr_payment, should insert of pack_id: ' . $pack_id );

                } else {
                    $this->subscription_cancel( $custom->user_id );

                    WP_User_Frontend::log( 'paypal-recurring', 'got subscr_payment. billing validation failed, cancel subscription. user_id: ' . $custom->user_id );
                }

            } else if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'web_accept' ) && ( strtolower( $postdata['payment_status'] ) == 'completed' ) ){

                WP_User_Frontend::log( 'paypal', 'got web_accept. type: ' . $custom->type . '. item_number: ' . $item_number );

                //verify payment
                $status   = 'web_accept';

                switch ($custom->type ) {
                    case 'post':
                        $post_id = $item_number;
                        $pack_id = 0;
                        break;

                    case 'pack':
                        $post_id = 0;
                        $pack_id = $item_number;
                        break;
                }

            } else if (
                isset ( $postdata['verify_sign'] )
                && !empty( $postdata['verify_sign'] )
                && isset ( $postdata['payment_status'] )
                && $postdata['payment_status'] == 'Pending'
                && isset ( $postdata['pending_reason'] )
                && $postdata['pending_reason'] == 'multi_currency'
                && isset ( $postdata['txn_type'] )
                && $postdata['txn_type'] == 'web_accept'
            ) {
                //verify payment
                $status  = 'web_accept';
                switch ($custom->type ) {
                    case 'post':
                        $post_id = $item_number;
                        $pack_id = 0;
                        break;

                    case 'pack':
                        $post_id = 0;
                        $pack_id = $item_number;
                        break;
                }

            } // payment type

            $data = array(
                'user_id'          => (int) $custom->user_id,
                'status'           => strtolower( $postdata['payment_status'] ),
                'subtotal'         => (float) $custom->subtotal,
                'tax'              => (float) $custom->tax,
                'cost'             => $postdata['mc_gross'],
                'post_id'          => isset( $post_id ) ? $post_id : '',
                'pack_id'          => isset( $pack_id ) ? $pack_id : '',
                'payer_first_name' => $postdata['first_name'],
                'payer_last_name'  => $postdata['last_name'],
                'payer_email'      => $postdata['payer_email'],
                'payment_type'     => 'Paypal',
                'payer_address'    => isset( $postdata['residence_country'] ) ? $postdata['residence_country'] : null,
                'transaction_id'   => $postdata['txn_id'],
                'created'          => current_time( 'mysql' ),
                'profile_id'       => isset( $postdata['subscr_id'] ) ? $postdata['subscr_id'] : null,
            );

            WP_User_Frontend::log( 'payment', 'inserting payment to database. ' . print_r( $data, true ) );

            $transaction_id = wp_strip_all_tags( $postdata['txn_id'] );
            WPUF_Payment::insert_payment( $data, $transaction_id, $is_recurring );

            if ( $coupon_id ) {
                $pre_usage = get_post_meta( $coupon_id, '_coupon_used', true );
                $pre_usage = (empty( $pre_usage )) ? 0 : $pre_usage;
                $new_use   = $pre_usage + 1;

                update_post_meta( $coupon_id, '_coupon_used', $new_use );
            }

            delete_user_meta( $custom->user_id, '_wpuf_user_active' );
            delete_user_meta( $custom->user_id, '_wpuf_activation_key' );

        }
    }

    /**
     * Handle the cancel payment / subscription
     *
     * @return void
     *
     * @since  2.4.1
     */
    public function handle_cancel_subscription( $data ) {
        $user_id = isset( $data['user_id'] ) ? $data['user_id'] : '';
        $this->recurring_change_status( $user_id, 'Cancel' );
    }

    /**
     * Validate the IPN notification
     *
     * @param none
     * @return boolean
     */
    public function validateIpn() {
        global $wp_version;

        WP_User_Frontend::log( 'paypal', 'Checking if PayPal IPN response is valid' );

        $this->set_mode();

        // Get received values from post data
        $validate_ipn = array( 'cmd' => '_notify-validate' );
        $validate_ipn += wp_unslash( $_POST );

        // Send back post vars to paypal
        $params = array(
            'body'        => $validate_ipn,
            'timeout'     => 60,
            'httpversion' => '1.1',
            'compress'    => false,
            'decompress'  => false,
            'user-agent'  => 'WP User Frontend Pro/' . WPUF_VERSION
        );
        if ( wpuf_get_option( 'sandbox_mode', 'wpuf_payment' ) == 'on' ) {
            $this->gateway_url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $this->gateway_url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        }
        $response = wp_safe_remote_post( $this->gateway_url, $params );

        WP_User_Frontend::log( 'paypal', 'IPN Request: ' . print_r( $params, true ) );
        WP_User_Frontend::log( 'paypal', 'IPN Response: ' . print_r( $response, true ) );

        // check to see if the request was valid
        if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
            WP_User_Frontend::log( 'paypal', 'Received valid response from PayPal' );
            return true;
        }

        WP_User_Frontend::log( 'paypal', 'Received invalid response from PayPal' );

        if ( is_wp_error( $response ) ) {
            WP_User_Frontend::log( 'paypal', 'Error response: ' . $response->get_error_message() );
        }

        return false;
    }
}