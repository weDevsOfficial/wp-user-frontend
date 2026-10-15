<?php
/**
 * Pack admin screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Admin_Subscription;
use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The subscription packs in wp-admin: the React screen's scripts and boot
 * data, notices, the redirect of the classic post type screens to the React
 * screen and the one-off sort order migration (through the subscription
 * store). The classic list columns, pack metaboxes, update messages, help
 * link and footer text went with the classic editor.
 *
 * @since WPUF_SINCE Moved out of Admin_Subscription, which keeps the hooks and delegates.
 */
class Pack_Screen {

    /**
     * The facade: callbacks and methods of the other groups go through it.
     *
     * @var Admin_Subscription
     */
    protected $admin;

    /**
     * @since WPUF_SINCE
     *
     * @param Admin_Subscription $admin The facade.
     */
    public function __construct( Admin_Subscription $admin ) {
        $this->admin = $admin;
    }

    /**
     * Enqueue scripts for subscription page
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        // Registered in the shared Assets registry (task 2.5b).
        wp_enqueue_script( 'wpuf-subscriptions-screen' );
        wp_enqueue_style( 'wpuf-subscriptions-screen' );
        $script_handle = 'wpuf-subscriptions-screen';
        wp_set_script_translations( $script_handle, 'wp-user-frontend', WPUF_ROOT . '/languages' );
        wpuf()->platform()->get( BootPayload::class )->attach( 'subscriptions', $script_handle );

        wp_localize_script(
            $script_handle,
            'wpufSubscriptions',
            [
                'version'         => WPUF_VERSION,
                'assetUrl'        => WPUF_ASSET_URI,
                'siteUrl'         => site_url(),
                'currencySymbol'  => wpuf_get_currency( 'symbol' ),
                'supportUrl'      => esc_url(
                    'https://wedevs.com/contact/?utm_source=wpuf-subscription'
                ),
                'isProActive'     => wpuf_is_pro_active(),
                'upgradeUrl'      => esc_url(
                    'https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpuf-subscription'
                ),
                'nonce'           => wp_create_nonce( 'wp_rest' ),
                'rest_url'        => esc_url_raw( rest_url() ),
                'sections'        => $this->admin->get_sections(),
                'subSections'     => $this->admin->get_sub_sections(),
                'fields'          => $this->admin->get_fields(),
                'dependentFields' => $this->admin->get_dependent_fields(),
                'perPage'         => apply_filters( 'wpuf_subscription_per_page', 9 ),
            ]
        );

        /**
         * Fires after the subscription React scripts are enqueued.
         * Pro and third-party plugins should use this hook to enqueue their own
         * scripts with 'wpuf-subscriptions-screen' as a dependency.
         *
         * @since WPUF_SINCE
         *
         * @param string $script_handle The handle of the subscription React script
         */
        do_action( 'wpuf_subscription_scripts_enqueued', $script_handle );

        // The pre-React handle stays enqueued as an alias of the React script.
        wp_enqueue_script( 'wpuf-admin-subscriptions' );
    }

    /**
     * Remove admin notices from this page
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function remove_notices() {
        add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
    }

    /**
     * Set default sort order for existing subscriptions that don't have it
     *
     * @since 4.1.7
     */
    public function set_default_sort_order_for_existing_subscriptions() {
        // Check if we've already run this migration
        if ( get_transient( 'wpuf_sort_order_migration_done' ) ) {
            return;
        }

        Stores::subscriptions()->set_default_sort_order();

        set_transient( 'wpuf_sort_order_migration_done', true, WEEK_IN_SECONDS );
    }
}
