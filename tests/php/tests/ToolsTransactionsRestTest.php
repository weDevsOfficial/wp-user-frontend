<?php
/**
 * Tools and Transactions REST routes of the admin app (task 5e).
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\ToolsController
 * @covers \WeDevs\Wpuf\Platform\Tools\ToolsService
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\TransactionsController
 * @covers \WeDevs\Wpuf\Platform\Transactions\TransactionService
 */
class ToolsTransactionsRestTest extends WP_UnitTestCase {

    /**
     * Administrator.
     *
     * @var int
     */
    private $admin = 0;

    public function set_up() {
        parent::set_up();

        global $wpdb;

        $this->admin = self::factory()->user->create( [ 'role' => 'administrator' ] );
        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        do_action( 'rest_api_init' );
    }

    /**
     * Run a REST request.
     *
     * @param string $method Method
     * @param string $route  Route under wpuf/v1
     * @param array  $params Params
     *
     * @return WP_REST_Response
     */
    private function call( $method, $route, $params = [] ) {
        $request = new WP_REST_Request( $method, '/wpuf/v1/' . $route );

        if ( 'GET' === $method ) {
            $request->set_query_params( $params );
        } else {
            $request->set_body_params( $params );
        }

        return rest_do_request( $request );
    }

    /**
     * A completed payment row.
     *
     * @param array $data Columns
     *
     * @return int Row ID
     */
    private function transaction( $data = [] ) {
        global $wpdb;

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . 'wpuf_transaction',
            array_merge(
                [
                    'user_id'        => $this->admin,
                    'status'         => 'completed',
                    'cost'           => '10',
                    'tax'            => '1',
                    'post_id'        => 0,
                    'pack_id'        => 0,
                    'payer_email'    => 'payer@example.com',
                    'payment_type'   => 'Paypal',
                    'transaction_id' => 'TX1',
                    'created'        => current_time( 'mysql' ),
                ],
                $data
            )
        );

