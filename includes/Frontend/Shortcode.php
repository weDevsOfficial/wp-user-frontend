<?php

namespace WeDevs\Wpuf\Frontend;

class Shortcode {
    public function __construct() {
        wpuf()->add_to_container( 'shortcode_frontend_dashboard', new Shortcodes\Frontend_Dashboard() );

        add_shortcode( 'wpuf_dashboard', [ wpuf()->shortcode_frontend_dashboard, 'shortcode' ] );
    }
}
