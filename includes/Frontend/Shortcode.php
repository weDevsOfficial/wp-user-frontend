<?php

namespace WeDevs\Wpuf\Frontend;

class Shortcode {
    public function __construct() {
        add_action( 'init', [ $this, 'init_shortcode' ] );
    }

    /**
     * initialize the WPUF shortcodes
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function init_shortcode() {
        add_shortcode( 'wpuf_dashboard', [ wpuf()->frontend->frontend_dashboard, 'shortcode' ] );
        add_shortcode( 'wpuf-registration', [ wpuf()->frontend->registration, 'registration_form' ] );
        add_shortcode( 'wpuf_form', [ wpuf()->frontend->frontend_form, 'add_post_shortcode' ] );
        add_shortcode( 'wpuf_edit', [ wpuf()->frontend->frontend_form, 'edit_post_shortcode' ] );
        add_shortcode( 'wpuf_editprofile', [ wpuf()->frontend->frontend_account, 'shortcode' ] );
        add_shortcode( 'wpuf_account', [ wpuf()->frontend->frontend_account, 'shortcode' ] );
        add_shortcode( 'wpuf-login', [ wpuf()->frontend->simple_login, 'login_form' ] );
        add_shortcode( 'wpuf_sub_info', [ wpuf()->subscription, 'subscription_info' ] );
        add_shortcode( 'wpuf_sub_pack', [ wpuf()->subscription, 'subscription_packs' ] );
        add_shortcode( 'wpuf-edit-users', 'wpuf_edit_users' );
        add_shortcode( 'wpuf-meta', 'wpuf_meta_shortcode' );
    }
}
