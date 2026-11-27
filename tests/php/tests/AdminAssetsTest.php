<?php
/**
 * Admin assets and boot payload (task 2.5b).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\BootPayload;

/**
 * Admin assets tests.
 */
class AdminAssetsTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function tear_down() {
        unset( $_GET['page'] );
        parent::tear_down();
    }

    public function test_apps_are_registered_in_the_shared_registry() {
        wpuf()->assets->register_all_scripts();

        foreach ( [ 'wpuf-forms-list-screen', 'wpuf-settings-screen', 'wpuf-subscriptions-screen', 'wpuf-form-builder-screen' ] as $handle ) {
            $this->assertTrue( wp_script_is( $handle, 'registered' ), $handle );
        }

        foreach ( [ 'wpuf-settings-screen', 'wpuf-subscriptions-screen' ] as $handle ) {
            $this->assertTrue( wp_style_is( $handle, 'registered' ), $handle . ' style' );
        }

    }

    public function test_shared_layer_handles_are_registered_but_not_enqueued() {
        wpuf()->assets->register_all_scripts();
        $scripts = wp_scripts();

        $this->assertTrue( wp_script_is( 'wpuf-admin-runtime', 'registered' ) );
        $this->assertTrue( wp_script_is( 'wpuf-admin-ui', 'registered' ) );
        $this->assertContains( 'wpuf-admin-runtime', $scripts->registered['wpuf-admin-ui']->deps, 'plugin-ui layer loads after the runtime' );
        $this->assertContains( 'wp-components', $scripts->registered['wpuf-admin-ui']->deps );
        $this->assertSame( 'wp-user-frontend', $scripts->registered['wpuf-admin-ui']->textdomain, 'shared components load translations' );
        $this->assertSame( 'replace', wp_styles()->get_data( 'wpuf-subscriptions-screen', 'rtl' ), 'subscriptions-rtl.css on RTL sites' );
        $this->assertFalse( wp_script_is( 'wpuf-admin-runtime', 'enqueued' ), 'no screen uses the shared layer yet (task 3.2)' );
        $this->assertFalse( wp_script_is( 'wpuf-admin-ui', 'enqueued' ) );
    }

    public function test_boot_payload_has_the_common_values_and_scoped_filters() {
        add_filter(
            'wpuf_admin_boot',
            function ( $data, $screen ) {
                $data['all'] = $screen;

                return $data;
            },
            10,
            2
        );
        add_filter(
            'wpuf_admin_boot_settings',
            function ( $data ) {
                $data['only_settings'] = true;

                return $data;
            }
        );

        $boot     = new BootPayload();
        $settings = $boot->data( 'settings' );
        $forms    = $boot->data( 'post_forms' );

        foreach ( [ 'screen', 'restUrl', 'restNonce', 'ajaxUrl', 'adminUrl', 'assetUrl', 'version', 'proVersion', 'isPro', 'plan', 'canManage' ] as $key ) {
            $this->assertArrayHasKey( $key, $settings );
        }

        $this->assertSame( 'settings', $settings['all'] );
        $this->assertTrue( $settings['only_settings'] );
        $this->assertArrayNotHasKey( 'only_settings', $forms, 'scoped filter only for its screen' );
        $this->assertStringNotContainsStringIgnoringCase( 'secret', wp_json_encode( $settings ) );
    }

    public function test_boot_payload_is_printed_once_before_the_app() {
        wp_register_script( 'wpuf-test-app', 'https://example.test/app.js', [], '1', true );

        $boot = new BootPayload();
        $boot->attach( 'post_forms', 'wpuf-test-app' );
        $boot->attach( 'post_forms', 'wpuf-test-app' );

        $before = array_values( array_filter( (array) wp_scripts()->get_data( 'wpuf-test-app', 'before' ) ) );

        $this->assertCount( 1, $before );
        $this->assertStringStartsWith( 'window.wpufAdmin = {', $before[0] );
    }

    public function test_common_scripts_print_nothing_and_move_the_badge_to_the_footer() {
        set_current_screen( 'dashboard' );
        $_GET['page'] = 'wpuf-post-forms';

        $admin = ( new ReflectionClass( WeDevs\Wpuf\Admin::class ) )->newInstanceWithoutConstructor();

        ob_start();
        $admin->enqueue_common_scripts();
        $output = ob_get_clean();

        $this->assertSame( '', $output, 'nothing printed before the document' );
        $this->assertNotFalse( has_action( 'admin_footer' ) );
    }

    public function test_forms_pages_serve_the_tailwind_4_sheet_in_place() {
        global $plugin_page;

        set_current_screen( 'dashboard' ); // is_admin()
        $assets = wpuf()->assets;

        // The old handles carry the React sheets (RTL copies swapped in by WordPress);
        // the Tailwind 3 sheets they used to point at are no longer built.
        $styles = $assets->get_styles();
        $this->assertStringEndsWith( '/css/admin/forms-screen.css', $styles['admin-form-builder']['src'] );
        $this->assertStringEndsWith( '/css/admin/forms-list-screen.css', $styles['forms-list']['src'] );
        $this->assertTrue( $styles['admin-form-builder']['rtl'] );
        $this->assertTrue( $styles['forms-list']['rtl'] );

        // Other screens: untouched.
        $plugin_page = 'wpuf-settings'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $this->assertSame( 'b.css', $assets->use_forms_styles( 'b.css', 'wpuf-admin-form-builder' ) );
        $this->assertSame( 'a', $assets->forms_body_class( 'a' ) );

        // Builders: the builder handle prints, forms-list prints nothing.
        $plugin_page    = 'wpuf-post-forms'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $_GET['action'] = 'edit';
        $this->assertSame( 'b.css', $assets->use_forms_styles( 'b.css', 'wpuf-admin-form-builder' ) );
        $this->assertSame( 'b-rtl.css', $assets->use_forms_styles( 'b-rtl.css', 'wpuf-admin-form-builder-rtl' ) );
        $this->assertFalse( $assets->use_forms_styles( 'y.css', 'wpuf-forms-list' ) );
        $this->assertSame( 'x.css', $assets->use_forms_styles( 'x.css', 'wpuf-admin' ) );
        $this->assertSame( 'a wpuf-builder-screen wpuf-admin-screen', $assets->forms_body_class( 'a' ) );

        // Lists: forms-list prints, the builder handle prints nothing.
        unset( $_GET['action'] );
        $this->assertFalse( $assets->use_forms_styles( 'b.css', 'wpuf-admin-form-builder' ) );
        $this->assertSame( 'y.css', $assets->use_forms_styles( 'y.css', 'wpuf-forms-list' ) );

        $plugin_page = 'wpuf-profile-forms'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $this->assertSame( 'wpuf-admin-screen', $assets->forms_body_class( '' ) );
    }
}
