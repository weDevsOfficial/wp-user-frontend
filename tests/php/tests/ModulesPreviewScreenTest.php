<?php
/**
 * User Frontend > Modules without Pro is the admin app route `#/modules`
 * (Admin\Screens\ModulesPreview): its route, the data it hands the app, and
 * that the classic page and its assets are gone.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Screens\ModulesPreview;

class ModulesPreviewScreenTest extends WP_UnitTestCase {

    public function test_the_route_opens_from_the_old_page() {
        $screen = new ModulesPreview();
        $routes = $screen->app_routes();

        $this->assertSame( 'wpuf-modules', $screen->slug() );
        $this->assertSame( '/modules', $screen->app_route_for_request() );
        $this->assertSame( '/modules', $routes[0]['path'] );
        $this->assertSame( 'modules-preview', $routes[0]['app'] );
    }

    public function test_the_app_gets_the_free_and_pro_modules() {
        if ( ! isset( wpuf()->free_loader ) ) {
            $this->markTestSkipped( 'The free loader runs only without Pro.' );
        }

        $data = ( new ModulesPreview() )->app_globals()['wpufModulesPreview'];

        $this->assertSame( 'user_directory', $data['free'][0]['id'] );
        $this->assertIsBool( $data['free'][0]['active'] );
        $this->assertStringContainsString( '/images/modules/', $data['free'][0]['thumbnail'] );
        $this->assertGreaterThan( 10, count( $data['pro'] ) );
        $this->assertNotEmpty( $data['upgradeUrl'] );
        $this->assertStringNotContainsString( '&amp;', $data['upgradeUrl'] );
    }

    public function test_the_classic_page_assets_are_gone() {
        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();

        $this->assertFalse( wp_script_is( 'wpuf-module', 'registered' ) );
        $this->assertFalse( wp_style_is( 'wpuf-module', 'registered' ) );
        $this->assertFalse( wp_script_is( 'wpuf-swiffy-slider', 'registered' ) );
        $this->assertFalse( file_exists( WPUF_ROOT . '/assets/js/admin/wpuf-module.js' ) );
    }
}
