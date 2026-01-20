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
