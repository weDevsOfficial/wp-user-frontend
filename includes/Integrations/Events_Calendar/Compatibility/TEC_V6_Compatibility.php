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
                return $this->create_event_using_orm( $event_data );
            } else {
                // Update existing event using ORM API
                return $this->update_event_using_orm( $post_id, $event_data );
            }

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v6_orm_error', $e->getMessage() );
        }
    }

    /**
     * Create new event using TEC v6 ORM API
     * Following the official ORM documentation: https://docs.theeventscalendar.com/apis/orm/create/events/
     *
     * @param array $event_data
     * @return int|WP_Error
     */
    private function create_event_using_orm( $event_data ) {
        try {
            // Convert our data format to ORM API format
            $orm_data = $this->prepare_event_data_for_orm( $event_data );

            if ( empty( $orm_data ) ) {
                return new \WP_Error( 'tec_api_exception', 'No valid event data to create' );
            }

            // Create event using ORM API - following official documentation
            $event = tribe_events()->set( $orm_data )->create();

            if ( ! $event ) {
                return new \WP_Error( 'tec_api_exception', 'Failed to create event using ORM API' );
            }

            return $event->ID;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_api_exception', $e->getMessage() );
        }
    }

    /**
     * Update existing event using TEC v6 ORM API
     * Following the official ORM documentation: https://docs.theeventscalendar.com/apis/orm/create/events/
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|WP_Error
     */
    private function update_event_using_orm( $post_id, $event_data ) {
        try {
            // Convert our data format to ORM API format
            $orm_data = $this->prepare_event_data_for_orm( $event_data );

            if ( empty( $orm_data ) ) {
                return new \WP_Error( 'tec_api_exception', 'No valid event data to update' );
            }

            // Update event using ORM API - following official documentation
            $result = tribe_events()
                ->by( 'ID', $post_id )
                ->set( $orm_data )
                ->save();

            if ( false === $result ) {
                return new \WP_Error( 'tec_api_exception', 'Failed to update event using ORM API' );
            }

            return $post_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_api_exception', $e->getMessage() );
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
     * @return array
     */
    private function prepare_event_data_for_orm( $event_data ) {
        $orm_data = [];

        // Required fields according to ORM API documentation
        if ( ! empty( $event_data['post_title'] ) ) {
            $orm_data['title'] = $event_data['post_title'];
        }

        if ( ! empty( $event_data['post_content'] ) ) {
            $orm_data['content'] = $event_data['post_content'];
        }

        if ( ! empty( $event_data['post_status'] ) ) {
            $orm_data['status'] = $event_data['post_status'];
        } else {
            $orm_data['status'] = 'publish';
        }

        // Handle all-day events - convert 'yes'/'no' to boolean
        if ( isset( $event_data['_EventAllDay'] ) ) {
            $orm_data['all_day'] = ( 'yes' === $event_data['_EventAllDay'] );
        }

        // Handle start_date - maps to _EventStartDate
        if ( isset( $event_data['_EventStartDate'] ) ) {
            $orm_data['start_date'] = $event_data['_EventStartDate'];
        }

        // Handle end_date - maps to _EventEndDate
        if ( isset( $event_data['_EventEndDate'] ) ) {
            $orm_data['end_date'] = $event_data['_EventEndDate'];
        }

        // Handle start_date_utc - maps to _EventStartDateUTC
        if ( isset( $event_data['_EventStartDateUTC'] ) ) {
            $orm_data['start_date_utc'] = $event_data['_EventStartDateUTC'];
        }

        // Handle end_date_utc - maps to _EventEndDateUTC
        if ( isset( $event_data['_EventEndDateUTC'] ) ) {
            $orm_data['end_date_utc'] = $event_data['_EventEndDateUTC'];
        }

        // Handle duration - maps to _EventDuration
        if ( isset( $event_data['_EventDuration'] ) ) {
            $orm_data['duration'] = intval( $event_data['_EventDuration'] );
        }

        // Handle timezone - maps to _EventTimezone
        if ( isset( $event_data['_EventTimezone'] ) ) {
            $orm_data['timezone'] = $event_data['_EventTimezone'];
        }

        // Handle cost - maps to _EventCost
        if ( isset( $event_data['_EventCost'] ) ) {
            $cost = floatval( $event_data['_EventCost'] );
            if ( $cost > 0 ) {
                $orm_data['cost'] = $cost;
            }
        }

        // Handle currency symbol - maps to _EventCurrencySymbol
        if ( isset( $event_data['_EventCurrencySymbol'] ) ) {
            $orm_data['currency_symbol'] = $event_data['_EventCurrencySymbol'];
        }

        // Handle currency position - maps to _EventCurrencyPosition
        if ( isset( $event_data['_EventCurrencyPosition'] ) ) {
            $orm_data['currency_position'] = $event_data['_EventCurrencyPosition'];
        }

        // Handle event URL - maps to _EventURL
        if ( isset( $event_data['_EventURL'] ) ) {
            $orm_data['website'] = $event_data['_EventURL'];
        }

        // Handle featured image
        if ( isset( $event_data['featured_image'] ) ) {
            $orm_data['featured_image'] = $event_data['featured_image'];
        }

        // Handle organizer data
        if ( isset( $event_data['organizer'] ) ) {
            $orm_data['organizer'] = $event_data['organizer'];
        }

        // Handle event settings
        if ( isset( $event_data['_EventShowMap'] ) ) {
            $orm_data['show_map'] = ( 'yes' === $event_data['_EventShowMap'] );
        }

        if ( isset( $event_data['_EventShowMapLink'] ) ) {
            $orm_data['show_map_link'] = ( 'yes' === $event_data['_EventShowMapLink'] );
        }

        if ( isset( $event_data['_EventHideFromUpcoming'] ) ) {
            $orm_data['hide_from_listings'] = ( 'yes' === $event_data['_EventHideFromUpcoming'] );
        }

        if ( isset( $event_data['_EventShowInCalendar'] ) ) {
            $orm_data['sticky'] = ( 'yes' === $event_data['_EventShowInCalendar'] );
        }

        if ( isset( $event_data['featured'] ) ) {
            $orm_data['featured'] = ( 'yes' === $event_data['featured'] );
        }

        return $orm_data;
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
