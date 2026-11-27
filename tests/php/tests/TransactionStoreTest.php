<?php
/**
 * The Transactions data store: rows of the wpuf_transaction table and pending
 * bank orders as one list.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Stores\TransactionStore;

/**
 * @covers \WeDevs\Wpuf\Platform\Stores\TransactionStore
 */
class TransactionStoreTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        global $wpdb;

        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * A completed payment row.
     *
     * @param array $row Column overrides
     *
     * @return int Row ID
     */
    private function row( array $row = [] ) {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'wpuf_transaction',
            wp_parse_args(
                $row,
                [
                    'user_id'        => 1,
                    'status'         => 'completed',
                    'cost'           => '10',
                    'tax'            => '1',
                    'payer_email'    => 'buyer@example.com',
                    'payment_type'   => 'Paypal',
                    'transaction_id' => 'TX' . wp_rand(),
                    'created'        => current_time( 'mysql' ),
                ]
            )
        );

        return (int) $wpdb->insert_id;
    }

    public function test_read_find_exists_and_read_many() {
        $store = new TransactionStore();
        $id    = $this->row();

        $this->assertTrue( $store->exists( $id ) );
        $this->assertFalse( $store->exists( 999999 ) );
        $this->assertSame( 'buyer@example.com', $store->read( $id )->payer_email );
        $this->assertNull( $store->read( 999999 ) );
        $this->assertEquals( $store->read( $id ), $store->find( $id ) );
        $this->assertSame( [ $id ], array_keys( $store->read_many( [ $id, 999999, 0 ] ) ) );
        $this->assertSame( [], $store->read_many( [] ) );
    }

    public function test_query_count_counts_gateways_and_delete() {
        $store = new TransactionStore();
        $a     = $this->row( [ 'payment_type' => 'Paypal' ] );
        $b     = $this->row( [ 'status' => 'refunded', 'payment_type' => 'Stripe', 'payer_email' => 'other@example.com' ] );
        $order = self::factory()->post->create( [ 'post_type' => 'wpuf_order', 'post_status' => 'publish' ] );

        $this->assertSame( 3, $store->count() );
        $this->assertSame( 1, $store->count( [ 'status' => 'completed' ] ) );
        $this->assertSame( 1, $store->count( [ 'status' => 'pending' ] ) );
        $this->assertSame( 1, $store->count( [ 'gateway' => 'Stripe' ] ), 'a gateway filter drops the pending orders' );
        $this->assertSame( 1, $store->count( [ 'search' => 'other@' ] ) );

        $page = $store->query( [ 'orderby' => 'id', 'order' => 'asc', 'per_page' => 2 ] );
        $this->assertSame( [ 'transaction', 'transaction' ], array_column( $page, 'kind' ) );
        $this->assertSame( [ $a, $b ], array_map( 'intval', array_column( $page, 'id' ) ) );
        $this->assertSame( [ 'order' ], array_column( $store->query( [ 'orderby' => 'id', 'order' => 'asc', 'per_page' => 2, 'page' => 2 ] ), 'kind' ) );
        $this->assertSame( [], $store->query( [ 'status' => 'pending', 'gateway' => 'Stripe' ] ), 'no part to query gives no rows' );

        $counts = $store->counts();
        $this->assertSame( [ 'all' => 3, 'completed' => 1, 'pending' => 1 ], [ 'all' => $counts['all'], 'completed' => $counts['completed'], 'pending' => $counts['pending'] ] );
        $this->assertEquals( 10, $counts['income'] );

        $this->assertSame( [ 'Paypal', 'Stripe' ], $store->gateways() );

        $this->assertTrue( $store->delete( $a ) );
        $this->assertFalse( $store->exists( $a ) );
        $this->assertNotNull( get_post( $order ), 'delete() never touches orders' );
    }
}
