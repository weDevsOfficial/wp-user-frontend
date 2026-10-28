<?php
/**
 * The pay page's checkout (Checkout_Service): the classic page's gates in
 * order, the free pack, the order with its filters, recurring gateways, the
 * React boot data with every checkout hook fired as the classic page fires
 * it, and which render the pay page gets.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Checkout\Checkout_Service;

class CheckoutServiceTest extends WP_UnitTestCase {

    const HOOKS = [
        'wpuf_before_payment_gateway',
        'wpuf_gateway_form_paypal',
        'wpuf_gateway_form_bank',
        'wpuf_after_payment_gateway',
        'wpuf_order_summary_before',
        'wpuf_order_summary_after_item',
        'wpuf_before_pack_payment_total',
        'wpuf_order_summary_after_total',
        'wpuf_checkout_after_order_summary',
    ];

    private $pay_page;

    private $user;

    private $backup;

    public function set_up() {
        parent::set_up();

        $this->backup   = get_option( 'wpuf_payment' );
        $this->pay_page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        $this->user     = self::factory()->user->create( [ 'role' => 'subscriber' ] );

        update_option(
            'wpuf_payment',
            [
                'payment_page'    => $this->pay_page,
                'active_gateways' => [
                    'paypal' => 'paypal',
                    'bank'   => 'bank',
                ],
            ]
        );
        wp_set_current_user( $this->user );
        $_REQUEST = [];
        $_GET     = [];
    }

    public function tear_down() {
        foreach ( array_merge( self::HOOKS, [ 'wpuf_payment_amount', 'wpuf_billing_data', 'wpuf_fp_activated_msg', 'wpuf_fp_activated_error' ] ) as $hook ) {
            remove_all_filters( $hook );
        }

        update_option( 'wpuf_payment', $this->backup );
        $_REQUEST = [];
        $_GET     = [];
        parent::tear_down();
    }

    private function service() {
        return wpuf()->platform()->get( Checkout_Service::class );
    }

    private function pack( $amount, $recurring = 'no' ) {
        $id = self::factory()->post->create(
            [
                'post_type'   => 'wpuf_subscription',
                'post_title'  => 'Pack ' . $amount,
                'post_status' => 'publish',
            ]
        );

        update_post_meta( $id, '_billing_amount', $amount );
        update_post_meta( $id, '_recurring_pay', $recurring );
        update_post_meta( $id, '_expiration_number', '30' );
        update_post_meta( $id, '_expiration_period', 'day' );

        return $id;
    }

    private function request( array $query ) {
        $_REQUEST = array_merge( [ 'action' => 'wpuf_pay' ], $query );
        $_GET     = $_REQUEST;
    }

    private function resolve() {
        return $this->service()->resolve( get_post( $this->pay_page ) );
    }

    public function test_gates_in_the_classic_order() {
        $pack = $this->pack( 5 );

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack ] );
        wp_set_current_user( 0 );
        $this->assertSame( 'login', $this->resolve()['status'], 'a guest paying for a pack logs in first' );

        $guest_post = self::factory()->post->create( [ 'post_author' => $this->user ] );
        $this->request( [ 'type' => 'post', 'post_id' => $guest_post ] );
        $resolved = $this->resolve();
        $this->assertSame( 'guest_post', $resolved['status'], 'a guest paying for a post is asked to log in' );
        $this->assertTrue( $resolved['has_account'], 'the post has its own author account' );

        $owner_post = self::factory()->post->create( [ 'post_author' => (int) wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 ) ] );
        $this->request( [ 'type' => 'post', 'post_id' => $owner_post ] );
        $this->assertFalse( $this->resolve()['has_account'], 'a post of the default owner has no guest account' );

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack ] );

        wp_set_current_user( $this->user );
        $this->assertSame( 'open', $this->resolve()['status'] );

        $this->request( [ 'type' => 'pack', 'pack_id' => 999999 ] );
        $this->assertSame( 'no_pack', $this->resolve()['status'] );

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack, 'post_id' => 3 ] );
        $this->assertSame( 'skip', $this->resolve()['status'], 'a pack and a post at once: the page content' );

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack, 'user_id' => $this->user + 1 ] );
        $this->assertSame( 'skip', $this->resolve()['status'], 'another user id in the link' );

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack ] );
        $this->assertSame( 'skip', $this->service()->resolve( get_post( self::factory()->post->create( [ 'post_type' => 'page' ] ) ) )['status'], 'not the pay page' );

        update_option( 'wpuf_payment', [ 'payment_page' => $this->pay_page ] );
        $this->assertSame( 'no_gateways', $this->resolve()['status'] );

        update_option( 'wpuf_payment', [] );
        $this->assertSame( 'no_page', $this->resolve()['status'] );
    }

    public function test_free_pack_is_activated_once_with_the_message_filters() {
        $pack = $this->pack( 0 );

        add_filter( 'wpuf_fp_activated_msg', function () { return 'QA activated'; } );
        add_filter( 'wpuf_fp_activated_error', function () { return 'QA used'; } );
        $this->request( [ 'type' => 'pack', 'pack_id' => $pack ] );

        $first = $this->resolve();

        $this->assertSame( 'free', $first['status'] );
        $this->assertSame( 'QA activated', $first['message'] );
        $this->assertSame( 'QA used', $this->resolve()['message'] );
    }

    public function test_billing_data_for_a_pack_and_a_post_with_the_filters() {
        $pack    = $this->pack( 10, 'yes' );
        $amounts = [];

        add_filter(
            'wpuf_payment_amount',
            function ( $amount, $post_id = 0 ) use ( &$amounts ) {
                $amounts[] = [ (float) $amount, $post_id ];

                return $amount + 1;
            },
            10,
            2
        );
        add_filter(
            'wpuf_billing_data',
            function ( $data ) {
                $data['title'] .= ' (QA)';

                return $data;
            }
        );

        $pack_data = $this->service()->billing_data( array_merge( $this->service()->request(), [ 'pack_id' => $pack ] ), wpuf()->subscription->get_subscription( $pack ) );

        $this->assertSame( 'pack', $pack_data['type'] );
        $this->assertSame( 11.0, (float) $pack_data['billing_amount'] );
        $this->assertSame( 'yes', $pack_data['recurring_pay'] );
        $this->assertStringEndsWith( '(QA)', $pack_data['title'] );

        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms' ] );
        $post = self::factory()->post->create( [ 'post_title' => 'Paid post' ] );

        update_post_meta( $form, 'wpuf_form_settings', [ 'payment_options' => 'true', 'pay_per_post' => 'true', 'pay_per_post_cost' => '3' ] );
        update_post_meta( $post, '_wpuf_form_id', $form );

        $post_data = $this->service()->billing_data( [ 'pack_id' => 0, 'post_id' => $post ] );

        $this->assertSame( 'post', $post_data['type'] );
        $this->assertSame( 3.0, (float) $post_data['subtotal'] );
        $this->assertSame( [ 3.0, $post ], end( $amounts ), 'the post id goes with the amount, as before' );
    }

    public function test_recurring_packs_drop_gateways_without_subscriptions() {
        $this->request( [ 'type' => 'pack', 'pack_id' => $this->pack( 5, 'yes' ) ] );

        $this->assertSame( [ 'paypal' ], array_keys( $this->resolve()['gateways'] ), 'Bank takes no recurring payment' );

        $this->request( [ 'type' => 'pack', 'pack_id' => $this->pack( 5 ) ] );

        $this->assertSame( [ 'paypal', 'bank' ], array_keys( $this->resolve()['gateways'] ) );
    }

    public function test_schema_fires_every_checkout_hook_in_order_with_its_arguments() {
        $pack  = $this->pack( 5 );
        $fired = [];

        foreach ( self::HOOKS as $hook ) {
            add_action(
                $hook,
                function ( ...$args ) use ( $hook, &$fired ) {
                    $fired[] = [ $hook, count( $args ), is_array( $args[0] ) ? $args[0]['type'] : $args[0] ];
                    echo '<i data-qa="' . esc_attr( $hook ) . '"></i>';
                },
                10,
                3
            );
        }

        $this->request( [ 'type' => 'pack', 'pack_id' => $pack ] );
        $schema = $this->service()->schema( $this->resolve() );

        $this->assertSame( self::HOOKS, array_column( $fired, 0 ) );
        $this->assertSame( [ 3, 'pack' ], array_slice( $fired[1], 1 ), 'gateway forms get type, post id, pack id' );
        $this->assertSame( 'pack', $fired[4][2], 'summary hooks get the billing data' );
        $this->assertStringContainsString( 'wpuf_gateway_form_bank', $schema['gateways'][1]['form'] );
        $this->assertStringContainsString( 'wpuf_order_summary_after_item', $schema['slots']['summary_after_item'] );
        $this->assertSame( 'paypal', $schema['selected'] );
        $this->assertSame( $pack, $schema['pack_id'] );
        $this->assertSame( $this->user, $schema['user_id'] );
        $this->assertSame( 1, wp_verify_nonce( $schema['nonce'], 'wpuf_payment_gateway' ) );
    }

    public function test_billing_address_settings() {
        update_option( 'wpuf_address_options', [ 'show_address' => true, 'country' => 'hidden', 'state' => 'required', 'address_1' => 'required', 'address_2' => 'optional', 'city' => '', 'zip' => 'hidden' ] );

        $billing = $this->service()->billing_address();

        $this->assertSame( 'hidden', $billing['fields']['country']['mode'] );
        $this->assertSame( 'hidden', $billing['fields']['state']['mode'], 'a hidden country hides the state' );
        $this->assertSame( 'required', $billing['fields']['add_line_1']['mode'] );
        $this->assertSame( 'optional', $billing['fields']['city']['mode'] );
        $this->assertSame( 'billing_add_line1', $billing['fields']['add_line_1']['name'] );
        $this->assertSame( 'wpuf_biiling_add_line_1', $billing['fields']['add_line_1']['input'] );
        $this->assertNotEmpty( $billing['countries'] );

        delete_option( 'wpuf_address_options' );
    }

    public function test_pay_page_renders_the_react_checkout() {
        if ( ! wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Renderer::class )->app_built( 'checkout' ) ) {
            $this->markTestSkipped( 'The checkout app is not built.' );
        }

        global $post;

        $post = get_post( $this->pay_page ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $this->request( [ 'type' => 'pack', 'pack_id' => $this->pack( 5 ) ] );

        $html = wpuf()->frontend->payment->payment_page( 'page content' );

        $this->assertStringContainsString( 'data-wpuf-mount="checkout"', $html );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-checkout', 'enqueued' ) );
        $this->assertTrue( $this->service()->is_checkout( $post ) );
    }
}
