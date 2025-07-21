<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC v6 Compatibility Handler
 *
 * Handles The Events Calendar v6.x API calls using the new ORM API
 * Documentation: https://docs.theeventscalendar.com/apis/orm/create/events/
 *
 * @since WPUF_SINCE
 */
class TEC_V6_Compatibility {

    /**
     * Save event using TEC v6 ORM API
     * Following the complete event creation process as documented in event-creation-process.md
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|WP_Error
     */
    public function save_event( $post_id, $event_data ) {
        try {
            // Check if this is a new event or existing event
            $post = get_post( $post_id );
            $is_new_event = ! $post || $post->post_type !== 'tribe_events';

            if ( $is_new_event ) {
                // Create new event using ORM API
                $result = $this->create_event_using_orm( $event_data );
            } else {
                // Update existing event using ORM API
                $result = $this->update_event_using_orm( $post_id, $event_data );
            }
            
            if ( is_wp_error( $result ) ) {
                return $result;
            }

            // Get the actual event ID
            $event_id = $post_id; // Default to post_id
            if ( $is_new_event ) {
                // For new events, result is the new event ID
                $event_id = $result;
            }

            // Step 2: Update custom tables (v6.0+)
            $custom_tables_result = $this->update_custom_tables( $event_id );
            
            if ( is_wp_error( $custom_tables_result ) ) {
                return $custom_tables_result;
            }

            // Step 3: Perform post-creation actions
            $this->perform_post_creation_actions( $event_id );

            return $event_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v6_orm_error', $e->getMessage() );
        }
    }

    /**
     * Create new event using TEC v6 ORM API
     *
     * @param array $event_data
     * @return int|WP_Error
     */
    private function create_event_using_orm( $event_data ) {
        try {
            // Convert our data format to ORM API format
            $orm_data = $this->prepare_event_data_for_orm( $event_data, 0 );
            
            if ( empty( $orm_data ) ) {
                return new \WP_Error( 'tec_api_exception', 'No valid event data to create' );
            }

            // Create event using ORM API explicitly
            $event_id = tribe_events()
                ->set( $orm_data )
                ->create();

            if ( ! $event_id ) {
                return new \WP_Error( 'tec_api_exception', 'Failed to create event using ORM API' );
            }

            return $event_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_api_exception', $e->getMessage() );
        }
    }

    /**
     * Update existing event using TEC v6 ORM API
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|WP_Error
     */
    private function update_event_using_orm( $post_id, $event_data ) {
        try {
            // Convert our data format to ORM API format
            $orm_data = $this->prepare_event_data_for_orm( $event_data, $post_id );
            
            if ( empty( $orm_data ) ) {
                return new \WP_Error( 'tec_api_exception', 'No valid event data to update' );
            }

            // Update event using ORM API explicitly
            $result = tribe_events()
                ->by( 'ID', $post_id )
                ->set( $orm_data )
                ->save();

            if ( false === $result ) {
                return new \WP_Error( 'tec_api_exception', 'Failed to update event using ORM API' );
            }

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_api_exception', $e->getMessage() );
        }
    }

    /**
     * Save event using TEC's native API (following Step 3 from event-creation-process.md)
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|WP_Error
     */
    private function save_event_using_tec_api( $post_id, $event_data ) {
        try {
            // Convert our data format to ORM API format
            $orm_data = $this->prepare_event_data_for_orm( $event_data, $post_id );
            
            if ( empty( $orm_data ) ) {
                return new \WP_Error( 'tec_api_exception', 'No valid event data to create' );
            }

            // Create event using ORM API explicitly
            $event_id = tribe_events()
                ->set( $orm_data )
                ->create();

            if ( ! $event_id ) {
                return new \WP_Error( 'tec_api_exception', 'Failed to create event using ORM API' );
            }

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_api_exception', $e->getMessage() );
        }
    }

