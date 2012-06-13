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
        $this->gateway_url = 'https://www.paypal.com/cgi-bin/webscr/?';
        $this->test_mode = false;

        add_action( 'wpuf_gateway_paypal', array($this, 'prepare_to_send') );
        add_action( 'init', array($this, 'paypal_success') );
    }

    /**
     * Prepare the payment form and send to paypal
     *
     * @since 0.8
     * @param array $data payment info
     */
    function prepare_to_send( $data ) {

        $listener_url = add_query_arg( 'action', 'wpuf_paypal_success', home_url( '/' ) );
        $return_url = add_query_arg( 'action', 'wpuf_paypal_success', get_permalink( get_option( 'wpuf_sub_pay_thank_page' ) ) );

        $paypal_args = array(
            'cmd' => '_xclick',
            'amount' => $data['price'],
            'business' => $data['email'],
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

        $paypal_url = $this->gateway_url . http_build_query( $paypal_args );

        wp_redirect( $paypal_url );
        exit;
    }

    /**
     * Set the payment mode to sandbox or live
     *
     * @since 0.8
     */
    function set_mode() {
        if ( get_option( 'wpuf_sub_paypal_sandbox', 'yes' ) == 'yes' ) {
            $this->gateway_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
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

        // parse the paypal URL
        $urlParsed = parse_url( $this->gateway_url );

        // generate the post string from the _POST vars
        $postString = '';
        $ipn_data = array();
        $ipn_response = '';

        foreach ($_POST as $field => $value) {
            $ipn_data[$field] = $value;
            $postString .= $field . '=' . urlencode( stripslashes( $value ) ) . '&';
        }

        $postString .="cmd=_notify-validate"; // append ipn command
        // open the connection to paypal
        $fp = fsockopen( $urlParsed['host'], "80", $errNum, $errStr, 30 );

        if ( !$fp ) {
            // Could not open the connection, log error if enabled
            return false;
        } else {
            // Post the data back to paypal

            fputs( $fp, "POST {$urlParsed['path']} HTTP/1.1\r\n" );
            fputs( $fp, "Host: {$urlParsed['host']}\r\n" );
            fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n" );
            fputs( $fp, "Content-length: " . strlen( $postString ) . "\r\n" );
            fputs( $fp, "Connection: close\r\n\r\n" );
            fputs( $fp, $postString . "\r\n\r\n" );

            // loop through the response from the server and append to variable
            while (!feof( $fp )) {
                $ipn_response .= fgets( $fp, 1024 );
            }

            fclose( $fp ); // close connection
        }

        if ( strpos( $ipn_response, "VERIFIED" ) !== false ) {
            return true;
        } else {
            WPUF_Main::log( 'error', "IPN Failed\n" . $ipn_response );
            return false;
        }
    }

}

$wpuf_paypal = new WPUF_Paypal();