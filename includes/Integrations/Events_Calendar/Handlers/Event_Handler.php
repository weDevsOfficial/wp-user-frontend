<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Handlers;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;

/**
 * Event Handler for Events Calendar
 *
 * Handles all event creation and editing operations
 *
 * @since 4.1.9
 */
class Event_Handler {
    /**
     * @var TEC_Compatibility_Manager
     */
    protected $compatibility_manager;

    /**
     * Constructor
     *
     * @param TEC_Compatibility_Manager $compatibility_manager
     */
    public function __construct( $compatibility_manager ) {
        // Before Pro's organizer save (priority 1000), so Pro still has the last word.
        add_action( 'wpuf_add_post_after_insert', [ $this, 'save_multiple_organizers' ], 999 );
        add_action( 'wpuf_edit_post_after_update', [ $this, 'save_multiple_organizers' ], 999 );

        $this->compatibility_manager = $compatibility_manager;
    }

    /**
     * Save the organizers of a Multi Select _EventOrganizerID field, one meta row each
     *
     * The form saves the picks as one "12 | 15" value, which The Events Calendar
     * can not read, so the event ended up with no organizer. TEC keeps one
     * _EventOrganizerID row per organizer; write it that way.
     *
     * @since WPUF_SINCE
     *
     * @param int $post_id Saved post ID.
     *
     * @return void
     */
    public function save_multiple_organizers( $post_id ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- runs inside the WPUF submit handler, after its nonce check.
        if ( 'tribe_events' !== get_post_type( $post_id ) || ! isset( $_POST['_EventOrganizerID'] ) || ! is_array( $_POST['_EventOrganizerID'] ) ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- see above.
        $organizer_ids = array_unique( array_map( 'absint', wp_unslash( $_POST['_EventOrganizerID'] ) ) );

        delete_post_meta( $post_id, '_EventOrganizerID' );

        foreach ( $organizer_ids as $organizer_id ) {
            if ( 'tribe_organizer' === get_post_type( $organizer_id ) && 'publish' === get_post_status( $organizer_id ) ) {
                add_post_meta( $post_id, '_EventOrganizerID', $organizer_id );
            }
        }
    }

    /**
     * Handle event submission with automatic compatibility routing
     *
     * This is a thin delegation layer that routes to the appropriate TEC version handler.
     * All the actual logic is handled by the compatibility classes.
     *
     * @since 4.1.9
     *
     * @param array $postarr       WordPress post array (title, content, status, etc.)
     * @param array $meta_vars     Form meta fields (event dates, cost, URL, etc.)
     * @param int   $form_id       WPUF form ID
     * @param array $form_settings WPUF form settings
     * @return int|false Event post ID on success, false on failure
     */
    public function handle_event_submission( $postarr, $meta_vars, $form_id, $form_settings ) {
        // Get the appropriate compatibility handler (V5 or V6)
        $compatibility_handler = $this->compatibility_manager->get_compatibility_handler();

        if ( ! $compatibility_handler || ! $compatibility_handler->is_active() ) {
            // Fallback to standard WordPress post creation if TEC is not available
            return wp_insert_post( $postarr );
        }

        // Delegate to the appropriate compatibility handler
        // All the complex logic is handled by TEC_V5_Compatibility or TEC_V6_Compatibility
        return $compatibility_handler->save_event( $postarr, $meta_vars, $form_id, $form_settings );
    }

    /**
     * Get the compatibility manager instance
     *
     * @since 4.1.9
     *
     * @return TEC_Compatibility_Manager
     */
    public function get_compatibility_manager() {
        return $this->compatibility_manager;
    }
}
