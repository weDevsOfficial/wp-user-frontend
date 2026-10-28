<?php
/**
 * The contracts Pro and add-ons resolve from free's container: each one is
 * the same shared object as the service class.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Forms\Form_Schema;
use WeDevs\Wpuf\Frontend\Renderer;
use WeDevs\Wpuf\Platform\Contracts\App_Renderer;
use WeDevs\Wpuf\Platform\Contracts\Form_Schema_Provider;
use WeDevs\Wpuf\Platform\Contracts\Rate_Limiter;
use WeDevs\Wpuf\Platform\REST\Rate_Limit;

class PlatformContractsTest extends WP_UnitTestCase {

    public function test_contracts_resolve_to_the_shared_services() {
        $container = wpuf()->platform();
        $pairs     = [
            App_Renderer::class         => Renderer::class,
            Form_Schema_Provider::class => Form_Schema::class,
            Rate_Limiter::class         => Rate_Limit::class,
        ];

        foreach ( $pairs as $contract => $service ) {
            $this->assertTrue( $container->has( $contract ), $contract );
            $this->assertInstanceOf( $contract, $container->get( $contract ) );
            $this->assertSame( $container->get( $service ), $container->get( $contract ), $contract . ' is the shared ' . $service );
        }
    }
}
