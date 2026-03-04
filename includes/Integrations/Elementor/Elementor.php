<?php
/**
 * DESCRIPTION: Elementor integration orchestrator. Registers widgets, categories, and handles asset enqueuing.
 *
 * @package WPUF\Elementor
 */

namespace WeDevs\Wpuf\Integrations\Elementor;

/**
 * Elementor Integration Class
 *
 * @since 4.0.0
 */
class Elementor {

    /**
     * Constructor
     *
     * @since 4.0.0
     */
    public function __construct() {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

        // Ensure editor scripts are enqueued for TinyMCE in Elementor preview
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Include Elementor pages with the User Directory widget in pretty URL rewrite rules
        // (used by the free module's PrettyUrls class when Pro is not active)
        add_filter( 'wpuf_ud_directory_pages', [ $this, 'add_elementor_directory_pages' ], 10, 2 );

        // Register pretty URL rewrite rules directly for Elementor pages — this runs regardless
        // of which module is active, ensuring the rules are always registered.
        add_action( 'init', [ $this, 'register_elementor_page_rewrite_rules' ], 5 );

        // Flush rewrite rules when Elementor saves a page with our widget
        add_action( 'elementor/editor/after_save', [ $this, 'maybe_flush_rules_on_elementor_save' ], 10, 2 );
    }

    /**
     * Enqueue Elementor Specific Styles for both frontend and editor
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_styles() {
        // Dequeue all WPUF hardcoded styles so Elementor styles can work properly
        wp_dequeue_style( 'wpuf-frontend-forms' );
        wp_dequeue_style( 'wpuf-layout1' );
        wp_dequeue_style( 'wpuf-layout2' );
        wp_dequeue_style( 'wpuf-layout3' );
        wp_dequeue_style( 'wpuf-layout4' );
        wp_dequeue_style( 'wpuf-layout5' );

        $style_handles = [ 'wpuf-elementor-frontend-forms' ];

        /**
         * Filters the list of style handles to enqueue in Elementor context
         *
         * @since WPUF_SINCE
         *
         * @param string[] $style_handles Array of style handles to enqueue.
         */
        $style_handles = apply_filters( 'wpuf_elementor_styles_to_enqueue', $style_handles );

        foreach ( $style_handles as $handle ) {
            wp_enqueue_style( $handle );
        }

        // User Directory assets — register if the free module hasn't done it
        // (e.g. when Pro is active, Free_Loader is skipped so Shortcode::register_assets() never runs)
        if ( ! wp_style_is( 'wpuf-user-directory-frontend', 'registered' ) ) {
            wp_register_style(
                'wpuf-user-directory-frontend',
                WPUF_ASSET_URI . '/css/wpuf-user-directory-free.css',
                [],
                WPUF_VERSION
            );
        }
        wp_enqueue_style( 'wpuf-user-directory-frontend' );
        wp_enqueue_style( 'wpuf-elementor-user-directory' );

        if ( wpuf_is_pro_active() ) {
            wp_enqueue_script( 'wpuf-conditional-logic' );
            wp_enqueue_script( 'wpuf-frontend-form' );

            // Pro's Assets::enqueue_shortcode_assets() bails on Elementor pages because
            // it checks has_shortcode($post->post_content) which is always false for Elementor
            // (widget data lives in _elementor_data meta). Enqueue Pro's UD styles here instead.
            wp_enqueue_style( 'wpuf-ud-styles' );
            wp_enqueue_style( 'wpuf-ud-shortcode-styles' );
        }

