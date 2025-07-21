# Events Calendar Integration Documentation

## Overview

The Events Calendar Integration module provides seamless integration between WP User Frontend (WPUF) and The Events Calendar (TEC) plugin. This module allows users to create, edit, and manage events directly from the frontend using WPUF forms.

## Architecture

### Module Structure

```
includes/Integrations/Events_Calendar/
├── Events_Calendar_Integration.php          # Main integration class
├── Compatibility/                           # Version compatibility
│   ├── TEC_Compatibility_Manager.php       # Version detection & routing
│   ├── TEC_V5_Compatibility.php           # TEC v5.x support
│   └── TEC_V6_Compatibility.php           # TEC v6.x support
├── Handlers/                               # Core functionality
│   ├── Event_Handler.php                   # Event creation/editing
│   ├── Date_Handler.php                    # Date/time handling
│   ├── Venue_Handler.php                   # Venue management
│   └── Organizer_Handler.php               # Organizer management
├── Validators/                             # Data validation
│   ├── Event_Validator.php                 # Event validation
│   └── Date_Validator.php                  # Date validation
├── Templates/                              # Form templates
│   └── Event_Form_Template.php            # Event form template
└── Utils/                                  # Utilities
    ├── TEC_Logger.php                      # Logging utilities
    ├── TEC_Constants.php                   # Constants
    └── TEC_Helper.php                      # Helper functions
```

## Features

### ✅ Supported Features

#### Event Management
- **Event Creation**: Create events with all standard TEC fields
- **Event Editing**: Edit existing events through frontend forms
- **Date/Time Handling**: Comprehensive date and time management
- **All-Day Events**: Support for all-day event creation
- **Event Cost**: Set event cost and currency
- **Event URL**: Add external event website links

#### Venue Management
- **Venue Creation**: Create new venues during event submission
- **Venue Selection**: Select from existing venues
- **Venue Details**: Full venue information (address, phone, website)
- **Map Integration**: Venue map display options

#### Organizer Management
- **Organizer Creation**: Create new organizers during event submission
- **Organizer Selection**: Select from existing organizers
- **Contact Information**: Organizer email, phone, and website

#### Form Fields
- **Event Title**: Required event title field
- **Event Description**: Rich text description field
- **Start Date/Time**: Event start date and time
- **End Date/Time**: Event end date and time
- **All-Day Event**: Checkbox for all-day events
- **Event Cost**: Numeric cost field
- **Currency Symbol**: Currency symbol field
- **Event URL**: URL field for external event links
- **Venue Fields**: Complete venue information fields
- **Organizer Fields**: Complete organizer information fields

### 🔄 Planned Features

#### Enhanced Date/Time Support
- [ ] Timezone selection
- [ ] UTC date handling
- [ ] Recurring events
- [ ] Multi-day events

#### Advanced Venue Features
- [ ] Venue map coordinates
- [ ] Venue categories
- [ ] Venue image upload

#### Advanced Organizer Features
- [ ] Organizer image upload
- [ ] Multiple organizers per event
- [ ] Organizer social media links

#### Event Features
- [ ] Event categories
- [ ] Event tags
- [ ] Event image upload
- [ ] Featured events
- [ ] Event visibility settings

## Installation & Setup

### Prerequisites

1. **WordPress**: Version 5.0 or higher
2. **WP User Frontend**: Version 4.1.7 or higher
3. **The Events Calendar**: Version 5.0 or higher

### Automatic Installation

The Events Calendar integration is automatically loaded when both WPUF and TEC are active. No manual installation is required.

### Manual Verification

To verify the integration is working:

1. Check if TEC is active:
   ```php
   if ( class_exists( 'Tribe__Events__Main' ) ) {
       echo 'TEC is active';
   }
   ```

2. Check if integration is loaded:
   ```php
   if ( class_exists( 'WeDevs\Wpuf\Integrations\Events_Calendar\Events_Calendar_Integration' ) ) {
       echo 'Integration is loaded';
   }
   ```

## Usage

### Creating an Event Form

1. **Access Form Builder**: Go to WPUF → Forms → Add New
2. **Select Template**: Choose "Events Calendar" template
3. **Configure Fields**: Customize the form fields as needed
4. **Save Form**: Save and publish the form

### Form Fields Reference

#### Required Fields
- **Event Title** (`post_title`): The event title
- **Event Description** (`post_content`): Event description
- **Start Date** (`_EventStartDate`): Event start date and time
- **End Date** (`_EventEndDate`): Event end date and time

