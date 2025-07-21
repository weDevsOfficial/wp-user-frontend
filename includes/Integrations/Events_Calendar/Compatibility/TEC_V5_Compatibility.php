<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC v5 Compatibility Handler
 *
 * Handles The Events Calendar v5.x API calls and functionality
 *
 * @since WPUF_SINCE
 */
class TEC_V5_Compatibility {

    /**
     * Save event using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|WP_Error
     */
    public function save_event( $post_id, $event_data ) {
        try {
            // Use TEC's v5 API method
            $result = \Tribe__Events__API::saveEventMeta( $post_id, $event_data, get_post( $post_id ) );

            if ( false === $result ) {
                return false;
            }

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v5_error', $e->getMessage() );
        }
    }

    /**
     * Create venue using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param array $venue_data
     * @return int|WP_Error
     */
    public function create_venue( $venue_data ) {
        try {
            // Use TEC's v5 API method
            $venue_id = \Tribe__Events__API::createVenue( $venue_data );

            if ( is_wp_error( $venue_id ) ) {
                return $venue_id;
            }

            return $venue_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v5_venue_error', $e->getMessage() );
        }
    }

    /**
     * Create organizer using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return int|WP_Error
     */
    public function create_organizer( $organizer_data ) {
        try {
            // Use TEC's v5 API method
            $organizer_id = \Tribe__Events__API::createOrganizer( $organizer_data );

            if ( is_wp_error( $organizer_id ) ) {
                return $organizer_id;
            }

            return $organizer_id;

        } catch ( \Exception $e ) {
            return new \WP_Error( 'tec_v5_organizer_error', $e->getMessage() );
        }
    }

    /**
     * Get venue data using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return array|false
     */
    public function get_venue_data( $venue_id ) {
        try {
            // Use the correct TEC function to get venue object
            $venue = tribe_get_venue_object( $venue_id, ARRAY_A );
            return $venue;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Get organizer data using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return array|false
     */
    public function get_organizer_data( $organizer_id ) {
        try {
            // Use the correct TEC function to get organizer object
            $organizer = tribe_get_organizer_object( $organizer_id, ARRAY_A );
            return $organizer;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Get all venues using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_venues() {
        try {
            // Use the correct TEC function to get venues
            $venues = tribe_get_venues( false, -1, true );
            return $venues;
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Get all organizers using TEC v5 API
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_organizers() {
        try {
            // Use the correct TEC function to get organizers
            $organizers = tribe_get_organizers( false, -1, true );
            return $organizers;
        } catch ( \Exception $e ) {
            return [];
        }
    }

    /**
     * Check if TEC v5 is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_active() {
        return class_exists( 'Tribe__Events__API' ) && class_exists( 'Tribe__Events__Main' );
    }
} 