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
}
