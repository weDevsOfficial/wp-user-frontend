<?php

namespace Wp\User\Frontend;

class Admin {
    function __construct() {
        wpuf()->add_to_container( 'menu', new Admin\Menu() );
        wpuf()->add_to_container( 'form_template', new Admin\PostFormTemplates\WPUF_Admin_Form_Template() );
        wpuf()->add_to_container( 'admin_form', new Admin\WPUF_Admin_Form() );
        wpuf()->add_to_container( 'admin_form_handler', new Admin\WPUF_Admin_Form_Handler() );

        // post form submenu operations
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_post_form_scripts' ] );

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_wpuf_post_form_template', [ $this, 'create_post_form_from_template' ] );
    }

    public function create_post_form_from_template() {
        wpuf()->form_template->create_post_form_from_template();
    }

    public function enqueue_post_form_scripts() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-admin' );
    }
}
