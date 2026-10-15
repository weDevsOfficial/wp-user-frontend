<?php
/**
 * The two app registries (Admin\Bundles for the admin screens, Frontend\Bundles
 * for the frontend apps) feed the legacy Assets registry: every handle they list
 * is registered with its dependencies, every `wpuf-*` dependency exists, and
 * the frontend handles stay out of the admin registry.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Bundles as Admin_Bundles;
use WeDevs\Wpuf\Frontend\Bundles as Frontend_Bundles;

class BundlesRegistryTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();
    }

    public function test_both_registries_come_from_the_container() {
        $this->assertInstanceOf( Admin_Bundles::class, wpuf()->platform()->get( Admin_Bundles::class ) );
        $this->assertInstanceOf( Frontend_Bundles::class, wpuf()->platform()->get( Frontend_Bundles::class ) );
    }

    public function test_every_app_handle_is_registered_with_its_dependencies() {
        $registries = [ wpuf()->platform()->get( Admin_Bundles::class ), wpuf()->platform()->get( Frontend_Bundles::class ) ];

        foreach ( $registries as $registry ) {
            foreach ( $registry->scripts() as $key => $script ) {
                $handle = 'wpuf-' . $key;

                $this->assertArrayHasKey( $handle, wp_scripts()->registered, $handle );

                foreach ( (array) $script['deps'] as $dep ) {
                    $this->assertContains( $dep, wp_scripts()->registered[ $handle ]->deps, $handle . ' depends on ' . $dep );

                    if ( 0 === strpos( $dep, 'wpuf-' ) ) {
                        $this->assertArrayHasKey( $dep, wp_scripts()->registered, $handle . ' needs ' . $dep );
                    }
                }
            }

            foreach ( array_keys( $registry->styles() ) as $key ) {
                $this->assertArrayHasKey( 'wpuf-' . $key, wp_styles()->registered, 'wpuf-' . $key );
            }
        }
    }

    public function test_frontend_handles_live_in_the_frontend_registry_only() {
        $admin    = array_keys( wpuf()->platform()->get( Admin_Bundles::class )->scripts() );
        $frontend = array_keys( wpuf()->platform()->get( Frontend_Bundles::class )->scripts() );

        $this->assertSame( [ 'frontend-runtime', 'frontend-forms', 'frontend-account', 'frontend-login', 'frontend-plans', 'frontend-checkout' ], $frontend );
        $this->assertSame( [], array_values( array_filter( $admin, function ( $key ) { return 0 === strpos( $key, 'frontend-' ); } ) ) );
        $this->assertSame( [], array_values( array_filter( array_keys( wpuf()->platform()->get( Admin_Bundles::class )->styles() ), function ( $key ) { return 0 === strpos( $key, 'frontend-' ); } ) ) );
    }
}
