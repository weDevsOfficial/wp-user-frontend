<?php
/**
 * User Directory Pretty URLs Handler
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Free\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PrettyUrls Class
 *
 * Handles pretty URL rewrite rules for user profile pages.
 *
 * @since 4.3.0
 */
class PrettyUrls {

    /**
     * Constructor
     *
     * @since 4.3.0
     */
    public function __construct() {
        // Don't register if Pro module is active
        if ( $this->is_pro_module_active() ) {
            return;
        }

        add_action( 'init', [ $this, 'add_rewrite_rules' ], 5 );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'save_post', [ $this, 'maybe_flush_rules' ] );
    }

    /**
     * Add rewrite rules for user profile pages
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function add_rewrite_rules() {
        // Get pages with directory shortcodes
        $pages = $this->get_directory_pages();

        foreach ( $pages as $page ) {
            $page_slug = $page->post_name;

            // Add rewrite rule matching Pro: /page-slug/username/ (no /user/ segment)
            add_rewrite_rule(
                '^' . preg_quote( $page_slug, '/' ) . '/([^/]+)/?$',
                'index.php?pagename=' . $page_slug . '&wpuf_user_profile=$matches[1]',
                'top'
            );
        }
    }

    /**
     * Add query vars
     *
     * @since 4.3.0
     *
     * @param array $vars Query vars.
     *
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'wpuf_user_profile';

        return $vars;
    }

    /**
     * Get pages containing directory shortcodes
     *
     * @since 4.3.0
     *
     * @return array Array of WP_Post objects.
     */
    private function get_directory_pages() {
        $pages = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ] );

        $directory_pages = [];

        foreach ( $pages as $page ) {
            if ( has_shortcode( $page->post_content, 'wpuf_user_listing' ) ||
                 has_shortcode( $page->post_content, 'wpuf_user_listing_id' ) ) {
                $directory_pages[] = $page;
            }
        }

        return $directory_pages;
    }

    /**
     * Flush rewrite rules when a page with directory shortcode is saved
     *
     * @since 4.3.0
     *
     * @param int $post_id Post ID.
     *
     * @return void
     */
    public function maybe_flush_rules( $post_id ) {
        // Skip auto-saves
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $post = get_post( $post_id );

        if ( ! $post || 'page' !== $post->post_type ) {
            return;
        }

        // Check if page has directory shortcode
        if ( has_shortcode( $post->post_content, 'wpuf_user_listing' ) ||
             has_shortcode( $post->post_content, 'wpuf_user_listing_id' ) ) {
            flush_rewrite_rules();
        }
    }

    /**
     * Check if Pro module is active
     *
     * @since 4.3.0
     *
     * @return bool
     */
    private function is_pro_module_active() {
        if ( ! wpuf_is_pro_active() ) {
            return false;
        }

        return class_exists( 'WPUF_User_Listing' );
    }
}
