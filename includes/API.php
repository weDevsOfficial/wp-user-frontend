<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Api\Subscription;
use WeDevs\WpUtils\ContainerTrait;

/**
 * API class.
 *
 * Handle API.
 */
#[AllowDynamicProperties]
class API {
    use ContainerTrait;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->subscription = new Subscription();

        add_action( 'rest_api_init', [ $this, 'init_api' ] );
    }

    /**
     * API initialization
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function init_api() {
        foreach ( $this->container as $class ) {
            $object = new $class();
            $object->register_routes();
        }
    }
}
