<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Handlers;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Constants;
use WeDevs\Wpuf\Integrations\Events_Calendar\Utils\TEC_Helper;

/**
 * Organizer Handler for Events Calendar
 *
 * Handles all organizer creation and management operations
 *
 * @since WPUF_SINCE
 */
class Organizer_Handler {

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
     * Handle organizer data from form data
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @return array|null
     */
    public function handle_organizer_data_from_form_data( $form_data ) {
        // Check if existing organizer is selected
        if ( ! empty( $form_data['_EventOrganizerID'] ) && is_numeric( $form_data['_EventOrganizerID'] ) ) {
            return [ 'OrganizerID' => intval( $form_data['_EventOrganizerID'] ) ];
        }

        // Check if we need to create a new organizer
        if ( ! empty( $form_data['organizer_name'] ) ) {
            return $this->create_organizer_from_form_data( $form_data );
        }

        return null;
    }

    /**
     * Create a new organizer from form data
     *
     * @since WPUF_SINCE
     *
     * @param array $form_data
     * @return array|null
     */
    private function create_organizer_from_form_data( $form_data ) {
        $organizer_data = [
            'Organizer' => sanitize_text_field( $form_data['organizer_name'] ),
        ];

        // Add organizer phone if provided
        if ( ! empty( $form_data['organizer_phone'] ) ) {
            $organizer_data['Phone'] = sanitize_text_field( $form_data['organizer_phone'] );
        }

        // Add organizer email if provided
        if ( ! empty( $form_data['organizer_email'] ) ) {
            $organizer_data['Email'] = sanitize_email( $form_data['organizer_email'] );
        }

        // Add organizer website if provided
        if ( ! empty( $form_data['organizer_website'] ) ) {
            $organizer_data['Website'] = esc_url_raw( $form_data['organizer_website'] );
        }

        // Sanitize organizer data
        $organizer_data = TEC_Helper::sanitize_organizer_data( $organizer_data );

        // Create organizer using compatibility manager
        $organizer_id = $this->compatibility_manager->create_organizer( $organizer_data );

        if ( is_wp_error( $organizer_id ) ) {
            $this->logger->error( 'Organizer creation failed: ' . $organizer_id->get_error_message() );
            return null;
        }

        $this->logger->info( 'Organizer created successfully with ID: ' . $organizer_id );
        return [ 'OrganizerID' => $organizer_id ];
    }

    /**
     * Handle organizer data from form submission
     *
     * @since WPUF_SINCE
     *
     * @return array|null
     */
    public function handle_organizer_data() {
        // Check if existing organizer is selected
        if ( ! empty( $_POST['_EventOrganizerID'] ) && is_numeric( $_POST['_EventOrganizerID'] ) ) {
            return [ 'OrganizerID' => intval( $_POST['_EventOrganizerID'] ) ];
        }

        // Check if we need to create a new organizer
        if ( ! empty( $_POST['organizer_name'] ) ) {
            return $this->create_organizer();
        }

        return null;
    }

    /**
     * Create a new organizer
     *
     * @since WPUF_SINCE
     *
     * @return array|null
     */
    private function create_organizer() {
        $organizer_data = [
            'Organizer' => sanitize_text_field( wp_unslash( $_POST['organizer_name'] ) ),
        ];

        // Add organizer phone if provided
        if ( ! empty( $_POST['organizer_phone'] ) ) {
            $organizer_data['Phone'] = sanitize_text_field( wp_unslash( $_POST['organizer_phone'] ) );
        }

        // Add organizer email if provided
        if ( ! empty( $_POST['organizer_email'] ) ) {
            $organizer_data['Email'] = sanitize_email( wp_unslash( $_POST['organizer_email'] ) );
        }

        // Add organizer website if provided
        if ( ! empty( $_POST['organizer_website'] ) ) {
            $organizer_data['Website'] = esc_url_raw( wp_unslash( $_POST['organizer_website'] ) );
        }

        // Sanitize organizer data
        $organizer_data = TEC_Helper::sanitize_organizer_data( $organizer_data );

        // Create organizer using compatibility manager
        $organizer_id = $this->compatibility_manager->create_organizer( $organizer_data );

        if ( is_wp_error( $organizer_id ) ) {
            $this->logger->error( 'Organizer creation failed: ' . $organizer_id->get_error_message() );
            return null;
        }

        $this->logger->info( 'Organizer created successfully with ID: ' . $organizer_id );
        return [ 'OrganizerID' => $organizer_id ];
    }

    /**
     * Validate organizer data
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return bool
     */
    public function validate_organizer_data( $organizer_data ) {
        // If organizer ID is provided, check if it exists
        if ( ! empty( $organizer_data['OrganizerID'] ) ) {
            $organizer = get_post( $organizer_data['OrganizerID'] );
            if ( ! $organizer || $organizer->post_type !== 'tribe_organizer' ) {
                $this->logger->error( 'Invalid organizer ID: ' . $organizer_data['OrganizerID'] );
                return false;
            }
            return true;
        }

        // If creating new organizer, validate required fields
        if ( ! empty( $organizer_data['Organizer'] ) ) {
            if ( empty( $organizer_data['Organizer'] ) ) {
                $this->logger->error( 'Organizer name is required' );
                return false;
            }

            // Validate email if provided
            if ( ! empty( $organizer_data['Email'] ) && ! is_email( $organizer_data['Email'] ) ) {
                $this->logger->error( 'Invalid organizer email: ' . $organizer_data['Email'] );
                return false;
            }

            return true;
        }

        $this->logger->error( 'No organizer data provided' );
        return false;
    }

    /**
     * Get organizer data
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return array|false
     */
    public function get_organizer_data( $organizer_id ) {
        if ( ! $this->compatibility_manager ) {
            return false;
        }

        return $this->compatibility_manager->get_current_compatibility_handler()->get_organizer_data( $organizer_id );
    }

    /**
     * Get all organizers
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_all_organizers() {
        if ( ! $this->compatibility_manager ) {
            return [];
        }

        return $this->compatibility_manager->get_current_compatibility_handler()->get_all_organizers();
    }

    /**
     * Check if organizer exists
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return bool
     */
    public function organizer_exists( $organizer_id ) {
        $organizer = get_post( $organizer_id );
        return $organizer && $organizer->post_type === 'tribe_organizer';
    }

    /**
     * Get organizer name
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return string
     */
    public function get_organizer_name( $organizer_id ) {
        $organizer = get_post( $organizer_id );
        if ( $organizer && $organizer->post_type === 'tribe_organizer' ) {
            return $organizer->post_title;
        }
        return '';
    }

    /**
     * Get organizer contact info
     *
     * @since WPUF_SINCE
     *
     * @param int $organizer_id
     * @return array
     */
    public function get_organizer_contact_info( $organizer_id ) {
        $contact = [];

        $contact['email'] = get_post_meta( $organizer_id, '_OrganizerEmail', true );
        $contact['phone'] = get_post_meta( $organizer_id, '_OrganizerPhone', true );
        $contact['website'] = get_post_meta( $organizer_id, '_OrganizerWebsite', true );

        return array_filter( $contact );
    }
}
