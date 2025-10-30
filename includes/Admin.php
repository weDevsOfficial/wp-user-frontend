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
        $this->container['ai_form_handler']       = new Admin\Forms\AI_Form_Handler();
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

        wp_enqueue_style( 'wpuf-whats-new' );
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-admin' );

        $page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
        $selected_page = [ 'wpuf-post-forms', 'wpuf-profile-forms', 'wpuf_subscription', 'wpuf_transaction', 'wpuf_tools' ];

        if ( in_array( $page, $selected_page ) ) {
            wpuf_load_headway_badge();
        }

        wp_localize_script(
            'wpuf-admin', 'wpuf_admin_script',
            [
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

        // Add inline script for dynamic API key link
        wp_add_inline_script( 'wpuf-admin', '
            jQuery(document).ready(function($) {
                function updateAPIKeyLink() {
                    var provider = $("[name=\'wpuf_ai[ai_provider]\']").val() || "openai";
                    var $link = $(".wpuf-api-key-link");
                    if ($link.length) {
                        var url = $link.data(provider) || $link.data("openai");
                        $link.attr("href", url);
                    }
                }

                // Update on provider change
                $(document).on("change", "[name=\'wpuf_ai[ai_provider]\']", updateAPIKeyLink);

                // Initial update when page loads
                updateAPIKeyLink();
            });
        ' );
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
     * Enqueue scripts for AI form builder page
     *
     * @since 4.0.0
     *
     * @param string $form_type Form type ('post' or 'profile')
     * @return void
     */
    public function enqueue_ai_form_builder_scripts( $form_type = 'post' ) {
        wp_enqueue_script( 'wpuf-ai-form-builder' );
        wp_enqueue_style( 'wpuf-ai-form-builder' );

        // Get AI settings
        $ai_settings = get_option('wpuf_ai', []);

        // Determine if we should expose API key status based on user capabilities
        $show_api_status = current_user_can( wpuf_admin_role() );

        // Prepare localization data
        $localize_data = [
            'version'    => WPUF_VERSION,
            'assetUrl'   => WPUF_ASSET_URI,
            'siteUrl'    => site_url(),
            'nonce'      => wp_create_nonce( 'wp_rest' ),
            'rest_url'   => esc_url_raw( rest_url() ),
            'formType'   => $form_type, // Pass form type to frontend
            'provider'   => $ai_settings['ai_provider'] ?? 'openai',
            'model'      => $ai_settings['ai_model'] ?? 'gpt-3.5-turbo',
            'hasApiKey'  => $show_api_status ? !empty($ai_settings['ai_api_key']) : null,
            'isProActive' => class_exists( 'WP_User_Frontend_Pro' ),
            'temperature' => floatval( $ai_settings['temperature'] ?? 0.7 ),
            'maxTokens'  => intval( $ai_settings['max_tokens'] ?? 2000 ),
            'i18n' => [
                'errorTitle' => __('Error', 'wp-user-frontend'),
                'errorMessage' => __('Something went wrong. Please try again.', 'wp-user-frontend'),
                'invalidRequest' => __('Invalid Request', 'wp-user-frontend'),
                'nonFormRequest' => __('I can only help with form creation. Try: "Create a contact form"', 'wp-user-frontend'),
                'proFieldWarning' => __('Pro Feature Required', 'wp-user-frontend'),
                'proFieldMessage' => __('This field type requires WP User Frontend Pro. You can continue without it or upgrade to Pro for full functionality.', 'wp-user-frontend'),
                'continueWithoutPro' => __('Continue without Pro', 'wp-user-frontend'),
                'upgradeToPro' => __('Upgrade to Pro', 'wp-user-frontend'),
                'tryAgain' => __('Try Again', 'wp-user-frontend'),
                'close' => __('Close', 'wp-user-frontend'),
            ]
        ];

        /**
         * Filter the AI Form Builder localization data.
         *
         * Allows external code to modify or enrich the data passed to the frontend,
         * including custom templates, stages, prompts, or form details.
         *
         * @since 4.2.1
         *
         * @param array $localize_data Localization data array to be passed to wp_localize_script.
         */
        $localize_data = apply_filters( 'wpuf_ai_form_builder_localize_data', $localize_data );

        wp_localize_script(
            'wpuf-ai-form-builder',
            'wpufAIFormBuilder',
            $localize_data
        );

        // Debug: Output form type as HTML comment for verification
        add_action( 'admin_footer', function() use ( $form_type ) {
            echo "\n<!-- WPUF AI Form Builder Debug: formType = " . esc_html( $form_type ) . " -->\n";
        } );
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
