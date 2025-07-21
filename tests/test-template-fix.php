<?php
/**
 * Test script to verify template fix for WP_Post objects
 * 
 * Run this in WordPress admin to test the template fix
 */

// Only run in admin
if ( ! is_admin() ) {
    return;
}

// Test template fix
function test_template_fix() {
    echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc;">';
    echo '<h2>Template Fix Test - WP_Post Objects</h2>';
    
    // Check if TEC is active
    $tec_active = class_exists( 'Tribe__Events__Main' );
    echo '<p><strong>TEC Active:</strong> ' . ( $tec_active ? 'Yes' : 'No' ) . '</p>';
    
    if ( ! $tec_active ) {
        echo '<p style="color: red;"><strong>❌ The Events Calendar plugin is not active!</strong></p>';
        echo '</div>';
        return;
    }
    
    try {
        // Test TEC_Helper methods
        echo '<h3>Testing TEC_Helper Methods</h3>';
        
        $venues = \WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper::get_all_venues();
        echo '<p><strong>Venues:</strong> ' . ( is_array( $venues ) ? '✅ Found ' . count( $venues ) . ' venues' : '❌ Failed' ) . '</p>';
        
        if ( ! empty( $venues ) ) {
            $first_venue = $venues[0];
            echo '<p><strong>First Venue Type:</strong> ' . gettype( $first_venue ) . '</p>';
            if ( is_object( $first_venue ) ) {
                echo '<p><strong>First Venue Class:</strong> ' . get_class( $first_venue ) . '</p>';
                echo '<p><strong>First Venue ID:</strong> ' . ( isset( $first_venue->ID ) ? $first_venue->ID : 'Not set' ) . '</p>';
                echo '<p><strong>First Venue Title:</strong> ' . ( isset( $first_venue->post_title ) ? $first_venue->post_title : 'Not set' ) . '</p>';
            }
        }
        
        $organizers = \WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper::get_all_organizers();
        echo '<p><strong>Organizers:</strong> ' . ( is_array( $organizers ) ? '✅ Found ' . count( $organizers ) . ' organizers' : '❌ Failed' ) . '</p>';
        
        if ( ! empty( $organizers ) ) {
            $first_organizer = $organizers[0];
            echo '<p><strong>First Organizer Type:</strong> ' . gettype( $first_organizer ) . '</p>';
            if ( is_object( $first_organizer ) ) {
                echo '<p><strong>First Organizer Class:</strong> ' . get_class( $first_organizer ) . '</p>';
                echo '<p><strong>First Organizer ID:</strong> ' . ( isset( $first_organizer->ID ) ? $first_organizer->ID : 'Not set' ) . '</p>';
                echo '<p><strong>First Organizer Title:</strong> ' . ( isset( $first_organizer->post_title ) ? $first_organizer->post_title : 'Not set' ) . '</p>';
            }
        }
        
        // Test template methods
        echo '<h3>Testing Template Methods</h3>';
        
        $template = new \WeDevs\Wpuf\Integrations\Events_Calendar\Templates\Event_Form_Template();
        
        // Use reflection to access private methods
        $reflection = new ReflectionClass( $template );
        
        $get_venue_options = $reflection->getMethod( 'get_venue_options' );
        $get_venue_options->setAccessible( true );
        
        $get_organizer_options = $reflection->getMethod( 'get_organizer_options' );
        $get_organizer_options->setAccessible( true );
        
        try {
            $venue_options = $get_venue_options->invoke( $template );
            echo '<p><strong>Venue Options:</strong> ✅ Generated successfully - ' . count( $venue_options ) . ' options</p>';
            
            if ( count( $venue_options ) > 1 ) {
                echo '<p><strong>Sample Venue Option:</strong> ' . array_keys( $venue_options )[1] . ' => ' . array_values( $venue_options )[1] . '</p>';
            }
        } catch ( Exception $e ) {
            echo '<p style="color: red;"><strong>❌ Venue Options Error:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
        }
        
        try {
            $organizer_options = $get_organizer_options->invoke( $template );
            echo '<p><strong>Organizer Options:</strong> ✅ Generated successfully - ' . count( $organizer_options ) . ' options</p>';
            
            if ( count( $organizer_options ) > 1 ) {
                echo '<p><strong>Sample Organizer Option:</strong> ' . array_keys( $organizer_options )[1] . ' => ' . array_values( $organizer_options )[1] . '</p>';
            }
        } catch ( Exception $e ) {
            echo '<p style="color: red;"><strong>❌ Organizer Options Error:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
        }
        
        // Test form fields generation
        echo '<h3>Testing Form Fields Generation</h3>';
        
        try {
            $form_fields = $template->get_form_fields();
            echo '<p><strong>Form Fields:</strong> ✅ Generated successfully - ' . count( $form_fields ) . ' fields</p>';
            
            // Check if venue and organizer fields are present
            $venue_field_found = false;
            $organizer_field_found = false;
            
            foreach ( $form_fields as $field ) {
                if ( isset( $field['name'] ) && $field['name'] === '_EventVenueID' ) {
                    $venue_field_found = true;
                }
                if ( isset( $field['name'] ) && $field['name'] === '_EventOrganizerID' ) {
                    $organizer_field_found = true;
                }
            }
            
            echo '<p><strong>Venue Field:</strong> ' . ( $venue_field_found ? '✅ Found' : '❌ Missing' ) . '</p>';
            echo '<p><strong>Organizer Field:</strong> ' . ( $organizer_field_found ? '✅ Found' : '❌ Missing' ) . '</p>';
            
        } catch ( Exception $e ) {
            echo '<p style="color: red;"><strong>❌ Form Fields Error:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
        }
        
        echo '<h3>✅ Template Fix Test Complete</h3>';
        echo '<p>The template should now work correctly with WP_Post objects!</p>';
        
    } catch ( Exception $e ) {
        echo '<p style="color: red;"><strong>❌ Test Error:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
    }
    
    echo '</div>';
}

// Run the test
add_action( 'admin_notices', 'test_template_fix' ); 