<?php
/**
 * Pro fixes from the 2026-10-16 adversarial review (AR-xx ids in the openspec
 * adversarial-review list). Runs with WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Coupons;
use WeDevs\Wpuf\Pro\Frontend\Profile_Form;
use WeDevs\Wpuf\Pro\Platform\Coupons\CouponStore;

class ProAdversarialReviewFixesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( CouponStore::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }
    }

    public function tear_down() {
        unset( $_COOKIE['wpuf_guest_upload'] );
        parent::tear_down();
    }

    private function coupon( $limit, $used ) {
        $pack   = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish' ] );
        update_post_meta( $pack, '_billing_amount', 10 );
        $coupon = self::factory()->post->create( [ 'post_type' => 'wpuf_coupon', 'post_status' => 'publish' ] );

        update_post_meta( $coupon, '_code', 'AR' . $coupon );
        update_post_meta( $coupon, '_type', 'amount' );
        update_post_meta( $coupon, '_amount', 5 );
        update_post_meta( $coupon, '_package', [ 'all' ] );
        update_post_meta( $coupon, '_usage_limit', $limit );
        update_post_meta( $coupon, '_coupon_used', $used );

        return [ $coupon, $pack ];
    }

    /** AR-36: a coupon used as often as its limit is used up at checkout. */
    public function test_coupon_at_its_limit_is_refused() {
        list( $coupon, $pack ) = $this->coupon( 1, 1 );

        $this->assertWPError( ( new Coupons() )->coupon_validation( 10, $coupon, $pack ) );

        list( $coupon, $pack ) = $this->coupon( 2, 1 );

        $this->assertNotWPError( ( new Coupons() )->coupon_validation( 10, $coupon, $pack ) );
    }

    /** AR-16: the coupons list shows the same state checkout uses. */
    public function test_coupon_list_state_matches_checkout() {
        $state = new ReflectionMethod( CouponStore::class, 'state' );
        $state->setAccessible( true );
        $store = ( new ReflectionClass( CouponStore::class ) )->newInstanceWithoutConstructor();

        $this->assertSame( 'used_up', $state->invoke( $store, 'publish', '', '', 5, 5 ) );
        $this->assertSame( 'active', $state->invoke( $store, 'publish', '', '', 5, 4 ) );
    }

    /** AR-32: a profile or registration submit only claims the requester's own uploads. */
    public function test_profile_files_only_claim_own_uploads() {
        $claim = new ReflectionMethod( Profile_Form::class, 'may_claim_attachment' );
        $claim->setAccessible( true );

        $victim = self::factory()->user->create();
        $member = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $theirs = get_post( self::factory()->attachment->create( [ 'post_author' => $victim ] ) );
        $mine   = get_post( self::factory()->attachment->create( [ 'post_author' => $member ] ) );
        $guest  = get_post( self::factory()->attachment->create( [ 'post_author' => 0 ] ) );

        update_post_meta( $guest->ID, '_wpuf_attachment', 1 );
        update_post_meta( $guest->ID, '_wpuf_guest_token', 'tok' );

        wp_set_current_user( $member );

        $this->assertFalse( $claim->invoke( null, $theirs ) );
        $this->assertTrue( $claim->invoke( null, $mine ) );
        $this->assertFalse( $claim->invoke( null, $guest ) );

        // A registration logs the new user in before attaching their guest uploads.
        $_COOKIE['wpuf_guest_upload'] = 'tok';
        $this->assertTrue( $claim->invoke( null, $guest ) );
    }
}
