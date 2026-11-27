<?php
/**
 * Platform service provider base
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

/**
 * Registers a group of services in the container. Services added through
 * share_tagged() / bind_tagged() are tagged with every interface their class
 * implements, so the bootstrap can collect e.g. every `Contracts\Hookable`
 * (FlyHR / Dokan BaseServiceProvider pattern).
 *
 * @since WPUF_SINCE
 */
abstract class ServiceProvider {

    /**
     * The platform container.
     *
     * @var Container
     */
    protected $container;

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
     * Register the provider's services.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    abstract public function register();

    /**
     * Register a shared service and tag it with its class's interfaces.
     *
     * @since WPUF_SINCE
     *
     * @param string   $id      Service id (a class name, for tagging)
     * @param callable $factory Factory
     *
     * @return void
     */
    protected function share_tagged( $id, callable $factory ) {
        $this->container->share( $id, $factory );
        $this->tag_interfaces( $id );
    }

    /**
     * Register a factory service and tag it with its class's interfaces.
     *
     * @since WPUF_SINCE
     *
     * @param string   $id      Service id (a class name, for tagging)
     * @param callable $factory Factory
     *
     * @return void
     */
    protected function bind_tagged( $id, callable $factory ) {
        $this->container->bind( $id, $factory );
        $this->tag_interfaces( $id );
    }

    /**
     * Tag a service id with every interface its class implements.
     *
     * @param string $id Service id
     *
     * @return void
     */
    private function tag_interfaces( $id ) {
        if ( ! class_exists( $id ) ) {
            return;
        }

        foreach ( (array) class_implements( $id ) as $interface ) {
            $this->container->add_tag( $id, $interface );
        }
    }
}
