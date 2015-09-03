<?php

/**
 * WP User Frontend payment gateway handler
 *
 * @since 0.8
 * @package WP User Frontend
 */
class WPUF_Payment {

    function __construct() {
        add_action( 'init', array($this, 'send_to_gateway') );
        add_action( 'wpuf_payment_received', array($this, 'payment_notify_admin') );
        add_filter( 'the_content', array($this, 'payment_page') );
    }

    public static function get_payment_gateways() {

        // default, built-in gateways
        $gateways = array(
            'paypal' => array(
                'admin_label'    => __( 'PayPal', 'wpuf' ),
                'checkout_label' => __( 'PayPal', 'wpuf' ),
                'icon'           => apply_filters( 'wpuf_paypal_checkout_icon', WPUF_ASSET_URI . '/images/paypal.png' )
             ),
            'bank' => array(
                'admin_label'    => __( 'Bank Payment', 'wpuf' ),
                'checkout_label' => __( 'Bank Payment', 'wpuf' ),
            )
        );

        $gateways = apply_filters( 'wpuf_payment_gateways', $gateways );

        return $gateways;
    }

    /**
     * Get active payment gateways
     *
     * @return array
     */
    function get_active_gateways() {
        $all_gateways    = wpuf_get_gateways( 'checkout' );
        $active_gateways = wpuf_get_option( 'active_gateways', 'wpuf_payment' );
        $active_gateways = is_array( $active_gateways ) ? $active_gateways : array();
        $gateways        = array();

        foreach ($all_gateways as $id => $label) {
            if ( array_key_exists( $id, $active_gateways ) ) {
                $gateways[$id] = $label;
            }
        }

        return $gateways;
    }

    /**
     * Show the payment page
     *
     * @param  string $content
     * @return string
     */
    function payment_page( $content ) {
        global $post;

        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpuf_pay' && $pay_page == 0 ) {
            _e('Please select your payment page from admin panel', 'wpuf' );
            return;
        }

        if ( $post->ID == $pay_page && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wpuf_pay' ) {

            if ( !is_user_logged_in() ) {
                //return __( 'You are not logged in', 'wpuf' );
            }

            $type    = ( $_REQUEST['type'] == 'post' ) ? 'post' : 'pack';
            $post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
            $pack_id = isset( $_REQUEST['pack_id'] ) ? intval( $_REQUEST['pack_id'] ) : 0;
            $is_free = false;

            if ( $pack_id ) {
                $pack_detail = WPUF_Subscription::get_subscription( $pack_id );
                if ( empty( $pack_detail->meta_value['billing_amount'] ) ||  $pack_detail->meta_value['billing_amount'] <= 0) {
                    $is_free = true;
                }
            }

            $gateways = $this->get_active_gateways();

            if ( isset( $_REQUEST['wpuf_payment_submit'] ) ) {
                $selected_gateway = $_REQUEST['wpuf_payment_method'];
            } else {
                $selected_gateway = 'paypal';
            }

            ob_start();

            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
            } else {
                $user_id      = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;
                $current_user = get_userdata( $user_id );
            }

