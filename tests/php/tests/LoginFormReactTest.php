<?php
/**
 * The [wpuf-login] form on the React frontend: the mount with its boot data
 * when the switch is on, the classic template otherwise; same post fields.
 *
 * @package WP_User_Frontend
 */
class LoginFormReactTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        $page = self::factory()->post->create( [ 'post_type' => 'page', 'post_content' => '[wpuf-login]' ] );
        $opts = (array) get_option( 'wpuf_profile', [] );

        $opts['login_page'] = $page;
        update_option( 'wpuf_profile', $opts );
        wp_set_current_user( 0 );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_frontend_react_enabled' );
        remove_all_filters( 'login_message' );
        remove_all_actions( 'wpuf_login_form_bottom' );
        parent::tear_down();
    }

    private function boot( $html ) {
        $this->assertSame( 1, preg_match( '#<script type="application/json" class="wpuf-boot">(.*?)</script>#s', $html, $m ), 'a boot script' );

        return json_decode( $m[1], true );
    }

    public function test_switch_on_renders_the_react_mount_with_the_template_data() {
        add_filter( 'wpuf_frontend_react_enabled', '__return_true' );
        add_filter( 'login_message', function ( $message ) { return $message . '<p class="qa-msg">Hi</p>'; } );
        add_action( 'wpuf_login_form_bottom', function () { echo '<span class="qa-bottom">bottom</span>'; } );

        $html = wpuf()->frontend->simple_login->login_form();

        $this->assertStringContainsString( 'data-wpuf-react="login"', $html );
        $this->assertStringNotContainsString( 'name="loginform"', $html, 'no classic form' );

        $data = $this->boot( $html );

        $this->assertNotFalse( wp_verify_nonce( $data['nonce'], 'wpuf_login_action' ), 'the nonce process_login() checks' );
        $this->assertSame( wp_lostpassword_url(), $data['lost_password'] );
        $this->assertStringContainsString( 'qa-msg', $data['message'], 'login_message output' );
        $this->assertStringContainsString( 'qa-bottom', $data['bottom'], 'wpuf_login_form_bottom output' );
        $this->assertSame( 'Log In', $data['texts']['buttonText'] );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-login', 'enqueued' ) );
    }

    public function test_switch_off_keeps_the_classic_template() {
        add_filter( 'wpuf_frontend_react_enabled', '__return_false' );

        $html = wpuf()->frontend->simple_login->login_form();

        $this->assertStringContainsString( 'name="loginform"', $html );
        $this->assertStringNotContainsString( 'data-wpuf-react="login"', $html );

        foreach ( [ 'name="log"', 'name="pwd"', 'name="rememberme"', 'name="wpuf_login"', 'name="wpuf-login-nonce"', 'name="redirect_to"' ] as $field ) {
            $this->assertStringContainsString( $field, $html );
        }
    }
}
