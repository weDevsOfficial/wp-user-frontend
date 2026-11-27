<?php
/**
 * Platform REST base, manager and capabilities (task 2.2)
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Caps;
use WeDevs\Wpuf\Platform\REST\Manager;
use WeDevs\Wpuf\Platform\REST\RestController;

/**
 * Controller test double: one paginated admin route plus cast helpers exposed.
 */
class WPUF_Test_Rest_Controller extends RestController {

    /**
     * Resource name.
     *
     * @var string
     */
    protected $resource = 'thing';

    /**
     * Register a test route.
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/admin/test-things',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => function () {
                    return $this->paginate( [ 'a', 'b' ], 5, 2 );
                },
                'permission_callback' => $this->permission( Caps::MANAGE_FORMS ),
            ]
        );
    }

    /**
     * Call a protected helper.
     *
     * @param string $method Helper name
     * @param mixed  ...$args Arguments
     *
     * @return mixed
     */
    public function call( $method, ...$args ) {
        return $this->$method( ...$args );
    }
}

/**
 * @covers \WeDevs\Wpuf\Platform\REST\RestController
 * @covers \WeDevs\Wpuf\Platform\REST\Manager
 * @covers \WeDevs\Wpuf\Platform\Caps
 */
class PlatformRestTest extends WP_UnitTestCase {

    /**
     * REST server for the test.
     *
     * @var WP_REST_Server
     */
    private $server;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;

        add_action( 'rest_api_init', [ new WPUF_Test_Rest_Controller(), 'register_routes' ] );
        do_action( 'rest_api_init', $this->server );
    }

    public function tear_down() {
        global $wp_rest_server;
        $wp_rest_server = null;

        parent::tear_down();
    }

    private function dispatch_as( $role ) {
        wp_set_current_user( $role ? self::factory()->user->create( [ 'role' => $role ] ) : 0 );

        return $this->server->dispatch( new WP_REST_Request( 'GET', '/wpuf/v1/admin/test-things' ) );
    }

    public function test_logged_out_gets_401_and_subscriber_gets_403() {
        $this->assertSame( 401, $this->dispatch_as( null )->get_status() );
        $this->assertSame( 403, $this->dispatch_as( 'subscriber' )->get_status() );
    }

    public function test_admin_gets_items_with_pagination_headers() {
        $response = $this->dispatch_as( 'administrator' );
        $headers  = $response->get_headers();

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( [ 'a', 'b' ], $response->get_data() );
        $this->assertSame( '5', $headers['X-WP-Total'] );
        $this->assertSame( '3', $headers['X-WP-TotalPages'] );
    }

    public function test_capability_follows_the_admin_role_filter() {
        $editor = self::factory()->user->create( [ 'role' => 'editor' ] );
        wp_set_current_user( $editor );

        $this->assertFalse( Caps::can( Caps::MANAGE_FORMS ) );

        $role = function () {
            return 'edit_posts';
        };
        add_filter( 'wpuf_admin_role', $role );
        $this->assertTrue( Caps::can( Caps::MANAGE_FORMS ), 'wpuf_admin_role still decides' );
        remove_filter( 'wpuf_admin_role', $role );
    }

    public function test_site_capability_stays_manage_options_and_is_filterable() {
        $this->assertSame( 'manage_options', Caps::capability( Caps::MANAGE_SITE ) );

        // The WPUF admin role filter does not lower the site-wide actions.
        $role = function () {
            return 'edit_posts';
        };
        add_filter( 'wpuf_admin_role', $role );
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertTrue( Caps::can( Caps::MANAGE_FORMS ) );
        $this->assertFalse( Caps::can( Caps::MANAGE_SITE ) );
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/admin/transactions/delete' );
        $request->set_param( 'items', [ [ 'kind' => 'transaction', 'id' => 1 ] ] );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
        remove_filter( 'wpuf_admin_role', $role );

        $split = function ( $capability, $cap ) {
            return Caps::MANAGE_SITE === $cap ? 'edit_posts' : $capability;
        };
        add_filter( 'wpuf_capability', $split, 10, 2 );
        $this->assertTrue( Caps::can( Caps::MANAGE_SITE ), 'wpuf_capability can change it' );
        remove_filter( 'wpuf_capability', $split, 10 );
    }

    public function test_casts_and_error_codes() {
        $controller = new WPUF_Test_Rest_Controller();

        $this->assertSame( 12, $controller->call( 'cast_int', '12' ) );
        $this->assertSame( 0, $controller->call( 'cast_int', 'x' ) );
        $this->assertTrue( $controller->call( 'cast_bool', 'yes' ) );
        $this->assertFalse( $controller->call( 'cast_bool', 'no' ) );
        $this->assertSame( '', $controller->call( 'cast_string', [ 1 ] ) );
        $this->assertSame( [], $controller->call( 'cast_array', '' ) );
        $this->assertSame( [ 'a' ], $controller->call( 'cast_array', 'a' ) );

        $error = $controller->call( 'error', 'not_found', 'Missing', 404 );
        $this->assertSame( 'wpuf_thing_not_found', $error->get_error_code() );
        $this->assertSame( 404, $error->get_error_data()['status'] );
    }

    public function test_manager_registers_the_frozen_routes_with_the_api_objects() {
        $routes = $this->server->get_routes();

        foreach ( [ '/wpuf/v1/wpuf_form', '/wpuf/v1/wpuf_subscription', '/wpuf/v1/wpuf_subscription/(?P<subscription_id>\d+)', '/wpuf/v1/settings' ] as $route ) {
            $this->assertArrayHasKey( $route, $routes, $route );
        }

        $manager     = wpuf()->platform()->get( Manager::class );
        $controllers = $manager->controllers();

        $this->assertSame( wpuf()->api->subscription, $controllers[0], 'the API object itself, not a second instance' );
        $this->assertContains( wpuf()->api->form_list, $controllers );
        $this->assertContains( wpuf()->api->settings, $controllers );
    }
}
