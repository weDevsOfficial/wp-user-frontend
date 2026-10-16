<?php
/**
 * The React plans app's data (Subscription_Schema): cards from the classic
 * values (pack_view_data + its filter), the user's subscription states,
 * the classic fallback when the markup filter is hooked, and the
 * account route.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Subscriptions\Subscription_Schema;

class SubscriptionSchemaTest extends WP_UnitTestCase {

    private $pack;

    public function set_up() {
        parent::set_up();

        $this->pack = self::factory()->post->create(
            [
                'post_type'    => 'wpuf_subscription',
                'post_status'  => 'publish',
                'post_title'   => 'Gold plan',
                'post_content' => 'Everything you need.',
            ]
        );
        update_post_meta( $this->pack, '_sort_order', 1 );
        update_post_meta( $this->pack, '_billing_amount', '12' );
        update_post_meta( $this->pack, '_recurring_pay', 'no' );
        update_post_meta( $this->pack, '_post_type_name', [ 'post' => '3' ] );
        update_option( 'wpuf_payment', [ 'payment_page' => self::factory()->post->create( [ 'post_type' => 'page' ] ) ] );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_subscription_pack_details_args' );
        remove_all_filters( 'wpuf_subscription_packs' );
        remove_all_filters( 'wpuf_subscription_listing_args' );
        unset( $_GET['action'] );
        parent::tear_down();
    }

    private function schema() {
        return wpuf()->platform()->get( Subscription_Schema::class );
    }

    private function card( array $data ) {
        foreach ( $data['cards'] as $card ) {
            if ( $card['id'] === $this->pack ) {
                return $card;
            }
        }

        return null;
    }

    public function test_service_comes_from_the_container() {
        $this->assertInstanceOf( Subscription_Schema::class, $this->schema() );
    }

    public function test_card_matches_the_classic_values_for_a_guest_and_a_user() {
        $card = $this->card( $this->schema()->packs() );

        $this->assertSame( 'Gold plan', $card['title'] );
        $this->assertFalse( $card['free'] );
        $this->assertContains( '3 posts allowed', $card['features'] );
        $this->assertStringContainsString( 'type=wpuf_sub', $card['url'], 'guests get the registration link' );
        $this->assertStringContainsString( 'pack_id=' . $this->pack, $card['url'] );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $card = $this->card( $this->schema()->packs() );

        $this->assertStringContainsString( 'action=wpuf_pay', $card['url'], 'logged-in users go to the payment page' );
        $this->assertSame( 'Buy Now', $card['button'] );
    }

    public function test_pack_details_filter_reaches_the_react_card() {
        add_filter(
            'wpuf_subscription_pack_details_args', function ( $args ) {
				$args['button_name'] = 'Join today';
				$args['query_url']   = 'https://example.com/join';
				return $args;
			}
        );

        $card = $this->card( $this->schema()->packs() );

        $this->assertSame( 'Join today', $card['button'] );
        $this->assertStringStartsWith( 'https://example.com/join', $card['url'] );
    }

    public function test_info_states_none_and_pending_bank_order() {
        $user = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $user );

        $this->assertSame( 'none', $this->schema()->info( false )['state'] );

        $order = wp_insert_post( [ 'post_type' => 'wpuf_order', 'post_status' => 'publish', 'post_title' => 'WPUF Bank Order', 'post_author' => $user ] );
        update_post_meta( $order, '_data', [ 'type' => 'pack', 'item_number' => $this->pack ] );

        $info = $this->schema()->info( false );

        $this->assertSame( 'pending', $info['state'] );
        $this->assertSame( 'Gold plan', $info['pack']['title'] );
    }

    public function test_a_filter_on_the_classic_markup_keeps_the_classic_list() {
        $react = wpuf()->subscription->subscription_packs();
        $this->assertStringContainsString( 'data-wpuf-mount="plans"', $react );

        add_filter( 'wpuf_subscription_packs', function ( $html ) {
			return $html . '<!-- extended -->';
		} );

        $classic = wpuf()->subscription->subscription_packs();
        $this->assertStringNotContainsString( 'data-wpuf-mount', $classic );
        $this->assertStringContainsString( '<!-- extended -->', $classic );
    }

    public function test_account_route_returns_the_card_data() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        do_action( 'rest_api_init' );

        $response = rest_get_server()->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/account/subscription' ) );

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( 'none', $response->get_data()['state'] );
    }

    public function test_title_entities_decoded_listing_args_filter_and_pay_page() {
        wp_update_post( [ 'ID' => $this->pack, 'post_title' => 'Gold & Silver' ] );

        $this->assertSame( 'Gold & Silver', $this->card( $this->schema()->packs() )['title'], 'no &amp; in the app text' );

        add_filter(
            'wpuf_subscription_listing_args', function ( $args ) {
                $args['packs'] = [];

                return $args;
            }
        );
        $this->assertSame( [], $this->schema()->packs()['cards'], 'the classic list filter still shapes the packs' );
        remove_all_filters( 'wpuf_subscription_listing_args' );

        $_GET['action'] = 'wpuf_pay';
        $data           = $this->schema()->packs();
        $this->assertTrue( $data['pay'] );
        $this->assertSame( [], $data['cards'] );
    }

    public function test_price_is_plain_text_for_the_app() {
        $price = $this->card( $this->schema()->packs() )['price'];

        $this->assertStringNotContainsString( '&#', $price, 'wpuf_format_price() writes the symbol as an entity; React would print it' );
        $this->assertStringContainsString( '12', $price );
    }

    public function test_current_pill_only_for_a_paid_pack() {
        $user = self::factory()->user->create();

        wp_set_current_user( $user );
        update_user_meta( $user, '_wpuf_subscription_pack', [ 'pack_id' => $this->pack, 'status' => 'pending', 'expire' => '' ] );
        $this->assertFalse( $this->card( $this->schema()->packs() )['current'], 'a pending order is not the current plan' );

        update_user_meta( $user, '_wpuf_subscription_pack', [ 'pack_id' => $this->pack, 'status' => 'completed', 'expire' => '' ] );
        $this->assertTrue( $this->card( $this->schema()->packs() )['current'] );
    }
}
