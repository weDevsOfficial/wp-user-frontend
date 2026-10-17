<?php
/**
 * Subscribers REST route of the subscriptions app (the React Subscribers view).
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\SubscribersController
 * @covers \WeDevs\Wpuf\Admin\Screens\Subscribers
 */
class SubscribersRestTest extends WP_UnitTestCase {

    /**
     * Administrator.
     *
     * @var int
     */
    private $admin = 0;

    /**
     * Pack ID.
     *
     * @var int
     */
    private $pack = 0;

    public function set_up() {
        parent::set_up();

        global $wpdb;

        $this->admin = self::factory()->user->create( [ 'role' => 'administrator', 'display_name' => 'Pack Admin' ] );
        $this->pack  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Gold', 'post_status' => 'publish' ] );
        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_subscribers" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        do_action( 'rest_api_init' );
    }

    /**
     * Run a GET request.
     *
     * @param int   $pack_id Pack ID
     * @param array $params  Query params
     *
     * @return WP_REST_Response
     */
    private function call( $pack_id, $params = [] ) {
        $request = new WP_REST_Request( 'GET', '/wpuf/v1/admin/subscriptions/' . $pack_id . '/subscribers' );
        $request->set_query_params( $params );

        return rest_do_request( $request );
    }

    /**
     * A subscribers row.
     *
     * @param array $data Columns
     *
     * @return void
     */
    private function subscriber( $data = [] ) {
        global $wpdb;

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . 'wpuf_subscribers',
            array_merge(
                [
                    'user_id'             => $this->admin,
                    'name'                => 'Pack Admin',
                    'subscribtion_id'     => $this->pack,
                    'subscribtion_status' => 'Completed',
                    'gateway'             => 'paypal',
                    'transaction_id'      => 'TX1',
                    'starts_from'         => '2026-09-01 10:00:00',
                    'expire'              => 'unlimited',
                ],
                $data
            )
        );
    }

    public function test_lists_rows_counts_and_formats() {
        wp_set_current_user( $this->admin );
        $this->subscriber();
        $this->subscriber( [ 'subscribtion_status' => 'Cancel', 'transaction_id' => 'TX2', 'gateway' => 'bank', 'expire' => '2026-12-01 10:00:00' ] );
        $this->subscriber( [ 'subscribtion_id' => $this->pack + 999, 'transaction_id' => 'OTHER' ] );

        $response = $this->call( $this->pack );
        $data     = $response->get_data();

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( [ 'id' => $this->pack, 'title' => 'Gold' ], $data['pack'] );
        $this->assertSame( 2, $data['total'] );
        $this->assertSame( [ 'all' => 2, 'Completed' => 1, 'Cancel' => 1 ], $data['counts'] );
        $this->assertSame( [ 'TX2', 'TX1' ], wp_list_pluck( $data['items'], 'transaction_id' ), 'newest row first, other packs left out' );

        $cancel = $data['items'][0];
        $this->assertSame( 'Cancel', $cancel['status'] );
        $this->assertSame( mysql2date( get_option( 'date_format' ), '2026-12-01 10:00:00' ), $cancel['expire'] );
        $this->assertStringEndsWith( '/images/bank.svg', $cancel['gateway_logo'] );
        $this->assertSame( 'unlimited', $data['items'][1]['expire'], 'a non-date stays as stored' );
        $this->assertNotEmpty( $data['items'][1]['edit_url'] );
    }

    public function test_status_sort_and_paging() {
        wp_set_current_user( $this->admin );
        $this->subscriber( [ 'transaction_id' => 'A', 'starts_from' => '2026-01-01 00:00:00' ] );
        $this->subscriber( [ 'transaction_id' => 'B', 'starts_from' => '2026-03-01 00:00:00' ] );
        $this->subscriber( [ 'transaction_id' => 'C', 'starts_from' => '2026-02-01 00:00:00', 'subscribtion_status' => 'Cancel' ] );

        $data = $this->call( $this->pack, [ 'status' => 'Completed' ] )->get_data();
        $this->assertSame( [ 'B', 'A' ], wp_list_pluck( $data['items'], 'transaction_id' ) );

        $data = $this->call( $this->pack, [ 'orderby' => 'starts_from', 'order' => 'asc' ] )->get_data();
        $this->assertSame( [ 'A', 'C', 'B' ], wp_list_pluck( $data['items'], 'transaction_id' ) );

        $data = $this->call( $this->pack, [ 'orderby' => 'starts_from', 'order' => 'asc', 'per_page' => 2, 'page' => 2 ] )->get_data();
        $this->assertSame( [ 'B' ], wp_list_pluck( $data['items'], 'transaction_id' ) );
        $this->assertSame( 2, $data['pages'] );
    }

    public function test_rejects_bad_input_and_non_packs() {
        wp_set_current_user( $this->admin );

        $this->assertSame( 400, $this->call( $this->pack, [ 'status' => 'Free' ] )->get_status() );
        $this->assertSame( 400, $this->call( $this->pack, [ 'orderby' => 'name; DROP' ] )->get_status() );
        $this->assertSame( 400, $this->call( $this->pack, [ 'per_page' => 500 ] )->get_status() );
        $this->assertSame( 404, $this->call( self::factory()->post->create() )->get_status() );
    }

    public function test_needs_subscription_managers() {
        $this->subscriber();

        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->call( $this->pack )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $this->call( $this->pack )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 403, $this->call( $this->pack )->get_status() );
    }

    public function test_old_page_maps_to_app_route() {
        $screen = new \WeDevs\Wpuf\Admin\Screens\Subscribers();

        $_GET['post_ID'] = (string) $this->pack;
        $this->assertSame( '/subscriptions?action=subscribers&id=' . $this->pack, $screen->app_route_for_request() );

        $_GET['post_ID'] = 'x';
        $this->assertSame( '/subscriptions', $screen->app_route_for_request() );

        unset( $_GET['post_ID'] );
        $this->assertSame( '/subscriptions', $screen->app_route_for_request() );
    }

    /**
     * Pack limits and the admin's pack meta for the usage tests.
     *
     * @param array $left      Remaining posts per type
     * @param int   $pack_id   Pack the user is on (default this pack)
     * @param mixed $featured  Remaining featured items
     *
     * @return void
     */
    private function usage_fixture( $left, $pack_id = 0, $featured = '2' ) {
        update_post_meta( $this->pack, '_post_type_name', [ 'post' => '10', 'page' => '-1', 'wp_template' => '-1', 'attachment' => '0' ] );
        update_post_meta( $this->pack, '_total_feature_item', '5' );
        update_user_meta(
            $this->admin,
            '_wpuf_subscription_pack',
            [
                'pack_id'            => $pack_id ? $pack_id : $this->pack,
                'posts'              => $left,
                'total_feature_item' => $featured,
                'expire'             => '2026-12-31 00:00:00',
            ]
        );
    }

    /**
     * Usage line of a type from a row.
     *
     * @param array  $row  Row
     * @param string $type Type key
     *
     * @return array|null
     */
    private function line( $row, $type ) {
        foreach ( $row['usage']['items'] as $line ) {
            if ( $type === $line['type'] ) {
                return $line;
            }
        }

        return null;
    }

    public function test_usage_shows_used_and_remaining_on_the_current_row_only() {
        wp_set_current_user( $this->admin );
        $this->usage_fixture( [ 'post' => '7', 'page' => '-1', 'wp_template' => '-1' ] );
        $this->subscriber( [ 'transaction_id' => 'OLD', 'starts_from' => '2026-01-01 10:00:00' ] );
        $this->subscriber( [ 'transaction_id' => 'NEW', 'starts_from' => '2026-09-01 10:00:00' ] );

        // Two pages after the start, one before: unlimited types count posts since the start.
        self::factory()->post->create( [ 'post_type' => 'page', 'post_author' => $this->admin, 'post_date' => '2026-09-05 10:00:00' ] );
        self::factory()->post->create( [ 'post_type' => 'page', 'post_author' => $this->admin, 'post_date' => '2026-09-06 10:00:00' ] );
        self::factory()->post->create( [ 'post_type' => 'page', 'post_author' => $this->admin, 'post_date' => '2026-08-01 10:00:00' ] );

        $items = $this->call( $this->pack )->get_data()['items'];
        $rows  = wp_list_pluck( $items, 'usage', 'transaction_id' );

        $this->assertNull( $rows['OLD'], 'an older row of the user has no usage' );
        $this->assertNotNull( $rows['NEW'] );

        $current = array_values( array_filter( $items, function ( $item ) {
            return 'NEW' === $item['transaction_id'];
        } ) )[0];

        $this->assertSame( [ 'type' => 'post', 'label' => 'Posts', 'limit' => 10, 'remaining' => 7, 'used' => 3 ], $this->line( $current, 'post' ) );
        $this->assertSame( -1, $this->line( $current, 'page' )['limit'] );
        $this->assertSame( 2, $this->line( $current, 'page' )['used'], 'unlimited: posts since the subscription started' );
        $this->assertNull( $this->line( $current, 'wp_template' ), 'internal unlimited types are left out' );
        $this->assertNull( $this->line( $current, 'attachment' ), 'a type the pack does not allow (0) is left out' );
        $this->assertSame( [ 'type' => 'featured', 'label' => 'Featured items', 'limit' => 5, 'remaining' => 2, 'used' => 3 ], $current['usage']['featured'] );
        $this->assertNotSame( '', $current['usage']['expire'] );
    }

    public function test_no_usage_when_the_user_is_on_another_pack() {
        wp_set_current_user( $this->admin );
        $this->usage_fixture( [ 'post' => '7' ], $this->pack + 999 );
        $this->subscriber();

        $this->assertNull( $this->call( $this->pack )->get_data()['items'][0]['usage'] );
    }

    public function test_used_never_goes_below_zero() {
        wp_set_current_user( $this->admin );
        // Remaining above the limit (the pack limit was lowered later).
        $this->usage_fixture( [ 'post' => '25' ], 0, '9' );
        $this->subscriber();

        $row = $this->call( $this->pack )->get_data()['items'][0];

        $this->assertSame( 0, $this->line( $row, 'post' )['used'] );
        $this->assertSame( 25, $this->line( $row, 'post' )['remaining'] );
        $this->assertSame( 0, $row['usage']['featured']['used'] );
    }
}