#### Optional Fields
- **All-Day Event** (`_EventAllDay`): Checkbox for all-day events
- **Event Cost** (`_EventCost`): Event cost amount
- **Currency Symbol** (`_EventCurrencySymbol`): Currency symbol
- **Event URL** (`_EventURL`): External event website

#### Venue Fields
- **Venue Name** (`venue_name`): Venue name
- **Venue Address** (`venue_address`): Venue address
- **Venue City** (`venue_city`): Venue city
- **Venue State** (`venue_state`): Venue state/province
- **Venue ZIP** (`venue_zip`): Venue ZIP/postal code
- **Venue Country** (`venue_country`): Venue country
- **Venue Phone** (`venue_phone`): Venue phone number
- **Venue Website** (`venue_url`): Venue website URL

#### Organizer Fields
- **Organizer Name** (`organizer_name`): Organizer name
- **Organizer Email** (`organizer_email`): Organizer email
- **Organizer Phone** (`organizer_phone`): Organizer phone
- **Organizer Website** (`organizer_website`): Organizer website

### API Usage

#### Event Handler

```php
// Get event handler
$integration = wpuf()->integrations->tribe__events__main;
$event_handler = $integration->get_event_handler();

// Handle event submission
$result = $event_handler->handle_event_submission( $post_id, $form_id, $form_settings );

// Handle event update
$result = $event_handler->handle_event_update( $post_id, $form_id, $form_settings );
```

#### Date Handler

```php
// Get date handler
$integration = wpuf()->integrations->tribe__events__main;
$date_handler = $integration->get_date_handler();

// Convert dates
$converted_data = $date_handler->convert_events_calendar_dates( $meta_data, $post_id );

// Validate dates
$validated_data = $date_handler->validate_events_calendar_dates( $meta_data, $post_id );
```

#### Venue Handler

```php
// Get venue handler
$integration = wpuf()->integrations->tribe__events__main;
$venue_handler = $integration->get_venue_handler();

// Handle venue data
$venue_data = $venue_handler->handle_venue_data();

// Create venue
$venue_id = $venue_handler->create_venue( $venue_data );
```

#### Organizer Handler

```php
// Get organizer handler
$integration = wpuf()->integrations->tribe__events__main;
$organizer_handler = $integration->get_organizer_handler();

// Handle organizer data
$organizer_data = $organizer_handler->handle_organizer_data();

// Create organizer
$organizer_id = $organizer_handler->create_organizer( $organizer_data );
```

## Configuration

### Form Settings

#### Basic Settings
- **Post Type**: Set to `tribe_events`
- **Post Status**: Choose default post status
- **User Role**: Set required user role

#### Advanced Settings
- **Enable Guest Posting**: Allow guest users to create events
- **Post Approval**: Require admin approval for events
- **Email Notification**: Send notifications on event creation

### Field Configuration

#### Date Fields
- **Date Format**: Configure date format (default: Y-m-d H:i:s)
- **Timezone**: Set default timezone
- **Validation**: Enable date validation

#### Cost Fields
- **Currency**: Set default currency symbol
- **Validation**: Enable numeric validation
- **Format**: Configure cost display format

## Validation

### Event Validation

The integration includes comprehensive validation for:

- **Required Fields**: Event title, start date, end date
- **Date Logic**: End date must be after start date
- **Cost Validation**: Cost must be numeric
- **URL Validation**: Event URL must be valid
- **Email Validation**: Organizer email must be valid

### Date Validation

- **Format Validation**: Ensures proper date format
- **Range Validation**: Validates date ranges
- **Timezone Handling**: Proper timezone conversion
- **All-Day Events**: Special handling for all-day events

## Error Handling

### Logging

The integration uses a comprehensive logging system:

```php
// Get logger
$integration = wpuf()->integrations->tribe__events__main;
$logger = $integration->get_logger();

// Log messages
$logger->info( 'Event created successfully' );
$logger->error( 'Event creation failed' );
$logger->warning( 'Date validation warning' );
```

### Error Types

- **Validation Errors**: Field validation failures
- **API Errors**: TEC API call failures
- **Date Errors**: Date parsing and validation errors
- **Venue Errors**: Venue creation/selection errors
- **Organizer Errors**: Organizer creation/selection errors

## Compatibility

### TEC Version Support

- **TEC v5.x**: Full support via TEC_V5_Compatibility
- **TEC v6.x**: Full support via TEC_V6_Compatibility
- **Auto-Detection**: Automatic version detection

