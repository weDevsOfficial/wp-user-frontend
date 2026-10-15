<?php
/**
 * The [wpuf-login] screens (login, lost password, reset password) on the
 * React frontend: the mount with its boot data, the classic template where
 * the call renders classic (Renderer); same post fields.
 *
 * @package WP_User_Frontend
 */
class LoginScreensTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        $page = self::factory()->post->create( [ 'post_type' => 'page', 'post_content' => '[wpuf-login]' ] );
        $opts = (array) get_option( 'wpuf_profile', [] );

        $opts['login_page'] = $page;
        update_option( 'wpuf_profile', $opts );
        wp_set_current_user( 0 );
    }

    public function tear_down() {
        remove_all_filters( 'login_message' );
        remove_all_actions( 'wpuf_login_form_bottom' );
        remove_all_actions( 'lostpassword_form' );
        remove_all_actions( 'resetpassword_form' );
        unset( $_GET['action'], $_GET['key'], $_GET['login'], $_REQUEST['key'], $_REQUEST['login'] );
        parent::tear_down();
    }

    /**
     * Render the shortcode the classic way (a call Renderer keeps classic).
     */
    private function classic() {
        return \WeDevs\Wpuf\Frontend\Renderer::with_source(
            'block',
            function () {
                return wpuf()->frontend->simple_login->login_form();
            }
        );
    }

    private function boot( $html ) {
        $this->assertSame( 1, preg_match( '#<script type="application/json" class="wpuf-boot">(.*?)</script>#s', $html, $m ), 'a boot script' );

        return json_decode( $m[1], true );
    }

    public function test_renders_the_mount_with_the_template_data() {
        add_filter( 'login_message', function ( $message ) { return $message . '<p class="qa-msg">Hi</p>'; } );
        add_action( 'wpuf_login_form_bottom', function () { echo '<span class="qa-bottom">bottom</span>'; } );

        $html = wpuf()->frontend->simple_login->login_form();

        $this->assertStringContainsString( 'data-wpuf-mount="login"', $html );
        $this->assertStringNotContainsString( 'name="loginform"', $html, 'no classic form' );

        $data = $this->boot( $html );

        $this->assertNotFalse( wp_verify_nonce( $data['nonce'], 'wpuf_login_action' ), 'the nonce process_login() checks' );
        $this->assertSame( wp_lostpassword_url(), $data['lost_password'] );
        $this->assertStringContainsString( 'qa-msg', $data['message'], 'login_message output' );
        $this->assertStringContainsString( 'qa-bottom', $data['bottom'], 'wpuf_login_form_bottom output' );
        $this->assertSame( 'login', $data['view'] );
        $this->assertSame( 'Log In', $data['texts']['buttonText'] );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-login', 'enqueued' ) );
    }

    public function test_classic_render_keeps_the_classic_template() {
        $html = $this->classic();

        $this->assertStringContainsString( 'name="loginform"', $html );
        $this->assertStringNotContainsString( 'data-wpuf-mount="login"', $html );

        foreach ( [ 'name="log"', 'name="pwd"', 'name="rememberme"', 'name="wpuf_login"', 'name="wpuf-login-nonce"', 'name="redirect_to"' ] as $field ) {
            $this->assertStringContainsString( $field, $html );
        }
    }

    public function test_lost_password_screen_renders_the_mount_with_the_template_data() {
        add_action( 'lostpassword_form', function () { echo '<span class="qa-lost">extra</span>'; } );
        $_GET['action'] = 'lostpassword';

        $html = wpuf()->frontend->simple_login->login_form();

        $this->assertStringContainsString( 'data-wpuf-mount="login"', $html );
        $this->assertStringNotContainsString( 'name="lostpasswordform"', $html, 'no classic form' );

        $data = $this->boot( $html );

        $this->assertSame( 'lostpassword', $data['view'] );
        $this->assertNotFalse( wp_verify_nonce( $data['nonce'], 'wpuf_lost_pass' ), 'the nonce process_reset_password() checks' );
        $this->assertStringContainsString( 'qa-lost', $data['hook'], 'lostpassword_form output' );
        $this->assertStringContainsString( 'Please enter your username or email address', $data['notices'] );
        $this->assertStringNotContainsString( 'action=lostpassword', $data['links'], 'no link to this screen' );
        $this->assertSame( 'Get New Password', $data['texts']['buttonText'] );
    }

    public function test_reset_password_screen_renders_the_mount_with_the_key() {
        add_action( 'resetpassword_form', function () { echo '<span class="qa-reset">extra</span>'; } );
        $_GET['action']    = 'rp';
        $_REQUEST['key']   = 'abc123';
        $_REQUEST['login'] = 'someone';

        $data = $this->boot( wpuf()->frontend->simple_login->login_form() );

        $this->assertSame( 'resetpass', $data['view'] );
        $this->assertSame( 'abc123', $data['key'] );
        $this->assertSame( 'someone', $data['login'] );
        $this->assertNotFalse( wp_verify_nonce( $data['nonce'], 'wpuf_reset_pass' ) );
        $this->assertStringContainsString( 'qa-reset', $data['hook'], 'resetpassword_form output' );
        $this->assertStringContainsString( 'Enter your new password below.', $data['notices'] );
    }

    public function test_classic_render_keeps_the_classic_lost_and_reset_templates() {
        $_GET['action'] = 'lostpassword';
        $lost           = $this->classic();

        $this->assertStringContainsString( 'name="lostpasswordform"', $lost );
        $this->assertStringContainsString( 'name="user_login"', $lost );

        $_GET['action'] = 'rp';
        $reset          = $this->classic();

        $this->assertStringContainsString( 'name="resetpasswordform"', $reset );

        foreach ( [ 'name="pass1"', 'name="pass2"', 'name="key"', 'name="login"', 'name="wpuf_reset_password"' ] as $field ) {
            $this->assertStringContainsString( $field, $reset );
        }
    }
}
