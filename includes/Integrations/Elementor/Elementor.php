<?php

namespace WeDevs\Wpuf\Integrations\Elementor;

/**
 * Elementor Integration Class
 *
 * @since 4.0.0
 */
class Elementor {

    public function __construct() {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'enqueue_styles' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_styles' ] );

        // Ensure editor scripts are enqueued for TinyMCE in Elementor preview
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Whether this request needs the WPUF assets in Elementor context.
     *
     * The editor page and the editor preview iframe always do (widgets can be
     * added without a reload). On the frontend only documents that hold a WPUF
     * widget or shortcode do; every other Elementor page loads nothing from WPUF.
     * Widgets rendered from a theme-builder template or popup are covered by the
     * render-time fallback in Frontend: they render through do_shortcode(), and
     * Frontend::enqueue_on_shortcode_render() loads the bundle at that moment.
     *
     * @since 4.3.12
     *
     * @return bool
     */
    private function should_enqueue_assets() {
        if ( is_admin() ) {
            return true;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) || ! did_action( 'elementor/loaded' ) ) {
            return false;
        }

        $elementor = \Elementor\Plugin::$instance;

        if ( isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode() ) {
            return true;
        }

        $frontend = wpuf()->frontend;

        if ( $frontend instanceof \WeDevs\Wpuf\Frontend ) {
            return $frontend->elementor_document_has_wpuf();
        }

        return false;
    }

    /**
     * Enqueue Elementor Specific Styles for both frontend and editor
     *
     * @since 4.3.1
     *
     * @return void
     */
    public function enqueue_styles() {
        if ( ! $this->should_enqueue_assets() ) {
            return;
        }

        // Dequeue all WPUF hardcoded styles so Elementor styles can work properly
        wp_dequeue_style( 'wpuf-frontend-forms' );
        wp_dequeue_style( 'wpuf-layout1' );
        wp_dequeue_style( 'wpuf-layout2' );
        wp_dequeue_style( 'wpuf-layout3' );
        wp_dequeue_style( 'wpuf-layout4' );
        wp_dequeue_style( 'wpuf-layout5' );

        $style_handles = [ 'wpuf-elementor-frontend-forms', 'wpuf-account' ];

        /**
         * Filters the list of style handles to enqueue in Elementor context.
         *
         * @since 4.3.1
         *
         * @param string[] $style_handles Array of style handles to enqueue.
         */
        $style_handles = apply_filters( 'wpuf_elementor_styles_to_enqueue', $style_handles );

        foreach ( $style_handles as $handle ) {
            wp_enqueue_style( $handle );
        }

        if ( wpuf_is_pro_active() ) {
            wp_enqueue_script( 'wpuf-conditional-logic' );
            wp_enqueue_script( 'wpuf-frontend-form' );
        }

        /**
         * Fires after WPUF has enqueued its styles in Elementor context.
         *
         * @since 4.3.1
         */
        do_action( 'wpuf_elementor_after_enqueue_styles' );
    }

    /**
     * Enqueue Elementor Specific Scripts for both frontend and editor
     *
     * Ensures TinyMCE editor scripts are loaded when WPUF forms with rich text
     * fields are rendered in Elementor preview.
     *
     * @since 4.3.1
     *
     * @return void
     */
    public function enqueue_scripts() {
        if ( ! $this->should_enqueue_assets() ) {
            return;
        }

        // Enqueue all required WPUF form assets
        $this->enqueue_wpuf_form_assets();

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
         * Fires after WPUF has enqueued its scripts in Elementor context.
         *
         * @since 4.3.1
         */
        do_action( 'wpuf_elementor_after_enqueue_scripts' );
    }

    /**
     * Enqueue all required WPUF form assets for Elementor
     *
     * Ensures all necessary styles and scripts are loaded for WPUF forms
     * to render properly in Elementor preview and frontend.
     *
     * @since 4.3.1
     *
     * @return void
     */
    private function enqueue_wpuf_form_assets() {
        $frontend = wpuf()->frontend;

        if ( $frontend instanceof \WeDevs\Wpuf\Frontend ) {
            // Same bundle and localized data as every other WPUF page (single source of truth).
            $frontend->enqueue_form_assets();
            $frontend->enqueue_account_assets();
        }

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
     * @param \Elementor\Elements_Manager $elements_manager
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
     * @param \Elementor\Widgets_Manager $widgets_manager
     *
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        require_once __DIR__ . '/Widget.php';
        require_once __DIR__ . '/Subscription_Plans_Widget.php';
        require_once __DIR__ . '/Account_Widget.php';

        $widgets_manager->register( new Widget() );
        $widgets_manager->register( new Subscription_Plans_Widget() );
        $widgets_manager->register( new Account_Widget() );
    }
}
