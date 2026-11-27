<?php
/**
 * "Use Theme CSS" values -> form style class.
 *
 * @package WP_User_Frontend
 */

/**
 * @covers ::wpuf_form_style_class
 */
class FormStyleClassTest extends WP_UnitTestCase {

    public function test_toggle_values_and_old_classes_map_to_the_style_class() {
        foreach ( [ 'on', 'true', 'yes', '1', true, 'wpuf-theme-style' ] as $value ) {
            $this->assertSame( 'wpuf-theme-style', wpuf_form_style_class( $value ), var_export( $value, true ) );
        }

        foreach ( [ '', 'off', 'false', 'no', '0', false, null, 'wpuf-style', [] ] as $value ) {
            $this->assertSame( 'wpuf-style', wpuf_form_style_class( $value ), var_export( $value, true ) );
        }
    }
}
