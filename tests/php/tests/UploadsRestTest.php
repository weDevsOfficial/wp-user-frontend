<?php
/**
 * The upload REST routes and the rate limiter behind the guest routes.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\REST\Rate_Limit;

class UploadsRestTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();

        do_action( 'rest_api_init' );
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    public function test_routes_are_registered() {
        $routes = rest_get_server()->get_routes();

        $this->assertArrayHasKey( '/wpuf/v1/uploads', $routes );
        $this->assertArrayHasKey( '/wpuf/v1/uploads/(?P<id>\d+)', $routes );
    }

    public function test_upload_needs_the_upload_nonce_and_a_form_the_visitor_may_use() {
        $form_id = wpuf_create_sample_form( 'Upload Form', 'wpuf_forms' );

        wp_set_current_user( 0 );
        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/uploads', [ 'form_id' => $form_id, 'wpuf_nonce' => 'nope' ] )->get_status() );
        $this->assertSame( 401, $this->request( 'POST', '/wpuf/v1/uploads', [ 'form_id' => $form_id, 'wpuf_nonce' => wp_create_nonce( 'wpuf-upload-nonce' ) ] )->get_status(), 'a login-only form refuses visitors' );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $response = $this->request( 'POST', '/wpuf/v1/uploads', [ 'form_id' => $form_id, 'wpuf_nonce' => wp_create_nonce( 'wpuf-upload-nonce' ) ] );

        $this->assertSame( 400, $response->get_status(), 'allowed, but no file in the request' );
        $this->assertSame( 'wpuf_upload_no_file', $response->get_data()['code'] );
    }

    public function test_delete_needs_ownership() {
        $owner = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $other = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $file  = DIR_TESTDATA . '/images/canola.jpg';

        wp_set_current_user( $owner );
        $attachment = self::factory()->attachment->create_upload_object( $file );
        wp_update_post(
            [
                'ID'          => $attachment,
                'post_author' => $owner,
            ]
        );

        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->request( 'DELETE', '/wpuf/v1/uploads/' . $attachment )->get_status() );

        wp_set_current_user( $other );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/uploads/' . $attachment )->get_status() );
        $this->assertNotNull( get_post( $attachment ) );

        wp_set_current_user( $owner );
        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/uploads/' . $attachment )->get_status() );
        $this->assertNull( get_post( $attachment ) );
        $this->assertSame( 404, $this->request( 'DELETE', '/wpuf/v1/uploads/' . $attachment )->get_status() );
    }

    public function test_rate_limit_counts_hits_per_key_inside_the_window() {
        wp_set_current_user( 0 );
        $_SERVER['REMOTE_ADDR'] = '203.0.113.9';
        $limiter                = new Rate_Limit();

        $this->assertTrue( $limiter->allow( 'probe', 2, 60 ) );
        $this->assertTrue( $limiter->allow( 'probe', 2, 60 ) );
        $this->assertFalse( $limiter->allow( 'probe', 2, 60 ), 'third hit inside the window is refused' );
        $this->assertTrue( $limiter->allow( 'other', 2, 60 ), 'keys are independent' );

        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $this->assertTrue( $limiter->allow( 'probe', 2, 60 ), 'another client has its own window' );

        add_filter( 'wpuf_rate_limit', '__return_zero' );
        $this->assertTrue( $limiter->allow( 'probe', 2, 60 ), 'a zero limit disables the check' );
        remove_filter( 'wpuf_rate_limit', '__return_zero' );

        wp_set_current_user( self::factory()->user->create() );
        $this->assertSame( 'user:' . get_current_user_id(), $limiter->actor() );
    }

    private function upload_field( $form_id, array $field ) {
        wp_insert_post(
            [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $form_id,
                'post_status'  => 'publish',
                'post_content' => maybe_serialize( $field ),
            ]
        );
    }

    public function test_the_server_enforces_the_field_size_and_file_types() {
        $service = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Forms\Upload_Service::class );
        $form_id = self::factory()->post->create( [ 'post_type' => 'wpuf_forms' ] );

        $this->upload_field( $form_id, [ 'input_type' => 'file_upload', 'template' => 'file_upload', 'name' => 'logo', 'max_size' => '50', 'extension' => [ 'images' ] ] );
        $this->upload_field( $form_id, [ 'input_type' => 'image_upload', 'template' => 'image_upload', 'name' => 'photo', 'max_size' => '0' ] );
        $this->upload_field( $form_id, [ 'input_type' => 'file_upload', 'template' => 'file_upload', 'name' => 'any', 'max_size' => '', 'extension' => [] ] );

        $big = $service->check_field_limits( [ 'name' => 'a.png', 'size' => 60 * 1024 ], $form_id, 'logo' );
        $this->assertWPError( $big );
        $this->assertSame( 'wpuf_upload_too_big', $big->get_error_code() );

        $pdf = $service->check_field_limits( [ 'name' => 'a.pdf', 'size' => 10 ], $form_id, 'logo' );
        $this->assertSame( 'wpuf_upload_bad_type', $pdf->get_error_code() );

        $this->assertTrue( $service->check_field_limits( [ 'name' => 'a.PNG', 'size' => 50 * 1024 ], $form_id, 'logo' ), 'at the limit, extension case-insensitive' );
        $this->assertSame( 'wpuf_upload_bad_type', $service->check_field_limits( [ 'name' => 'a.pdf', 'size' => 10 ], $form_id, 'photo' )->get_error_code(), 'image fields take images only' );
        $this->assertTrue( $service->check_field_limits( [ 'name' => 'a.zip', 'size' => 99999999 ], $form_id, 'any' ), 'no limits set: WordPress rules only' );
    }
}
