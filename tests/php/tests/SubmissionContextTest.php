<?php
/**
 * Submission_Context: a REST submission sees the request a classic
 * admin-ajax submission had (posted values, admin-ajax flags, no front-end
 * request), the nonce is checked, handler exits come back as values, and
 * the request is restored afterwards.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Http\Ajax_Abort;
use WeDevs\Wpuf\Platform\Http\Submission_Context;

/**
 * @covers \WeDevs\Wpuf\Platform\Http\Submission_Context
 */
class SubmissionContextTest extends WP_UnitTestCase {

    public function test_runs_with_the_admin_ajax_request_and_restores_it() {
        global $wp;

        $wp->request      = 'wp-json/wpuf/v1/forms/1/submissions';
        $wp->query_string = 'rest_route=/wpuf/v1/forms/1/submissions';
        $_POST            = [ 'before' => 'x' ];

        $this->assertFalse( is_admin() );
        $this->assertFalse( wp_doing_ajax() );

        $seen = Submission_Context::run(
            [ 'post_title' => "It's here" ],
            function () {
                global $wp;

                return [ is_admin(), wp_doing_ajax(), $wp->request, $wp->query_string, home_url( $wp->request ), $_POST['post_title'], isset( $_POST['before'] ) ];
            },
            [ 'nonce' => false ]
        );

        $this->assertSame( [ true, true, '', '', home_url(), "It\\'s here", false ], $seen, 'inside: admin-ajax flags, no front-end request, slashed posted values only' );
        $this->assertFalse( is_admin(), 'restored' );
        $this->assertFalse( wp_doing_ajax(), 'restored' );
        $this->assertSame( 'wp-json/wpuf/v1/forms/1/submissions', $wp->request );
        $this->assertSame( 'rest_route=/wpuf/v1/forms/1/submissions', $wp->query_string );
        $this->assertSame( [ 'before' => 'x' ], $_POST, 'superglobal restored' );
    }

    public function test_checks_the_form_nonce_from_wpuf_nonce() {
        $handler = function () {
            return 'ran';
        };

        $error = Submission_Context::run( [ 'wpuf_nonce' => 'bad' ], $handler );

        $this->assertWPError( $error );
        $this->assertSame( 'wpuf_submit_expired', $error->get_error_code() );

        wp_set_current_user( self::factory()->user->create() );

        $this->assertSame( 'ran', Submission_Context::run( [ 'wpuf_nonce' => wp_create_nonce( 'wpuf_form_add' ) ], $handler ) );
    }

    public function test_handler_exits_come_back_as_errors() {
        $result = Submission_Context::run(
            [],
            function () {
                Ajax_Abort::send_error( 'Nope', 422 );
            },
            [ 'nonce' => false ]
        );

        $this->assertWPError( $result );
        $this->assertSame( 422, $result->get_error_data()['status'] );
    }

    public function test_restores_the_request_when_the_call_throws() {
        try {
            Submission_Context::run(
                [],
                function () {
                    throw new RuntimeException( 'boom' );
                },
                [ 'nonce' => false ]
            );
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'boom', $e->getMessage() );
        }

        $this->assertFalse( is_admin() );
        $this->assertFalse( wp_doing_ajax() );
    }
}
