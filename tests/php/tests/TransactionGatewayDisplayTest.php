<?php
/**
 * How a payment's gateway shows in the admin and the member's invoices:
 * the registered name whatever spelling the gateway stored, and a link to
 * the payment at PayPal / Stripe (other gateways through a filter).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Transactions\TransactionService;

class TransactionGatewayDisplayTest extends WP_UnitTestCase {

    public function tear_down() {
        delete_option( 'wpuf_payment' );
        remove_all_filters( 'wpuf_transaction_gateway_url' );

        parent::tear_down();
    }

    private function service() {
        return wpuf()->platform()->get( TransactionService::class );
    }

    public function test_stored_spellings_show_the_registered_name() {
        $this->assertSame( 'PayPal', wpuf_get_payment_type_label( 'Paypal' ) );
        $this->assertSame( 'PayPal', wpuf_get_payment_type_label( 'paypal' ) );
        $this->assertSame( 'Bank/Manual', wpuf_get_payment_type_label( 'bank' ) );
        // A gateway that is not installed keeps its stored name.
        $this->assertSame( 'Razorpay', wpuf_get_payment_type_label( 'Razorpay' ) );
    }

    public function test_paypal_payments_link_to_paypal_live_or_sandbox() {
        $this->assertSame( 'https://www.paypal.com/activity/payment/8AB12345CD678901E', $this->service()->transaction_url( 'Paypal', '8AB12345CD678901E' ) );

        update_option( 'wpuf_payment', [ 'paypal_test_mode' => 'test' ] );
        $this->assertSame( 'https://www.sandbox.paypal.com/activity/payment/8AB12345CD678901E', $this->service()->transaction_url( 'PayPal', '8AB12345CD678901E' ) );
    }

    public function test_bank_unknown_and_odd_ids_have_no_link() {
        $this->assertSame( '', $this->service()->transaction_url( 'Bank/Manual', '837' ) );
        $this->assertSame( '', $this->service()->transaction_url( 'Razorpay', 'pay_x' ) );
        $this->assertSame( '', $this->service()->transaction_url( 'Paypal', 'bad id"><script>' ) );
        $this->assertSame( '', $this->service()->transaction_url( 'Paypal', '' ) );
    }

    public function test_other_gateways_add_their_link_through_the_filter() {
        add_filter(
            'wpuf_transaction_gateway_url',
            function ( $url, $gateway_id, $transaction_id, $gateway ) {
                return 'Razorpay' === $gateway ? 'https://dashboard.razorpay.com/app/payments/' . $transaction_id : $url;
            },
            10,
            4
        );

        $this->assertSame( 'https://dashboard.razorpay.com/app/payments/pay_x', $this->service()->transaction_url( 'Razorpay', 'pay_x' ) );
    }
}
