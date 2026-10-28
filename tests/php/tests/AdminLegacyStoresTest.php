<?php
/**
 * The admin legacy classes that now persist through the stores: Posting metaboxes, the privacy eraser,
 * Admin_Tools delete, the Pro subscription helpers' store methods
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Admin_Tools;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\SubmissionStore;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WeDevs\Wpuf\Platform\Stores\UserPackStore;
use WeDevs\Wpuf\WPUF_Privacy;

/**
 * Store methods added for the last legacy admin classes, and the classes using them.
 */
class AdminLegacyStoresTest extends WP_UnitTestCase {

    /**
     * SubmissionStore: form selection and the edit lock of a post, as the Posting metaboxes use them.
     */
    public function test_submission_form_and_lock() {
        $store   = Stores::submissions();
        $post_id = self::factory()->post->create();

        $this->assertSame( 0, $store->form_id( $post_id ) );
        $store->set_form_id( $post_id, '12' );
        $this->assertSame( 12, $store->form_id( $post_id ) );
        $this->assertSame( '12', get_post_meta( $post_id, SubmissionStore::FORM, true ), 'stored as given, the old meta key' );
        $store->set_form_id( $post_id, '' );
        $this->assertSame( 0, $store->form_id( $post_id ) );

        $this->assertSame( '', $store->lock( $post_id ) );
        $this->assertSame( '', $store->lock_time( $post_id ) );
        $store->set_lock( $post_id, 'yes' );
        update_post_meta( $post_id, SubmissionStore::LOCK_TIME, '1700000000' );
        $this->assertSame( 'yes', $store->lock( $post_id ) );
        $this->assertSame( 'yes', get_post_meta( $post_id, '_wpuf_lock_editing_post', true ) );
        $this->assertSame( '1700000000', $store->lock_time( $post_id ) );
    }

    /**
     * SubmissionStore::detach_author() and the privacy eraser that uses it.
     */
    public function test_detach_author_and_privacy_eraser() {
        $user  = self::factory()->user->create( [ 'user_email' => 'erase-me@example.com' ] );
        $other = self::factory()->user->create();
        $mine  = self::factory()->post->create( [ 'post_author' => $user ] );
        $his   = self::factory()->post->create( [ 'post_author' => $other ] );

        $this->assertSame( 0, Stores::submissions()->detach_author( [] ) );
        $this->assertSame( 0, Stores::submissions()->detach_author( [ 0, 'x' ] ) );

        $result = WPUF_Privacy::erase_user_data( 'erase-me@example.com' );

        $this->assertTrue( $result['items_removed'] );
        clean_post_cache( $mine );
        clean_post_cache( $his );
        $this->assertSame( '0', get_post( $mine )->post_author );
        $this->assertSame( (string) $other, get_post( $his )->post_author, 'other authors untouched' );
    }

    /**
     * UserPackStore: the expiry notice flags the Pro cron reads and writes.
     */
    public function test_user_pack_notice_flags() {
        $store = Stores::user_packs();
        $user  = self::factory()->user->create();

        $this->assertFalse( $store->notice_sent( $user, UserPackStore::PRE_NOTICE ) );
        $store->mark_notice_sent( $user, UserPackStore::PRE_NOTICE );
        $this->assertTrue( $store->notice_sent( $user, UserPackStore::PRE_NOTICE ) );
        $this->assertSame( 'sent', get_user_meta( $user, 'wpuf_pre_sub_exp', true ), 'the old meta key and value' );
        $this->assertFalse( $store->notice_sent( $user, UserPackStore::POST_NOTICE ) );

        $store->mark_notice_sent( $user, 'not_a_notice' );
        $this->assertFalse( $store->notice_sent( $user, 'not_a_notice' ) );
        $this->assertSame( '', get_user_meta( $user, 'not_a_notice', true ), 'unknown keys are never written' );

        $store->clear_expiry_notices( $user );
        $this->assertFalse( $store->notice_sent( $user, UserPackStore::PRE_NOTICE ) );
    }

    /**
     * TransactionStore::latest_pack_value(): allowlisted column of the latest pack transaction.
     */
    public function test_transaction_latest_pack_value() {
        global $wpdb;

        if ( ! $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'wpuf_transaction' ) ) ) {
            $this->markTestSkipped( 'no plugin tables in this test database' );
        }

        $store = Stores::transactions();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = 909" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 909, 'status' => 'completed', 'pack_id' => 0, 'post_id' => '5', 'created' => '2026-01-01 00:00:00' ] );
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 909, 'status' => 'completed', 'pack_id' => 31, 'created' => '2026-02-01 00:00:00' ] );
        $wpdb->insert( $wpdb->prefix . 'wpuf_transaction', [ 'user_id' => 909, 'status' => 'pending', 'pack_id' => 32, 'created' => '2026-03-01 00:00:00' ] );

        $this->assertSame( '32', $store->latest_pack_value( 909, 'pack_id' ), 'latest pack row, any status, post rows ignored' );
        $this->assertSame( '2026-03-01 00:00:00', $store->latest_pack_value( 909, 'created' ) );
        $this->assertNull( $store->latest_pack_value( 909, 'id; DROP' ), 'unknown column refused' );
        $this->assertNull( $store->latest_pack_value( 910, 'pack_id' ), 'no rows' );
        $this->assertNull( $store->latest_pack_value( 0, 'pack_id' ) );

        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = 909" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    /**
     * Admin_Tools::delete_post_type() still deletes every post of the type (through ToolsService now).
     */
    public function test_admin_tools_delete_post_type_delegates() {
        $a = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        $b = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'draft' ] );
        $c = self::factory()->post->create( [ 'post_type' => 'post' ] );

        ( new Admin_Tools() )->delete_post_type( 'wpuf_forms' );

        $this->assertNull( get_post( $a ) );
        $this->assertNull( get_post( $b ) );
        $this->assertNotNull( get_post( $c ) );
    }
}
