<?php

namespace WeDevs\Wpuf\Integrations\Elementor;

/**
 * Elementor Integration Class
 *
 * @since 4.0.0
 */
class Elementor {

    public function __construct() {
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
