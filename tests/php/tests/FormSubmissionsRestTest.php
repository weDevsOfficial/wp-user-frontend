<?php
/**
 * A post form's Submissions page data: wpuf/v1/wpuf_form/<id>/submissions
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Platform\REST\Controllers\FormListController::get_submissions
 */
class FormSubmissionsRestTest extends WP_UnitTestCase {

    /**
     * REST server for the test.
     *
     * @var WP_REST_Server
     */
    private $server;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        $this->server   = $wp_rest_server;
        do_action( 'rest_api_init' );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    /**
     * A post submitted through a form.
     */
    private function submission( $form_id, $status, $title ) {
        $id = self::factory()->post->create(
            [
                'post_title'  => $title,
                'post_status' => $status,
            ]
        );
        update_post_meta( $id, '_wpuf_form_id', $form_id );

        return $id;
    }

    private function get( $form_id, array $params = [] ) {
        $request = new WP_REST_Request( 'GET', '/wpuf/v1/wpuf_form/' . $form_id . '/submissions' );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    public function test_route_is_registered() {
        $this->assertArrayHasKey( '/wpuf/v1/wpuf_form/(?P<id>[\d]+)/submissions', $this->server->get_routes() );
    }

    public function test_lists_only_the_form_posts_with_counts_and_columns() {
        $form  = wpuf_create_sample_form( 'Subs Form', 'wpuf_forms' );
        $other = wpuf_create_sample_form( 'Other Form', 'wpuf_forms' );

        $this->submission( $form, 'publish', 'Sub A' );
        $this->submission( $form, 'pending', 'Sub B' );
        $this->submission( $form, 'pending', 'Sub C' );
        $this->submission( $form, 'draft', 'Sub D' );
        $this->submission( $other, 'publish', 'Not mine' );
        self::factory()->post->create( [ 'post_title' => 'No form' ] );

        $data = $this->get( $form )->get_data();

        $this->assertSame( 4, $data['total'] );
        $this->assertSame( [ 'any' => 4, 'publish' => 1, 'pending' => 2, 'draft' => 1, 'future' => 0, 'private' => 0 ], $data['counts'] );
        $this->assertNotContains( 'Not mine', wp_list_pluck( $data['items'], 'title' ) );
        $this->assertSame( 'Subs Form', $data['form']['title'] );
        $this->assertSame( [ 'category', 'post_tag' ], wp_list_pluck( $data['columns'], 'key' ) );
        $this->assertTrue( $data['comments'] );

        $item = $data['items'][0];
        $this->assertArrayHasKey( 'category', $item['terms'] );
        $this->assertNotEmpty( $item['edit_url'] );
    }

    public function test_status_search_and_paging() {
        $form = wpuf_create_sample_form( 'Subs Form', 'wpuf_forms' );

        foreach ( range( 1, 12 ) as $i ) {
            $this->submission( $form, 'publish', 'Paged ' . $i );
        }
        $this->submission( $form, 'pending', 'Waiting one' );

        $pending = $this->get( $form, [ 'status' => 'pending' ] )->get_data();
        $this->assertSame( [ 'Waiting one' ], wp_list_pluck( $pending['items'], 'title' ) );

        $page2 = $this->get( $form, [ 'page' => 2 ] )->get_data();
        $this->assertCount( 3, $page2['items'] );
        $this->assertSame( 2, $page2['pages'] );

        $search = $this->get( $form, [ 's' => 'Waiting' ] )->get_data();
        $this->assertSame( 1, $search['total'] );
        $this->assertSame( 1, $search['counts']['any'] );

        // An unknown status falls back to all.
        $this->assertSame( 13, $this->get( $form, [ 'status' => 'bogus' ] )->get_data()['total'] );
    }

    public function test_extra_columns_and_row_values_come_from_the_filters() {
        $form = wpuf_create_sample_form( 'Subs Form', 'wpuf_forms' );
        $post = $this->submission( $form, 'publish', 'Reviewed' );

        $columns = function ( $columns, $form_id ) use ( $form ) {
            if ( $form === $form_id ) {
                $columns[] = [ 'key' => 'ai_review', 'label' => 'AI Review' ];
            }

            return $columns;
        };
        $value   = function ( $item ) {
            $item['extra']['ai_review'] = [ 'text' => 'Full Match', 'tone' => 'green', 'title' => 'Good' ];

            return $item;
        };

        add_filter( 'wpuf_form_submissions_columns', $columns, 10, 2 );
        add_filter( 'wpuf_form_submissions_item', $value );

        $data = $this->get( $form )->get_data();

        $this->assertSame( [ [ 'key' => 'ai_review', 'label' => 'AI Review' ] ], $data['extra_columns'] );
        $this->assertSame( $post, $data['items'][0]['id'] );
        $this->assertSame( 'Full Match', $data['items'][0]['extra']['ai_review']['text'] );

        remove_filter( 'wpuf_form_submissions_columns', $columns, 10 );
        remove_filter( 'wpuf_form_submissions_item', $value );

        $this->assertSame( [], $this->get( $form )->get_data()['extra_columns'] );
    }

    public function test_rejects_lower_roles_and_non_post_forms() {
        $form = wpuf_create_sample_form( 'Subs Form', 'wpuf_forms' );

        $this->assertSame( 404, $this->get( self::factory()->post->create() )->get_status() );
        $this->assertSame( 404, $this->get( wpuf_create_sample_form( 'Reg', 'wpuf_profile' ) )->get_status() );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $this->assertContains( $this->get( $form )->get_status(), [ 401, 403 ] );

        wp_set_current_user( 0 );
        $this->assertContains( $this->get( $form )->get_status(), [ 401, 403 ] );
    }
}
