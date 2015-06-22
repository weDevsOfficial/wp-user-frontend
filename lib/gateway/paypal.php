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

    function __construct() {
        $this->gateway_url = 'https://www.paypal.com/webscr/?';
        $this->gateway_cancel_url = 'https://api-3t.paypal.com/nvp';
        $this->test_mode = false;

        add_action( 'wpuf_gateway_paypal', array($this, 'prepare_to_send') );
        add_action( 'wpuf_options_payment', array($this, 'payment_options') );
        add_action( 'init', array($this, 'paypal_success') );
        /*add_action( 'wpuf_payment_cancel_paypal', array($this, 'subscription_cancel') );
        add_action( 'wpuf_payment_suspend_paypal', array($this, 'subscription_suspend') );
        add_action( 'wpuf_payment_suspend_paypal', array($this, 'subscription_suspend_reactive') );*/
    }

    /*function subscription_suspend_reactive( $user_id = null ) {
        if ( isset( $_POST['wpuf_payment_suspend_submit'] ) && $_POST['action'] == 'wpuf_suspend_reactive' && wp_verify_nonce( $_POST['wpuf_payment_cancel'], '_wpnonce' ) ) {
            $user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : '';
            $this->recurring_change_status( $user_id, 'Reactivate' );
        }
    }

    function subscription_suspend( $user_id = null ) {
        if ( isset( $_POST['wpuf_payment_suspend_submit'] ) && $_POST['action'] == 'wpuf_suspend_pay' && wp_verify_nonce( $_POST['wpuf_payment_cancel'], '_wpnonce' ) ) {
            $user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : '';
            $this->recurring_change_status( $user_id, 'Suspend' );
        }
    }*/

    function subscription_cancel( $user_id ) {
        $sub_meta = 'cancel';
        WPUF_Subscription::init()->update_user_subscription_meta( $user_id, $sub_meta );
    }

    /**
     * Change PayPal recurring payment status
     *
     * @param  int $user_id
     * @param  string $status
     * @return void
     */
    function recurring_change_status( $user_id, $status ) {
        global $wp_version;

        $sub_info          = get_user_meta( $user_id, '_wpuf_subscription_pack', true );
        $api_username      = wpuf_get_option( 'paypal_api_username', 'wpuf_payment' );
        $api_password      = wpuf_get_option( 'paypal_api_password', 'wpuf_payment' );
        $api_signature     = wpuf_get_option( 'paypal_api_signature', 'wpuf_payment' );
        $profile_id        = $sub_info['profile_id'];
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
            'NOTE'      => sprintf( __( 'Subscription %s at %s', 'dps' ), $new_status_string, get_bloginfo( 'name' ) ),
        );

        // Send back post vars to paypal
        $params = array(
            'body'       => $args,
            'sslverify'  => false,
            'timeout'    => 30,
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
        );

        $response = wp_remote_post( $this->gateway_cancel_url, $params );

        parse_str( $response['body'], $parsed_response );

        if ( strtolower( $parsed_response['ACK'] ) == 'success' ) {
            $this->subscription_cancel( $user_id );
            //WPUF_Subscription::init()->update_user_subscription_meta( $user_id, $sub_info );
        }
    }

    /**
     * Adds paypal specific options to the admin panel
     *
     * @param type $options
     * @return string
     */
    function payment_options( $options ) {

        $options[] = array(
            'name' => 'paypal_email',
            'label' => __( 'PayPal Email', 'wpuf' )
        );

        $options[] = array(
            'name' => 'gate_instruct_paypal',
            'label' => __( 'PayPal Instruction', 'wpuf' ),
            'type' => 'textarea',
            'default' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account"
        );

        $options[] = array(
            'name' => 'paypal_api_username',
            'label' => __( 'PayPal API username', 'wpuf' )
        );
        $options[] = array(
            'name' => 'paypal_api_password',
            'label' => __( 'PayPal API password', 'wpuf' )
        );
        $options[] = array(
            'name' => 'paypal_api_signature',
            'label' => __( 'PayPal API signature', 'wpuf' )
        );

        return $options;
    }

    /**
     * Prepare the payment form and send to paypal
     *
     * @since 0.8
     * @param array $data payment info
     */
    function prepare_to_send( $data ) {

        $user_id = $data['user_info']['id'];
        $listener_url = add_query_arg   ( 'action', 'wpuf_paypal_success', home_url( '/' ) );
        $redirect_page_id = wpuf_get_option( 'payment_success', 'wpuf_payment' );
      
        if ( $redirect_page_id ) {
           $return_url   = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( $redirect_page_id ) ); 
        } else {
            $return_url  = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( wpuf_get_option( 'subscription_page', 'wpuf_payment' ) ) );
        }
        
        $billing_amount = empty( $data['price'] ) ? 0 : number_format( $data['price'], 2 );

        if ( isset( $_POST['coupon_id'] ) && !empty( $_POST['coupon_id'] ) ) {
            $billing_amount = WPUF_Coupons::init()->discount( $billing_amount, $_POST['coupon_id'], $data['item_number'] );
            $coupon_id = $_POST['coupon_id'];
        } else {
            $coupon_id = '';
        }
      
        if ( $billing_amount == 0 ) {
            WPUF_Subscription::init()->new_subscription( $user_id, $data['item_number'], $profile_id = null, false,'free' );
            wp_redirect( $return_url );
            exit();
        }


        if ( $data['type'] == 'pack' && $data['custom']['recurring_pay'] == 'yes' ) {

            $trial_cost = !empty( $data['custom']['trial_cost'] ) ? number_format( $data['custom']['trial_cost'] ) : '0';

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
                'custom'        =>  json_encode( array( 'billing_amount' => $billing_amount, 'trial_cost' => $trial_cost, 'type' => $data['type'], 'user_id' => $user_id, 'coupon_id' => $coupon_id )),
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
                $paypal_args['a1'] = $trial_cost;
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
                'custom'        => json_encode( array( 'type' => $data['type'], 'user_id' => $user_id, 'coupon_id' => $coupon_id ) ),
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
    function set_mode() {
        if ( wpuf_get_option( 'sandbox_mode', 'wpuf_payment' ) == 'on' ) {
            $this->gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
            $this->gateway_cancel_url = 'https://api-3t.sandbox.paypal.com/nvp';
            $this->test_mode   = true;
        }
    }

    /**
     * Handle the payment info sent from paypal
     *
     * @since 0.8
     */
    function paypal_success() {
       // WP_User_Frontend::log( $postdata['txn_type'],print_r( $_POST, true) );


        $postdata = $_POST;

        //cancel subscription form admin panel
        if ( isset( $_POST['wpuf_payment_cancel_submit'] ) && $_POST['action'] == 'wpuf_cancel_pay' && wp_verify_nonce( $_POST['wpuf_payment_cancel'], '_wpnonce' ) ) {
            $user_id = isset( $_POST['user_id'] ) ? $_POST['user_id'] : '';
            $this->recurring_change_status( $user_id, 'Cancel' );
            return;
        }

        //when subscription expire
        if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_eot' ) ) {
            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $this->subscription_cancel( $custom->user_id );
            return;
        }

        //when subscription cancel
        if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_cancel' ) ) {
            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $this->subscription_cancel( $custom->user_id );
            return;
        }

        $insert_payment = false;

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'wpuf_paypal_success' ) {

            $postdata     = $_POST;
            $type         = $postdata['custom'];
            $item_number  = $postdata['item_number'];
            $amount       = $postdata['mc_gross'];
            $is_recurring = false;

            $custom = json_decode( stripcslashes( $postdata['custom'] ) );
            $coupon_id = isset( $custom->coupon_id ) ? $custom->coupon_id : false;

            // check if recurring payment
            if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'subscr_payment' ) && ( strtolower( $postdata['payment_status'] ) == 'completed' ) ) {

                if ( $postdata['mc_gross'] == $custom->billing_amount || ( $postdata['mc_gross'] == $custom->trial_cost && $custom->trial_cost > 0 ) ) {

                    $insert_payment     = true;
                    $post_id            = 0;
                    $pack_id            = $item_number;
                    $is_recurring       = true;
                    $status             = 'subscr_payment';
                } else {
                    $this->subscription_cancel( $custom->user_id );
                }


            } else if ( isset( $postdata['txn_type'] ) && ( $postdata['txn_type'] == 'web_accept' ) && ( strtolower( $postdata['payment_status'] ) == 'completed' ) ){

                //verify payment
                $verified = $this->validateIpn();
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

                if ( $verified || $this->test_mode ) {
                    $insert_payment = true;
                }

            } // payment type

            if ( $insert_payment ) {
                $data = array(
                    'user_id'          => (int) $custom->user_id,
                    'status'           => $status,
                    'cost'             => $postdata['mc_gross'],
                    'post_id'          => $post_id,
                    'pack_id'          => $pack_id,
                    'payer_first_name' => $postdata['first_name'],
                    'payer_last_name'  => $postdata['last_name'],
                    'payer_email'      => $postdata['payer_email'],
                    'payment_type'     => 'Paypal',
                    'payer_address'    => $postdata['residence_country'],
                    'transaction_id'   => $postdata['txn_id'],
                    'created'          => current_time( 'mysql' ),
                    'profile_id'       => isset( $postdata['subscr_id'] ) ? $postdata['subscr_id'] : null,
                );

                $transaction_id = wp_strip_all_tags( $postdata['txn_id'] );
                WPUF_Payment::insert_payment( $data, $transaction_id, $is_recurring );

                if ( $coupon_id ) {
                    $pre_usage = get_post_meta( $post_id, '_coupon_used', true );
                    $new_use = $pre_usage + 1;
                    update_post_meta( $post_id, '_coupon_used', $coupon_id );
                }

                delete_user_meta( $custom->user_id, '_wpuf_user_active' );
                delete_user_meta( $custom->user_id, '_wpuf_activation_key' );
            }
        }
    }

    /**
     * Validate the IPN notification
     *
     * @param none
     * @return boolean
     */
    public function validateIpn() {
        global $wp_version;

        $this->set_mode();

        // Get recieved values from post data
        $ipn_data = (array) stripslashes_deep( $_POST );
        $ipn_data['cmd'] = '_notify-validate';

        // Send back post vars to paypal
        $params = array(
            'body'       => $ipn_data,
            'sslverify'  => false,
            'timeout'    => 30,
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
        );

        $response = wp_remote_post( $this->gateway_url, $params );

        if ( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && (strcmp( $response['body'], "VERIFIED" ) == 0) ) {
            return true;
        } else {
            return false;
        }
    }
}

$wpuf_paypal = new WPUF_Paypal();