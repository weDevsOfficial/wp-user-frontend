<?php

namespace WeDevs\Wpuf;

use WeDevs\WpUtils\ContainerTrait;

/**
 * The Admin class which will hold all the starting point of WordPress dashboard admin operations for WPUF
 * We will initialize all the admin classes from here.
 *
 * @since 4.0.0
 */
class Admin {
    use ContainerTrait;

    public function __construct() {
        $this->container['admin_welcome']         = new Admin\Admin_Welcome();
        $this->container['menu']                  = new Admin\Menu();
        $this->container['dashboard_metabox']     = new Admin\Dashboard_Metabox();
        $this->container['form_template']         = new Admin\Forms\Post\Templates\Form_Template();
        $this->container['admin_form']            = new Admin\Forms\Admin_Form();
        $this->container['admin_form_handler']    = new Admin\Forms\Admin_Form_Handler();
        $this->container['admin_subscription']    = new Admin\Admin_Subscription();
        $this->container['admin_installer']       = new Admin\Admin_Installer();
        $this->container['settings']              = new Admin\Admin_Settings();
        $this->container['forms']                 = new Admin\Forms\Form_Manager();
        $this->container['gutenberg_block']       = new Frontend\Form_Gutenberg_Block();
        $this->container['plugin_upgrade_notice'] = new Admin\Plugin_Upgrade_Notice();
        $this->container['posting']               = new Admin\Posting();
        $this->container['shortcodes_button']     = new Admin\Shortcodes_Button();
        $this->container['tools']                 = new Admin\Admin_Tools();

        // only free users will see the promotion
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->container['promotion'] = new Admin\Promotion();
        }

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_post_form_template', [ $this, 'create_post_form_from_template' ] );

        // enqueue common scripts that will load throughout WordPress dashboard. notice, what's new etc.
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_common_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_cpt_page_scripts' ] );

        // block admin access as per wpuf settings
        add_action( 'admin_init', [ $this, 'block_admin_access' ] );
    }

    /**
     * Create post form templates depending on the action
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function create_post_form_from_template() {
        $this->container['form_template']->create_post_form_from_template();
    }

    /**
     * Enqueue the common CSS and JS needed for WordPress admin area
     *
     * @since 4.0.0
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

    public function enqueue_cpt_page_scripts( $hook_suffix ) {
        $cpt = [ 'wpuf_subscription', 'post', 'page' ];
        if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
            wp_enqueue_script( 'wpuf-subscriptions' );
            $screen = get_current_screen();

            if ( is_object( $screen ) && in_array( $screen->post_type, $cpt, true ) ) {
                wp_enqueue_script( 'wpuf-subscriptions' );
            }
        }

        if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
            wp_enqueue_script( 'wpuf-upload' );
            wp_localize_script(
                'wpuf-upload',
                'wpuf_upload',
                [
                    'confirmMsg' => __( 'Are you sure?', 'wp-user-frontend' ),
                    'delete_it'  => __( 'Yes, delete it', 'wp-user-frontend' ),
                    'cancel_it'  => __( 'No, cancel it', 'wp-user-frontend' ),
                    'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                    'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
                    'plupload'   => [
                        'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                        'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                        'filters'          => [
                            [
                                'title' => __( 'Allowed Files', 'wp-user-frontend' ),
                                'extensions' => '*',
                            ],
                        ],
                        'multipart'        => true,
                        'urlstream_upload' => true,
                        'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                        'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wp-user-frontend' ),
                        'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend' ),
                    ],
                ]
            );
        }
    }

    /**
     * Block user access to admin panel for specific roles
     *
     * @global string $pagenow
     */
    public function block_admin_access() {
        global $pagenow;

        // bail out if we are from WP Cli
        if ( defined( 'WP_CLI' ) ) {
            return;
        }

        $access_level = wpuf_get_option( 'admin_access', 'wpuf_general', 'read' );
        $valid_pages  = [ 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' ];

        if ( ! current_user_can( $access_level ) && ! in_array( $pagenow, $valid_pages ) ) {
            // wp_die( __( 'Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'wpuf' ) );
            wp_redirect( home_url() );
            exit;
        }
    }
}
