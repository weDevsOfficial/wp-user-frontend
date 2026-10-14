<?php
/**
 * The Pro registration / profile REST routes (wpuf/v1/registration): schema,
 * register, profile update, username check, gates and hooks. Runs only with
 * Pro loaded (WPUF_TESTS_WITH_PRO=1).
 *
 * @package WP_User_Frontend
 */

class RegistrationRestTest extends WP_UnitTestCase {

    private $server;

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;

        do_action( 'rest_api_init' );

        // One rate-limit actor per test, and no Set-Cookie headers from the auto-login.
        $_SERVER['REMOTE_ADDR'] = '10.9.' . wp_rand( 0, 255 ) . '.' . wp_rand( 1, 254 );
        add_filter( 'send_auth_cookies', '__return_false' );
        update_option( 'users_can_register', 1 );
        wp_set_current_user( 0 );
    }

    private function form( array $settings = [] ) {
        $form_id = wpuf_create_sample_form( 'React Registration', 'wpuf_profile' );

        if ( $settings ) {
            update_post_meta( $form_id, 'wpuf_form_settings', array_merge( (array) wpuf_get_form_settings( $form_id ), $settings ) );
        }

        return $form_id;
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        if ( in_array( $method, [ 'POST', 'PUT' ], true ) ) {
            $request->set_body_params( $params );
        } else {
            foreach ( $params as $key => $value ) {
                $request->set_param( $key, $value );
            }
        }

        return rest_do_request( $request );
    }

    private function registration( array $extra = [] ) {
        return array_merge(
            [
                'user_email' => 'react-' . wp_rand() . '@example.com',
                'pass1'      => 'Str0ng-Pass!',
                'pass2'      => 'Str0ng-Pass!',
                'page_id'    => 0,
                'wpuf_nonce' => wp_create_nonce( 'wpuf_form_add' ),
            ],
            $extra
        );
    }

    public function test_routes_are_registered() {
        $routes = $this->server->get_routes();

        $this->assertArrayHasKey( '/wpuf/v1/registration/(?P<form_id>\d+)/schema', $routes );
        $this->assertArrayHasKey( '/wpuf/v1/registration/(?P<form_id>\d+)/submissions', $routes );
        $this->assertArrayHasKey( '/wpuf/v1/registration/(?P<form_id>\d+)/profile', $routes );
        $this->assertArrayHasKey( '/wpuf/v1/registration/username-check', $routes );
        $this->assertCount( 2, $routes['/wpuf/v1/registration/(?P<form_id>\d+)/profile'], 'GET and PUT on the profile route' );
    }

    public function test_registration_schema_for_a_visitor() {
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/registration/' . $form_id . '/schema' );

        $this->assertSame( 200, $response->get_status() );

        $data = $response->get_data();

        $this->assertSame( 'profile', $data['form_type'] );
        $this->assertSame( 'registration', $data['mode'] );
        $this->assertTrue( $data['state']['open'] );
        $this->assertSame( [ 'user_email', 'password' ], wp_list_pluck( $data['fields'], 'name' ) );
        $this->assertSame( 'yes', $data['fields'][1]['repeat_pass'] );
        $this->assertSame( 'Register', $data['layout']['submit_text'] );
        $this->assertNotEmpty( $data['links']['login'] );
        $this->assertSame( 1, wp_verify_nonce( $data['nonces']['submit'], 'wpuf_form_add' ) );
        $this->assertSame( [], $data['extra_fields'] );
    }

    public function test_registration_schema_is_closed_when_the_site_disables_registration() {
        update_option( 'users_can_register', 0 );
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/registration/' . $form_id . '/schema' );

        $this->assertSame( 403, $response->get_status() );
    }

    public function test_registration_schema_for_a_post_form_is_not_found() {
        $post_form = wpuf_create_sample_form( 'Post Form', 'wpuf_forms' );
        $response  = $this->request( 'GET', '/wpuf/v1/registration/' . $post_form . '/schema' );

        $this->assertSame( 404, $response->get_status() );
    }

    public function test_registration_schema_tells_a_logged_in_user() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/registration/' . $form_id . '/schema' );

        $this->assertSame( 200, $response->get_status() );
        $this->assertFalse( $response->get_data()['state']['open'] );
        $this->assertSame( 'logged_in', $response->get_data()['state']['reason'] );
    }

    public function test_register_creates_the_user_and_fires_the_hooks() {
        $form_id = $this->form( [ 'wpuf_user_status' => 'approved' ] );
        $fired   = [];

        add_action(
            'wpuf_after_register',
            function ( $user_id, $hook_form_id ) use ( &$fired ) {
                $fired[] = [ $user_id, $hook_form_id ];
            },
            10,
            2
        );

        $input    = $this->registration();
        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $input );

        $this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

        $data = $response->get_data();
        $user = get_user_by( 'email', $input['user_email'] );

        $this->assertTrue( $data['success'] );
        $this->assertInstanceOf( 'WP_User', $user );
        $this->assertSame( $user->ID, $data['post_id'] );
        $this->assertTrue( $data['show_message'], 'reg_redirect_to = same shows the message in place' );
        $this->assertSame( 'Registration successful', $data['message'] );
        $this->assertContains( 'subscriber', $user->roles );
        $this->assertTrue( wp_check_password( 'Str0ng-Pass!', $user->user_pass, $user->ID ) );
        $this->assertSame( [ [ $user->ID, $form_id ] ], $fired );
        $this->assertSame( $user->ID, get_current_user_id(), 'the auto-login after registration' );
    }

    public function test_register_follows_the_redirect_setting() {
        $page    = self::factory()->post->create( [ 'post_type' => 'page' ] );
        $form_id = $this->form( [ 'reg_redirect_to' => 'page', 'reg_page_id' => $page ] );

        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $this->registration() );

        $this->assertSame( 200, $response->get_status() );
        $this->assertFalse( $response->get_data()['show_message'] );
        $this->assertSame( get_permalink( $page ), $response->get_data()['redirect_to'] );
    }

    public function test_register_rejects_a_password_mismatch_without_creating_a_user() {
        $form_id = $this->form();
        $input   = $this->registration( [ 'pass2' => 'other' ] );

        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $input );

        $this->assertSame( 'wpuf_submit_rejected', $response->as_error()->get_error_code() );
        $this->assertSame( "Password didn't match", $response->as_error()->get_error_message() );
        $this->assertFalse( get_user_by( 'email', $input['user_email'] ) );
    }

    public function test_register_rejects_a_taken_email() {
        $form_id = $this->form();
        $taken   = get_userdata( self::factory()->user->create() )->user_email;

        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $this->registration( [ 'user_email' => $taken ] ) );

        $this->assertSame( 'wpuf_submit_rejected', $response->as_error()->get_error_code() );
        $this->assertSame( 'E-mail address already exists.', $response->as_error()->get_error_message() );
    }

    public function test_register_needs_the_form_nonce() {
        $form_id = $this->form();
        $input   = $this->registration( [ 'wpuf_nonce' => 'stale' ] );

        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $input );

        $this->assertSame( 403, $response->get_status() );
        $this->assertSame( 'wpuf_submit_expired', $response->as_error()->get_error_code() );
        $this->assertFalse( get_user_by( 'email', $input['user_email'] ) );
    }

    public function test_register_refuses_a_logged_in_user() {
        wp_set_current_user( self::factory()->user->create() );
        $form_id = $this->form();

        $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $this->registration() );

        $this->assertSame( 403, $response->get_status() );
    }

    public function test_register_has_no_rate_limit_like_the_classic_form() {
        $form_id = $this->form();

        // Owner 2026-10-14: no per-IP limit for now (classic has none); it comes back as a later enhancement.
        for ( $i = 0; $i < 8; $i++ ) {
            $response = $this->request( 'POST', '/wpuf/v1/registration/' . $form_id . '/submissions', $this->registration( [ 'wpuf_nonce' => 'stale' ] ) );
            $this->assertSame( 403, $response->get_status(), 'attempt ' . ( $i + 1 ) );
        }
    }

    public function test_profile_schema_needs_a_login() {
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/registration/' . $form_id . '/profile' );

        $this->assertSame( 401, $response->get_status() );
    }

    public function test_profile_schema_carries_the_user_values() {
        $user_id = self::factory()->user->create( [ 'role' => 'subscriber', 'user_email' => 'me@example.com' ] );
        wp_set_current_user( $user_id );
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/registration/' . $form_id . '/profile' );

        $this->assertSame( 200, $response->get_status() );

        $data = $response->get_data();

        $this->assertSame( 'profile', $data['mode'] );
        $this->assertSame( $user_id, $data['user_id'] );
        $this->assertTrue( $data['state']['open'] );
        $this->assertSame( 'me@example.com', $data['fields'][0]['value'] );
        $this->assertSame( 'Update Profile', $data['layout']['update_text'] );
    }

    public function test_profile_update_changes_the_user_and_fires_the_hook() {
        $user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $user_id );
        $form_id = $this->form( [ 'profile_redirect_to' => 'same', 'update_message' => 'Saved.' ] );
        $fired   = [];

        add_action(
            'wpuf_update_profile',
            function ( $hook_user_id, $hook_form_id ) use ( &$fired ) {
                $fired[] = [ $hook_user_id, $hook_form_id ];
            },
            10,
            2
        );

        $response = $this->request(
            'PUT', '/wpuf/v1/registration/' . $form_id . '/profile', [
                'user_email' => 'changed@example.com',
                'pass1'      => '',
                'pass2'      => '',
                'wpuf_nonce' => wp_create_nonce( 'wpuf_form_add' ),
            ]
        );

        $this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
        $this->assertTrue( $response->get_data()['show_message'] );
        $this->assertSame( 'Saved.', $response->get_data()['message'] );
        $this->assertSame( 'changed@example.com', get_userdata( $user_id )->user_email );
        $this->assertSame( [ [ $user_id, $form_id ] ], $fired );
    }

    public function test_profile_update_needs_a_login() {
        $form_id  = $this->form();
        $response = $this->request(
            'PUT', '/wpuf/v1/registration/' . $form_id . '/profile', [
                'user_email' => 'changed@example.com',
                'wpuf_nonce' => wp_create_nonce( 'wpuf_form_add' ),
            ]
        );

        $this->assertSame( 401, $response->get_status() );
    }

    public function test_username_check_answers_only_availability() {
        $taken = get_userdata( self::factory()->user->create( [ 'user_login' => 'takenuser' ] ) );

        $this->assertFalse( $this->request( 'GET', '/wpuf/v1/registration/username-check', [ 'value' => 'takenuser' ] )->get_data()['available'] );
        $this->assertFalse( $this->request( 'GET', '/wpuf/v1/registration/username-check', [ 'value' => $taken->user_email ] )->get_data()['available'] );
        $this->assertTrue( $this->request( 'GET', '/wpuf/v1/registration/username-check', [ 'value' => 'freshuser' ] )->get_data()['available'] );
        $this->assertSame( [ 'available' => true ], $this->request( 'GET', '/wpuf/v1/registration/username-check', [ 'value' => 'fresh@example.com' ] )->get_data() );
    }
}
