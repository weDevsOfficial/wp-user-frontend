# The Events Calendar - Event Creation Process

This document outlines the complete process of how a new event is created in The Events Calendar plugin, based on analysis of the codebase.

## Overview

The Events Calendar plugin creates events through multiple pathways:
1. **Admin Interface** - WordPress admin panel
2. **REST API** - Programmatic creation via API endpoints
3. **Import/Export** - Bulk creation via CSV/ICS files
4. **Event Aggregator** - Import from external sources
5. **WPUF Integration** - Frontend form creation with ORM API

## Core Components

### 1. Main Plugin Class
- **File**: `the-events-calendar/src/Tribe/Main.php`
- **Class**: `Tribe__Events__Main`
- **Purpose**: Central plugin initialization and event registration

### 2. Event API
- **File**: `the-events-calendar/src/Tribe/API.php`
- **Class**: `Tribe__Events__API`
- **Purpose**: REST API endpoints for event management

### 3. Event Model
- **File**: `the-events-calendar/src/Tribe/Models/Post_Types/Event.php`
- **Class**: `Tribe\Events\Models\Post_Types\Event`
- **Purpose**: Event data structure and property management

### 4. WPUF Integration
- **File**: `wp-user-frontend/includes/Integrations/Events_Calendar/`
- **Purpose**: Frontend form integration with TEC ORM API

## WPUF Integration Architecture

### Hook System
The integration uses a custom hook system to intercept WPUF form submissions:

```php
// Hook registration in Event_Handler constructor
add_action( 'wpuf_post_created_tribe_events', [ $this, 'handle_event_creation' ], 10, 3 );

// Hook fired in Frontend_Form_Ajax.php after post creation
do_action( 'wpuf_post_created_' . $post_type, $post_id, $form_data, $meta_vars );
```

### Flow
1. **WPUF creates WordPress post** (`tribe_events` post type)
2. **Hook fires** with post ID and form data
3. **Event_Handler processes** data using TEC ORM API
4. **ORM updates** the existing post with TEC-specific data
5. **Venue/Organizer** creation and association handled separately

## API-Based Creation

### REST API Endpoint
- **File**: `the-events-calendar/src/Tribe/REST/V1/Endpoints/Single_Event.php`
- **Endpoint**: `/wp-json/tribe/events/v1/events`
- **Method**: `POST`

### Programmatic Creation

#### ORM Approach (v6+ Recommended)
The Events Calendar v6+ provides an ORM-based API for cleaner event creation:

```php
// Using the ORM API (v6+)
$result = tribe_events()
    ->set_args([
        'title'       => 'Programmatically Created Event',
        'description' => 'Event description here',
        'start_date'  => '2024-01-01 10:00:00',
        'end_date'    => '2024-01-01 11:00:00',
        'all_day'     => false,
        'timezone'    => 'America/New_York',
        'cost'        => '10.00',
        'featured'    => true,
        'sticky'      => false,
        'venue'       => 123, // Venue post ID
        'organizer'   => 456, // Organizer post ID
    ])
    ->create();
```

**Note:** Venue and organizer must be created separately first:

```php
// Create venue first
$venue_id = tribe_venues()
    ->set_args([
        'title'    => 'Event Venue',
        'address'  => '123 Main St',
        'city'     => 'New York',
        'state'    => 'NY',
        'zip'      => '10001',
        'country'  => 'US',
    ])
    ->create();

// Create organizer first
$organizer_id = tribe_organizers()
    ->set_args([
        'title'  => 'Event Organizer',
        'phone'  => '555-1234',
        'email'  => 'organizer@example.com',
        'website' => 'https://example.com',
    ])
    ->create();

// Then use IDs in event creation
$event = tribe_events()
    ->set_args([
        'title'     => 'My Event',
        'venue'     => $venue_id,
        'organizer' => $organizer_id,
        // ... other fields
    ])
    ->create();
```

#### Legacy Approach (Pre-v6)
```php
// Using legacy tribe_create_event() function
$event_id = tribe_create_event([
    'post_title'    => 'Legacy Event',
    'EventStartDate' => '2024-01-01 10:00:00',
    'EventEndDate'   => '2024-01-01 11:00:00',
    'EventAllDay'    => 'no',
    'EventTimezone'  => 'America/New_York',
    'EventCost'      => '10.00',
    'EventFeatured'  => 'yes',
    'EventSticky'    => 'no',
]);
```

## Data Validation

### ORM Validation Requirements
The TEC ORM API has strict validation requirements:

