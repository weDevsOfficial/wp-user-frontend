<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Handlers;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Constants;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper;

/**
 * Venue Handler for Events Calendar
 *
 * Handles all venue creation and management operations
 *
 * @since WPUF_SINCE
 */
class Venue_Handler {

    /**
     * Compatibility manager instance
     *
     * @var TEC_Compatibility_Manager
     */
    private $compatibility_manager;

    /**
     * Constructor
     *
     * @param TEC_Compatibility_Manager $compatibility_manager
     */
    public function __construct( $compatibility_manager ) {
        $this->compatibility_manager = $compatibility_manager;
    }

    /**
     * Handle venue data from form data
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @return array|null
     */
    public function handle_venue_data_from_form_data( $form_data ) {
        // Check if existing venue is selected
        if ( ! empty( $form_data['_EventVenueID'] ) && is_numeric( $form_data['_EventVenueID'] ) ) {
            return [ 'VenueID' => intval( $form_data['_EventVenueID'] ) ];
        }

        // Check if we need to create a new venue
        if ( ! empty( $form_data['venue_name'] ) ) {
            return $this->create_venue_from_form_data( $form_data );
        }

        return null;
    }

    /**
     * Create a new venue from form data
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @return array|null
     */
    private function create_venue_from_form_data( $form_data ) {
        $venue_data = [
            'Venue' => sanitize_text_field( $form_data['venue_name'] ),
        ];

        // Add venue address if provided
        if ( ! empty( $form_data['venue_address'] ) && is_array( $form_data['venue_address'] ) ) {
            $address = $form_data['venue_address'];

            if ( ! empty( $address['street_address'] ) ) {
                $venue_data['Address'] = sanitize_text_field( $address['street_address'] );
            }

            if ( ! empty( $address['city_name'] ) ) {
                $venue_data['City'] = sanitize_text_field( $address['city_name'] );
            }

            if ( ! empty( $address['state'] ) ) {
                $venue_data['State'] = sanitize_text_field( $address['state'] );
            }

            if ( ! empty( $address['zip'] ) ) {
                $venue_data['Zip'] = sanitize_text_field( $address['zip'] );
            }

            if ( ! empty( $address['country_select'] ) ) {
                $venue_data['Country'] = sanitize_text_field( $address['country_select'] );
            }
        }

        // Add venue phone if provided
        if ( ! empty( $form_data['venue_phone'] ) ) {
            $venue_data['Phone'] = sanitize_text_field( $form_data['venue_phone'] );
        }

        // Add venue website if provided
        if ( ! empty( $form_data['venue_website'] ) ) {
            $venue_data['Website'] = esc_url_raw( $form_data['venue_website'] );
        }

        // Sanitize venue data
        $venue_data = TEC_Helper::sanitize_venue_data( $venue_data );

        // Create venue using compatibility manager
        $venue_id = $this->compatibility_manager->create_venue( $venue_data );

        if ( is_wp_error( $venue_id ) ) {
            $this->logger->error( 'Venue creation failed: ' . $venue_id->get_error_message() );
            return null;
        }

        $this->logger->info( 'Venue created successfully with ID: ' . $venue_id );
        return [ 'VenueID' => $venue_id ];
    }

    /**
     * Handle venue data from form submission
     *
     * @since WPUF_SINCE
     *
     * @return array|null
     */
    public function handle_venue_data() {
        // Check if existing venue is selected
        if ( ! empty( $_POST['_EventVenueID'] ) && is_numeric( $_POST['_EventVenueID'] ) ) {
            return [ 'VenueID' => intval( $_POST['_EventVenueID'] ) ];
        }

        // Check if we need to create a new venue
        if ( ! empty( $_POST['venue_name'] ) ) {
            return $this->create_venue();
        }

        return null;
    }

