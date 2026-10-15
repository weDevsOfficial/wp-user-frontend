<?php
/**
 * Free/Pro version guard (task 2.3)
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\VersionGuard;

/**
 * VersionGuard tests.
 */
class VersionGuardTest extends WP_UnitTestCase {

    /**
     * Free plugin declares its platform version.
     */
    public function test_free_declares_platform_version() {
        $this->assertTrue( defined( 'WPUF_PLATFORM_VERSION' ) );
        $this->assertSame( '1.0', WPUF_PLATFORM_VERSION );
    }

    /**
     * Pro version skew detection.
     */
    public function test_outdated_pro_detection() {
        $this->assertFalse( VersionGuard::is_outdated_pro( null, null ), 'no Pro' );
        $this->assertTrue( VersionGuard::is_outdated_pro( '4.2.19', null ), 'Pro without platform' );
        $this->assertTrue( VersionGuard::is_outdated_pro( '5.0.0', '0.9' ), 'Pro with older platform' );
        $this->assertFalse( VersionGuard::is_outdated_pro( '5.0.0', '1.0' ), 'Pro with current platform' );
        $this->assertFalse( VersionGuard::is_outdated_pro( '5.0.0', '1.1' ), 'Pro with newer platform' );
        $this->assertTrue( VersionGuard::is_outdated_pro( '', null ), 'Pro class loaded, version not defined yet, no platform' );
    }

    /**
     * Without Pro, or with a Pro that declares the platform, nothing is stopped
     * and wpuf_is_pro_active() follows the Pro class.
     */
    public function test_current_pro_is_not_stopped() {
        $this->assertFalse( VersionGuard::pro_needs_update() );
        $this->assertFalse( VersionGuard::stop_outdated_pro() );
        $this->assertSame( class_exists( 'WP_User_Frontend_Pro' ), wpuf_is_pro_active() );

        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->assertTrue( defined( 'WPUF_PRO_PLATFORM_VERSION' ), 'Pro declares its platform when its file loads' );
        }
    }

    /**
     * The guard is a booted, hooked platform service.
     */
    public function test_guard_is_hooked() {
        $guard = wpuf()->platform()->get( VersionGuard::class );

        $this->assertInstanceOf( VersionGuard::class, $guard );
        $this->assertSame( 999, has_action( 'admin_enqueue_scripts', [ $guard, 'skip_legacy_pro_builder' ] ) );
        $this->assertSame( 10, has_action( 'admin_notices', [ $guard, 'pro_update_notice' ] ) );
    }

    /**
     * Without Pro: no notice, nothing dequeued.
     */
    public function test_no_notice_or_dequeue_without_pro() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $_GET['page'] = 'wpuf-post-forms';

        wp_register_script( 'wpuf-vue', 'https://example.test/vue.js', [], '1', true );
        wp_enqueue_script( 'wpuf-vue' );

        $guard = new VersionGuard();

        ob_start();
        $guard->pro_update_notice();
        $this->assertSame( '', ob_get_clean() );

        $guard->skip_legacy_pro_builder();
        $this->assertTrue( wp_script_is( 'wpuf-vue', 'enqueued' ) );

        unset( $_GET['page'] );
    }
}
