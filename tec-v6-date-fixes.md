# The Events Calendar v6 Date Handling Fixes

## Problem Identified

The WP User Frontend integration with The Events Calendar v6 was failing with validation errors:

```
[20-Jul-2025 18:49:01 UTC] tribe-canonical-line channel=default level=debug source="TEC\Events\Custom_Tables\V1\Models\Model::validate:288" errors="{\"end_date\":\"The start_date should be a valid date.\",\"timezone\":\"The provided timezone is not a valid timezone.\",\"start_date_utc\":\"The start_date_utc requires a value.\"}"
```

## Root Cause Analysis

The integration was not properly handling:

1. **Date Format**: TEC v6 expects dates in `Y-m-d H:i:s` format (DBDATETIMEFORMAT)
2. **Timezone Validation**: Invalid timezone strings were being passed
3. **Required Fields**: Missing required date fields were causing validation failures
4. **Field Name Mapping**: Incorrect field names were being used for the TEC API

## Fixes Implemented

### 1. **Date Handler Updates** (`Date_Handler.php`)

**Changes Made:**
- Updated date format to use TEC's `DBDATETIMEFORMAT` (`Y-m-d H:i:s`)
- Added fallback date handling when start/end dates are missing
- Improved timezone validation and handling
- Enhanced error logging with input values for debugging

**Key Improvements:**
```php
// Use TEC's expected date format
$date_format = 'Y-m-d H:i:s'; // TEC's DBDATETIMEFORMAT

// Fallback handling for missing dates
if ( empty( $_POST['_EventStartDate'] ) ) {
    $now = new \DateTimeImmutable( 'now', $site_timezone );
    $args['EventStartDate'] = $now->format( $date_format );
    $args['EventStartDateUTC'] = $now->setTimezone( new \DateTimeZone( 'UTC' ) )->format( $date_format );
}
```

### 2. **Timezone Validation** (`TEC_Helper.php`)

**Added Method:**
```php
public static function is_valid_timezone( $timezone_string ) {
    if ( empty( $timezone_string ) ) {
        return false;
    }

    try {
        $timezone = new \DateTimeZone( $timezone_string );
        return true;
    } catch ( \Exception $e ) {
        return false;
    }
}
```

### 3. **TEC v6 Compatibility Updates** (`TEC_V6_Compatibility.php`)

**Added Data Conversion:**
- Field name mapping from our format to TEC's expected format
- Proper handling of venue and organizer data
- Fallback date/time creation for required fields

**Key Changes:**
```php
private function convert_to_tec_format( $event_data, $post_id ) {
    $field_mapping = [
        'EventStartDate' => '_EventStartDate',
        'EventEndDate' => '_EventEndDate',
        'EventStartDateUTC' => '_EventStartDateUTC',
        'EventEndDateUTC' => '_EventEndDateUTC',
        // ... more mappings
    ];
    
    // Ensure required fields are present
    if ( empty( $tec_data['_EventStartDate'] ) ) {
        $now = new \DateTimeImmutable( 'now', wp_timezone() );
        $tec_data['_EventStartDate'] = $now->format( 'Y-m-d H:i:s' );
        $tec_data['_EventStartDateUTC'] = $now->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
    }
}
```

### 4. **Form Template Updates** (`Event_Form_Template.php`)

**Added Default Values:**
```php
[
    'input_type' => 'date',
    'template'   => 'date_field',
    'required'   => 'yes',
    'label'      => __( 'Event Start', 'wp-user-frontend' ),
    'name'       => '_EventStartDate',
    'is_meta'    => 'yes',
    'width'      => 'large',
    'format'     => 'yy-mm-dd',
    'time'       => 'yes',
    'default'    => current_time( 'Y-m-d H:i:s' ),
    'wpuf_cond'  => $this->conditionals,
],
```

## Expected Results

After these fixes:

1. **Events will be created successfully** without validation errors
2. **Events will appear in the frontend** at `event/the-new-event` URLs
3. **Date fields will have proper defaults** when not filled by users
4. **Timezone handling will be robust** with fallback to site timezone
5. **Error logging will be more informative** for debugging

## Testing Recommendations

1. **Test event creation** with and without date fields filled
2. **Verify frontend display** at event URLs
3. **Check timezone handling** with different timezone settings
4. **Monitor error logs** for any remaining issues
5. **Test all-day events** and regular events

## Files Modified

- `includes/Integrations/Events_Calendar/Handlers/Date_Handler.php`
- `includes/Integrations/Events_Calendar/Utils/TEC_Helper.php`
- `includes/Integrations/Events_Calendar/Compatibility/TEC_V6_Compatibility.php`
- `includes/Integrations/Events_Calendar/Templates/Event_Form_Template.php`

## Next Steps

1. Test the integration with the updated code
2. Monitor error logs for any remaining issues
3. Verify that events appear properly in the frontend
4. Consider adding more comprehensive date validation if needed 