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
        $this->compatibility_manager = $compatibility_manager;
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
