<?php
/**
 * Platform container, providers and bootstrap (task 2.1)
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Builder\HookBridge;
use WeDevs\Wpuf\Platform\Bootstrap;
use WeDevs\Wpuf\Platform\Container;
use WeDevs\Wpuf\Platform\Contracts\Hookable;
use WeDevs\Wpuf\Platform\NotFoundException;
use WeDevs\Wpuf\Platform\ServiceProvider;

/**
 * Hookable test double: counts register_hooks() calls.
 */
class WPUF_Test_Hookable_Service implements Hookable {

    /**
     * Calls of register_hooks().
     *
     * @var int
     */
    public $hooked = 0;

    /**
     * Count the call.
     *
     * @return void
     */
    public function register_hooks() {
        $this->hooked++;
    }
}

/**
 * Provider test double counting its registrations.
 */
class WPUF_Test_Counting_Provider extends ServiceProvider {

    /**
     * Registrations so far.
     *
     * @var int
     */
    public static $registered = 0;

    /**
     * Register the hookable double, count the call.
     *
     * @return void
     */
    public function register() {
        self::$registered++;
        $this->share_tagged(
            WPUF_Test_Hookable_Service::class,
            function () {
                return new WPUF_Test_Hookable_Service();
            }
        );
    }
}

/**
 * Provider test double registering the hookable double.
 */
class WPUF_Test_Provider extends ServiceProvider {

    /**
     * Register the double as a shared, tagged service.
     *
     * @return void
     */
    public function register() {
        $this->share_tagged(
            WPUF_Test_Hookable_Service::class,
            function () {
                return new WPUF_Test_Hookable_Service();
            }
        );
    }
}

/**
 * Provider test double registering nothing.
 */
class WPUF_Test_Empty_Provider extends ServiceProvider {

    /**
     * Register nothing.
     *
     * @return void
     */
    public function register() {}
}

/**
 * @covers \WeDevs\Wpuf\Platform\Container
 * @covers \WeDevs\Wpuf\Platform\ServiceProvider
 * @covers \WeDevs\Wpuf\Platform\Bootstrap
 */
class PlatformContainerTest extends WP_UnitTestCase {

    public function test_shared_service_resolves_once_and_bind_resolves_each_time() {
        $container = new Container();
        $container->share( 'shared', function () {
            return new stdClass();
        } );
        $container->bind( 'factory', function ( $c, $label = 'none' ) {
            $object        = new stdClass();
            $object->label = $label;

            return $object;
        } );

        $this->assertSame( $container->get( 'shared' ), $container->get( 'shared' ) );
        $this->assertNotSame( $container->get( 'factory' ), $container->get( 'factory' ) );
        $this->assertSame( 'x', $container->get( 'factory', 'x' )->label, 'bind passes get() arguments to the factory' );
        $this->assertTrue( $container->is_shared( 'shared' ) );
        $this->assertFalse( $container->is_shared( 'factory' ) );
    }

    public function test_unknown_service_throws() {
        $this->expectException( NotFoundException::class );
        ( new Container() )->get( 'missing' );
    }

    public function test_tags_keep_order_and_have_no_duplicates() {
        $container = new Container();
        $container->share( 'a', '__return_true' );
        $container->share( 'b', '__return_false' );
        $container->add_tag( 'b', 'role' );
        $container->add_tag( 'a', 'role' );
        $container->add_tag( 'b', 'role' );

        $this->assertSame( [ 'b', 'a' ], $container->tagged_ids( 'role' ) );
        $this->assertSame( [ false, true ], $container->tagged( 'role' ) );
        $this->assertSame( [], $container->tagged_ids( 'none' ) );
    }

    public function test_provider_tags_services_by_interface() {
        $container = new Container();
        ( new WPUF_Test_Provider( $container ) )->register();

        $this->assertSame( [ WPUF_Test_Hookable_Service::class ], $container->tagged_ids( Hookable::class ) );
    }

    public function test_bootstrap_hooks_each_service_once_and_fires_loaded_once() {
        $container = new Container();
        $bootstrap = new Bootstrap( $container );
        $fired     = did_action( 'wpuf_platform_loaded' );

        $bootstrap->boot();
        $bootstrap->boot();

        $this->assertTrue( $bootstrap->is_booted() );
        $this->assertSame( $fired + 1, did_action( 'wpuf_platform_loaded' ), 'wpuf_platform_loaded fires once' );

        $bootstrap->register_provider( new WPUF_Test_Provider( $container ) );
        // A later provider (e.g. Pro on wpuf_platform_loaded) must not hook earlier services again.
        $bootstrap->register_provider( new WPUF_Test_Empty_Provider( $container ) );

        $this->assertSame( 1, $container->get( WPUF_Test_Hookable_Service::class )->hooked, 'register_hooks() runs once per service' );
    }

    public function test_plugin_boots_the_platform_with_the_core_services() {
        $this->assertTrue( wpuf()->platform_bootstrap()->is_booted() );
        $this->assertInstanceOf( Container::class, wpuf()->platform() );
        $this->assertInstanceOf( HookBridge::class, wpuf()->platform()->get( HookBridge::class, 'post', [] ) );
    }

