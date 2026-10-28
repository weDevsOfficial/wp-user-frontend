<?php
/**
 * A post paid through its pricing fields ("Enable Pricing Fields Payment")
 * waits as pending with an order id and payment status pending until the
 * payment is received; with nothing to pay it is not held. Runs with
 * WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Pricing_Field_Payment;

class ProPricingPaymentHoldTest extends WP_UnitTestCase {

    private $form_id;

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( Pricing_Field_Payment::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $this->form_id = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        self::factory()->post->create(
            [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $this->form_id,
                'post_status'  => 'publish',
                'post_content' => maybe_serialize(
                    [
                        'input_type' => 'pricing_checkbox',
                        'template'   => 'pricing_checkbox',
                        'name'       => 'extras',
                        'label'      => 'Extras',
                        'is_meta'    => 'yes',
                        'options'    => [ 'a' => 'A' ],
                        'prices'     => [ 'a' => '12' ],
                        'id'         => 1,
                    ]
                ),
            ]
        );

        update_post_meta( $this->form_id, 'wpuf_form_settings', [ 'post_status' => 'publish', 'enable_pricing_payment' => 'on' ] );
    }

    public function tear_down() {
        $_POST = [];
        parent::tear_down();
    }

    public function test_a_post_with_a_price_waits_until_paid() {
        $payment  = new Pricing_Field_Payment();
        $settings = wpuf_get_form_settings( $this->form_id );
        $_POST    = [ 'form_id' => $this->form_id, 'extras' => [ 'a' ] ];

        $args = $payment->hold_until_paid( [ 'post_status' => 'publish' ], $this->form_id, $settings, [] );

        $this->assertSame( 'pending', $args['post_status'] );

        $post_id  = self::factory()->post->create( [ 'post_status' => 'pending' ] );
        $response = $payment->redirect_to_payment( [ 'redirect_to' => '' ], $post_id, $this->form_id, $settings );

        $this->assertStringContainsString( 'wpuf_pay', $response['redirect_to'] );
        $this->assertSame( 'pending', get_post_meta( $post_id, '_wpuf_payment_status', true ) );
        $this->assertNotEmpty( get_post_meta( $post_id, '_wpuf_order_id', true ), 'the received payment finds it by order id' );
    }

    public function test_nothing_to_pay_or_setting_off_is_not_held() {
        $payment  = new Pricing_Field_Payment();
        $settings = wpuf_get_form_settings( $this->form_id );

        $_POST = [ 'form_id' => $this->form_id ];
        $this->assertSame( 'publish', $payment->hold_until_paid( [ 'post_status' => 'publish' ], $this->form_id, $settings, [] )['post_status'] );

        $_POST                              = [ 'form_id' => $this->form_id, 'extras' => [ 'a' ] ];
        $settings['enable_pricing_payment'] = 'off';
        $this->assertSame( 'publish', $payment->hold_until_paid( [ 'post_status' => 'publish' ], $this->form_id, $settings, [] )['post_status'] );
    }
}
