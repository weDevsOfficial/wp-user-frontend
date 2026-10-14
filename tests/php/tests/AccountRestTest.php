<?php
/**
 * The account REST routes: scoped to the current user, the section rules of account.php.
 *
 * @package WP_User_Frontend
 */

class AccountRestTest extends WP_UnitTestCase {

    private $server;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;

        do_action( 'rest_api_init' );
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    private function user() {
        $user_id = self::factory()->user->create(
            [
                'role'       => 'subscriber',
                'first_name' => 'Ada',
                'last_name'  => 'Lovelace',
                'user_pass'  => 'old-pass-123',
            ]
        );
        wp_set_current_user( $user_id );

        return $user_id;
    }

    public function test_visitors_are_refused_on_every_account_route() {
        wp_set_current_user( 0 );

        foreach ( [ [ 'GET', '/wpuf/v1/account' ], [ 'GET', '/wpuf/v1/account/posts' ], [ 'DELETE', '/wpuf/v1/account/posts/1' ], [ 'GET', '/wpuf/v1/account/sections/dashboard' ], [ 'PUT', '/wpuf/v1/account/profile' ], [ 'PUT', '/wpuf/v1/account/password' ], [ 'POST', '/wpuf/v1/account/avatar' ], [ 'DELETE', '/wpuf/v1/account/avatar' ] ] as $route ) {
            $this->assertSame( 401, $this->request( $route[0], $route[1] )->get_status(), $route[0] . ' ' . $route[1] );
        }
    }

    public function test_account_answers_the_current_users_profile_sections_and_stats() {
        $user_id = $this->user();
        $data    = $this->request( 'GET', '/wpuf/v1/account' )->get_data();

        $this->assertSame( $user_id, $data['profile']['id'] );
        $this->assertSame( 'Ada', $data['profile']['first_name'] );
        $this->assertSame( 'subscriber', $data['profile']['role'] );
        $this->assertSame( 'AL', $data['profile']['avatar']['initials'] );

        $slugs = wp_list_pluck( $data['sections'], 'slug' );

        $this->assertSame( 'dashboard', $slugs[0], 'dashboard first, as wpuf_get_account_sections() orders it' );
        $this->assertContains( 'post', $slugs );
        $this->assertContains( 'edit-profile', $slugs );
        $this->assertContains( 'change-password', $slugs );
        $this->assertContains( 'submit-post', $slugs, 'the free listener adds Submit Post through wpuf_account_sections' );

        $kinds = array_combine( $slugs, wp_list_pluck( $data['sections'], 'kind' ) );

        $this->assertSame( 'native', $kinds['dashboard'] );
        $this->assertSame( 'posts', $kinds['post'] );
        $this->assertSame( 'html', $kinds['submit-post'] );

        $stats = array_combine( wp_list_pluck( $data['stats'], 'id' ), $data['stats'] );

        $this->assertSame( 0, $stats['posts_post']['value'] );
        $this->assertSame( 1, wp_verify_nonce( $data['nonces']['profile'], 'wpuf-account-update-profile' ) );
    }

    public function test_sections_follow_the_show_settings_of_account_php() {
        $this->user();

        update_option( 'wpuf_my_account', [ 'show_subscriptions' => 'off', 'show_billing_address' => 'off' ] );
        $slugs = wp_list_pluck( $this->request( 'GET', '/wpuf/v1/account' )->get_data()['sections'], 'slug' );

        $this->assertNotContains( 'subscription', $slugs );
        $this->assertNotContains( 'billing-address', $slugs );
    }

    public function test_a_third_party_section_is_listed_as_html_and_served_by_its_hook() {
        $this->user();

        add_filter(
            'wpuf_account_sections',
            function ( $sections ) {
                $sections['loyalty'] = 'Loyalty';

                return $sections;
            }
        );
        add_action(
            'wpuf_account_content_loyalty',
            function ( $sections, $current ) {
                echo '<p class="loyalty">' . esc_html( $current ) . '</p>';
            },
            10,
            2
        );

        $sections = $this->request( 'GET', '/wpuf/v1/account' )->get_data()['sections'];
        $kinds    = array_combine( wp_list_pluck( $sections, 'slug' ), wp_list_pluck( $sections, 'kind' ) );

        $this->assertSame( 'html', $kinds['loyalty'] );

        $html = $this->request( 'GET', '/wpuf/v1/account/sections/loyalty' )->get_data()['html'];

        $this->assertStringContainsString( '<p class="loyalty">loyalty</p>', $html );
        $this->assertSame( 404, $this->request( 'GET', '/wpuf/v1/account/sections/nope' )->get_status() );
    }

