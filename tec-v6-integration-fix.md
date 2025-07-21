# The Events Calendar v6 Integration Fix

## Problem Analysis

The previous approach was trying to handle event creation ourselves, but TEC has its own complex process that hooks into `save_post` at priority 15. The errors were occurring because:

1. **We were interfering with TEC's natural process** - TEC expects to handle the `save_post` hook itself
2. **Data format issues** - We weren't providing data in the format TEC expects
3. **Timing issues** - We were trying to save after the post was created, but TEC needs the data before

## New Approach: Follow TEC's Natural Process

### Key Changes Made:

1. **Hook into `wpuf_form_submit_before`** instead of `wpuf_form_submit_after`
   - This allows us to prepare the data BEFORE the post is created
   - TEC can then handle the `save_post` hook naturally

2. **Prepare data for TEC's `save_post` hook**
   - Convert our field names to TEC's expected format (`_EventStartDate`, etc.)
   - Put the data in `$_POST` so TEC's `saveEventMeta()` can process it
   - Let TEC handle all the complex date/time logic and custom tables

3. **Remove manual event creation**
   - No more calling `Tribe__Events__API::saveEventMeta()` manually
   - No more trying to update custom tables ourselves
   - Let TEC handle everything through its natural hooks

### Code Changes:

#### 1. **Events_Calendar_Integration.php**
```php
// OLD: Hook after post creation
add_action( 'wpuf_form_submit_after', [ $this->event_handler, 'handle_event_submission' ], 10, 3 );

// NEW: Hook before post creation
add_action( 'wpuf_form_submit_before', [ $this->event_handler, 'prepare_event_data' ], 10, 3 );
```

#### 2. **Event_Handler.php**
```php
// NEW: Prepare data before post creation
public function prepare_event_data( $form_id, $form_settings, $form_data ) {
    // Build event data from form data
    $event_data = $this->build_event_data_from_form_data( $form_data );
    
    // Prepare data for TEC's save_post hook
    $this->prepare_data_for_tec_save_post( $event_data );
}

// NEW: Convert field names and add to $_POST
private function prepare_data_for_tec_save_post( $event_data ) {
    $field_mapping = [
        'EventStartDate' => '_EventStartDate',
        'EventEndDate' => '_EventEndDate',
        // ... more mappings
    ];
    
    foreach ( $field_mapping as $our_field => $tec_field ) {
        if ( isset( $event_data[ $our_field ] ) ) {
            $_POST[ $tec_field ] = $event_data[ $our_field ];
        }
    }
}
```

#### 3. **Date_Handler.php**
```php
// NEW: Work with form data instead of $_POST
public function build_event_date_data_from_form_data( $form_data ) {
    // Use TEC's expected date format: 'Y-m-d H:i:s'
    $date_format = 'Y-m-d H:i:s';
    
    // Handle date conversion with proper timezone handling
    // Add fallback dates when fields are empty
}
```

#### 4. **TEC_V6_Compatibility.php**
```php
// OLD: Try to handle event creation ourselves
private function save_event_using_tec_api( $post_id, $event_data ) {
    $result = \Tribe__Events__API::saveEventMeta( $post_id, $event_data, get_post( $post_id ) );
}

// NEW: Let TEC handle it naturally
private function save_event_using_tec_api( $post_id, $event_data ) {
    // Convert our data format to TEC's expected format
    $tec_data = $this->convert_to_tec_format( $event_data, $post_id );
    
    // Let TEC handle the save_post hook naturally
    foreach ( $tec_data as $key => $value ) {
        $_POST[ $key ] = $value;
    }
    
    // TEC will automatically handle the save_post hook and call saveEventMeta
}
```

## How It Works Now:

1. **Form Submission** → `wpuf_form_submit_before`
2. **Prepare Data** → Convert our format to TEC's format
3. **Add to $_POST** → Put data where TEC expects it
4. **Post Creation** → `wp_insert_post()` creates the event
5. **TEC Hook** → `save_post` at priority 15 triggers TEC's processing
6. **TEC Processing** → `saveEventMeta()` handles all complex logic
7. **Custom Tables** → TEC updates custom tables automatically
8. **Frontend Display** → Event appears properly at `event/the-new-event`

## Expected Results:

- ✅ **No more validation errors** - TEC handles all validation
- ✅ **Events appear in frontend** - Proper custom table updates
- ✅ **Correct date handling** - TEC's built-in date processing
- ✅ **Timezone support** - TEC's timezone handling
- ✅ **All-day events** - TEC's all-day event logic
- ✅ **Venue/Organizer support** - TEC's linked post handling

## Testing:

1. **Create event with dates** - Should work without errors
2. **Create event without dates** - Should use fallback dates
3. **Create all-day event** - Should handle properly
4. **Check frontend display** - Should appear at event URL
5. **Check admin dashboard** - Should show as proper event

This approach follows TEC's intended architecture and should resolve all the validation errors we were seeing. 