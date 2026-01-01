<?php

namespace WeDevs\Wpuf\Frontend;

use stdClass;
use WeDevs\Wpuf\Admin;
use WeDevs\Wpuf\Ajax;
use WeDevs\Wpuf\WPUF_User;
use WP_Post;

/**
 * WP User Frontend payment gateway handler
 *
 * @since 0.8
 */
class Payment {

    public function __construct() {
        add_action( 'init', [ $this, 'send_to_gateway' ] );
        add_action( 'wpuf_payment_received', [ $this, 'payment_notify_admin' ] );
        add_action( 'wpuf_payment_received', [ $this, 'payment_notify_user' ] );
        add_filter( 'the_content', [ $this, 'payment_page' ] );
        add_action( 'init', [ $this, 'handle_cancel_payment' ] );
    }

    public static function get_payment_gateways() {
        // default, built-in gateways
        $gateways = [
            'paypal' => [
                'admin_label'    => __( 'PayPal', 'wp-user-frontend' ),
                'checkout_label' => __( 'PayPal', 'wp-user-frontend' ),
                'icon'           => apply_filters( 'wpuf_paypal_checkout_icon', WPUF_ASSET_URI . '/images/paypal.png' ),
            ],
            'bank'   => [
                'admin_label'    => __( 'Bank Payment', 'wp-user-frontend' ),
                'checkout_label' => __( 'Bank Payment', 'wp-user-frontend' ),
            ],
        ];
        $gateways = apply_filters( 'wpuf_payment_gateways', $gateways );

        return $gateways;
    }

    /**
     * Get active payment gateways
     *
     * @return array
     */
    public function get_active_gateways() {
        $all_gateways    = wpuf_get_gateways( 'checkout' );
        $active_gateways = wpuf_get_option( 'active_gateways', 'wpuf_payment' );
        $active_gateways = is_array( $active_gateways ) ? $active_gateways : [];
        $gateways        = [];
        foreach ( $all_gateways as $id => $label ) {
            if ( array_key_exists( $id, $active_gateways ) ) {
                $gateways [ $id ] = $label;
            }
        }

        return $gateways;
    }

