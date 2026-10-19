<?php
/**
 * `[wpuf_edit]` returns its markup and leaves no output buffer open: the
 * guest login form and the early messages were printed / left in two open
 * buffers, which on block themes put the login form before <!DOCTYPE html>.
 *
 * @package WP_User_Frontend
 */

class EditShortcodeOutputTest extends WP_UnitTestCase {

    private function render() {
        $level = ob_get_level();

        ob_start();
        $html    = do_shortcode( '[wpuf_edit]' );
        $printed = ob_get_clean();

        return [ $html, $printed, ob_get_level() - $level ];
    }

    public function test_guest_gets_the_login_form_returned_not_printed() {
        wp_set_current_user( 0 );

        [ $html, $printed, $open ] = $this->render();

        $this->assertStringContainsString( 'You are not logged in', $html );
        $this->assertStringContainsString( 'loginform', $html );
        $this->assertSame( '', $printed );
        $this->assertSame( 0, $open, 'no buffer left open' );
    }

    public function test_early_messages_leave_no_buffer_open() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

        [ $html, $printed, $open ] = $this->render();

        $this->assertStringContainsString( 'Please re-open the post', $html );
        $this->assertSame( '', $printed );
        $this->assertSame( 0, $open );
    }
}
