<?php
/**
 * Test script to simulate complete event creation flow
 * 
 * This script tests:
 * 1. Event data validation
 * 2. Date handling and conversion
 * 3. Venue creation and handling
 * 4. Organizer creation and handling
 * 5. Event creation with TEC API
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo "=== Events Calendar Integration - Event Creation Flow Test ===\n\n";

// Test 1: Check if all required classes exist
echo "=== Test 1: Required Classes Check ===\n";
$required_classes = [
    'Events_Calendar_Integration' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Events_Calendar_Integration',
    'Event_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler',
    'Venue_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Venue_Handler',
    'Organizer_Handler' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Organizer_Handler',
    'Event_Validator' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Event_Validator',
    'Date_Validator' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Date_Validator',
    'TEC_Compatibility_Manager' => 'WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager',
];

$all_classes_exist = true;
foreach ( $required_classes as $name => $class ) {
    if ( class_exists( $class ) ) {
        echo "✅ $name class exists\n";
    } else {
        echo "❌ $name class not found\n";
        $all_classes_exist = false;
    }
}

if ( ! $all_classes_exist ) {
    echo "\n❌ Some required classes are missing. Cannot proceed with tests.\n";
    exit;
}

// Test 2: Test Event Validator
echo "\n=== Test 2: Event Validator ===\n";
try {
    $event_validator = new \WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Event_Validator();
    echo "✅ Event Validator instantiated\n";
    
    // Test with valid event data
    $valid_event_data = [
        'post_title' => 'Test Event',
        'post_content' => 'This is a test event',
        '_EventStartDate' => '2024-01-15 10:00:00',
        '_EventEndDate' => '2024-01-15 12:00:00',
        '_EventAllDay' => 'no',
        '_EventURL' => 'https://example.com',
        '_EventCost' => '25.00',
        '_EventCurrencySymbol' => '$',
    ];
    
    $validation_result = $event_validator->validate_event_data( $valid_event_data );
    if ( $validation_result ) {
        echo "✅ Event validation passed with valid data\n";
    } else {
        echo "❌ Event validation failed with valid data\n";
    }
    
    // Test with invalid event data (missing required fields)
    $invalid_event_data = [
        'post_title' => '',
        'post_content' => 'This is a test event',
        '_EventStartDate' => '2024-01-15 10:00:00',
        '_EventEndDate' => '2024-01-15 12:00:00',
    ];
    
    $validation_result = $event_validator->validate_event_data( $invalid_event_data );
    if ( ! $validation_result ) {
        echo "✅ Event validation correctly failed with invalid data\n";
    } else {
        echo "❌ Event validation incorrectly passed with invalid data\n";
    }
    
} catch ( Exception $e ) {
    echo "❌ Event Validator test failed: " . $e->getMessage() . "\n";
}

// Test 3: Test Date Validator
echo "\n=== Test 3: Date Validator ===\n";
try {
    $date_validator = new \WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Date_Validator();
    echo "✅ Date Validator instantiated\n";
    
    // Test with valid date data
    $valid_date_data = [
        '_EventStartDate' => '2024-01-15 10:00:00',
        '_EventEndDate' => '2024-01-15 12:00:00',
        '_EventAllDay' => 'no',
    ];
    
    $validation_result = $date_validator->validate_date_data( $valid_date_data );
    if ( $validation_result ) {
        echo "✅ Date validation passed with valid data\n";
    } else {
        echo "❌ Date validation failed with valid data\n";
    }
    
    // Test with invalid date data (end date before start date)
    $invalid_date_data = [
        '_EventStartDate' => '2024-01-15 12:00:00',
        '_EventEndDate' => '2024-01-15 10:00:00',
        '_EventAllDay' => 'no',
    ];
    
    $validation_result = $date_validator->validate_date_data( $invalid_date_data );
    if ( ! $validation_result ) {
        echo "✅ Date validation correctly failed with invalid data\n";
    } else {
        echo "❌ Date validation incorrectly passed with invalid data\n";
    }
    
} catch ( Exception $e ) {
    echo "❌ Date Validator test failed: " . $e->getMessage() . "\n";
}

// Test 4: Test Venue Handler
echo "\n=== Test 4: Venue Handler ===\n";
try {
    $compatibility_manager = new \WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager();
    $venue_handler = new \WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Venue_Handler( $compatibility_manager );
    echo "✅ Venue Handler instantiated\n";
    
    // Test venue data building
    $venue_data = [
        'venue_name' => 'Test Venue',
        'venue_address' => '123 Test Street',
        'venue_city' => 'Test City',
        'venue_state' => 'Test State',
        'venue_zip' => '12345',
        'venue_country' => 'Test Country',
        'venue_phone' => '555-123-4567',
        'venue_url' => 'https://testvenue.com',
    ];
    
    $built_venue_data = $venue_handler->build_venue_data( $venue_data );
    if ( is_array( $built_venue_data ) ) {
        echo "✅ Venue data building completed\n";
        echo "Built venue data keys: " . implode( ', ', array_keys( $built_venue_data ) ) . "\n";
    } else {
        echo "❌ Venue data building failed\n";
    }
    
} catch ( Exception $e ) {
    echo "❌ Venue Handler test failed: " . $e->getMessage() . "\n";
}

// Test 5: Test Organizer Handler
echo "\n=== Test 5: Organizer Handler ===\n";
try {
    $compatibility_manager = new \WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager();
    $organizer_handler = new \WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Organizer_Handler( $compatibility_manager );
    echo "✅ Organizer Handler instantiated\n";
    
    // Test organizer data building
    $organizer_data = [
        'organizer_name' => 'Test Organizer',
        'organizer_email' => 'test@example.com',
        'organizer_phone' => '555-987-6543',
        'organizer_website' => 'https://testorganizer.com',
    ];
    
    $built_organizer_data = $organizer_handler->build_organizer_data( $organizer_data );
    if ( is_array( $built_organizer_data ) ) {
        echo "✅ Organizer data building completed\n";
        echo "Built organizer data keys: " . implode( ', ', array_keys( $built_organizer_data ) ) . "\n";
    } else {
        echo "❌ Organizer data building failed\n";
    }
    
} catch ( Exception $e ) {
    echo "❌ Organizer Handler test failed: " . $e->getMessage() . "\n";
}

// Test 6: Test Event Handler
echo "\n=== Test 6: Event Handler ===\n";
try {
    $compatibility_manager = new \WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager();
    $event_handler = new \WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler( $compatibility_manager );
    echo "✅ Event Handler instantiated\n";
    
    // Test event data building
    $event_data = [
        'post_title' => 'Test Event',
        'post_content' => 'This is a test event',
        '_EventStartDate' => '2024-01-15 10:00:00',
        '_EventEndDate' => '2024-01-15 12:00:00',
        '_EventAllDay' => 'no',
        '_EventURL' => 'https://example.com',
        '_EventCost' => '25.00',
        '_EventCurrencySymbol' => '$',
    ];
    
    // Use reflection to access private method
    $reflection = new ReflectionClass( $event_handler );
    $build_event_data_method = $reflection->getMethod( 'build_event_data' );
    $build_event_data_method->setAccessible( true );
    $built_event_data = $build_event_data_method->invoke( $event_handler, $event_data );
    if ( is_array( $built_event_data ) ) {
        echo "✅ Event data building completed\n";
        echo "Built event data keys: " . implode( ', ', array_keys( $built_event_data ) ) . "\n";
    } else {
        echo "❌ Event data building failed\n";
    }
    
} catch ( Exception $e ) {
    echo "❌ Event Handler test failed: " . $e->getMessage() . "\n";
}

// Test 7: Test Compatibility Manager
echo "\n=== Test 7: Compatibility Manager ===\n";
try {
    $compatibility_manager = new \WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager();
    echo "✅ Compatibility Manager instantiated\n";
    
    // Use reflection to access private methods
    $reflection = new ReflectionClass( $compatibility_manager );
    
    $get_tec_version_method = $reflection->getMethod( 'get_tec_version' );
    $get_tec_version_method->setAccessible( true );
    $tec_version = $get_tec_version_method->invoke( $compatibility_manager );
    echo "TEC Version detected: $tec_version\n";
    
    $get_handler_method = $reflection->getMethod( 'get_compatibility_handler' );
    $get_handler_method->setAccessible( true );
    $handler = $get_handler_method->invoke( $compatibility_manager );
    echo "Compatibility Handler: " . get_class( $handler ) . "\n";
    
} catch ( Exception $e ) {
    echo "❌ Compatibility Manager test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "All event creation flow tests completed.\n";
echo "If all tests pass, the Events Calendar integration should be working properly.\n";
echo "The next step would be to test actual event creation with real data.\n"; 