    /**
     * Convert our data format to TEC's expected format
     *
     * @param array $event_data
     * @param int   $post_id
     * @return array
     */
    private function convert_to_tec_format( $event_data, $post_id ) {
        $tec_data = [];

        // Map our field names to TEC's expected field names
        $field_mapping = [
            'EventStartDate' => '_EventStartDate',
            'EventEndDate' => '_EventEndDate',
            'EventStartDateUTC' => '_EventStartDateUTC',
            'EventEndDateUTC' => '_EventEndDateUTC',
            'EventDuration' => '_EventDuration',
            'EventAllDay' => '_EventAllDay',
            'EventTimezone' => '_EventTimezone',
            'EventCost' => '_EventCost',
            'EventCurrencySymbol' => '_EventCurrencySymbol',
            'EventURL' => '_EventURL',
            'EventShowMap' => '_EventShowMap',
            'EventShowMapLink' => '_EventShowMapLink',
            'EventHideFromUpcoming' => '_EventHideFromUpcoming',
        ];

        // Convert field names
        foreach ( $field_mapping as $our_field => $tec_field ) {
            if ( isset( $event_data[ $our_field ] ) ) {
                $tec_data[ $tec_field ] = $event_data[ $our_field ];
            }
        }

        // Handle venue data
        if ( isset( $event_data['venue'] ) ) {
            if ( isset( $event_data['venue']['VenueID'] ) ) {
                $tec_data['_EventVenueID'] = $event_data['venue']['VenueID'];
            }
        }

        // Handle organizer data
        if ( isset( $event_data['organizer'] ) ) {
            if ( isset( $event_data['organizer']['OrganizerID'] ) ) {
                $tec_data['_EventOrganizerID'] = $event_data['organizer']['OrganizerID'];
            }
        }

        // Ensure required fields are present
        $post = get_post( $post_id );
        if ( $post ) {
            if ( empty( $tec_data['_EventStartDate'] ) ) {
                $now = new \DateTimeImmutable( 'now', wp_timezone() );
                $tec_data['_EventStartDate'] = $now->format( 'Y-m-d H:i:s' );
                $tec_data['_EventStartDateUTC'] = $now->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
            }

            if ( empty( $tec_data['_EventEndDate'] ) ) {
                if ( ! empty( $tec_data['_EventStartDate'] ) ) {
                    $start_date = new \DateTimeImmutable( $tec_data['_EventStartDate'], wp_timezone() );
                    $end_date = $start_date->modify( '+1 hour' );
                    $tec_data['_EventEndDate'] = $end_date->format( 'Y-m-d H:i:s' );
                    $tec_data['_EventEndDateUTC'] = $end_date->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
                }
            }

            if ( empty( $tec_data['_EventTimezone'] ) ) {
                $tec_data['_EventTimezone'] = wp_timezone_string();
            }

            if ( ! isset( $tec_data['_EventAllDay'] ) ) {
                $tec_data['_EventAllDay'] = 'no';
            }
        }

        return $tec_data;
    }

