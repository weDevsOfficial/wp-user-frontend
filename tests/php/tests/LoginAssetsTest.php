<?php
/**
 * What a [wpuf-login] page loads: the login app only when the screen renders
 * as an app (no classic form bundle, no Pro jQuery layout script), the classic
 * bundle and Pro's script when the screen renders from its template; the
 * screen routing the asset code shares with login_form().
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Free\Simple_Login;
use WeDevs\Wpuf\Frontend\React_Renderer;

class LoginAssetsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        $page = self::factory()->post->create( [ 'post_type' => 'page', 'post_content' => '[wpuf-login]' ] );
        $opts = (array) get_option( 'wpuf_profile', [] );

        $opts['login_page'] = $page;
        update_option( 'wpuf_profile', $opts );
        wp_set_current_user( 0 );

        // Fresh queues: they persist across tests otherwise.
        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();
        do_action( 'wp_enqueue_scripts' );
        $GLOBALS['wp_scripts']->queue = [];
        $GLOBALS['wp_styles']->queue  = [];
    }

    public function tear_down() {
        unset( $_GET['action'], $_GET['reset'] );
        parent::tear_down();
    }

    public function test_screen_part_follows_the_login_form_routing() {
        $this->assertSame( 'login', Simple_Login::screen_part() );

        $_GET['action'] = 'lostpassword';
        $this->assertSame( 'lost_password', Simple_Login::screen_part() );

        $_GET['action'] = 'rp';
        $this->assertSame( 'reset_password', Simple_Login::screen_part() );

        $_GET['action'] = 'resetpass';
        $this->assertSame( 'reset_password', Simple_Login::screen_part() );

        $_GET['reset'] = 'true';
        $this->assertSame( 'login', Simple_Login::screen_part(), 'after a reset the login form shows again' );

        wp_set_current_user( self::factory()->user->create() );
        $this->assertSame( '', Simple_Login::screen_part(), 'logged in: the logged-in view' );
    }

    public function test_app_screen_loads_the_login_app_without_the_classic_bundle() {
        $html = do_shortcode( '[wpuf-login]' );

        $this->assertStringContainsString( 'data-wpuf-react="login"', $html );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-login', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'wpuf-frontend-form', 'enqueued' ), 'no classic form bundle' );
        $this->assertFalse( wp_script_is( 'wpuf-login-form', 'enqueued' ), 'no Pro jQuery layout script' );
    }

    public function test_lost_password_app_screen_skips_the_classic_bundle_too() {
        $_GET['action'] = 'lostpassword';

        do_shortcode( '[wpuf-login]' );

        $this->assertTrue( wp_script_is( 'wpuf-frontend-login', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'wpuf-frontend-form', 'enqueued' ) );
    }

    public function test_template_screen_keeps_the_classic_bundle_and_pro_script() {
        $html = React_Renderer::with_source(
            'block',
            function () {
                return do_shortcode( '[wpuf-login]' );
            }
        );

        $this->assertStringContainsString( 'name="loginform"', $html );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-form', 'enqueued' ), 'classic screen: classic bundle' );

        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->assertTrue( wp_script_is( 'wpuf-login-form', 'enqueued' ), 'classic screen: Pro layout script' );
            $this->assertStringContainsString( 'wpufLoginForm', (string) wp_scripts()->get_data( 'wpuf-login-form', 'data' ) );
        }
    }

    public function test_pro_layout_reaches_the_app_in_the_boot_data() {
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $html = do_shortcode( '[wpuf-login]' );

        $this->assertSame( 1, preg_match( '#<script type="application/json" class="wpuf-boot">(.*?)</script>#s', $html, $m ) );

        $data = json_decode( $m[1], true );

        $this->assertArrayHasKey( 'layout', $data['pro'] );
        $this->assertArrayHasKey( 'buttonText', $data['pro']['texts'] );
    }
}
