<?php
/**
 * Subscription writers forward to the subscription store and store the same
 * bytes, fire the same hooks and send the same responses (task 2.4b); compared
 * with the pre-store copies in tests/php/src/LegacySubscriptionWriters.php.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\REST\Controllers\SubscriptionController as Subscription_Api;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\SubscriptionStore;

/**
 * Subscription store tests.
 */
class SubscriptionStoreTest extends WP_UnitTestCase {

    /**
     * Pack hooks fired, with the pack id replaced by a marker.
     *
     * @var array
     */
    private $fired = [];

    const PACK_HOOKS = [
        'wpuf_before_update_subscription_pack',
        'wpuf_before_update_subscription_pack_meta',
        'wpuf_after_update_subscription_pack_meta',
        'wpuf_update_subscription_pack',
        'wpuf_before_update_subscription_single_row',
        'wpuf_after_update_subscription_single_row',
    ];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        foreach ( self::PACK_HOOKS as $hook ) {
            add_action(
                $hook,
                function ( $id, $second = null ) use ( $hook ) {
                    $this->fired[] = [ $hook, $second instanceof WP_REST_Request ? 'request' : $second ];
                },
                10,
                2
            );
        }
    }

    public function tear_down() {
        $_POST = [];
        parent::tear_down();
    }

    /**
     * Post fields and raw meta of a pack (dates and the id left out).
     */
    private function snapshot( $id ) {
        global $wpdb;

        $post = get_post( $id );
        $meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key NOT LIKE %s ORDER BY meta_id", $id, '\_edit%' ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        return [
            'post' => $post ? [ $post->post_type, $post->post_status, $post->post_title, $post->post_content ] : null,
            'meta' => $meta,
        ];
    }

    private function request( $subscription ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
        $request->set_param( 'subscription', $subscription );

        return $request;
    }

    /**
     * A payload with floats, lists, term ids (good and bad), HTML and slashes.
     */
    private function payload( $id = 0 ) {
        return [
            'ID'           => $id,
            'post_title'   => 'Gold "pack" ü',
            'post_status'  => 'publish',
            'post_content' => "Line one\nLine <b>two</b>",
            'meta_value'   => [
                '_billing_amount'            => '9.99',
                '_expiration_number'         => '30',
                '_expiration_period'         => 'day',
                '_recurring_pay'             => 'yes',
                '_billing_cycle_number'      => '1',
                '_cycle_period'              => '',
                '_trial_status'              => 'no',
                '_post_type_name'            => [ 'post' => '5', 'page' => '-1' ],
                'additional_cpt_options'     => [ 'product' => '2' ],
                '_enable_post_expiration'    => 'on',
                '_post_expiration_number'    => '7',
                '_post_expiration_period'    => 'day',
                '_post_expiration_message'   => '<p>Expired <script>x</script></p>',
                '_total_feature_item'        => '3',
                '_sort_order'                => '0',
                '_sub_allowed_term_ids'      => [],
                '_sub_view_allowed_term_ids' => [ '4', 7, 'x', '-2' ],
                'postnum_rollback_on_delete' => 'on',
            ],
        ];
    }

    /**
     * Run the same call on the legacy controller and the current one.
     *
     * @return array [ legacy response, current response, legacy fired hooks, current fired hooks ]
     */
    private function both( $method, $legacy_request, $current_request ) {
        $this->fired = [];
        $legacy      = ( new WPUF_Legacy_Subscription_Api() )->$method( $legacy_request );
        $legacy_hook = $this->fired;

        $this->fired = [];
        $current     = ( new Subscription_Api() )->$method( $current_request );

        return [ $legacy, $current, $legacy_hook, $this->fired ];
    }

    private function same_response( $legacy, $current ) {
        $this->assertSame( $legacy->get_data(), $current->get_data() );
        $this->assertSame( $legacy->get_status(), $current->get_status() );
    }

    private function last_pack() {
        $ids = get_posts( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'any', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'DESC', 'fields' => 'ids' ] );

        return (int) $ids[0];
    }

    public function test_store_is_shared_from_the_container() {
        $this->assertInstanceOf( SubscriptionStore::class, Stores::subscriptions() );
        $this->assertSame( Stores::subscriptions(), wpuf()->platform()->get( SubscriptionStore::class ) );
    }

    public function test_rest_create_stores_the_same_bytes_hooks_and_response() {
        $this->fired = [];
        $legacy      = ( new WPUF_Legacy_Subscription_Api() )->create_or_update_item( $this->request( $this->payload() ) );
        $legacy_id   = $this->last_pack();
        $legacy_hook = $this->fired;
        delete_option( 'wpuf_taxonomy_view_restrictions_enabled' );

        $this->fired = [];
        $current     = ( new Subscription_Api() )->create_or_update_item( $this->request( $this->payload() ) );
        $current_id  = $this->last_pack();

        $this->assertNotSame( $legacy_id, $current_id );
        $this->same_response( $legacy, $current );
        $this->assertEquals( $this->snapshot( $legacy_id ), $this->snapshot( $current_id ) );
        $this->assertSame( $legacy_hook, $this->fired, 'same pack hooks, same order, same args' );
        $this->assertSame( 'yes', get_option( 'wpuf_taxonomy_view_restrictions_enabled' ) );
    }

    public function test_rest_update_stores_the_same_bytes() {
        $legacy_id  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Old' ] );
        $current_id = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Old' ] );
        update_post_meta( $legacy_id, '_sub_allowed_term_ids', [ '3' ] );
        update_post_meta( $current_id, '_sub_allowed_term_ids', [ '3' ] );

        list( $legacy, $current, $legacy_hook, $current_hook ) = $this->both( 'edit_item', $this->request( $this->payload( $legacy_id ) ), $this->request( $this->payload( $current_id ) ) );

        $this->same_response( $legacy, $current );
        $this->assertEquals( $this->snapshot( $legacy_id ), $this->snapshot( $current_id ) );
        $this->assertSame( $legacy_hook, $current_hook );
    }

    public function test_empty_posting_restriction_reaches_listeners_on_create_only() {
        $seen = [];
        $spy  = function ( $id, $request ) use ( &$seen ) {
            $subscription = $request->get_param( 'subscription' );
            $seen[]       = array_key_exists( '_sub_allowed_term_ids', (array) $subscription['meta_value'] );
        };
        add_action( 'wpuf_before_update_subscription_pack_meta', $spy, 10, 2 );

        // New pack: the empty list goes to the listeners (pro stores a:0:{} like develop).
        ( new Subscription_Api() )->create_or_update_item( $this->request( $this->payload() ) );
        $new_id = $this->last_pack();

        // Existing pack that never stored it: an untouched save keeps it absent.
        delete_post_meta( $new_id, '_sub_allowed_term_ids' );
        ( new Subscription_Api() )->edit_item( $this->request( $this->payload( $new_id ) ) );

        remove_action( 'wpuf_before_update_subscription_pack_meta', $spy, 10 );

        $this->assertSame( [ true, false ], $seen );
    }

    public function test_rest_minimal_payload_and_refusals_match() {
        $minimal = [ 'post_title' => 'Bare' ];

        $this->fired = [];
        ( new WPUF_Legacy_Subscription_Api() )->create_or_update_item( $this->request( $minimal ) );
        $legacy_id = $this->last_pack();
        ( new Subscription_Api() )->create_or_update_item( $this->request( $minimal ) );
        $this->assertEquals( $this->snapshot( $legacy_id ), $this->snapshot( $this->last_pack() ), 'defaults when nothing is sent' );

        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        foreach ( [ [ 'ID' => $page, 'post_title' => 'X' ], [ 'post_title' => 'Has # sign' ], [] ] as $payload ) {
            list( $legacy, $current ) = $this->both( 'create_or_update_item', $this->request( $payload ), $this->request( $payload ) );
            $this->same_response( $legacy, $current );
        }
    }

    public function test_no_expiry_values_store_an_empty_duration() {
        foreach ( [ [ '-1', 'day' ], [ '7', 'forever' ], [ '0', 'week' ] ] as $case ) {
            $payload                                           = $this->payload();
            $payload['post_title']                             = 'No expiry ' . implode( ' ', $case );
            $payload['meta_value']['_post_expiration_number'] = $case[0];
            $payload['meta_value']['_post_expiration_period'] = $case[1];

            ( new Subscription_Api() )->create_or_update_item( $this->request( $payload ) );

            $this->assertSame( '', get_post_meta( $this->last_pack(), '_post_expiration_time', true ), implode( ' ', $case ) );
        }

        $payload = $this->payload();
        ( new Subscription_Api() )->create_or_update_item( $this->request( $payload ) );
        $this->assertSame( '7 day', get_post_meta( $this->last_pack(), '_post_expiration_time', true ) );
    }

    public function test_single_row_edit_matches() {
        $legacy_id  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'Pack' ] );
        $current_id = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'Pack' ] );
        $row        = function ( $id, $name, $value ) {
            return $this->request( [ 'ID' => $id, 'edit_single_row' => true, 'edit_row_name' => $name, 'edit_row_value' => $value ] );
        };

        foreach ( [ [ 'post_status', 'draft' ], [ 'post_title', 'Hacked' ], [ 'post_status', 'bogus' ], [ 'post_status', '' ] ] as $edit ) {
            list( $legacy, $current, $legacy_hook, $current_hook ) = $this->both( 'edit_item', $row( $legacy_id, $edit[0], $edit[1] ), $row( $current_id, $edit[0], $edit[1] ) );

            $this->same_response( $legacy, $current );
            $this->assertSame( $legacy_hook, $current_hook );
            $this->assertEquals( get_post( $legacy_id )->post_status, get_post( $current_id )->post_status );
            $this->assertSame( 'Pack', get_post( $current_id )->post_title, 'fields outside the allowlist are not changed' );
        }
    }

    public function test_delete_matches_and_refuses_other_post_types() {
        $legacy_id  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        $current_id = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        $delete     = function ( $id ) {
            $request = new WP_REST_Request( 'DELETE', '/wpuf/v1/wpuf_subscription/' . $id );
            $request->set_param( 'subscription_id', $id );

            return $request;
        };

        list( $legacy, $current ) = $this->both( 'delete_item', $delete( $legacy_id ), $delete( $current_id ) );
        $this->same_response( $legacy, $current );
        $this->assertNull( get_post( $current_id ) );

        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        list( $legacy, $current ) = $this->both( 'delete_item', $delete( $page ), $delete( $page ) );
        $this->same_response( $legacy, $current );
        $this->assertNotNull( get_post( $page ) );
        $this->assertFalse( Stores::subscriptions()->delete( $page ) );
    }
}
