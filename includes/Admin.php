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
        wpuf()->add_to_container( 'form_template', new Admin\PostFormTemplates\WPUF_Admin_Form_Template() );
        wpuf()->add_to_container( 'admin_form', new Admin\WPUF_Admin_Form() );
        wpuf()->add_to_container( 'admin_form_handler', new Admin\WPUF_Admin_Form_Handler() );
        wpuf()->add_to_container( 'admin_subscription', new Admin\WPUF_Admin_Subscription() );
        // $this->container['admin_subscription'] = new Wp\User\Frontend\Admin\WPUF_Admin_Subscription();

        // post form submenu operations
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_post_form_scripts' ] );

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_wpuf_post_form_template', [ $this, 'create_post_form_from_template' ] );
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
