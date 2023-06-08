<?php

namespace WeDevs\Wpuf\Frontend;

class Shortcode {
    public function __construct() {
        add_shortcode( 'wpuf_dashboard', [ wpuf()->shortcode_frontend_dashboard, 'shortcode' ] );
        add_shortcode( 'wpuf-registration', [ wpuf()->registration, 'registration_form' ] );
        add_shortcode( 'wpuf_form', [ wpuf()->frontend_form, 'add_post_shortcode' ] );
        add_shortcode( 'wpuf_edit', [ wpuf()->frontend_form, 'edit_post_shortcode' ] );
        add_shortcode( 'wpuf_editprofile', [ wpuf()->frontend_account, 'shortcode' ] );
        add_shortcode( 'wpuf-login', [ wpuf()->simple_login, 'login_form' ] );

        add_shortcode( 'wpuf-edit-users', 'wpuf_edit_users' );
        add_shortcode( 'wpuf-meta', 'wpuf_meta_shortcode' );
    }
}
