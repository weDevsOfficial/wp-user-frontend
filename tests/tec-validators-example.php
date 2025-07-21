<?php
/**
 * TEC Validators Test Example
 * 
 * This file demonstrates how to use the new Events Calendar validators
 * 
 * @since 3.6.0
 */

// Include the validators
require_once __DIR__ . '/../includes/Integrations/Events_Calendar/Validators/Event_Validator.php';
require_once __DIR__ . '/../includes/Integrations/Events_Calendar/Validators/Date_Validator.php';

use WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Event_Validator;
use WeDevs\Wpuf\Integrations\Events_Calendar\Validators\Date_Validator;

/**
 * Example usage of TEC Validators
 */
function test_tec_validators() {
    echo "=== TEC Validators Test Examples ===\n\n";

    // Initialize validators
    $event_validator = new Event_Validator();
    $date_validator = new Date_Validator();

    // Test 1: Valid event data
    echo "Test 1: Valid Event Data\n";
    $valid_event_data = [
        'post_title' => 'Test Event',
        'post_content' => 'This is a test event',
        'EventStartDate' => '2024-01-15',
        'EventEndDate' => '2024-01-15',
        'EventStartHour' => '14',
        'EventStartMinute' => '30',
        'EventEndHour' => '16',
        'EventEndMinute' => '00',
        'EventTimezone' => 'America/New_York',
        'EventAllDay' => 'no',
        'EventCost' => '25.00',
        'EventURL' => 'https://example.com/event',
        'venue' => [
            'Venue' => 'Test Venue',
            'Address' => '123 Test Street',
            'Phone' => '555-123-4567',
            'Website' => 'https://testvenue.com'
        ],
        'organizer' => [
            'Organizer' => 'Test Organizer',
            'Email' => 'organizer@example.com',
            'Phone' => '555-987-6543',
            'Website' => 'https://testorganizer.com'
        ]
    ];

    $result = $event_validator->validate_event_data($valid_event_data);
    if ($result === true) {
        echo "✅ Valid event data passed validation\n";
    } else {
        echo "❌ Valid event data failed validation:\n";
        print_r($result);
    }

    // Test 2: Invalid event data (missing required fields)
    echo "\nTest 2: Invalid Event Data (Missing Required Fields)\n";
    $invalid_event_data = [
        'post_title' => 'Test Event',
        'EventStartDate' => '2024-01-15',
        // Missing EventEndDate, EventStartHour, etc.
    ];

    $result = $event_validator->validate_event_data($invalid_event_data);
    if ($result === true) {
        echo "❌ Invalid event data incorrectly passed validation\n";
    } else {
        echo "✅ Invalid event data correctly failed validation:\n";
        print_r($result);
    }

    // Test 3: Invalid date range (end before start)
    echo "\nTest 3: Invalid Date Range (End Before Start)\n";
    $invalid_date_data = [
        'EventStartDate' => '2024-01-15',
        'EventEndDate' => '2024-01-14', // End date before start date
        'EventStartHour' => '14',
        'EventStartMinute' => '30',
        'EventEndHour' => '16',
        'EventEndMinute' => '00'
    ];

    $result = $date_validator->validate_date_data($invalid_date_data);
    if ($result === true) {
        echo "❌ Invalid date range incorrectly passed validation\n";
    } else {
        echo "✅ Invalid date range correctly failed validation:\n";
        print_r($result);
    }

    // Test 4: Valid date data
    echo "\nTest 4: Valid Date Data\n";
    $valid_date_data = [
        'EventStartDate' => '2024-01-15',
        'EventEndDate' => '2024-01-15',
        'EventStartHour' => '14',
        'EventStartMinute' => '30',
        'EventEndHour' => '16',
        'EventEndMinute' => '00',
        'EventTimezone' => 'America/New_York',
        'EventAllDay' => 'no'
    ];

    $result = $date_validator->validate_date_data($valid_date_data);
    if ($result === true) {
        echo "✅ Valid date data passed validation\n";
    } else {
        echo "❌ Valid date data failed validation:\n";
        print_r($result);
    }

    // Test 5: All-day event validation
    echo "\nTest 5: All-Day Event Validation\n";
    $allday_event_data = [
        'EventStartDate' => '2024-01-15',
        'EventEndDate' => '2024-01-15',
        'EventStartHour' => '0',
        'EventStartMinute' => '0',
        'EventEndHour' => '0',
        'EventEndMinute' => '0',
        'EventAllDay' => 'yes'
    ];

    $result = $date_validator->validate_date_data($allday_event_data);
    if ($result === true) {
        echo "✅ All-day event data passed validation\n";
    } else {
        echo "❌ All-day event data failed validation:\n";
        print_r($result);
    }

    // Test 6: Field-specific validation
    echo "\nTest 6: Field-Specific Validation\n";
    $field_errors = $event_validator->validate_field($valid_event_data, 'post_title');
    if (empty($field_errors)) {
        echo "✅ Field-specific validation passed for post_title\n";
    } else {
        echo "❌ Field-specific validation failed for post_title:\n";
        print_r($field_errors);
    }

    echo "\n=== Test Examples Complete ===\n";
}

// Run the test examples
if (php_sapi_name() === 'cli') {
    test_tec_validators();
} 