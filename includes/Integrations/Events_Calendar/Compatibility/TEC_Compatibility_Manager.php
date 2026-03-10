<?php

namespace WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility;

/**
 * TEC Compatibility Manager
 *
 * Handles version detection and routes to appropriate compatibility handler
 *
 * @since 4.1.9
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
     * @var TEC_V5_Compatibility|TEC_V6_Compatibility|null
     */
    private $compatibility_handler;

    /**
     * Constructor
     */
    public function __construct() {
        $this->tec_version = $this->detect_tec_version();
        $this->compatibility_handler = $this->get_compatibility_handler();
    }

    /**
     * Get TEC version
     *
     * @since 4.1.9
     *
     * @return string
     */
    private function detect_tec_version() {
        if ( class_exists( 'Tribe__Events__Main' ) ) {
            return \Tribe__Events__Main::VERSION;
        }
        return '0.0.0';
    }

    /**
     * Get current TEC version
     *
     * @since 4.1.9
     *
     * @return string
     */
    public function get_tec_version() {
        return $this->tec_version;
    }

    /**
     * Get appropriate compatibility handler based on TEC version
     *
     * @since 4.1.9
     *
     * @return TEC_V5_Compatibility|TEC_V6_Compatibility
     */
    public function get_compatibility_handler() {
        if ( version_compare( $this->tec_version, '6.0', '<' ) ) {
            if ( class_exists( '\WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_V5_Compatibility' ) ) {
                return new TEC_V5_Compatibility();
            }
        }

        if ( class_exists( '\WeDevs\Wpuf\Integrations\Events_Calendar\Compatibility\TEC_V6_Compatibility' ) ) {
            return new TEC_V6_Compatibility();
        }

        return null; // Or throw an exception, or return a default handler
    }
}
