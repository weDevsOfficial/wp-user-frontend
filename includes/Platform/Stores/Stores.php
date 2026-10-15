<?php
/**
 * Store access for legacy entry points
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Providers\StoreServiceProvider;

/**
 * The stores from the platform container, also before the platform boots
 * (plugin activation runs the installer without `plugins_loaded`).
 *
 * @since WPUF_SINCE
 */
class Stores {

    /**
     * Form store
     *
     * @since WPUF_SINCE
     *
     * @return FormStore
     */
    public static function forms() {
        return self::get( FormStore::class );
    }

    /**
     * Field store
     *
     * @since WPUF_SINCE
     *
     * @return FieldStore
     */
    public static function fields() {
        return self::get( FieldStore::class );
    }

    /**
     * Subscription pack store
     *
     * @since WPUF_SINCE
     *
     * @return SubscriptionStore
     */
    public static function subscriptions() {
        return self::get( SubscriptionStore::class );
    }

    /**
     * The transaction store.
     *
     * @since WPUF_SINCE
     *
     * @return TransactionStore
     */
    public static function transactions() {
        return self::get( TransactionStore::class );
    }

    /**
     * The subscriber store (the `wpuf_subscribers` table).
     *
     * @since WPUF_SINCE
     *
     * @return SubscriberStore
     */
    public static function subscribers() {
        return self::get( SubscriberStore::class );
    }

    /**
     * The user pack store (user meta).
     *
     * @since WPUF_SINCE
     *
     * @return UserPackStore
     */
    public static function user_packs() {
        return self::get( UserPackStore::class );
    }

    /**
     * The submission store (the plugin's meta on submitted posts).
     *
     * @since WPUF_SINCE
     *
     * @return SubmissionStore
     */
    public static function submissions() {
        return self::get( SubmissionStore::class );
    }

    /**
     * The user store (profile and photo meta).
     *
     * @since WPUF_SINCE
     *
     * @return UserStore
     */
    public static function users() {
        return self::get( UserStore::class );
    }

    /**
     * Settings store
     *
     * @since WPUF_SINCE
     *
     * @return SettingsStore
     */
    public static function settings() {
        return self::get( SettingsStore::class );
    }

    /**
     * Resolve a store, registering the stores first when the boot has not run yet.
     *
     * @param string $id Service id
     *
     * @return object
     */
    private static function get( $id ) {
        $container = wpuf()->platform();

        if ( ! $container->has( $id ) ) {
            ( new StoreServiceProvider( $container ) )->register();
        }

        return $container->get( $id );
    }
}
