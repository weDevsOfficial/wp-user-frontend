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

        wp_localize_script(
            'wpuf-admin-transactions', 'wpufTransactions',
            [
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'transactionSummary' => $this->get_transaction_summary(),
                'perPage'            => apply_filters( 'wpuf_transactions_per_page', 10 ),
            ]
        );
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

    /**
     * Get transaction summary
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_transaction_summary() {
        $total = apply_filters(
            'wpuf_transaction_summary_total', [
                'amount'      => 4500,
                'change_type' => 'positive',
                'percentage'  => 4.75,
                'label'       => __( 'Total', 'wp-user-frontend' ),
            ]
        );

        $approved = apply_filters(
            'wpuf_transaction_summary_approved', [
                'amount'         => 4000,
                'change_type'    => 'negative',
                'percentage'     => 4.5,
                'label'          => __( 'Approved', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $pending = apply_filters(
            'wpuf_transaction_summary_pending', [
                'amount'         => 4000,
                'change_type'    => 'positive',
                'percentage'     => 4.5,
                'label'          => __( 'Pending', 'wp-user-frontend' ),
                'is_pro_preview' => true,
            ]
        );

        $summary = apply_filters(
            'wpuf_transaction_summary', [
                'total'         => $total,
                'approved'      => $approved,
                'pending'       => $pending,
                'refunded'      => $pending,
                'subscriptions' => $pending,
            ]
        );

        return $summary;
    }
}