        /**
         * Fires after WPUF has enqueued its styles in Elementor context
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_elementor_after_enqueue_styles' );
    }

    /**
     * Enqueue Elementor Specific Scripts for both frontend and editor
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function enqueue_scripts() {
        // Enqueue all required WPUF form assets
        $this->enqueue_wpuf_form_assets();

        // User Directory scripts — register if the free module hasn't done it
        if ( ! wp_script_is( 'wpuf-user-directory-frontend', 'registered' ) ) {
            wp_register_script(
                'wpuf-user-directory-frontend',
                WPUF_ASSET_URI . '/js/wpuf-user-directory-frontend.js',
                [ 'jquery' ],
                WPUF_VERSION,
                true
            );
        }
        if ( ! wp_script_is( 'wpuf-ud-search-shortcode', 'registered' ) ) {
            wp_register_script(
                'wpuf-ud-search-shortcode',
                WPUF_ASSET_URI . '/js/ud-search-shortcode.js',
                [],
                WPUF_VERSION,
                true
            );
        }
        wp_enqueue_script( 'wpuf-user-directory-frontend' );
        wp_enqueue_script( 'wpuf-ud-search-shortcode' );

        // Localize search script
        wp_localize_script(
            'wpuf-ud-search-shortcode',
            'wpufUserDirectorySearch',
            [
                'restUrl' => rest_url( 'wpuf/v1/user_directory/search' ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ]
        );

        // Ensure editor scripts are loaded for TinyMCE in Elementor preview
        if ( function_exists( 'wp_enqueue_editor' ) ) {
            wp_enqueue_editor();

            // Also explicitly enqueue TinyMCE scripts if available
            if ( function_exists( 'wp_enqueue_script' ) ) {
                // Check if these scripts exist and enqueue them
                global $wp_scripts;
                if ( isset( $wp_scripts->registered['tinymce'] ) ) {
                    wp_enqueue_script( 'tinymce' );
                }
                if ( isset( $wp_scripts->registered['wp-tinymce'] ) ) {
                    wp_enqueue_script( 'wp-tinymce' );
                }
            }
        }

        /**
         * Fires after WPUF has enqueued its scripts in Elementor context
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_elementor_after_enqueue_scripts' );
    }

    /**
     * Enqueue all required WPUF form assets for Elementor
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    private function enqueue_wpuf_form_assets() {
        // Core styles
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_style( 'wpuf-jquery-ui' );

        // Core scripts required for all WPUF forms
        wp_enqueue_script( 'suggest' );
        wp_enqueue_script( 'wpuf-billing-address' );
        wp_enqueue_script( 'wpuf-upload' );
        wp_enqueue_script( 'wpuf-frontend-form' );
        wp_enqueue_script( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-subscriptions' );

        // Localize wpuf-upload script
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
                            'title'      => __( 'Allowed Files', 'wp-user-frontend' ),
                            'extensions' => '*',
                        ],
                    ],
                    'multipart'        => true,
                    'urlstream_upload'  => true,
                    'warning'          => __( 'Maximum number of files reached!', 'wp-user-frontend' ),
                    'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wp-user-frontend' ),
                    'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wp-user-frontend' ),
                ],
            ]
        );

        // Localize wpuf-frontend-form script
        wp_localize_script(
            'wpuf-frontend-form',
            'wpuf_frontend',
            apply_filters(
                'wpuf_frontend_object',
                [
                    'asset_url'                    => WPUF_ASSET_URI,
                    'ajaxurl'                      => admin_url( 'admin-ajax.php' ),
                    'error_message'                => __( 'Please fix the errors to proceed', 'wp-user-frontend' ),
                    'nonce'                        => wp_create_nonce( 'wpuf_nonce' ),
                    'word_limit'                   => __( 'Word limit reached', 'wp-user-frontend' ),
                    'cancelSubMsg'                 => __( 'Are you sure you want to cancel your current subscription ?', 'wp-user-frontend' ),
                    'delete_it'                    => __( 'Yes', 'wp-user-frontend' ),
                    'cancel_it'                    => __( 'No', 'wp-user-frontend' ),
                    'word_max_title'               => __( 'Maximum word limit reached. Please shorten your texts.', 'wp-user-frontend' ),
                    'word_max_details'             => __( 'This field supports a maximum of %number% words, and the limit is reached. Remove a few words to reach the acceptable limit of the field.', 'wp-user-frontend' ),
                    'word_min_title'               => __( 'Minimum word required.', 'wp-user-frontend' ),
                    'word_min_details'             => __( 'This field requires minimum %number% words. Please add some more text.', 'wp-user-frontend' ),
                    'char_max_title'               => __( 'Maximum character limit reached. Please shorten your texts.', 'wp-user-frontend' ),
                    'char_max_details'             => __( 'This field supports a maximum of %number% characters, and the limit is reached. Remove a few characters to reach the acceptable limit of the field.', 'wp-user-frontend' ),
                    'char_min_title'               => __( 'Minimum character required.', 'wp-user-frontend' ),
                    'char_min_details'             => __( 'This field requires minimum %number% characters. Please add some more character.', 'wp-user-frontend' ),
                    'protected_shortcodes'         => wpuf_get_protected_shortcodes(),
                    'protected_shortcodes_message' => __( 'Using %shortcode% is restricted', 'wp-user-frontend' ),
                    'password_warning_weak'        => __( 'Your password should be at least weak in strength', 'wp-user-frontend' ),
                    'password_warning_medium'      => __( 'Your password needs to be medium strength for better protection', 'wp-user-frontend' ),
                    'password_warning_strong'      => __( 'Create a strong password for maximum security', 'wp-user-frontend' ),
                ]
            )
        );

        wp_localize_script(
            'wpuf-frontend-form',
            'error_str_obj',
            [
                'required'   => __( 'is required', 'wp-user-frontend' ),
                'mismatch'   => __( 'does not match', 'wp-user-frontend' ),
                'validation' => __( 'is not valid', 'wp-user-frontend' ),
            ]
        );

        // Localize subscription script
        wp_localize_script(
            'wpuf-subscriptions',
            'wpuf_subscription',
            apply_filters(
                'wpuf_subscription_js_data',
                [
                    'pack_notice' => __( 'Please Cancel Your Currently Active Pack first!', 'wp-user-frontend' ),
                ]
            )
        );

        // Localize billing address script
        wp_localize_script(
            'wpuf-billing-address',
            'ajax_object',
            [
                'ajaxurl'     => admin_url( 'admin-ajax.php' ),
                'fill_notice' => __( 'Some Required Fields are not filled!', 'wp-user-frontend' ),
            ]
        );

        // Conditionally enqueue Google Maps if API key is configured
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );
        if ( ! empty( $api_key ) ) {
            $scheme = is_ssl() ? 'https' : 'http';
            wp_enqueue_script( 'wpuf-google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key, [], null, true );
        }
    }

    /**
     * Register Elementor Widget Category
     *
     * @since WPUF_SINCE
     *
     * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
     *
     * @return void
     */
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'user-frontend',
            [
                'title' => __( 'User Frontend', 'wp-user-frontend' ),
                'icon'  => 'eicon-form-horizontal',
            ]
        );
    }

    /**
     * Register Elementor Widgets
     *
     * @since WPUF_SINCE
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     *
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        require_once __DIR__ . '/User_Directory_Widget.php';

        $widgets_manager->register( new User_Directory_Widget() );
    }

    /**
     * Flush rewrite rules when Elementor saves a page containing the User Directory widget
     *
     * @since WPUF_SINCE
     *
     * @param int   $post_id The post ID.
     * @param array $data    The Elementor data.
     *
     * @return void
     */
    public function maybe_flush_rules_on_elementor_save( $post_id, $data ) {
        $post = get_post( $post_id );

        if ( ! $post || 'page' !== $post->post_type ) {
            return;
        }

        $elementor_data = get_post_meta( $post_id, '_elementor_data', true );

        $has_widget = ! empty( $elementor_data ) && is_string( $elementor_data ) && strpos( $elementor_data, '"widgetType":"wpuf-user-directory"' ) !== false;

        // Keep a queryable meta flag so register_elementor_page_rewrite_rules() can find
        // these pages efficiently without scanning all _elementor_data values on every init.
        if ( $has_widget ) {
            update_post_meta( $post_id, '_wpuf_has_ud_elementor_widget', '1' );
            flush_rewrite_rules();
        } else {
            delete_post_meta( $post_id, '_wpuf_has_ud_elementor_widget' );
        }
    }

    /**
     * Register pretty URL rewrite rules for pages using the User Directory Elementor widget
     *
     * This runs directly on init (priority 5) so the rules are registered regardless of
     * whether the free or Pro module is handling PrettyUrls. Pages are found via a post
     * meta flag (_wpuf_has_ud_elementor_widget) set in maybe_flush_rules_on_elementor_save().
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_elementor_page_rewrite_rules() {
        // Fast path: query only pages that have been flagged via the meta key.
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        $pages = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_wpuf_has_ud_elementor_widget',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
        ] );

        // Fallback: if no flagged pages exist (e.g. first deploy, meta not yet written),
        // scan _elementor_data for existing pages and back-fill the meta flag so future
        // requests use the fast path. This only runs once until a page is found.
        if ( empty( $pages ) ) {
            $all_pages = get_posts( [
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ] );

            foreach ( $all_pages as $page ) {
                $elementor_data = get_post_meta( $page->ID, '_elementor_data', true );
                if ( ! empty( $elementor_data ) && is_string( $elementor_data ) && strpos( $elementor_data, '"widgetType":"wpuf-user-directory"' ) !== false ) {
                    update_post_meta( $page->ID, '_wpuf_has_ud_elementor_widget', '1' );
                    $pages[] = $page;
                }
            }

            // If we found pages via the fallback scan, flush so the new rules take effect.
            if ( ! empty( $pages ) ) {
                flush_rewrite_rules();
            }
        }

        foreach ( $pages as $page ) {
            $page_slug = $page->post_name;
            add_rewrite_rule(
                '^' . $page_slug . '/([^/]+)/?$',
                'index.php?pagename=' . $page_slug . '&wpuf_user_profile=$matches[1]',
                'top'
            );
        }
    }

    /**
     * Add Elementor pages that use the User Directory widget to the pretty URL rewrite rules
     *
     * Elementor stores widget data in post meta, not in post_content, so pages
     * using the User Directory widget via Elementor won't be detected by the
     * default shortcode-based check in PrettyUrls::get_directory_pages().
     *
     * @since WPUF_SINCE
     *
     * @param array $directory_pages Pages already detected via shortcode.
     * @param array $all_pages       All published pages.
     *
     * @return array
     */
    public function add_elementor_directory_pages( $directory_pages, $all_pages ) {
        $existing_ids = wp_list_pluck( $directory_pages, 'ID' );

        foreach ( $all_pages as $page ) {
            // Skip pages already detected via shortcode
            if ( in_array( $page->ID, $existing_ids, true ) ) {
                continue;
            }

            $elementor_data = get_post_meta( $page->ID, '_elementor_data', true );

            if ( ! empty( $elementor_data ) && is_string( $elementor_data ) && strpos( $elementor_data, '"widgetType":"wpuf-user-directory"' ) !== false ) {
                $directory_pages[] = $page;
            }
        }

        return $directory_pages;
    }
}
