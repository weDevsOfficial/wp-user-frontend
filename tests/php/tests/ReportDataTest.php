<?php
/**
 * Pro Reports module: Report_Data ranges and counts, Report_Controller route
 *
 * The module's classes are required straight from the Pro checkout next to
 * this plugin (no Pro bootstrap), so the suite runs without Pro: the test is
 * skipped when that checkout is not there.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Modules\Report\Report_Controller;
use WeDevs\Wpuf\Pro\Modules\Report\Report_Data;

/**
 * @covers \WeDevs\Wpuf\Pro\Modules\Report\Report_Data
 * @covers \WeDevs\Wpuf\Pro\Modules\Report\Report_Controller
 */
class ReportDataTest extends WP_UnitTestCase {

    /**
     * Report queries.
     *
     * @var Report_Data
     */
    private $data;

    public static function set_up_before_class() {
        parent::set_up_before_class();

        $module = dirname( WPUF_TESTS_PLUGIN_DIR ) . '/wpuf-pro/modules/report/includes/';

        if ( ! class_exists( Report_Data::class ) && file_exists( $module . 'Report_Data.php' ) ) {
            require_once $module . 'Report_Data.php';
            require_once $module . 'Report_Controller.php';
        }
    }

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( Report_Data::class ) ) {
            $this->markTestSkipped( 'The Pro Reports module is not next to this plugin.' );
        }

        global $wpdb;

        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->query( "DELETE FROM {$wpdb->prefix}wpuf_transaction" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

        $this->data = new Report_Data();
    }

    public function tear_down() {
        global $wp_rest_server;

        $wp_rest_server = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        parent::tear_down();
    }

    /**
     * Every preset resolves to a `Y-m-d` period with the period before it.
     */
    public function test_range_resolves_every_preset() {
        $today = current_time( 'Y-m-d' );
        $year  = (int) substr( $today, 0, 4 );
        $month = (int) substr( $today, 5, 2 );

        $ranges = [];

        foreach ( Report_Data::PRESETS as $preset ) {
            $range = $this->data->range( $preset, '2024-02-10', '2024-02-20' );

            foreach ( [ 'from', 'to', 'last_from', 'last_to' ] as $key ) {
                $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $range[ $key ], "$preset $key" );
            }

            $this->assertLessThanOrEqual( strtotime( $range['to'] ), strtotime( $range['from'] ), "$preset: from <= to" );
            $this->assertLessThan( strtotime( $range['from'] ), strtotime( $range['last_to'] ), "$preset: the previous period ends before the period" );
            $this->assertSame( $preset, $range['preset'] );
            $this->assertContains( $range['group'], [ 'day', 'month' ] );

            $ranges[ $preset ] = $range;
        }

        $this->assertSame( gmdate( 'Y-m-01', strtotime( $today ) ), $ranges['this_month']['from'] );
        $this->assertSame( gmdate( 'Y-m-t', strtotime( $today ) ), $ranges['this_month']['to'] );
        $this->assertSame( 'day', $ranges['this_month']['group'] );
        $this->assertSame( gmdate( 'Y-m-t', strtotime( $ranges['this_month']['from'] . ' -1 day' ) ), $ranges['this_month']['last_to'] );

        $this->assertSame( gmdate( 'Y-m-01', mktime( 0, 0, 0, $month - 1, 1, $year ) ), $ranges['last_month']['from'] );
        $this->assertSame( gmdate( 'Y-m-t', mktime( 0, 0, 0, $month - 1, 1, $year ) ), $ranges['last_month']['to'] );

        $quarter_month = ( (int) ceil( $month / 3 ) - 1 ) * 3 + 1;
        $this->assertSame( gmdate( 'Y-m-01', mktime( 0, 0, 0, $quarter_month, 1, $year ) ), $ranges['this_quarter']['from'] );
        $this->assertSame( gmdate( 'Y-m-t', mktime( 0, 0, 0, $quarter_month + 2, 1, $year ) ), $ranges['this_quarter']['to'] );
        $this->assertSame( 'month', $ranges['this_quarter']['group'] );
        $this->assertSame( $ranges['last_quarter']['to'], $ranges['this_quarter']['last_to'] );
        $this->assertSame( $ranges['last_quarter']['from'], $ranges['this_quarter']['last_from'] );

        $this->assertSame( gmdate( 'Y-m-01', mktime( 0, 0, 0, $month - 6, 1, $year ) ), $ranges['last_6_month']['from'] );
        $this->assertSame( $ranges['last_month']['to'], $ranges['last_6_month']['to'] );

        $this->assertSame( "$year-01-01", $ranges['this_year']['from'] );
        $this->assertSame( "$year-12-31", $ranges['this_year']['to'] );
        $this->assertSame( ( $year - 1 ) . '-01-01', $ranges['last_year']['from'] );
        $this->assertSame( ( $year - 1 ) . '-12-31', $ranges['last_year']['to'] );
        $this->assertSame( $ranges['last_year']['from'], $ranges['this_year']['last_from'] );

        $this->assertSame( '2024-02-10', $ranges['custom']['from'] );
        $this->assertSame( '2024-02-20', $ranges['custom']['to'] );
        $this->assertSame( '2024-01-30', $ranges['custom']['last_from'], 'the previous period has the same number of days' );
        $this->assertSame( '2024-02-09', $ranges['custom']['last_to'] );
        $this->assertSame( 'day', $ranges['custom']['group'] );
    }

    public function test_custom_range_swaps_reversed_days_and_buckets_long_spans_by_month() {
        $swapped = $this->data->range( 'custom', '2024-03-05', '2024-03-01' );
        $this->assertSame( [ '2024-03-01', '2024-03-05' ], [ $swapped['from'], $swapped['to'] ] );

        $long = $this->data->range( 'custom', '2024-01-01', '2024-06-30' );
        $this->assertSame( 'month', $long['group'] );

        $this->assertSame( 'this_month', $this->data->range( 'nope' )['preset'], 'an unknown preset falls back to this month' );
        $this->assertSame( '', $this->data->valid_day( '2024-02-30' ) );
        $this->assertSame( '2024-02-29', $this->data->valid_day( '2024-02-29' ) );
    }

    public function test_registrations_count_users_registered_in_the_range_per_day() {
        $range = $this->data->range( 'custom', '2024-02-10', '2024-02-12' );

        $this->user_on( '2024-02-10 09:00:00' );
        $this->user_on( '2024-02-10 18:00:00' );
        $this->user_on( '2024-02-12 00:30:00' );
        $this->user_on( '2024-02-13 00:00:00', 'subscriber' );
        $this->user_on( '2024-02-08 12:00:00', 'subscriber' );

        $report = $this->data->registrations( $range );

        $this->assertSame( [ '2024-02-10', '2024-02-11', '2024-02-12' ], $report['keys'] );
        $this->assertCount( 3, $report['labels'] );
        $this->assertSame( [ 2, 0, 1 ], $report['series']['current'] );
        $this->assertSame( [ 0, 1, 0 ], $report['series']['previous'], 'the previous period (Feb 7 to 9) aligned by position' );
        $this->assertSame( 3, $report['totals']['current'] );
        $this->assertSame( 1, $report['totals']['previous'] );
        $this->assertSame( 200.0, $report['totals']['change'] );

        $roles = wp_list_pluck( $report['breakdown'], 'value', 'key' );
        $this->assertArrayHasKey( 'subscriber', $roles );
        $this->assertGreaterThanOrEqual( 5, $roles['subscriber'] );
    }

    public function test_posts_count_published_posts_per_day_with_types_and_authors() {
        $range  = $this->data->range( 'custom', '2024-02-10', '2024-02-12' );
        $author = self::factory()->user->create( [ 'role' => 'editor', 'display_name' => 'Report Author' ] );

        self::factory()->post->create( [ 'post_author' => $author, 'post_date' => '2024-02-10 10:00:00', 'post_status' => 'publish' ] );
        self::factory()->post->create( [ 'post_author' => $author, 'post_date' => '2024-02-11 10:00:00', 'post_status' => 'publish', 'post_type' => 'page' ] );
        self::factory()->post->create( [ 'post_author' => $author, 'post_date' => '2024-02-11 11:00:00', 'post_status' => 'draft' ] );
        self::factory()->post->create( [ 'post_author' => $author, 'post_date' => '2024-02-20 10:00:00', 'post_status' => 'publish' ] );

        $report = $this->data->posts( $range );

        $this->assertSame( [ 1, 1, 0 ], $report['series']['current'] );
        $this->assertSame( 2, $report['totals']['current'] );

        $types = wp_list_pluck( $report['breakdown'], 'value', 'key' );
        $this->assertSame( 2, $types['post'], 'all published posts, all time' );
        $this->assertSame( 1, $types['page'] );
        $this->assertArrayNotHasKey( 'attachment', $types );

        $columns = wp_list_pluck( $report['authors']['columns'], 'key' );
        $this->assertContains( 'post', $columns );
        $this->assertContains( 'page', $columns );

        $rows = array_values( array_filter( $report['authors']['rows'], function ( $row ) use ( $author ) {
            return $row['id'] === $author;
        } ) );
        $this->assertCount( 1, $rows );
        $this->assertSame( 'Report Author', $rows[0]['name'] );
        $this->assertSame( 2, $rows[0]['counts']['post'] );
        $this->assertSame( 1, $rows[0]['counts']['page'] );
        $this->assertSame( 3, $rows[0]['total'] );
        $this->assertStringContainsString( 'edit.php?post_type=post&author=' . $author, $rows[0]['links']['post'] );
    }

    public function test_subscriptions_and_transactions_sum_completed_payments() {
        $range = $this->data->range( 'custom', '2024-02-10', '2024-02-12' );
        $pack  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_title' => 'Gold', 'post_status' => 'publish' ] );

        $this->transaction( '2024-02-10 10:00:00', 'completed', 10, 1, $pack );
        $this->transaction( '2024-02-10 12:00:00', 'completed', 20, 2, $pack );
        $this->transaction( '2024-02-12 12:00:00', 'completed', 5, 0, 0, 7 );
        $this->transaction( '2024-02-11 12:00:00', 'pending', 99, 9, $pack );
        $this->transaction( '2024-02-08 12:00:00', 'completed', 40, 4, $pack );

        $subscriptions = $this->data->subscriptions( $range );

        $this->assertSame( [ 30.0, 0.0, 0.0 ], $subscriptions['series']['current'] );
        $this->assertSame( [ 0.0, 40.0, 0.0 ], $subscriptions['series']['previous'] );
        $this->assertSame( [ 2, 0, 0 ], $subscriptions['series']['packs'] );
        $this->assertSame( 30.0, $subscriptions['totals']['sales'] );
        $this->assertSame( 2, $subscriptions['totals']['packs'] );
        $this->assertSame( -25.0, $subscriptions['totals']['change'] );
        $this->assertSame( 'Gold', $subscriptions['breakdown'][0]['label'] );
        $this->assertSame( 2, $subscriptions['breakdown'][0]['value'] );
        $this->assertIsInt( $subscriptions['totals']['pending'] );

        $transactions = $this->data->transactions( $range );

        $this->assertSame( [ 30.0, 0.0, 5.0 ], $transactions['series']['current'], 'every completed payment, pack or post' );
        $this->assertSame( [ 3.0, 0.0, 0.0 ], $transactions['series']['tax'] );
        $this->assertSame( 35.0, $transactions['totals']['sales'] );
        $this->assertSame( 3.0, $transactions['totals']['tax'] );
        $this->assertSame( 32.0, $transactions['totals']['net'] );
        $this->assertSame( 3, $transactions['totals']['count'] );
        $this->assertSame( [], $transactions['breakdown'] );
        $this->assertIsString( $transactions['totals']['sales_text'] );
    }

    public function test_rest_route_needs_manage_options_and_answers_the_report_shape() {
        global $wp_rest_server;

        $wp_rest_server = new WP_REST_Server(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $controller     = new Report_Controller( $this->data );

        add_action( 'rest_api_init', [ $controller, 'register_routes' ] );
        do_action( 'rest_api_init' );

        $this->assertArrayHasKey( '/wpuf/v1/reports/(?P<tab>registrations|posts|subscriptions|transactions)', $wp_rest_server->get_routes() );

        $request = new WP_REST_Request( 'GET', '/wpuf/v1/reports/registrations' );
        $this->assertSame( 401, $wp_rest_server->dispatch( $request )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 403, $wp_rest_server->dispatch( $request )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $response = $wp_rest_server->dispatch( $request );
        $this->assertSame( 200, $response->get_status() );

        $body = $response->get_data();
        $this->assertSame( 'this_month', $body['range']['preset'] );
        foreach ( [ 'labels', 'keys', 'series', 'breakdown', 'totals' ] as $key ) {
            $this->assertArrayHasKey( $key, $body );
        }

        $custom = new WP_REST_Request( 'GET', '/wpuf/v1/reports/posts' );
        $custom->set_query_params( [ 'range' => 'custom', 'from' => '2024-02-10', 'to' => '2024-02-12' ] );
        $body = $wp_rest_server->dispatch( $custom )->get_data();
        $this->assertSame( [ '2024-02-10', '2024-02-12' ], [ $body['range']['from'], $body['range']['to'] ] );
        $this->assertArrayHasKey( 'authors', $body );

        $missing = new WP_REST_Request( 'GET', '/wpuf/v1/reports/posts' );
        $missing->set_query_params( [ 'range' => 'custom' ] );
        $this->assertSame( 400, $wp_rest_server->dispatch( $missing )->get_status() );

        $bad_day = new WP_REST_Request( 'GET', '/wpuf/v1/reports/posts' );
        $bad_day->set_query_params( [ 'range' => 'custom', 'from' => '2024-02-30', 'to' => '2024-02-12' ] );
        $this->assertSame( 400, $wp_rest_server->dispatch( $bad_day )->get_status() );

        $bad_range = new WP_REST_Request( 'GET', '/wpuf/v1/reports/posts' );
        $bad_range->set_query_params( [ 'range' => 'forever' ] );
        $this->assertSame( 400, $wp_rest_server->dispatch( $bad_range )->get_status() );

        $this->assertSame( 404, $wp_rest_server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/reports/nope' ) )->get_status() );
    }

    /**
     * A user registered at a given time.
     *
     * @param string $registered Y-m-d H:i:s
     * @param string $role       Role
     *
     * @return int
     */
    private function user_on( $registered, $role = 'subscriber' ) {
        return self::factory()->user->create( [ 'role' => $role, 'user_registered' => $registered ] );
    }

    /**
     * A row of wpuf_transaction.
     *
     * @param string $created Y-m-d H:i:s
     * @param string $status  Status
     * @param float  $cost    Cost
     * @param float  $tax     Tax
     * @param int    $pack_id Pack
     * @param int    $post_id Post
     *
     * @return void
     */
    private function transaction( $created, $status, $cost, $tax, $pack_id, $post_id = 0 ) {
        global $wpdb;

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prefix . 'wpuf_transaction',
            [
                'user_id'        => 1,
                'status'         => $status,
                'cost'           => (string) $cost,
                'tax'            => (string) $tax,
                'post_id'        => $post_id,
                'pack_id'        => $pack_id,
                'payer_email'    => 'payer@example.com',
                'payment_type'   => 'Paypal',
                'transaction_id' => wp_generate_password( 8, false ),
                'created'        => $created,
            ]
        );
    }
}