            if ( $pack_id && $is_free ) {

                $wpuf_subscription = WPUF_Subscription::init();

                if ( ! WPUF_Subscription::has_used_free_pack( $current_user->ID, $pack_id) ) {

                    $wpuf_subscription->new_subscription( $current_user->ID, $pack_id, null, false, 'free' );
                    WPUF_Subscription::add_used_free_pack( $current_user->ID, $pack_id );

                    $message = apply_filters( 'wpuf_fp_activated_msg', __( 'Your free package has been activated. Enjoy!' ), 'wpuf' );
                } else {
                    $message = apply_filters( 'wpuf_fp_activated_error', __( 'You already have activated a free package previously.' ), 'wpuf' );
                }
                ?>
                    <div class="wpuf-info"><?php echo $message; ?></div>
                <?php
            } else {
                ?>
                <?php if ( count( $gateways ) ) {
                   ?>
                    <form id="wpuf-payment-gateway" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">

                        <?php if ( $pack_id ) {
                        $pack         = WPUF_Subscription::init()->get_subscription( $pack_id );
                        $details_meta = WPUF_Subscription::init()->get_details_meta_value();
                        $currency = wpuf_get_option( 'currency_symbol', 'wpuf_payment' );
                        if ( is_user_logged_in() ) {
                            ?>
                            <input type="hidden" name="user_id" value="<?php echo $current_user->ID; ?>">
                            <?php } ?>

                            <div class="wpuf-coupon-info-wrap">
                                <div class="wpuf-coupon-info">
                                    <div class="wpuf-pack-info">
                                        <h3>
                                            <?php _e( 'Pricing & Plans', 'wpuf' ); ?>

                                            <a href="<?php echo wpuf_get_subscription_page_url(); ?>"><?php _e( 'Change Pack', 'wpuf' ); ?></a>
                                        </h3>
                                        <div class="wpuf-subscription-error"></div>

                                        <div class="wpuf-pack-inner">
                                            <?php if ( class_exists( 'WPUF_Coupons' ) ) { ?>
                                                <?php echo WPUF_Coupons::init()->after_apply_coupon( $pack ); ?>
                                            <?php } else {

                                                $currency = wpuf_get_option( 'currency_symbol', 'wpuf_payment' );
                                                ?>
                                                <div><?php _e( 'Selected Pack ', 'wpuf' ); ?>: <strong><?php echo $pack->post_title; ?></strong></div>
                                                <?php _e( 'Pack Price ', 'wpuf' ); ?>: <strong><?php echo $currency . $pack->meta_value['billing_amount']; ?></strong>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if ( class_exists( 'WPUF_Coupons' ) ) { ?>
                                <div class="wpuf-copon-wrap"  style="display:none;">
                                    <div class="wpuf-coupon-error" style="color: red;"></div>
                                    <input type="text" name="coupon_code" size="20" class="wpuf-coupon-field">
                                    <input type="hidden" name="coupon_id" size="20" class="wpuf-coupon-id-field">
                                    <div>
                                        <a href="#" data-pack_id="<?php echo $pack_id; ?>" class="wpuf-apply-coupon"><?php _e( 'Apply Coupon', 'wpuf' ); ?></a>
                                        <a href="#" data-pack_id="<?php echo $pack_id; ?>" class="wpuf-copon-cancel"><?php _e( 'Cancel', 'wpuf' ); ?></a>
                                    </div>
                                </div>
                                <a href="#" class="wpuf-copon-show"><?php _e( 'Have a discount code?', 'wpuf' ); ?></a>

                                <?php } // coupon ?>
                            </div>

                        <?php } ?>
                        <?php wp_nonce_field( 'wpuf_payment_gateway' ) ?>

                        <?php do_action( 'wpuf_before_payment_gateway' ); ?>

                        <p>
                            <label for="wpuf-payment-method"><?php _e( 'Choose Your Payment Method', 'wpuf' ); ?></label><br />

                            <ul class="wpuf-payment-gateways">
                                <?php foreach ($gateways as $gateway_id => $gateway) { ?>
                                    <li class="wpuf-gateway-<?php echo $gateway_id; ?>">
                                        <label>
                                            <input name="wpuf_payment_method" type="radio" value="<?php echo esc_attr( $gateway_id ); ?>" <?php checked( $selected_gateway, $gateway_id ); ?>>
                                            <?php
                                            echo $gateway['label'];

                                            if ( !empty( $gateway['icon'] ) ) {
                                                printf(' <img src="%s" alt="image">', $gateway['icon'] );
                                            }
                                            ?>
                                        </label>

                                        <div class="wpuf-payment-instruction" style="display: none;">
                                            <div class="wpuf-instruction"><?php echo wpuf_get_option( 'gate_instruct_' . $gateway_id, 'wpuf_payment' ); ?></div>

                                            <?php do_action( 'wpuf_gateway_form_' . $gateway_id, $type, $post_id, $pack_id ); ?>
                                        </div>
                                    </li>
                                <?php } ?>
                            </ul>

                        </p>
                        <?php do_action( 'wpuf_after_payment_gateway' ); ?>
                        <p>
                            <input type="hidden" name="type" value="<?php echo $type; ?>" />
                            <input type="hidden" name="action" value="wpuf_pay" />
                            <?php if ( $post_id ) { ?>
                                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
                            <?php } ?>

                            <?php if ( $pack_id ) { ?>
                                <input type="hidden" name="pack_id" value="<?php echo $pack_id; ?>" />
                            <?php } ?>
                            <input type="submit" name="wpuf_payment_submit" value="<?php _e( 'Proceed', 'wpuf' ); ?>"/>
                        </p>
                    </form>
                <?php } else { ?>
                    <?php _e( 'No Payment gateway found', 'wpuf' ); ?>
                <?php } ?>

                <?php
            }

            return ob_get_clean();
        }

