# The Events Calendar v6 Integration Fixes

## Problem Identified

The WP User Frontend integration with The Events Calendar v6 was **not following the complete event creation process** as documented in `event-creation-process.md`. This caused events created via WPUF forms to not appear properly in the frontend, even though they showed up in the WordPress dashboard.

## Root Cause Analysis

The integration was missing several critical steps from the complete event creation process:

### 1. **Missing Custom Tables Integration (v6.0+)**
- TEC v6 uses custom tables (`tec_events`, `tec_occurrences`) for performance
- The integration wasn't updating these tables after event creation
- This caused events to not appear in calendar views and frontend

### 2. **Missing Post-Creation Actions**
- No known date range updates
- No cache clearing
- No linked post publishing (venues/organizers)

### 3. **Incomplete Event Meta Processing**
- Not using `Tribe__Events__API::saveEventMeta()` which handles complex logic
- Missing proper date/time processing
- Missing venue/organizer linking

### 4. **Missing Required Form Fields**
- No venue/organizer selection fields
- Missing required date fields validation
- Incomplete form field structure

## Fixes Implemented

### 1. **Updated TEC_V6_Compatibility.php**

**Before:**
```php
// Only used ORM API directly
$result = tribe_events()
    ->by( 'ID', $post_id )
    ->set( $orm_data )
    ->save();
```

**After:**
```php
// Step 1: Use TEC's native API for proper event meta processing
$result = $this->save_event_using_tec_api( $post_id, $event_data );

// Step 2: Update custom tables (v6.0+)
$custom_tables_result = $this->update_custom_tables( $post_id );

// Step 3: Perform post-creation actions
$this->perform_post_creation_actions( $post_id );
```

**Key Changes:**
- Added `save_event_using_tec_api()` method using `Tribe__Events__API::saveEventMeta()`
- Added `update_custom_tables()` method for custom tables integration
- Added `perform_post_creation_actions()` for cache clearing and linked post publishing
- Added proper error handling and logging

### 2. **Updated Event_Handler.php**

**Added `ensure_required_fields()` method:**
```php
private function ensure_required_fields( $args, $post_id ) {
    // Ensure post type is set
    $args['post_type'] = 'tribe_events';
    
    // Ensure post status is set
    if ( empty( $args['post_status'] ) ) {
        $args['post_status'] = 'publish';
    }
    
    // Ensure timezone is set
    if ( empty( $args['EventTimezone'] ) ) {
        $args['EventTimezone'] = wp_timezone_string();
    }
    
    // Ensure all-day event flag is set
    if ( ! isset( $args['EventAllDay'] ) ) {
        $args['EventAllDay'] = 'no';
    }
    
    return $args;
}
```

**Key Changes:**
- Made `build_event_data()` public for better access
- Added proper field validation and defaults
- Fixed event settings mapping (EventShowMap, EventShowMapLink, etc.)

### 3. **Updated Event_Form_Template.php**

**Added Missing Form Fields:**
```php
[
    'input_type' => 'select',
    'template'   => 'dropdown_field',
    'required'   => 'no',
    'label'      => __( 'Venue', 'wp-user-frontend' ),
    'name'       => '_EventVenueID',
    'is_meta'    => 'yes',
    'options'    => $this->get_venue_options(),
],
[
    'input_type' => 'text',
    'template'   => 'text_field',
    'required'   => 'no',
    'label'      => __( 'Create New Venue', 'wp-user-frontend' ),
    'name'       => 'venue_name',
    'is_meta'    => 'yes',
],
```

**Key Changes:**
- Added venue selection and creation fields
- Added organizer selection and creation fields
- Made start/end dates required fields
- Added proper field validation

### 4. **Updated Venue_Handler.php and Organizer_Handler.php**

**Simplified venue/organizer creation logic:**
```php
// Check if existing venue is selected
if ( ! empty( $_POST['_EventVenueID'] ) && is_numeric( $_POST['_EventVenueID'] ) ) {
    return [ 'VenueID' => intval( $_POST['_EventVenueID'] ) ];
}

// Check if we need to create a new venue
if ( ! empty( $_POST['venue_name'] ) ) {
    return $this->create_venue();
}
```

**Key Changes:**
- Simplified field detection logic
- Improved error handling
- Better logging for debugging

## Complete Event Creation Process Now Followed

The integration now follows the complete 5-step process from `event-creation-process.md`:

### Step 1: Form Initialization ✅
- Form fields properly configured
- Required fields validation
- Venue/organizer selection options

### Step 2: Form Submission ✅
- WordPress post creation with `tribe_events` post type
- Proper event meta processing

### Step 3: Event Meta Save Process ✅
- Uses `Tribe__Events__API::saveEventMeta()` for proper processing
- Handles date/time conversions
- Processes venue/organizer linking
- Saves all event metadata

### Step 4: Custom Tables Integration ✅
- Updates `tec_events` table
- Updates `tec_occurrences` table
- Handles recurring events properly

### Step 5: Post-Creation Actions ✅
- Updates known date range
- Publishes linked venues/organizers
- Clears relevant caches
- Updates calendar views

## Testing Recommendations

1. **Create a new event** using the WPUF form
2. **Verify the event appears** in the frontend at `event/the-new-event`
3. **Check the WordPress dashboard** to ensure the event is properly created
4. **Test venue/organizer creation** by creating new ones via the form
5. **Test existing venue/organizer selection** from dropdowns
6. **Verify calendar views** show the new events
7. **Test event editing** to ensure updates work properly

## Expected Results

After these fixes:
- ✅ Events created via WPUF forms will appear in frontend
- ✅ Events will be properly indexed in calendar views
- ✅ Venues and organizers will be properly linked
- ✅ Event URLs will work correctly (`event/the-new-event`)
- ✅ All TEC v6 features will work as expected
- ✅ Cache clearing will ensure immediate visibility

## Compatibility Notes

- **TEC v6.0+**: Full support with custom tables
- **TEC v5.x**: Still supported via v5 compatibility handler
- **WordPress 6.6+**: Required for TEC v6
- **PHP 7.4+**: Required for TEC v6

The integration now properly follows the complete event creation process as documented in `event-creation-process.md`, ensuring full compatibility with The Events Calendar v6. 