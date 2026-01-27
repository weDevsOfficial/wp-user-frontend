<?php

namespace WeDevs\Wpuf\Integrations\Elementor;

/**
 * Elementor Integration Class
 *
 * @since 4.0.0
 */
class Elementor {

    public function __construct() {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

        // Ensure editor scripts are enqueued for TinyMCE in Elementor preview
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Enqueue Elementor Specific Styles for both frontend and editor
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_styles() {
        wp_dequeue_style('wpuf-frontend-forms');
        wp_dequeue_style('wpuf-layout1');
        wp_dequeue_style('wpuf-layout2');
        wp_dequeue_style('wpuf-layout3');
        wp_dequeue_style('wpuf-layout4');
        wp_dequeue_style('wpuf-layout5');
        wp_enqueue_style( 'wpuf-elementor-frontend-forms' );
    }

    /**
     * Enqueue Elementor Specific Scripts for both frontend and editor
     *
     * Ensures TinyMCE editor scripts are loaded when WPUF forms with rich text
     * fields are rendered in Elementor preview.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_scripts() {
        // Enqueue all required WPUF form assets
        $this->enqueue_wpuf_form_assets();

        // Ensure editor scripts are loaded for TinyMCE in Elementor preview
        if ( function_exists( 'wp_enqueue_editor' ) ) {
            wp_enqueue_editor();
            
            // Also explicitly enqueue TinyMCE scripts if available
            if ( function_exists( 'wp_enqueue_script' ) ) {
                // Check if these scripts exist and enqueue them
                global $wp_scripts;
                if ( isset( $wp_scripts->registered['tinymce'] ) ) {
                    wp_enqueue_script( 'tinymce' );
                }
                if ( isset( $wp_scripts->registered['wp-tinymce'] ) ) {
                    wp_enqueue_script( 'wp-tinymce' );
                }
            }
        }
    }

    /**
     * Enqueue all required WPUF form assets for Elementor
     *
     * Ensures all necessary styles and scripts are loaded for WPUF forms
     * to render properly in Elementor preview and frontend.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function enqueue_wpuf_form_assets() {
        // Core styles (already handled by enqueue_styles, but ensure they're available)
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_style( 'wpuf-jquery-ui' );

        // Core scripts required for all WPUF forms
        wp_enqueue_script( 'suggest' );
        wp_enqueue_script( 'wpuf-billing-address' );
        wp_enqueue_script( 'wpuf-upload' );
        wp_enqueue_script( 'wpuf-frontend-form' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-subscriptions' );

        // Localize wpuf-upload script
        wp_localize_script(
            'wpuf-upload',
            'wpuf_upload',
            [
                'confirmMsg' => __( 'Are you sure?', 'wp-user-frontend' ),
                'delete_it'  => __( 'Yes, delete it', 'wp-user-frontend' ),
                'cancel_it'  => __( 'No, cancel it', 'wp-user-frontend' ),
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
                'plupload'   => [
                    'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                    'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                    'filters'          => [
                        [
                            'title'      => __( 'Allowed Files', 'wp-user-frontend' ),
                            'extensions' => '*',
                        ],
                    ],
                    'multipart'        => true,
                    'urlstream_upload' => true,
                    'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                    'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wp-user-frontend' ),
                    'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend' ),
                ],
            ]
        );

        // Localize wpuf-frontend-form script
        wp_localize_script(
            'wpuf-frontend-form',
            'wpuf_frontend',
            apply_filters(
                'wpuf_frontend_object',
                [
                    'asset_url'                    => WPUF_ASSET_URI,
                    'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
                    'error_message'                => __( 'Please fix the errors to proceed', 'wp-user-frontend' ),
                    'nonce'                        => wp_create_nonce( 'wpuf_nonce' ),
                    'word_limit'                   => __( 'Word limit reached', 'wp-user-frontend' ),
                    'cancelSubMsg'                 => __( 'Are you sure you want to cancel your current subscription ?', 'wp-user-frontend' ),
                    'delete_it'                    => __( 'Yes', 'wp-user-frontend' ),
                    'cancel_it'                    => __( 'No', 'wp-user-frontend' ),
                    'word_max_title'               => __( 'Maximum word limit reached. Please shorten your texts.', 'wp-user-frontend' ),
                    'word_max_details'             => __( 'This field supports a maximum of %number% words, and the limit is reached. Remove a few words to reach the acceptable limit of the field.', 'wp-user-frontend' ),
                    'word_min_title'               => __( 'Minimum word required.', 'wp-user-frontend' ),
                    'word_min_details'             => __( 'This field requires minimum %number% words. Please add some more text.', 'wp-user-frontend' ),
                    'char_max_title'               => __( 'Maximum character limit reached. Please shorten your texts.', 'wp-user-frontend' ),
                    'char_max_details'             => __( 'This field supports a maximum of %number% characters, and the limit is reached. Remove a few characters to reach the acceptable limit of the field.', 'wp-user-frontend' ),
                    'char_min_title'               => __( 'Minimum character required.', 'wp-user-frontend' ),
                    'char_min_details'             => __( 'This field requires minimum %number% characters. Please add some more character.', 'wp-user-frontend' ),
                    'protected_shortcodes'         => wpuf_get_protected_shortcodes(),
                    'protected_shortcodes_message' => __( 'Using %shortcode% is restricted', 'wp-user-frontend' ),
                    'password_warning_weak'        => __( 'Your password should be at least weak in strength', 'wp-user-frontend' ),
                    'password_warning_medium'      => __( 'Your password needs to be medium strength for better protection', 'wp-user-frontend' ),
                    'password_warning_strong'      => __( 'Create a strong password for maximum security', 'wp-user-frontend' ),
                ]
            )
        );

        wp_localize_script(
            'wpuf-frontend-form',
            'error_str_obj',
            [
                'required'   => __( 'is required', 'wp-user-frontend' ),
                'mismatch'   => __( 'does not match', 'wp-user-frontend' ),
                'validation' => __( 'is not valid', 'wp-user-frontend' ),
            ]
        );

        // Localize subscription script
        wp_localize_script(
            'wpuf-subscriptions',
            'wpuf_subscription',
            apply_filters(
                'wpuf_subscription_js_data',
                [
                    'pack_notice' => __( 'Please Cancel Your Currently Active Pack first!', 'wp-user-frontend' ),
                ]
            )
        );

        // Localize billing address script
        wp_localize_script(
            'wpuf-billing-address',
            'ajax_object',
            [
                'ajaxurl'     => admin_url( 'admin-ajax.php' ),
                'fill_notice' => __( 'Some Required Fields are not filled!', 'wp-user-frontend' ),
            ]
        );

        // Conditionally enqueue Google Maps if API key is configured
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );
        if ( ! empty( $api_key ) ) {
            $scheme = is_ssl() ? 'https' : 'http';
            wp_enqueue_script( 'wpuf-google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key, [], null, true );
        }
    }

    /**
     * Register Elementor Widget Category
     *
     * @param \Elementor\Elements_Manager $elements_manager
     *
     * @return void
     */
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'user-frontend',
            [
                'title' => __( 'User Frontend', 'wp-user-frontend' ),
                'icon'  => 'eicon-form-horizontal',
            ]
        );
    }

    /**
     * Register Elementor Widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     *
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        require_once __DIR__ . '/Widget.php';

        $widgets_manager->register( new Widget() );
    }
}
