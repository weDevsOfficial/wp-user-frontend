<?php
/**
 * SubscriberStore, UserPackStore, SubmissionStore and the subscription / transaction lookups
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\SubmissionStore;
use WeDevs\Wpuf\Platform\Stores\SubscriberStore;
use WeDevs\Wpuf\Platform\Stores\SubscriptionStore;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WeDevs\Wpuf\Platform\Stores\UserPackStore;

/**
 * The subscription domain stores: subscribers table, user pack meta, submission meta, pack and transaction lookups.
 */
class SubscriptionDomainStoresTest extends WP_UnitTestCase {

    /**
     * The plugin tables exist in this test database.
     *
     * @return bool
     */
    private function has_tables() {
        global $wpdb;

        return (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wpuf_subscribers' ) ) && (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wpuf_transaction' ) );
    }

    /**
     * The stores are shared and reachable through the facade.
     */
    public function test_stores_are_shared() {
        foreach ( [ 'subscribers' => SubscriberStore::class, 'user_packs' => UserPackStore::class, 'submissions' => SubmissionStore::class ] as $method => $class ) {
            $this->assertInstanceOf( $class, Stores::$method() );
            $this->assertSame( Stores::$method(), wpuf()->platform()->get( $class ) );
        }
    }

    /**
     * SubscriberStore: insert, query / count by pack and status, distinct user ids, cancel.
     */
    public function test_subscriber_store_rows() {
        if ( ! $this->has_tables() ) {
            $this->markTestSkipped( 'no plugin tables in this test database' );
        }

        global $wpdb;
        $store = Stores::subscribers();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_subscribers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        $a = $store->insert( [ 'user_id' => 7, 'name' => 'A', 'subscribtion_id' => 10, 'subscribtion_status' => 'completed', 'gateway' => 'bank', 'transaction_id' => 'T1', 'starts_from' => '01-01-2026', 'expire' => 'recurring' ] );
        $store->insert( [ 'user_id' => 7, 'subscribtion_id' => 10, 'subscribtion_status' => 'Free', 'transaction_id' => 'Free' ] );
        $store->insert( [ 'user_id' => 8, 'subscribtion_id' => 11, 'subscribtion_status' => 'completed', 'transaction_id' => 'T2' ] );

        $this->assertIsInt( $a );
        $this->assertTrue( $store->exists( $a ) );
        $this->assertSame( 'bank', $store->read( $a )->gateway );
        $this->assertSame( '', $store->read( $a + 1 )->gateway, 'missing columns stored empty' );
        $this->assertSame( 3, $store->count() );
        $this->assertSame( 2, $store->count( [ 'pack_id' => 10 ] ) );
        $this->assertSame( 2, $store->count( [ 'status' => 'completed' ] ) );
        $this->assertSame( 1, $store->count( [ 'pack_id' => 10, 'status' => 'completed' ] ) );
        $this->assertSame( [ 7 ], $store->user_ids( 10 ), 'distinct' );
        $this->assertSame( [ 7, 8 ], $store->user_ids( '', 'completed' ) );
        $this->assertSame( [ 7, 8 ], $store->user_ids() );

        $this->assertSame( 1, $store->cancel( 7, 10, 'T1' ) );
        $this->assertSame( 'cancel', $store->read( $a )->subscribtion_status );
        $this->assertSame( [ 7, 8 ], $store->user_ids( '', 'completed' ), 'the free row and user 8 stay' );
    }

    /**
     * UserPackStore: pack meta, pack id, free packs used, trial flag, expiry notices, every stored pack.
     */
    public function test_user_pack_store() {
        $store = Stores::user_packs();
        $user  = self::factory()->user->create();

        $this->assertFalse( $store->exists( $user ) );
        $this->assertFalse( $store->pack_id( $user ) );

        $store->write( $user, [ 'pack_id' => 42, 'posts' => [ 'post' => 3 ] ] );
        $this->assertTrue( $store->exists( $user ) );
        $this->assertSame( 42, $store->pack_id( $user ) );
        $this->assertSame( [ 'post' => 3 ], $store->read( $user )['posts'] );
        $this->assertSame( [ 'pack_id' => 42, 'posts' => [ 'post' => 3 ] ], get_user_meta( $user, '_wpuf_subscription_pack', true ) );

        $rows = $store->query();
        $this->assertContains( (string) $user, wp_list_pluck( $rows, 'user_id' ) );
        $this->assertSame( count( $rows ), $store->count() );

        $this->assertSame( [], $store->free_packs_used( $user ) );
        $store->mark_free_pack_used( $user, 42 );
        $store->mark_free_pack_used( $user, 43 );
        $this->assertSame( [ 42 => 42, 43 => 43 ], $store->free_packs_used( $user ) );

        $this->assertFalse( $store->used_trial( $user ) );
        update_user_meta( $user, '_wpuf_used_trial', 'yes' );
        $this->assertTrue( $store->used_trial( $user ) );

        update_user_meta( $user, 'wpuf_pre_sub_exp', 'sent' );
        $store->clear_expiry_notices( $user );
        $this->assertSame( '', get_user_meta( $user, 'wpuf_pre_sub_exp', true ) );

        $store->write( $user, 'cancel' );
        $this->assertSame( 'cancel', $store->read( $user ) );
        $this->assertFalse( $store->pack_id( $user ) );
        $store->delete( $user );
        $this->assertFalse( $store->exists( $user ) );
    }

    /**
     * SubmissionStore: order set once, payment status, quota flag, lookup by order, post status.
     */
    public function test_submission_store() {
        $store = Stores::submissions();
        $form  = Stores::forms()->create( [ 'post_title' => 'F' ] );
        $post  = self::factory()->post->create( [ 'post_status' => 'pending' ] );
        update_post_meta( $post, '_wpuf_form_id', $form );

        $this->assertTrue( $store->exists( $post ) );
        $this->assertSame( $form, $store->form_id( $post ) );
        $this->assertSame( '', $store->order_id( $post ) );

        $store->set_order_once( $post, 'ORDER-1' );
        $store->set_order_once( $post, 'ORDER-2' );
        $this->assertSame( 'ORDER-1', $store->order_id( $post ), 'the first order id stays' );

        $store->set_payment_status( $post, 'pending' );
        $store->set_quota_flag( $post, 'new_draft' );
        $read = $store->read( $post );
        $this->assertSame( [ 'ORDER-1', 'pending', 'new_draft' ], [ $read['order_id'], $read['payment_status'], $read['quota_flag'] ] );

        $found = $store->find_by_order( 'ORDER-1' );
        $this->assertSame( $post, (int) $found->ID );
        $this->assertSame( 'pending', $found->post_status );
        $this->assertCount( 1, $store->query( [ 'order_id' => 'ORDER-1' ] ) );
        $this->assertSame( 1, $store->count( [ 'form_id' => $form ] ) );

        $store->set_post_status( $post, 'publish' );
        $this->assertSame( 'publish', get_post_status( $post ) );
        $this->assertNull( $store->find_by_order( 'ORDER-1' ), 'published posts are not pending orders' );
        $this->assertNull( $store->read( 999999 ) );
    }

    /**
     * SubscriptionStore::ordered() / counts; TransactionStore lookups.
     */
    public function test_pack_and_transaction_lookups() {
        $packs = Stores::subscriptions();
        $b     = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'B' ] );
        $a     = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'A' ] );
        self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Draft', 'post_status' => 'draft' ] );
        update_post_meta( $b, '_sort_order', 2 );
        update_post_meta( $a, '_sort_order', 5 );

        $ordered = $packs->ordered();
        $this->assertSame( [ $b, $a ], wp_list_pluck( $ordered, 'ID' ) );
        $this->assertSame( '2', $ordered[0]->meta_value['_sort_order'] );

        $c = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'C' ] );
        $this->assertNotContains( $c, wp_list_pluck( $packs->ordered(), 'ID' ), 'no _sort_order meta at all: left out, as get_subscriptions() did' );
        update_post_meta( $c, '_sort_order', '' );
        $this->assertContains( $c, wp_list_pluck( $packs->ordered(), 'ID' ) );
        $this->assertSame( '1', get_post_meta( $c, '_sort_order', true ), 'an empty order gets 1' );

        $counts = $packs->counts_by_status();
        $this->assertSame( 3, $counts['publish'] );
        $this->assertSame( 1, $counts['draft'] );
        $this->assertSame( 4, $counts['all'] );
        $this->assertSame( 4, $packs->count_status() );
        $this->assertSame( 1, $packs->count_status( 'draft' ) );

        if ( ! $this->has_tables() ) {
            return;
        }

        global $wpdb;
        $tx = Stores::transactions();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 7, 'status' => 'completed', 'cost' => '1', 'pack_id' => 10, 'payment_type' => 'bank', 'transaction_id' => 'T1', 'created' => '2026-01-01 00:00:00' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 7, 'status' => 'completed', 'cost' => '2', 'pack_id' => 10, 'payment_type' => 'paypal', 'transaction_id' => 'T2', 'created' => '2026-02-01 00:00:00' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        $this->assertSame( 'T2', $tx->latest_for_pack( 7, 10 )->transaction_id );
        $this->assertSame( 'T1', $tx->transaction_id_for_pack( 7, 10 ) );
        $this->assertSame( 'paypal', $tx->last_completed_gateway( 7 ) );
        $this->assertNull( $tx->latest_for_pack( 8, 10 ) );
        $this->assertNull( $tx->transaction_id_for_pack( 8, 10 ) );
        $this->assertSame( '', $tx->last_completed_gateway( 8 ) );
    }
}
