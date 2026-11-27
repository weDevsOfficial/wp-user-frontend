<?php
/**
 * Credit Card (Pro Stripe) is offered at checkout only when both Stripe keys
 * are set; the checkout list goes through `wpuf_checkout_gateways`.
 * The Pro part runs with WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Checkout\Checkout_Service;

class ProStripeCheckoutGatewayTest extends WP_UnitTestCase {

    public function tear_down() {
        delete_option( 'wpuf_payment' );
        remove_all_filters( 'wpuf_checkout_gateways' );

        parent::tear_down();
    }

    public function test_checkout_gateways_go_through_the_filter() {
        update_option( 'wpuf_payment', [ 'active_gateways' => [ 'bank' => 'bank' ] ] );

        add_filter(
            'wpuf_checkout_gateways',
            function ( $gateways ) {
                unset( $gateways['bank'] );

                return $gateways;
            }
        );

        $service = wpuf()->platform()->get( Checkout_Service::class );

        $this->assertArrayNotHasKey( 'bank', $service->gateways() );
    }

    public function test_stripe_needs_both_keys_to_show_at_checkout() {
        $file = dirname( WPUF_TESTS_PLUGIN_DIR ) . '/wpuf-pro/modules/stripe/wpuf-stripe.php';

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) || ! file_exists( $file ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        if ( ! class_exists( 'WPUF_Stripe' ) ) {
            require_once $file;
        }

        // The filter only, without booting the module (no Stripe API, no hooks).
        $stripe   = ( new ReflectionClass( 'WPUF_Stripe' ) )->newInstanceWithoutConstructor();
        $gateways = [
            'bank'   => [ 'label' => 'Bank Payment' ],
            'stripe' => [ 'label' => 'Credit Card' ],
        ];

        update_option( 'wpuf_payment', [ 'stripe_api_key' => '', 'stripe_api_secret' => '' ] );
        $this->assertSame( [ 'bank' ], array_keys( $stripe->filter_checkout_gateways( $gateways ) ) );

        update_option( 'wpuf_payment', [ 'stripe_api_key' => 'pk_test_x', 'stripe_api_secret' => '' ] );
        $this->assertSame( [ 'bank' ], array_keys( $stripe->filter_checkout_gateways( $gateways ) ) );

        update_option( 'wpuf_payment', [ 'stripe_api_key' => 'pk_test_x', 'stripe_api_secret' => 'sk_test_x' ] );
        $this->assertSame( [ 'bank', 'stripe' ], array_keys( $stripe->filter_checkout_gateways( $gateways ) ) );
    }
}
