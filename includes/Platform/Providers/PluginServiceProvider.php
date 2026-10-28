<?php
/**
 * The plugin's core services in the platform container
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Platform\ServiceProvider;

/**
 * The services WP_User_Frontend built by hand into its `$container` array,
 * registered in the platform container under their class names so new code
 * can inject them (`$container->get( Assets::class )`) and so one instance
 * serves both `wpuf()->assets` and the platform.
 *
 * Constructors of these classes add hooks, so they are not lazy: the plugin
 * resolves them eagerly in the order and under the conditions it always
 * did (WP_User_Frontend::instantiate() and the later `init` / loader steps).
 * Only the registration moved here.
 *
 * @since WPUF_SINCE
 */
class PluginServiceProvider extends ServiceProvider {

    /**
     * Container key (`wpuf()->key`) => class, in the plugin's boot order.
     */
    const SERVICES = [
        'tracker'         => \WeDevs\Wpuf\Lib\WeDevs_Insights::class,
        'free_loader'     => \WeDevs\Wpuf\Free\Free_Loader::class,
        'upgrades'        => \WeDevs\Wpuf\Admin\Upgrades::class,
        'assets'          => \WeDevs\Wpuf\Assets::class,
        'subscription'    => \WeDevs\Wpuf\Admin\Subscription::class,
        'fields'          => \WeDevs\Wpuf\Admin\Forms\Field_Manager::class,
        'customize'       => \WeDevs\Wpuf\Admin\Customizer_Options::class,
        'bank'            => \WeDevs\Wpuf\Lib\Gateway\Bank::class,
        'paypal'          => \WeDevs\Wpuf\Lib\Gateway\Paypal::class,
        'api'             => \WeDevs\Wpuf\API::class,
        'integrations'    => \WeDevs\Wpuf\Integrations::class,
        'ai_manager'      => \WeDevs\Wpuf\AI_Manager::class,
        'post_form_block' => \WeDevs\Wpuf\Blocks\PostForm::class,
        'admin'           => \WeDevs\Wpuf\Admin::class,
        'setup_wizard'    => \WeDevs\Wpuf\Setup_Wizard::class,
        'pro_upgrades'    => \WeDevs\Wpuf\Pro_Upgrades::class,
        'privacy'         => \WeDevs\Wpuf\WPUF_Privacy::class,
        'frontend'        => \WeDevs\Wpuf\Frontend::class,
        'gateway_manager' => \WeDevs\Wpuf\Lib\Gateway\Gateway_Manager::class,
        'ajax'            => \WeDevs\Wpuf\Ajax::class,
        'widgets'         => \WeDevs\Wpuf\Widgets\Manager::class,
    ];

    /**
     * Register each service class as a shared service.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        foreach ( self::SERVICES as $key => $class ) {
            if ( $this->container->has( $class ) ) {
                continue;
            }

            $this->share_tagged(
                $class,
                function () use ( $class ) {
                    // The tracker takes the plugin file; every other class has no arguments.
                    return \WeDevs\Wpuf\Lib\WeDevs_Insights::class === $class ? new $class( WPUF_FILE ) : new $class();
                }
            );
        }
    }

    /**
     * The class behind a legacy container key.
     *
     * @since WPUF_SINCE
     *
     * @param string $key Service key (`assets`, `admin`, ...)
     *
     * @return string|null
     */
    public static function class_of( $key ) {
        return isset( self::SERVICES[ $key ] ) ? self::SERVICES[ $key ] : null;
    }
}
