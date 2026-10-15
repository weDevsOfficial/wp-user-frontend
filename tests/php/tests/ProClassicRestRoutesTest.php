<?php
/**
 * Pro's REST routes for its classic scripts (they posted to admin-ajax
 * before): the avatar field removal, the coupon box, the comments list, the
 * QR code field and the Stripe pay form. Runs with WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

class ProClassicRestRoutesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();

        do_action( 'rest_api_init' );
        add_filter( 'wpuf_rate_limit', '__return_zero' );
    }

    public function tear_down() {
        remove_filter( 'wpuf_rate_limit', '__return_zero' );
        parent::tear_down();
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    public function test_the_routes_are_registered_and_the_ajax_actions_gone() {
        $routes = rest_get_server()->get_routes();

        foreach ( [ '/wpuf/v1/profile/avatar', '/wpuf/v1/coupons/apply', '/wpuf/v1/coupons/cancel' ] as $route ) {
            $this->assertArrayHasKey( $route, $routes, $route );
        }

        foreach ( [ 'wpuf_submit_register', 'wpuf_update_profile', 'wpuf_delete_avatar', 'wpuf_coupon_apply', 'wpuf_coupon_cancel' ] as $action ) {
            $this->assertFalse( has_action( 'wp_ajax_' . $action ), $action );
        }
    }

    public function test_avatar_removal_is_for_the_user_or_who_may_edit_them() {
        $owner = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $other = self::factory()->user->create( [ 'role' => 'subscriber' ] );

        update_user_meta( $owner, 'user_avatar', 'https://example.org/no-file.png' );

        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->request( 'DELETE', '/wpuf/v1/profile/avatar' )->get_status() );

        wp_set_current_user( $other );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/profile/avatar', [ 'user_id' => $owner ] )->get_status() );
        $this->assertNotEmpty( get_user_meta( $owner, 'user_avatar', true ) );

        wp_set_current_user( $owner );
        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/profile/avatar' )->get_status() );
        $this->assertSame( '', get_user_meta( $owner, 'user_avatar', true ) );

        update_user_meta( $owner, 'user_avatar', 'https://example.org/no-file.png' );
        $admin = self::factory()->user->create( [ 'role' => 'administrator' ] );

        if ( is_multisite() ) {
            grant_super_admin( $admin );
        }

        wp_set_current_user( $admin );
        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/profile/avatar', [ 'user_id' => $owner ] )->get_status() );
        $this->assertSame( '', get_user_meta( $owner, 'user_avatar', true ) );
    }

    public function test_coupon_routes_need_the_page_nonce_and_a_real_pack() {
        wp_set_current_user( 0 );

        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/coupons/apply', [ 'pack_id' => 1, 'coupon' => 'X', 'wpuf_nonce' => 'nope' ] )->get_status() );

        $response = $this->request( 'POST', '/wpuf/v1/coupons/apply', [ 'pack_id' => 999999, 'coupon' => 'X', 'wpuf_nonce' => wp_create_nonce( 'wpuf_nonce' ) ] );
        $this->assertSame( 400, $response->get_status() );
        $this->assertSame( 'wpuf_coupon_invalid_pack', $response->get_data()['code'] );
    }

    public function test_comment_routes_need_a_moderator() {
        if ( ! class_exists( 'WPUF_Comments' ) ) {
            $this->markTestSkipped( 'Comments module is not active.' );
        }

        $post    = self::factory()->post->create( [ 'post_author' => self::factory()->user->create( [ 'role' => 'author' ] ) ] );
        $comment = self::factory()->comment->create( [ 'comment_post_ID' => $post ] );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/comments/' . $comment . '/status', [ 'status' => 'hold' ] )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $response = $this->request( 'POST', '/wpuf/v1/comments/' . $comment . '/status', [ 'status' => 'hold' ] );
        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( 'pending', $response->get_data()['item']['status'] );
        $this->assertArrayHasKey( 'counts', $response->get_data() );
        $this->assertSame( '0', get_comment( $comment )->comment_approved );
    }

    public function test_qr_type_fields_need_the_page_nonce() {
        if ( ! class_exists( 'WPUF_Ajax_QR_Code' ) ) {
            $this->markTestSkipped( 'QR code module is not active.' );
        }

        $this->assertSame( 403, $this->request( 'GET', '/wpuf/v1/qr-code/type-fields', [ 'type' => 'url', 'wpuf_nonce' => 'nope' ] )->get_status() );

        $response = $this->request( 'GET', '/wpuf/v1/qr-code/type-fields', [ 'type' => 'url', 'formfield' => 'qr', 'wpuf_nonce' => wp_create_nonce( 'wpuf_nonce' ) ] );
        $this->assertSame( 200, $response->get_status() );
        $this->assertStringContainsString( 'name="qr[type_param][url]"', $response->get_data()['html'] );
    }

    public function test_stripe_intent_needs_the_page_nonce() {
        if ( ! class_exists( 'WPUF_Gateway_Stripe' ) ) {
            $this->markTestSkipped( 'Stripe module is not active.' );
        }

        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/payments/stripe/intent', [ 'form_data' => '', 'wpuf_nonce' => 'nope' ] )->get_status() );
    }

    public function test_the_tax_filter_answers_only_when_tax_is_on() {
        $tax  = new \WeDevs\Wpuf\Pro\Tax();
        $data = [
            'type'            => 'pack',
            'id'              => 999999,
            'billing_country' => 'US',
            'billing_state'   => 'CA',
        ];

        add_filter( 'wpuf_tax_enabled', '__return_false' );
        $this->assertSame( 'kept', $tax->filter_tax_response( 'kept', $data ), 'tax off: the response is left alone' );
        remove_filter( 'wpuf_tax_enabled', '__return_false' );

        add_filter( 'wpuf_tax_enabled', '__return_true' );
        $answer = $tax->filter_tax_response( null, $data );
        remove_filter( 'wpuf_tax_enabled', '__return_true' );

        $this->assertArrayHasKey( 'tax', $answer, 'an unknown pack costs 0, no warning' );
        $this->assertArrayHasKey( 'cost', $answer );
    }
}