    /**
     * Show the payment page
     *
     * @param string $content
     *
     * @return string|void
     */
    public function payment_page( $content ) {
        global $post;

        if ( ! ( $post instanceof WP_Post ) ) {
            return $content;
        }

        $pay_page       = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        $billing_amount = 0;
        $action   = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
        $get_type = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
        $type     = ( $get_type === 'post' ) ? 'post' : 'pack';
        if ( ! is_user_logged_in() && $action === 'wpuf_pay' && $type !== 'post' ) {
            /* translators: %s: login url */
            printf( esc_html( __( 'This page is restricted. Please %s to view this page.', 'wp-user-frontend' ) ),
                    wp_loginout( '', false ) );

            return;
        }
        if ( $action === 'wpuf_pay' && 0 === intval( $pay_page ) ) {
            esc_html_e( 'Please select your payment page from admin panel', 'wp-user-frontend' );

            return;
        }
        if ( $post->ID === $pay_page && 'wpuf_pay' === $action ) {
            $post_id = isset( $_REQUEST['post_id'] ) ? intval( wp_unslash( $_REQUEST['post_id'] ) ) : 0;
            $pack_id = isset( $_REQUEST['pack_id'] ) ? intval( wp_unslash( $_REQUEST['pack_id'] ) ) : 0;
            $is_free = false;
            if ( $pack_id ) {
                $pack_detail = wpuf()->subscription->get_subscription( $pack_id );
                if ( ! $pack_detail ) {
                    ?>
                    <div class="wpuf-info"><?php esc_html_e( 'No subscription pack found.',
                                                             'wp-user-frontend' ); ?></div>
                    <?php
                    return;
                }
                $recurring_pay = isset( $pack_detail->meta_value['recurring_pay'] ) ? $pack_detail->meta_value['recurring_pay'] : 'no';
                if ( empty( $pack_detail->meta_value['billing_amount'] ) || $pack_detail->meta_value['billing_amount'] <= 0 ) {
                    $is_free = true;
                }
            }
            $gateways = $this->get_active_gateways();
            if ( isset( $_REQUEST['wpuf_payment_submit'] ) ) {
                $selected_gateway = isset( $_REQUEST['wpuf_payment_method'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpuf_payment_method'] ) ) : '';
            } else {
                $selected_gateway = 'paypal';
            }
            ob_start();
            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();
            } else {
                $user_id      = isset( $_GET['user_id'] ) ? intval( wp_unslash( $_GET['user_id'] ) ) : 0;
                $current_user = get_userdata( $user_id );
            }
            if ( $pack_id && $is_free ) {
                $wpuf_subscription = wpuf()->subscription;
                $wpuf_user         = new WPUF_User( $current_user->ID );
                if ( ! $wpuf_user->subscription()->used_free_pack( $pack_id ) ) {
                    wpuf_get_user( $current_user->ID )->subscription()->add_pack( $pack_id, NULL, false, 'Free' );
                    $wpuf_user->subscription()->add_free_pack( $current_user->ID, $pack_id );
                    $message = apply_filters( 'wpuf_fp_activated_msg',
                                              __( 'Your Free package has been activated. Enjoy!',
                                                  'wp-user-frontend' ) );
                } else {
                    $message = apply_filters( 'wpuf_fp_activated_error',
                                              __( 'You already have activated a Free package previously.',
                                                  'wp-user-frontend' ) );
                }
                ?>
                <div class="wpuf-info"><?php echo esc_html( $message ); ?></div>
                <?php
            } else {
                ?>
                <?php
                if ( count( $gateways ) ) {
                    ?>
                    <div class="wpuf-payment-page-wrap wpuf-pay-row">
                        <?php $pay_page_style = ''; ?>
                        <div class="wpuf-bill-addr-wrap wpuf-pay-col">
                            <?php
                            if ( wpuf_get_option( 'show_address', 'wpuf_address_options',
                                                  false ) && is_user_logged_in() ) {
                                $pay_page_style = 'vertical-align:top; margin-left: 20px; display: inline-block;';
                                ?>
                                <div class="wpuf-bill-addr-info">
                                    <h3> <?php esc_html_e( 'Billing Address', 'wp-user-frontend' ); ?> </h3>
                                    <div class="wpuf-bill_addr-inner">
                                        <?php
                                        $add_form = new Ajax\Address_Form_Ajax();
                                        $add_form->wpuf_ajax_address_form();
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="wpuf-payment-gateway-wrap" style="<?php echo esc_attr( $pay_page_style ); ?>">
                            <form id="wpuf-payment-gateway" action="" method="POST">

                                <?php
                                if ( $pack_id ) {
                                    $pack         = wpuf()->subscription->get_subscription( $pack_id );
                                    $details_meta = wpuf()->subscription->get_details_meta_value();
                                    $currency     = wpuf_get_currency( 'symbol' );
                                    if ( is_user_logged_in() ) {
                                        ?>
                                        <input type="hidden" name="user_id"
                                               value="<?php echo esc_attr( $current_user->ID ); ?>">
                                        <?php
                                    }
                                    ?>

                                    <div class="wpuf-coupon-info-wrap wpuf-pay-col">
                                        <div class="wpuf-coupon-info">
                                            <div class="wpuf-pack-info">
                                                <h3 class="wpuf-pay-col">
                                                    <?php esc_html_e( 'Pricing & Plans', 'wp-user-frontend' ); ?>

                                                    <a style="white-space: nowrap"
                                                       href="<?php echo esc_attr( wpuf_get_subscription_page_url() ); ?>"><?php esc_html_e( 'Change Pack',
                                                                                                                                            'wp-user-frontend' ); ?></a>
                                                </h3>
                                                <div class="wpuf-subscription-error"></div>
                                                <div class="wpuf-subscription-success"></div>

                                                <div class="wpuf-pack-inner">

                                                    <?php
                                                    if ( class_exists( 'WeDevs\Wpuf\Pro\Coupons' ) ) {
                                                        echo wp_kses_post( wpuf_pro()->coupons->after_apply_coupon( $pack ) );
                                                    } else {
                                                        $pack_cost      = $pack->meta_value['billing_amount'];
                                                        $billing_amount = apply_filters( 'wpuf_payment_amount',
                                                                                         $pack->meta_value['billing_amount'] );
                                                        ?>
                                                        <div><?php esc_html_e( 'Selected Pack',
                                                                               'wp-user-frontend' ); ?>:
                                                            <strong><?php echo esc_attr( $pack->post_title ); ?></strong>
                                                        </div>
                                                        <div><?php esc_html_e( 'Pack Price', 'wp-user-frontend' ); ?>:
                                                            <strong><span
                                                                    id="wpuf_pay_page_cost"><?php echo esc_attr( wpuf_format_price( $pack_cost ) ); ?>
                                                            </strong></span></div>

                                                        <?php do_action( 'wpuf_before_pack_payment_total' ); ?>

                                                        <div><?php esc_html_e( 'Total', 'wp-user-frontend' ); ?>:
                                                            <strong><span
                                                                    id="wpuf_pay_page_total"><?php echo esc_attr( wpuf_format_price( $billing_amount ) ); ?>
                                                            </strong></span></div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ( class_exists( 'WeDevs\Wpuf\Pro\Coupons' ) ) { ?>
                                            <div class="wpuf-copon-wrap" style="display:none;">
                                                <div class="wpuf-coupon-error" style="color: red;"></div>
                                                <input type="text" name="coupon_code" size="20"
                                                       class="wpuf-coupon-field">
                                                <input type="hidden" name="coupon_id" size="20"
                                                       class="wpuf-coupon-id-field">
                                                <div>
                                                    <a href="#" data-pack_id="<?php echo esc_attr( $pack_id ); ?>"
                                                       class="wpuf-apply-coupon"><?php esc_html_e( 'Apply Coupon',
                                                                                                   'wp-user-frontend' ); ?></a>
                                                    <a href="#" data-pack_id="<?php echo esc_attr( $pack_id ); ?>"
                                                       class="wpuf-copon-cancel"><?php esc_html_e( 'Cancel',
                                                                                                   'wp-user-frontend' ); ?></a>
                                                </div>
                                            </div>
                                            <a href="#"
                                               class="wpuf-copon-show"><?php esc_html_e( 'Have a discount code?',
                                                                                         'wp-user-frontend' ); ?></a>

                                        <?php } ?>
                                    </div>

                                    <?php
                                }
                                if ( $post_id ) {
                                    $form              = new Admin\Forms\Form(
                                        get_post_meta(
                                            $post_id, '_wpuf_form_id', true
                                        )
                                    );
                                    $force_pack        = $form->is_enabled_force_pack();
                                    $pay_per_post      = $form->is_enabled_pay_per_post();
                                    $fallback_enabled  = $form->is_enabled_fallback_cost();
                                    $fallback_cost     = (float) $form->get_subs_fallback_cost();
                                    $pay_per_post_cost = (float) $form->get_pay_per_post_cost();
                                    $current_user      = wpuf_get_user();
                                    $current_pack = $current_user->subscription()->current_pack();
                                    if ( $force_pack && ! is_wp_error( $current_pack ) && $fallback_enabled ) {
                                        $post_cost      = $fallback_cost;
                                        $billing_amount = apply_filters( 'wpuf_payment_amount', $fallback_cost, $post_id );
                                    } else {
                                        $post_cost      = $pay_per_post_cost;
                                        $billing_amount = apply_filters( 'wpuf_payment_amount', $pay_per_post_cost, $post_id );
                                    }
                                    ?>
                                    <div id="wpuf_type" style="display: none"><?php echo 'post'; ?></div>
                                    <div id="wpuf_id" style="display: none"><?php echo esc_attr( $post_id ); ?></div>
                                    <div><?php esc_html_e( 'Post cost', 'wp-user-frontend' ); ?>: <strong><span
                                                id="wpuf_pay_page_cost"><?php echo esc_attr( wpuf_format_price( $post_cost ) ); ?>
                                        </strong></span></div>

                                    <?php do_action( 'wpuf_before_pack_payment_total' ); ?>

                                    <div><?php esc_html_e( 'Total', 'wp-user-frontend' ); ?>: <strong><span
                                                id="wpuf_pay_page_total"><?php echo esc_html( wpuf_format_price( $billing_amount ) ); ?>
                                        </strong></span></div>
                                    <?php
                                }
                                ?>
                                <?php wp_nonce_field( 'wpuf_payment_gateway' ); ?>

                                <?php do_action( 'wpuf_before_payment_gateway' ); ?>

                                <p>
                                    <label for="wpuf-payment-method"><?php esc_html_e( 'Choose Your Payment Method',
                                                                                       'wp-user-frontend' ); ?></label><br/>

                                <ul class="wpuf-payment-gateways">
                                    <?php foreach ( $gateways as $gateway_id => $gateway ) { ?>
                                        <li class="wpuf-gateway-<?php echo esc_attr( $gateway_id ); ?>">
                                            <label>
                                                <input name="wpuf_payment_method" type="radio"
                                                       value="<?php echo esc_attr( $gateway_id ); ?>" <?php checked( $selected_gateway,
                                                                                                                     $gateway_id ); ?>>
                                                <?php
                                                echo esc_html( $gateway['label'] );
                                                if ( ! empty( $gateway['icon'] ) ) {
                                                    printf( ' <img src="%s" alt="image">',
                                                            wp_kses_post( $gateway['icon'] ) );
                                                }
                                                ?>
                                            </label>

                                            <div class="wpuf-payment-instruction" style="display: none;">
                                                <div
                                                    class="wpuf-instruction"><?php echo wp_kses_post( wpuf_get_option( 'gate_instruct_' . esc_html( $gateway_id ),
                                                                                                                       'wpuf_payment' ) ); ?></div>

                                                <?php do_action( 'wpuf_gateway_form_' . $gateway_id, $type, $post_id,
                                                                 $pack_id ); ?>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                                </p>
                                <?php do_action( 'wpuf_after_payment_gateway' ); ?>
                                <p>
                                    <input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>"/>
                                    <input type="hidden" name="action" value="wpuf_pay"/>
                                    <?php if ( $post_id ) { ?>
                                        <input type="hidden" name="post_id"
                                               value="<?php echo esc_attr( $post_id ); ?>"/>
                                    <?php } ?>

                                    <?php if ( $pack_id ) { ?>
                                        <input type="hidden" name="pack_id"
                                               value="<?php echo esc_attr( $pack_id ); ?>"/>
                                        <input type="hidden" name="recurring_pay"
                                               value="<?php echo esc_attr( $recurring_pay ); ?>"/>
                                    <?php } ?>
                                    <input type="submit" name="wpuf_payment_submit" class="wpuf-btn"
                                           value="<?php esc_html_e( 'Proceed', 'wp-user-frontend' ); ?>"/>
                                </p>
                            </form>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <?php esc_html_e( 'No Payment gateway found', 'wp-user-frontend' ); ?>
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
    public function send_to_gateway() {
        $action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
        $nonce  = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( $action !== 'wpuf_pay' || empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wpuf_payment_gateway' ) ) {
            return;
        }

        $post_id      = isset( $_REQUEST['post_id'] ) ? intval( wp_unslash( $_REQUEST['post_id'] ) ) : 0;
        $pack_id      = isset( $_REQUEST['pack_id'] ) ? intval( wp_unslash( $_REQUEST['pack_id'] ) ) : 0;
        $gateway      = isset( $_REQUEST['wpuf_payment_method'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpuf_payment_method'] ) ) : '';
        $type         = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $current_user = wpuf_get_user();
        $current_pack = $current_user->subscription()->current_pack();
        $cost         = 0;
        if ( is_user_logged_in() ) {
            $userdata = wp_get_current_user();
        } else {
            $user_id = isset( $_REQUEST['user_id'] ) ? intval( wp_unslash( $_REQUEST['user_id'] ) ) : 0;
            if ( $user_id ) {
                $userdata = get_userdata( $user_id );
            } else if ( $type === 'post' && ! is_user_logged_in() ) {
                $post     = get_post( $post_id );
                $user_id  = $post->post_author;
                $userdata = get_userdata( $user_id );
            } else {
                $userdata             = new stdClass();
                $userdata->ID         = 0;
                $userdata->user_email = '';
                $userdata->first_name = '';
                $userdata->last_name  = '';
            }
        }

        switch ( $type ) {
            case 'post':
                $post          = get_post( $post_id );
                $form_id       = get_post_meta( $post_id, '_wpuf_form_id', true );
                $form          = new Admin\Forms\Form( $form_id );
                $form_settings = $form->get_settings();
                $force_pack    = $form->is_enabled_force_pack();
                $fallback_on   = $form->is_enabled_fallback_cost();
                $post_count    = $current_user->subscription()->has_post_count( $form_settings['post_type'] );
                if ( $force_pack && $fallback_on && ! is_wp_error( $current_pack ) && ! $post_count ) {
                    $amount = $form->get_subs_fallback_cost();
                } else {
                    $amount = $form->get_pay_per_post_cost();
                }
                $item_number = $post->ID;
                $item_name   = $post->post_title;
                break;
            case 'pack':
                $pack        = wpuf()->subscription->get_subscription( $pack_id );
                $custom      = $pack->meta_value;
                $cost        = $pack->meta_value['billing_amount'];
                $amount      = $cost;
                $item_name   = $pack->post_title;
                $item_number = $pack->ID;
                break;
        }
        $payment_vars = [
            'currency'            => wpuf_get_option( 'currency', 'wpuf_payment' ),
            'price'               => $amount,
            'item_number'         => $item_number,
            'item_name'           => $item_name,
            'type'                => $type,
            'user_info'           => [
                'id'         => $userdata->ID,
                'email'      => $userdata->user_email,
                'first_name' => $userdata->first_name,
                'last_name'  => $userdata->last_name,
            ],
            'date'                => gmdate( 'Y-m-d H:i:s' ),
            'post_data'           => $_POST,
            'custom'              => isset( $custom ) ? $custom : '',
            'wpuf_payment_method' => $gateway,
        ];
        if ( isset( $_POST['billing_address'] ) ) {
            $address_fields = array_map( 'sanitize_text_field', wp_unslash( $_POST['billing_address'] ) );
        } else {
            $address_fields = wpuf_get_user_address();
        }
        if ( ! empty( $address_fields ) ) {
            update_user_meta( $userdata->ID, 'wpuf_address_fields', $address_fields );
        }
        /**
         * Filter: wpuf_payment_vars
         *
         * @since 3.1.13
         */
        $payment_vars = apply_filters( 'wpuf_payment_vars', $payment_vars );
        do_action( 'wpuf_gateway_' . $gateway, $payment_vars );
    }

    /**
     * Insert payment info to database
     *
     * @param array   $data           payment data to insert
     * @param int     $transaction_id the transaction id in case of update
     *
     * @global object $wpdb
     */
    public static function insert_payment( $data, $transaction_id = 0, $recurring = false ) {
        global $wpdb;
        $user_id = get_current_user_id();
        //check if it's already there
        $result = $wpdb->get_row( $wpdb->prepare( 'SELECT transaction_id
            FROM ' . $wpdb->prefix . 'wpuf_transaction
            WHERE transaction_id = %s LIMIT 1', $transaction_id ) );
        if ( $recurring !== false ) {
            $profile_id = $data['profile_id'];
        }
        if ( isset( $data['profile_id'] ) || empty( $data['profile_id'] ) ) {
            unset( $data['profile_id'] );
        }
        if ( empty( $data['tax'] ) ) {
            $data['tax'] = floatval( $data['cost'] ) - floatval( $data['subtotal'] );
        }
        if ( wpuf_get_option( 'show_address', 'wpuf_address_options', false ) && ! empty( $data['user_id'] ) ) {
            $data['payer_address'] = wpuf_get_user_address( $data['user_id'] );
        }
        if ( ! empty( $data['payer_address'] ) ) {
            $data['payer_address'] = maybe_serialize( $data['payer_address'] );
        }
        if ( isset( $profile_id ) ) {
            $data['profile_id'] = $profile_id;
        }

        if ( ! $result ) {
            $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', $data );

            do_action( 'wpuf_payment_received', $data, $recurring );
        } else {
            $wpdb->update( $wpdb->prefix . 'wpuf_transaction', $data, [ 'transaction_id' => $transaction_id ] );
        }
        //workaround for subscriptions can't be assigned from user profile regression
        if ( ! did_action( 'wpuf_payment_received' ) ) {
            do_action( 'wpuf_payment_received', $data, $recurring );
        }
    }

    /**
     * Send payment received mail
     *
     * @param array $info payment information
     */
    public function payment_notify_admin( $info ) {
        $headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n\\";
        // translators: %s is site title name
        $subject = sprintf( __( '[%s] Payment Received', 'wp-user-frontend' ), get_bloginfo( 'name' ) );
        // translators: %s is site title name
        $msg = sprintf( __( 'New payment received at %s', 'wp-user-frontend' ), get_bloginfo( 'name' ) );
        $receiver = get_bloginfo( 'admin_email' );
        wp_mail( $receiver, $subject, $msg, $headers );
    }

    /**
     * Send payment confirmation mail to user
     *
     * @since 4.1.8
     *
     * @param array $info payment information
     */
    public function payment_notify_user( $info ) {
        // Validate user_id exists and is numeric
        if ( ! isset( $info['user_id'] ) || ! is_numeric( $info['user_id'] ) ) {
            return;
        }

        // Get user data
        $user = get_userdata( $info['user_id'] );

        if ( ! $user ) {
            return;
        }

        // Create a unique key for this notification to prevent duplicates
        $notification_key = 'wpuf_payment_notification_' . $info['user_id'] . '_' . $info['transaction_id'];

        // Check if notification has already been sent for this transaction
        if ( get_transient( $notification_key ) ) {
            return;
        }

        // Set transient to prevent duplicate notifications (expires in 1 hour)
        set_transient( $notification_key, true, HOUR_IN_SECONDS );

        // Check if Pro is available and invoices are enabled
        $enable_invoices = wpuf_get_option( 'enable_invoices', 'wpuf_payment_invoices', 'off' );
        $pro_available = class_exists( 'WeDevs\Wpuf\Pro\Admin\Invoice' );

        if ( $pro_available && wpuf_is_checkbox_or_toggle_on( $enable_invoices ) ) {
            // Pro is available and invoices are enabled - send invoice email only
            $this->wpuf_send_invoice( $info, $user );
        } else {
            // Pro not available or invoices disabled - send normal payment confirmation email
            $payment_type = $this->determine_user_payment_type( $info );
            $subject_msg = $this->get_user_notification_content( $payment_type, $user, $info );

            $subject = $subject_msg['subject'];
            $message = $subject_msg['message'];

            // Set proper headers
            $headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
            $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

            wp_mail( $user->user_email, $subject, $message, $headers );
        }

    }

    /**
     * Determine the payment type for user notification
     *
     * @since 4.1.8
     *
     * @param array $info payment information
     *
     * @return string payment type
     */
    private function determine_user_payment_type( $info ) {
        // Check if it's a trial payment (zero cost)
        if ( isset( $info['cost'] ) && 0 === intval( $info['cost'] ) ) {
            return 'trial';
        }

        // Check if it's a subscription/pack payment
        if ( isset( $info['pack_id'] ) && $info['pack_id'] > 0 ) {
            return 'subscription';
        }

        // Check if it's a post payment
        if ( isset( $info['post_id'] ) && $info['post_id'] > 0 ) {
            return 'post';
        }

        return 'general';
    }

    /**
     * Get notification content for user
     *
     * @since 4.1.8
     *
     * @param string $payment_type type of payment
     * @param \WP_User $user user object
     * @param array $info payment information
     *
     * @return array subject and message
     */
    private function get_user_notification_content( $payment_type, $user, $info ) {
        $site_name = get_bloginfo( 'name' );
        $amount = isset( $info['cost'] ) ? wpuf_format_price( $info['cost'] ) : '';

        switch ( $payment_type ) {
            case 'trial':
                // translators: %s is the site name
                $subject = sprintf( __( '[%s] Your Trial Subscription is Active', 'wp-user-frontend' ), $site_name );
                $message = sprintf(
                    // translators: %1$s is the user display name, %2$s is the site name
                    __( 'Hello %1$s,<br><br>Your trial subscription has been activated successfully at %2$s.<br><br>Thank you!', 'wp-user-frontend' ),
                    $user->display_name,
                    $site_name
                );
                break;

            case 'subscription':
                // translators: %s is the site name
                $subject = sprintf( __( '[%s] Payment Confirmation - Subscription', 'wp-user-frontend' ), $site_name );
                $message = sprintf(
                    // translators: %1$s is the user display name, %2$s is the payment amount, %3$s is the site name
                    __( 'Hello %1$s,<br><br>Thank you for your payment of %2$s for your subscription at %3$s.<br><br>Your subscription is now active.<br><br>Thank you!', 'wp-user-frontend' ),
                    $user->display_name,
                    $amount,
                    $site_name
                );
                break;

            case 'post':
                // translators: %s is the site name
                $subject = sprintf( __( '[%s] Payment Confirmation - Post Submission', 'wp-user-frontend' ), $site_name );
                $message = sprintf(
                    // translators: %1$s is the user display name, %2$s is the payment amount, %3$s is the site name
                    __( 'Hello %1$s,<br><br>Thank you for your payment of %2$s for post submission at %3$s.<br><br>Your post has been submitted successfully.<br><br>Thank you!', 'wp-user-frontend' ),
                    $user->display_name,
                    $amount,
                    $site_name
                );
                break;

            default:
                // translators: %s is the site name
                $subject = sprintf( __( '[%s] Payment Confirmation', 'wp-user-frontend' ), $site_name );
                $message = sprintf(
                    // translators: %1$s is the user display name, %2$s is the payment amount, %3$s is the site name
                    __( 'Hello %1$s,<br><br>Thank you for your payment of %2$s at %3$s.<br><br>Thank you!', 'wp-user-frontend' ),
                    $user->display_name,
                    $amount,
                    $site_name
                );
                break;
        }

        return [
            'subject' => $subject,
            'message' => $message
        ];
    }

    /**
     * Send invoice if invoices are enabled
     *
     * @since 4.1.8
     *
     * @param array $info payment information
     * @param \WP_User $user user object
     */
    private function wpuf_send_invoice( $info, $user ) {
        // Create a unique key for this invoice to prevent duplicates
        $invoice_key = 'wpuf_invoice_sent_' . $info['user_id'] . '_' . $info['transaction_id'];

        // Check if invoice has already been sent for this transaction
        if ( get_transient( $invoice_key ) ) {
            return;
        }

        try {
            // Generate and send invoice using the Pro Invoice class
            $this->generate_and_send_invoice( $info, $user );

            // Set transient to prevent duplicate invoices (expires in 24 hours)
            set_transient( $invoice_key, true, DAY_IN_SECONDS );
        } catch ( \Exception $e ) {
           return;
        }
    }

    /**
     * Generate and send invoice
     *
     * @since 4.1.8
     *
     * @param array $info payment information
     * @param \WP_User $user user object
     */
    private function generate_and_send_invoice( $info, $user ) {

        $invoicr_path = WP_CONTENT_DIR . '/plugins/wp-user-frontend-pro/lib/invoicr/invoicr.php';

        if ( ! file_exists( $invoicr_path ) ) {
            return;
        }

        require_once $invoicr_path;

        // Get invoice settings
        $inv_logo = wpuf_get_option( 'set_logo', 'wpuf_payment_invoices' );
        $inv_color = wpuf_get_option( 'set_color', 'wpuf_payment_invoices', '#e435226' );
        $inv_from_addr = wpuf_get_option( 'set_from_address', 'wpuf_payment_invoices' );
        $inv_from_addr = explode( '<br>', $inv_from_addr );
        $inv_title = wpuf_get_option( 'set_title', 'wpuf_payment_invoices' );
        $inv_para = wpuf_get_option( 'set_paragraph', 'wpuf_payment_invoices' );
        $inv_foot = wpuf_get_option( 'set_footernote', 'wpuf_payment_invoices' );
        $inv_filename = wpuf_get_option( 'set_filename', 'wpuf_payment_invoices', 'invoice' );

        // Prepare invoice data
        $inv_u_id = $info['user_id'];
        $inv_status = ! empty( $info['status'] ) ? $info['status'] : 'completed';
        $inv_subtotal = ! empty( $info['subtotal'] ) ? $info['subtotal'] : $info['cost'];
        $inv_cost = $info['cost'];
        $inv_id = ! empty( $info['transaction_id'] ) ? $info['transaction_id'] : uniqid();
        $inv_date = ! empty( $info['created'] ) ? wp_date( 'Y-m-d', strtotime( $info['created'] ) ) : wp_date( 'Y-m-d' );
        $inv_payment_type = ! empty( $info['payment_type'] ) ? $info['payment_type'] : 'Unknown';

        $currency = wpuf_get_option( 'currency', 'wpuf_payment', 'USD' );

        // Create invoice instance
        $invoice = new \invoicr( 'A4', $currency, 'en' );
        $invoice->setNumberFormat( '.', ',' );

        // Set logo if exists
        if ( $inv_logo && $this->is_invoice_image_exists( $inv_logo ) ) {
            $invoice->setLogo( $inv_logo, 100, 88 );
        }

        // Prepare "To" address
        $inv_to_addr = array();
        $inv_to_addr[] = $user->display_name;
        $inv_to_addr[] = $user->user_email;

        // Get item name based on payment type
        $item_name = $this->get_invoice_item_name( $info );

        // Set invoice details
        $invoice->setColor( $inv_color );
        $invoice->setType( __( 'Invoice', 'wp-user-frontend' ) );
        $invoice->setReference( $inv_id );
        $invoice->setDate( $inv_date );
        $invoice->setFrom( $inv_from_addr );
        $invoice->setTo( $inv_to_addr );
        $invoice->addItem( $item_name, false, $inv_subtotal, '0%', $inv_cost, false, $inv_subtotal );
        $invoice->addTotal( __( 'Subtotal', 'wp-user-frontend' ), $inv_subtotal );
        $invoice->addTotal( __( 'Payment Type', 'wp-user-frontend' ), $inv_payment_type );
        $invoice->addTotal( __( 'Total due', 'wp-user-frontend' ), $inv_cost, true );
        $invoice->addBadge( ucfirst( $inv_status ) );

        if ( $inv_title ) {
            $invoice->addTitle( $inv_title );
        }

        if ( $inv_para ) {
            $invoice->addParagraph( $inv_para );
        }

        if ( $inv_foot ) {
            $invoice->setFooternote( $inv_foot );
        }

        // Create invoice directory
        $inv_dir = WP_CONTENT_DIR . '/uploads/wpuf-invoices/';
        if ( ! file_exists( $inv_dir ) ) {
            wp_mkdir_p( $inv_dir );
        }

        // Generate PDF file
        $pdf_file = $inv_dir . "{$inv_u_id}_{$inv_filename}_{$inv_id}.pdf";
        $invoice->render( $pdf_file, 'F' );

        // Save download link
        $dl_link = content_url() . '/uploads/wpuf-invoices/' . "{$inv_u_id}_{$inv_filename}_{$inv_id}.pdf";
        update_user_meta( $inv_u_id, '_invoice_link' . $inv_id, $dl_link );

        // Send invoice via email
        $this->send_invoice_email( $pdf_file, $user->user_email, $info );

    }

    /**
     * Get invoice item name based on payment info
     *
     * @since 4.1.8
     *
     * @param array $info payment information
     *
     * @return string item name
     */
    private function get_invoice_item_name( $info ) {
        if ( isset( $info['post_id'] ) && $info['post_id'] > 0 ) {
            $post = get_post( $info['post_id'] );
            if ( $post ) {
                $item_name = mb_strimwidth( $post->post_title, 0, 40, '...' );
                // translators: %s is the post title
                return sprintf( __( 'Payment for post submission (%s)', 'wp-user-frontend' ), $item_name );
            }
        }

        if ( isset( $info['pack_id'] ) && $info['pack_id'] > 0 ) {
            $pack = get_post( $info['pack_id'] );
            if ( $pack ) {
                // translators: %s is the subscription pack title
                return sprintf( __( 'Subscription: %s', 'wp-user-frontend' ), $pack->post_title );
            }
        }

        return __( 'Payment', 'wp-user-frontend' );
    }

    /**
     * Send invoice via email
     *
     * @since 4.1.8
     *
     * @param string $pdf_file path to PDF file
     * @param string $user_email user email address
     * @param array $data payment data for placeholder replacement
     */
    private function send_invoice_email( $pdf_file, $user_email, $data = array() ) {
        if ( ! file_exists( $pdf_file ) ) {
            return false;
        }

        $subj = wpuf_get_option( 'set_mail_sub', 'wpuf_payment_invoices' );
        $text_body = wpuf_get_option( 'set_mail_body', 'wpuf_payment_invoices' );
        $send_attachment = wpuf_get_option( 'send_attachment', 'wpuf_payment_invoices', 'on' );

        if ( empty( $subj ) ) {
            // translators: %s is the site name
            $subj = sprintf( __( '[%s] Your Payment Invoice', 'wp-user-frontend' ), get_bloginfo( 'name' ) );
        }

        if ( empty( $text_body ) ) {
            $text_body = 'Hi {username},<br><br>Thank you for your recent payment.<br><br>Please find your invoice details below:<br>Invoice ID: {invoice_id}<br>Payment Amount: {payment_amount}<br>Payment Method: {payment_type} ({payment_type_label})<br><br>We appreciate doing business with you!<br><br>Best regards,<br>Admin.';
        }

        // Add payment_type_label for template
        if ( ! empty( $data ) ) {
            $subj = $this->replace_email_placeholders( $subj, $data );
            $text_body = $this->replace_email_placeholders( $text_body, $data );
        }

        // If not HTML, convert newlines to <br>
        if ( strpos( $text_body, '<' ) === false ) {
            $text_body = nl2br( $text_body );
        }

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . ">\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        $attach = ( wpuf_is_checkbox_or_toggle_on( $send_attachment ) ) ? array( $pdf_file ) : array();

        $mail_body = function_exists( 'get_formatted_mail_body' ) ? get_formatted_mail_body( $text_body, $subj ) : $text_body;

        $sent = wp_mail( $user_email, $subj, $mail_body, $headers, $attach );

        return $sent;
    }

    /**
     * Replace email placeholders with actual values
     *
     * @since 4.1.8
     *
     * @param string $content The email content with placeholders
     * @param array $data The payment data
     *
     * @return string The content with placeholders replaced
     */
    private function replace_email_placeholders( $content, $data ) {
        if ( empty( $data ) || empty( $content ) ) {
            return $content;
        }

        // Get user information
        $user_id = isset( $data['user_id'] ) ? $data['user_id'] : 0;
        if ( $user_id ) {
            $user = get_userdata( $user_id );
            $username = $user ? $user->user_login : '';
            $user_email = $user ? $user->user_email : '';
            $display_name = $user ? $user->display_name : '';
        } else {
            $username = isset( $data['payer_first_name'] ) ? $data['payer_first_name'] : '';
            $user_email = isset( $data['payer_email'] ) ? $data['payer_email'] : '';
            $display_name = '';
            if ( isset( $data['payer_first_name'] ) && isset( $data['payer_last_name'] ) ) {
                $display_name = $data['payer_first_name'] . ' ' . $data['payer_last_name'];
            }
        }

        // Get payment information
        $invoice_id = isset( $data['transaction_id'] ) ? $data['transaction_id'] : '';
        $payment_amount = isset( $data['cost'] ) ? wpuf_format_price( $data['cost'] ) : '';
        $payment_type = isset( $data['payment_type'] ) ? $data['payment_type'] : '';

        // Define replacements
        $replacements = [
            '{username}'           => $username,
            '{user_email}'         => $user_email,
            '{display_name}'       => $display_name,
            '{invoice_id}'         => $invoice_id,
            '{payment_amount}'     => $payment_amount,
            '{payment_type}'       => $payment_type,
        ];

        // Replace placeholders
        foreach ( $replacements as $placeholder => $value ) {
            $content = str_replace( $placeholder, $value, $content );
        }

        return $content;
    }

    /**
     * Check if invoice image file exists
     *
     * @since 4.1.8
     *
     * @param string $url The url to the remote image
     *
     * @return bool Whether the remote image exists
     */
    private function is_invoice_image_exists( $url ) {
        if ( empty( $url ) ) {
            return false;
        }

        $response = wp_remote_head( $url );
        return 200 === wp_remote_retrieve_response_code( $response );
    }

    /**
     * Handle the cancel payment
     *
     * @since  2.4.1
     *
     * @return void
     */
    public function handle_cancel_payment() {
        $nonce  = isset( $_POST['wpuf_payment_cancel'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_payment_cancel'] ) ) : '';
        $action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
        if ( ! isset( $_POST['wpuf_payment_cancel_submit'] ) || $action !== 'wpuf_cancel_pay' || ! wp_verify_nonce( $nonce,
                                                                                                                    '_wpnonce' ) ) {
            return;
        }
        $gateway = isset( $_POST['gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway'] ) ) : '';
        do_action( "wpuf_cancel_payment_{$gateway}", $_POST );
    }
}
