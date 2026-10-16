<?php
/**
 * Fixes from the 2026-10-16 adversarial review (AR-xx ids in the openspec
 * adversarial-review list): each test fails on the code before the fix.
 *
 * @package WP_User_Frontend
 */
class AdversarialReviewFixesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        add_filter( 'wpuf_rate_limit', '__return_zero' );
        do_action( 'rest_api_init' );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_rate_limit' );
        remove_all_filters( 'pre_http_request' );
        delete_option( 'wpuf_general' );
        delete_option( 'wpuf_profile' );
        $_POST    = [];
        $_REQUEST = [];
        parent::tear_down();
    }

    private function login( array $extra = [] ) {
        $user = self::factory()->user->create( [ 'user_login' => 'ar_login', 'user_pass' => 'Right-Pass-1' ] );

        wp_set_current_user( 0 );

        $request = new WP_REST_Request( 'POST', '/wpuf/v1/login' );
        $request->set_header( 'content-type', 'application/json' );
        $request->set_body(
            wp_json_encode(
                array_merge(
                    [
                        'log'              => 'ar_login',
                        'pwd'              => 'Right-Pass-1',
                        'wpuf_login'       => 'true',
                        'wpuf-login-nonce' => wp_create_nonce( 'wpuf_login_action' ),
                    ],
                    $extra
                )
            )
        );

        return rest_do_request( $request );
    }

    /** AR-30: a user cannot hand themselves a pack from their own profile screen. */
    public function test_profile_screen_pack_update_needs_edit_users() {
        $pack = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish' ] );
        update_post_meta( $pack, '_billing_amount', 10 );
        $user = self::factory()->user->create( [ 'role' => 'subscriber' ] );

        wp_set_current_user( $user );
        $_POST = $_REQUEST = [
            'wpuf-subscription-nonce' => wp_create_nonce( 'update-profile_' . $user ),
            'pack_id'                 => $pack,
        ];

        $admin = ( new ReflectionClass( \WeDevs\Wpuf\Admin\Admin_Subscription::class ) )->newInstanceWithoutConstructor();
        ( new \WeDevs\Wpuf\Admin\Subscriptions\User_Profile( $admin ) )->profile_subscription_update( $user );

        $this->assertEmpty( get_user_meta( $user, '_wpuf_subscription_pack', true ) );
    }

    /** AR-33: a failed Turnstile check stops the login. */
    public function test_failed_turnstile_stops_login() {
        update_option( 'wpuf_general', [ 'enable_turnstile' => 'on', 'turnstile_secret_key' => 'secret' ] );
        add_filter(
            'pre_http_request', function () {
                return [ 'body' => wp_json_encode( [ 'success' => false, 'error-codes' => [ 'invalid-input-response' ] ] ), 'response' => [ 'code' => 200 ] ];
            }
        );

        $response = $this->login( [ 'cf-turnstile-response' => 'forged' ] );

        $this->assertSame( 400, $response->get_status() );
        $this->assertStringContainsString( 'Turnstile', $response->as_error()->get_error_message() );
    }

    /** AR-33: with reCAPTCHA on, leaving the token out does not skip it. */
    public function test_login_without_recaptcha_token_is_refused_when_recaptcha_is_on() {
        update_option( 'wpuf_profile', [ 'login_form_recaptcha' => 'on' ] );
        update_option( 'wpuf_general', [ 'recaptcha_public' => 'pub', 'recaptcha_private' => 'priv' ] );

        $response = $this->login();

        $this->assertSame( 400, $response->get_status() );
        $this->assertStringContainsString( 'reCaptcha', $response->as_error()->get_error_message() );
    }

    /** AR-33: reCAPTCHA on without keys keeps logins working (nothing is printed to solve). */
    public function test_login_works_when_recaptcha_is_on_without_keys() {
        update_option( 'wpuf_profile', [ 'login_form_recaptcha' => 'on' ] );

        $this->assertSame( 200, $this->login()->get_status() );
    }

    /** AR-31: the shared registration role guard. */
    public function test_registration_role_guard() {
        $this->assertFalse( wpuf_is_role_allowed_for_registration( 'administrator' ) );
        $this->assertFalse( wpuf_is_role_allowed_for_registration( 'no_such_role' ) );
        $this->assertTrue( wpuf_is_role_allowed_for_registration( 'subscriber' ) );
        $this->assertTrue( wpuf_is_role_allowed_for_registration( 'editor' ) );
    }

    /** AR-37: only published packs are for sale. */
    public function test_only_published_packs_are_purchasable() {
        $draft = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'draft' ] );
        $live  = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish' ] );
        $post  = self::factory()->post->create();

        $this->assertFalse( wpuf()->subscription->get_purchasable_subscription( $draft ) );
        $this->assertFalse( wpuf()->subscription->get_purchasable_subscription( $post ) );
        $this->assertSame( $live, wpuf()->subscription->get_purchasable_subscription( $live )->ID );
        $this->assertNotFalse( wpuf()->subscription->get_subscription( $draft ) );
    }

    /** AR-38: the upload response escapes the field name. */
    public function test_upload_markup_escapes_the_field_name() {
        $attachment = self::factory()->attachment->create( [ 'post_mime_type' => 'application/pdf', 'post_title' => 'doc' ] );

        $html = \WeDevs\Wpuf\Frontend\Forms\Upload_Handler::attach_html( $attachment, 'x" onmouseover="alert(1)' );

        $this->assertStringNotContainsString( '" onmouseover="', $html );
    }
}
