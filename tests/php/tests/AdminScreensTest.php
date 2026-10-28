<?php
/**
 * Admin screens on their menu slugs (task 2.5a).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Screens\PostFormsList;
use WeDevs\Wpuf\Admin\Screens\Registry;
use WeDevs\Wpuf\Admin\Screens\Settings;
use WeDevs\Wpuf\Admin\Screens\Subscriptions;

/**
 * Admin screen tests.
 */
class AdminScreensTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        // The old page URLs redirect to the app route: block the redirect the
        // WordPress way (Registry exits only when the header went out).
        add_filter( 'wp_redirect', '__return_false' );
    }

    public function tear_down() {
        remove_filter( 'wp_redirect', '__return_false' );
        global $plugin_page;

        $plugin_page = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        unset( $_GET['action'] );
        parent::tear_down();
    }

    public function test_registry_is_shared_and_knows_the_free_screens() {
        $registry = wpuf()->platform()->get( Registry::class );

        $this->assertSame( $registry, wpuf()->platform()->get( Registry::class ) );
        $this->assertInstanceOf( PostFormsList::class, $registry->get( 'wpuf-post-forms' ) );
        $this->assertInstanceOf( Subscriptions::class, $registry->get( 'wpuf_subscription' ) );
        $this->assertInstanceOf( Settings::class, $registry->get( 'wpuf-settings' ) );
        $this->assertNull( $registry->get( 'nope' ) );
    }

    public function test_current_screen_comes_from_plugin_page() {
        global $plugin_page;

        $registry = new Registry();
        $this->assertNull( $registry->current() );

        $plugin_page = 'wpuf_subscription'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $this->assertInstanceOf( Subscriptions::class, $registry->current() );
    }

    public function test_load_fires_the_existing_load_hooks_through_the_menu_shims() {
        $fired = [];
        foreach ( [ 'wpuf_load_post_forms', 'wpuf_load_subscription_page' ] as $hook ) {
            add_action(
                $hook,
                function () use ( &$fired, $hook ) {
                    $fired[] = $hook;
                }
            );
        }

        $menu = ( new ReflectionClass( WeDevs\Wpuf\Admin\Menu::class ) )->newInstanceWithoutConstructor();
        $menu->post_form_menu_action();
        $menu->subscription_menu_action();

        $this->assertSame( [ 'wpuf_load_post_forms', 'wpuf_load_subscription_page' ], $fired );
    }

    public function test_notices_are_captured_and_printed_once_in_the_wrapper() {
        $registry = new Registry();
        $registry->add( new class() extends WeDevs\Wpuf\Admin\Screens\Screen {
            public function slug() {
                return 'wpuf-test-notices';
            }

            public function render() {}

            public function captures_notices() {
                return true;
            }
        } );
        $registry->load( 'wpuf-test-notices' );

        $this->assertSame( PHP_INT_MIN, has_action( 'admin_notices', [ $registry, 'start_capture' ] ) );
        $this->assertSame( PHP_INT_MAX, has_action( 'all_admin_notices', [ $registry, 'end_capture' ] ) );

        add_action(
            'admin_notices',
            function () {
                echo '<div class="notice notice-info"><p>Hello</p></div>';
            }
        );

        ob_start();
        do_action( 'admin_notices' );
        do_action( 'all_admin_notices' );
        $at_top = ob_get_clean();

        $this->assertSame( '', $at_top, 'nothing printed at the top' );

        ob_start();
        $registry->print_notices();
        $registry->print_notices();
        $printed = ob_get_clean();

        $this->assertSame( '<div class="wpuf-admin-notices"><div class="notice notice-info"><p>Hello</p></div></div>', $printed );
    }

    public function test_builder_and_subscriptions_do_not_capture() {
        $_GET['action'] = 'edit';
        $this->assertFalse( ( new PostFormsList() )->captures_notices() );
        $this->assertFalse( ( new Subscriptions() )->captures_notices() );

        unset( $_GET['action'] );
        $this->assertTrue( ( new PostFormsList() )->captures_notices() );
    }

    public function test_screens_add_the_scope_body_class() {
        $settings = new WeDevs\Wpuf\Admin\Screens\Settings();
        $this->assertSame( 'wpuf-admin-screen', $settings->body_class() );

        $this->assertSame( 'wpuf-admin-screen', ( new Subscriptions() )->body_class() );
        $this->assertSame( '', ( new PostFormsList() )->body_class(), 'screens without the Tailwind 4 sheet add nothing' );
    }

    public function test_registry_load_adds_the_body_class_filter() {
        $registry = new Registry();
        $registry->add( new class() extends WeDevs\Wpuf\Admin\Screens\Screen {
            public function slug() {
                return 'wpuf-test-screen';
            }

            public function render() {}

            public function body_class() {
                return 'wpuf-admin-screen';
            }
        } );

        remove_all_filters( 'admin_body_class' ); // core callbacks need a current screen; restored after the test.
        $registry->load( 'wpuf-test-screen' );

        $this->assertSame( 'folded wpuf-admin-screen', apply_filters( 'admin_body_class', 'folded' ) );
    }
}
