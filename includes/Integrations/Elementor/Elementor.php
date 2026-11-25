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
     * Option holding the IDs of the pages with the User Directory widget
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    const WIDGET_PAGES_OPTION = 'wpuf_ud_elementor_pages';

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

        // User Directory styles: only registered here (when Pro is active, Free_Loader is skipped
        // so Shortcode::register_assets() never runs). User_Directory_Widget::get_style_depends()
        // loads them only where that widget renders: their form resets would hide the checkboxes
        // and radios of every other WPUF form on the page.
        if ( ! wp_style_is( 'wpuf-user-directory-frontend', 'registered' ) ) {
            wp_register_style(
                'wpuf-user-directory-frontend',
                WPUF_ASSET_URI . '/css/wpuf-user-directory-free.css',
                [],
                WPUF_VERSION
            );
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
     * @since 4.3.1
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
     * @since 4.3.1
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     *
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        require_once __DIR__ . '/Widget.php';
        require_once __DIR__ . '/Subscription_Plans_Widget.php';
        require_once __DIR__ . '/Account_Widget.php';
        require_once __DIR__ . '/User_Directory_Widget.php';

        $widgets_manager->register( new Widget() );
        $widgets_manager->register( new Subscription_Plans_Widget() );
        $widgets_manager->register( new Account_Widget() );
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

        $page_ids = $this->get_widget_page_ids();
        $listed   = in_array( (int) $post_id, $page_ids, true );

        if ( $has_widget ) {
            if ( ! $listed ) {
                $page_ids[] = (int) $post_id;
                update_option( self::WIDGET_PAGES_OPTION, $page_ids, true );
            }

            delete_transient( 'wpuf_ud_directory_pages' );

            // This request's init ran before the page was listed, so add the page's rule before the flush saves the rules.
            if ( 'publish' === $post->post_status ) {
                $this->add_page_rewrite_rule( $post->post_name );
            }

            flush_rewrite_rules();
        } elseif ( $listed ) {
            update_option( self::WIDGET_PAGES_OPTION, array_values( array_diff( $page_ids, [ (int) $post_id ] ) ), true );
            delete_transient( 'wpuf_ud_directory_pages' );
        }
    }

    /**
     * IDs of the pages that hold the User Directory widget
     *
     * Kept in one autoloaded option, maintained on Elementor save, so a normal
     * request reads it without a query. Pages saved before the option existed are
     * found once by a scan of the published pages.
     *
     * @since WPUF_SINCE
     *
     * @return int[]
     */
    private function get_widget_page_ids() {
        $page_ids = get_option( self::WIDGET_PAGES_OPTION, false );

        if ( is_array( $page_ids ) ) {
            return array_map( 'intval', $page_ids );
        }

        $page_ids  = [];
        $all_pages = get_posts(
            [
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            ]
        );

        foreach ( $all_pages as $page ) {
            $elementor_data = get_post_meta( $page->ID, '_elementor_data', true );

            if ( ! empty( $elementor_data ) && is_string( $elementor_data ) && strpos( $elementor_data, '"widgetType":"wpuf-user-directory"' ) !== false ) {
                $page_ids[] = (int) $page->ID;
            }
        }

        // Saved even when empty, so the scan runs only once.
        update_option( self::WIDGET_PAGES_OPTION, $page_ids, true );

        if ( ! empty( $page_ids ) ) {
            // Rules are added on this init; flush once they are, so they are saved.
            add_action( 'init', 'flush_rewrite_rules', 99 );
        }

        return $page_ids;
    }

    /**
     * Register pretty URL rewrite rules for pages using the User Directory Elementor widget
     *
     * This runs directly on init (priority 5) so the rules are registered regardless of
     * whether the free or Pro module is handling PrettyUrls.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register_elementor_page_rewrite_rules() {
        $page_ids = $this->get_widget_page_ids();

        if ( empty( $page_ids ) ) {
            return;
        }

        $pages = get_posts(
            [
                'post_type'              => 'page',
                'post_status'            => 'publish',
                'post__in'               => $page_ids,
                'posts_per_page'         => -1,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]
        );

        foreach ( $pages as $page ) {
            $this->add_page_rewrite_rule( $page->post_name );
        }
    }

    /**
     * Add the profile pretty URL rewrite rule for one User Directory widget page
     *
     * @since WPUF_SINCE
     *
     * @param string $page_slug The page slug.
     *
     * @return void
     */
    private function add_page_rewrite_rule( $page_slug ) {
        add_rewrite_rule(
            '^' . $page_slug . '/([^/]+)/?$',
            'index.php?pagename=' . $page_slug . '&wpuf_user_profile=$matches[1]',
            'top'
        );
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
        $widget_ids   = $this->get_widget_page_ids();

        foreach ( $all_pages as $page ) {
            // Skip pages already detected via shortcode
            if ( in_array( $page->ID, $existing_ids, true ) ) {
                continue;
            }

            if ( in_array( (int) $page->ID, $widget_ids, true ) ) {
                $directory_pages[] = $page;
            }
        }

        return $directory_pages;
    }
}
