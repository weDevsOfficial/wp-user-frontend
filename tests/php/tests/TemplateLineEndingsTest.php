<?php
/**
 * The form templates whose multi-line strings are copied into a new form's
 * settings keep develop's CRLF bytes (pinned `-text` in Pro's .gitattributes):
 * an LF file makes a form created from the template store "\n" where develop
 * stores "\r\n" (notification bodies, descriptions, messages).
 *
 * @package WP_User_Frontend
 */
class TemplateLineEndingsTest extends WP_UnitTestCase {

    public function test_pinned_templates_keep_crlf() {
        if ( ! defined( 'WPUF_PRO_ROOT' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $root       = rtrim( WPUF_PRO_ROOT, '/' ) . '/';
        $attributes = (string) file_get_contents( $root . '.gitattributes' );

        preg_match_all( '/^(\S*Template\S*\.php)\s+-text$/m', $attributes, $matches );

        $this->assertNotEmpty( $matches[1], 'Pro pins its form templates with -text' );

        foreach ( $matches[1] as $file ) {
            $bytes = (string) file_get_contents( $root . $file );

            $this->assertStringContainsString( "\r\n", $bytes, $file . ' keeps develop\'s CRLF' );
            $this->assertSame( 0, preg_match( "/(?<!\r)\n/", $bytes ), $file . ' has no bare LF' );
        }
    }
}
