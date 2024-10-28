<?php

namespace WeDevs\Wpuf;

/**
 * The integration class to handle all integrations with our plugin
 *
 * @since 4.0.12
 */
class Integrations {
    /**
     * Holds various class instances
     *
     * @since 4.0.12
     *
     * @var array
     */
    public $container = [];

    private $integrations = [
        'WeDevs_Dokan' => 'WPUF_Dokan_Integration',
        'WC_Vendors'   => 'WPUF_WC_Vendors_Integration',
        'WCMp'         => 'WPUF_WCMp_Integration',
        'ACF'          => 'WPUF_ACF_Compatibility',
    ];

    public function __construct() {
        foreach ( $this->integrations as $external_class => $integration_class ) {
            if ( class_exists( $external_class ) ) {
                $full_class_name = __NAMESPACE__ . '\\Integrations\\' . $integration_class;
                try {
                    $this->container[ strtolower( $external_class ) ] = new $full_class_name();
                } catch ( \Exception $e ) {
                    \WP_User_Frontend::log( 'integration', print_r( $external_class . ' integration failed', true ) );
                }
            }
        }
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 4.0.12
     *
     * @param string $prop
     *
     * @return null|object Class Instance
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return null;
    }
}
