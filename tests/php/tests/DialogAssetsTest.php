<?php
/**
 * The WPUF dialog replaces SweetAlert2: registered with its strings, a
 * dependency of the classic scripts and the frontend runtime, and WPUF no
 * longer enqueues SweetAlert2 (the handle stays registered for add-ons).
 *
 * @package WP_User_Frontend
 */

class DialogAssetsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();
    }

    public function test_the_dialog_is_registered_with_its_strings() {
        $this->assertTrue( wp_script_is( 'wpuf-dialog', 'registered' ) );
        $this->assertTrue( wp_style_is( 'wpuf-dialog', 'registered' ) );
        $this->assertStringContainsString( 'wpufDialogL10n', (string) wp_scripts()->get_data( 'wpuf-dialog', 'data' ) );
        $this->assertTrue( wp_script_is( 'wpuf-sweetalert2', 'registered' ), 'kept for add-ons' );
    }

    public function test_the_classic_scripts_depend_on_the_dialog() {
        foreach ( [ 'wpuf-frontend-form', 'wpuf-upload', 'wpuf-admin', 'wpuf-subscriptions' ] as $handle ) {
            $this->assertContains( 'wpuf-dialog', wp_scripts()->registered[ $handle ]->deps, $handle );
            $this->assertNotContains( 'wpuf-sweetalert2', wp_scripts()->registered[ $handle ]->deps, $handle );
        }
    }

    public function test_the_plugin_sources_no_longer_call_sweetalert() {
        $root = dirname( __DIR__, 3 );

        foreach ( [ 'assets/js/frontend-form.js', 'assets/js/upload.js', 'assets/js/wpuf-admin.js', 'assets/js/subscriptions-old.js', 'src/frontend/kit/components/confirm.js' ] as $file ) {
            $this->assertStringNotContainsString( 'Swal.', (string) file_get_contents( $root . '/' . $file ), $file );
        }
    }
}
