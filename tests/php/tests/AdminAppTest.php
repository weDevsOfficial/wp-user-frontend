<?php
/**
 * Single React admin app (task 5d): app page, routes, redirects, menu links,
 * notice isolation.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\App\AppPage;
use WeDevs\Wpuf\Admin\Screens\Registry;
use WeDevs\Wpuf\Admin\Screens\Screen;

/**
 * Admin app tests.
 */
class AdminAppTest extends WP_UnitTestCase {

    /**
     * Script and style queues before the test (the app page load enqueues the
     * screens' bundles).
     *
     * @var array
     */
    private $queues = [];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $this->queues = [ wp_scripts()->queue, wp_styles()->queue ];
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_admin_app_enabled' );
        remove_all_filters( 'wpuf_admin_app_routes' );
        wp_scripts()->queue = $this->queues[0];
        wp_styles()->queue  = $this->queues[1];
        parent::tear_down();
    }

    private function app() {
        return wpuf()->platform()->get( AppPage::class );
    }

    public function test_app_is_always_on() {
        $this->assertTrue( wpuf_admin_app_enabled() );

        // The classic per-page screens are gone: the old filter cannot bring them back.
        add_filter( 'wpuf_admin_app_enabled', '__return_false' );
        $this->assertTrue( wpuf_admin_app_enabled() );
    }

    public function test_app_url_points_at_the_top_level_page_with_a_hash_route() {
        $this->assertSame( admin_url( 'admin.php?page=wp-user-frontend' ) . '#/post-forms/12/edit', wpuf_admin_app_url( 'post-forms/12/edit' ) );
        $this->assertSame( admin_url( 'admin.php?page=wp-user-frontend' ) . '#/settings?tab=general', wpuf_admin_app_url( '/settings', [ 'tab' => 'general' ] ) );
    }

    public function test_redirect_url_carries_the_route_in_the_query() {
        $url = Registry::app_redirect_url( '/settings?tab=payments' );

        $this->assertStringStartsWith( admin_url( 'admin.php?page=wp-user-frontend' ), $url );
        $this->assertStringContainsString( 'wpuf_route=' . rawurlencode( '/settings?tab=payments' ), $url );
        $this->assertStringNotContainsString( '#', $url );
    }

    public function test_routes_list_every_react_screen_with_its_mode() {
        $routes = wp_list_pluck( $this->app()->routes(), 'mode', 'id' );

        foreach ( [ 'post-forms', 'post-form-new', 'post-form-edit', 'post-forms-ai', 'subscriptions', 'settings' ] as $id ) {
            $this->assertArrayHasKey( $id, $routes, $id );
        }

        foreach ( $this->app()->routes() as $route ) {
            $this->assertSame( ! empty( $route['in_app'] ) ? 'app' : 'page', $route['mode'] );
            $this->assertStringStartsWith( admin_url(), $route['page'] );
            $this->assertNotEmpty( $route['app'] );
        }
    }

    public function test_old_builder_requests_map_to_builder_routes() {
        $forms = wpuf()->platform()->get( Registry::class )->get( 'wpuf-post-forms' );

        $this->assertInstanceOf( Screen::class, $forms );
        $this->assertSame( '/post-forms', $forms->app_route_for_request() );

        $_GET['action'] = 'edit';
        $_GET['id']     = '12';
        $this->assertSame( '/post-forms/12/edit', $forms->app_route_for_request() );

        // add-new: the load step creates the form and redirects to its builder.
        $_GET['action'] = 'add-new';
        unset( $_GET['id'] );
        $this->assertSame( '', $forms->app_route_for_request() );
        unset( $_GET['action'] );
    }

    public function test_ai_route_loads_the_ai_form_builder_for_its_form_type() {
        $fired   = did_action( 'wpuf_load_ai_form_builder_page' );
        $globals = wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Screens\AiFormBuilder::class )->load_in_app_for( 'post' );

        $this->assertSame( $fired + 1, did_action( 'wpuf_load_ai_form_builder_page' ), 'the AI page load hook fires' );
        $this->assertSame( 'post', $globals['wpufAIFormBuilder']['formType'] );
        $this->assertSame( 'input', $globals['wpufAIFormBuilder']['stage'] );
        $this->assertSame( 'wpuf-post-forms-ai', wp_list_pluck( $this->app()->routes(), 'group', 'id' )['post-forms-ai'] );
    }

    public function test_builder_routes_have_their_own_load_group() {
        $groups = wp_list_pluck( $this->app()->routes(), 'group', 'id' );

        $this->assertSame( 'wpuf-post-forms', $groups['post-forms'] );
        $this->assertSame( 'wpuf-post-forms-builder', $groups['post-form-edit'] );
        $this->assertSame( 'wpuf-post-forms-builder', $groups['post-form-new'] );
        $this->assertArrayHasKey( 'wpuf-post-forms-builder', wpuf()->platform()->get( Registry::class )->get( 'wpuf-post-forms' )->app_groups() );
    }

    public function test_menu_rows_point_at_app_routes_only_when_in_app() {
        global $submenu;

        $submenu['wp-user-frontend'] = [ [ 'Post Forms', 'manage_options', 'wpuf-post-forms' ], [ 'Other', 'manage_options', 'wpuf-no-route' ] ]; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        $this->app()->point_menu_rows_at_app();
        $this->assertSame( 'admin.php?page=wp-user-frontend#/post-forms', $submenu['wp-user-frontend'][0][2] );
        // A page no screen routes (Modules has a route with Pro, so not that one).
        $this->assertSame( 'wpuf-no-route', $submenu['wp-user-frontend'][1][2], 'rows without a route stay' );

        $submenu['wp-user-frontend'][0][2] = 'wpuf-post-forms'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        add_filter(
            'wpuf_admin_app_routes',
            function ( $routes ) {
                foreach ( $routes as $i => $route ) {
                    if ( 'post-forms' === $route['id'] ) {
                        $routes[ $i ]['mode'] = 'page';
                    }
                }

                return $routes;
            }
        );

        $this->app()->point_menu_rows_at_app();
        $this->assertSame( 'wpuf-post-forms', $submenu['wp-user-frontend'][0][2], 'page mode keeps the row' );
    }

    public function test_a_screen_load_cannot_remove_notices_for_the_other_routes() {
        $notice = function () {};
        add_action( 'admin_notices', $notice );

        $screen = new class() extends Screen {
            public function slug() {
                return 'test-app-screen';
            }

            public function render() {}

            public function load() {
                wpuf_remove_admin_notices();
                add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
            }

            public function app_routes() {
                return [ [ 'id' => 'test', 'path' => '/test', 'app' => 'test', 'in_app' => true ] ];
            }
        };
        wpuf()->platform()->get( Registry::class )->add( $screen );

        $this->app()->load();

        $this->assertNotFalse( has_action( 'admin_notices', $notice ), 'removed notice callback is back' );
        $this->assertFalse( has_action( 'in_admin_header', 'wpuf_remove_admin_notices' ), 'page-only header callback dropped' );
        $this->assertTrue( wp_script_is( AppPage::HANDLE, 'enqueued' ) );
        $this->assertStringContainsString( 'wpuf-admin-app', $this->app()->body_class( '' ) );
    }

    public function test_settings_subscriptions_and_lists_run_in_the_app() {
        $modes = wp_list_pluck( $this->app()->routes(), 'mode', 'id' );

        $this->assertSame( 'app', $modes['settings'] );
        $this->assertSame( 'app', $modes['subscriptions'] );
        $this->assertSame( 'app', $modes['post-forms'] );
        $this->assertSame( 'app', $modes['post-form-edit'] );
        $this->assertSame( 'app', $modes['post-forms-ai'] );
    }

    public function test_old_settings_and_subscriptions_requests_map_to_their_routes() {
        $registry = wpuf()->platform()->get( Registry::class );

        $_GET['tab'] = 'payments';
        $_GET['sub'] = 'wpuf_payment';
        $this->assertSame( '/settings?tab=payments&sub=wpuf_payment', $registry->get( 'wpuf-settings' )->app_route_for_request() );
        unset( $_GET['tab'], $_GET['sub'] );

        $_GET['action'] = 'edit';
        $_GET['id']     = '42';
        $this->assertSame( '/subscriptions?action=edit&id=42', $registry->get( 'wpuf_subscription' )->app_route_for_request() );
        unset( $_GET['action'], $_GET['id'] );
    }

    public function test_settings_has_no_classic_screen_any_more() {
        $settings = wpuf()->platform()->get( Registry::class )->get( 'wpuf-settings' );

        // The old per-request switch is ignored: the settings page is the React app.
        $_GET['wpuf_settings_ui'] = 'legacy';
        $this->assertSame( '/settings', $settings->app_route_for_request() );
        $this->assertSame( [], $settings->app_redirect_args() );
        $this->assertSame( 'wpuf-admin-react', $settings->body_class() );
        unset( $_GET['wpuf_settings_ui'] );

        $this->assertFalse( function_exists( 'wpuf_settings_use_legacy' ) );
    }
}
