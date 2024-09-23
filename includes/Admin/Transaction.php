<?php

namespace WeDevs\Wpuf\Admin;

/**
 * Manage Subscription packs
 */
class Transaction {
    /**
     * The constructor
     */
    public function __construct() {
        add_action( 'wpuf_load_transactions_page', [ $this, 'remove_notices' ] );
        add_action( 'wpuf_load_transactions_page', [ $this, 'enqueue_admin_scripts' ] );
        add_filter( 'script_loader_tag', [ $this, 'add_type_attribute' ], 10, 3 );
    }

    /**
     * Add type="module" to the script tag
     *
     * @param $tag
     * @param $handle
     * @param $src
     *
     * @since WPUF_SINCE
     *
     * @return mixed|string
     */
    public function add_type_attribute( $tag, $handle, $src ) {
        // Check if this is the script you want to modify
        if ( 'wpuf-admin-transactions' === $handle ) {
            // phpcs:ignore
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        }

        return $tag;
    }


    /**
     * Enqueue scripts for subscription page
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_script( 'wpuf-admin-transactions' );
        wp_enqueue_style( 'wpuf-admin-transactions' );
        wp_script_add_data( 'wpuf-admin-transactions', 'type', 'module' );

        //
//        wp_localize_script(
//            'wpuf-admin-subscriptions', 'wpufSubscriptions',
//            [
//                'version'         => WPUF_VERSION,
//                'assetUrl'        => WPUF_ASSET_URI,
//                'siteUrl'         => site_url(),
//                'currencySymbol'  => wpuf_get_currency( 'symbol' ),
//                'supportUrl'      => esc_url(
//                    'https://wedevs.com/contact/?utm_source=wpuf_transaction'
//                ),
//                'isProActive'     => class_exists( 'WP_User_Frontend_Pro' ),
//                'upgradeUrl'      => esc_url(
//                    'https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpuf-subscription'
//                ),
//                'nonce'           => wp_create_nonce( 'wp_rest' ),
//                'perPage'         => apply_filters( 'wpuf_transactions_per_page', 10 ),
//            ]
//        );
    }

    /**
     * Remove admin notices from this page
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function remove_notices() {
        add_action( 'in_admin_header', 'wpuf_remove_admin_notices' );
    }
}
