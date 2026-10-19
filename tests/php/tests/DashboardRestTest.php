<?php
/**
 * `[wpuf_dashboard]` on React: Dashboard_Service (signed attributes, query,
 * columns, delete rules), the `wpuf/v1/dashboard/posts` routes and the
 * shortcode's mount / classic fallback.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Dashboard\Dashboard_Service;

class DashboardRestTest extends WP_UnitTestCase {

    private $author;

    private $other;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        do_action( 'rest_api_init' );

        // The payments block reads wpuf_transaction.
        ( new \WeDevs\Wpuf\Installer() )->create_tables();

        $this->author = self::factory()->user->create( [ 'role' => 'author' ] );
        $this->other  = self::factory()->user->create( [ 'role' => 'author' ] );
    }

    public function tear_down() {
        delete_option( 'wpuf_dashboard' );
        remove_all_actions( 'wpuf_dashboard_row_col' );

        parent::tear_down();
    }

    private function service() {
        return wpuf()->platform()->get( Dashboard_Service::class );
    }

    private function request( $method, $path, array $params ) {
        $request = new WP_REST_Request( $method, $path );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    public function test_page_lists_only_the_users_posts_with_the_shortcode_columns() {
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Mine' ] );
        self::factory()->post->create( [ 'post_author' => $this->other, 'post_title' => 'Theirs' ] );
        wp_set_current_user( $this->author );

        $service = $this->service();
        $page    = $service->page( $service->attributes( [ 'category' => 'on', 'excerpt' => 'on' ] ) );

        $this->assertSame( [ 'Mine' ], wp_list_pluck( $page['items'], 'title' ) );
        $this->assertTrue( $page['columns']['category'] );
        $this->assertTrue( $page['columns']['excerpt'] );
        $this->assertArrayHasKey( 'terms', $page['items'][0] );
        $this->assertSame( 1, $page['total'] );
    }

    public function test_routes_answer_with_the_rendered_signature_only() {
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Mine' ] );
        self::factory()->post->create( [ 'post_author' => $this->other, 'post_title' => 'Their draft', 'post_status' => 'draft' ] );
        wp_set_current_user( $this->author );

        $signed = $this->service()->sign( $this->service()->attributes( [] ) );
        $ok     = $this->request( 'GET', '/wpuf/v1/dashboard/posts', $signed );

        $this->assertSame( 200, $ok->get_status() );
        $this->assertSame( [ 'Mine' ], wp_list_pluck( $ok->get_data()['items'], 'title' ) );

        // Attributes changed in the browser (another author's drafts): refused.
        $tampered = $this->request(
            'GET', '/wpuf/v1/dashboard/posts', [
                'atts' => wp_json_encode( array_merge( json_decode( $signed['atts'], true ), [ 'author__in' => get_userdata( $this->other )->user_nicename ] ) ),
                'sig'  => $signed['sig'],
            ]
        );

        $this->assertSame( 400, $tampered->get_status() );

        // The same signed attributes sent by another user: refused.
        wp_set_current_user( $this->other );

        $this->assertSame( 400, $this->request( 'GET', '/wpuf/v1/dashboard/posts', $signed )->get_status() );

        // Visitors: 401.
        wp_set_current_user( 0 );

        $this->assertSame( 401, $this->request( 'GET', '/wpuf/v1/dashboard/posts', $signed )->get_status() );
    }

    public function test_delete_follows_the_setting_the_author_and_the_listed_post_types() {
        $mine   = self::factory()->post->create( [ 'post_author' => $this->author ] );
        $theirs = self::factory()->post->create( [ 'post_author' => $this->other ] );
        $page   = self::factory()->post->create( [ 'post_author' => $this->author, 'post_type' => 'page' ] );
        wp_set_current_user( $this->author );

        $signed = $this->service()->sign( $this->service()->attributes( [] ) );

        update_option( 'wpuf_dashboard', [ 'enable_post_del' => 'no' ] );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/dashboard/posts/' . $mine, $signed )->get_status() );
        $this->assertSame( 'publish', get_post_status( $mine ) );

        update_option( 'wpuf_dashboard', [ 'enable_post_del' => 'yes' ] );
        $this->assertSame( 403, $this->request( 'DELETE', '/wpuf/v1/dashboard/posts/' . $theirs, $signed )->get_status() );
        $this->assertSame( 404, $this->request( 'DELETE', '/wpuf/v1/dashboard/posts/' . $page, $signed )->get_status(), 'a page is not in a post list' );
        $this->assertSame( 200, $this->request( 'DELETE', '/wpuf/v1/dashboard/posts/' . $mine, $signed )->get_status() );
        $this->assertSame( 'trash', get_post_status( $mine ) );
        $this->assertSame( 'publish', get_post_status( $theirs ) );
    }

    public function test_shortcode_mounts_react_and_keeps_classic_for_printing_hooks() {
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Mine' ] );
        wp_set_current_user( $this->author );

        $renderer = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Renderer::class );

        if ( ! $renderer->mounts( 'dashboard' ) ) {
            $this->markTestSkipped( 'The dashboard bundle is not built.' );
        }

        $fired = 0;

        add_action(
            'wpuf_dashboard_shortcode_init',
            function () use ( &$fired ) {
                $fired++;
            }
        );

        $html = do_shortcode( '[wpuf_dashboard]' );

        $this->assertStringContainsString( 'data-wpuf-mount="dashboard"', $html );
        $this->assertStringContainsString( 'Mine', $html, 'the first page is in the boot data' );
        $this->assertSame( 1, $fired );

        add_action(
            'wpuf_dashboard_row_col',
            function () {
                echo '<td>extra</td>';
            }
        );

        $classic = do_shortcode( '[wpuf_dashboard]' );

        $this->assertStringNotContainsString( 'data-wpuf-mount="dashboard"', $classic );
        $this->assertStringContainsString( '<td>extra</td>', $classic );
        $this->assertSame( 2, $fired );
    }

    public function test_member_filters_only_narrow_the_owners_list() {
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Live one' ] );
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Draft one', 'post_status' => 'draft' ] );
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_title' => 'Gone', 'post_status' => 'trash' ] );
        wp_set_current_user( $this->author );

        $signed = $this->service()->sign( $this->service()->attributes( [ 'post_status' => 'publish,draft' ] ) );
        $titles = function ( array $params ) use ( $signed ) {
            return wp_list_pluck( $this->request( 'GET', '/wpuf/v1/dashboard/posts', array_merge( $signed, $params ) )->get_data()['items'], 'title' );
        };

        $this->assertSame( [ 'Draft one' ], $titles( [ 'status' => 'draft' ] ) );
        $this->assertSame( [ 'Live one' ], $titles( [ 'search' => 'Live' ] ) );
        // A status or type outside the owner's list is dropped, not obeyed.
        $this->assertNotContains( 'Gone', $titles( [ 'status' => 'trash' ] ) );
        $this->assertCount( 2, $titles( [ 'type' => 'page' ] ) );
    }

    public function test_overview_blocks_follow_their_settings() {
        self::factory()->post->create( [ 'post_author' => $this->author ] );
        self::factory()->post->create( [ 'post_author' => $this->author, 'post_status' => 'draft' ] );
        wp_set_current_user( $this->author );

        $overview = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Dashboard\Dashboard_Overview::class );
        $atts     = $this->service()->attributes( [] );
        $data     = $overview->build( $atts );
        $counts   = wp_list_pluck( $data['stats'], 'count', 'status' );

        $this->assertSame( 1, $counts['publish'] );
        $this->assertSame( 1, $counts['draft'] );
        $this->assertNotEmpty( $data['profile']['avatar']['initials'], 'the account hero data' );
        $this->assertIsArray( $data['comments'] );

        update_option(
            'wpuf_dashboard', [
                'dashboard_status_cards'    => 'off',
                'dashboard_table_filters'   => 'off',
                'dashboard_recent_comments' => 'off',
                'dashboard_quick_actions'   => 'off',
                'dashboard_attention'       => 'off',
            ]
        );
        update_option( 'wpuf_payment', [ 'enable_payment' => 'off' ] );

        $off = $overview->build( $atts );

        $this->assertSame( [], $off['stats'] );
        $this->assertNull( $off['comments'] );
        $this->assertSame( [], $off['actions'] );
        $this->assertSame( [], $off['attention'] );
        $this->assertNull( $off['plan'], 'no plan card without payments' );
        $this->assertNull( $off['payments'] );

        delete_option( 'wpuf_payment' );
    }

    public function test_plan_messages_only_when_posting_needs_a_plan() {
        $free = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        $paid = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        update_post_meta( $free, 'wpuf_form_settings', [ 'post_type' => 'post' ] );
        update_post_meta(
            $paid, 'wpuf_form_settings', [
                'post_type'             => 'post',
                'payment_options'       => 'true',
                'choose_payment_option' => 'force_pack_purchase',
            ]
        );

        $post = self::factory()->post->create( [ 'post_author' => $this->author ] );
        update_post_meta( $post, '_wpuf_form_id', $free );
        wp_set_current_user( $this->author );

        $overview = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Dashboard\Dashboard_Overview::class );
        $atts     = $this->service()->attributes( [] );
        $keys     = function () use ( $overview, $atts ) {
            return wp_list_pluck( $overview->attention( $atts, [ 'state' => 'none', 'packs_url' => 'http://example.org/plans/' ] ), 'key' );
        };

        $this->assertNotContains( 'plan_needed', $keys(), 'a free form needs no plan' );

        update_post_meta( $post, '_wpuf_form_id', $paid );
        $this->assertContains( 'plan_needed', $keys(), 'a mandatory subscription form does' );

        $settings                        = get_post_meta( $paid, 'wpuf_form_settings', true );
        $settings['fallback_ppp_enable'] = 'true';
        update_post_meta( $paid, 'wpuf_form_settings', $settings );
        $this->assertNotContains( 'plan_needed', $keys(), 'pay per post as a fallback: no plan needed' );

        $settings['fallback_ppp_enable'] = 'false';
        $settings['post_permission']     = 'role_base';
        $settings['roles']               = [ 'administrator', 'subscriber' ];
        update_post_meta( $paid, 'wpuf_form_settings', $settings );
        $this->assertNotContains( 'plan_needed', $keys(), 'a form the role may not use does not count' );

        self::factory()->post->create( [ 'post_author' => $this->author, 'post_status' => 'draft' ] );
        $this->assertContains( 'drafts', $keys() );
    }
}
