<?php

namespace WeDevs\Wpuf\Ajax;

/**
 * Billing address scripts and form (back-compat): the fields are printed by
 * Frontend\Billing\Billing_Address and saved over the REST route
 * `wpuf/v1/account/billing-address`; this class keeps the old entry points.
 */
class Address_Form_Ajax {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_plugin_scripts' ] );
    }

    /**
     * Enqueue scripts
     */
    public function register_plugin_scripts() {
        global $post;

        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        if ( wpuf_get_option( 'load_script', 'wpuf_general', 'on' ) == 'on' ) {
            $this->plugin_scripts();
        } elseif ( isset( $post->ID ) && ( $pay_page == $post->ID ) ) {
            $this->plugin_scripts();
        }
    }

    /**
     * Load billing scripts
     */
    public function plugin_scripts() {
        $billing = wpuf()->platform()->has( \WeDevs\Wpuf\Frontend\Billing\Billing_Address::class )
            ? wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Billing\Billing_Address::class )
            : new \WeDevs\Wpuf\Frontend\Billing\Billing_Address();

        $billing->localize( 'wpuf-ajax-script' );
    }

    /**
     * Address Form
     *
     * @deprecated WPUF_SINCE Billing_Address::form() (container service) prints the fields.
     *
     * @param bool $in_checkout Inside the checkout's payment form: the fields only, in a div
     */
    public static function wpuf_ajax_address_form( $in_checkout = false ) {
        $billing = wpuf()->platform()->has( \WeDevs\Wpuf\Frontend\Billing\Billing_Address::class )
            ? wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Billing\Billing_Address::class )
            : new \WeDevs\Wpuf\Frontend\Billing\Billing_Address();

        $billing->form( $in_checkout );
    }

    /**
     * Ajax Form action
     *
     * @deprecated WPUF_SINCE No script sends `wpuf_address_ajax_action` any more: the billing
     *             script saves through the REST route `wpuf/v1/account/billing-address`.
     */
    public function ajax_form_action() {
        check_ajax_referer( 'wpuf_ajax_address' );

        $post_data = wp_unslash( $_POST );

        $user_id = get_current_user_id();

        $address_fields = [];

        $add_line_1 = isset( $post_data['billing_add_line1'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_add_line1'] ) ) : '';
        $add_line_2 = isset( $post_data['billing_add_line2'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_add_line2'] ) ) : '';
        $city       = isset( $post_data['billing_city'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_city'] ) ) : '';

        $state    = isset( $post_data['billing_state'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_state'] ) ) : '';
        $zip_code = isset( $post_data['billing_zip'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_zip'] ) ) : '';
        $country  = isset( $post_data['billing_country'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_country'] ) ) : '';

        $address_fields = [
            'add_line_1' => $add_line_1,
            'add_line_2' => $add_line_2,
            'city'       => $city,
            'state'      => $state,
            'zip_code'   => $zip_code,
            'country'    => $country,
        ];

        update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );

        $msg = '<div class="wpuf-success">' . __( 'Billing address is updated.', 'wp-user-frontend' ) . '</div>';

        echo wp_kses_post( $msg );
        exit();
    }
}
