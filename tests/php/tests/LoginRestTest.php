<?php
/**
 * `wpuf/v1/login...` (LoginController): the React login screens send their
 * fields here and Simple_Login runs the classic checks. One generic message
 * for a wrong user or password, the same answer for a lost password whether
 * the account exists or not, the form nonces, the reset key, visitors only.
 *
 * @package WP_User_Frontend
 */
class LoginRestTest extends WP_UnitTestCase {

    private $user;

    public function set_up() {
        parent::set_up();

        $this->user = self::factory()->user->create_and_get( [ 'user_login' => 'rest_login_user', 'user_email' => 'rest-login@example.com', 'user_pass' => 'Right-Pass-1' ] );

        add_filter( 'wpuf_rate_limit', '__return_zero' );
        wp_set_current_user( 0 );
        do_action( 'rest_api_init' );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_rate_limit' );
        remove_all_filters( 'pre_wp_mail' );
        $_POST    = [];
        $_REQUEST = [];
        parent::tear_down();
    }

    private function post( $path, array $body ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1' . $path );

        $request->set_header( 'content-type', 'application/json' );
        $request->set_body( wp_json_encode( $body ) );

        return rest_do_request( $request );
    }

    private function login_body( $log, $pwd ) {
        return [
            'log'              => $log,
            'pwd'              => $pwd,
            'wpuf_login'       => 'true',
            'wpuf-login-nonce' => wp_create_nonce( 'wpuf_login_action' ),
            'redirect_to'      => home_url( '/after/' ),
        ];
    }

    public function test_wrong_password_and_unknown_user_get_the_same_message() {
        $wrong   = $this->post( '/login', $this->login_body( 'rest_login_user', 'nope' ) );
        $unknown = $this->post( '/login', $this->login_body( 'no_such_user', 'nope' ) );
        $email   = $this->post( '/login', $this->login_body( 'nobody@example.com', 'nope' ) );

        $this->assertSame( 400, $wrong->get_status() );
        $this->assertSame( $wrong->as_error()->get_error_message(), $unknown->as_error()->get_error_message() );
        $this->assertSame( $wrong->as_error()->get_error_message(), $email->as_error()->get_error_message() );
        $this->assertStringNotContainsString( 'rest_login_user', $wrong->as_error()->get_error_message() );
    }

    public function test_bad_nonce_is_refused() {
        $body                     = $this->login_body( 'rest_login_user', 'Right-Pass-1' );
        $body['wpuf-login-nonce'] = 'bad';

        $response = $this->post( '/login', $body );

        $this->assertSame( 400, $response->get_status() );
        $this->assertStringContainsString( 'session has expired', $response->as_error()->get_error_message() );
    }

    public function test_right_password_logs_in_and_answers_the_redirect() {
        $response = $this->post( '/login', $this->login_body( 'rest_login_user', 'Right-Pass-1' ) );

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( home_url( '/after/' ), $response->get_data()['redirect'] );
        $this->assertSame( [], wpuf()->frontend->simple_login->get_errors() );
    }

    public function test_logged_in_users_are_refused() {
        wp_set_current_user( $this->user->ID );

        $this->assertSame( 400, $this->post( '/login', $this->login_body( 'rest_login_user', 'Right-Pass-1' ) )->get_status() );
    }

    public function test_lost_password_answers_the_same_for_unknown_accounts_and_reports_mail_failure() {
        $mails = 0;

        add_filter(
            'pre_wp_mail',
            function () use ( &$mails ) {
                ++$mails;

                return true;
            }
        );

        $body = [
            'user_login'          => 'nobody-here@example.com',
            'wpuf_reset_password' => 'true',
            '_wpnonce'            => wp_create_nonce( 'wpuf_lost_pass' ),
        ];

        $unknown = $this->post( '/login/lost-password', $body );

        $body['user_login'] = 'rest-login@example.com';
        $known              = $this->post( '/login/lost-password', $body );

        $this->assertSame( 200, $unknown->get_status() );
        $this->assertSame( $unknown->get_data(), $known->get_data(), 'no account-existence leak' );
        $this->assertSame( 1, $mails, 'one e-mail, for the real account' );

        remove_all_filters( 'pre_wp_mail' );
        add_filter( 'pre_wp_mail', '__return_false' );

        $failed = $this->post( '/login/lost-password', $body );

        $this->assertSame( 400, $failed->get_status(), 'mail failure: an error in the form, no wp_die' );
        $this->assertStringContainsString( 'could not send the e-mail', $failed->as_error()->get_error_message() );
    }

    public function test_reset_password_with_the_key_sets_the_new_password() {
        $key  = get_password_reset_key( $this->user );
        $body = [
            'pass1'               => 'New-Pass-22',
            'pass2'               => 'New-Pass-22',
            'key'                 => $key,
            'login'               => 'rest_login_user',
            'wpuf_reset_password' => 'true',
            '_wpnonce'            => wp_create_nonce( 'wpuf_reset_pass' ),
        ];

        $bad        = $body;
        $bad['key'] = 'wrong';

        $this->assertStringContainsString( 'invalid or has expired', $this->post( '/login/reset-password', $bad )->as_error()->get_error_message() );

        $mismatch          = $body;
        $mismatch['pass2'] = 'Other-Pass-22';

        $this->assertStringContainsString( 'do not match', $this->post( '/login/reset-password', $mismatch )->as_error()->get_error_message() );

        $response = $this->post( '/login/reset-password', $body );

        $this->assertSame( 200, $response->get_status() );
        $this->assertStringContainsString( 'reset=true', $response->get_data()['redirect'] );
        $this->assertTrue( wp_check_password( 'New-Pass-22', get_userdata( $this->user->ID )->user_pass, $this->user->ID ) );
    }
}
