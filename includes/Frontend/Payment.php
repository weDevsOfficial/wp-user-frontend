<?php

namespace WeDevs\Wpuf\Frontend;

use stdClass;
use WeDevs\Wpuf\Admin;
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
        /**
         * Filter payment gateways
         *
         * This filter now works with the new Gateway Manager architecture.
         * Gateways registered via Gateway_Manager will be automatically included.
         *
         * For backward compatibility, you can still add gateways using this filter:
         *
         * add_filter( 'wpuf_payment_gateways', function( $gateways ) {
         *     $gateways['custom_gateway'] = [
         *         'admin_label'    => 'Custom Gateway',
         *         'checkout_label' => 'Custom Gateway',
         *         'icon'           => 'url-to-icon',
         *     ];
         *     return $gateways;
         * });
         *
         * @since 0.8
         * @since WPUF_PRO_SINCE Updated to work with Gateway Manager
         *
         * @param array $gateways Array of gateway configurations
         */
        $gateways = apply_filters( 'wpuf_payment_gateways', [] );

        return $gateways;
    }

    /**
     * Get active payment gateways
     *
     * @return array
     */
    public function get_active_gateways() {
        return $this->checkout()->gateways();
    }

    /**
     * Show the payment page: the React checkout app on the pay page, the
     * classic markup where page builders render it (Checkout_Service).
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

        $checkout = $this->checkout();
        $resolved = $checkout->resolve( $post );

        switch ( $resolved['status'] ) {
            case 'skip':
                return $content;

            // Returned, not echoed: echo inside `the_content` prints above the
            // theme's header on block themes.
            case 'login':
                $this->enqueue_checkout_style();

                return '<div class="wpuf-frontend wpuf-form-gate wpuf-checkout-gate"><div class="wpuf-message">' . sprintf(
                    /* translators: %s: login url */
                    esc_html__( 'This page is restricted. Please %s to view this page.', 'wp-user-frontend' ),
                    wp_loginout( '', false ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress builds this login link.
                ) . '</div></div>';

            case 'guest_post':
                $this->enqueue_checkout_style();

                if ( empty( $resolved['has_account'] ) ) {
                    return '<div class="wpuf-frontend wpuf-form-gate wpuf-checkout-gate"><div class="wpuf-message">' . esc_html__( 'This post can not be paid for online. Please contact the site owner.', 'wp-user-frontend' ) . '</div></div>';
                }

                $pay_link = add_query_arg(
                    [
                        'action'  => 'wpuf_pay',
                        'type'    => 'post',
                        'post_id' => absint( $resolved['post_id'] ),
                    ],
                    get_permalink( $post )
                );

                return '<div class="wpuf-frontend wpuf-form-gate wpuf-checkout-gate"><div class="wpuf-message">' . sprintf(
                    /* translators: %s: login link */
                    esc_html__( 'Please %s to pay for your post. We sent you an email with a link to set your password.', 'wp-user-frontend' ),
                    '<a href="' . esc_url( wp_login_url( $pay_link ) ) . '">' . esc_html__( 'log in', 'wp-user-frontend' ) . '</a>'
                ) . '</div></div>';

            case 'no_page':
                return esc_html__( 'Please select your payment page from admin panel', 'wp-user-frontend' );

            case 'no_pack':
                $this->enqueue_checkout_style();

                return '<div class="wpuf-frontend wpuf-form-gate wpuf-checkout-gate"><div class="wpuf-info">' . esc_html__( 'No subscription pack found.', 'wp-user-frontend' ) . '</div></div>';
        }

        $this->enqueue_checkout_style();

        if ( 'free' === $resolved['status'] ) {
            return '<div class="wpuf-frontend wpuf-checkout"><div class="wpuf-form-gate"><div class="wpuf-info wpuf-checkout-free">' . esc_html( $resolved['message'] ) . '</div></div></div>';
        }

        if ( 'no_gateways' === $resolved['status'] ) {
            return '<div class="wpuf-frontend wpuf-checkout"><div class="wpuf-form-gate"><div class="wpuf-info">' . esc_html__( 'No Payment gateway found', 'wp-user-frontend' ) . '</div></div></div>';
        }

        if ( $checkout->renders_react( $post ) ) {
            return $checkout->react_markup( $resolved );
        }

        return $this->classic_checkout( $resolved );
    }

    /**
     * The classic checkout markup (a pay page built with Elementor, an
     * unbuilt app): the form posts to send_to_gateway().
     *
     * @since WPUF_SINCE
     *
     * @param array $resolved Checkout_Service::resolve() with status `open`
     *
     * @return string
     */
    private function classic_checkout( array $resolved ) {
        $checkout         = $this->checkout();
        $request          = $resolved['request'];
        $gateways         = $resolved['gateways'];
        $type             = $request['type'];
        $post_id          = $request['post_id'];
        $pack_id          = $request['pack_id'];
        $selected_gateway = $request['gateway'];
        $current_user     = wp_get_current_user();
        $recurring_pay    = $resolved['pack'] ? $checkout->recurring_pay( $resolved['pack'] ) : 'no';
        $billing_data     = $checkout->billing_data( $request, $resolved['pack'] );

        $notice = $checkout->notice();

        ob_start();
        echo '<div class="wpuf-frontend wpuf-checkout">';

        if ( $notice ) {
            echo '<div class="wpuf-errors wpuf-checkout-notice" role="alert">' . esc_html( $notice['message'] ) . ( $notice['detail'] ? '<br><small>' . esc_html( $notice['detail'] ) . '</small>' : '' ) . '</div>';
        }
        ?>
                    <form id="wpuf-payment-gateway" action="" method="POST">
                    <div class="wpuf-checkout-wrap">
                        <?php
                        // Billing address section (above the two columns)
                        if ( wpuf_get_option( 'show_address', 'wpuf_address_options', false ) && is_user_logged_in() ) {
                            ?>
                            <div class="wpuf-checkout-billing">
                                <h3><?php esc_html_e( 'Billing Address', 'wp-user-frontend' ); ?></h3>
                                <div class="wpuf-bill_addr-inner">
                                    <?php
                                    wpuf()->platform()->get( Billing\Billing_Address::class )->form( true );
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="wpuf-checkout-columns">
                            <!-- Left column: Payment gateways -->
                            <div class="wpuf-checkout-left">
                                <?php do_action( 'wpuf_before_payment_gateway' ); ?>

                                <ul class="wpuf-payment-gateways">
                                    <?php
                                    foreach ( $gateways as $gateway_id => $gateway ) {
                                        $instruction = wpuf_get_option( 'gate_instruct_' . $gateway_id, 'wpuf_payment' );
                                        ?>
                                        <li class="wpuf-gateway-<?php echo esc_attr( $gateway_id ); ?>">
                                            <label class="wpuf-gateway-card">
                                                <span class="wpuf-gateway-card-main">
                                                    <?php if ( ! empty( $gateway['icon'] ) ) { ?>
                                                        <span class="wpuf-gateway-icon">
                                                            <img src="<?php echo esc_url( $gateway['icon'] ); ?>" alt="<?php echo esc_attr( $gateway['label'] ); ?>">
                                                        </span>
                                                    <?php } ?>
                                                    <span class="wpuf-gateway-info">
                                                        <span class="wpuf-gateway-label"><?php echo esc_html( $gateway['label'] ); ?></span>
                                                        <?php if ( ! empty( $instruction ) ) { ?>
                                                            <span class="wpuf-gateway-desc"><?php echo esc_html( wp_strip_all_tags( $instruction ) ); ?></span>
                                                        <?php } ?>
                                                    </span>
                                                    <input name="wpuf_payment_method" type="radio"
                                                            value="<?php echo esc_attr( $gateway_id ); ?>"
                                                            <?php checked( $selected_gateway, $gateway_id ); ?>>
                                                </span>

                                                <?php
                                                // The gateway's own form (Stripe's card field, instructions); no empty box when it prints nothing.
                                                ob_start();
                                                do_action( 'wpuf_gateway_form_' . $gateway_id, $type, $post_id, $pack_id );
                                                $gateway_form = trim( (string) ob_get_clean() );

                                                if ( '' !== $gateway_form ) {
                                                    ?>
                                                    <div class="wpuf-payment-instruction" style="display: none;">
                                                        <?php echo $gateway_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the gateway's own markup, printed as before. ?>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </label>
                                        </li>
                                    <?php } ?>
                                </ul>

                                <?php do_action( 'wpuf_after_payment_gateway' ); ?>
                            </div>

                            <!-- Right column: Order summary -->
                            <div class="wpuf-checkout-right">
                                <div class="wpuf-order-summary">
                                    <?php
                                    if ( $pack_id ) {
                                        if ( is_user_logged_in() ) {
                                            ?>
                                            <input type="hidden" name="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>">
                                            <?php
                                        }
                                    }
                                    ?>

                                    <div class="wpuf-pack-inner">
                                        <?php $this->render_order_summary( $billing_data ); ?>
                                    </div><!-- .wpuf-pack-inner -->

                                    <?php
                                    // Coupon UI. Rendered here rather than by Pro so the
                                    // field keeps working on any Pro version, since Pro
                                    // only supplies the Ajax handlers.
                                    if ( $pack_id && wpuf_is_pro_active() && class_exists( 'WeDevs\Wpuf\Pro\Coupons' ) ) {
                                        ?>
                                        <a href="#" class="wpuf-coupon-show"><?php esc_html_e( 'Have a discount code?', 'wp-user-frontend' ); ?></a>

                                        <div class="wpuf-coupon-wrap" style="display:none;">
                                            <div class="wpuf-coupon-error"></div>
                                            <input type="text" name="coupon_code" size="20" class="wpuf-coupon-field" placeholder="<?php esc_attr_e( 'Coupon code', 'wp-user-frontend' ); ?>">
                                            <input type="hidden" name="coupon_id" size="20" class="wpuf-coupon-id-field">
                                            <div class="wpuf-coupon-actions">
                                                <a href="#" data-pack_id="<?php echo esc_attr( $pack_id ); ?>" class="wpuf-apply-coupon"><?php esc_html_e( 'Apply Coupon', 'wp-user-frontend' ); ?></a>
                                                <a href="#" data-pack_id="<?php echo esc_attr( $pack_id ); ?>" class="wpuf-coupon-cancel"><?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?></a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>

                                    <?php do_action( 'wpuf_checkout_after_order_summary', $billing_data ); ?>

                                    <?php
                                    if ( $pack_id ) {
                                        ?>
                                        <div id="wpuf_type" style="display: none"><?php echo 'pack'; ?></div>
                                        <div id="wpuf_id" style="display: none"><?php echo esc_attr( $pack_id ); ?></div>
                                        <?php
                                    } elseif ( $post_id ) {
                                        ?>
                                        <div id="wpuf_type" style="display: none"><?php echo 'post'; ?></div>
                                        <div id="wpuf_id" style="display: none"><?php echo esc_attr( $post_id ); ?></div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <?php wp_nonce_field( 'wpuf_payment_gateway' ); ?>

                        <!-- Hidden fields and submit button -->
                        <div class="wpuf-checkout-footer">
                            <input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>"/>
                            <input type="hidden" name="action" value="wpuf_pay"/>
                            <?php if ( $post_id ) { ?>
                                <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>"/>
                            <?php } ?>

                            <?php if ( $pack_id ) { ?>
                                <input type="hidden" name="pack_id" value="<?php echo esc_attr( $pack_id ); ?>"/>
                                <input type="hidden" name="recurring_pay" value="<?php echo esc_attr( $recurring_pay ); ?>"/>
                            <?php } ?>
                            <div id="wpuf-checkout-errors" class="wpuf-checkout-errors" role="alert" aria-live="polite" style="display:none;"></div>
                            <button type="submit" name="wpuf_payment_submit" class="wpuf-btn wpuf-checkout-btn">
                                <?php esc_html_e( 'Continue to Payment', 'wp-user-frontend' ); ?>
                            </button>
                        </div>
                    </div>
                    </form>
        <?php
        echo '</div>';

        return ob_get_clean();
    }

    /**
     * The checkout service.
     *
     * @return \WeDevs\Wpuf\Frontend\Checkout\Checkout_Service
     */
    private function checkout() {
        return wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Checkout\Checkout_Service::class );
    }

    /**
     * The checkout in the frontend apps' look: the kit tokens and font of the
     * runtime sheet (the classic markup, ids and hooks stay as they are).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function enqueue_checkout_style() {
        wp_enqueue_style( 'wpuf-frontend-runtime-style' );
    }

    /**
     * Render the order item row
     *
     * @since 4.3.9
     *
     * @param array $billing_data Billing data array with keys: title, subtotal, billing_amount, type, id
     *
     * @return void
     */
    private function render_order_item( $billing_data ) {
        $recurring_pay = isset( $billing_data['recurring_pay'] ) ? $billing_data['recurring_pay'] : '';
        ?>
        <div class="wpuf-order-row wpuf-order-item">
            <span class="wpuf-order-label"><?php echo esc_html( $billing_data['title'] ); ?></span>
            <span class="wpuf-order-value" id="wpuf_pay_page_cost"><?php echo esc_html( wpuf_format_price( $billing_data['subtotal'] ) ); ?></span>
        </div>
        <?php if ( 'pack' === $billing_data['type'] ) { ?>
        <div class="wpuf-order-subtitle">
            <?php echo ( 'yes' === $recurring_pay || 'on' === $recurring_pay ) ? esc_html__( 'Recurring payment', 'wp-user-frontend' ) : esc_html__( 'One time payment', 'wp-user-frontend' ); ?>
        </div>
            <?php
        }
    }

    /**
     * Render the order total row
     *
     * @since 4.3.9
     *
     * @param array $billing_data Billing data array with keys: title, subtotal, billing_amount, type, id
     *
     * @return void
     */
    private function render_order_total( $billing_data ) {
        ?>
        <div class="wpuf-order-divider"></div>
        <div class="wpuf-order-row wpuf-order-total">
            <span class="wpuf-order-label"><?php esc_html_e( 'Total', 'wp-user-frontend' ); ?></span>
            <span class="wpuf-order-value" id="wpuf_pay_page_total"><?php echo esc_html( wpuf_format_price( $billing_data['billing_amount'] ) ); ?></span>
        </div>
        <?php
    }

    /**
     * Render the tax rate row when Pro has tax enabled
     *
     * The previous checkout markup got this row from Pro's
     * Coupons::after_apply_coupon(). The redesigned summary renders it here so
     * the tax line keeps showing on first paint, before any coupon is applied.
     *
     * @since 4.3.9
     *
     * @return void
     */
    private function render_tax_row() {
        if ( ! wpuf_is_pro_active() || ! class_exists( 'WeDevs\Wpuf\Pro\Coupons' ) || ! function_exists( 'wpuf_pro' ) ) {
            return;
        }

        $coupons = wpuf_pro()->coupons;

        if (
            ! is_object( $coupons )
            || ! method_exists( $coupons, 'wpuf_tax_enabled' )
            || ! method_exists( $coupons, 'wpuf_current_tax_rate' )
            || ! $coupons->wpuf_tax_enabled()
        ) {
            return;
        }
        ?>
        <div class="wpuf-order-row wpuf-order-tax">
            <span class="wpuf-order-label"><?php esc_html_e( 'Tax', 'wp-user-frontend' ); ?></span>
            <span class="wpuf-order-value" id="wpuf_pay_page_tax"><?php echo esc_html( $coupons->wpuf_current_tax_rate() . '%' ); ?></span>
        </div>
        <?php
    }

    /**
     * Render the full order summary using a hookable sequence
     *
     * Hook sequence:
     * - wpuf_order_summary_before     — before the item row
     * - wpuf_order_summary_after_item — after item row, before total (Pro injects coupon/tax rows here)
     * - wpuf_before_pack_payment_total — existing hook, kept for back-compat
     * - wpuf_order_summary_after_total — after the total row
     *
     * @since 4.3.9
     *
     * @param array $billing_data Billing data array with keys: title, subtotal, billing_amount, type, id
     *
     * @return void
     */
    private function render_order_summary( $billing_data ) {
        if ( empty( $billing_data ) ) {
            return;
        }

        do_action( 'wpuf_order_summary_before', $billing_data );
        $this->render_order_item( $billing_data );
        do_action( 'wpuf_order_summary_after_item', $billing_data );
        $this->render_tax_row();
        do_action( 'wpuf_before_pack_payment_total' );
        $this->render_order_total( $billing_data );
        do_action( 'wpuf_order_summary_after_total', $billing_data );
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
        } elseif ( 'post' === $type ) {
            // Guest pay per post: the beneficiary is the post's own author, never a value
            // taken from the request, which could name any user.
            $post     = get_post( $post_id );
            $userdata = $post ? get_userdata( $post->post_author ) : false;

            if ( ! $userdata ) {
                $userdata             = new stdClass();
                $userdata->ID         = 0;
                $userdata->user_email = '';
                $userdata->first_name = '';
                $userdata->last_name  = '';
            }
        } else {
            // A subscription pack is always bought by the logged-in buyer. Without this a
            // logged-out request could name another user and hand them a free pack.
            wp_die( esc_html__( 'Invalid payment request.', 'wp-user-frontend' ) );
        }

        switch ( $type ) {
            case 'post':
                $post          = get_post( $post_id );
                $form_id       = get_post_meta( $post_id, '_wpuf_form_id', true );
                // only a post made by a WPUF form can be paid for here, never a pack or another object
                if (
                    ! $post
                    || 'wpuf_subscription' === $post->post_type
                    || 'wpuf_forms' !== get_post_type( $form_id )
                    || ( is_user_logged_in() && (int) $post->post_author !== get_current_user_id() )
                ) {
                    wp_die( esc_html__( 'Invalid payment request.', 'wp-user-frontend' ) );
                }
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
                $pack        = wpuf()->subscription->get_purchasable_subscription( $pack_id );

                if ( ! $pack ) {
                    wp_die( esc_html__( 'This subscription pack is not available.', 'wp-user-frontend' ) );
                }

                $custom      = $pack->meta_value;
                $cost        = $pack->meta_value['billing_amount'];
                $amount      = $cost;
                $item_name   = $pack->post_title;
                $item_number = $pack->ID;
                break;
        }
        // Keep the submitted request for the gateways, but never carry the raw payment
        // method with it: only the sanitized gateway name routes the payment, so storing
        // the raw value would let markup survive into the admin transaction list.
        $post_data                        = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- nonce verified at the top of this method; the array is handed to the gateways unchanged, as before.
        $post_data['wpuf_payment_method'] = $gateway;

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
            'post_data'           => $post_data,
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
        $result = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT transaction_id
            FROM ' . $wpdb->prefix . 'wpuf_transaction
            WHERE transaction_id = %s LIMIT 1', $transaction_id
            )
        );

        // Store profile_id separately if it exists (needed for wpuf_payment_received action)
        // but don't insert it into database as the column doesn't exist
        $profile_id = isset( $data['profile_id'] ) ? $data['profile_id'] : null;

        if ( wpuf_get_option( 'show_address', 'wpuf_address_options', false ) && ! empty( $data['user_id'] ) ) {
            $data['payer_address'] = wpuf_get_user_address( $data['user_id'] );
        }
        if ( ! empty( $data['payer_address'] ) ) {
            $data['payer_address'] = maybe_serialize( $data['payer_address'] );
        }

        // Create a copy of data for database insert (without profile_id)
        $db_data = $data;
        if ( isset( $db_data['profile_id'] ) ) {
            unset( $db_data['profile_id'] );
        }

        if ( ! $result ) {
            $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', $db_data );

            do_action( 'wpuf_payment_received', $data, $recurring );
        } else {
            $wpdb->update( $wpdb->prefix . 'wpuf_transaction', $db_data, [ 'transaction_id' => $transaction_id ] );
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

        // With Pro's invoices on, Pro's invoice (Admin\Invoice::generate_invoice() on the
        // same action) is the customer's email: a second, free-made invoice was sent here
        // before. Unsaved setting = on, as Pro reads it.
        $enable_invoices = wpuf_get_option( 'enable_invoices', 'wpuf_payment_invoices', 'on' );
        $pro_available   = wpuf_is_pro_active() && class_exists( 'WeDevs\Wpuf\Pro\Admin\Invoice' );

        if ( $pro_available && wpuf_is_checkbox_or_toggle_on( $enable_invoices ) ) {
            return;
        }

        // Pro not available or invoices disabled - send normal payment confirmation email
        $payment_type = $this->determine_user_payment_type( $info );
        $subject_msg  = $this->get_user_notification_content( $payment_type, $user, $info );

        $subject = $subject_msg['subject'];
        $message = $subject_msg['message'];

        // Set proper headers
        $headers  = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        wp_mail( $user->user_email, $subject, $message, $headers );
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
            'message' => $message,
        ];
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
        if ( ! isset( $_POST['wpuf_payment_cancel_submit'] ) || $action !== 'wpuf_cancel_pay' || ! wp_verify_nonce(
            $nonce,
            '_wpnonce'
        ) ) {
            return;
        }
        // The form posts the owner's id; only an admin may cancel for someone else.
        $owner_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;

        if ( $owner_id && $owner_id !== get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $gateway = isset( $_POST['gateway'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway'] ) ) : '';
        do_action( "wpuf_cancel_payment_{$gateway}", $_POST );
    }
}
