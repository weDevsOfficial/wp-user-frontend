<?php
/**
 * Settings > General > Custom CSS codes reaches the page head.
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Frontend::print_custom_css
 */
class FrontendCustomCssTest extends WP_UnitTestCase {

    private function head() {
        ob_start();
        ( new \WeDevs\Wpuf\Frontend() )->print_custom_css();

        return ob_get_clean();
    }

    public function test_prints_the_css_in_a_style_tag_only_when_set() {
        delete_option( 'wpuf_general' );
        $this->assertSame( '', $this->head(), 'nothing without a value' );

        update_option( 'wpuf_general', [ 'custom_css' => '.wpuf-form-add { color: #123456; }' ] );
        $html = $this->head();

        $this->assertStringContainsString( '<style id="wpuf-custom-css">', $html );
        $this->assertStringContainsString( '.wpuf-form-add { color: #123456; }', $html );
    }

    public function test_cannot_close_the_style_tag() {
        update_option( 'wpuf_general', [ 'custom_css' => 'a{color:red}</style><script>alert(1)</script>' ] );
        $html = $this->head();

        $this->assertSame( 1, substr_count( $html, '</style>' ), 'only the closing tag of the block' );
        $this->assertStringNotContainsString( '<script', $html );
        delete_option( 'wpuf_general' );
    }
}
