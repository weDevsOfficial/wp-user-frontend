<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Handlers;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Constants;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Logger;

/**
 * Event Handler for Events Calendar
 *
 * Handles all event creation and editing operations
 *
 * @since WPUF_SINCE
 */
class Event_Handler {

    /**
     * Compatibility manager instance
     *
     * @var TEC_Compatibility_Manager
     */
    private $compatibility_manager;

    /**
     * Venue handler instance
     *
     * @var Venue_Handler
     */
    private $venue_handler;

    /**
     * Organizer handler instance
     *
     * @var Organizer_Handler
     */
    private $organizer_handler;

    /**
     * Logger instance
     *
     * @var TEC_Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param TEC_Compatibility_Manager $compatibility_manager
     */
    public function __construct( $compatibility_manager ) {
        $this->compatibility_manager = $compatibility_manager;
        $this->venue_handler = new Venue_Handler( $compatibility_manager );
        $this->organizer_handler = new Organizer_Handler( $compatibility_manager );
        $this->logger = new TEC_Logger();

        // Hook into WPUF's post creation for tribe_events
        add_action( 'wpuf_post_created_tribe_events', [ $this, 'handle_event_creation' ], 10, 3 );

        // Prevent TEC from processing event creation when we're handling it
        add_action( 'save_post', [ $this, 'prevent_tec_event_processing' ], 5, 2 );
    }

    /**
     * Prevent TEC from processing event creation when we're handling it through WPUF
     *
     * @since WPUF_SINCE
     *
     * @param int     $post_id
     * @param WP_Post $post
     */
    public function prevent_tec_event_processing( $post_id, $post ) {
        error_log( print_r( $post->post_type, true ) );
        // Only handle tribe_events post type
        if ( $post->post_type !== 'tribe_events' ) {
            error_log( print_r( 'returning', true ) );
            return;
        }

        // Check if this is a WPUF form submission
        if ( $this->is_wpuf_form_submission() ) {
            error_log( print_r( 'is_wpuf_form_submission', true ) );
            // Remove TEC's event meta processing to prevent conflicts
            remove_action( 'save_post', [ 'Tribe__Events__Main', 'addEventMeta' ], 15 );

            $this->logger->info( 'Prevented TEC event processing for WPUF submission - post ID: ' . $post_id );
        } else {
            error_log( print_r( '! is_wpuf_form_submission', true ) );
        }
    }

    /**
     * Check if current request is a WPUF form submission
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function is_wpuf_form_submission() {
        // Check for WPUF form submission indicators
        $wpuf_indicators = [
            'wpuf_form_submit',
            'wpuf_post_new',
            'wpuf_edit_post',
            'wpuf_ajax_submit',
        ];

        foreach ( $wpuf_indicators as $indicator ) {
            if ( isset( $_POST[ $indicator ] ) || isset( $_REQUEST[ $indicator ] ) ) {
                return true;
            }
        }

        // Check for WPUF nonce
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wpuf_form_add' ) ) {
            return true;
        }

        // Check for WPUF AJAX action
        if ( isset( $_POST['action'] ) && strpos( $_POST['action'], 'wpuf' ) === 0 ) {
            return true;
        }

        return false;
    }

    /**
     * Handle event submission
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     * @return bool
     */
    public function handle_event_submission( $post_id, $form_id, $form_settings ) {
        if ( $form_settings['post_type'] !== 'tribe_events' ) {
            return true;
        }

        $this->logger->info( 'Handling event submission for post ID: ' . $post_id );

        // Build event data
        $event_data = $this->build_event_data( $post_id );

        if ( empty( $event_data ) ) {
            $this->logger->error( 'Failed to build event data for post ID: ' . $post_id );
            return false;
        }

        // Prepare data for TEC's save_post hook
        $this->prepare_data_for_tec_save_post( $event_data );

        $this->logger->info( 'Event data prepared for TEC save_post hook for post ID: ' . $post_id );
        return true;
    }

    /**
     * Handle event update
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     * @return bool
     */
    public function handle_event_update( $post_id, $form_id, $form_settings ) {
        if ( $form_settings['post_type'] !== 'tribe_events' ) {
            return true;
        }

        $this->logger->info( 'Handling event update for post ID: ' . $post_id );

        // Build event data
        $event_data = $this->build_event_data( $post_id );

        if ( empty( $event_data ) ) {
            $this->logger->error( 'Failed to build event data for post ID: ' . $post_id );
            return false;
        }

        // Prepare data for TEC's save_post hook
        $this->prepare_data_for_tec_save_post( $event_data );

        $this->logger->info( 'Event data prepared for TEC save_post hook for post ID: ' . $post_id );
        return true;
    }

    /**
     * Prepare event data before post creation
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id
     * @param array $form_settings
     * @param array $form_data
     * @return bool
     */
    public function prepare_event_data( $form_id, $form_settings, $form_data ) {
        if ( $form_settings['post_type'] !== 'tribe_events' ) {
            return true;
        }

        $this->logger->info( 'Preparing event data for form ID: ' . $form_id );

        // Convert form data to ORM format
        $orm_args = $this->convert_form_data_to_orm_format( $form_data );

        if ( empty( $orm_args ) ) {
            $this->logger->error( 'Failed to convert form data to ORM format for form ID: ' . $form_id );
            return false;
        }

        // Prepare data for TEC's save_post hook
        $this->prepare_data_for_tec_save_post_from_orm( $orm_args );

        $this->logger->info( 'Event data prepared for TEC save_post hook for form ID: ' . $form_id );
        return true;
    }

