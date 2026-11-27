<?php
/**
 * One list contract on every list route (RestController::collection_args()):
 * the forms list and a form's submissions sort on allowlisted columns, an
 * unknown column or a page size over 100 is refused.
 *
 * @package WP_User_Frontend
 */

class RestListArgsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        do_action( 'rest_api_init' );
    }

    private function get( $route, array $params ) {
        $request = new WP_REST_Request( 'GET', $route );

        foreach ( $params as $key => $value ) {
            $request->set_param( $key, $value );
        }

        return rest_get_server()->dispatch( $request );
    }

    public function test_forms_list_sorts_by_title_both_ways() {
        foreach ( [ 'Bravo', 'Alpha', 'Charlie' ] as $title ) {
            self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'QA sort ' . $title ] );
        }

        $titles = function ( $order ) {
            $data = $this->get( '/wpuf/v1/wpuf_form', [ 's' => 'QA sort', 'orderby' => 'title', 'order' => $order ] )->get_data();

            return wp_list_pluck( $data['result'], 'post_title' );
        };

        $this->assertSame( [ 'QA sort Alpha', 'QA sort Bravo', 'QA sort Charlie' ], $titles( 'asc' ) );
        $this->assertSame( [ 'QA sort Charlie', 'QA sort Bravo', 'QA sort Alpha' ], $titles( 'desc' ) );
    }

    public function test_forms_list_default_is_newest_first() {
        $first  = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'QA new one' ] );
        $second = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'QA new two' ] );
        $data   = $this->get( '/wpuf/v1/wpuf_form', [ 's' => 'QA new' ] )->get_data();

        $this->assertSame( [ $second, $first ], array_map( 'intval', wp_list_pluck( $data['result'], 'ID' ) ) );
    }

    public function test_unknown_column_and_big_pages_are_refused() {
        $this->assertSame( 400, $this->get( '/wpuf/v1/wpuf_form', [ 'orderby' => 'post_password' ] )->get_status() );
        $this->assertSame( 400, $this->get( '/wpuf/v1/wpuf_form', [ 'order' => 'sideways' ] )->get_status() );
        $this->assertSame( 400, $this->get( '/wpuf/v1/wpuf_form', [ 'per_page' => 500 ] )->get_status() );
    }

    public function test_submissions_sort_by_title() {
        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        foreach ( [ 'Yankee', 'Xray', 'Zulu' ] as $title ) {
            $post = self::factory()->post->create( [ 'post_status' => 'publish', 'post_title' => $title ] );
            update_post_meta( $post, '_wpuf_form_id', $form );
        }

        $data = $this->get( "/wpuf/v1/wpuf_form/{$form}/submissions", [ 'orderby' => 'title', 'order' => 'asc' ] )->get_data();

        $this->assertSame( [ 'Xray', 'Yankee', 'Zulu' ], wp_list_pluck( $data['items'], 'title' ) );
        $this->assertSame( 400, $this->get( "/wpuf/v1/wpuf_form/{$form}/submissions", [ 'orderby' => 'author' ] )->get_status() );
    }
}
