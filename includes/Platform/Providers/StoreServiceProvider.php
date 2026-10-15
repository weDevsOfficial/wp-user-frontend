<?php
/**
 * Form and field stores
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Platform\Onboarding\Pages;
use WeDevs\Wpuf\Platform\Onboarding\Plugin_Installer;
use WeDevs\Wpuf\Platform\Onboarding\State;
use WeDevs\Wpuf\Platform\Onboarding\Wizard;
use WeDevs\Wpuf\Platform\ServiceProvider;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\Stores\SubmissionStore;
use WeDevs\Wpuf\Platform\Stores\SubscriberStore;
use WeDevs\Wpuf\Platform\Stores\SubscriptionStore;
use WeDevs\Wpuf\Platform\Stores\TransactionStore;
use WeDevs\Wpuf\Platform\Stores\UserPackStore;
use WeDevs\Wpuf\Platform\Stores\UserStore;
use WeDevs\Wpuf\Platform\Tools\ToolsService;
use WeDevs\Wpuf\Platform\Transactions\TransactionService;

/**
 * Registers the stores every form, subscription and settings writer goes through.
 *
 * @since WPUF_SINCE
 */
class StoreServiceProvider extends ServiceProvider {

    /**
     * Register services (once: Stores may register them before the boot).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        if ( $this->container->has( FormStore::class ) ) {
            return;
        }

        $this->share_tagged(
            FieldStore::class,
            function () {
                return new FieldStore();
            }
        );

        $this->share_tagged(
            FormStore::class,
            function ( $container ) {
                return new FormStore( $container->get( FieldStore::class ) );
            }
        );

        $this->share_tagged(
            SubscriptionStore::class,
            function () {
                return new SubscriptionStore();
            }
        );

        $this->share_tagged(
            SettingsStore::class,
            function () {
                return new SettingsStore();
            }
        );

        // Subscribers table, user packs, submission meta and user meta.
        foreach ( [ SubscriberStore::class, UserPackStore::class, SubmissionStore::class, UserStore::class ] as $store ) {
            $this->share_tagged(
                $store,
                function () use ( $store ) {
                    return new $store();
                }
            );
        }

        // Domain services over the stores and develop's tables (Tools, Transactions, onboarding).
        $this->share_tagged(
            ToolsService::class,
            function ( $container ) {
                return new ToolsService(
                    $container->get( FormStore::class ),
                    $container->get( SubscriptionStore::class ),
                    $container->get( TransactionStore::class ),
                    $container->get( SettingsStore::class )
                );
            }
        );

        $this->share_tagged(
            TransactionStore::class,
            function () {
                return new TransactionStore();
            }
        );

        $this->share_tagged(
            TransactionService::class,
            function ( $container ) {
                return new TransactionService( $container->get( TransactionStore::class ) );
            }
        );

        // The setup wizard (Admin\Onboarding page, Screens\Onboarding, REST, Tools).
        $this->share_tagged(
            Pages::class,
            function () {
                return new Pages();
            }
        );

        $this->share_tagged(
            Plugin_Installer::class,
            function ( $container ) {
                return new Plugin_Installer( $container->get( State::class ) );
            }
        );

        $this->share_tagged(
            Wizard::class,
            function ( $container ) {
                return new Wizard(
                    $container->get( State::class ),
                    $container->get( Pages::class ),
                    $container->get( Plugin_Installer::class )
                );
            }
        );
    }
}
