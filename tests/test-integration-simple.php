<?php
/**
 * Simple test script to verify Events Calendar integration
 * 
 * This script can be run in a WordPress context to test:
 * 1. Integration loading
 * 2. Template registration
 * 3. Basic functionality
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Test 1: Check if TEC is active
echo "=== Test 1: TEC Active Check ===\n";
if ( class_exists( 'Tribe__Events__Main' ) ) {
    echo "✅ TEC is active\n";
    echo "TEC Version: " . \Tribe__Events__Main::VERSION . "\n";
} else {
    echo "❌ TEC is not active\n";
    exit;
}

// Test 2: Check if our integration is loaded
echo "\n=== Test 2: Integration Loading ===\n";
if ( class_exists( 'WeDevs\Wpuf\Integrations\Events_Calendar\Events_Calendar_Integration' ) ) {
    echo "✅ Events Calendar Integration class exists\n";
} else {
    echo "❌ Events Calendar Integration class not found\n";
}

// Test 3: Check if template class exists
echo "\n=== Test 3: Template Class Check ===\n";
if ( class_exists( 'WeDevs\Wpuf\Integrations\Events_Calendar\Templates\Event_Form_Template' ) ) {
    echo "✅ Event Form Template class exists\n";
} else {
    echo "❌ Event Form Template class not found\n";
}

// Test 4: Test template instantiation
echo "\n=== Test 4: Template Instantiation ===\n";
try {
    $template = new \WeDevs\Wpuf\Integrations\Events_Calendar\Templates\Event_Form_Template();
    echo "✅ Template instantiated successfully\n";
} catch ( Exception $e ) {
    echo "❌ Template instantiation failed: " . $e->getMessage() . "\n";
}

// Test 5: Test template fields generation
echo "\n=== Test 5: Template Fields Generation ===\n";
try {
    $template = new \WeDevs\Wpuf\Integrations\Events_Calendar\Templates\Event_Form_Template();
    $fields = $template->get_form_fields();
    
    if ( is_array( $fields ) && ! empty( $fields ) ) {
        echo "✅ Template fields generated successfully\n";
        echo "Number of fields: " . count( $fields ) . "\n";
        
        // List the field names
        echo "Field names:\n";
        foreach ( $fields as $field ) {
            if ( isset( $field['name'] ) ) {
                echo "  - " . $field['name'] . " (" . $field['input_type'] . ")\n";
            }
        }
    } else {
        echo "❌ Template fields generation failed\n";
    }
} catch ( Exception $e ) {
    echo "❌ Template fields generation failed: " . $e->getMessage() . "\n";
}

// Test 6: Test TEC API functions
echo "\n=== Test 6: TEC API Functions Check ===\n";
$tec_functions = [
    'tribe_get_venues' => 'Get venues',
    'tribe_get_organizers' => 'Get organizers',
    'tribe_get_venue_object' => 'Get venue object',
    'tribe_get_organizer_object' => 'Get organizer object',
    'tribe_get_event' => 'Get event',
    'tribe_create_event' => 'Create event',
    'tribe_create_venue' => 'Create venue',
    'tribe_create_organizer' => 'Create organizer',
];

foreach ( $tec_functions as $function => $description ) {
    if ( function_exists( $function ) ) {
        echo "✅ $function: $description\n";
    } else {
        echo "❌ $function: $description (not available)\n";
    }
}

// Test 7: Test handlers
echo "\n=== Test 7: Handlers Check ===\n";
$handlers = [
    'Event_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler',
    'Venue_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Venue_Handler',
    'Organizer_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Organizer_Handler',
];

foreach ( $handlers as $name => $class ) {
    if ( class_exists( $class ) ) {
        echo "✅ $name class exists\n";
    } else {
        echo "❌ $name class not found\n";
    }
}

// Test 8: Test validators
echo "\n=== Test 8: Validators Check ===\n";
$validators = [
    'Event_Validator' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Event_Validator',
    'Date_Validator' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Date_Validator',
];

foreach ( $validators as $name => $class ) {
    if ( class_exists( $class ) ) {
        echo "✅ $name class exists\n";
    } else {
        echo "❌ $name class not found\n";
    }
}

echo "\n=== Test Summary ===\n";
echo "All tests completed. Check the output above for any issues.\n";
echo "If all tests pass, the Events Calendar integration should be working properly.\n"; 