        return $content;
    }

    /**
     * Send payment handler to the gateway
     *
     * This function sends the payment handler mechanism to the selected
     * gateway. If 'paypal' is selected, then a particular action is being
     * called. A  listener function can be invoked for that gateway to handle
     * the request and send it to the gateway.
     *
     * Need to use `wpuf_gateway_{$gateway_name}
     */
    function send_to_gateway() {

        if ( isset( $_POST['wpuf_payment_submit'] ) && $_POST['action'] == 'wpuf_pay' && wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_payment_gateway' ) ) {

            $post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;
            $pack_id = isset( $_REQUEST['pack_id'] ) ? intval( $_REQUEST['pack_id'] ) : 0;
            $gateway = $_POST['wpuf_payment_method'];
            $type    = $_POST['type'];

            if ( is_user_logged_in() ) {
                $userdata = wp_get_current_user();
            } else {
                $user_id = isset( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : 0;

                if ( $user_id ) {
                    $userdata = get_userdata( $user_id );
                } else {
                    $userdata             = new stdClass;
                    $userdata->ID         = 0;
                    $userdata->user_email = '';
                    $userdata->first_name = '';
                    $userdata->last_name  = '';
                }
            }


            switch ($type) {
                case 'post':
                    $post        = get_post( $post_id );
                    $amount      = wpuf_get_option( 'cost_per_post', 'wpuf_payment' );
                    $item_number = get_post_meta( $post_id, '_wpuf_order_id', true );
                    $item_name   = $post->post_title;
                    break;

                case 'pack':

                    $pack        = WPUF_Subscription::init()->get_subscription( $pack_id );

                    $custom      = $pack->meta_value;
                    $amount      = $this->coupon_discount( $_POST['coupon_code'], $pack->meta_value['billing_amount'], $pack_id );
                    $item_name   = $pack->post_title;
                    $item_number = $pack->ID;
                    break;
            }

            $payment_vars = array(
                'currency'    => wpuf_get_option( 'currency', 'wpuf_payment' ),
                'price'       => $amount,
                'item_number' => $item_number,
                'item_name'   => $item_name,
                'type'        => $type,
                'user_info' => array(
                    'id'         => $userdata->ID,
                    'email'      => $userdata->user_email,
                    'first_name' => $userdata->first_name,
                    'last_name'  => $userdata->last_name
                ),
                'date'      => date( 'Y-m-d H:i:s' ),
                'post_data' => $_POST,
                'custom'    => isset( $custom ) ? $custom : '',
            );

            do_action( 'wpuf_gateway_' . $gateway, $payment_vars );
        }
    }

    function coupon_discount( $coupon_code, $amount, $pack_id ) {
        if ( empty( $coupon_code ) ) {
            return $amount;
        }

        $coupon       = get_page_by_title( $coupon_code, 'OBJECT', 'wpuf_coupon' );
        $coupon_meta  = WPUF_Coupons::init()->get_coupon_meta( $coupon->ID );
        $coupon_usage = get_post_meta( $coupon->ID, 'coupon_usage',  true );
        $coupon_usage = count( $coupon_usage );
        $start_date   = date( 'Y-d-m', strtotime( $coupon_meta['start_date'] ) );
        $end_date     = date( 'Y-d-m', strtotime( $coupon_meta['end_date'] ) );
        $today        = date( 'Y-d-m', time() );
        $current_use_email = wp_get_current_user()->user_email;

        if ( empty( $coupon_meta['amount'] ) || $coupon_meta['amount'] == 0 ) {
            return $amount;
        }

        if ( $coupon_meta['package'] != 'all' && $coupon_meta['package'] != $pack_id ) {
            return $amount;
        }

        if ( $coupon_meta['usage_limit'] < $coupon_usage ) {
            return $amount;
        }

        if ( $start_date > $today && $today > $end_date ) {
            return $amount;
        }

        if ( count( $coupon_meta['access'] ) && !in_array( $current_use_email, $coupon_meta['access'] ) ) {
            return $amount;
        }

        if ( $coupon_meta['type'] == 'amount' ) {

            $new_amount = $amount -  $coupon_meta['amount'];
        } else {
            $new_amount = ( $amount * $coupon_meta['amount'] ) / 100;
        }

        if ( $new_amount >= 0 ) {
            return $new_amount;
        }

        return $amount;

    }

    /**
     * Insert payment info to database
     *
     * @global object $wpdb
     * @param array $data payment data to insert
     * @param int $transaction_id the transaction id in case of update
     */
    public static function insert_payment( $data, $transaction_id = 0, $recurring = false ) {
        global $wpdb;

        //check if it's already there
        $sql = "SELECT transaction_id
                FROM " . $wpdb->prefix . "wpuf_transaction
                WHERE transaction_id = '" . $wpdb->escape( $transaction_id ) . "' LIMIT 1";

        $result = $wpdb->get_row( $sql );

        if ( $recurring != false ) {
            $profile_id = $data['profile_id'];
        }

        if ( isset( $data['profile_id'] ) || empty( $data['profile_id'] ) ) {
            unset( $data['profile_id'] );
        }

        if ( !$result ) {
            $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', $data );
        } else {
            $wpdb->update( $wpdb->prefix . 'wpuf_transaction', $data, array('transaction_id' => $transaction_id) );
        }

        if( isset( $profile_id ) ) {
            $data['profile_id'] = $profile_id;
        }

        do_action( 'wpuf_payment_received', $data, $recurring );
    }

    /**
     * Send payment received mail
     *
     * @param array $info payment information
     */
    function payment_notify_admin( $info ) {
        $headers = "From: " . get_bloginfo( 'name' ) . " <" . get_bloginfo( 'admin_email' ) . ">" . "\r\n\\";
        $subject = sprintf( __( '[%s] Payment Received', 'wpuf' ), get_bloginfo( 'name' ) );
        $msg = sprintf( __( 'New payment received at %s', 'wpuf' ), get_bloginfo( 'name' ) );

        $receiver = get_bloginfo( 'admin_email' );
        wp_mail( $receiver, $subject, $msg, $headers );
    }

}