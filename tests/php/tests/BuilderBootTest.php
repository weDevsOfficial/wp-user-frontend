<?php
/**
 * Builder boot for the React admin app (task 5d): the builder data for one
 * form, built as on the builder screen, over REST.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Builder\BuilderBoot;

/**
 * Builder boot tests.
 */
class BuilderBootTest extends WP_UnitTestCase {

    public static function set_up_before_class() {
        parent::set_up_before_class();

        // A REST request builds free's admin layer once (BuilderBoot); build it
        // here, before each test backs up the hooks, so its listeners stay.
        if ( empty( wpuf()->container['admin'] ) ) {
            wpuf()->container['admin'] = wpuf()->platform()->get( \WeDevs\Wpuf\Admin::class );
        }
    }

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        // Another test class may have built the admin layer inside a test, whose
        // hooks were restored away afterwards: make sure the post builder listener
        // is there (restored again after this test).
        if ( ! self::has_free_builder_listener() ) {
            add_action( 'wpuf_load_post_forms', [ new \WeDevs\Wpuf\Admin\Forms\Admin_Form(), 'post_forms_builder_init' ] );
        }
    }

    /**
     * Whether free's post builder listener is on `wpuf_load_post_forms` (Pro
     * adds its own listeners there, so has_action() alone says nothing).
     *
     * @return bool
     */
    private static function has_free_builder_listener() {
        global $wp_filter;

        if ( empty( $wp_filter['wpuf_load_post_forms'] ) ) {
            return false;
        }

        foreach ( $wp_filter['wpuf_load_post_forms']->callbacks as $callbacks ) {
            foreach ( $callbacks as $callback ) {
                $function = $callback['function'];

                if ( is_array( $function ) && $function[0] instanceof \WeDevs\Wpuf\Admin\Forms\Admin_Form && 'post_forms_builder_init' === $function[1] ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function test_boot_returns_the_builder_globals_for_a_post_form() {
        $form_id = wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' );
        $data    = ( new BuilderBoot() )->boot( $form_id );

        $this->assertIsArray( $data );
        $this->assertSame( [ 'wpuf_form_builder', 'wpuf_single_objects', 'wpuf_mixins', 'builder_form' ], array_keys( $data ) );
        $this->assertSame( 'wpuf_forms', $data['wpuf_form_builder']['form_type'] );
        $this->assertSame( $form_id, $data['wpuf_form_builder']['post']->ID );
        $this->assertNotEmpty( $data['wpuf_form_builder']['form_fields'] );
        $this->assertArrayHasKey( 'legacy_slots', $data['wpuf_form_builder'] );
        $this->assertContains( 'post_title', $data['wpuf_single_objects'] );
        $this->assertSame( [ 'root', 'builder_stage', 'form_fields', 'field_options' ], array_keys( $data['wpuf_mixins'] ) );
    }

    public function test_boot_returns_the_builder_form_inputs() {
        $form_id = wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' );
        $form    = ( new BuilderBoot() )->boot( $form_id )['builder_form'];

        // The hidden inputs of the builder screen's form (wpuf_form_id, form_settings_key, nonce).
        $this->assertSame( (string) $form_id, $form['form_id'] );
        $this->assertSame( 'post', $form['form_type'] );
        $this->assertSame( 'wpuf_forms', $form['post_type'] );
        $this->assertSame( 'wpuf_form_settings', $form['form_settings_key'] );
        $this->assertSame( 1, wp_verify_nonce( $form['nonce'], 'wpuf_form_builder_save_form' ) );
    }

    public function test_rest_creates_a_form_for_the_new_form_route() {
        $request = new \WP_REST_Request( 'POST', '/wpuf/v1/admin/forms' );
        $request->set_param( 'type', 'wpuf_forms' );

        $response = rest_get_server()->dispatch( $request );
        $id       = $response->get_data()['data']['id'];

        $this->assertSame( 201, $response->get_status() );
        $this->assertSame( 'wpuf_forms', get_post_type( $id ) );

        $request->set_param( 'type', 'post' );
        $this->assertSame( 400, rest_get_server()->dispatch( $request )->get_status(), 'only form post types' );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
        $request->set_param( 'type', 'wpuf_forms' );
        $this->assertSame( 403, rest_get_server()->dispatch( $request )->get_status(), 'managers only' );
    }

    public function test_top_level_scalars_have_the_localized_shape() {
        $data = ( new BuilderBoot() )->boot( wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' ) );

        // wp_localize_script() prints true as "1" and false as "".
        $this->assertContains( $data['wpuf_form_builder']['is_pro_active'], [ '1', '' ] );
        $this->assertIsString( $data['wpuf_form_builder']['nonce'] );
    }

    public function test_the_request_is_put_back() {
        global $plugin_page, $pagenow, $post;

        $_GET['keep']  = 'yes';
        $plugin_page   = 'other'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $pagenow       = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $before_screen = isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null;

        ( new BuilderBoot() )->boot( wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' ) );

        $this->assertSame( 'yes', $_GET['keep'] );
        $this->assertArrayNotHasKey( 'page', $_GET );
        $this->assertArrayNotHasKey( 'action', $_GET );
        $this->assertSame( 'other', $plugin_page );
        $this->assertSame( 'index.php', $pagenow );
        $this->assertSame( $before_screen, isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null );
        $this->assertFalse( BuilderBoot::is_running() );
        unset( $_GET['keep'] );
    }

    public function test_boot_lists_the_pro_preview_fields_without_pro() {
        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is active: no preview fields.' );
        }

        // A REST request (not is_admin()) has no Pro_Upgrades until the boot builds it
        // (the platform's instance is dropped too: its hooks were restored away after the test that built it).
        unset( wpuf()->container['pro_upgrades'] );
        wpuf()->platform()->forget( \WeDevs\Wpuf\Pro_Upgrades::class );

        $form_id  = wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' );
        $sections = wp_list_pluck( ( new BuilderBoot() )->boot( $form_id )['wpuf_form_builder']['panel_sections'], 'fields', 'id' );

        $this->assertContains( 'repeat_field', $sections['custom-fields'] );
        $this->assertContains( 'qr_code', $sections['others'] );
        $this->assertArrayHasKey( 'pricing-fields', $sections );
    }

    public function test_non_forms_are_rejected() {
        $page = self::factory()->post->create( [ 'post_type' => 'page' ] );

        $this->assertWPError( ( new BuilderBoot() )->boot( $page ) );
        $this->assertWPError( ( new BuilderBoot() )->boot( 999999 ) );
    }

    public function test_rest_route_is_registered_with_the_form_permission() {
        do_action( 'rest_api_init' );
        $routes = rest_get_server()->get_routes();

        $this->assertArrayHasKey( '/wpuf/v1/admin/forms/(?P<id>[\d]+)/builder', $routes );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $form_id  = wpuf_create_sample_form( 'Boot Form', 'wpuf_forms' );
        $response = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/admin/forms/' . $form_id . '/builder' ) );

        $this->assertContains( $response->get_status(), [ 401, 403 ] );
    }
}
