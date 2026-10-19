<?php
/**
 * The default guest verification mail signs with the site name and prints
 * nothing (it echoed the name into the submit response before).
 *
 * @package WP_User_Frontend
 */

class GuestMailSiteNameTest extends WP_UnitTestCase {

    public function tear_down() {
        unset( $_POST['guest_email'] );

        parent::tear_down();
    }

    public function test_default_guest_mail_is_signed_and_prints_nothing() {
        $sent = null;

        update_option( 'blogname', 'QA Shop' );
        $_POST['guest_email'] = 'guest@example.test';

        add_filter(
            'pre_wp_mail', function ( $return, $atts ) use ( &$sent ) {
                $sent = $atts;

                return true;
            }, 10, 2
        );

        ob_start();
        wpuf_send_mail_to_guest( 'p', 'f', 'no', 2 );
        $printed = ob_get_clean();

        $this->assertSame( '', $printed );
        $this->assertNotNull( $sent );
        $this->assertStringContainsString( 'QA Shop', $sent['message'] );
    }
}