        return (int) $wpdb->insert_id;
    }

    /**
     * A pending bank order for a pack.
     *
     * @param int $pack_id Pack
     *
     * @return int Order post ID
     */
    private function order( $pack_id ) {
        $order = self::factory()->post->create( [ 'post_type' => 'wpuf_order', 'post_status' => 'publish' ] );

        update_post_meta(
            $order,
            '_data',
            [
                'type'        => 'pack',
                'item_number' => $pack_id,
                'price'       => 15,
                'subtotal'    => 15,
                'tax'         => 0,
                'date'        => current_time( 'mysql' ),
                'user_info'   => [
                    'id'         => $this->admin,
                    'first_name' => 'Order',
                    'last_name'  => 'Buyer',
                    'email'      => 'buyer@example.com',
                ],
                'post_data'   => [ 'wpuf_payment_method' => 'bank' ],
            ]
        );

        return $order;
    }

    public function test_tools_routes_need_permissions() {
        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->call( 'GET', 'admin/tools/shortcodes' )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 403, $this->call( 'POST', 'admin/tools/reset-settings' )->get_status() );
        $this->assertSame( 403, $this->call( 'GET', 'admin/tools/shortcodes' )->get_status() );
    }

    public function test_reset_settings_deletes_the_plugin_options() {
        wp_set_current_user( $this->admin );
        update_option( 'wpuf_general', [ 'a' => 'b' ] );
        update_option( '_wpuf_page_created', '1' );

        $this->assertSame( 200, $this->call( 'POST', 'admin/tools/reset-settings' )->get_status() );
        $this->assertFalse( get_option( 'wpuf_general' ) );
        $this->assertFalse( get_option( '_wpuf_page_created' ) );
    }

    public function test_delete_forms_only_takes_plugin_types() {
        wp_set_current_user( $this->admin );
        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );
        self::factory()->post->create( [ 'post_type' => 'wpuf_coupon', 'post_status' => 'draft' ] );

        $this->assertSame( 400, $this->call( 'POST', 'admin/tools/delete-forms', [ 'type' => 'page' ] )->get_status() );
        $this->assertNotNull( get_post( $page ) );

        $response = $this->call( 'POST', 'admin/tools/delete-forms', [ 'type' => 'wpuf_coupon' ] );
        $this->assertSame( 1, $response->get_data()['deleted'] );
    }

    public function test_export_has_every_form_not_ten() {
        wp_set_current_user( $this->admin );
        self::factory()->post->create_many( 12, [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        $data = $this->call( 'GET', 'admin/tools/export', [ 'type' => 'wpuf_forms' ] )->get_data();

        $this->assertCount( 12, $data['forms'] );
        $this->assertArrayHasKey( 'post_data', $data['forms'][0] );
        $this->assertArrayNotHasKey( 'ID', $data['forms'][0]['post_data'] );
        $this->assertSame( [ 'fields', 'settings', 'notifications' ], array_keys( $data['forms'][0]['meta_data'] ) );
    }

    /**
     * Install WPUF Pages a second time reuses the pages (develop made a second
     * set; QA story 24).
     */
    public function test_install_pages_twice_creates_no_duplicates() {
        wp_set_current_user( $this->admin );

        // A real REST request defines REST_REQUEST, which skips the installer's
        // redirect; rest_do_request() does not, so mark the call the way the
        // onboarding screen does (also no redirect) instead of defining it.
        $_GET['page'] = \WeDevs\Wpuf\Admin\Onboarding::PAGE_SLUG;

        try {
            $this->assertSame( 200, $this->call( 'POST', 'admin/tools/install-pages' )->get_status() );
            $first = wp_count_posts( 'page' )->publish;

            $this->assertSame( 200, $this->call( 'POST', 'admin/tools/install-pages' )->get_status() );
            $this->assertSame( $first, wp_count_posts( 'page' )->publish, 'no new pages on the second run' );
            $this->assertGreaterThan( 0, (int) $first );
        } finally {
            unset( $_GET['page'] );
        }
    }

    public function test_import_without_a_file_is_refused() {
        wp_set_current_user( $this->admin );

        $this->assertSame( 400, $this->call( 'POST', 'admin/tools/import' )->get_status() );
    }

    public function test_transactions_list_counts_and_kinds() {
        wp_set_current_user( $this->admin );
        $this->transaction();
        $this->transaction( [ 'status' => 'refunded', 'transaction_id' => 'TX2' ] );
        $this->order( 0 );

        $data = $this->call( 'GET', 'admin/transactions' )->get_data();

        $this->assertSame( 3, $data['counts']['all'] );
        $this->assertSame( 1, $data['counts']['completed'] );
        $this->assertSame( 1, $data['counts']['pending'] );
        $this->assertEqualsCanonicalizing( [ 'transaction', 'transaction', 'order' ], array_column( $data['items'], 'kind' ) );

        $completed = $this->call( 'GET', 'admin/transactions', [ 'status' => 'completed' ] )->get_data();
        $this->assertSame( 1, $completed['total'] );

        $search = $this->call( 'GET', 'admin/transactions', [ 'search' => 'buyer@' ] )->get_data();
        $this->assertSame( [ 'order' ], array_column( $search['items'], 'kind' ) );

        $gateway = $this->call( 'GET', 'admin/transactions', [ 'gateway' => 'Paypal' ] )->get_data();
        $this->assertSame( 2, $gateway['total'] );
    }

    public function test_actions_check_the_row_kind() {
        wp_set_current_user( $this->admin );
        $row   = $this->transaction();
        $order = $this->order( 0 );

        // A transaction ID sent as an order, and an order sent to delete, do nothing.
        $wrong = $this->call( 'POST', 'admin/transactions/accept', [ 'items' => [ [ 'kind' => 'order', 'id' => $row ] ] ] )->get_data();
        $this->assertSame( 0, $wrong['done'] );
        $wrong = $this->call( 'POST', 'admin/transactions/delete', [ 'items' => [ [ 'kind' => 'order', 'id' => $order ] ] ] )->get_data();
        $this->assertSame( 0, $wrong['done'] );
        $this->assertNotNull( get_post( $order ) );

        $done = $this->call( 'POST', 'admin/transactions/delete', [ 'items' => [ [ 'kind' => 'transaction', 'id' => $row ] ] ] )->get_data();
        $this->assertSame( 1, $done['done'] );
    }

    public function test_accept_stores_the_order_id_and_fires_the_hooks() {
        global $wpdb;

        wp_set_current_user( $this->admin );
        $order    = $this->order( 0 );
        $complete = 0;
        $received = 0;

        add_action(
            'wpuf_gateway_bank_order_complete',
            function () use ( &$complete ) {
                ++$complete;
            }
        );
        add_action(
            'wpuf_payment_received',
            function () use ( &$received ) {
                ++$received;
            }
        );

        // A row with transaction ID 0 must not be overwritten (classic accept updated it).
        $old = $this->transaction( [ 'transaction_id' => '0', 'payer_email' => 'old@example.com' ] );

        $data = $this->call( 'POST', 'admin/transactions/accept', [ 'items' => [ [ 'kind' => 'order', 'id' => $order ] ] ] )->get_data();

        $this->assertSame( 1, $data['done'] );
        $this->assertNull( get_post( $order ) );
        $this->assertSame( 1, $complete );
        $this->assertGreaterThanOrEqual( 1, $received );
        $this->assertSame( 'buyer@example.com', $wpdb->get_var( $wpdb->prepare( "SELECT payer_email FROM {$wpdb->prefix}wpuf_transaction WHERE transaction_id = %s", (string) $order ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $this->assertSame( 'old@example.com', $wpdb->get_var( $wpdb->prepare( "SELECT payer_email FROM {$wpdb->prefix}wpuf_transaction WHERE id = %d", $old ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    }

    public function test_transaction_actions_need_manage_options() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

        $this->assertSame( 403, $this->call( 'GET', 'admin/transactions' )->get_status() );
        $this->assertSame( 403, $this->call( 'POST', 'admin/transactions/delete', [ 'items' => [ [ 'kind' => 'transaction', 'id' => 1 ] ] ] )->get_status() );
    }
}
