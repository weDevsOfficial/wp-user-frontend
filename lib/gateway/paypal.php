<?php

/**
 * WP User Frotnend Paypal gateway
 *
 * @since 0.8
 * @package WP User Frontend
 */
class WPUF_Paypal {

    private $gateway_url;
    private $test_mode;

    function __construct() {
        $this->gateway_url = 'https://www.paypal.com/webscr/';
        $this->test_mode = false;

        add_action( 'wpuf_gateway_paypal', array($this, 'prepare_to_send') );
        add_action( 'wpuf_options_payment', array($this, 'payment_options') );
        add_action( 'init', array($this, 'paypal_success') );
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
            'label' => __( 'Paypal Email', 'wpuf' )
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

        $listener_url = add_query_arg( 'action', 'wpuf_paypal_success', home_url( '/' ) );
        $return_url = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( wpuf_get_option( 'payment_success' ) ) );

        $paypal_args = array(
            'cmd' => '_xclick',
            'amount' => $data['price'],
            'business' => wpuf_get_option( 'paypal_email' ),
            'item_name' => $data['item_name'],
            'item_number' => $data['item_number'],
            'email' => $data['user_info']['email'],
            'no_shipping' => '1',
            'no_note' => '1',
            'currency_code' => $data['currency'],
            'charset' => 'UTF-8',
            'custom' => $data['type'],
            'rm' => '2',
            'return' => $return_url,
            'notify_url' => $listener_url,
            'cbt' => sprintf( __( 'Click here to complete the purchase on %s', 'wpuf' ), get_bloginfo( 'name' ) )
        );

        $this->set_mode();

        $paypal_url = $this->gateway_url . '?' . http_build_query( $paypal_args );

        wp_redirect( $paypal_url );
        exit;
    }

    /**
     * Set the payment mode to sandbox or live
     *
     * @since 0.8
     */
    function set_mode() {
        if ( wpuf_get_option( 'sandbox_mode' ) == 'on' ) {
            $this->gateway_url = 'https://www.sandbox.paypal.com/webscr/';
            $this->test_mode = true;
        }
    }

    /**
     * Handle the payment info sent from paypal
     *
     * @since 0.8
     */
    function paypal_success() {

        if ( isset( $_GET['action'] ) && $_GET['action'] == 'wpuf_paypal_success' ) {
            $postdata = $_POST;

            //var_dump( $postdata );exit;

            $type = $postdata['custom'];
            $item_number = $postdata['item_number'];
            $amount = $postdata['mc_gross'];
            $payment_status = strtolower( $postdata['payment_status'] );

            //verify payment
            $verified = $this->validateIpn();

            switch ($type) {
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
                $data = array(
                    'user_id' => get_current_user_id(),
                    'status' => 'completed',
                    'cost' => $postdata['mc_gross'],
                    'post_id' => $post_id,
                    'pack_id' => $pack_id,
                    'payer_first_name' => $postdata['first_name'],
                    'payer_last_name' => $postdata['last_name'],
                    'payer_email' => $postdata['payer_email'],
                    'payment_type' => 'Paypal',
                    'payer_address' => $postdata['residence_country'],
                    'transaction_id' => $postdata['txn_id'],
                    'created' => current_time( 'mysql' )
                );

                WPUF_Payment::insert_payment( $data, $postdata['txn_id'] );
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

        $this->set_mode();

        // Get recieved values from post data
        $ipn_data = (array) stripslashes_deep( $_POST );
        $ipn_data['cmd'] = '_notify-validate';

        // Send back post vars to paypal
        $params = array(
            'body' => $ipn_data,
            'sslverify' => false,
            'timeout' => 30,
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
        );

        $response = wp_remote_post( $this->gateway_url, $params );

        if ( !is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && (strcmp( $response['body'], "VERIFIED" ) == 0) ) {
            return true;
        } else {
            WPUF_Main::log( 'error', "IPN Failed\n" . $ipn_response );
            return false;
        }
    }

}

$wpuf_paypal = new WPUF_Paypal();