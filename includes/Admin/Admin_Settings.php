<?php

namespace WeDevs\Wpuf\Admin;

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

    /**
     * The menu page hooks
     *
     * Used for checking if any page is under WPUF menu
     *
     * @var array
     */
    private $menu_pages = [];

    public function __construct() {
        wpuf_require_once( WPUF_INCLUDES . '/functions/settings-options.php' );
        wpuf_require_once( WPUF_ROOT . '/Lib/WeDevs_Settings_API.php' );

        $this->settings_api = new WeDevs_Settings_API();
        add_action( 'admin_init', [ $this, 'admin_init' ] );
    }

    public function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
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

    public function plugin_page() {
        ?>
        <div class="wrap">

            <h2 style="margin-bottom: 15px;"><?php esc_html_e( 'Settings', 'wp-user-frontend' ); ?></h2>
            <div class="wpuf-settings-wrap">
                <?php
                settings_errors();
                $this->settings_api->show_navigation();
                $this->settings_api->show_forms();
                ?>
            </div>
            <script>
                ( function () {
                    document.addEventListener( 'DOMContentLoaded', function () {
                        var tabs = document.querySelector( '.wpuf-settings-wrap' ).querySelectorAll( 'h2 a' );
                        var content = document.querySelectorAll( '.wpuf-settings-wrap .metabox-holder th' );
                        var close = document.querySelector( '#wpuf-search-section span' );

                        var search_input = document.querySelector( '#wpuf-settings-search' );

                        search_input.addEventListener( 'keyup', function ( e ) {
                            var search_value = e.target.value.toLowerCase();
                            var value_tab = [];

                            if (search_value.length) {
                                close.style.display = 'flex'
                                content.forEach( function ( row, index ) {

                                    var content_id = row.closest( 'div' ).getAttribute( 'id' );
                                    var tab_id = content_id + '-tab';
                                    var found_value = row.innerText.toLowerCase().includes( search_value );

                                    if (found_value) {
                                        row.closest( 'tr' ).style.display = 'table-row';
                                    } else {
                                        row.closest( 'tr' ).style.display = 'none';
                                    }

                                    if ('wpuf_mails' === content_id) {
                                        row.closest( 'tbody' ).querySelectorAll( 'tr' ).forEach( function ( tr ) {
                                            tr.style.display = '';
                                        } );
                                    }

                                    if (found_value === true && !value_tab.includes( tab_id )) {
                                        value_tab.push( tab_id );
                                    }
                                } )

                                if (value_tab.length) {
                                    document.getElementById( value_tab[0] ).click();
                                }

                                tabs.forEach( function ( tab ) {
                                    var tab_id = tab.getAttribute( 'id' );
                                    if (!value_tab.includes( tab_id )) {
                                        document.getElementById( tab_id ).style.display = 'none';
                                    } else {
                                        document.getElementById( tab_id ).style.display = 'block';
                                    }
                                } )

                            } else {
                                wpuf_search_reset();
                            }
                        } )

                        close.addEventListener( 'click', function ( event ) {
                            wpuf_search_reset();
                            search_input.value = '';
                            close.style.display = 'none';
                        } )

                        function wpuf_search_reset() {
                            content.forEach( function ( row, index ) {
                                var content_id = row.closest( 'div' ).getAttribute( 'id' );
                                var tab_id = content_id + '-tab';
                                document.getElementById( content_id ).style.display = '';
                                document.getElementById( tab_id ).style.display = '';
                                document.getElementById( 'wpuf_general-tab' ).click();
                            } )
                            document.querySelector( '.wpuf-settings-wrap .metabox-holder' ).querySelectorAll( 'tr' ).forEach( function ( row ) {
                                row.style.display = '';
                            } );

                        }
                    } );
                } )();
            </script>
        </div>
        <?php
    }

    /**
     * Check if the current page is a settings/menu page
     *
     * @param string $screen_id
     *
     * @return bool
     */
    public function is_admin_menu_page( $screen ) {
        if ( $screen && in_array( $screen->id, $this->menu_pages, true ) ) {
            return true;
        }

        return false;
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
