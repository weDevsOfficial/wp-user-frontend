<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar;

use WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_Compatibility_Manager;
use WeDevs\Wpuf\Integrations\Events_Calendar\Handlers\Event_Handler;

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
    public $event_handler;

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

        /**
         * Opportunity to perform actions after Events Calendar integration initialization
         *
         * This action allows developers to perform additional setup after the TEC integration
         * has been initialized. Useful for custom handlers, compatibility layers, or
         * integration with other plugins.
         *
         * @since WPUF_SINCE
         *
         * @param Events_Calendar_Integration $this The integration instance
         * @param TEC_Compatibility_Manager $compatibility_manager The compatibility manager instance
         * @param Event_Handler $event_handler The event handler instance
         */
        do_action( 'wpuf_tec_integration_ready', $this, $this->compatibility_manager, $this->event_handler );
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
}