1. **Title is always required** - No event can be created without a title
2. **Date requirements are flexible** - One of these combinations must be provided:
   - `start_date` + `end_date` (most common)
   - `start_date` + `duration` (alternative)
   - `all_day = true` (for all-day events, start_date still needed)

**Validation Failure:** If requirements are not met, the ORM will return `false` and no event will be created.

### Minimum Required Fields
Based on the Event model analysis and TEC ORM requirements, the following fields are **absolutely required** to create a valid event:

#### ORM Approach (v6+)
**Required:**
1. **`title`** - Event title (maps to `post_title`)

**AND one of the following combinations:**
- **`start_date`** + **`end_date`** - Start and end dates (YYYY-MM-DD HH:MM:SS format)
- **`start_date`** + **`duration`** - Start date and duration in seconds
- **`all_day`** = `true` - For all-day events (start_date still required)

**Important Note:** If any of these requirements are missing, `tribe_events()->set_args($args)->create()` will return `false` and no event will be created.

#### Legacy Approach (Pre-v6)
1. **`post_title`** - Event title (WordPress post field)
2. **`_EventStartDate`** - Start date (YYYY-MM-DD format)
3. **`_EventEndDate`** - End date (YYYY-MM-DD format)

### Required Meta Fields
These meta fields are automatically generated but require the above data:

- **`_EventStartDateUTC`** - UTC start date
- **`_EventEndDateUTC`** - UTC end date
- **`_EventDuration`** - Event duration in seconds
- **`_EventTimezone`** - Event timezone (defaults to site timezone)

### Optional Fields

#### ORM Properties (v6+)
- **`description`** - Event description (maps to `post_content`)
- **`all_day`** - Boolean for all-day events (maps to `_EventAllDay`)
- **`timezone`** - Event timezone (defaults to site timezone)
- **`cost`** - Event cost (maps to `_EventCost`)
- **`featured`** - Boolean for featured events (maps to `_EventFeatured`)
- **`sticky`** - Boolean for sticky events (maps to `menu_order`)
- **`venue`** - Venue post ID (integer)
- **`organizer`** - Organizer post ID (integer)

#### Legacy Meta Fields (Pre-v6)
- **`_EventAllDay`** - All-day event flag ('yes'/'no')
- **`_EventCost`** - Event cost (string)
- **`_EventFeatured`** - Featured event flag ('yes'/'no')
- **`_EventSticky`** - Sticky event flag ('yes'/'no')
- **`_EventURL`** - Event website URL
- **`_EventCurrencySymbol`** - Currency symbol for cost
- **`_EventShowMap`** - Show map flag ('yes'/'no')
- **`_EventShowMapLink`** - Show map link flag ('yes'/'no')
- **`_EventVenueID`** - Associated venue post ID
- **`_EventOrganizerID`** - Associated organizer post ID

## Event Model Properties

The Event model (`Tribe\Events\Models\Post_Types\Event`) provides a comprehensive data structure:

### Core Event Properties
- **`start_date`** - Event start date string
- **`start_date_utc`** - Event start date in UTC
- **`end_date`** - Event end date string  
- **`end_date_utc`** - Event end date in UTC
- **`duration`** - Event duration in seconds
- **`timezone`** - Event timezone string
- **`all_day`** - Boolean indicating if it's an all-day event

### Date Objects
- **`dates`** - Object containing various date representations:
  - `start` - Start date object
  - `start_utc` - Start date in UTC
  - `start_site` - Start date in site timezone
  - `start_display` - Start date for display
  - `end` - End date object
  - `end_utc` - End date in UTC
  - `end_site` - End date in site timezone
  - `end_display` - End date for display

### Event Classification
- **`multiday`** - Number of days or false
- **`is_past`** - Boolean for past events
- **`is_now`** - Boolean for current events
- **`featured`** - Boolean for featured events
- **`sticky`** - Boolean for sticky events

### Week Context Properties
- **`starts_this_week`** - Boolean for week start
- **`ends_this_week`** - Boolean for week end
- **`happens_this_week`** - Boolean for week occurrence
- **`this_week_duration`** - Duration within week
- **`displays_on`** - Array of display dates

### Content Properties
- **`title`** - Event title
- **`description`** - Event description
- **`excerpt`** - Event excerpt
- **`cost`** - Event cost
- **`permalink`** - Event permalink
- **`thumbnail`** - Event featured image

### Schedule Properties
- **`schedule_details`** - Formatted schedule
- **`short_schedule_details`** - Short schedule format
- **`plain_schedule_details`** - Plain text schedule

