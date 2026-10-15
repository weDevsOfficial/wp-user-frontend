<?php
/**
 * The admin layer's services, shared in the container
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Platform\ServiceProvider;

/**
 * Registers the classes `Admin` used to build with `new` as shared services, so the
 * admin layer, Tools, Onboarding and the REST boot all see one instance of each.
 *
 * Keys are the `wpuf()->admin->{key}` names; the order is the order `Admin` builds them.
 * `admin_installer` is CoreServiceProvider's and `ai_form_handler` AiServiceProvider's.
 *
 * @since WPUF_SINCE
 */
class AdminServiceProvider extends ServiceProvider {

    /**
     * Admin container key => class.
     *
     * @since WPUF_SINCE
     */
    const SERVICES = [
        'admin_welcome'         => \WeDevs\Wpuf\Admin\Admin_Welcome::class,
        'menu'                  => \WeDevs\Wpuf\Admin\Menu::class,
        'dashboard_metabox'     => \WeDevs\Wpuf\Admin\Dashboard_Metabox::class,
        'form_template'         => \WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Templates::class,
        'admin_form'            => \WeDevs\Wpuf\Admin\Forms\Admin_Form::class,
        'admin_subscription'    => \WeDevs\Wpuf\Admin\Admin_Subscription::class,
        'forms'                 => \WeDevs\Wpuf\Admin\Forms\Form_Manager::class,
        'gutenberg_block'       => \WeDevs\Wpuf\Frontend\Form_Gutenberg_Block::class,
        'plugin_upgrade_notice' => \WeDevs\Wpuf\Admin\Plugin_Upgrade_Notice::class,
        'posting'               => \WeDevs\Wpuf\Admin\Posting::class,
        'shortcodes_button'     => \WeDevs\Wpuf\Admin\Shortcodes_Button::class,
        'tools'                 => \WeDevs\Wpuf\Admin\Admin_Tools::class,
        'onboarding'            => \WeDevs\Wpuf\Admin\Onboarding::class,
        'promotion'             => \WeDevs\Wpuf\Admin\Promotion::class,
    ];

    /**
     * Share every admin service (no arguments; the constructors add their hooks).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        foreach ( self::SERVICES as $class ) {
            if ( $this->container->has( $class ) ) {
                continue;
            }

            $this->share_tagged(
                $class,
                function () use ( $class ) {
                    return new $class();
                }
            );
        }
    }

    /**
     * The class behind an admin container key.
     *
     * @since WPUF_SINCE
     *
     * @param string $key Admin container key
     *
     * @return string|null
     */
    public static function class_of( $key ) {
        return isset( self::SERVICES[ $key ] ) ? self::SERVICES[ $key ] : null;
    }
}
