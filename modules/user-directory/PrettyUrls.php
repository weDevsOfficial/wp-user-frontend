<?php
/**
 * User Directory Pretty URLs Handler
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Modules\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PrettyUrls Class
 *
 * Handles pretty URL rewrite rules for user profile pages.
 * Provides hooks for Pro version to extend functionality.
 *
 * @since WPUF_SINCE
 */
class PrettyUrls {

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        /**
         * Filter to allow Pro to take over pretty URL registration
         *
         * When Pro is active, it can return true to prevent Free from registering its rewrite rules.
         *
         * @since WPUF_SINCE
         *
         * @param bool $skip_pretty_urls Whether to skip Free pretty URL registration. Default false.
         */
        if ( apply_filters( 'wpuf_ud_skip_free_pretty_urls', false ) ) {
            return;
        }

        add_action( 'init', [ $this, 'add_rewrite_rules' ], 5 );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'save_post', [ $this, 'maybe_flush_rules' ] );

        /**
         * Action fired after User Directory pretty URLs are initialized
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_ud_pretty_urls_initialized' );
    }

    /**
     * Add rewrite rules for user profile pages
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function add_rewrite_rules() {
        // Check if a deferred flush was requested
        if ( get_option( 'wpuf_ud_flush_rewrite_rules' ) ) {
            delete_option( 'wpuf_ud_flush_rewrite_rules' );
            flush_rewrite_rules();
        }

        // Get pages with directory shortcodes
        $pages = $this->get_directory_pages();

        foreach ( $pages as $page ) {
            $page_slug = $page->post_name;

            /**
             * Filter the rewrite rule pattern for a directory page
             *
             * @since WPUF_SINCE
             *
             * @param string   $pattern   The regex pattern for the rewrite rule.
             * @param \WP_Post $page      The page object.
             * @param string   $page_slug The page slug.
             */
            $pattern = apply_filters(
                'wpuf_ud_rewrite_pattern',
                '^' . preg_quote( $page_slug, '/' ) . '/([^/]+)/?$',
                $page,
                $page_slug
            );

            /**
             * Filter the rewrite rule redirect for a directory page
             *
             * @since WPUF_SINCE
             *
             * @param string   $redirect  The redirect query string.
             * @param \WP_Post $page      The page object.
             * @param string   $page_slug The page slug.
             */
            $redirect = apply_filters(
                'wpuf_ud_rewrite_redirect',
                'index.php?pagename=' . $page_slug . '&wpuf_user_profile=$matches[1]',
                $page,
                $page_slug
            );

            // Add rewrite rule matching Pro: /page-slug/username/ (no /user/ segment)
            add_rewrite_rule( $pattern, $redirect, 'top' );
        }

        /**
         * Action fired after User Directory rewrite rules are added
         *
         * @since WPUF_SINCE
         *
         * @param array $pages The directory pages.
         */
        do_action( 'wpuf_ud_rewrite_rules_added', $pages );
    }

    /**
     * Add query vars
     *
     * @since WPUF_SINCE
     *
     * @param array $vars Query vars.
     *
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'wpuf_user_profile';

        /**
         * Filter query vars for User Directory
         *
         * @since WPUF_SINCE
         *
         * @param array $vars The query vars array.
         */
        return apply_filters( 'wpuf_ud_query_vars', $vars );
    }

    /**
     * Get pages containing directory shortcodes
     *
     * @since WPUF_SINCE
     *
     * @return array Array of WP_Post objects.
     */
    private function get_directory_pages() {
        $cache_key = 'wpuf_ud_directory_pages';
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

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

        /**
         * Filter the directory pages for rewrite rules
         *
         * @since WPUF_SINCE
         *
         * @param array $directory_pages The directory pages.
         * @param array $pages           All published pages.
         */
        $directory_pages = apply_filters( 'wpuf_ud_directory_pages', $directory_pages, $pages );

        set_transient( $cache_key, $directory_pages, DAY_IN_SECONDS );

        return $directory_pages;
    }

    /**
     * Flush rewrite rules when a page with directory shortcode is saved
     *
     * @since WPUF_SINCE
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
            // Invalidate the directory pages cache
            delete_transient( 'wpuf_ud_directory_pages' );

            // Defer flush to next init instead of flushing on every save
            if ( ! get_option( 'wpuf_ud_flush_rewrite_rules' ) ) {
                update_option( 'wpuf_ud_flush_rewrite_rules', true );
            }
        }
    }
}
