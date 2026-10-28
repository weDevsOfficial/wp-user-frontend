<?php
/**
 * The coupon store keeps the classic coupon hooks: `wpuf_check_save_permission`
 * (post, update flag) and `wpuf_update_coupon` fire on every save. Runs with
 * WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Platform\Coupons\CouponStore;

class ProCouponStoreHooksTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( CouponStore::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function tear_down() {
        remove_all_actions( 'wpuf_check_save_permission' );
        remove_all_actions( 'wpuf_update_coupon' );
        parent::tear_down();
    }

    public function test_save_fires_the_classic_hooks() {
        $seen = [];

        add_action(
            'wpuf_check_save_permission',
            function ( $post, $update ) use ( &$seen ) {
                $seen[] = [ 'check', $post instanceof WP_Post ? $post->post_type : null, $update ];
            },
            10,
            2
        );
        add_action(
            'wpuf_update_coupon',
            function ( $id ) use ( &$seen ) {
                $seen[] = [ 'update', get_post_type( $id ) ];
            }
        );

        $store   = wpuf()->platform()->get( CouponStore::class );
        $created = $store->save( [ 'code' => 'QAHOOK10', 'type' => 'percent', 'amount' => 10, 'status' => 'publish' ] );

        $this->assertNotWPError( $created );
        $this->assertSame( [ [ 'check', 'wpuf_coupon', false ], [ 'update', 'wpuf_coupon' ] ], $seen );

        $seen = [];
        $this->assertNotWPError( $store->save( [ 'amount' => 15 ], $created['id'] ) );
        $this->assertSame( [ 'check', 'wpuf_coupon', true ], $seen[0] );
    }
}
