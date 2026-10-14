<?php
/**
 * The post form REST routes: schema, submit, update, draft, gates and hooks.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Forms\Form;

class FrontendFormRestTest extends WP_UnitTestCase {

    private $server;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;

        do_action( 'rest_api_init' );
    }

    private function form( array $settings = [] ) {
        $form_id = wpuf_create_sample_form( 'React Form', 'wpuf_forms' );

        if ( $settings ) {
            update_post_meta( $form_id, 'wpuf_form_settings', array_merge( (array) wpuf_get_form_settings( $form_id ), $settings ) );
        }

        return $form_id;
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    private function submission( $form_id, array $extra = [] ) {
        return array_merge(
            [
                'post_title'   => 'Posted through REST',
                'post_content' => 'Body text',
                'page_id'      => 0,
                'wpuf_nonce'   => wp_create_nonce( 'wpuf_form_add' ),
            ],
            $extra
        );
    }

    public function test_routes_are_registered() {
        $routes = $this->server->get_routes();

        foreach ( [ 'schema', 'submissions', 'submissions/(?P<post_id>\d+)', 'drafts', 'terms', 'embed', 'states' ] as $tail ) {
            $this->assertArrayHasKey( '/wpuf/v1/forms/(?P<id>\d+)/' . $tail, $routes, $tail );
        }
    }

    public function test_schema_describes_the_form() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id  = $this->form();
        $response = $this->request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' );

        $this->assertSame( 200, $response->get_status() );

        $data = $response->get_data();

        $this->assertSame( $form_id, $data['id'] );
        $this->assertSame( 'create', $data['mode'] );
        $this->assertTrue( $data['state']['open'] );
        $this->assertSame( [ 'post_title', 'post_content' ], wp_list_pluck( $data['fields'], 'name' ) );
        $this->assertSame( 'post', $data['settings']['post_type'] );
        $this->assertSame( 'Submit', $data['layout']['submit_text'] );
        $this->assertTrue( $data['needs']['editor'], 'the rich post content field needs the editor' );
        $this->assertSame( 1, wp_verify_nonce( $data['nonces']['submit'], 'wpuf_form_add' ) );
        $this->assertArrayNotHasKey( 'notification', $data['settings'], 'admin-only settings stay server-side' );
    }

    public function test_schema_captures_the_printing_hooks_in_slots() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id = $this->form();
        $seen    = [];

        add_action(
            'wpuf_form_fields_top',
            function ( $form, $fields ) use ( &$seen ) {
                $seen = [ $form, $fields ];
                echo '<li class="probe-top">top</li>';
            },
            10,
            2
        );
        add_action(
            'wpuf_add_post_form_bottom',
            function ( $id, $settings ) {
                echo '<li class="probe-bottom">' . (int) $id . '</li>';
            },
            10,
            2
        );

        $data = $this->request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' )->get_data();

        $this->assertStringContainsString( 'probe-top', $data['slots']['fields_top'] );
        $this->assertStringContainsString( 'probe-bottom">' . $form_id, $data['slots']['form_bottom'] );
        $this->assertInstanceOf( Form::class, $seen[0], 'the hook gets the Form object, as the classic renderer passes it' );
        $this->assertSame( 'post_title', $seen[1][0]['name'] );
    }

    public function test_visitor_is_refused_on_a_login_only_form() {
        wp_set_current_user( 0 );
        $form_id = $this->form();

        $this->assertSame( 401, $this->request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' )->get_status() );
        $this->assertSame( 401, $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) )->get_status() );
    }

    public function test_visitor_reads_a_guest_form() {
        wp_set_current_user( 0 );
        $form_id = $this->form( [ 'post_permission' => 'guest_post' ] );

        $this->assertSame( 200, $this->request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' )->get_status() );
    }

    public function test_submit_creates_the_post_and_fires_the_hooks() {
        $user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $user_id );
        $form_id = $this->form();
        $fired   = [];

        add_action(
            'wpuf_add_post_after_insert',
            function ( $post_id, $fid, $settings, $meta_vars ) use ( &$fired ) {
                $fired = [ $post_id, $fid, $settings['post_type'], $meta_vars ];
            },
            10,
            4
        );

        $response = $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) );

        $this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

        $data = $response->get_data();
        $post = get_posts(
            [
                'author'      => $user_id,
                'post_status' => 'any',
                'numberposts' => 1,
            ]
        )[0];

        $this->assertTrue( $data['success'] );
        $this->assertSame( get_permalink( $post ), $data['redirect_to'], 'redirect_to = post (the sample form setting)' );
        $this->assertFalse( $data['show_message'] );
        $this->assertSame( 'Posted through REST', $post->post_title );
        $this->assertSame( 'publish', $post->post_status );
        $this->assertSame( (string) $form_id, get_post_meta( $post->ID, '_wpuf_form_id', true ) );
        $this->assertSame( $post->ID, $fired[0] );
        $this->assertSame( $form_id, $fired[1] );
        $this->assertSame( 'post', $fired[2] );
    }

    public function test_submit_same_page_answers_the_message_instead_of_a_redirect() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id = $this->form(
            [
                'redirect_to' => 'same',
                'message'     => 'Thanks a lot',
            ]
        );

        $data = $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) )->get_data();

        $this->assertTrue( $data['show_message'] );
        $this->assertSame( 'Thanks a lot', $data['message'] );
        $this->assertFalse( $data['redirect_to'] );
    }

    public function test_submit_with_a_stale_nonce_is_refused() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id  = $this->form();
        $response = $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id, [ 'wpuf_nonce' => 'nope' ] ) );

        $this->assertSame( 403, $response->get_status() );
        $this->assertSame( 'wpuf_submit_expired', $response->get_data()['code'] );
        $this->assertSame( 0, (int) wp_count_posts()->publish );
    }

    public function test_a_validation_filter_becomes_a_rest_error_with_its_message() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id = $this->form();

        add_filter(
            'wpuf_add_post_validate',
            function () {
                return 'Nope, not today';
            }
        );

        $response = $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) );

        $this->assertSame( 400, $response->get_status() );
        $this->assertSame( 'wpuf_submit_rejected', $response->get_data()['code'] );
        $this->assertSame( 'Nope, not today', $response->get_data()['message'] );
        $this->assertSame( 'Nope, not today', $response->get_data()['data']['error'], 'the AJAX payload shape travels along' );
    }

    public function test_request_superglobals_are_restored_after_a_submit() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id          = $this->form();
        $_POST['marker']  = 'before';
        $_REQUEST['keep'] = 'yes';

        $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) );

        $this->assertSame( 'before', $_POST['marker'] );
        $this->assertSame( 'yes', $_REQUEST['keep'] );
        $this->assertArrayNotHasKey( 'post_title', $_POST );

        unset( $_POST['marker'], $_REQUEST['keep'] );
    }

    public function test_draft_saves_a_pending_draft() {
        $user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $user_id );
        $form_id  = $this->form();
        $response = $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/drafts', $this->submission( $form_id, [ 'post_title' => 'Draft one' ] ) );

        $this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

        $data = $response->get_data();

        $this->assertSame( 'Post Saved', $data['message'] );
        $this->assertSame( 'draft', get_post_status( $data['post_id'] ) );
        $this->assertSame( '1', get_post_meta( $data['post_id'], '_wpuf_draft_pending', true ) );
        $this->assertSame( (string) $form_id, get_post_meta( $data['post_id'], '_wpuf_form_id', true ) );
    }

    public function test_draft_needs_a_logged_in_user() {
        wp_set_current_user( 0 );
        $form_id = $this->form( [ 'post_permission' => 'guest_post' ] );

        $this->assertSame( 401, $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/drafts', $this->submission( $form_id ) )->get_status() );
    }

    public function test_update_is_refused_for_another_users_post() {
        $owner = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $other = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $form  = $this->form();

        wp_set_current_user( $owner );
        $created = $this->request( 'POST', '/wpuf/v1/forms/' . $form . '/submissions', $this->submission( $form ) )->get_data();
        $post_id = (int) url_to_postid( $created['redirect_to'] );

        wp_set_current_user( $other );
        $response = $this->request( 'PUT', '/wpuf/v1/forms/' . $form . '/submissions/' . $post_id, $this->submission( $form, [ 'post_title' => 'Hijacked' ] ) );

        $this->assertGreaterThanOrEqual( 400, $response->get_status() );
        $this->assertSame( 'Posted through REST', get_post( $post_id )->post_title );
    }

    public function test_edit_schema_carries_the_values_and_the_edit_gate() {
        $owner = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $form  = $this->form();

        wp_set_current_user( $owner );
        $created = $this->request( 'POST', '/wpuf/v1/forms/' . $form . '/submissions', $this->submission( $form ) )->get_data();
        $post_id = (int) url_to_postid( $created['redirect_to'] );

        $data = $this->request( 'GET', '/wpuf/v1/forms/' . $form . '/schema', [ 'post_id' => $post_id ] )->get_data();

        $this->assertSame( 'edit', $data['mode'] );
        $this->assertSame( 'Posted through REST', $data['fields'][0]['value'] );
        $this->assertSame( $post_id, $data['post']['id'] );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

        $this->assertSame( 403, $this->request( 'GET', '/wpuf/v1/forms/' . $form . '/schema', [ 'post_id' => $post_id ] )->get_status() );
    }

    public function test_the_legacy_ajax_payload_shape_is_unchanged_for_a_guest_email_error() {
        // The two hard exits of wpuf_get_post_user() now go through Ajax_Abort; outside a collect they still answer the client.
        $this->assertFalse( \WeDevs\Wpuf\Platform\Http\Ajax_Abort::collecting() );

        $error = \WeDevs\Wpuf\Platform\Http\Ajax_Abort::collect(
            function () {
                \WeDevs\Wpuf\Platform\Http\Ajax_Abort::send(
                    [
                        'success' => false,
                        'error'   => 'Invalid email address.',
                    ]
                );
            }
        );

        $this->assertWPError( $error );
        $this->assertSame( 'Invalid email address.', $error->get_error_message() );
        $this->assertFalse( \WeDevs\Wpuf\Platform\Http\Ajax_Abort::collecting(), 'the collect depth is reset' );
    }

    public function test_submit_builds_the_admin_side_submission_hooks_once() {
        // A classic submit runs on admin-ajax with the admin layer built; the REST submit
        // builds its submission hooks (post templates, then add-ons on the action) itself.
        $user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $user_id );
        $form_id = $this->form();

        $this->assertSame( 0, did_action( 'wpuf_frontend_submission_hooks' ) );

        $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) );
        $this->request( 'POST', '/wpuf/v1/forms/' . $form_id . '/submissions', $this->submission( $form_id ) );

        $this->assertSame( 1, did_action( 'wpuf_frontend_submission_hooks' ), 'once per request' );
        $this->assertTrue(
            wpuf()->platform()->has( \WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Templates::class ),
            'the post form templates service is built for the submit'
        );
    }
}
