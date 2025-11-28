<?php
/**
 * Directory Custom Styles
 *
 * @package WPUF\Free\UserDirectory
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Free\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DirectoryStyles class
 *
 * Handles custom styling for user directory pages
 */
class DirectoryStyles {

    /**
     * Constructor
     */
    public function __construct() {
        // Primary hook for filtering titles
        add_filter( 'the_title', [ $this, 'filter_page_title' ], 999, 2 );
        add_filter( 'single_post_title', [ $this, 'filter_single_post_title' ], 999, 2 );

        // Block editor specific filters with high priority
        add_filter( 'render_block', [ $this, 'filter_block_content' ], 1, 2 );
        add_filter( 'render_block_core/post-title', [ $this, 'filter_post_title_block' ], 1, 3 );
        add_filter( 'pre_render_block', [ $this, 'pre_filter_block' ], 1, 2 );

        // Add styles
        add_action( 'wp_head', [ $this, 'add_styles' ] );

        // Add body classes for styling purposes
        add_filter( 'body_class', [ $this, 'add_body_class' ] );

        // Add JavaScript for dynamic block detection
        add_action( 'wp_footer', [ $this, 'add_dynamic_detection_script' ] );
    }

    /**
     * Filter block content to remove title blocks on profile pages
     *
     * @param string $block_content The block content
     * @param array  $block The block data
     * @return string Modified block content
     */
    public function filter_block_content( $block_content, $block ) {
        // Check various title block types
        $title_blocks = [
            'core/post-title',
            'core/query-title',
            'core/heading' // Sometimes titles are rendered as headings
        ];

        // Check if this is a title block
        if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $title_blocks ) ) {
            // Check if on a single profile page
            if ( $this->should_hide_title() ) {
                // Add a marker for JavaScript to detect
                return '<div class="wpuf-profile-title-hidden" style="display:none" data-hidden="true"></div>';
            }
        }

        return $block_content;
    }

    /**
     * Filter specifically for post-title blocks
     *
     * @param string $block_content The block content
     * @param array  $parsed_block The parsed block
     * @param object $block The block instance
     * @return string Modified block content
     */
    public function filter_post_title_block( $block_content, $parsed_block, $block ) {
        if ( $this->should_hide_title() ) {
            return '';
        }
        return $block_content;
    }

    /**
     * Pre-filter blocks before rendering
     *
     * @param string|null $pre_render The pre-rendered content
     * @param array       $block The block being rendered
     * @return string|null
     */
    public function pre_filter_block( $pre_render, $block ) {
        // If already handled, return
        if ( $pre_render !== null ) {
            return $pre_render;
        }

        // Check if it's a title block and we should hide it
        if ( isset( $block['blockName'] ) &&
             ( $block['blockName'] === 'core/post-title' || $block['blockName'] === 'core/query-title' ) &&
             $this->should_hide_title() ) {
            return ''; // Return empty string to prevent rendering
        }

        return $pre_render;
    }

    /**
     * Add styles for directory pages
     */
    public function add_styles() {
        // Check if we're on a directory page
        if ( ! $this->is_directory_page() ) {
            return;
        }

        // Add different styles based on page type
        if ( $this->is_single_profile_page() ) {
            // Add minimal CSS for block themes as fallback
            $this->add_profile_hide_css();
        } else {
            // Add listing page styles
            $this->output_listing_title_styles();
        }
    }

    /**
     * Add minimal CSS to hide titles on profile pages
     */
    private function add_profile_hide_css() {
        ?>
        <style id="wpuf-hide-profile-title">
            /* Hide ONLY PAGE TITLES on profile pages - not all h1 elements */
            body.wpuf-viewing-profile .wp-block-post-title,
            body.wpuf-viewing-profile h1.wp-block-post-title,
            body.wpuf-viewing-profile h2.wp-block-post-title,
            body.wpuf-viewing-profile .entry-title,
            body.wpuf-viewing-profile .page-title,
            body.wpuf-viewing-profile header.entry-header > .entry-title,
            body.wpuf-viewing-profile header.entry-header > h1.entry-title {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                line-height: 0 !important;
                font-size: 0 !important;
            }

            /* For block themes - target specific title block */
            .wpuf-profile-view-active .wp-block-post-title,
            .wpuf-profile-view-active h1.wp-block-post-title,
            .wpuf-profile-view-active h2.wp-block-post-title {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
            }
        </style>
        <?php
    }

    /**
     * Check if current page has directory functionality
     */
    private function is_directory_page() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) ) {
            return false;
        }

        // Check for shortcodes
        $has_shortcode = ( has_shortcode( $post->post_content, 'wpuf_user_listing' ) ||
                          has_shortcode( $post->post_content, 'wpuf_user_listing_id' ) );

        return $has_shortcode;
    }

    /**
     * Output listing page title styles
     */
    private function output_listing_title_styles() {
        ?>
        <style id="wpuf-user-directory-listing-title-styles">
            /* Custom title styles for user directory listing pages (both shortcode and block) */
            body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .wp-block-post-title,
            body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .entry-title {
                font-weight: 400 !important;
                font-size: 60px !important;
                line-height: 60px !important;
                letter-spacing: 0 !important;
                text-align: center !important;
                color: #000000 !important;
                margin: 0 auto !important;
                padding: 20px 20px !important;
            }

            /* Responsive adjustments */
            @media screen and (max-width: 768px) {
                body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .wp-block-post-title,
                body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .entry-title {
                    font-size: 40px !important;
                    line-height: 45px !important;
                    padding: 30px 15px !important;
                }
            }

            @media screen and (max-width: 480px) {
                body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .wp-block-post-title,
                body:not(.wpuf-single-user-profile):not(.wpuf-viewing-profile) .entry-title {
                    font-size: 32px !important;
                    line-height: 36px !important;
                    padding: 20px 10px !important;
                }
            }
        </style>
        <?php
    }


    /**
     * Add dynamic detection script for block-based directory
     */
    public function add_dynamic_detection_script() {
        // Only add on pages with directory shortcodes
        if ( ! $this->is_directory_page() ) {
            return;
        }
        ?>
        <script>
        (function() {
            // Function to detect and handle profile view
            function checkProfileView() {
                const pathname = window.location.pathname;
                const segments = pathname.split('/').filter(s => s);

                // Check if URL indicates a profile view (has user segment after page slug)
                let isProfileView = false;

                // If we have at least 2 segments and last one isn't a WordPress system path
                if (segments.length >= 2) {
                    const lastSegment = segments[segments.length - 1];
                    const systemPaths = ['page', 'feed', 'rss', 'comment-page', 'trackback'];

                    if (lastSegment && !systemPaths.includes(lastSegment)) {
                        // Check if the segment looks like a username or ID
                        // It's a profile if it's not empty and not a number-only pagination
                        isProfileView = lastSegment !== '' && !(lastSegment === 'page' || /^\d+$/.test(lastSegment) && segments[segments.length - 2] === 'page');
                    }
                }

                // Also check for "Back" link which appears on profile views
                const hasBackLink = document.querySelector('a[href]:not([href*="http"])')?.textContent?.includes('Back') ||
                                   document.querySelector('.wp-block-button__link')?.textContent?.includes('Back');

                if (isProfileView || hasBackLink) {
                    document.body.classList.add('wpuf-viewing-profile', 'wpuf-profile-view-active');
                } else {
                    document.body.classList.remove('wpuf-viewing-profile', 'wpuf-profile-view-active');
                }
            }

            // Run immediately
            checkProfileView();

            // Run on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkProfileView);
            }

            // Monitor URL changes
            let lastUrl = location.href;
            new MutationObserver(() => {
                const url = location.href;
                if (url !== lastUrl) {
                    lastUrl = url;
                    setTimeout(checkProfileView, 100); // Small delay for DOM update
                }
            }).observe(document, {subtree: true, childList: true});
        })();
        </script>
        <?php
    }

    /**
     * Check if current page is viewing a single user profile
     *
     * @return bool
     */
    private function is_single_profile_page() {
        // ONLY check for profile if we're already on a directory page
        // This prevents false positives on other single pages

        // PRIMARY METHOD: Check for the pretty URL query var (set by PrettyUrls.php)
        $wpuf_user_profile = get_query_var( 'wpuf_user_profile', false );
        if ( $wpuf_user_profile !== false && ! empty( $wpuf_user_profile ) ) {
            return true;
        }

        // SECONDARY: Check URL parameters that indicate profile view
        $profile_params = [
            'user',       // Primary parameter set by PrettyUrls
            'user_name',
            'user_id',
            'profile_user',
            'wpuf_profile',
            'wpuf_user',
            'username',
            'uid'
        ];

        foreach ( $profile_params as $param ) {
            if ( ! empty( $_GET[$param] ) ) {
                // Additional validation: ensure we're on a directory page
                return $this->is_directory_page();
            }
        }

        // For pretty URLs: Check if current page slug matches and has additional segment
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return false;
        }

        // Must have directory functionality
        if ( ! $this->has_directory_shortcode( $post ) ) {
            return false;
        }

        // Check URL structure
        $current_uri = trim( $_SERVER['REQUEST_URI'], '/' );
        $page_slug = $post->post_name;

        // Pattern: /page-slug/username-or-id/
        if ( preg_match( '/\/' . preg_quote( $page_slug, '/' ) . '\/([^\/]+)\/?$/', '/' . $current_uri, $matches ) ) {
            $potential_user = $matches[1];

            // Exclude WordPress system paths
            $system_paths = ['page', 'feed', 'rss', 'trackback', 'comment-page', 'embed'];
            if ( ! empty( $potential_user ) && ! in_array( $potential_user, $system_paths ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if post has directory shortcode
     */
    private function has_directory_shortcode( $post ) {
        if ( ! is_a( $post, 'WP_Post' ) ) {
            return false;
        }

        // Check for shortcodes
        $has_shortcode = ( has_shortcode( $post->post_content, 'wpuf_user_listing' ) ||
                          has_shortcode( $post->post_content, 'wpuf_user_listing_id' ) );

        return $has_shortcode;
    }

    /**
     * Filter the page title
     *
     * @param string $title The post title
     * @param int    $id    The post ID
     * @return string
     */
    public function filter_page_title( $title, $id = null ) {
        // Only filter if we should hide title and it's the main page title
        if ( $this->should_hide_title() && $this->is_main_page_title( $id ) ) {
            return '';
        }

        return $title;
    }

    /**
     * Check if this is the main page title (not widget titles, menu titles, etc.)
     */
    private function is_main_page_title( $id ) {
        global $post;

        // Must be in the main loop and query
        if ( ! in_the_loop() || ! is_main_query() ) {
            return false;
        }

        // Must be on a singular page
        if ( ! is_singular() ) {
            return false;
        }

        // Check if this is the current page's title
        if ( $id && is_a( $post, 'WP_Post' ) && $post->ID == $id ) {
            return true;
        }

        return false;
    }

    /**
     * Filter single post title
     *
     * @param string $title The single post title
     * @param object $post  The post object
     * @return string
     */
    public function filter_single_post_title( $title, $post = null ) {
        if ( $this->should_hide_title() ) {
            return '';
        }

        return $title;
    }

    /**
     * Determine if title should be hidden
     */
    private function should_hide_title() {
        // Must be on a directory page first
        if ( ! $this->is_directory_page() ) {
            return false;
        }

        // Then check if viewing a single profile
        if ( ! $this->is_single_profile_page() ) {
            return false;
        }

        return true;
    }


    /**
     * Add body class for CSS targeting
     *
     * @param array $classes Body classes
     * @return array
     */
    public function add_body_class( $classes ) {
        // Only add classes if on a directory page viewing a profile
        if ( $this->is_directory_page() && $this->is_single_profile_page() ) {
            $classes[] = 'wpuf-single-user-profile';
            $classes[] = 'wpuf-viewing-profile';
        }

        return $classes;
    }
}
