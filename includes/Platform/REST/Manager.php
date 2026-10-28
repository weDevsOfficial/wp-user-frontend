<?php
/**
 * Platform REST manager
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST;

use WeDevs\Wpuf\Platform\Container;
use WeDevs\Wpuf\Platform\Contracts\Hookable;
use WeDevs\Wpuf\Platform\Contracts\RestRoute;

/**
 * Registers the free plugin's REST controllers on `rest_api_init`: every
 * platform service tagged `RestRoute` (RestServiceProvider), which includes the
 * frozen controllers `API` built (FormList, Subscription, Settings; same
 * objects, frozen paths, arguments, permissions and responses). Replaces the
 * loop in API::init_api(), which built every controller a second time.
 *
 * @since WPUF_SINCE
 */
class Manager implements Hookable {

    /**
     * Platform container.
     *
     * @var Container
     */
    private $container;

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     *
     * @param Container $container Platform container
     */
    public function __construct( Container $container ) {
        $this->container = $container;
    }

    /**
     * Hook route registration.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_hooks() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * The controllers to register.
     *
     * @since WPUF_SINCE
     *
     * @return object[] Objects with a register_routes() method
     */
    public function controllers() {
        $controllers = [];
        $api         = wpuf()->api;

        // The legacy core controllers, as API built them (wpuf()->api->subscription etc.).
        if ( is_object( $api ) && method_exists( $api, 'controllers' ) ) {
            $controllers = $api->controllers();
        }

        foreach ( $this->container->tagged_ids( RestRoute::class ) as $id ) {
            $controllers[] = $this->container->get( $id );
        }

        // The frozen controllers come from API and from the container (same
        // objects, RestServiceProvider): register each object once.
        $unique = [];

        foreach ( $controllers as $controller ) {
            $unique[ spl_object_hash( $controller ) ] = $controller;
        }

        $controllers = array_values( $unique );

        /**
         * Filter the REST controllers WPUF registers on rest_api_init.
         *
         * @since WPUF_SINCE
         *
         * @param object[] $controllers Objects with a register_routes() method
         */
        return (array) apply_filters( 'wpuf_rest_controllers', $controllers );
    }

    /**
     * Register every controller's routes on each rest_api_init, as API::init_api()
     * did (a REST server built twice in one request gets the routes again).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_routes() {
        foreach ( $this->controllers() as $controller ) {
            if ( is_object( $controller ) && method_exists( $controller, 'register_routes' ) ) {
                $controller->register_routes();
            }
        }
    }
}
