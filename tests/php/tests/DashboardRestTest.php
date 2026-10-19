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
}
