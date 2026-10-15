<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\Providers\AdminServiceProvider;

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
        // Every admin service comes from the platform container (AdminServiceProvider,
        // Core for the installer, Ai for the AI handler): one instance per request,
        // built here in the old order so the constructors add their hooks as before.
        $platform = wpuf()->platform();

        foreach ( [ 'admin_welcome', 'menu', 'dashboard_metabox', 'form_template', 'admin_form', 'admin_form_handler' ] as $key ) {
            $this->container[ $key ] = $platform->get( AdminServiceProvider::class_of( $key ) );
        }

        $this->container['ai_form_handler']    = $platform->get( Admin\Forms\AI_Form_Handler::class );
        $this->container['admin_subscription'] = $platform->get( Admin\Admin_Subscription::class );
        $this->container['admin_installer']    = $platform->get( Admin\Admin_Installer::class );

        // Legacy wpuf/form block (gutenberg_block). Superseded by the wpuf/post-form block but
        // still registered so existing posts keep editing without an invalid block notice.
        foreach ( [ 'settings', 'forms', 'gutenberg_block', 'plugin_upgrade_notice', 'posting', 'shortcodes_button', 'tools', 'onboarding' ] as $key ) {
            $this->container[ $key ] = $platform->get( AdminServiceProvider::class_of( $key ) );
        }

        // Notice for plugins that still use the retired Vue builder hooks (4.4g).
        // On admin_init: the platform providers register after this constructor.
        add_action(
            'admin_init',
            function () {
                wpuf()->platform()->get( Builder\HookDeprecations::class )->register();
            }
        );

        // only free users will see the promotion
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->container['promotion'] = $platform->get( AdminServiceProvider::class_of( 'promotion' ) );
        }

        // dynamic hook. format: "admin_action_{$action}". more details: wp-admin/admin.php
        add_action( 'admin_action_post_form_template', [ $this, 'create_post_form_from_template' ] );

        // enqueue common scripts that will load throughout WordPress dashboard. notice, what's new etc.
        add_action( 'init', [ $this, 'enqueue_common_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_cpt_page_scripts' ] );
        add_action( 'wpuf_load_ai_form_builder_page', [ $this, 'enqueue_ai_form_builder_scripts' ] );

        // block admin access as per wpuf settings
        add_action( 'admin_init', [ $this, 'block_admin_access' ] );
    }

    /**
     * Create a post form from the selected template
     *
     * @return void
     */
    public function create_post_form_from_template() {
        // Verify nonce for security
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_create_from_template' ) ) {
            wp_die( __( 'Security check failed', 'wp-user-frontend' ) );
        }

        // Check if this is an AI form template
        // Verify the template parameter is set and valid
        if ( ! isset( $_GET['template'] ) ) {
            $this->container['form_template']->create_post_form_from_template();
            return;
        }

        $template_name = sanitize_text_field( wp_unslash( $_GET['template'] ) );

        if ( $template_name === 'ai_form' ) {
            // AI Form Handler will verify its own nonce (redundant but safe)
            $this->container['ai_form_handler']->handle_ai_form_template();
            return;
        }

        // Otherwise, handle normal templates
        $this->container['form_template']->create_post_form_from_template();
    }    /**
     * Enqueue the common CSS and JS needed for WordPress admin area
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function enqueue_common_scripts() {
        if ( ! is_admin() ) {
            return;
        }

        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-admin' );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        // The settings screen shows the same header (4.6b): its changelog icon was an empty dot.
        $selected_page = [ 'wpuf-post-forms', 'wpuf-profile-forms', 'wpuf_subscription', 'wpuf_subscriptions', 'wpuf_transaction', 'wpuf_tools', 'wpuf-settings' ];

        // The admin app page shows these screens as routes.
        if ( in_array( $page, $selected_page, true ) || 'wp-user-frontend' === $page ) {
            // Printed in the footer (task 2.5b): called here on `init`, its script
            // landed before <!DOCTYPE> and put these screens in quirks mode.
            // admin_footer passes '' as its first argument, so keep the default selector.
            add_action(
                'admin_footer',
                function () {
                    wpuf_load_headway_badge();
                }
            );
        }

        wp_localize_script(
            'wpuf-admin', 'wpuf_admin_script',
            [
                'rest'                          => wpuf_rest_script_data(),
                'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
                'nonce'                        => wp_create_nonce( 'wpuf_nonce' ),
                'cleared_schedule_lock'        => __( 'Post lock has been cleared', 'wp-user-frontend' ),
                'asset_url'                    => WPUF_ASSET_URI,
                'admin_url'                    => admin_url(),
                'support_url'                  => esc_url(
                    'https://wedevs.com/contact/?utm_source=wpuf-subscription'
                ),
                'version'                      => WPUF_VERSION,
                'pro_version'                  => defined( 'WPUF_PRO_VERSION' ) ? WPUF_PRO_VERSION : '',
                'isProActive'                  => class_exists( 'WP_User_Frontend_Pro' ),
                'plan'                         => function_exists( 'wpuf_pro_current_plan' ) ? wpuf_pro_current_plan() : '',
                'protected_shortcodes'         => wpuf_get_protected_shortcodes(),
                'protected_shortcodes_message' => sprintf(
                    // translators: %1$s is the opening div tag, %2$s is the shortcode [wpuf-registration], %3$s is the opening strong tag, %4$s is the closing strong tag, %5$s is the closing div tag
                    __( '%1$sThis post contains a sensitive short-code %2$s, that may allow others to sign-up with distinguished roles. If unsure, remove the short-code before publishing (recommended) %3$sas this may be exploited as a security vulnerability.%4$s', 'wp-user-frontend' ),
                    '<div style="font-size: 1em; text-align: justify; color: darkgray">',
                    '[wpuf-registration]',
                    '<strong>',
                    '</strong>',
                    '</div>'
                ),
                'upgradeUrl'                   => esc_url(
                    'https://wedevs.com/wp-user-frontend-pro/pricing/'
                ),
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
                    'rest'        => wpuf_rest_script_data(),
                    'upload_nonce' => wp_create_nonce( 'wpuf-upload-nonce' ),
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
     * Enqueue scripts for AI form builder page
     *
     * @since 4.0.0
     * @since WPUF_SINCE Forwards to Admin\Screens\AiFormBuilder::enqueue() (React app).
     *
     * @param string $form_type Form type ('post' or 'profile')
     * @return void
     */
    public function enqueue_ai_form_builder_scripts( $form_type = 'post' ) {
        wpuf()->platform()->get( Admin\Screens\AiFormBuilder::class )->enqueue( $form_type );
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

        if ( ! current_user_can( $access_level ) && ! in_array( $pagenow, $valid_pages, true ) ) {
            // wp_die( __( 'Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'wpuf' ) );
            wp_redirect( home_url() );
            exit;
        }
    }
}
