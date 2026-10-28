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
                // Quick actions of the packs: where they are listed and bought.
                'plansPage'       => $this->plans_page(),
                'paymentPageUrl'  => $this->page_url( 'payment_page' ),
                'gateways'        => $this->gateways(),
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

    /**
     * The plans page (`subscription_page`): its URL and the `include` /
     * `exclude` pack ids of its `[wpuf_sub_pack]` shortcode, so a pack card
     * can say "not on the plans page".
     *
     * @since WPUF_SINCE
     *
     * @return array url, edit, include, exclude
     */
    private function plans_page() {
        $page_id = absint( Stores::settings()->get( 'wpuf_payment', 'subscription_page', 0 ) );
        $page    = $page_id ? get_post( $page_id ) : null;
        $out     = [
            'url'     => '',
            'edit'    => '',
            'include' => [],
            'exclude' => [],
        ];

        if ( ! $page || 'page' !== $page->post_type || 'trash' === $page->post_status ) {
            return $out;
        }

        $out['url']  = 'publish' === $page->post_status ? get_permalink( $page ) : '';
        $out['edit'] = current_user_can( 'edit_post', $page_id ) ? get_edit_post_link( $page_id, 'raw' ) : '';

        if ( preg_match( '/\[wpuf_sub_pack(\s[^\]]*)?\]/', (string) $page->post_content, $match ) ) {
            $atts = shortcode_parse_atts( isset( $match[1] ) ? trim( $match[1] ) : '' );

            foreach ( [ 'include', 'exclude' ] as $key ) {
                if ( is_array( $atts ) && ! empty( $atts[ $key ] ) ) {
                    $out[ $key ] = array_values( array_filter( array_map( 'absint', explode( ',', (string) $atts[ $key ] ) ) ) );
                }
            }
        }

        return $out;
    }

    /**
     * Permalink of a payment settings page, '' when unset or unpublished.
     *
     * @param string $setting Setting key in `wpuf_payment`
     *
     * @return string
     */
    private function page_url( $setting ) {
        $page_id = absint( Stores::settings()->get( 'wpuf_payment', $setting, 0 ) );

        return $page_id && 'publish' === get_post_status( $page_id ) ? (string) get_permalink( $page_id ) : '';
    }

    /**
     * Active gateways and whether each can renew a pack (pack form hints).
     *
     * @return array[] id, label, recurring
     */
    private function gateways() {
        $out = [];

        foreach ( ( new \WeDevs\Wpuf\Platform\Setup\Prerequisites() )->active_gateways() as $id => $gateway ) {
            $out[] = [
                'id'        => $id,
                'label'     => $gateway['label'],
                'recurring' => (bool) $gateway['supports_subscription'],
            ];
        }

        return $out;
    }
}
