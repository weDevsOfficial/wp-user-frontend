<?php
/**
 * TEC Integration Test Example
 * 
 * This file demonstrates how all the Events Calendar components work together
 * 
 * @since 3.6.0
 */

// Include the main integration class
require_once __DIR__ . '/../includes/Integrations/Events_Calendar/Events_Calendar_Integration.php';

use WeDevs\Wpuf\Integrations\Events_Calendar\Events_Calendar_Integration;

/**
 * Example integration test showing complete TEC workflow
 */
function test_tec_integration() {
    echo "=== TEC Integration Test Example ===\n\n";

    // Initialize the integration
    $integration = new Events_Calendar_Integration();
    
    echo "✅ TEC Integration initialized successfully\n";

    // Simulate form submission data
    $form_data = [
        'post_title' => 'Community Meetup',
        'post_content' => 'Join us for an exciting community meetup!',
        'EventStartDate' => '2024-02-15',
        'EventEndDate' => '2024-02-15',
        'EventStartHour' => '18',
        'EventStartMinute' => '00',
        'EventEndHour' => '20',
        'EventEndMinute' => '00',
        'EventTimezone' => 'America/New_York',
        'EventAllDay' => 'no',
        'EventCost' => 'Free',
        'EventURL' => 'https://example.com/meetup',
        'venue' => [
            'Venue' => 'Community Center',
            'Address' => '123 Main Street, City, State 12345',
            'Phone' => '555-123-4567',
            'Website' => 'https://communitycenter.com'
        ],
        'organizer' => [
            'Organizer' => 'Community Events Team',
            'Email' => 'events@community.com',
            'Phone' => '555-987-6543',
            'Website' => 'https://communityevents.com'
        ]
    ];

    echo "📝 Form data prepared for submission\n";

    // Test validation
    echo "\n🔍 Testing validation...\n";
    
    // This would normally be called by the form submission handler
    // For demonstration, we'll show the validation process
    $validation_result = validate_form_data($form_data);
    
    if ($validation_result === true) {
        echo "✅ Form data validation passed\n";
    } else {
        echo "❌ Form data validation failed:\n";
        print_r($validation_result);
        return;
    }

    // Test event creation
    echo "\n📅 Testing event creation...\n";
    
    $event_result = create_event($form_data);
    
    if ($event_result) {
        echo "✅ Event created successfully\n";
        echo "📊 Event ID: " . $event_result . "\n";
    } else {
        echo "❌ Event creation failed\n";
    }

    // Test venue creation
    echo "\n🏢 Testing venue creation...\n";
    
    $venue_result = create_venue($form_data['venue']);
    
    if ($venue_result) {
        echo "✅ Venue created successfully\n";
        echo "📊 Venue ID: " . $venue_result . "\n";
    } else {
        echo "❌ Venue creation failed\n";
    }

    // Test organizer creation
    echo "\n👥 Testing organizer creation...\n";
    
    $organizer_result = create_organizer($form_data['organizer']);
    
    if ($organizer_result) {
        echo "✅ Organizer created successfully\n";
        echo "📊 Organizer ID: " . $organizer_result . "\n";
    } else {
        echo "❌ Organizer creation failed\n";
    }

    echo "\n=== Integration Test Complete ===\n";
}

/**
 * Simulate form data validation
 * 
 * @param array $form_data Form data to validate
 * @return bool|array True if valid, array of errors if invalid
 */
function validate_form_data($form_data) {
    // In a real implementation, this would use the validators
    // For demonstration, we'll do basic validation
    
    $errors = [];
    
    // Check required fields
    $required_fields = [
        'post_title', 'EventStartDate', 'EventEndDate',
        'EventStartHour', 'EventStartMinute', 'EventEndHour', 'EventEndMinute'
    ];
    
    foreach ($required_fields as $field) {
        if (!isset($form_data[$field]) || empty($form_data[$field])) {
            $errors[] = "Required field '$field' is missing";
        }
    }
    
    // Check date validity
    if (isset($form_data['EventStartDate']) && isset($form_data['EventEndDate'])) {
        $start_date = strtotime($form_data['EventStartDate']);
        $end_date = strtotime($form_data['EventEndDate']);
        
        if ($start_date === false || $end_date === false) {
            $errors[] = "Invalid date format";
        } elseif ($end_date <= $start_date) {
            $errors[] = "End date must be after start date";
        }
    }
    
    // Check time validity
    $time_fields = ['EventStartHour', 'EventStartMinute', 'EventEndHour', 'EventEndMinute'];
    foreach ($time_fields as $field) {
        if (isset($form_data[$field])) {
            $value = (int) $form_data[$field];
            if ($value < 0 || $value > 59) {
                $errors[] = "Invalid time value for $field";
            }
        }
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Simulate event creation
 * 
 * @param array $form_data Form data
 * @return int|false Event ID or false on failure
 */
function create_event($form_data) {
    // In a real implementation, this would use the Event Handler
    // For demonstration, we'll simulate successful creation
    
    // Simulate some processing time
    usleep(100000); // 0.1 seconds
    
    // Return a simulated event ID
    return rand(1000, 9999);
}

/**
 * Simulate venue creation
 * 
 * @param array $venue_data Venue data
 * @return int|false Venue ID or false on failure
 */
function create_venue($venue_data) {
    // In a real implementation, this would use the Venue Handler
    // For demonstration, we'll simulate successful creation
    
    if (empty($venue_data['Venue'])) {
        return false;
    }
    
    // Simulate some processing time
    usleep(50000); // 0.05 seconds
    
    // Return a simulated venue ID
    return rand(100, 999);
}

/**
 * Simulate organizer creation
 * 
 * @param array $organizer_data Organizer data
 * @return int|false Organizer ID or false on failure
 */
function create_organizer($organizer_data) {
    // In a real implementation, this would use the Organizer Handler
    // For demonstration, we'll simulate successful creation
    
    if (empty($organizer_data['Organizer'])) {
        return false;
    }
    
    // Simulate some processing time
    usleep(50000); // 0.05 seconds
    
    // Return a simulated organizer ID
    return rand(100, 999);
}

// Run the integration test
if (php_sapi_name() === 'cli') {
    test_tec_integration();
} 