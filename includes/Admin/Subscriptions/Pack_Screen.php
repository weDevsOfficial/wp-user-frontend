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
                'isProActive'     => class_exists( 'WP_User_Frontend_Pro' ),
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
     * The classic `wpuf_subscription` screens (list, Add New, edit) open the
     * React subscriptions screen: `edit.php?post_type=wpuf_subscription` the
     * list (its status filter kept), `post-new.php` the new pack form,
     * `post.php?post=N&action=edit` that pack's form. Other post.php actions
     * (trash, untrash, delete links) run as before.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function redirect_legacy_screen() {
        global $pagenow;

        $route = $this->legacy_screen_route( (string) $pagenow );

        if ( null === $route ) {
            return;
        }

        if ( wp_safe_redirect( \WeDevs\Wpuf\Admin\Screens\Registry::app_redirect_url( $route ) ) ) {
            exit;
        }
    }

    /**
     * App route of a classic `wpuf_subscription` screen request, null when
     * the request is not one (or is a post.php action other than edit).
     *
     * @since WPUF_SINCE
     *
     * @param string $page The admin file (`edit.php`, `post-new.php`, `post.php`)
     *
     * @return string|null
     */
    public function legacy_screen_route( $page ) {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only routing.
        if ( 'edit.php' === $page || 'post-new.php' === $page ) {
            $post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

            if ( 'wpuf_subscription' !== $post_type || isset( $_GET['page'] ) ) {
                return null;
            }

            if ( 'post-new.php' === $page ) {
                return '/subscriptions?action=new';
            }

            $status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';

            return in_array( $status, [ 'publish', 'draft', 'trash' ], true ) ? '/subscriptions?post_status=' . $status : '/subscriptions';
        }

        if ( 'post.php' !== $page ) {
            return null;
        }

        $action  = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
        $pack_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( 'edit' !== $action || ! $pack_id || 'wpuf_subscription' !== get_post_type( $pack_id ) ) {
            return null;
        }

        return '/subscriptions?action=edit&id=' . $pack_id;
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
