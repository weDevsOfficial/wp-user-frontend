<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler;
use WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Venue_Handler;
use WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Organizer_Handler;

/**
 * Main Events Calendar Integration Class
 *
 * @since WPUF_SINCE
 */
class Events_Calendar_Integration {

    /**
     * Event handler instance
     *
     * @var Event_Handler
     */
    private $event_handler;

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
     * Compatibility manager instance
     *
     * @var TEC_Compatibility_Manager
     */
    private $compatibility_manager;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_handlers();
        $this->register_hooks();
    }

    /**
     * Initialize all handlers
     *
     * @since WPUF_SINCE
     */
    private function init_handlers() {
        // Only initialize if TEC is active
        if ( ! $this->is_tec_active() ) {
            return;
        }

        $this->compatibility_manager = new TEC_Compatibility_Manager();
        $this->event_handler = new Event_Handler( $this->compatibility_manager );
        $this->venue_handler = new Venue_Handler( $this->compatibility_manager );
        $this->organizer_handler = new Organizer_Handler( $this->compatibility_manager );
    }

    /**
     * Register WordPress hooks
     *
     * @since WPUF_SINCE
     */
    private function register_hooks() {
        // Only register hooks if TEC is active
        if ( ! $this->is_tec_active() ) {
            return;
        }

        // Hook into form submission BEFORE post creation
        add_action( 'wpuf_form_submit_before', [ $this->event_handler, 'prepare_event_data' ], 10, 3 );
        add_action( 'wpuf_form_edit_before', [ $this->event_handler, 'prepare_event_data' ], 10, 3 );

        // Dashboard compatibility
        add_action( 'wpuf_dashboard_shortcode_init', [ $this, 'remove_tribe_pre_get_posts' ] );

        // Meta field processing
        add_filter( 'wpuf_before_updating_post_meta_fields', [ $this, 'process_tec_meta_fields' ], 10, 4 );
    }

    /**
     * Check if The Events Calendar is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    private function is_tec_active() {
        return class_exists( 'Tribe__Events__Main' );
    }

    /**
     * Register the Events Calendar form template
     *
     * @since WPUF_SINCE
     *
     * @param array $templates
     * @return array
     */
    /*
    public function register_form_template( $templates ) {
        if ( $this->is_tec_active() ) {
            $templates['post_form_template_events_calendar'] = new Templates\Event_Form_Template();
        }

        return $templates;
    }
    */

    /**
     * Remove TEC's pre_get_posts filter for dashboard compatibility
     *
     * @since WPUF_SINCE
     */
    public function remove_tribe_pre_get_posts() {
        if ( class_exists( 'Tribe__Events__Query' ) ) {
            remove_action( 'pre_get_posts', [ \Tribe__Events__Query::class, 'pre_get_posts' ], 50 );
        }
    }

    /**
     * Process TEC meta fields before saving
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $meta_key_value
     * @param array $multi_repeated
     * @param array $files
     */
    public function process_tec_meta_fields( $post_id, $meta_key_value, $multi_repeated, $files ) {
        if ( ! $this->is_tec_active() ) {
            return;
        }

        $post_type = get_post_type( $post_id );
        if ( ! in_array( $post_type, [ 'tribe_events', 'tribe_venue', 'tribe_organizer' ], true ) ) {
            return;
        }

        // Process meta fields without date handling
        return $meta_key_value;
    }

    /**
     * Get event handler instance
     *
     * @since WPUF_SINCE
     *
     * @return Event_Handler|null
     */
    public function get_event_handler() {
        return $this->event_handler;
    }

    /**
     * Get venue handler instance
     *
     * @since WPUF_SINCE
     *
     * @return Venue_Handler|null
     */
    public function get_venue_handler() {
        return $this->venue_handler;
    }

    /**
     * Get organizer handler instance
     *
     * @since WPUF_SINCE
     *
     * @return Organizer_Handler|null
     */
    public function get_organizer_handler() {
        return $this->organizer_handler;
    }

    /**
     * Get compatibility manager instance
     *
     * @since WPUF_SINCE
     *
     * @return TEC_Compatibility_Manager|null
     */
    public function get_compatibility_manager() {
        return $this->compatibility_manager;
    }
}
