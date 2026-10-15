<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs_Settings_API;

/**
 * WPUF settings
 */
class Admin_Settings {

    /**
     * Settings API
     *
     * @var WeDevs_Settings_API
     */
    private $settings_api;

    public function __construct() {
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-options.php' );
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-screen.php' );
        wpuf_require_once( WPUF_ROOT . '/Lib/WeDevs_Settings_API.php' );

        $this->settings_api = wpuf()->platform()->get( WeDevs_Settings_API::class );
        add_action( 'admin_init', [ $this, 'admin_init' ] );
    }

    public function admin_init() {
        $sections = $this->get_settings_sections();

        //set the settings
        $this->settings_api->set_sections( $sections );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();

        // The legacy screen saves through the settings store (task 2.4d).
        Stores::settings()->hook_legacy_screen( $this->settings_api, $sections );
    }

    /**
     * WPUF Settings sections
     *
     * @since 1.0
     *
     * @return array
     */
    public function get_settings_sections() {
        return wpuf_settings_sections();
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields() {
        return wpuf_settings_fields();
    }

    /**
     * Get the settings_api property
     *
     * @since 4.0.0
     *
     * @return WeDevs_Settings_API
     */
    public function get_settings_api() {
        return $this->settings_api;
    }
}
