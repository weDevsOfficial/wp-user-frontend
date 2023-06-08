<?php

namespace WeDevs\Wpuf\Frontend;

class Shortcode {
    public function __construct() {
        wpuf()->add_to_container( 'shortcode_frontend_dashboard', new Shortcodes\Frontend_Dashboard() );

        add_shortcode( 'wpuf_dashboard', [ wpuf()->shortcode_frontend_dashboard, 'shortcode' ] );
        add_shortcode( 'wpuf-registration', [ wpuf()->registration, 'registration_form' ] );
        add_shortcode( 'wpuf_form', [ wpuf()->frontend_form, 'add_post_shortcode' ] );
        add_shortcode( 'wpuf_edit', [ wpuf()->frontend_form, 'edit_post_shortcode' ] );
        add_shortcode( 'wpuf_editprofile', [ wpuf()->frontend_account, 'shortcode' ] );
    }
}
