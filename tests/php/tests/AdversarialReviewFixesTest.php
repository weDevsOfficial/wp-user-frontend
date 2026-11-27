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
        update_option( 'wpuf_profile', [ 'login_form_turnstile' => 'on' ] );
        add_filter(
            'pre_http_request', function () {
                return [ 'body' => wp_json_encode( [ 'success' => false, 'error-codes' => [ 'invalid-input-response' ] ] ), 'response' => [ 'code' => 200 ] ];
            }
        );

        $response = $this->login( [ 'cf-turnstile-response' => 'forged' ] );

        $this->assertSame( 400, $response->get_status() );
        $this->assertStringContainsString( 'Turnstile', $response->as_error()->get_error_message() );
    }

    /** AR-33: Turnstile on for other forms but not the login form: logins work (no widget, no token). */
    public function test_turnstile_off_on_the_login_form_does_not_block_login() {
        update_option( 'wpuf_general', [ 'enable_turnstile' => 'on', 'turnstile_secret_key' => 'secret' ] );
        update_option( 'wpuf_profile', [ 'login_form_turnstile' => 'off' ] );
        add_filter(
            'pre_http_request', function () {
                return [ 'body' => wp_json_encode( [ 'success' => false ] ), 'response' => [ 'code' => 200 ] ];
            }
        );

        $this->assertSame( 200, $this->login()->get_status() );
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

    /** AR-09: old pack screens and pack links open the subscriptions screen. */
    public function test_pack_screen_urls() {
        $url = \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::screen_url( 12 );

        $this->assertStringContainsString( 'page=wpuf_subscription', $url );
        $this->assertStringContainsString( 'action=edit', $url );
        $this->assertStringContainsString( 'id=12', $url );
        $this->assertStringContainsString( 'action=new', \WeDevs\Wpuf\Admin\Subscriptions\Pack_Post_Type::screen_url( 'new' ) );
        $this->assertSame( 10, has_action( 'load-post.php', [ wpuf()->subscription, 'redirect_classic_pack_screens' ] ) );
    }

    /** AR-07: a filtered wpuf_admin_role may read the settings but only administrators save them. */
    public function test_settings_save_needs_manage_options() {
        $editor = self::factory()->user->create( [ 'role' => 'editor' ] );
        $filter = function () {
            return 'edit_others_posts';
        };

        add_filter( 'wpuf_admin_role', $filter );
        wp_set_current_user( $editor );

        $this->assertSame( 200, rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/settings' ) )->get_status() );

        $save = new WP_REST_Request( 'POST', '/wpuf/v1/settings' );
        $save->set_param( 'section', 'wpuf_general' );
        $save->set_param( 'values', [ 'admin_access' => 'read' ] );

        $this->assertSame( 403, rest_do_request( $save )->get_status() );

        remove_filter( 'wpuf_admin_role', $filter );
    }

    /** AR-12: one account's failed logins do not lock other visitors behind the same address out. */
    public function test_login_rate_limit_is_per_account() {
        remove_all_filters( 'wpuf_rate_limit' );
        wp_set_current_user( 0 );

        $try = function ( $log ) {
            $request = new WP_REST_Request( 'POST', '/wpuf/v1/login' );
            $request->set_header( 'content-type', 'application/json' );
            $request->set_body( wp_json_encode( [ 'log' => $log, 'pwd' => 'wrong', 'wpuf_login' => 'true', 'wpuf-login-nonce' => wp_create_nonce( 'wpuf_login_action' ) ] ) );

            return rest_do_request( $request )->get_status();
        };

        for ( $i = 0; $i < 10; $i++ ) {
            $try( 'someone' );
        }

        $this->assertSame( 429, $try( 'someone' ) );
        $this->assertSame( 400, $try( 'someone_else' ), 'another account from the same address still gets an answer' );
    }

    /** AR-13: upload values read every meta row (one id per row), not just the first. */
    public function test_upload_values_read_every_meta_row() {
        $ids = new ReflectionMethod( \WeDevs\Wpuf\Frontend\Forms\Form_Schema::class, 'upload_ids' );
        $ids->setAccessible( true );
        $schema = ( new ReflectionClass( \WeDevs\Wpuf\Frontend\Forms\Form_Schema::class ) )->newInstanceWithoutConstructor();

        $this->assertSame( [ 11, 12, 13 ], $ids->invoke( $schema, [ '11', '12', '13' ] ), 'one id per row' );
        $this->assertSame( [ 11, 12 ], $ids->invoke( $schema, [ [ '11', '12' ] ] ), 'one row holding an array' );
        $this->assertSame( [ 11, 12 ], $ids->invoke( $schema, [ serialize( [ 11, 12 ] ) ] ), 'one serialized row' );
        $this->assertSame( [ 11, 12 ], $ids->invoke( $schema, [ '11,12' ] ), 'a comma list' );
        $this->assertSame( [], $ids->invoke( $schema, [] ) );

        $post = self::factory()->post->create();
        add_post_meta( $post, 'gallery', 21 );
        add_post_meta( $post, 'gallery', 22 );
        $this->assertSame( [ 21, 22 ], $ids->invoke( $schema, get_post_meta( $post, 'gallery', false ) ) );
    }
}