    /**
     * Update custom tables (Step 4 from event-creation-process.md)
     *
     * @param int $post_id
     * @return bool|WP_Error
     */
    private function update_custom_tables( $post_id ) {
        try {
            // Check if custom tables are available (TEC v6.0+)
            if ( ! class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
                return true;
            }

            // Use TEC's custom tables update mechanism
            $updater = new \TEC\Events\Custom_Tables\V1\Updates\Events();
            $result = $updater->update( $post_id );

            if ( false === $result ) {
                return new \WP_Error( 'custom_tables_error', 'Failed to update custom tables' );
            }

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'custom_tables_exception', $e->getMessage() );
        }
    }

    /**
     * Perform post-creation actions (Step 5 from event-creation-process.md)
     *
     * @param int $post_id
     */
    private function perform_post_creation_actions( $post_id ) {
        try {
            // 1. Update known date range
            if ( class_exists( 'Tribe__Events__Dates__Known_Range' ) ) {
                $known_range = \Tribe__Events__Dates__Known_Range::instance();
                $known_range->maybe_update_known_range();
            }

            // 2. Publish associated venues and organizers
            $this->publish_linked_posts( $post_id );

            // 3. Clear caches
            $this->clear_event_caches( $post_id );

        } catch ( \Exception $e ) {
            // No logging removed
        }
    }

    /**
     * Publish linked posts (venues and organizers)
     *
     * @param int $post_id
     */
    private function publish_linked_posts( $post_id ) {
        $post_meta = get_post_custom( $post_id );
        
        $linked_post_prefixes = [
            'venue'     => '_EventVenue',
            'organizer' => '_EventOrganizer',
        ];

        foreach ( $linked_post_prefixes as $type => $linked_post_prefix ) {
            $id_index = "{$linked_post_prefix}ID";

            if ( empty( $post_meta[ $id_index ] ) ) {
                continue;
            }

            $linked_post_ids = is_array( $post_meta[ $id_index ] ) ? $post_meta[ $id_index ] : [ $post_meta[ $id_index ] ];

            foreach ( $linked_post_ids as $linked_post_id ) {
                if ( ! $linked_post_id ) {
                    continue;
                }

                if ( in_array( get_post_status( $linked_post_id ), [ 'publish', 'private' ], true ) ) {
                    continue;
                }

                wp_publish_post( $linked_post_id );
            }
        }
    }

    /**
     * Clear event caches
     *
     * @param int $post_id
     */
    private function clear_event_caches( $post_id ) {
        // Clear TEC caches
        if ( function_exists( 'tribe_cache' ) ) {
            tribe_cache()->reset();
        }

        // Clear WordPress object cache for this post
        clean_post_cache( $post_id );

        // Clear any TEC-specific caches
        if ( class_exists( 'Tribe__Events__Cache_Listener' ) ) {
            \Tribe__Events__Cache_Listener::instance()->save_post( $post_id );
        }
    }

    /**
     * Create venue using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @param array $venue_data
     * @return int|WP_Error
     */
    public function create_venue( $venue_data ) {
        try {
            // Convert venue data to ORM API format
            $orm_venue_data = $this->prepare_venue_data_for_orm( $venue_data );
            
            if ( empty( $orm_venue_data ) ) {
                return new \WP_Error( 'tec_v6_venue_error', 'No valid venue data provided' );
            }

            // Create venue using ORM API
            $venue_id = tribe_venues()
                ->set( $orm_venue_data )
                ->create();

            if ( ! $venue_id ) {
                return new \WP_Error( 'tec_v6_venue_error', 'Failed to create venue' );
            }

            return $venue_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v6_venue_error', $e->getMessage() );
        }
    }

    /**
     * Create organizer using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return int|WP_Error
     */
    public function create_organizer( $organizer_data ) {
        try {
            // Convert organizer data to ORM API format
            $orm_organizer_data = $this->prepare_organizer_data_for_orm( $organizer_data );
            
            if ( empty( $orm_organizer_data ) ) {
                return new \WP_Error( 'tec_v6_organizer_error', 'No valid organizer data provided' );
            }

            // Create organizer using ORM API
            $organizer_id = tribe_organizers()
                ->set( $orm_organizer_data )
                ->create();

            if ( ! $organizer_id ) {
                return new \WP_Error( 'tec_v6_organizer_error', 'Failed to create organizer' );
            }

            return $organizer_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v6_organizer_error', $e->getMessage() );
        }
    }

    /**
     * Get venue data using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return array|false
     */
    public function get_venue_data( $venue_id ) {
        try {
            // Use the ORM API to get venue data
            $venue = tribe_venues()->by( 'ID', $venue_id )->first();
            return $venue ? $venue->to_array() : false;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Get organizer data using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return array|false
     */
    public function get_organizer_data( $organizer_id ) {
        try {
            // Use the ORM API to get organizer data
            $organizer = tribe_organizers()->by( 'ID', $organizer_id )->first();
            return $organizer ? $organizer->to_array() : false;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Get all venues using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_venues() {
        try {
            // Use the ORM API to get all venues
            $venues = tribe_venues()->per_page( -1 )->all();
            return $venues ? $venues->to_array() : [];
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Get all organizers using TEC v6 ORM API
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_organizers() {
        try {
            // Use the ORM API to get all organizers
            $organizers = tribe_organizers()->per_page( -1 )->all();
            return $organizers ? $organizers->to_array() : [];
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Prepare event data for ORM API
     * Based on TEC ORM API documentation: https://docs.theeventscalendar.com/apis/orm/create/events/
     *
     * @since WPUF_SINCE
     *
     * @param array $event_data
     * @param int   $post_id
     * @return array
     */
    private function prepare_event_data_for_orm( $event_data, $post_id ) {
        $orm_data = [];
        
        // Get post data for required fields
        $post = get_post( $post_id );
        if ( ! $post ) {
            return [];
        }

        // Required fields according to ORM API documentation
        $orm_data['title'] = $post->post_title;
        $orm_data['content'] = $post->post_content;
        $orm_data['status'] = $post->post_status;
        
        // Handle all-day events - convert 'yes'/'no' to boolean
        if ( isset( $event_data['EventAllDay'] ) ) {
            $orm_data['all_day'] = ( 'yes' === $event_data['EventAllDay'] );
        }
        
        // Handle start_date - optional field (maps to _EventStartDate)
        if ( isset( $event_data['EventStartDate'] ) ) {
            $orm_data['start_date'] = $event_data['EventStartDate'];
        }
        
        // Handle end_date - optional field (maps to _EventEndDate)
        if ( isset( $event_data['EventEndDate'] ) ) {
            $orm_data['end_date'] = $event_data['EventEndDate'];
        }
        
        // Handle start_date_utc (maps to _EventStartDateUTC)
        if ( isset( $event_data['EventStartDateUTC'] ) ) {
            $orm_data['start_date_utc'] = $event_data['EventStartDateUTC'];
        }
        
        // Handle end_date_utc (maps to _EventEndDateUTC)
        if ( isset( $event_data['EventEndDateUTC'] ) ) {
            $orm_data['end_date_utc'] = $event_data['EventEndDateUTC'];
        }
        
        // Handle duration (maps to _EventDuration)
        if ( isset( $event_data['EventDuration'] ) ) {
            $orm_data['duration'] = intval( $event_data['EventDuration'] );
        }
        
        // Handle timezone (maps to _EventTimezone)
        if ( isset( $event_data['EventTimezone'] ) ) {
            $orm_data['timezone'] = $event_data['EventTimezone'];
        }
        
        // Handle cost - numeric value (maps to _EventCost)
        if ( isset( $event_data['EventCost'] ) ) {
            $cost = floatval( $event_data['EventCost'] );
            if ( $cost > 0 ) {
                $orm_data['cost'] = $cost;
            }
        }
        
        // Handle currency symbol (maps to _EventCurrencySymbol)
        if ( isset( $event_data['EventCurrencySymbol'] ) ) {
            $orm_data['currency_symbol'] = $event_data['EventCurrencySymbol'];
        }
        
        // Handle currency position (maps to _EventCurrencyPosition)
        if ( isset( $event_data['EventCurrencyPosition'] ) ) {
            $orm_data['currency_position'] = $event_data['EventCurrencyPosition'];
        }
        
        // Handle event URL (maps to _EventURL)
        if ( isset( $event_data['EventURL'] ) ) {
            $orm_data['website'] = $event_data['EventURL'];
        }
        
        // Handle featured image
        if ( isset( $event_data['FeaturedImage'] ) ) {
            $orm_data['featured_image'] = $event_data['FeaturedImage'];
        }
        
        // Handle venue data
        if ( isset( $event_data['venue'] ) ) {
            $orm_data['venue'] = $event_data['venue'];
        }
        
        // Handle organizer data
        if ( isset( $event_data['organizer'] ) ) {
            $orm_data['organizer'] = $event_data['organizer'];
        }
        
        // Handle event settings
        if ( isset( $event_data['EventShowMap'] ) ) {
            $orm_data['show_map'] = ( 'yes' === $event_data['EventShowMap'] );
        }
        
        if ( isset( $event_data['EventShowMapLink'] ) ) {
            $orm_data['show_map_link'] = ( 'yes' === $event_data['EventShowMapLink'] );
        }
        
        if ( isset( $event_data['EventHideFromUpcoming'] ) ) {
            $orm_data['hide_from_listings'] = ( 'yes' === $event_data['EventHideFromUpcoming'] );
        }
        
        if ( isset( $event_data['EventShowInCalendar'] ) ) {
            $orm_data['sticky'] = ( 'yes' === $event_data['EventShowInCalendar'] );
        }
        
        if ( isset( $event_data['featured'] ) ) {
            $orm_data['featured'] = ( 'yes' === $event_data['featured'] );
        }

        return $orm_data;
    }

    /**
     * Prepare venue data for ORM API
     *
     * @since WPUF_SINCE
     *
     * @param array $venue_data
     * @return array
     */
    private function prepare_venue_data_for_orm( $venue_data ) {
        $orm_venue_data = [];
        
        // Required field: title
        if ( isset( $venue_data['Venue'] ) ) {
            $orm_venue_data['title'] = $venue_data['Venue'];
        }
        
        // Optional fields
        if ( isset( $venue_data['Address'] ) ) {
            $orm_venue_data['address'] = $venue_data['Address'];
        }
        
        if ( isset( $venue_data['City'] ) ) {
            $orm_venue_data['city'] = $venue_data['City'];
        }
        
        if ( isset( $venue_data['State'] ) ) {
            $orm_venue_data['state'] = $venue_data['State'];
        }
        
        if ( isset( $venue_data['Province'] ) ) {
            $orm_venue_data['province'] = $venue_data['Province'];
        }
        
        if ( isset( $venue_data['Zip'] ) ) {
            $orm_venue_data['zip'] = $venue_data['Zip'];
        }
        
        if ( isset( $venue_data['Country'] ) ) {
            $orm_venue_data['country'] = $venue_data['Country'];
        }
        
        if ( isset( $venue_data['Phone'] ) ) {
            $orm_venue_data['phone'] = $venue_data['Phone'];
        }
        
        if ( isset( $venue_data['Website'] ) ) {
            $orm_venue_data['website'] = $venue_data['Website'];
        }
        
        if ( isset( $venue_data['EventShowMap'] ) ) {
            $orm_venue_data['show_map'] = ( 'yes' === $venue_data['EventShowMap'] );
        }
        
        if ( isset( $venue_data['EventShowMapLink'] ) ) {
            $orm_venue_data['show_map_link'] = ( 'yes' === $venue_data['EventShowMapLink'] );
        }

        return $orm_venue_data;
    }

    /**
     * Prepare organizer data for ORM API
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return array
     */
    private function prepare_organizer_data_for_orm( $organizer_data ) {
        $orm_organizer_data = [];
        
        // Required field: title
        if ( isset( $organizer_data['Organizer'] ) ) {
            $orm_organizer_data['title'] = $organizer_data['Organizer'];
        }
        
        // Optional fields
        if ( isset( $organizer_data['Phone'] ) ) {
            $orm_organizer_data['phone'] = $organizer_data['Phone'];
        }
        
        if ( isset( $organizer_data['Email'] ) ) {
            $orm_organizer_data['email'] = $organizer_data['Email'];
        }
        
        if ( isset( $organizer_data['Website'] ) ) {
            $orm_organizer_data['website'] = $organizer_data['Website'];
        }

        return $orm_organizer_data;
    }

    /**
     * Check if TEC v6 is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_active() {
        return class_exists( 'Tribe__Events__Main' ) && function_exists( 'tribe_events' );
    }

    /**
     * Test ORM API functionality
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function test_orm_api() {
        try {
            // Test if ORM API is available
            $test_query = tribe_events()->per_page( 1 );
            return $test_query instanceof \Tribe__Repository__Interface;
        } catch ( \Exception $e ) {
            return false;
        }
    }
} 