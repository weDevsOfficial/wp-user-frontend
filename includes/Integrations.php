<?php

namespace WeDevs\Wpuf;

/**
 * The integration class to handle all integrations with our plugin
 *
 * @since WPUF_SINCE
 */
class Integrations {
    /**
     * Holds various class instances
     *
     * @since WPUF_SINCE
     *
     * @var array
     */
    public $container = [];

    public function __construct() {
        if ( class_exists( 'WeDevs_Dokan' ) ) {
            $this->container['dokan'] = new Integrations\WPUF_Dokan_Integration();
        }

        if ( class_exists( 'WC_Vendors' ) ) {
            $this->container['wc_vendors'] = new Integrations\WPUF_WC_Vendors_Integration();
        }

        if ( class_exists( 'WCMp' ) ) {
            $this->container['wcmp'] = new Integrations\WPUF_WCMp_Integration();
        }

        if ( class_exists( 'ACF' ) ) {
            $this->container['acf'] = new Integrations\WPUF_ACF_Compatibility();
        }
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since WPUF_SINCE
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
