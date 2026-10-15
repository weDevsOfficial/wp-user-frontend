<?php
/**
 * Pro's classic field scripts (intl-tel-input, conditional logic, field
 * initialisation) follow free's classic form bundle: none on a React account
 * page, also on `?section=submit-post`; there with a classic form.
 *
 * @package WP_User_Frontend
 */
class ProFieldAssetsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();
        do_action( 'wp_enqueue_scripts' );
        $GLOBALS['wp_scripts']->queue = [];
        $GLOBALS['wp_styles']->queue  = [];
    }

    public function tear_down() {
        unset( $_GET['section'] );
        $GLOBALS['post'] = null;
        parent::tear_down();
    }

    private function account_page() {
        $GLOBALS['post'] = get_post( self::factory()->post->create( [ 'post_type' => 'page', 'post_content' => '[wpuf_account]' ] ) );
    }

    public function test_react_account_submit_section_loads_no_classic_field_scripts() {
        $this->account_page();
        $_GET['section'] = 'submit-post';

        wpuf_pro()->frontend->enqueue_assets();

        $this->assertFalse( wp_script_is( 'wpuf-intlTelInput', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'wpuf-field-initialization', 'enqueued' ) );
    }

    public function test_classic_form_bundle_brings_the_field_scripts() {
        $this->account_page();
        wp_enqueue_script( 'wpuf-frontend-form' );

        wpuf_pro()->frontend->enqueue_assets();

        $this->assertTrue( wp_script_is( 'wpuf-intlTelInput', 'enqueued' ) );
        $this->assertTrue( wp_script_is( 'wpuf-field-initialization', 'enqueued' ) );
    }
}
