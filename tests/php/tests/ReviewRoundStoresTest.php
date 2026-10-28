<?php
/**
 * Review round: ordered subscriber queries, a user's transaction rows, the AI key resolver and status
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Store and service methods added while putting the last raw queries on the platform.
 */
class ReviewRoundStoresTest extends WP_UnitTestCase {

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
     * SubscriberStore::query(): sort column allowlisted, order, page.
     */
    public function test_subscriber_query_sort_and_page() {
        if ( ! $this->has_tables() ) {
            $this->markTestSkipped( 'no plugin tables in this test database' );
        }

        global $wpdb;
        $store = Stores::subscribers();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_subscribers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        foreach ( [ [ 5, 'Completed' ], [ 3, 'Cancel' ], [ 9, 'Completed' ] ] as $row ) {
            $store->insert( [ 'user_id' => $row[0], 'subscribtion_id' => 77, 'subscribtion_status' => $row[1], 'transaction_id' => 'T' . $row[0] ] );
        }

        $this->assertSame( [ '9', '3', '5' ], wp_list_pluck( $store->query( [ 'pack_id' => 77 ] ), 'user_id' ), 'default: newest first' );
        $this->assertSame( [ '3', '5', '9' ], wp_list_pluck( $store->query( [ 'pack_id' => 77, 'orderby' => 'user_id', 'order' => 'asc' ] ), 'user_id' ) );
        $this->assertSame( [ '9', '3' ], wp_list_pluck( $store->query( [ 'pack_id' => 77, 'number' => 2 ] ), 'user_id' ), 'first page' );
        $this->assertSame( [ '5' ], wp_list_pluck( $store->query( [ 'pack_id' => 77, 'number' => 2, 'offset' => 2 ] ), 'user_id' ), 'second page' );
        $this->assertSame( [ '9', '3', '5' ], wp_list_pluck( $store->query( [ 'pack_id' => 77, 'orderby' => 'id; DROP' ] ), 'user_id' ), 'unknown sort column falls back to id' );
        $this->assertSame( 2, $store->count( [ 'pack_id' => 77, 'status' => 'Completed' ] ) );
        $this->assertSame( 1, $store->count( [ 'pack_id' => 77, 'status' => 'Cancel' ] ) );
    }

    /**
     * TransactionStore: a user's rows (columns allowlisted, paged, arrays on request), their count, a row by gateway id.
     */
    public function test_transaction_rows_for_user() {
        if ( ! $this->has_tables() ) {
            $this->markTestSkipped( 'no plugin tables in this test database' );
        }

        global $wpdb;
        $store = Stores::transactions();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction WHERE user_id IN (808, 809)" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 808, 'status' => 'completed', 'cost' => '10', 'transaction_id' => 'GW-1', 'payer_address' => 'a:1:{s:4:"city";s:4:"Oslo";}', 'created' => '2026-01-01 00:00:00' ] );
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 808, 'status' => 'pending', 'cost' => '20', 'transaction_id' => 'GW-2', 'created' => '2026-02-01 00:00:00' ] );
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 809, 'status' => 'completed', 'cost' => '30', 'transaction_id' => 'GW-3', 'created' => '2026-03-01 00:00:00' ] );

        $rows = $store->rows_for_user( 808 );
        $this->assertSame( [ '20', '10' ], wp_list_pluck( $rows, 'cost' ), 'newest first, this user only' );
        $this->assertIsObject( $rows[0] );
        $this->assertSame( [ 'transaction_id', 'created', 'cost' ], array_keys( $store->rows_for_user( 808, [ 'transaction_id', 'created', 'cost', 'nope' ], 0, 0, true )[0] ), 'columns allowlisted, arrays' );
        $this->assertSame( [ '10' ], wp_list_pluck( $store->rows_for_user( 808, [], 1, 1 ), 'cost' ), 'paged' );
        $this->assertSame( 2, $store->count_for_user( 808 ) );
        $this->assertSame( 0, $store->count_for_user( 810 ) );
        $this->assertSame( 'a:1:{s:4:"city";s:4:"Oslo";}', $store->find_by_transaction_id( 'GW-1' )->payer_address );
        $this->assertNull( $store->find_by_transaction_id( 'GW-9' ) );
        $this->assertNull( $store->find_by_transaction_id( '' ) );

        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction WHERE user_id IN (808, 809)" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Provider_Settings: the key resolver, status() and a merging save().
     */
    public function test_provider_settings_key_status_and_merge() {
        $this->assertSame( 'sk-p', Provider_Settings::api_key_for( [ 'openai_api_key' => 'sk-p', 'ai_api_key' => 'sk-g' ], 'openai' ), 'provider key first' );
        $this->assertSame( 'sk-g', Provider_Settings::api_key_for( [ 'ai_api_key' => 'sk-g' ], 'openai' ), 'legacy generic key as fallback' );
        $this->assertSame( '', Provider_Settings::api_key_for( [ 'openai_api_key' => 'sk-p' ], 'anthropic' ), 'another provider\'s key does not count' );
        $this->assertSame( '', Provider_Settings::api_key_for( 'not-an-array', 'openai' ) );

        $backup = get_option( 'wpuf_ai', [] );
        $ps     = wpuf()->platform()->get( Provider_Settings::class );

        update_option( 'wpuf_ai', [ 'ai_provider' => 'openai', 'ai_model' => 'gpt-4o', 'openai_api_key' => 'sk-keep-1234567890', 'google_api_key' => 'g-keep-1234567890', 'temperature' => '0.3' ] );
        $status = $ps->status();
        $this->assertTrue( $status['configured'] );
        $this->assertTrue( $status['has_api_key'] );
        $this->assertSame( 0.3, $status['temperature'] );
        $this->assertSame( 2000, $status['max_tokens'] );

        update_option( 'wpuf_ai', [ 'ai_provider' => 'openai', 'openai_api_key' => 'sk-keep-1234567890' ] );
        $status = $ps->status();
        $this->assertTrue( $status['has_api_key'] );
        $this->assertFalse( $status['configured'], 'no model: not configured' );

        update_option( 'wpuf_ai', [ 'ai_provider' => 'openai', 'ai_model' => 'gpt-4o', 'openai_api_key' => 'sk-keep-1234567890', 'google_api_key' => 'g-keep-1234567890', 'other' => 'kept' ] );
        $result = $ps->save( [ 'provider' => 'anthropic', 'model' => 'claude-3', 'api_key' => 'an-new-1234567890' ] );
        $after  = get_option( 'wpuf_ai', [] );
        $this->assertTrue( $result['success'] );
        $this->assertSame( 'sk-keep-1234567890', $after['openai_api_key'], 'other providers\' keys survive the AI settings route' );
        $this->assertSame( 'g-keep-1234567890', $after['google_api_key'] );
        $this->assertSame( 'kept', $after['other'] );
        $this->assertSame( 'an-new-1234567890', $after['anthropic_api_key'] );
        $this->assertSame( 'an-new-1234567890', $after['ai_api_key'], 'the generic key the route always wrote is still written' );
        $this->assertSame( 'anthropic', $after['ai_provider'] );
        $this->assertTrue( $ps->read()['settings']['has_api_key'] );

        $this->assertFalse( $ps->save( [ 'api_key' => 'short' ] )['success'], 'too short a key is refused' );

        update_option( 'wpuf_ai', $backup );
    }
}
