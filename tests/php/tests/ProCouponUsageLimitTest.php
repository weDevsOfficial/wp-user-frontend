<?php
/**
 * A coupon's usage limit can not be lowered below the times it was already
 * used; editing other fields of such a coupon still works. Runs with
 * WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Platform\Coupons\CouponStore;

class ProCouponUsageLimitTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( CouponStore::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function test_limit_below_used_is_refused() {
        $store   = wpuf()->platform()->get( CouponStore::class );
        $created = $store->save( [ 'code' => 'QALIMIT5', 'type' => 'percent', 'amount' => 10, 'status' => 'publish', 'usage_limit' => 10 ] );

        $this->assertNotWPError( $created );
        update_post_meta( $created['id'], '_coupon_used', 5 );

        $lowered = $store->save( [ 'usage_limit' => 3 ], $created['id'] );
        $this->assertWPError( $lowered );
        $this->assertSame( 'wpuf_coupon_limit_below_used', $lowered->get_error_code() );

        $this->assertNotWPError( $store->save( [ 'usage_limit' => 5 ], $created['id'] ) );
        $this->assertNotWPError( $store->save( [ 'usage_limit' => 0 ], $created['id'] ) );
        $this->assertNotWPError( $store->save( [ 'amount' => 15 ], $created['id'] ) );
    }
}
