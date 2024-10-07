<?php

namespace WeDevs\Wpuf;

/**
 * The installer class
 *
 * @since 2.6.0
 */
class Integrations {
    /**
     * Holds various class instances
     *
     * @since 4.0.9
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
}