### WordPress Version Support

- **WordPress 5.0+**: Full support
- **WordPress 6.0+**: Enhanced features
- **WordPress 6.4+**: Latest features

### PHP Version Support

- **PHP 7.4+**: Full support
- **PHP 8.0+**: Enhanced features
- **PHP 8.2+**: Latest features

## Troubleshooting

### Common Issues

#### 1. Events Not Creating

**Symptoms**: Form submission succeeds but no event is created

**Solutions**:
- Check if TEC is active
- Verify form post type is set to `tribe_events`
- Check error logs for API errors
- Verify required fields are present

#### 2. Date Issues

**Symptoms**: Dates not saving correctly or validation errors

**Solutions**:
- Check date format in form fields
- Verify timezone settings
- Check for date validation errors
- Ensure start date is before end date

#### 3. Venue/Organizer Issues

**Symptoms**: Venues or organizers not creating/selecting

**Solutions**:
- Check venue/organizer field configuration
- Verify TEC API functions are available
- Check for permission issues
- Verify field names match expected format

#### 4. All-Day Event Issues

**Symptoms**: All-day events not working correctly

**Solutions**:
- Verify checkbox value is `'yes'` (lowercase)
- Check date handler configuration
- Verify TEC all-day event handling
- Check timezone settings

### Debug Mode

Enable debug mode to get detailed error information:

```php
// Enable debug logging
add_filter( 'wpuf_tec_debug_enabled', '__return_true' );

// Check debug logs
$logger = wpuf()->integrations->tribe__events__main->get_logger();
$logs = $logger->get_logs();
```

### Testing

Use the provided test scripts to verify functionality:

```bash
# Test integration loading
php tests/test-integration-simple.php

# Test event creation flow
php tests/test-event-creation-flow.php

# Test form template registration
php tests/test-form-template-registration.php
```

## Migration Guide

### From Old Integration

If you're upgrading from the old scattered integration:

1. **Backup**: Backup your current setup
2. **Update**: Update to latest WPUF version
3. **Verify**: Check that new integration is loaded
4. **Test**: Test event creation and editing
5. **Cleanup**: Remove any old custom code

### Custom Code Migration

If you have custom code using the old integration:

```php
// Old way
$ajax = new WeDevs\Wpuf\Ajax\Frontend_Form_Ajax();
$ajax->handle_events_calendar_data( $post_id, $is_update );

// New way
$integration = wpuf()->integrations->tribe__events__main;
$event_handler = $integration->get_event_handler();
$event_handler->handle_event_submission( $post_id, $form_id, $form_settings );
```

## Development

### Adding New Features

1. **Create Handler**: Add new handler in `Handlers/` directory
2. **Add Validation**: Create validator in `Validators/` directory
3. **Update Template**: Modify form template as needed
4. **Add Tests**: Create test scripts for new features
5. **Update Docs**: Update this documentation

### Contributing

1. **Fork**: Fork the repository
2. **Branch**: Create feature branch
3. **Code**: Implement your changes
4. **Test**: Add comprehensive tests
5. **Document**: Update documentation
6. **Submit**: Submit pull request

## Support

### Getting Help

- **Documentation**: Check this documentation first
- **GitHub Issues**: Report bugs on GitHub
- **Community**: Ask questions in WPUF community
- **Support**: Contact WPUF support for premium issues

### Reporting Bugs

When reporting bugs, please include:

1. **Environment**: WordPress, WPUF, and TEC versions
2. **Steps**: Detailed steps to reproduce
3. **Expected**: What should happen
4. **Actual**: What actually happens
5. **Logs**: Any error logs or debug information

### Feature Requests

When requesting features, please include:

1. **Use Case**: How the feature would be used
2. **Benefits**: What benefits it would provide
3. **Implementation**: Suggested implementation approach
4. **Priority**: How important the feature is

## Changelog

### Version 1.0.0 (Current)

#### Added
- Complete Events Calendar integration
- Modular architecture with handlers and validators
- TEC v5 and v6 compatibility
- Comprehensive form template
- Date/time handling with timezone support
- Venue and organizer management
- Comprehensive validation and error handling
- Logging system for debugging
- Test scripts for verification

#### Fixed
- All-day event checkbox value issue
- TEC API method compatibility
- Date format validation
- Template registration issues

#### Improved
- Code organization and maintainability
- Error handling and user feedback
- Performance and reliability
- Documentation and testing

---

*This documentation is maintained by the WP User Frontend team. For questions or contributions, please contact the development team.* 