<?php
/**
 * Stray output guard for REST and AJAX JSON answers
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Http\JsonOutputGuard;

/**
 * JsonOutputGuard: route and action matching, JSON tail detection,
 * buffer release for REST and AJAX.
 */
class JsonOutputGuardTest extends WP_UnitTestCase {

    /**
     * The guard only watches wpuf/v1 routes and wpuf_* AJAX actions.
     */
    public function test_only_plugin_routes_and_actions_are_guarded() {
        $guard = new JsonOutputGuard();

        $this->assertTrue( $guard->is_guarded_rest_route( '/wpuf/v1/forms' ) );
        $this->assertTrue( $guard->is_guarded_rest_route( 'wpuf/v1/admin/transactions?per_page=1' ) );
        $this->assertFalse( $guard->is_guarded_rest_route( '/wp/v2/posts' ) );
        $this->assertFalse( $guard->is_guarded_rest_route( '/wpuf/v10/forms' ) );
        $this->assertFalse( $guard->is_guarded_rest_route( '' ) );

        $this->assertTrue( $guard->is_guarded_ajax_action( 'wpuf_form_search' ) );
        $this->assertTrue( $guard->is_guarded_ajax_action( 'wpuf-get-forms' ) );
        $this->assertFalse( $guard->is_guarded_ajax_action( 'heartbeat' ) );
        $this->assertFalse( $guard->is_guarded_ajax_action( '' ) );
    }

    /**
     * Namespaces and prefixes are filterable.
     */
    public function test_namespaces_and_prefixes_are_filterable() {
        $guard = new JsonOutputGuard();

        add_filter( 'wpuf_json_output_guard_rest_namespaces', function ( $namespaces ) {
            $namespaces[] = 'wpuf-pro/v1';

            return $namespaces;
        } );
        add_filter( 'wpuf_json_output_guard_ajax_prefixes', function ( $prefixes ) {
            $prefixes[] = 'wpufpro_';

            return $prefixes;
        } );

        $this->assertTrue( $guard->is_guarded_rest_route( '/wpuf-pro/v1/x' ) );
        $this->assertTrue( $guard->is_guarded_ajax_action( 'wpufpro_x' ) );

        remove_all_filters( 'wpuf_json_output_guard_rest_namespaces' );
        remove_all_filters( 'wpuf_json_output_guard_ajax_prefixes' );
    }

    /**
     * The request kind comes from rest_route, the REST URL prefix, or the AJAX action.
     */
    public function test_request_kind_is_read_from_the_request() {
        $guard = new JsonOutputGuard();
        $uri   = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';

        $this->assertSame( '', $guard->request_kind() );

        $_SERVER['REQUEST_URI'] = '/wp-json/wpuf/v1/forms?page=2';
        $this->assertSame( 'rest', $guard->request_kind() );

        $_SERVER['REQUEST_URI'] = '/wp-json/wp/v2/posts';
        $this->assertSame( '', $guard->request_kind() );

        $_SERVER['REQUEST_URI'] = '/index.php';
        $_GET['rest_route']     = '/wpuf/v1/subscriptions';
        $this->assertSame( 'rest', $guard->request_kind() );
        unset( $_GET['rest_route'] );
        $_SERVER['REQUEST_URI'] = $uri;
    }

    /**
     * json_tail(): untouched JSON, noise + JSON, and non-JSON answers.
     */
    public function test_json_tail_finds_the_document_after_stray_output() {
        $json = '{"success":true,"data":{"items":[1,2],"note":"a { brace } inside"}}';

        $this->assertSame( $json, JsonOutputGuard::json_tail( $json ) );
        $this->assertSame( $json . "\n", JsonOutputGuard::json_tail( $json . "\n" ) );
        $this->assertSame( $json, JsonOutputGuard::json_tail( "\nDeprecated: foo() in [file.php] on line 5\n<br />{\"x\"\n" . $json ) );
        $this->assertSame( '[1,2,3]', JsonOutputGuard::json_tail( '<b>Warning</b>: bar[1,2,3]' ) );

        $this->assertNull( JsonOutputGuard::json_tail( '' ) );
        $this->assertNull( JsonOutputGuard::json_tail( "   \n" ) );
        $this->assertNull( JsonOutputGuard::json_tail( '-1' ) );
        $this->assertNull( JsonOutputGuard::json_tail( '<option value="1">Child</option>' ) );
        $this->assertNull( JsonOutputGuard::json_tail( 'Notice: x {"unterminated": tr' ) );
    }

    /**
     * REST: what was printed before the answer is dropped; buffers opened above are folded in.
     */
    public function test_rest_answer_drops_everything_buffered_before_it() {
        $guard = new JsonOutputGuard();

        ob_start();
        $level = ob_get_level();

        $this->assertTrue( $guard->start() );
        $this->assertTrue( $guard->is_buffering() );
        echo "\nDeprecated: other plugin\n";
        ob_start();
        echo 'inner';

        $served = $guard->drop_before_rest_answer( false, null, new WP_REST_Request( 'GET', '/wpuf/v1/forms' ), null );

        $this->assertFalse( $served );
        $this->assertFalse( $guard->is_buffering() );
        $this->assertSame( $level, ob_get_level() );
        $this->assertSame( '', ob_get_clean() );
    }

    /**
     * REST: a route outside the namespace is printed as it was.
     */
    public function test_rest_answer_for_another_route_is_left_alone() {
        $guard = new JsonOutputGuard();

        ob_start();
        $guard->start();
        echo 'kept';
        $guard->drop_before_rest_answer( false, null, new WP_REST_Request( 'GET', '/wp/v2/posts' ), null );
        $printed = ob_get_clean();

        $this->assertSame( 'kept', $printed );
    }

    /**
     * AJAX: the wrapped die handler prints only the JSON, then runs the original handler.
     */
    public function test_ajax_die_handler_prints_only_the_json() {
        $guard = new JsonOutputGuard();
        $calls = [];
        $inner = function ( $message, $title, $args ) use ( &$calls ) {
            $calls[] = [ $message, $title, $args ];
        };

        $handler = $guard->wrap_ajax_die_handler( $inner );

        ob_start();
        $guard->start();
        echo "Notice: stray\n";
        echo '{"success":true}';
        $handler( '', '', [ 'response' => null ] );
        $printed = ob_get_clean();

        $this->assertSame( '{"success":true}', $printed );
        $this->assertSame( [ [ '', '', [ 'response' => null ] ] ], $calls );
    }

    /**
     * AJAX: HTML partials, plain text and -1 answers are printed untouched.
     */
    public function test_ajax_non_json_answers_are_printed_as_they_were() {
        foreach ( [ "Notice: stray\n<option>1</option>", '-1', "Notice: x\n0" ] as $body ) {
            $guard   = new JsonOutputGuard();
            $handler = $guard->wrap_ajax_die_handler( '__return_null' );

            ob_start();
            $guard->start();
            echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $handler( '', '', [] );
            $this->assertSame( $body, ob_get_clean() );
        }
    }

    /**
     * The guard is a shared platform service hooked at boot.
     */
    public function test_guard_is_a_platform_service() {
        $this->assertTrue( wpuf()->platform()->is_shared( JsonOutputGuard::class ) );
        $this->assertSame( wpuf()->platform()->get( JsonOutputGuard::class ), wpuf()->platform()->get( JsonOutputGuard::class ) );
    }
}
