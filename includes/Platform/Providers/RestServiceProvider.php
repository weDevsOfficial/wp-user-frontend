<?php
/**
 * REST platform services
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Builder\FormSave;
use WeDevs\Wpuf\Platform\REST\Controllers\FormListController;
use WeDevs\Wpuf\Platform\REST\Controllers\FormsController;
use WeDevs\Wpuf\Platform\REST\Controllers\OnboardingController;
use WeDevs\Wpuf\Platform\REST\Controllers\SettingsController;
use WeDevs\Wpuf\Platform\REST\Controllers\SubscribersController;
use WeDevs\Wpuf\Platform\REST\Controllers\SubscriptionController;
use WeDevs\Wpuf\Platform\REST\Controllers\ToolsController;
use WeDevs\Wpuf\Platform\REST\Controllers\TransactionsController;
use WeDevs\Wpuf\Platform\REST\Manager;
use WeDevs\Wpuf\Platform\ServiceProvider;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Tools\ToolsService;
use WeDevs\Wpuf\Platform\Transactions\TransactionService;

/**
 * Registers the REST manager and every REST controller the free plugin owns.
 * All controllers extend `Platform\REST\RestController` and are tagged
 * `RestRoute`, so the manager registers each once on rest_api_init. The three
 * frozen controllers resolve to the objects `API` built (`wpuf()->api->...`),
 * so code holding those keeps the same instances.
 *
 * @since WPUF_SINCE
 */
class RestServiceProvider extends ServiceProvider {

    /**
     * Register services
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        // Registers the REST controllers once on rest_api_init (replaces API::init_api()).
        $this->share_tagged(
            Manager::class,
            function ( $container ) {
                return new Manager( $container );
            }
        );

        // Builder save: REST route and the AJAX shim share it.
        $this->share_tagged(
            FormSave::class,
            function () {
                return new FormSave();
            }
        );

        // wpuf/v1/admin/forms/{id}.
        $this->share_tagged(
            FormsController::class,
            function ( $container ) {
                return new FormsController( $container->get( FormStore::class ), $container->get( FormSave::class ) );
            }
        );

        // wpuf/v1/onboarding (setup wizard steps).
        $this->share_tagged(
            OnboardingController::class,
            function () {
                return new OnboardingController();
            }
        );

        // wpuf/v1/admin/tools/* (User Frontend > Tools).
        $this->share_tagged(
            ToolsController::class,
            function ( $container ) {
                return new ToolsController( $container->get( ToolsService::class ) );
            }
        );

        // wpuf/v1/admin/subscriptions/{id}/subscribers (a pack's subscribers).
        $this->share_tagged(
            SubscribersController::class,
            function ( $container ) {
                return new SubscribersController( $container->get( TransactionService::class ) );
            }
        );

        // wpuf/v1/admin/transactions (User Frontend > Transactions).
        $this->share_tagged(
            TransactionsController::class,
            function ( $container ) {
                return new TransactionsController( $container->get( TransactionService::class ) );
            }
        );

        // Frozen routes (wpuf_form, wpuf_subscription*, subscription-settings, settings).
        $legacy = [
            FormListController::class     => 'form_list',
            SubscriptionController::class => 'subscription',
            SettingsController::class     => 'settings',
        ];

        foreach ( $legacy as $class => $key ) {
            $this->share_tagged(
                $class,
                function () use ( $class, $key ) {
                    $api = wpuf()->api;

                    return ( is_object( $api ) && $api->$key instanceof $class ) ? $api->$key : new $class();
                }
            );
        }
    }
}
