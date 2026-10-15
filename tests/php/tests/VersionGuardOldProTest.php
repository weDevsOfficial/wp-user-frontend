<?php
/**
 * An older Pro (before 4.3.0: main class loaded, no WPUF_PRO_PLATFORM_VERSION)
 * is not started: its plugins_loaded boot is removed and free runs as the
 * free plugin. Runs in its own process: it declares a stand-in Pro class.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\VersionGuard;

class VersionGuardOldProTest extends WP_UnitTestCase {

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_older_pro_is_stopped_and_free_runs_alone() {
        if ( class_exists( 'WP_User_Frontend_Pro', false ) ) {
            $this->markTestSkipped( 'The real Pro is loaded.' );
        }

        // What an older Pro's main file does when it loads: the singleton hooks its boot.
        eval( // phpcs:ignore Squiz.PHP.Eval.Discouraged -- a stand-in for the older Pro's main file.
            'class WP_User_Frontend_Pro {
                public static $instance;
                public static function instance() { return self::$instance ?: ( self::$instance = new self() ); }
                public function __construct() { add_action( "plugins_loaded", [ $this, "init_plugin" ] ); }
                public function init_plugin() {}
            }
            function wpuf_pro() { return WP_User_Frontend_Pro::instance(); }'
        );
        wpuf_pro();

        $this->assertTrue( VersionGuard::pro_needs_update() );
        $this->assertFalse( wpuf_is_pro_active(), 'free runs as the free plugin' );
        $this->assertSame( 10, has_action( 'plugins_loaded', [ wpuf_pro(), 'init_plugin' ] ) );

        $this->assertTrue( VersionGuard::stop_outdated_pro() );
        $this->assertFalse( has_action( 'plugins_loaded', [ wpuf_pro(), 'init_plugin' ] ), 'the older Pro does not boot' );

        // The notice on WPUF screens.
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $_GET['page'] = 'wpuf-post-forms';

        ob_start();
        ( new VersionGuard() )->pro_update_notice();
        $notice = ob_get_clean();

        $this->assertStringContainsString( 'needs WP User Frontend Pro ' . VersionGuard::REQUIRED_PRO_VERSION . ' or newer', $notice );

        unset( $_GET['page'] );
    }
}