    /**
     * Prepare data for TEC's save_post hook from ORM format
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     */
    private function prepare_data_for_tec_save_post_from_orm( $orm_args ) {
        // Convert ORM field names to TEC's expected field names
        $field_mapping = [
            'all_day'               => '_EventAllDay',
            'timezone'              => '_EventTimezone',
            'cost'                  => '_EventCost',
            'currency_symbol'       => '_EventCurrencySymbol',
            'url'                   => '_EventURL',
            'show_map'              => '_EventShowMap',
            'show_map_link'         => '_EventShowMapLink',
            'hide_from_upcoming'    => '_EventHideFromUpcoming',
        ];

        // Convert field names and add to $_POST for TEC's save_post hook
        foreach ( $field_mapping as $orm_field => $tec_field ) {
            if ( isset( $orm_args[ $orm_field ] ) ) {
                $_POST[ $tec_field ] = $orm_args[ $orm_field ];
            }
        }

        // Handle venue data
        if ( isset( $orm_args['venue'] ) ) {
            if ( isset( $orm_args['venue']['VenueID'] ) ) {
                $_POST['_EventVenueID'] = $orm_args['venue']['VenueID'];
            }
        }

        // Handle organizer data
        if ( isset( $orm_args['organizer'] ) ) {
            if ( isset( $orm_args['organizer']['OrganizerID'] ) ) {
                $_POST['_EventOrganizerID'] = $orm_args['organizer']['OrganizerID'];
            }
        }
    }



