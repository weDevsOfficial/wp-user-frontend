<?php
/**
 * The REST routes the classic scripts call instead of admin-ajax: the Login
 * widget, the billing address, the tag suggest, the post lock, the admin
 * notices, the free modules switch and the user pack removal. Permissions,
 * nonces, payloads, and that the admin-ajax actions are gone (except the ones
 * old theme template copies still post).
 *
 * @package WP_User_Frontend
 */

class ClassicRestRoutesTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();

        do_action( 'rest_api_init' );
        add_filter( 'wpuf_rate_limit', '__return_zero' );
    }

    public function tear_down() {
        remove_filter( 'wpuf_rate_limit', '__return_zero' );
        parent::tear_down();
    }

    private function request( $method, $path, array $params = [] ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    private function admin() {
        $user = self::factory()->user->create( [ 'role' => 'administrator' ] );

        if ( is_multisite() ) {
            grant_super_admin( $user );
        }

        wp_set_current_user( $user );

        return $user;
    }

    public function test_the_routes_are_registered() {
        $routes = rest_get_server()->get_routes();

        foreach ( [
            '/wpuf/v1/auth/login',
            '/wpuf/v1/auth/lost-password',
            '/wpuf/v1/auth/logout',
            '/wpuf/v1/account/billing-address',
            '/wpuf/v1/account/billing-address/states',
            '/wpuf/v1/terms/suggest',
            '/wpuf/v1/admin/posts/(?P<id>\d+)/lock',
            '/wpuf/v1/admin/notices/(?P<notice>review|promotion|paypal|acf)/dismiss',
            '/wpuf/v1/admin/integrations/acf/(?P<task>compatible|migrate)',
            '/wpuf/v1/admin/free-modules/(?P<module>[a-z0-9_-]+)',
            '/wpuf/v1/admin/users/(?P<user_id>\d+)/pack',
            '/wpuf/v1/forms/(?P<id>\d+)/child-terms',
        ] as $route ) {
            $this->assertArrayHasKey( $route, $routes, $route );
        }
    }

    public function test_the_ported_admin_ajax_actions_are_gone_and_the_template_ones_stay() {
        new \WeDevs\Wpuf\Ajax();

        foreach ( [
            'wpuf_submit_post', 'wpuf_draft_post', 'wpuf_upload_file', 'wpuf_file_del', 'wpuf_insert_image',
            'wpuf_make_media_embed_code', 'wpuf_get_child_cat', 'wpuf_ajax_tag_search', 'wpuf_ajax_login',
            'wpuf_lost_password', 'wpuf_ajax_logout', 'wpuf_ajax_address', 'wpuf_update_billing_address',
            'wpuf_address_ajax_action', 'wpuf_delete_user_package', 'wpuf_clear_schedule_lock',
            'wpuf_dismiss_review_notice', 'wpuf_dismiss_promotional_offer_notice', 'wpuf_dismiss_paypal_notice',
            'wpuf_dismiss_notice_acf', 'wpuf_compatibility_acf', 'wpuf_migrate_acf', 'wpuf_toggle_free_module',
        ] as $action ) {
            $this->assertFalse( has_action( 'wp_ajax_' . $action ), $action );
            $this->assertFalse( has_action( 'wp_ajax_nopriv_' . $action ), $action );
        }

        // Old copies of the account templates in a theme post these.
        $this->assertNotFalse( has_action( 'wp_ajax_wpuf_account_update_profile' ) );
        $this->assertNotFalse( has_action( 'wp_ajax_wpuf_account_change_password' ) );
    }

    public function test_widget_login_is_for_visitors_with_the_widget_nonce() {
        $user = self::factory()->user->create(
            [
                'user_login' => 'widget_user',
                'user_pass'  => 'secret-pass-1',
            ]
        );

        wp_set_current_user( 0 );
        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/auth/login', [ 'wpuf_nonce' => 'nope' ] )->get_status() );

        $nonce = wp_create_nonce( 'wpuf_lost_pass' );

        $empty = $this->request( 'POST', '/wpuf/v1/auth/login', [ 'wpuf_nonce' => $nonce ] );
        $this->assertSame( 400, $empty->get_status() );
        $this->assertSame( 'wpuf_login_empty', $empty->get_data()['code'] );

        $wrong = $this->request( 'POST', '/wpuf/v1/auth/login', [ 'wpuf_nonce' => $nonce, 'log' => 'widget_user', 'pwd' => 'bad' ] );
        $this->assertSame( 403, $wrong->get_status() );
        $this->assertSame( 'wpuf_login_failed', $wrong->get_data()['code'] );

        $fired = 0;
        $count = function () use ( &$fired ) {
            ++$fired;
        };
        add_action( 'wp_login', $count );

        $ok = $this->request( 'POST', '/wpuf/v1/auth/login', [ 'wpuf_nonce' => $nonce, 'log' => 'widget_user', 'pwd' => 'secret-pass-1' ] );
        remove_action( 'wp_login', $count );

        $this->assertSame( 200, $ok->get_status(), wp_json_encode( $ok->get_data() ) );
        $this->assertSame( 1, $fired, 'wp_login fires' );

        wp_set_current_user( $user );
        $this->assertSame( 400, $this->request( 'POST', '/wpuf/v1/auth/login', [ 'wpuf_nonce' => wp_create_nonce( 'wpuf_lost_pass' ) ] )->get_status(), 'logged in already' );
    }

    public function test_widget_lost_password_sends_the_mail_and_answers_errors_as_html() {
        self::factory()->user->create(
            [
                'user_login' => 'lost_user',
                'user_email' => 'lost@example.org',
            ]
        );

        wp_set_current_user( 0 );
        reset_phpmailer_instance();

        $nonce   = wp_create_nonce( 'wpuf_lost_pass' );
        $unknown = $this->request( 'POST', '/wpuf/v1/auth/lost-password', [ 'wpuf_nonce' => $nonce, 'user_login' => 'nobody@example.org' ] );

        $this->assertSame( 400, $unknown->get_status() );
        $this->assertStringContainsString( '<p>', $unknown->get_data()['message'] );

        $sent = $this->request( 'POST', '/wpuf/v1/auth/lost-password', [ 'wpuf_nonce' => $nonce, 'user_login' => 'lost_user' ] );

        $this->assertSame( 200, $sent->get_status() );
        $this->assertSame( 'lost@example.org', tests_retrieve_phpmailer_instance()->get_recipient( 'to' )->address );
    }

    public function test_widget_logout_needs_a_logged_in_user() {
        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->request( 'POST', '/wpuf/v1/auth/logout' )->get_status() );

        wp_set_current_user( self::factory()->user->create() );
        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/auth/logout' )->get_status() );
        $this->assertSame( 0, get_current_user_id() );
    }

    public function test_billing_address_saves_for_the_current_user_and_asks_the_tax_filter() {
        wp_set_current_user( 0 );
        $this->assertSame( 401, $this->request( 'PUT', '/wpuf/v1/account/billing-address', [ 'billing_country' => 'US' ] )->get_status() );

        $user = self::factory()->user->create();
        wp_set_current_user( $user );

        $seen   = null;
        $filter = function ( $response, $post_data ) use ( &$seen ) {
            $seen = $post_data;

            return [
                'tax'  => '10%',
                'cost' => '$11.00',
            ];
        };
        add_filter( 'wpuf_billing_tax_response', $filter, 10, 2 );

        $response = $this->request(
            'PUT', '/wpuf/v1/account/billing-address', [
                'billing_country' => 'US',
                'billing_state'   => 'CA',
                'billing_city'    => 'Fresno',
                'type'            => 'pack',
                'id'              => '7',
            ]
        );
        remove_filter( 'wpuf_billing_tax_response', $filter, 10 );

        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( '10%', $response->get_data()['tax'] );
        $this->assertSame( 'pack', $seen['type'] );
        $this->assertSame( 'CA', $seen['billing_state'] );

        $stored = get_user_meta( $user, 'wpuf_address_fields', true );
        $this->assertSame( 'Fresno', $stored['city'] );
        $this->assertSame( 'US', $stored['country'] );
    }

    public function test_billing_states_answer_the_state_field() {
        wp_set_current_user( self::factory()->user->create() );

        $with = $this->request( 'GET', '/wpuf/v1/account/billing-address/states', [ 'country' => 'US', 'field_name' => 'wpuf_biiling_state' ] )->get_data();
        $this->assertTrue( $with['states'] );
        $this->assertStringContainsString( '<select', $with['html'] );
        $this->assertStringContainsString( 'name="wpuf_biiling_state"', $with['html'] );

        $none = $this->request( 'GET', '/wpuf/v1/account/billing-address/states', [ 'country' => 'XX', 'field_name' => 'wpuf_biiling_state' ] )->get_data();
        $this->assertFalse( $none['states'] );
    }

    public function test_tag_suggest_answers_plain_text_names() {
        self::factory()->term->create( [ 'taxonomy' => 'post_tag', 'name' => 'Apple pie' ] );
        self::factory()->term->create( [ 'taxonomy' => 'post_tag', 'name' => 'Banana' ] );

        wp_set_current_user( 0 );

        $response = $this->request( 'GET', '/wpuf/v1/terms/suggest', [ 'tax' => 'post_tag', 'q' => 'foo, app' ] );
        $this->assertSame( 200, $response->get_status() );
        $this->assertSame( 'Apple pie', $response->get_data(), 'the last part of the input is matched' );

        $this->assertSame( '', $this->request( 'GET', '/wpuf/v1/terms/suggest', [ 'tax' => 'post_tag', 'q' => 'a' ] )->get_data(), 'two characters at least' );
        $this->assertSame( 400, $this->request( 'GET', '/wpuf/v1/terms/suggest', [ 'tax' => 'nope', 'q' => 'app' ] )->get_status() );

        $url = wpuf_tag_suggest_url( 'post_tag' );
        $this->assertStringContainsString( 'terms', rawurldecode( $url ) );
        $this->assertStringContainsString( 'wpuf/v1/terms/suggest', rawurldecode( $url ) );
        $this->assertStringContainsString( 'tax=post_tag', $url );
    }

    public function test_post_lock_needs_edit_others_posts_and_the_post() {
        $post = self::factory()->post->create();

        update_post_meta( $post, '_wpuf_lock_editing_post', 'yes' );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/admin/posts/' . $post . '/lock' )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/admin/posts/' . $post . '/lock' )->get_status() );
        $this->assertSame( 404, $this->request( 'DELETE', '/wpuf/v1/admin/posts/999999/lock' )->get_status() );
    }

    public function test_notice_dismissals_need_the_admin_role_and_a_known_promotion_key() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/admin/notices/review/dismiss' )->get_status() );

        $this->admin();
        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/admin/notices/review/dismiss' )->get_status() );
        $this->assertSame( 'yes', get_option( 'wpuf_review_notice_dismiss' ) );

        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/admin/notices/paypal/dismiss' )->get_status() );
        $this->assertNotEmpty( get_option( 'wpuf_paypal_settings_notice_dismissed' ) );

        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/admin/notices/acf/dismiss' )->get_status() );
        $this->assertSame( 'yes', get_option( 'wpuf_dismiss_notice_acf' ) );

        set_transient( \WeDevs\Wpuf\Admin\Promotion::PROMO_KEY, wp_json_encode( [ 'key' => 'wpuf_bfcm_2026' ] ), HOUR_IN_SECONDS );

        $this->assertSame( 400, $this->request( 'POST', '/wpuf/v1/admin/notices/promotion/dismiss', [ 'key' => 'siteurl' ] )->get_status(), 'no other option' );
        $this->assertNotSame( 'hide', get_option( 'siteurl' ) );

        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/admin/notices/promotion/dismiss', [ 'key' => 'wpuf_bfcm_2026' ] )->get_status() );
        $this->assertSame( 'hide', get_option( 'wpuf_bfcm_2026' ) );

        $this->assertSame( 200, $this->request( 'POST', '/wpuf/v1/admin/integrations/acf/compatible' )->get_status() );
        $this->assertSame( 'yes', wpuf_get_option( 'wpuf_compatibility_acf', 'wpuf_general' ) );
    }

    public function test_free_module_switch_needs_manage_options_and_a_known_module() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertSame( 403, $this->request( 'POST', '/wpuf/v1/admin/free-modules/anything', [ 'status' => 'active' ] )->get_status() );

        $this->admin();
        $this->assertSame( 404, $this->request( 'POST', '/wpuf/v1/admin/free-modules/no-such-module', [ 'status' => 'active' ] )->get_status() );
        $this->assertSame( 400, $this->request( 'POST', '/wpuf/v1/admin/free-modules/no-such-module', [ 'status' => 'maybe' ] )->get_status() );
    }

    public function test_user_pack_removal_is_for_subscription_managers() {
        $member = self::factory()->user->create();

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/admin/users/' . $member . '/pack' )->get_status() );

        $this->admin();
        $this->assertSame( 404, $this->request( 'DELETE', '/wpuf/v1/admin/users/999999/pack' )->get_status() );

        $response = $this->request( 'DELETE', '/wpuf/v1/admin/users/' . $member . '/pack' );
        $this->assertSame( 200, $response->get_status() );
        $this->assertArrayHasKey( 'deleted', $response->get_data() );
    }

    public function test_classic_scripts_get_the_rest_data() {
        $data = wpuf_rest_script_data();

        $this->assertStringContainsString( 'wpuf/v1/', $data['root'] );
        $this->assertNotEmpty( $data['nonce'] );

        $js = wpuf_rest_post_js( 'admin/notices/review/dismiss', [ 'key' => 'x' ] );
        $this->assertStringStartsWith( 'window.fetch(', $js );
        $this->assertStringContainsString( 'X-WP-Nonce', $js );
    }
}
