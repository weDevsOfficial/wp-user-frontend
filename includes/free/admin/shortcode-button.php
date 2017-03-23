<?php

/**
 * wpuf tinyMce Shortcode Button class
 *
 * @since 2.5.2
 */
class WPUF_Shortcodes_Button {

    /**
     * Constructor for shortcode class
     */
    public function __construct() {

        add_filter( 'mce_external_plugins',  array( $this, 'enqueue_plugin_scripts' ) );
        add_filter( 'mce_buttons',  array( $this, 'register_buttons_editor' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'localize_shortcodes' ) , 90  );
    }

    /**
     * Generate shortcode array
     *
     * @since 2.4.12
     *
     */
    function localize_shortcodes() {

        $shortcodes = array(
            'wpuf-dashboard'=> array(
                'title'   => __( 'Dashboard', 'wpuf' ),
                'content' => '[wpuf_dashboard]'
            ),
            'wpuf-account'  => array(
                'title'   => __( 'Account', 'wpuf' ),
                'content' => '[wpuf_account]'
            ),
            'wpuf-edit'     => array(
                'title'   => __( 'Edit', 'wpuf' ),
                'content' => '[wpuf_edit]'
            ),
            'wpuf-login'    => array(
                'title'   => __( 'Login', 'wpuf' ),
                'content' => '[wpuf-login]'
            ),
            'wpuf-sub-pack' => array(
                'title'   => __( 'Subscription', 'wpuf' ),
                'content' => '[wpuf_sub_pack]'
            )
        );

        $assets_url = WPUF_ASSET_URI;

        wp_localize_script( 'wpuf-subscriptions', 'wpuf_shortcodes', apply_filters( 'wpuf_button_shortcodes', $shortcodes ) );
        wp_localize_script( 'wpuf-subscriptions', 'wpuf_assets_url', $assets_url );
    }

    /**
     * * Singleton object
     *
     * @staticvar boolean $instance
     *
     * @return \self
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new WPUF_Shortcodes_Button();
        }

        return $instance;
    }

    /**
     * Add button on Post Editor
     *
     * @since 2.4.12
     *
     * @param array $plugin_array
     *
     * @return array
     */
    function enqueue_plugin_scripts( $plugin_array ) {
        //enqueue TinyMCE plugin script with its ID.
        $plugin_array["wpuf_button"] =  WPUF_ASSET_URI . "/js/wpuf-tmc-button.js";

        return $plugin_array;
    }

    /**
     * Register tinyMce button
     *
     * @since 2.4.12
     *
     * @param array $buttons
     *
     * @return array
     */
    function register_buttons_editor( $buttons ) {
        //register buttons with their id.
        array_push( $buttons, "wpuf_button" );

        return $buttons;
    }

}

WPUF_Shortcodes_Button::init();
