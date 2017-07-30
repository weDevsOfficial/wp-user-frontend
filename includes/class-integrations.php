<?php

/**
 * The Integration Loader
 */
class WPUF_Integrations {

    /**
     * The integration instances
     *
     * @var array
     */
    public $integrations = array();

    /**
     * Initialize the integrations
     */
    public function __construct() {

        $integrations = apply_filters( 'wpuf_integrations', array() );

        // Load integration classes
        foreach ( $integrations as $integration ) {

            $integration_instance = new $integration();

            $this->integrations[ $integration_instance->id ] = $integration_instance;
        }
    }

    /**
     * Return loaded integrations.
     *
     * @return array
     */
    public function get_integrations() {
        return $this->integrations;
    }
}
