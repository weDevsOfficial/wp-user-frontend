<?php

namespace Wp\User\Frontend;

/**
 * The Admin class which will hold all the starting point of WordPress dashboard admin operations for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since WPUF_SINCE
 */

class Admin {
    public function __construct() {
        wpuf()->add_to_container( 'menu', new Admin\Menu() );
        wpuf()->add_to_container( 'dashboard_metabox', new Admin\Dashboard_Metabox() );
        wpuf()->add_to_container( 'form_template', new Admin\Forms\Post\Templates\Admin_Form_Template() );
        wpuf()->add_to_container( 'admin_form', new Admin\Forms\Admin_Form() );
        wpuf()->add_to_container( 'admin_form_handler', new Admin\Forms\Admin_Form_Handler() );
        wpuf()->add_to_container( 'admin_subscription', new Admin\Admin_Subscription() );
        wpuf()->add_to_container( 'admin_installer', new Admin\Admin_Installer() );
        wpuf()->add_to_container( 'settings', new Admin\Admin_Settings() );
        wpuf()->add_to_container( 'forms', new Admin\Forms\Form_Manager() );
        wpuf()->add_to_container( 'gutenberg_block', new Frontend\Form_Gutenberg_Block() );
        wpuf()->add_to_container( 'whats_new', new Whats_New() );

        // post form submenu operations
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_post_form_scripts' ] );

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_post_form_template', [ $this, 'create_post_form_from_template' ] );
    }

    /**
     * Create post form templates depending on the action
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function create_post_form_from_template() {
        wpuf()->form_template->create_post_form_from_template();
    }

    /**
     * Enqueue the scripts needed for wpuf post form
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_post_form_scripts() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-subscriptions' );
    }
}