    /**
     * Create a new venue
     *
     * @since WPUF_SINCE
     *
     * @return array|null
     */
    private function create_venue() {
        $venue_data = [
            'Venue' => sanitize_text_field( wp_unslash( $_POST['venue_name'] ) ),
        ];

        // Add venue address if provided
        if ( ! empty( $_POST['venue_address'] ) && is_array( $_POST['venue_address'] ) ) {
            $address = $_POST['venue_address'];

            if ( ! empty( $address['street_address'] ) ) {
                $venue_data['Address'] = sanitize_text_field( wp_unslash( $address['street_address'] ) );
            }

            if ( ! empty( $address['city_name'] ) ) {
                $venue_data['City'] = sanitize_text_field( wp_unslash( $address['city_name'] ) );
            }

            if ( ! empty( $address['state'] ) ) {
                $venue_data['State'] = sanitize_text_field( wp_unslash( $address['state'] ) );
            }

            if ( ! empty( $address['zip'] ) ) {
                $venue_data['Zip'] = sanitize_text_field( wp_unslash( $address['zip'] ) );
            }

            if ( ! empty( $address['country_select'] ) ) {
                $venue_data['Country'] = sanitize_text_field( wp_unslash( $address['country_select'] ) );
            }
        }

        // Add venue phone if provided
        if ( ! empty( $_POST['venue_phone'] ) ) {
            $venue_data['Phone'] = sanitize_text_field( wp_unslash( $_POST['venue_phone'] ) );
        }

        // Add venue website if provided
        if ( ! empty( $_POST['venue_website'] ) ) {
            $venue_data['Website'] = esc_url_raw( wp_unslash( $_POST['venue_website'] ) );
        }

        // Sanitize venue data
        $venue_data = TEC_Helper::sanitize_venue_data( $venue_data );

        // Create venue using compatibility manager
        $venue_id = $this->compatibility_manager->create_venue( $venue_data );

        if ( is_wp_error( $venue_id ) ) {
            $this->logger->error( 'Venue creation failed: ' . $venue_id->get_error_message() );
            return null;
        }

        $this->logger->info( 'Venue created successfully with ID: ' . $venue_id );
        return [ 'VenueID' => $venue_id ];
    }

    /**
     * Validate venue data
     *
     * @since WPUF_SINCE
     *
     * @param array $venue_data
     * @return bool
     */
    public function validate_venue_data( $venue_data ) {
        // If venue ID is provided, check if it exists
        if ( ! empty( $venue_data['VenueID'] ) ) {
            $venue = get_post( $venue_data['VenueID'] );
            if ( ! $venue || $venue->post_type !== 'tribe_venue' ) {
                $this->logger->error( 'Invalid venue ID: ' . $venue_data['VenueID'] );
                return false;
            }
            return true;
        }

        // If creating new venue, validate required fields
        if ( ! empty( $venue_data['Venue'] ) ) {
            if ( empty( $venue_data['Venue'] ) ) {
                $this->logger->error( 'Venue name is required' );
                return false;
            }

            // Validate address fields if present
            if ( ! empty( $venue_data['Address'] ) && ! empty( $venue_data['City'] ) ) {
                // Address validation passed
            }

            return true;
        }

        $this->logger->error( 'No venue data provided' );
        return false;
    }

    /**
     * Get venue data
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return array|false
     */
    public function get_venue_data( $venue_id ) {
        if ( ! $this->compatibility_manager ) {
            return false;
        }

        return $this->compatibility_manager->get_current_compatibility_handler()->get_venue_data( $venue_id );
    }

    /**
     * Get all venues
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_venues() {
        if ( ! $this->compatibility_manager ) {
            return [];
        }

        return $this->compatibility_manager->get_current_compatibility_handler()->get_all_venues();
    }

    /**
     * Check if venue exists
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return bool
     */
    public function venue_exists( $venue_id ) {
        $venue = get_post( $venue_id );
        return $venue && $venue->post_type === 'tribe_venue';
    }

    /**
     * Get venue name
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return string
     */
    public function get_venue_name( $venue_id ) {
        $venue = get_post( $venue_id );
        if ( $venue && $venue->post_type === 'tribe_venue' ) {
            return $venue->post_title;
        }
        return '';
    }

    /**
     * Get venue address
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return array
     */
    public function get_venue_address( $venue_id ) {
        $address = [];

        $address['street'] = get_post_meta( $venue_id, '_VenueAddress', true );
        $address['city'] = get_post_meta( $venue_id, '_VenueCity', true );
        $address['state'] = get_post_meta( $venue_id, '_VenueState', true );
        $address['zip'] = get_post_meta( $venue_id, '_VenueZip', true );
        $address['country'] = get_post_meta( $venue_id, '_VenueCountry', true );

        return array_filter( $address );
    }

    /**
     * Get venue contact info
     *
     * @since WPUF_SINCE
     *
     * @param int $venue_id
     * @return array
     */
    public function get_venue_contact_info( $venue_id ) {
        $contact = [];

        $contact['phone'] = get_post_meta( $venue_id, '_VenuePhone', true );
        $contact['website'] = get_post_meta( $venue_id, '_VenueWebsite', true );

        return array_filter( $contact );
    }
}