    /**
     * Ensure all required fields are present for TEC API
     *
     * @since WPUF_SINCE
     *
     * @param array $args
     * @param int   $post_id
     * @return array
     */
    private function ensure_required_fields( $args, $post_id ) {
        // Get post data
        $post = get_post( $post_id );
        if ( ! $post ) {
            return $args;
        }

        // Ensure post type is set
        $args['post_type'] = 'tribe_events';

        // Ensure post status is set
        if ( empty( $args['post_status'] ) ) {
            $args['post_status'] = 'publish';
        }

        // Ensure post title is set
        if ( empty( $args['post_title'] ) && ! empty( $post->post_title ) ) {
            $args['post_title'] = $post->post_title;
        }

        // Ensure post content is set
        if ( empty( $args['post_content'] ) && ! empty( $post->post_content ) ) {
            $args['post_content'] = $post->post_content;
        }

        // Ensure post excerpt is set
        if ( empty( $args['post_excerpt'] ) && ! empty( $post->post_excerpt ) ) {
            $args['post_excerpt'] = $post->post_excerpt;
        }

        // Ensure post author is set
        if ( empty( $args['post_author'] ) && ! empty( $post->post_author ) ) {
            $args['post_author'] = $post->post_author;
        }

        // Ensure featured image is handled
        if ( ! empty( $_POST['featured_image'] ) ) {
            $args['FeaturedImage'] = intval( $_POST['featured_image'] );
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

    /**
     * Validate ORM args
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     * @return bool
     */
    public function validate_orm_args( $orm_args ) {
        // Validate venue if present
        if ( ! empty( $orm_args['venue'] ) ) {
            if ( ! $this->venue_handler->validate_venue_data( $orm_args['venue'] ) ) {
                return false;
            }
        }

        // Validate organizer if present
        if ( ! empty( $orm_args['organizer'] ) ) {
            if ( ! $this->organizer_handler->validate_organizer_data( $orm_args['organizer'] ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get event data for a post
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id
     * @return array
     */
    public function get_event_data( $post_id ) {
        $event_data = [];

        foreach ( TEC_Constants::EVENT_META_FIELDS as $field ) {
            $value = get_post_meta( $post_id, $field, true );
            if ( ! empty( $value ) ) {
                $event_data[ $field ] = $value;
            }
        }

        return $event_data;
    }

    /**
     * Check if post is a TEC event
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id
     * @return bool
     */
    public function is_tec_event( $post_id ) {
        $post_type = get_post_type( $post_id );
        return $post_type === 'tribe_events';
    }

    /**
     * Handle event creation using TEC ORM
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $form_data
     * @param array $meta_vars
     * @return bool
     */
    public function handle_event_creation( $post_id, $form_data, $meta_vars ) {
        $this->logger->info( 'Handling event creation using ORM for post ID: ' . $post_id );

        try {
            // Convert form data directly to ORM format
            $orm_args = $this->convert_form_data_to_orm_format( $form_data, $meta_vars );

            if ( empty( $orm_args ) ) {
                $this->logger->error( 'Failed to convert form data to ORM format for post ID: ' . $post_id );
                return false;
            }

            // Validate ORM requirements before saving
            if ( ! $this->validate_orm_requirements( $orm_args ) ) {
                $this->logger->error( 'ORM requirements validation failed for post ID: ' . $post_id );
                return false;
            }

            // Temporarily disable TEC's save_post hooks to prevent conflicts
            $this->temporarily_disable_tec_hooks();

                        // Use direct WordPress update and TEC meta saving
            $this->logger->info( 'Attempting direct WordPress update with args: ' . print_r( $orm_args, true ) );

            try {
                // Prepare post data for WordPress update
                $post_data = [
                    'ID' => $post_id,
                    'post_type' => 'tribe_events',
                ];

                // Add basic post fields
                if ( isset( $orm_args['title'] ) ) {
                    $post_data['post_title'] = $orm_args['title'];
                }

                if ( isset( $orm_args['description'] ) ) {
                    $post_data['post_content'] = $orm_args['description'];
                }

                if ( isset( $orm_args['excerpt'] ) ) {
                    $post_data['post_excerpt'] = $orm_args['excerpt'];
                }

                // Update the post first
                $post_result = wp_update_post( $post_data, true );

                if ( is_wp_error( $post_result ) ) {
                    $this->logger->error( 'WordPress post update failed: ' . $post_result->get_error_message() );
                    $result = false;
                } else {
                    // Now save the event meta using TEC's API
                    $meta_result = $this->save_event_meta( $post_id, $orm_args );
                    $result = $meta_result;

                    $this->logger->info( 'WordPress update and TEC meta save result: ' . ( $result ? 'success' : 'failed' ) );
                }

                if ( ! $result ) {
                    $this->logger->error( 'Event update failed for post ID: ' . $post_id );
                }
            } catch ( \Exception $e ) {
                $this->logger->error( 'Exception during event update: ' . $e->getMessage() );
                $this->logger->error( 'Exception trace: ' . $e->getTraceAsString() );
                $result = false;
            }

            // Re-enable TEC's save_post hooks
            // $this->restore_tec_hooks();

            if ( ! $result ) {
                $this->logger->error( 'Failed to update event using ORM for post ID: ' . $post_id );
                return false;
            }

            // Handle venue creation if needed
            if ( ! empty( $orm_args['venue'] ) ) {
                $this->handle_venue_creation( $post_id, $orm_args['venue'] );
            }

            // Handle organizer creation if needed
            if ( ! empty( $orm_args['organizer'] ) ) {
                $this->handle_organizer_creation( $post_id, $orm_args['organizer'] );
            }

            $this->logger->info( 'Event created successfully using ORM for post ID: ' . $post_id );
            return true;

        } catch ( \Exception $e ) {
            $this->logger->error( 'Exception during event creation: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Create an event using the official TEC ORM API.
     *
     * @since WPUF_SINCE
     *
     * @param array $postarr      The post array (title, content, etc.)
     * @param array $meta_vars    The meta fields from the form
     * @param int   $form_id      The WPUF form ID
     * @param array $form_settings The WPUF form settings
     *
     * @return int|false The created event post ID on success, 0 or false on failure
     */
    public function create_event_via_tec_api( $postarr, $meta_vars, $form_id, $form_settings ) {
        // Convert WPUF data to TEC ORM format
        $args = $this->convert_form_data_to_orm_format( $postarr, $meta_vars );
        if ( empty( $args ) || ! is_array( $args ) ) {
            $this->logger->error( 'Failed to convert form data to TEC ORM format. Postarr: ' . print_r( $postarr, true ) . ' Meta: ' . print_r( $meta_vars, true ) );
            return 0;
        }
        $this->logger->info( 'TEC ORM args for create: ' . print_r( $args, true ) );
        // Remove ID if present (should not be set for new posts)
        if ( isset( $args['ID'] ) ) {
            unset( $args['ID'] );
        }
        // Call the TEC ORM API
        try {
            if ( function_exists( 'tribe_events' ) ) {
                $event = tribe_events()->set_args( $args )->create();
                if ( $event instanceof \WP_Post ) {
                    $this->logger->info( 'Event created via TEC API: ' . $event->ID );
                    return $event->ID;
                } else {
                    $this->logger->error( 'TEC API did not return WP_Post. Args: ' . print_r( $args, true ) );
                    return 0;
                }
            } else {
                $this->logger->error( 'tribe_events() function not found.' );
                return 0;
            }
        } catch ( \Exception $e ) {
            $this->logger->error( 'Exception during TEC event creation: ' . $e->getMessage() );
            return 0;
        }
    }

    /**
     * Convert form data directly to ORM format
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @param array $meta_vars
     * @return array
     */
    private function convert_form_data_to_orm_format( $form_data, $meta_vars = [] ) {
        $orm_args = [];

        // Merge form data with meta vars for comprehensive data access
        $all_data = array_merge( $form_data, $meta_vars );

        // Log the incoming data for debugging
        $this->logger->info( 'Converting form data to ORM format' );
        $this->logger->info( 'Form data keys: ' . implode( ', ', array_keys( $form_data ) ) );
        $this->logger->info( 'Meta vars keys: ' . implode( ', ', array_keys( $meta_vars ) ) );

        // Log specific date fields
        if ( isset( $all_data['_EventStartDate'] ) ) {
            $this->logger->info( 'Start date raw: ' . $all_data['_EventStartDate'] );
        }
        if ( isset( $all_data['_EventEndDate'] ) ) {
            $this->logger->info( 'End date raw: ' . $all_data['_EventEndDate'] );
        }
        if ( isset( $all_data['_EventAllDay'] ) ) {
            $this->logger->info( 'All day raw: ' . print_r( $all_data['_EventAllDay'], true ) );
        }

        // Required fields
        if ( ! empty( $all_data['post_title'] ) ) {
            $orm_args['title'] = sanitize_text_field( $all_data['post_title'] );
        }

        // Map basic fields
        if ( ! empty( $all_data['post_content'] ) ) {
            $orm_args['description'] = wp_kses_post( $all_data['post_content'] );
        }

        if ( ! empty( $all_data['post_excerpt'] ) ) {
            $orm_args['excerpt'] = sanitize_textarea_field( $all_data['post_excerpt'] );
        }

        // Handle date fields - these are critical for TEC ORM
        // Use TEC ORM field aliases instead of meta field names
        if ( ! empty( $all_data['_EventStartDate'] ) ) {
            $start_date = $this->format_date_for_tec( $all_data['_EventStartDate'] );
            if ( $start_date ) {
                $orm_args['start_date'] = $start_date;
                $this->logger->info( 'Formatted start date: ' . $start_date );
            } else {
                $this->logger->error( 'Failed to format start date: ' . $all_data['_EventStartDate'] );
            }
        } else {
            // Fallback: create a default start date if none provided
            $default_start = current_time( 'Y-m-d H:i:s' );
            $orm_args['start_date'] = $default_start;
            $this->logger->info( 'Using default start date: ' . $default_start );
        }

        if ( ! empty( $all_data['_EventEndDate'] ) ) {
            $end_date = $this->format_date_for_tec( $all_data['_EventEndDate'] );
            if ( $end_date ) {
                $orm_args['end_date'] = $end_date;
                $this->logger->info( 'Formatted end date: ' . $end_date );
            } else {
                $this->logger->error( 'Failed to format end date: ' . $all_data['_EventEndDate'] );
            }
        } else {
            // Fallback: create a default end date if none provided
            if ( ! empty( $orm_args['start_date'] ) ) {
                $default_end = date( 'Y-m-d H:i:s', strtotime( $orm_args['start_date'] ) + 3600 ); // 1 hour later
                $orm_args['end_date'] = $default_end;
                $this->logger->info( 'Using default end date: ' . $default_end );
            }
        }

        // Add UTC dates for TEC v6 compatibility using ORM field aliases
        if ( ! empty( $orm_args['start_date'] ) ) {
            $timezone = isset( $orm_args['timezone'] ) ? $orm_args['timezone'] : 'UTC';
            try {
                $start_datetime = new \DateTime( $orm_args['start_date'], new \DateTimeZone( $timezone ) );
                $orm_args['start_date_utc'] = $start_datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
                $this->logger->info( 'Added start_date_utc: ' . $orm_args['start_date_utc'] . ' from timezone: ' . $timezone );
            } catch ( \Exception $e ) {
                $this->logger->error( 'Failed to convert start date to UTC: ' . $e->getMessage() );
                // Fallback: use the same time as UTC
                $orm_args['start_date_utc'] = $orm_args['start_date'];
            }
        }

        if ( ! empty( $orm_args['end_date'] ) ) {
            $timezone = isset( $orm_args['timezone'] ) ? $orm_args['timezone'] : 'UTC';
            try {
                $end_datetime = new \DateTime( $orm_args['end_date'], new \DateTimeZone( $timezone ) );
                $orm_args['end_date_utc'] = $end_datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
                $this->logger->info( 'Added end_date_utc: ' . $orm_args['end_date_utc'] . ' from timezone: ' . $timezone );
            } catch ( \Exception $e ) {
                $this->logger->error( 'Failed to convert end date to UTC: ' . $e->getMessage() );
                // Fallback: use the same time as UTC
                $orm_args['end_date_utc'] = $orm_args['end_date'];
            }
        }

        // Handle all-day events using ORM field alias
        if ( ! empty( $all_data['_EventAllDay'] ) ) {
            $is_all_day = is_array( $all_data['_EventAllDay'] ) && in_array( '1', $all_data['_EventAllDay'] );
            $orm_args['all_day'] = $is_all_day;

            // For all-day events, if no end date is provided, set it to the same day
            if ( $is_all_day && ! empty( $orm_args['start_date'] ) && empty( $orm_args['end_date'] ) ) {
                $start_date = $this->format_date_for_tec( $all_data['_EventStartDate'] );
                if ( $start_date ) {
                    // Set end date to same day at 23:59:59
                    $end_date = date( 'Y-m-d 23:59:59', strtotime( $start_date ) );
                    $orm_args['end_date'] = $end_date;
                }
            }
        }

        if ( isset( $all_data['EventFeatured'] ) ) {
            $orm_args['featured'] = tribe_is_truthy( $all_data['EventFeatured'] );
        }

        if ( isset( $all_data['EventSticky'] ) ) {
            $orm_args['sticky'] = tribe_is_truthy( $all_data['EventSticky'] );
        }

        // Map cost field (int or float) - handle properly for ORM API
        if ( ! empty( $all_data['_EventCost'] ) ) {
            $cost = sanitize_text_field( $all_data['_EventCost'] );
            // Remove any currency symbols and clean the cost value
            $cost = preg_replace( '/[^0-9.]/', '', $cost );
            if ( is_numeric( $cost ) && floatval( $cost ) > 0 ) {
                $orm_args['cost'] = floatval( $cost );
            }
        }

        // Map currency fields using ORM field aliases
        if ( ! empty( $all_data['_EventCurrencySymbol'] ) ) {
            $orm_args['currency_symbol'] = sanitize_text_field( $all_data['_EventCurrencySymbol'] );
        }

        if ( ! empty( $all_data['_EventCurrencyPosition'] ) ) {
            $orm_args['currency_position'] = sanitize_text_field( $all_data['_EventCurrencyPosition'] );
        }

        // Map timezone
        if ( ! empty( $all_data['_EventTimezone'] ) ) {
            $timezone = sanitize_text_field( $all_data['_EventTimezone'] );
            // Validate timezone
            if ( in_array( $timezone, timezone_identifiers_list() ) ) {
                $orm_args['timezone'] = $timezone;
                $this->logger->info( 'Using timezone: ' . $timezone );
            } else {
                $this->logger->error( 'Invalid timezone: ' . $timezone );
                // Use default timezone as fallback
                $orm_args['timezone'] = wp_timezone_string();
                $this->logger->info( 'Using fallback timezone: ' . $orm_args['timezone'] );
            }
        } else {
            // Use default timezone if none provided
            $orm_args['timezone'] = wp_timezone_string();
            $this->logger->info( 'Using default timezone: ' . $orm_args['timezone'] );
        }

        // Ensure timezone is valid for TEC
        if ( ! in_array( $orm_args['timezone'], timezone_identifiers_list() ) ) {
            $this->logger->error( 'Invalid timezone for TEC: ' . $orm_args['timezone'] );
            $orm_args['timezone'] = 'UTC';
            $this->logger->info( 'Using UTC as fallback timezone' );
        }

        // Map URL field using ORM field alias
        if ( ! empty( $all_data['_EventURL'] ) ) {
            $orm_args['url'] = esc_url_raw( $all_data['_EventURL'] );
        }

        // Map image field
        if ( ! empty( $all_data['featured_image'] ) ) {
            $orm_args['image'] = intval( $all_data['featured_image'] );
        }

        // Map presentation fields using ORM field aliases
        if ( ! empty( $all_data['_EventShowMap'] ) ) {
            $show_map = is_array( $all_data['_EventShowMap'] ) && in_array( '1', $all_data['_EventShowMap'] );
            $orm_args['show_map'] = $show_map;
        }

        if ( ! empty( $all_data['_EventShowMapLink'] ) ) {
            $show_map_link = is_array( $all_data['_EventShowMapLink'] ) && in_array( '1', $all_data['_EventShowMapLink'] );
            $orm_args['show_map_link'] = $show_map_link;
        }

        if ( ! empty( $all_data['_EventHideFromUpcoming'] ) ) {
            $hide_from_upcoming = is_array( $all_data['_EventHideFromUpcoming'] ) && in_array( '1', $all_data['_EventHideFromUpcoming'] );
            $orm_args['hide_from_upcoming'] = $hide_from_upcoming;
        }

        // Map duration if available
        if ( ! empty( $all_data['_EventDuration'] ) ) {
            $orm_args['duration'] = intval( $all_data['_EventDuration'] );
        }

        // Handle venue data
        $venue_data = $this->venue_handler->handle_venue_data_from_form_data( $all_data );
        if ( ! empty( $venue_data ) ) {
            $orm_args['venue'] = $venue_data;
        }

        // Handle organizer data
        $organizer_data = $this->organizer_handler->handle_organizer_data_from_form_data( $all_data );
        if ( ! empty( $organizer_data ) ) {
            $orm_args['organizer'] = $organizer_data;
        }

        // Log the final ORM args for debugging
        $this->logger->info( 'Final ORM args: ' . print_r( $orm_args, true ) );

        return $orm_args;
    }

    /**
     * Convert datepicker format to TEC format
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     * @return string|false
     */
    private function convert_datepicker_to_tec_format( $date_string ) {
        if ( empty( $date_string ) ) {
            return false;
        }

        try {
            // Get the datepicker format from TEC settings
            if ( class_exists( 'Tribe__Date_Utils' ) ) {
                $datepicker_format = \Tribe__Date_Utils::datepicker_formats( tribe_get_option( 'datepickerFormat' ) );

                // Use TEC's datetime_from_format function
                $formatted_date = \Tribe__Date_Utils::datetime_from_format( $datepicker_format, $date_string );

                if ( $formatted_date ) {
                    return $formatted_date;
                }
            }

            // Fallback to direct formatting
            return $this->format_date_for_tec( $date_string );
        } catch ( \Exception $e ) {
            $this->logger->error( 'Failed to convert datepicker format: ' . $date_string . ' - ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Format date for TEC ORM
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     * @return string|false
     */
    private function format_date_for_tec( $date_string ) {
        if ( empty( $date_string ) ) {
            return false;
        }

        try {
            // Try multiple date formats that WPUF might use
            $formats_to_try = [
                'Y-m-d H:i:s',           // Standard MySQL format
                'Y-m-d H:i',             // Without seconds
                'Y-m-d',                 // Date only
                'm/d/Y H:i:s',           // US format with time
                'm/d/Y H:i',             // US format without seconds
                'm/d/Y',                 // US date only
                'd/m/Y H:i:s',           // European format with time
                'd/m/Y H:i',             // European format without time
                'd/m/Y',                 // European date only
                'Y-m-d\TH:i:s',          // ISO format
                'Y-m-d\TH:i',            // ISO format without seconds
            ];

            // First try TEC's build_date_object function
            if ( class_exists( 'Tribe__Date_Utils' ) ) {
                $date_object = \Tribe__Date_Utils::build_date_object( $date_string );
                if ( $date_object ) {
                    $formatted = $date_object->format( \Tribe__Date_Utils::DBDATETIMEFORMAT );
                    $this->logger->info( 'TEC formatted date: ' . $formatted . ' from: ' . $date_string );
                    return $formatted;
                }
            }

            // Try parsing with different formats
            foreach ( $formats_to_try as $format ) {
                $date_object = \DateTime::createFromFormat( $format, $date_string );
                if ( $date_object ) {
                    $formatted = $date_object->format( 'Y-m-d H:i:s' );
                    $this->logger->info( 'Parsed date with format ' . $format . ': ' . $formatted . ' from: ' . $date_string );
                    return $formatted;
                }
            }

            // Fallback to WordPress date parsing
            $timestamp = strtotime( $date_string );
            if ( $timestamp !== false ) {
                $formatted = date( 'Y-m-d H:i:s', $timestamp );
                $this->logger->info( 'WordPress parsed date: ' . $formatted . ' from: ' . $date_string );
                return $formatted;
            }

            $this->logger->error( 'Could not parse date: ' . $date_string );
            return false;
        } catch ( \Exception $e ) {
            $this->logger->error( 'Failed to format date: ' . $date_string . ' - ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Validate ORM requirements before saving
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     * @return bool
     */
    private function validate_orm_requirements( $orm_args ) {
        // Title is always required
        if ( empty( $orm_args['title'] ) ) {
            $this->logger->error( 'Event title is required' );
            return false;
        }

        // Check date requirements
        $has_start_date = ! empty( $orm_args['start_date'] );
        $has_end_date = ! empty( $orm_args['end_date'] );
        $has_duration = ! empty( $orm_args['duration'] );
        $is_all_day = ! empty( $orm_args['all_day'] ) && $orm_args['all_day'];

        // Must have start_date AND (end_date OR duration OR all_day)
        if ( ! $has_start_date ) {
            $this->logger->error( 'Event start date is required' );
            return false;
        }

        if ( ! $has_end_date && ! $has_duration && ! $is_all_day ) {
            $this->logger->error( 'Event must have either end date, duration, or be marked as all-day' );
            return false;
        }

        // Validate date formats if present
        if ( $has_start_date && ! $this->is_valid_date_format( $orm_args['start_date'] ) ) {
            $this->logger->error( 'Invalid start date format: ' . $orm_args['start_date'] );
            return false;
        }

        if ( $has_end_date && ! $this->is_valid_date_format( $orm_args['end_date'] ) ) {
            $this->logger->error( 'Invalid end date format: ' . $orm_args['end_date'] );
            return false;
        }

        // Validate that end date is after start date
        if ( $has_start_date && $has_end_date ) {
            $start_timestamp = strtotime( $orm_args['start_date'] );
            $end_timestamp = strtotime( $orm_args['end_date'] );

            if ( $start_timestamp === false || $end_timestamp === false ) {
                $this->logger->error( 'Invalid date timestamps' );
                return false;
            }

            if ( $end_timestamp <= $start_timestamp ) {
                $this->logger->error( 'End date must be after start date' );
                return false;
            }
        }

        return true;
    }

    /**
     * Check if date string is in valid format
     *
     * @since WPUF_SINCE
     *
     * @param string $date_string
     * @return bool
     */
    private function is_valid_date_format( $date_string ) {
        if ( empty( $date_string ) ) {
            return false;
        }

        // Check if it's in Y-m-d H:i:s format
        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
        if ( preg_match( $pattern, $date_string ) ) {
            return true;
        }

        // Check if it's a valid date string
        $timestamp = strtotime( $date_string );
        return $timestamp !== false;
    }

    /**
     * Temporarily disable TEC's save_post hooks to prevent conflicts
     *
     * @since WPUF_SINCE
     */
    private function temporarily_disable_tec_hooks() {
        // Store the current state
        $this->tec_hooks_disabled = true;

        // Remove TEC's main event meta processing
        remove_action( 'save_post', [ 'Tribe__Events__Main', 'addEventMeta' ], 15 );

        // Remove TEC's custom tables update hook if it exists
        if ( class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
            try {
                $tec_updates = tribe( 'tec.events.custom-tables.v1.updates.events' );
                if ( $tec_updates ) {
                    remove_action( 'save_post', [ $tec_updates, 'update' ], 10 );
                }
            } catch ( \Exception $e ) {
                // Service not bound, skip this hook
                $this->logger->info( 'TEC custom tables service not available, skipping hook removal' );
            }
        }

        // Remove TEC's linked posts processing - use instance method
        $linked_posts = tribe( 'tec.linked-posts' );
        if ( $linked_posts ) {
            remove_action( 'save_post', [ $linked_posts, 'handle_submission' ], 10 );
        }

        $this->logger->info( 'Temporarily disabled TEC save_post hooks' );
    }

    /**
     * Restore TEC's save_post hooks
     *
     * @since WPUF_SINCE
     */
    private function restore_tec_hooks() {
        // Only restore if we disabled them
        if ( ! isset( $this->tec_hooks_disabled ) || ! $this->tec_hooks_disabled ) {
            return;
        }

        // Re-add TEC's main event meta processing
        add_action( 'save_post', [ 'Tribe__Events__Main', 'addEventMeta' ], 15, 2 );

        // Re-add TEC's custom tables update hook if it exists
        if ( class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
            try {
                // Use instance method instead of static call
                $tec_updates = tribe( 'tec.events.custom-tables.v1.updates.events' );
                if ( $tec_updates ) {
                    add_action( 'save_post', [ $tec_updates, 'update' ], 10, 1 );
                }
            } catch ( \Exception $e ) {
                // Service not bound, skip this hook
                $this->logger->info( 'TEC custom tables service not available, skipping hook restoration' );
            }
        }

        // Re-add TEC's linked posts processing - use instance method
        $linked_posts = tribe( 'tec.linked-posts' );
        if ( $linked_posts ) {
            add_action( 'save_post', [ $linked_posts, 'handle_submission' ], 10, 2 );
        }

        $this->tec_hooks_disabled = false;
        $this->logger->info( 'Restored TEC save_post hooks' );
    }

    /**
     * Handle venue creation and association
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $venue_data
     * @return bool
     */
    private function handle_venue_creation( $post_id, $venue_data ) {
        // Use the compatibility manager to handle venue creation
        $venue_result = $this->compatibility_manager->handle_venue_creation( $venue_data );

        if ( is_wp_error( $venue_result ) ) {
            $this->logger->error( 'Venue creation failed: ' . $venue_result->get_error_message() );
            return false;
        }

        if ( ! empty( $venue_result['venue_id'] ) ) {
            // Associate venue with event using compatibility manager
            $association_result = $this->compatibility_manager->associate_venue_with_event( $post_id, $venue_result['venue_id'] );

            if ( is_wp_error( $association_result ) ) {
                $this->logger->error( 'Venue association failed: ' . $association_result->get_error_message() );
                return false;
            }

            $this->logger->info( 'Venue created and associated successfully with event ID: ' . $post_id );
            return true;
        }

        return false;
    }

    /**
     * Convert ORM args to TEC API format
     *
     * @since WPUF_SINCE
     *
     * @param array $orm_args
     * @return array
     */
    private function convert_orm_args_to_tec_api_format( $orm_args ) {
        $tec_args = [];

        // Map basic post fields
        if ( isset( $orm_args['title'] ) ) {
            $tec_args['post_title'] = $orm_args['title'];
        }

        if ( isset( $orm_args['description'] ) ) {
            $tec_args['post_content'] = $orm_args['description'];
        }

        if ( isset( $orm_args['excerpt'] ) ) {
            $tec_args['post_excerpt'] = $orm_args['excerpt'];
        }

        // Map event-specific fields to TEC API format
        if ( isset( $orm_args['start_date'] ) ) {
            $tec_args['EventStartDate'] = $orm_args['start_date'];
        }

        if ( isset( $orm_args['end_date'] ) ) {
            $tec_args['EventEndDate'] = $orm_args['end_date'];
        }

        if ( isset( $orm_args['start_date_utc'] ) ) {
            $tec_args['EventStartDateUTC'] = $orm_args['start_date_utc'];
        }

        if ( isset( $orm_args['end_date_utc'] ) ) {
            $tec_args['EventEndDateUTC'] = $orm_args['end_date_utc'];
        }

        if ( isset( $orm_args['timezone'] ) ) {
            $tec_args['EventTimezone'] = $orm_args['timezone'];
        }

        if ( isset( $orm_args['all_day'] ) ) {
            $tec_args['EventAllDay'] = $orm_args['all_day'] ? 'yes' : 'no';
        }

        if ( isset( $orm_args['cost'] ) ) {
            $tec_args['EventCost'] = $orm_args['cost'];
        }

        if ( isset( $orm_args['currency_symbol'] ) ) {
            $tec_args['EventCurrencySymbol'] = $orm_args['currency_symbol'];
        }

        if ( isset( $orm_args['currency_position'] ) ) {
            $tec_args['EventCurrencyPosition'] = $orm_args['currency_position'];
        }

        if ( isset( $orm_args['url'] ) ) {
            $tec_args['EventURL'] = $orm_args['url'];
        }

        if ( isset( $orm_args['show_map'] ) ) {
            $tec_args['EventShowMap'] = $orm_args['show_map'] ? '1' : '0';
        }

        if ( isset( $orm_args['show_map_link'] ) ) {
            $tec_args['EventShowMapLink'] = $orm_args['show_map_link'] ? '1' : '0';
        }

        if ( isset( $orm_args['hide_from_upcoming'] ) ) {
            $tec_args['EventHideFromUpcoming'] = $orm_args['hide_from_upcoming'] ? '1' : '0';
        }

        if ( isset( $orm_args['featured'] ) ) {
            $tec_args['EventFeatured'] = $orm_args['featured'] ? '1' : '0';
        }

        if ( isset( $orm_args['image'] ) ) {
            $tec_args['FeaturedImage'] = $orm_args['image'];
        }

        // Handle venue data
        if ( isset( $orm_args['venue'] ) ) {
            $tec_args['venue'] = $orm_args['venue'];
        }

        // Handle organizer data
        if ( isset( $orm_args['organizer'] ) ) {
            $tec_args['organizer'] = $orm_args['organizer'];
        }

        $this->logger->info( 'Converted to TEC API format: ' . print_r( $tec_args, true ) );

        return $tec_args;
    }

    /**
     * Save event meta using TEC's API
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $orm_args
     * @return bool
     */
    private function save_event_meta( $post_id, $orm_args ) {
        try {
            // Convert ORM args to TEC meta format
            $meta_data = [];

            // Map event-specific fields to TEC meta format
            if ( isset( $orm_args['start_date'] ) ) {
                $meta_data['_EventStartDate'] = $orm_args['start_date'];
            }

            if ( isset( $orm_args['end_date'] ) ) {
                $meta_data['_EventEndDate'] = $orm_args['end_date'];
            }

            if ( isset( $orm_args['start_date_utc'] ) ) {
                $meta_data['_EventStartDateUTC'] = $orm_args['start_date_utc'];
            }

            if ( isset( $orm_args['end_date_utc'] ) ) {
                $meta_data['_EventEndDateUTC'] = $orm_args['end_date_utc'];
            }

            if ( isset( $orm_args['timezone'] ) ) {
                $meta_data['_EventTimezone'] = $orm_args['timezone'];
            }

            if ( isset( $orm_args['all_day'] ) ) {
                $meta_data['_EventAllDay'] = $orm_args['all_day'] ? 'yes' : 'no';
            }

            if ( isset( $orm_args['cost'] ) ) {
                $meta_data['_EventCost'] = $orm_args['cost'];
            }

            if ( isset( $orm_args['currency_symbol'] ) ) {
                $meta_data['_EventCurrencySymbol'] = $orm_args['currency_symbol'];
            }

            if ( isset( $orm_args['currency_position'] ) ) {
                $meta_data['_EventCurrencyPosition'] = $orm_args['currency_position'];
            }

            if ( isset( $orm_args['url'] ) ) {
                $meta_data['_EventURL'] = $orm_args['url'];
            }

            if ( isset( $orm_args['show_map'] ) ) {
                $meta_data['_EventShowMap'] = $orm_args['show_map'] ? '1' : '0';
            }

            if ( isset( $orm_args['show_map_link'] ) ) {
                $meta_data['_EventShowMapLink'] = $orm_args['show_map_link'] ? '1' : '0';
            }

            if ( isset( $orm_args['hide_from_upcoming'] ) ) {
                $meta_data['_EventHideFromUpcoming'] = $orm_args['hide_from_upcoming'] ? '1' : '0';
            }

            if ( isset( $orm_args['featured'] ) ) {
                $meta_data['_tribe_featured'] = $orm_args['featured'] ? '1' : '0';
            }

            if ( isset( $orm_args['image'] ) ) {
                $meta_data['_thumbnail_id'] = $orm_args['image'];
            }

            $this->logger->info( 'Saving event meta: ' . print_r( $meta_data, true ) );

            // Save each meta field
            foreach ( $meta_data as $meta_key => $meta_value ) {
                update_post_meta( $post_id, $meta_key, $meta_value );
            }

            // Handle venue data
            if ( isset( $orm_args['venue'] ) ) {
                $this->handle_venue_creation( $post_id, $orm_args['venue'] );
            }

            // Handle organizer data
            if ( isset( $orm_args['organizer'] ) ) {
                $this->handle_organizer_creation( $post_id, $orm_args['organizer'] );
            }

            $this->logger->info( 'Event meta saved successfully for post ID: ' . $post_id );
            return true;

        } catch ( \Exception $e ) {
            $this->logger->error( 'Failed to save event meta: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Handle organizer creation and association
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $organizer_data
     * @return bool
     */
    private function handle_organizer_creation( $post_id, $organizer_data ) {
        // Use the compatibility manager to handle organizer creation
        $organizer_result = $this->compatibility_manager->handle_organizer_creation( $organizer_data );

        if ( is_wp_error( $organizer_result ) ) {
            $this->logger->error( 'Organizer creation failed: ' . $organizer_result->get_error_message() );
            return false;
        }

        if ( ! empty( $organizer_result['organizer_id'] ) ) {
            // Associate organizer with event using compatibility manager
            $association_result = $this->compatibility_manager->associate_organizer_with_event( $post_id, $organizer_result['organizer_id'] );

            if ( is_wp_error( $association_result ) ) {
                $this->logger->error( 'Organizer association failed: ' . $association_result->get_error_message() );
                return false;
            }

            $this->logger->info( 'Organizer created and associated successfully with event ID: ' . $post_id );
            return true;
        }

        return false;
    }
}