    public function test_tools_and_transactions_services_come_from_the_container() {
        $tools = wpuf()->platform()->get( \WeDevs\Wpuf\Platform\Tools\ToolsService::class );
        $pay   = wpuf()->platform()->get( \WeDevs\Wpuf\Platform\Transactions\TransactionService::class );

        $this->assertSame( $tools, wpuf()->platform()->get( \WeDevs\Wpuf\Platform\Tools\ToolsService::class ), 'shared' );
        $this->assertSame( $pay, wpuf()->platform()->get( \WeDevs\Wpuf\Platform\Transactions\TransactionService::class ), 'shared' );
    }

    public function test_legacy_accessor_keeps_its_keys_and_returns_null_for_unknown() {
        $this->assertInstanceOf( \WeDevs\Wpuf\Admin\Forms\Field_Manager::class, wpuf()->fields );
        $this->assertInstanceOf( \WeDevs\Wpuf\Admin\Subscription::class, wpuf()->subscription );
        $this->assertInstanceOf( \WeDevs\Wpuf\Integrations::class, wpuf()->integrations );
        $this->assertNull( wpuf()->no_such_service );
        $this->assertNull( wpuf()->{HookBridge::class}, 'factory-only platform services are not exposed as properties' );
    }

    /**
     * The legacy services are the platform's: one instance for wpuf()->key and the class id, built in the plugin's order.
     */
    public function test_legacy_services_are_the_platform_services() {
        $platform = wpuf()->platform();

        foreach ( \WeDevs\Wpuf\Platform\Providers\PluginServiceProvider::SERVICES as $key => $class ) {
            $this->assertTrue( $platform->has( $class ), $class . ' registered' );
            $this->assertSame( $class, \WeDevs\Wpuf\Platform\Providers\PluginServiceProvider::class_of( $key ) );
        }

        // The keys the plugin builds in the test context (no wp-admin, no AJAX;
        // other tests may put their own Admin into the legacy array).
        foreach ( [ 'tracker', 'assets', 'subscription', 'fields', 'customize', 'bank', 'paypal', 'api', 'integrations', 'ai_manager', 'post_form_block', 'frontend', 'gateway_manager', 'widgets' ] as $key ) {
            $this->assertNotNull( wpuf()->$key, $key );
            $this->assertSame( wpuf()->$key, $platform->get( \WeDevs\Wpuf\Platform\Providers\PluginServiceProvider::class_of( $key ) ), $key );
        }

        $this->assertNull( \WeDevs\Wpuf\Platform\Providers\PluginServiceProvider::class_of( 'nope' ) );
        $this->assertSame( wpuf()->assets, $platform->get( \WeDevs\Wpuf\Assets::class ) );
        $this->assertSame( wpuf()->subscription, $platform->get( \WeDevs\Wpuf\Admin\Subscription::class ) );
        $this->assertSame( wpuf()->api, $platform->get( \WeDevs\Wpuf\API::class ) );
    }

    /**
     * Bootstrap::register() registers the providers once and boot() still hooks and fires once.
     */
    public function test_bootstrap_register_is_idempotent_and_boot_hooks_once() {
        $container = new Container();
        $bootstrap = new class( $container ) extends Bootstrap {
            public $registrations = 0;

            public function providers() {
                return [ WPUF_Test_Counting_Provider::class ];
            }
        };
        WPUF_Test_Counting_Provider::$registered = 0;

        $bootstrap->register();
        $bootstrap->register();
        $this->assertSame( 1, WPUF_Test_Counting_Provider::$registered, 'providers register once' );
        $this->assertFalse( $bootstrap->is_booted() );
        $this->assertTrue( $container->has( WPUF_Test_Hookable_Service::class ) );
        $this->assertSame( 0, $container->get( WPUF_Test_Hookable_Service::class )->hooked, 'no hooks before boot' );

        $fired = 0;
        $count = function () use ( &$fired ) {
            $fired++;
        };
        add_action( 'wpuf_platform_loaded', $count );
        $bootstrap->boot();
        $bootstrap->boot();
        remove_action( 'wpuf_platform_loaded', $count );

        $this->assertSame( 1, WPUF_Test_Counting_Provider::$registered, 'boot does not register again' );
        $this->assertSame( 1, $container->get( WPUF_Test_Hookable_Service::class )->hooked );
        $this->assertSame( 1, $fired );
    }

    /**
     * forget() drops a shared instance; the next get() builds a new one.
     */
    public function test_forget_rebuilds_a_shared_service() {
        $container = new \WeDevs\Wpuf\Platform\Container();
        $container->share( 'thing', function () { return new \stdClass(); } );

        $first = $container->get( 'thing' );
        $this->assertSame( $first, $container->get( 'thing' ) );

        $container->forget( 'thing' );
        $this->assertNotSame( $first, $container->get( 'thing' ) );
        $this->assertTrue( $container->has( 'thing' ), 'the factory stays registered' );
    }
}
