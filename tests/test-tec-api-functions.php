<?php
/**
 * Test script to verify TEC API functions and template registration
 * 
 * Run this in WordPress admin to test the Events Calendar integration
 */

// Only run in admin
if ( ! is_admin() ) {
    return;
}

// Test TEC API functions
function test_tec_api_functions() {
    echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
    echo '<h2>Events Calendar API Functions Test</h2>';
    
    // Check if TEC is active
    $tec_active = class_exists( 'Tribe__Events__Main' );
    echo '<p><strong>TEC Active:</strong> ' . ( $tec_active ? 'Yes' : 'No' ) . '</p>';
    
    if ( ! $tec_active ) {
        echo '<p style="color: red;"><strong>❌ The Events Calendar plugin is not active!</strong></p>';
        echo '</div>';
        return;
    }
    
    // Test venue functions
    echo '<h3>Venue Functions Test</h3>';
    
    // Test tribe_get_venues()
    try {
        $venues = tribe_get_venues( false, -1, true );
        echo '<p><strong>tribe_get_venues():</strong> ' . ( is_array( $venues ) ? '✅ Working - Found ' . count( $venues ) . ' venues' : '❌ Failed' ) . '</p>';
        
        if ( ! empty( $venues ) ) {
            echo '<ul>';
            foreach ( array_slice( $venues, 0, 3 ) as $venue ) {
                echo '<li>Venue: ' . esc_html( $venue->post_title ) . ' (ID: ' . $venue->ID . ')</li>';
            }
            echo '</ul>';
        }
    } catch ( Exception $e ) {
        echo '<p style="color: red;"><strong>tribe_get_venues() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
    }
    
    // Test tribe_get_organizers()
    echo '<h3>Organizer Functions Test</h3>';
    
    try {
        $organizers = tribe_get_organizers( false, -1, true );
        echo '<p><strong>tribe_get_organizers():</strong> ' . ( is_array( $organizers ) ? '✅ Working - Found ' . count( $organizers ) . ' organizers' : '❌ Failed' ) . '</p>';
        
        if ( ! empty( $organizers ) ) {
            echo '<ul>';
            foreach ( array_slice( $organizers, 0, 3 ) as $organizer ) {
                echo '<li>Organizer: ' . esc_html( $organizer->post_title ) . ' (ID: ' . $organizer->ID . ')</li>';
            }
            echo '</ul>';
        }
    } catch ( Exception $e ) {
        echo '<p style="color: red;"><strong>tribe_get_organizers() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
    }
    
    // Test individual venue/organizer functions
    echo '<h3>Individual Object Functions Test</h3>';
    
    if ( ! empty( $venues ) ) {
        $first_venue = $venues[0];
        try {
            $venue_object = tribe_get_venue_object( $first_venue->ID, ARRAY_A );
            echo '<p><strong>tribe_get_venue_object():</strong> ' . ( is_array( $venue_object ) ? '✅ Working' : '❌ Failed' ) . '</p>';
            if ( is_array( $venue_object ) ) {
                echo '<p>Venue data keys: ' . implode( ', ', array_keys( $venue_object ) ) . '</p>';
            }
        } catch ( Exception $e ) {
            echo '<p style="color: red;"><strong>tribe_get_venue_object() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
        }
    }
    
    if ( ! empty( $organizers ) ) {
        $first_organizer = $organizers[0];
        try {
            $organizer_object = tribe_get_organizer_object( $first_organizer->ID, ARRAY_A );
            echo '<p><strong>tribe_get_organizer_object():</strong> ' . ( is_array( $organizer_object ) ? '✅ Working' : '❌ Failed' ) . '</p>';
            if ( is_array( $organizer_object ) ) {
                echo '<p>Organizer data keys: ' . implode( ', ', array_keys( $organizer_object ) ) . '</p>';
            }
        } catch ( Exception $e ) {
            echo '<p style="color: red;"><strong>tribe_get_organizer_object() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
        }
    }
    
    // Test our helper functions
    echo '<h3>Our Helper Functions Test</h3>';
    
    try {
        $helper_venues = \WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper::get_all_venues();
        echo '<p><strong>TEC_Helper::get_all_venues():</strong> ' . ( is_array( $helper_venues ) ? '✅ Working - Found ' . count( $helper_venues ) . ' venues' : '❌ Failed' ) . '</p>';
    } catch ( Exception $e ) {
        echo '<p style="color: red;"><strong>TEC_Helper::get_all_venues() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
    }
    
    try {
        $helper_organizers = \WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper::get_all_organizers();
        echo '<p><strong>TEC_Helper::get_all_organizers():</strong> ' . ( is_array( $helper_organizers ) ? '✅ Working - Found ' . count( $helper_organizers ) . ' organizers' : '❌ Failed' ) . '</p>';
    } catch ( Exception $e ) {
        echo '<p style="color: red;"><strong>TEC_Helper::get_all_organizers() Exception:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
    }
    
    echo '</div>';
}

// Add to admin footer for testing
add_action( 'admin_footer', 'test_tec_api_functions' ); 