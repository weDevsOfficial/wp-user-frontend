<?php

namespace WeDevs\Wpuf;

/**
 * The Admin class which will hold all the starting point of WordPress dashboard admin operations for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since WPUF_SINCE
 */

class Admin {
    public $tools;

    public function __construct() {
        $this->admin_welcome         = new Admin\Admin_Welcome();
        $this->menu                  = new Admin\Menu();
        $this->dashboard_metabox     = new Admin\Dashboard_Metabox();
        $this->form_template         = new Admin\Forms\Post\Templates\Form_Template();
        $this->admin_form            = new Admin\Forms\Admin_Form();
        $this->admin_form_handler    = new Admin\Forms\Admin_Form_Handler();
        $this->admin_subscription    = new Admin\Admin_Subscription();
        $this->admin_installer       = new Admin\Admin_Installer();
        $this->settings              = new Admin\Admin_Settings();
        $this->forms                 = new Admin\Forms\Form_Manager();
        $this->gutenberg_block       = new Frontend\Form_Gutenberg_Block();
        $this->whats_new             = new Admin\Whats_New();
        $this->promotion             = new Admin\Promotion();
        $this->plugin_upgrade_notice = new Admin\Plugin_Upgrade_Notice();

        // post form submenu operations
        add_action( 'wpuf_load_post_forms', [ $this, 'enqueue_post_form_scripts' ] );

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_post_form_template', [ $this, 'create_post_form_from_template' ] );

        // enqueue common scripts that will load throughout WordPress dashboard. notice, what's new etc.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_common_scripts' ] );
    }

    /**
     * Create post form templates depending on the action
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function create_post_form_from_template() {
        $this->form_template->create_post_form_from_template();
    }

    /**
     * Enqueue the scripts needed for wpuf post form
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_post_form_scripts() {
        wp_enqueue_script( 'wpuf-subscriptions' );
    }

    /**
     * Enqueue the common CSS and JS needed for WordPress admin area
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_common_scripts() {
        wp_enqueue_style( 'wpuf-whats-new' );
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-admin' );

        wp_localize_script(
            'wpuf-admin', 'wpuf_admin_script',
            [
                'ajaxurl'               => admin_url( 'admin-ajax.php' ),
                'nonce'                 => wp_create_nonce( 'wpuf_nonce' ),
                'cleared_schedule_lock' => __( 'Post lock has been cleared', 'wp-user-frontend' ),
                'asset_url' => WPUF_ASSET_URI,
                'protected_shortcodes'         => wpuf_get_protected_shortcodes(),
                'protected_shortcodes_message' => sprintf(
                    __( '%sThis post contains a sensitive short-code %s, that may allow others to sign-up with distinguished roles. If unsure, remove the short-code before publishing (recommended) %sas this may be exploited as a security vulnerability.%s', 'wp-user-frontend' ),
                    '<div style="font-size: 1em; text-align: justify; color: darkgray">',
                    '[wpuf-registration]',
                    '<strong>',
                    '</strong>',
                    '</div>'
                )
            ]
        );
    }
}