### Related Content
- **`organizers`** - Array of organizer objects
- **`organizer_names`** - Array of organizer names
- **`venues`** - Array of venue objects

## WPUF Integration Implementation

### Event_Handler Class
The main integration class handles event creation using TEC's ORM API:

```php
class Event_Handler {
    public function __construct( $compatibility_manager ) {
        // Hook into WPUF's post creation for tribe_events
        add_action( 'wpuf_post_created_tribe_events', [ $this, 'handle_event_creation' ], 10, 3 );
    }

    public function handle_event_creation( $post_id, $form_data, $meta_vars ) {
        // Convert form data directly to ORM format
        $orm_args = $this->convert_form_data_to_orm_format( $form_data, $meta_vars );
        
        // Validate ORM requirements before saving
        if ( ! $this->validate_orm_requirements( $orm_args ) ) {
            $this->logger->error( 'ORM validation failed for post ID: ' . $post_id );
            return false;
        }
        
        // Use ORM to update the existing post
        $result = tribe_events()
            ->where( 'ID', $post_id )
            ->set_args( $orm_args )
            ->save();
            
        // Handle venue/organizer creation
        $this->handle_venue_creation( $post_id, $orm_args['venue'] ?? null );
        $this->handle_organizer_creation( $post_id, $orm_args['organizer'] ?? null );
    }
}
```

### Data Flow
1. **Form Submission** → WPUF processes form data
2. **Post Creation** → WPUF creates `tribe_events` post
3. **Hook Fires** → `wpuf_post_created_tribe_events` action triggered
4. **Data Processing** → Event_Handler converts form data to ORM format
5. **Validation** → Validate ORM requirements before saving
6. **ORM Update** → TEC ORM updates post with event data
7. **Related Content** → Venue/organizer creation and association

### Validation Method
The integration should include a `validate_orm_requirements()` method that checks:

```php
private function validate_orm_requirements( $orm_args ) {
    // Title is always required
    if ( empty( $orm_args['title'] ) ) {
        return false;
    }
    
    // Check date requirements
    $has_start_date = ! empty( $orm_args['start_date'] );
    $has_end_date = ! empty( $orm_args['end_date'] );
    $has_duration = ! empty( $orm_args['duration'] );
    $is_all_day = ! empty( $orm_args['all_day'] ) && $orm_args['all_day'];
    
    // Must have start_date AND (end_date OR duration OR all_day)
    return $has_start_date && ( $has_end_date || $has_duration || $is_all_day );
}
```

### Compatibility Management
The integration supports both TEC v5 and v6 through a compatibility manager:

```php
class TEC_Compatibility_Manager {
    public function __construct() {
        $this->detect_tec_version();
        $this->initialize_compatibility_layer();
    }
    
    private function detect_tec_version() {
        // Detect TEC version and set appropriate compatibility layer
    }
}
```

## Error Handling

### Validation Errors
- **Required fields missing** - Log error and return false
- **Invalid date formats** - Validate and sanitize dates
- **Venue/Organizer errors** - Handle creation failures gracefully

### ORM Errors
- **Database errors** - Log and handle gracefully
- **Validation failures** - Return appropriate error messages
- **Association errors** - Handle venue/organizer linking failures

## Performance Considerations

### Caching
- **Event model caching** - TEC provides built-in caching
- **Query optimization** - Use ORM's efficient query methods
- **Memory management** - Clean up after processing

### Database Optimization
- **Batch operations** - Use ORM for efficient updates
- **Transaction handling** - Ensure data consistency
- **Index usage** - Leverage TEC's optimized database structure

## Security Considerations

### Data Sanitization
- **Input validation** - Sanitize all form data
- **SQL injection prevention** - Use ORM's prepared statements
- **XSS prevention** - Escape output appropriately

### Permission Checks
- **User capabilities** - Verify user permissions
- **Form validation** - Validate form submissions
- **CSRF protection** - Use WordPress nonces

## Testing

### Unit Tests
- **Event creation** - Test ORM integration
- **Data validation** - Test field validation
- **Error handling** - Test error scenarios

### Integration Tests
- **Form submission** - Test complete flow
- **Hook execution** - Test hook timing
- **Data persistence** - Test database operations

## Future Enhancements

### Planned Features
- **Bulk operations** - Handle multiple events
- **Advanced validation** - Enhanced field validation
- **Performance optimization** - Query optimization
- **Error recovery** - Better error handling

### API Extensions
- **Custom fields** - Support for custom event fields
- **Advanced queries** - Complex event queries
- **Webhook support** - Real-time notifications 