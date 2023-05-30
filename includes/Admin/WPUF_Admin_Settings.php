<?php

namespace Wp\User\Frontend\Admin;

use Wp\User\Frontend\Lib\WeDevs_Settings_API;
use WP_Query;

/**
 * WPUF settings
 */
class WPUF_Admin_Settings {

    /**
     * Settings API
     *
     * @var \WeDevs_Settings_API
     */
    private $settings_api;

    /**
     * Static instance of this class
     *
     * @var \self
     */
    private static $_instance;

    /**
     * Public instance of this class
     *
     * @var \self
     */
    public $subscribers_list_table_obj;

    /**
     * The menu page hooks
     *
     * Used for checking if any page is under WPUF menu
     *
     * @var array
     */
    private $menu_pages = [];

    public function __construct() {
        require_once WPUF_ROOT . '/admin/settings-options.php';

        $this->settings_api = new WeDevs_Settings_API();
        add_action( 'admin_init', [ $this, 'admin_init' ] );
        // add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    public function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Fire when post form submenu registered
     */
    public function post_form_menu_action() {
        // do_action('wpuf_load_post_forms');
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

    public function transactions_page() {
        require_once dirname( __DIR__ ) . '/admin/transactions.php';
    }

    /**
     * Callback method for Post Forms submenu
     *
     * @since 2.5
     *
     * @return void
     */
    public function wpuf_post_forms_page() {
        $action           = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : NULL;
        $add_new_page_url = admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' );
        switch ( $action ) {
            case 'edit':
                require_once WPUF_ROOT . '/views/post-form.php';
                break;
            case 'add-new':
                require_once WPUF_ROOT . '/views/post-form.php';
                break;
            default:
                require_once WPUF_ROOT . '/admin/post-forms-list-table-view.php';
                break;
        }
    }

    public function subscribers_page( $post_ID ) {
        include dirname( __DIR__ ) . '/admin/subscribers.php';
    }

    public function premium_page() {
        require_once dirname( __DIR__ ) . '/admin/premium.php';
    }

    public function tools_page() {
        $this->enqueue_tools_scripts();
        include dirname( __DIR__ ) . '/admin/tools.php';
    }

    public function support_page() {
        require_once dirname( __DIR__ ) . '/admin/html/support.php';
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
     * Highlight the proper top level menu
     *
     * @see http://wordpress.org/support/topic/moving-taxonomy-ui-to-another-main-menu?replies=5#post-2432769
     *
     * @global obj   $current_screen
     *
     * @param string $parent_file
     *
     * @return string
     */
    public function fix_parent_menu( $parent_file ) {
        $current_screen = get_current_screen();
        $post_types = [ 'wpuf_forms', 'wpuf_profile', 'wpuf_subscription', 'wpuf_coupon' ];
        if ( in_array( $current_screen->post_type, $post_types, true ) ) {
            $parent_file = 'wp-user-frontend';
        }
        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_the-slug' ) {
            $parent_file = 'wp-user-frontend';
        }

        return $parent_file;
    }

    /**
     * Fix the submenu class in admin menu
     *
     * @since 2.6.0
     *
     * @param string $submenu_file
     *
     * @return string
     */
    public function fix_submenu_file( $submenu_file ) {
        $current_screen = get_current_screen();
        if ( 'wpuf_subscription' === $current_screen->post_type && $current_screen->base === 'admin_page_wpuf_subscribers' ) {
            $submenu_file = 'edit.php?post_type=wpuf_subscription';
        }

        return $submenu_file;
    }

    /**
     * Screen options.
     *
     * @return void
     */
    public function transactions_screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => __( 'Number of items per page:', 'wp-user-frontend' ),
            'default' => 20,
            'option'  => 'transactions_per_page',
        ];
        add_screen_option( $option, $args );
        if ( ! class_exists( 'Wp\User\Frontend\Admin\WPUF_Transactions_List_Table' ) ) {
            require_once WPUF_ROOT . '/class/transactions-list-table.php';
        }
        $this->transactions_list_table_obj = new WPUF_Transactions_List_Table();
    }

    /**
     * Enqueue styles
     *
     * @return void
     */
    public function enqueue_styles() {
        if ( ! $this->is_admin_menu_page( get_current_screen() ) && get_current_screen()->parent_base === 'edit' ) {
            return;
        }
        // wp_enqueue_style( 'wpuf-admin', WPUF_ASSET_URI . '/css/admin.css', false, WPUF_VERSION );
        // wp_enqueue_script( 'wpuf-admin-script', WPUF_ASSET_URI . '/js/wpuf-admin.js', [ 'jquery' ], WPUF_VERSION, false );
        wp_localize_script( 'wpuf-admin-script', 'wpuf_admin_script', [
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'wpuf_nonce' ),
            'cleared_schedule_lock' => __( 'Post lock has been cleared',
                                           'wp-user-frontend' ),
        ] );
    }

    /**
     * Enqueue Tools page scripts
     *
     * @since 3.2.0
     *
     * @return void
     * @todo  Move this method to WPUF_Admin_Tools class
     *
     */
    private function enqueue_tools_scripts() {
        $prefix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        // wp_enqueue_script( 'wpuf-vue', WPUF_ASSET_URI . '/vendor/vue/vue' . $prefix . '.js', [], WPUF_VERSION, true );
        wp_enqueue_media();
        // wp_enqueue_script( 'wpuf-admin-tools', WPUF_ASSET_URI . '/js/wpuf-admin-tools.js', [ 'jquery', 'wpuf-vue' ], WPUF_VERSION, true );
        wp_localize_script( 'wpuf-admin-tools', 'wpuf_admin_tools', [
            'url'   => [
                'ajax' => admin_url( 'admin-ajax.php' ),
            ],
            'nonce' => wp_create_nonce( 'wpuf_admin_tools' ),
            'i18n'  => [
                'wpuf_import_forms'      => __( 'WPUF Import Forms',
                                                'wp-user-frontend' ),
                'add_json_file'          => __( 'Add JSON file',
                                                'wp-user-frontend' ),
                'could_not_import_forms' => __( 'Could not import forms.',
                                                'wp-user-frontend' ),
            ],
        ] );
    }

    /**
     * Get the settings_api property
     *
     * @since WPUF_SINCE
     *
     * @return \WeDevs_Settings_API|WeDevs_Settings_API
     */
    public function get_settings_api() {
        return $this->settings_api;
    }


}
