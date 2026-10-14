<?php
/**
 * Admin_Ajax_Context: a REST submission sees the request a classic
 * admin-ajax submission had, and the request is restored afterwards.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Http\Admin_Ajax_Context;

/**
 * @covers \WeDevs\Wpuf\Platform\Http\Admin_Ajax_Context
 */
class AdminAjaxContextTest extends WP_UnitTestCase {

    public function test_runs_with_the_admin_ajax_flags_and_restores_them() {
        global $wp;

        $wp->request      = 'wp-json/wpuf/v1/forms/1/submissions';
        $wp->query_string = 'rest_route=/wpuf/v1/forms/1/submissions';

        $this->assertFalse( is_admin() );
        $this->assertFalse( wp_doing_ajax() );

        $seen = Admin_Ajax_Context::run(
            function () {
                global $wp;

                return [ is_admin(), wp_doing_ajax(), $wp->request, $wp->query_string, home_url( $wp->request ) ];
            }
        );

        $this->assertSame( [ true, true, '', '', home_url() ], $seen, 'inside: admin-ajax flags, no front-end request' );
        $this->assertFalse( is_admin(), 'restored' );
        $this->assertFalse( wp_doing_ajax(), 'restored' );
        $this->assertSame( 'wp-json/wpuf/v1/forms/1/submissions', $wp->request );
        $this->assertSame( 'rest_route=/wpuf/v1/forms/1/submissions', $wp->query_string );
    }

    public function test_restores_the_request_when_the_call_throws() {
        try {
            Admin_Ajax_Context::run(
                function () {
                    throw new RuntimeException( 'boom' );
                }
            );
        } catch ( RuntimeException $e ) {
            $this->assertSame( 'boom', $e->getMessage() );
        }

        $this->assertFalse( is_admin() );
        $this->assertFalse( wp_doing_ajax() );
    }
}
