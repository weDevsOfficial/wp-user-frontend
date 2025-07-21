<?php
/**
 * Test script to check if Events Calendar template is registered
 * 
 * Run this in WordPress admin to test template registration
 */

// Only run in admin
if ( ! is_admin() ) {
    return;
}

// Test template registration
function test_tec_template_registration() {
    echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
    echo '<h2>Events Calendar Template Registration Test</h2>';
    
    // Check if TEC is active
    $tec_active = class_exists( 'Tribe__Events__Main' );
    echo '<p><strong>TEC Active:</strong> ' . ( $tec_active ? 'Yes' : 'No' ) . '</p>';
    
    // Get form templates
    $templates = apply_filters( 'wpuf_get_post_form_templates', [] );
    echo '<p><strong>Total Templates:</strong> ' . count( $templates ) . '</p>';
    
    // Check for Events Calendar template
    $tec_template_found = false;
    $tec_template_key = '';
    
    foreach ( $templates as $key => $template ) {
        echo '<p><strong>Template Key:</strong> ' . esc_html( $key ) . '</p>';
        echo '<p><strong>Template Class:</strong> ' . get_class( $template ) . '</p>';
        echo '<p><strong>Template Title:</strong> ' . esc_html( $template->get_title() ) . '</p>';
        echo '<p><strong>Template Enabled:</strong> ' . ( $template->enabled ? 'Yes' : 'No' ) . '</p>';
        echo '<hr>';
        
        if ( strpos( $key, 'events_calendar' ) !== false ) {
            $tec_template_found = true;
            $tec_template_key = $key;
        }
    }
    
    if ( $tec_template_found ) {
        echo '<p style="color: green;"><strong>✅ Events Calendar template found!</strong></p>';
        echo '<p><strong>Template Key:</strong> ' . esc_html( $tec_template_key ) . '</p>';
    } else {
        echo '<p style="color: red;"><strong>❌ Events Calendar template NOT found!</strong></p>';
    }
    
    // Check if our integration is loaded
    $integration_loaded = false;
    if ( isset( wpuf()->container['integrations'] ) ) {
        $integrations = wpuf()->container['integrations'];
        if ( isset( $integrations->container['tribe__events__main'] ) ) {
            $integration_loaded = true;
        }
    }
    
    echo '<p><strong>Integration Loaded:</strong> ' . ( $integration_loaded ? 'Yes' : 'No' ) . '</p>';
    
    echo '</div>';
}

// Add to admin footer for testing
add_action( 'admin_footer', 'test_tec_template_registration' ); 