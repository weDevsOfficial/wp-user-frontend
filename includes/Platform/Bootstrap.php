<?php
/**
 * Platform bootstrap
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

use WeDevs\Wpuf\Platform\Providers\AdminServiceProvider;
use WeDevs\Wpuf\Platform\Providers\FrontendServiceProvider;

use WeDevs\Wpuf\Platform\Contracts\Hookable;
use WeDevs\Wpuf\Platform\Providers\CoreServiceProvider;
use WeDevs\Wpuf\Platform\Providers\PluginServiceProvider;
use WeDevs\Wpuf\Platform\Providers\StoreServiceProvider;
use WeDevs\Wpuf\Platform\Providers\RestServiceProvider;
use WeDevs\Wpuf\Platform\Providers\AiServiceProvider;

/**
 * Boots the platform once: registers the free providers, calls
 * register_hooks() once on every `Hookable` service, then fires
 * `wpuf_platform_loaded` so Pro (and add-ons) can add their providers.
 *
 * @since WPUF_SINCE
 */
class Bootstrap {

    /**
     * The platform container.
     *
     * @var Container
     */
    private $container;

    /**
     * Whether boot() ran.
     *
     * @var bool
     */
    private $booted = false;

    /**
     * Hookable ids whose register_hooks() already ran.
     *
     * @var bool[]
     */
    private $hooked = [];

    /**
     * Whether the providers are registered.
     *
     * @var bool
     */
    private $registered = false;

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
     * Free providers, in registration order.
     *
     * @since WPUF_SINCE
     *
     * @return string[] Provider class names
     */
    public function providers() {
        return [
            PluginServiceProvider::class,
            CoreServiceProvider::class,
            AdminServiceProvider::class,
            FrontendServiceProvider::class,
            StoreServiceProvider::class,
            RestServiceProvider::class,
            AiServiceProvider::class,
        ];
    }

    /**
     * Register the free providers, hook their services and announce the
     * platform. Runs once; later calls do nothing.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function boot() {
        if ( $this->booted ) {
            return;
        }

        $this->booted = true;

        $this->register();
        $this->hook_services();

        /**
         * The platform container is ready and the free services are hooked.
         * Pro and add-ons register their providers here (register_provider()).
         *
         * @since WPUF_SINCE
         *
         * @param Container $container Platform container
         * @param Bootstrap $bootstrap Platform bootstrap
         */
        do_action( 'wpuf_platform_loaded', $this->container, $this );
    }

    /**
     * Register the providers once (no hooks yet): the plugin calls this before
     * it builds its legacy services, so they resolve from the container; boot()
     * calls it too.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        if ( $this->registered ) {
            return;
        }

        $this->registered = true;

        foreach ( $this->providers() as $provider ) {
            ( new $provider( $this->container ) )->register();
        }
    }

    /**
     * Register a provider after boot (Pro, add-ons) and hook its new services.
     *
     * @since WPUF_SINCE
     *
     * @param ServiceProvider $provider Provider
     *
     * @return void
     */
    public function register_provider( ServiceProvider $provider ) {
        $provider->register();
        $this->hook_services();
    }

    /**
     * Whether boot() ran.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_booted() {
        return $this->booted;
    }

    /**
     * Call register_hooks() once on every Hookable service not hooked yet.
     *
     * @return void
     */
    private function hook_services() {
        foreach ( $this->container->tagged_ids( Hookable::class ) as $id ) {
            if ( isset( $this->hooked[ $id ] ) ) {
                continue;
            }

            $this->hooked[ $id ] = true;
            $this->container->get( $id )->register_hooks();
        }
    }
}
