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
