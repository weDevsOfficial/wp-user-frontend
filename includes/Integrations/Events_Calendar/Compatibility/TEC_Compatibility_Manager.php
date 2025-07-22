<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC Compatibility Manager
 *
 * Handles version detection and routes to appropriate compatibility handler
 *
 * @since WPUF_SINCE
 */
class TEC_Compatibility_Manager {

    /**
     * TEC version
     *
     * @var string
     */
    private $tec_version;

    /**
     * Compatibility handler instance
     *
     * @var TEC_V5_Compatibility|TEC_V6_Compatibility
     */
    private $compatibility_handler;

    /**
     * Constructor
     */
    public function __construct() {
        $this->tec_version = $this->get_tec_version();
        $this->compatibility_handler = $this->get_compatibility_handler();
    }

    /**
     * Get TEC version
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function get_tec_version() {
        if ( class_exists( 'Tribe__Events__Main' ) ) {
            return \Tribe__Events__Main::VERSION;
        }
        return '0.0.0';
    }

    /**
     * Get appropriate compatibility handler based on TEC version
     *
     * @since WPUF_SINCE
     *
     * @return TEC_V5_Compatibility|TEC_V6_Compatibility
     */
    private function get_compatibility_handler() {
        if ( version_compare( $this->tec_version, '6.0', '<' ) ) {
            return new TEC_V5_Compatibility();
        }

        return new TEC_V6_Compatibility();
    }

    /**
     * Save event using appropriate compatibility handler
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id
     * @param array $event_data
     * @return bool|\WP_Error
     */
    public function save_event( $post_id, $event_data ) {
        if ( ! $this->compatibility_handler ) {
            return false;
        }

        return $this->compatibility_handler->save_event( $post_id, $event_data );
    }



    /**
     * Create organizer using appropriate compatibility handler
     *
     * @since WPUF_SINCE
     *
     * @param array $organizer_data
     * @return int|\WP_Error
     */
    public function create_organizer( $organizer_data ) {
        if ( ! $this->compatibility_handler ) {
            return false;
        }

        return $this->compatibility_handler->create_organizer( $organizer_data );
    }

    /**
     * Get current TEC version
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function get_current_tec_version() {
        return $this->tec_version;
    }

    /**
     * Check if TEC is active
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_tec_active() {
        return class_exists( 'Tribe__Events__Main' );
    }

    /**
     * Check if using TEC v6 or higher
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function is_tec_v6() {
        return version_compare( $this->tec_version, '6.0', '>=' );
    }

    /**
     * Get current compatibility handler instance
     *
     * @since WPUF_SINCE
     *
     * @return TEC_V5_Compatibility|TEC_V6_Compatibility|null
     */
    public function get_current_compatibility_handler() {
        return $this->compatibility_handler;
    }


}
