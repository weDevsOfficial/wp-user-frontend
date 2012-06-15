<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
class WPUF_Settings {

    private $settings_api;

    function __construct() {
        $this->settings_api = WeDevs_Settings_API::getInstance();

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Register the admin menu
     *
     * @since 1.0
     */
    function admin_menu() {
        add_menu_page( __( 'WP User Frontend', 'wpuf' ), __( 'WP User Frontend', 'wpuf' ), 'activate_plugins', 'wpuf-admin-opt', array($this, 'plugin_page'), null );
        add_submenu_page( 'wpuf-admin-opt', __( 'Custom Fields', 'wpuf' ), __( 'Custom Fields', 'wpuf' ), 'activate_plugins', 'wpuf_custom_fields', 'wpuf_custom_fields' );
        //add_submenu_page( 'wpuf-admin-opt', 'Custom Taxonomies', 'Custom Taxonomies', 'activate_plugins', 'wpuf_custom_tax', 'wpuf_taxonomy_fields' );
        add_submenu_page( 'wpuf-admin-opt', __( 'Subscription', 'wpuf' ), __( 'Subscription', 'wpuf' ), 'activate_plugins', 'wpuf_subscription', 'wpuf_subscription_admin' );
        add_submenu_page( 'wpuf-admin-opt', __( 'Transaction', 'wpuf' ), __( 'Transaction', 'wpuf' ), 'activate_plugins', 'wpuf_transaction', 'wpuf_transaction' );
    }

    /**
     * WPUF Settings sections
     *
     * @since 1.0
     * @return array
     */
    function get_settings_sections() {
        return wpuf_settings_sections();
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    function get_settings_fields() {
        return wpuf_settings_fields();
    }

    function plugin_page() {
        ?>
        <div class="wrap">

            <?php screen_icon( 'options-general' ); ?>
            <h2><?php _e( 'WP User Frontend: Settings', 'wpuf' ); ?></h2>

            <?php
            settings_errors();

            $this->settings_api->show_navigation();
            $this->settings_api->show_forms();
            ?>

        </div>
        <?php
    }
}

$wpuf_settings = new WPUF_Settings();