<?php
/**
 * The frontend layer's services, shared in the container
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Frontend\Account\Account_Service;
use WeDevs\Wpuf\Frontend\Forms\Form_Schema;
use WeDevs\Wpuf\Frontend\Forms\Submission_Service;
use WeDevs\Wpuf\Frontend\Forms\Upload_Service;
use WeDevs\Wpuf\Frontend\Login\Login_Schema;
use WeDevs\Wpuf\Frontend\Renderer;
use WeDevs\Wpuf\Platform\REST\Controllers\AccountController;
use WeDevs\Wpuf\Platform\REST\Controllers\FormsPublicController;
use WeDevs\Wpuf\Platform\REST\Controllers\UploadsController;
use WeDevs\Wpuf\Platform\REST\Rate_Limit;
use WeDevs\Wpuf\Platform\ServiceProvider;

/**
 * Registers the classes `Frontend` and `Ajax` used to build with `new` as shared
 * services: one instance of each per request, so the AJAX layer reuses the
 * frontend's objects instead of building (and hooking) a second copy.
 *
 * SERVICES keys are the `wpuf()->frontend->{key}` names in the order `Frontend`
 * builds them. HANDLERS are the classes `Ajax` (and the payment page) build
 * with no container key.
 *
 * @since WPUF_SINCE
 */
class FrontendServiceProvider extends ServiceProvider {

    /**
     * Frontend container key => class.
     *
     * @since WPUF_SINCE
     */
    const SERVICES = [
        'frontend_form'      => \WeDevs\Wpuf\Frontend\Frontend_Form::class,
        'registration'       => \WeDevs\Wpuf\Frontend\Registration::class,
        'simple_login'       => \WeDevs\Wpuf\Free\Simple_Login::class,
        'frontend_account'   => \WeDevs\Wpuf\Frontend\Frontend_Account::class,
        'frontend_dashboard' => \WeDevs\Wpuf\Frontend\Frontend_Dashboard::class,
        'shortcode'          => \WeDevs\Wpuf\Frontend\Shortcode::class,
        'payment'            => \WeDevs\Wpuf\Frontend\Payment::class,
        'form_preview'       => \WeDevs\Wpuf\Frontend\Form_Preview::class,
    ];

    /**
     * Handler classes with no container key.
     *
     * @since WPUF_SINCE
     */
    const HANDLERS = [
        \WeDevs\Wpuf\Ajax\Frontend_Form_Ajax::class,
        \WeDevs\Wpuf\Ajax\Upload_Ajax::class,
        \WeDevs\Wpuf\Ajax\Admin_Form_Builder_Ajax::class,
        \WeDevs\Wpuf\Ajax\Address_Form_Ajax::class,
        \WeDevs\Wpuf\Integrations\WPUF_ACF_Compatibility::class,
        \WeDevs\Wpuf\Widgets\Login_Widget::class,
    ];

    /**
     * Register each frontend class as a shared service.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        foreach ( array_merge( array_values( self::SERVICES ), self::HANDLERS ) as $class ) {
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

        $this->register_frontend();
    }

    /**
     * The class behind a frontend container key.
     *
     * @since WPUF_SINCE
     *
     * @param string $key Frontend key (`frontend_form`, `payment`, ...)
     *
     * @return string|null
     */
    /**
     * The services behind the React post form, login screens and account page and their
     * REST controllers (tagged RestRoute, registered by REST\Manager).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function register_frontend() {
        $this->container->share(
            Renderer::class,
            function () {
                return new Renderer();
            }
        );
        $this->container->share(
            Rate_Limit::class,
            function () {
                return new Rate_Limit();
            }
        );
        $this->container->share(
            Form_Schema::class,
            function () {
                return new Form_Schema();
            }
        );
        $this->container->share(
            Submission_Service::class,
            function ( $container ) {
                return new Submission_Service( $container->get( \WeDevs\Wpuf\Ajax\Frontend_Form_Ajax::class ) );
            }
        );
        $this->container->share(
            Upload_Service::class,
            function ( $container ) {
                return new Upload_Service( $container->get( \WeDevs\Wpuf\Ajax\Upload_Ajax::class ) );
            }
        );
        $this->container->share(
            Login_Schema::class,
            function ( $container ) {
                return new Login_Schema( $container->get( \WeDevs\Wpuf\Free\Simple_Login::class ) );
            }
        );
        $this->container->share(
            Account_Service::class,
            function () {
                return new Account_Service();
            }
        );
        $this->share_tagged(
            FormsPublicController::class,
            function ( $container ) {
                return new FormsPublicController( $container->get( Form_Schema::class ), $container->get( Submission_Service::class ), $container->get( Rate_Limit::class ), $container->get( Renderer::class ) );
            }
        );
        $this->share_tagged(
            UploadsController::class,
            function ( $container ) {
                return new UploadsController( $container->get( Upload_Service::class ), $container->get( Rate_Limit::class ) );
            }
        );
        $this->share_tagged(
            AccountController::class,
            function ( $container ) {
                return new AccountController( $container->get( Account_Service::class ), $container->get( Upload_Service::class ) );
            }
        );
    }

    public static function class_of( $key ) {
        return isset( self::SERVICES[ $key ] ) ? self::SERVICES[ $key ] : null;
    }
}