    public function test_posts_lists_only_the_current_users_posts_and_deletes_only_theirs() {
        $user_id = $this->user();
        $other   = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        $mine    = self::factory()->post->create_many( 2, [ 'post_author' => $user_id ] );
        $theirs  = self::factory()->post->create( [ 'post_author' => $other ] );

        $response = $this->request( 'GET', '/wpuf/v1/account/posts', [ 'type' => 'post' ] );
        $data     = $response->get_data();

        $this->assertSame( 2, $data['total'] );
        $this->assertSame( '2', $response->get_headers()['X-WP-Total'] );
        $this->assertEqualSets( $mine, wp_list_pluck( $data['items'], 'id' ) );
        $this->assertSame( 'Live', $data['items'][0]['status_label'] );
        $this->assertTrue( $data['items'][0]['can_delete'] );

        update_option( 'wpuf_frontend_posting', [ 'edit_page_id' => self::factory()->post->create( [ 'post_type' => 'page' ] ) ] );
        $item = $this->request( 'GET', '/wpuf/v1/account/posts', [ 'type' => 'post' ] )->get_data()['items'][0];

        $this->assertStringContainsString( '_wpnonce=', $item['edit_url'] );
        $this->assertStringNotContainsString( '&amp;', $item['edit_url'], 'JSON carries a raw URL, not an HTML-escaped one' );
        $this->assertSame( 1, wp_verify_nonce( wp_parse_url( $item['edit_url'], PHP_URL_QUERY ) ? ( wp_parse_args( wp_parse_url( $item['edit_url'], PHP_URL_QUERY ) )['_wpnonce'] ?? '' ) : '', 'wpuf_edit' ) );

        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/account/posts/' . $theirs )->get_status() );
        $this->assertSame( 'publish', get_post_status( $theirs ) );

        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/account/posts/' . $mine[0] )->get_status() );
        $this->assertSame( 'trash', get_post_status( $mine[0] ) );

        $this->assertSame( 404, $this->request( 'GET', '/wpuf/v1/account/posts', [ 'type' => 'page' ] )->get_status(), 'only the post types enabled for the page' );
    }

    public function test_posts_follow_the_dashboard_edit_and_delete_settings() {
        $user_id = $this->user();
        $post    = self::factory()->post->create( [ 'post_author' => $user_id ] );
        update_option( 'wpuf_frontend_posting', [ 'edit_page_id' => self::factory()->post->create( [ 'post_type' => 'page' ] ) ] );

        update_option( 'wpuf_dashboard', [ 'enable_post_edit' => 'no', 'enable_post_del' => 'no' ] );
        $item = $this->request( 'GET', '/wpuf/v1/account/posts', [ 'type' => 'post' ] )->get_data()['items'][0];

        $this->assertSame( '', $item['edit_url'], 'no edit link when editing is off (wpuf_is_post_editable)' );
        $this->assertFalse( $item['can_delete'] );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/account/posts/' . $post )->get_status(), 'the route refuses too' );
        $this->assertSame( 'publish', get_post_status( $post ) );

        update_option( 'wpuf_dashboard', [ 'enable_post_edit' => 'yes', 'enable_post_del' => 'yes' ] );
        $item = $this->request( 'GET', '/wpuf/v1/account/posts', [ 'type' => 'post' ] )->get_data()['items'][0];

        $this->assertNotSame( '', $item['edit_url'] );
        $this->assertTrue( $item['can_delete'] );
        delete_option( 'wpuf_dashboard' );
    }

    public function test_posts_section_falls_back_to_html_when_a_column_hook_is_attached() {
        $this->user();

        add_action(
            'wpuf_account_posts_head_col',
            function () {
                echo '<th>Extra</th>';
            }
        );

        $sections = $this->request( 'GET', '/wpuf/v1/account' )->get_data()['sections'];
        $kinds    = array_combine( wp_list_pluck( $sections, 'slug' ), wp_list_pluck( $sections, 'kind' ) );

        $this->assertSame( 'html', $kinds['post'] );
    }

    public function test_profile_update_validates_and_saves() {
        $user_id = $this->user();

        $response = $this->request( 'PUT', '/wpuf/v1/account/profile', [ 'last_name' => 'L', 'email' => 'ada@example.com' ] );

        $this->assertSame( 422, $response->get_status() );
        $this->assertSame( 'first_name', $response->get_data()['data']['field'] );

        $response = $this->request( 'PUT', '/wpuf/v1/account/profile', [ 'first_name' => 'Ada', 'last_name' => 'Byron', 'email' => 'ada@example.com' ] );

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( 'Byron', get_user_by( 'id', $user_id )->last_name );
        $this->assertSame( 'ada@example.com', get_user_by( 'id', $user_id )->user_email );
    }

    public function test_password_change_checks_the_current_password() {
        $user_id = $this->user();

        $response = $this->request( 'PUT', '/wpuf/v1/account/password', [ 'current_password' => 'wrong', 'pass1' => 'new-pass-456', 'pass2' => 'new-pass-456' ] );

        $this->assertSame( 422, $response->get_status() );
        $this->assertSame( 'current_password', $response->get_data()['data']['field'] );

        $response = $this->request( 'PUT', '/wpuf/v1/account/password', [ 'current_password' => 'old-pass-123', 'pass1' => 'new-pass-456', 'pass2' => 'new-pass-456' ] );

        $this->assertSame( 200, $response->get_status() );
        $this->assertTrue( wp_check_password( 'new-pass-456', get_user_by( 'id', $user_id )->user_pass, $user_id ) );
    }
}
