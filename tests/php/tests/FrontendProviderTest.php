<?php
/**
 * Frontend round: the frontend layer and the AJAX layer share one instance of each class
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Providers\FrontendServiceProvider;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * FrontendServiceProvider, the frontend container keys, and the store methods added with it.
 */
class FrontendProviderTest extends WP_UnitTestCase {

    /**
     * Every frontend key resolves to a shared class and `wpuf()->frontend->{key}` is that instance.
     */
    public function test_frontend_keys_are_shared_services() {
        $platform = wpuf()->platform();

        foreach ( FrontendServiceProvider::SERVICES as $key => $class ) {
            $this->assertSame( $class, FrontendServiceProvider::class_of( $key ) );
            $this->assertTrue( $platform->has( $class ), $class );
            $this->assertTrue( $platform->is_shared( $class ), $class );
        }

        foreach ( FrontendServiceProvider::HANDLERS as $class ) {
            $this->assertTrue( $platform->has( $class ), $class );
            $this->assertSame( $platform->get( $class ), $platform->get( $class ), $class );
        }

        $this->assertNull( FrontendServiceProvider::class_of( 'nope' ) );

        $frontend = isset( wpuf()->frontend ) ? wpuf()->frontend : new \WeDevs\Wpuf\Frontend();

        foreach ( array_keys( FrontendServiceProvider::SERVICES ) as $key ) {
            $this->assertSame( $platform->get( FrontendServiceProvider::class_of( $key ) ), $frontend->{$key}, $key );
        }
    }

    /**
     * Ajax registers its handlers on the shared instances (no second copy of a hooked class).
     */
    public function test_ajax_uses_shared_handlers() {
        $platform = wpuf()->platform();
        $account  = $platform->get( \WeDevs\Wpuf\Frontend\Frontend_Account::class );
        $ajax     = new \WeDevs\Wpuf\Ajax();

        $this->assertInstanceOf( \WeDevs\Wpuf\Ajax::class, $ajax );
        $this->assertSame( $account, $platform->get( \WeDevs\Wpuf\Frontend\Frontend_Account::class ) );

        // The classic account templates (theme overrides) still post this action.
        $callbacks = $GLOBALS['wp_filter']['wp_ajax_wpuf_account_update_profile']->callbacks;
        $found     = false;

        foreach ( $callbacks as $priority ) {
            foreach ( $priority as $cb ) {
                if ( is_array( $cb['function'] ) && $cb['function'][0] === $account && 'update_profile' === $cb['function'][1] ) {
                    $found = true;
                }
            }
        }

        $this->assertTrue( $found, 'wpuf_account_update_profile is bound to the shared Frontend_Account' );
    }

    /**
     * SubmissionStore::set_lock_time(), UserPackStore paypal subscription id, TransactionStore::last_completed_date().
     */
    public function test_store_methods_added_with_the_frontend_round() {
        $post_id = self::factory()->post->create();
        $user_id = self::factory()->user->create();

        Stores::submissions()->set_lock_time( $post_id, 1234 );
        $this->assertSame( '1234', get_post_meta( $post_id, '_wpuf_lock_user_editing_post_time', true ) );
        Stores::submissions()->set_lock_time( $post_id, '' );
        $this->assertSame( '', Stores::submissions()->lock_time( $post_id ) );

        $this->assertSame( '', Stores::user_packs()->paypal_subscription_id( $user_id ) );
        Stores::user_packs()->set_paypal_subscription_id( $user_id, 'I-ABC' );
        $this->assertSame( 'I-ABC', get_user_meta( $user_id, '_wpuf_paypal_subscription_id', true ) );
        $this->assertSame( 'I-ABC', Stores::user_packs()->paypal_subscription_id( $user_id ) );

        global $wpdb;
        if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wpuf_transaction' ) ) ) {
            $this->markTestSkipped( 'no transaction table in this test database' );
        }

        $this->assertNull( Stores::transactions()->last_completed_date( $user_id ) );
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => $user_id, 'status' => 'completed', 'payment_type' => 'paypal', 'created' => '2026-01-02 03:04:05', 'cost' => 1, 'transaction_id' => 'T1' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $this->assertSame( '2026-01-02 03:04:05', Stores::transactions()->last_completed_date( $user_id ) );
        $this->assertSame( 'paypal', Stores::transactions()->last_completed_gateway( $user_id ) );
    }
}
