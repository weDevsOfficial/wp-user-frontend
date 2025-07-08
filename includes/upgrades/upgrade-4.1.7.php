<?php

/**
 * Migrate subscription sort order data (runs once)
 * 
 * This function ensures that all existing subscription packs have a default sort order
 * set if they don't already have one.
 *
 * @since WPUF_SINCE
 *
 * @return void
 */
function wpuf_upgrade_4_1_7_subscription_sort_order_migration() {

    $migration_version = get_option( 'wpuf_subscription_sort_order_migration', '0' );
    $current_version = '1.0';
    
    if ( version_compare( $migration_version, $current_version, '>=' ) ) {
        return;
    }
    
    // Get all subscription packs that might need migration
    $subscriptions = get_posts( [
        'post_type'      => 'wpuf_subscription',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_sort_order',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key'     => '_sort_order',
                'value'   => '',
                'compare' => '='
            ]
        ]
    ] );
    
    if ( ! empty( $subscriptions ) ) {
        foreach ( $subscriptions as $subscription ) {
            $sort_order = get_post_meta( $subscription->ID, '_sort_order', true );
            
            // Set default sort order if not set or empty
            if ( empty( $sort_order ) ) {
                update_post_meta( $subscription->ID, '_sort_order', 1 );
            }
        }
    }
    
    update_option( 'wpuf_subscription_sort_order_migration', $current_version );
}

wpuf_upgrade_4_1_7_subscription_sort_order_migration(); 