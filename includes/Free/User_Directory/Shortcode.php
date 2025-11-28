<?php
/**
 * User Directory Shortcode Handler
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
 * Shortcode Class
 *
 * Handles the [wpuf_user_listing] shortcode rendering.
 *
 * @since 4.3.0
 */
class Shortcode {

    /**
     * Constructor
     *
     * @since 4.3.0
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register hooks
     *
     * @since 4.3.0
     *
     * @return void
     */
    private function register_hooks() {
        // Only register if Pro module is not active
        if ( $this->is_pro_module_active() ) {
            return;
        }

        add_shortcode( 'wpuf_user_listing', [ $this, 'render_shortcode' ] );
        add_shortcode( 'wpuf_user_listing_id', [ $this, 'render_shortcode_with_id' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    /**
     * Register frontend assets
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function register_assets() {
        // Use the built Tailwind CSS file for frontend
        wp_register_style(
            'wpuf-user-directory-frontend',
            WPUF_ASSET_URI . '/css/wpuf-user-directory-free.css',
            [],
            WPUF_VERSION
        );

        wp_register_script(
            'wpuf-user-directory-frontend',
            WPUF_ASSET_URI . '/js/wpuf-user-directory-frontend.js',
            [ 'jquery' ],
            WPUF_VERSION,
            true
        );

        // Register AJAX search script
        wp_register_script(
            'wpuf-ud-search-shortcode',
            WPUF_ASSET_URI . '/js/ud-search-shortcode.js',
            [],
            WPUF_VERSION,
            true
        );
    }

    /**
     * Render shortcode output
     *
     * @since 4.3.0
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string HTML output.
     */
    public function render_shortcode( $atts ) {
        // Parse attributes
        $atts = shortcode_atts(
            [
                'id' => '',
            ],
            $atts,
            'wpuf_user_listing'
        );

        // If ID provided, use stored settings
        if ( ! empty( $atts['id'] ) ) {
            return $this->render_from_stored_settings( $atts['id'] );
        }

        // Try to get first available directory
        $directory = User_Directory::get_first_directory();

        if ( $directory ) {
            return $this->render_from_stored_settings( $directory->ID );
        }

        // No directory found
        return $this->render_no_directory_message();
    }

    /**
     * Render shortcode with ID as value
     *
     * @since 4.3.0
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string HTML output.
     */
    public function render_shortcode_with_id( $atts ) {
        if ( is_array( $atts ) && ! empty( $atts ) ) {
            $directory_id = reset( $atts );

            return $this->render_from_stored_settings( $directory_id );
        }

        return '';
    }

    /**
     * Render from stored directory settings
     *
     * @since 4.3.0
     *
     * @param int $directory_id Directory post ID.
     *
     * @return string HTML output.
     */
    private function render_from_stored_settings( $directory_id ) {
        $directory_id = absint( $directory_id );

        if ( ! $directory_id ) {
            return '';
        }

        // Get directory post
        $post = get_post( $directory_id );

        if ( ! $post || User_Directory::POST_TYPE !== $post->post_type ) {
            return '';
        }

        // Parse settings
        $settings = [];

        if ( ! empty( $post->post_content ) ) {
            $settings = json_decode( $post->post_content, true ) ?: [];
        }

        // Merge with defaults
        $settings = wp_parse_args( $settings, User_Directory::get_default_settings() );

        // Enqueue assets
        wp_enqueue_style( 'wpuf-user-directory-frontend' );
        wp_enqueue_script( 'wpuf-user-directory-frontend' );
        wp_enqueue_script( 'wpuf-ud-search-shortcode' );

        // Localize script with REST API data
        wp_localize_script(
            'wpuf-ud-search-shortcode',
            'wpufUserDirectorySearch',
            [
                'restUrl' => rest_url( 'wpuf/v1/user_directory/search' ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ]
        );

        // Check if viewing a profile
        $profile_user = $this->get_profile_user( $settings );

        if ( $profile_user ) {
            return $this->render_profile( $profile_user, $settings, $directory_id );
        }

        // Render directory list
        return $this->render_directory( $settings, $directory_id );
    }

    /**
     * Get profile user from URL
     *
     * @since 4.3.0
     *
     * @param array $settings Directory settings.
     *
     * @return \WP_User|null
     */
    private function get_profile_user( $settings ) {
        // Check pretty URL
        $profile_slug = get_query_var( 'wpuf_user_profile' );

        if ( $profile_slug ) {
            $profile_base = $settings['profile_base'] ?? 'username';

            if ( 'user_id' === $profile_base ) {
                return get_user_by( 'id', absint( $profile_slug ) );
            }

            return get_user_by( 'login', sanitize_user( $profile_slug ) );
        }

        // Check query parameter
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpuf_user'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $user_id = absint( $_GET['wpuf_user'] );

            return get_user_by( 'id', $user_id );
        }

        return null;
    }

    /**
     * Render user directory list
     *
     * @since 4.3.0
     *
     * @param array $settings     Directory settings.
     * @param int   $directory_id Directory post ID.
     *
     * @return string HTML output.
     */
    private function render_directory( $settings, $directory_id ) {
        // Get current page - check WordPress query var first (for /page/X/ URLs), then legacy udpage param
        $paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
        if ( ! $paged || 1 === $paged ) {
            $paged = get_query_var( 'page' ) ? absint( get_query_var( 'page' ) ) : 1;
        }
        // Fallback to legacy udpage param for backwards compatibility
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! $paged || 1 === $paged ) {
            $paged = isset( $_GET['udpage'] ) ? absint( $_GET['udpage'] ) : 1;
        }
        $paged = max( 1, $paged ); // Ensure paged is at least 1

        // Get search term
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $search = isset( $_GET['udsearch'] ) ? sanitize_text_field( wp_unslash( $_GET['udsearch'] ) ) : '';

        // Get orderby from DB settings first, then allow URL override
        $db_orderby = ! empty( $settings['orderby'] ) ? sanitize_text_field( $settings['orderby'] ) : 'ID';
        $db_order   = ! empty( $settings['order'] ) ? strtoupper( sanitize_text_field( $settings['order'] ) ) : 'DESC';

        // Allow URL params to override DB settings
        $allowed_orderby = [ 'ID', 'id', 'user_login', 'login', 'user_nicename', 'nicename', 'user_email', 'email', 'display_name', 'user_registered', 'registered' ];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $orderby_param = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
        $orderby = ! empty( $orderby_param ) && in_array( $orderby_param, $allowed_orderby, true ) ? $orderby_param : $db_orderby;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $order_param = isset( $_GET['order'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) : '';
        $order = ! empty( $order_param ) && in_array( $order_param, [ 'ASC', 'DESC' ], true ) ? $order_param : $db_order;

        // Build user query args - check users_per_page first (admin setting), then per_page
        $per_page = absint( $settings['users_per_page'] ?? ( $settings['per_page'] ?? 12 ) );
        $offset   = ( $paged - 1 ) * $per_page;

        $args = [
            'number'  => $per_page,
            'offset'  => $offset,
            'orderby' => $orderby,
            'order'   => $order,
        ];

        // Roles filter
        if ( ! empty( $settings['roles'] ) && is_array( $settings['roles'] ) ) {
            $args['role__in'] = array_map( 'sanitize_text_field', $settings['roles'] );
        }

        // Exclude users
        if ( ! empty( $settings['exclude_users'] ) ) {
            $exclude_ids = array_map( 'absint', explode( ',', $settings['exclude_users'] ) );
            $args['exclude'] = $exclude_ids;
        }

        // Search
        if ( $search ) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = [ 'user_login', 'user_nicename', 'display_name', 'user_email' ];
        }

        /**
         * Filter user query args
         *
         * @since 4.3.0
         *
         * @param array $args         User query args.
         * @param array $settings     Directory settings.
         * @param int   $directory_id Directory post ID.
         */
        $args = apply_filters( 'wpuf_ud_free_user_query_args', $args, $settings, $directory_id );

        // Execute query
        $user_query = new \WP_User_Query( $args );
        $users      = $user_query->get_results();
        $total      = $user_query->get_total();
        $max_pages  = (int) ceil( $total / $per_page );

        // Build block ID for data attributes (use shortcode_ prefix to match Pro pattern)
        $block_id = 'shortcode_' . $directory_id;
        $page_id  = get_the_ID();

        // Build pagination data
        $pagination = [
            'total_pages'  => $max_pages,
            'current_page' => $paged,
            'per_page'     => $per_page,
            'total_items'  => $total,
        ];

        // Build all_data array for template compatibility with Pro
        $all_data = [
            'id'                      => $directory_id,
            'directory_id'            => $directory_id,
            'orderby'                 => $orderby,
            'order'                   => $order,
            'max_item'                => $per_page,
            'profile_permalink'       => $settings['profile_base'] ?? 'username',
            'enable_search'           => ! empty( $settings['enable_search'] ),
            'enable_frontend_sorting' => true, // Always enable sorting in Free
            'search_placeholder'      => __( 'Search users...', 'wp-user-frontend' ),
        ];

        // Build template data
        $template_data = [
            'users'                   => $users,
            'total'                   => $total,
            'paged'                   => $paged,
            'max_pages'               => $max_pages,
            'per_page'                => $per_page,
            'search'                  => $search,
            'orderby'                 => $orderby,
            'order'                   => $order,
            'settings'                => $settings,
            'directory_id'            => $directory_id,
            'users_per_row'           => absint( $settings['users_per_row'] ?? 3 ),
            'avatar_size'             => absint( $settings['avatar_size'] ?? 192 ),
            // Pro template compatibility
            'block_id'                => $block_id,
            'page_id'                 => $page_id,
            'directory_layout'        => 'layout-3',
            'enable_search'           => ! empty( $settings['enable_search'] ),
            'enable_frontend_sorting' => true,
            'all_data'                => $all_data,
            'pagination'              => $pagination,
        ];

        /**
         * Filter template data
         *
         * @since 4.3.0
         *
         * @param array $template_data Template data.
         * @param array $settings      Directory settings.
         * @param int   $directory_id  Directory post ID.
         */
        $template_data = apply_filters( 'wpuf_ud_free_template_data', $template_data, $settings, $directory_id );

        // Load template
        ob_start();
        $this->load_template( 'directory/layout-3', $template_data );

        return ob_get_clean();
    }

    /**
     * Render user profile
     *
     * @since 4.3.0
     *
     * @param \WP_User $user         User object.
     * @param array    $settings     Directory settings.
     * @param int      $directory_id Directory post ID.
     *
     * @return string HTML output.
     */
    private function render_profile( $user, $settings, $directory_id ) {
        // Get profile size from settings (Free uses profile_size)
        $profile_size = $settings['profile_size'] ?? 'thumbnail';

        // Build profile data
        $profile_data = [
            'user'            => $user,
            'settings'        => $settings,
            'directory_id'    => $directory_id,
            'avatar_size'     => absint( $settings['avatar_size'] ?? 192 ),
            'back_url'        => $this->get_directory_url(),
            'profile_size'    => $profile_size,
        ];

        /**
         * Filter profile data
         *
         * @since 4.3.0
         *
         * @param array    $profile_data Profile data.
         * @param \WP_User $user         User object.
         * @param array    $settings     Directory settings.
         * @param int      $directory_id Directory post ID.
         */
        $profile_data = apply_filters( 'wpuf_ud_free_profile_data', $profile_data, $user, $settings, $directory_id );

        // Add template_data key for Pro template compatibility
        $profile_data['template_data'] = $profile_data;

        // Load template
        ob_start();
        $this->load_template( 'profile/layout-2', $profile_data );

        return ob_get_clean();
    }

    /**
     * Load template file
     *
     * @since 4.3.0
     *
     * @param string $template Template name.
     * @param array  $data     Template data.
     *
     * @return void
     */
    private function load_template( $template, $data ) {
        // Extract data for template use
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract( $data );

        $template_file = WPUF_UD_FREE_VIEWS . '/' . $template . '.php';

        /**
         * Filter template file path
         *
         * @since 4.3.0
         *
         * @param string $template_file Template file path.
         * @param string $template      Template name.
         * @param array  $data          Template data.
         */
        $template_file = apply_filters( 'wpuf_ud_free_template_path', $template_file, $template, $data );

        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
    }

    /**
     * Get current page number (matching Pro logic)
     *
     * @since 4.3.0
     *
     * @return int
     */
    private function get_current_page() {
        $current_page = 1;

        // Support both ?page=2 and /page/2/
        if ( get_query_var( 'paged' ) ) {
            $current_page = (int) get_query_var( 'paged' );
        } elseif ( ! empty( $_GET['page'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $current_page = (int) $_GET['page'];
        }

        return max( 1, $current_page );
    }

    /**
     * Get directory URL (current page without profile params)
     *
     * @since 4.3.0
     *
     * @return string URL.
     */
    private function get_directory_url() {
        $url = remove_query_arg( [ 'wpuf_user', 'wpuf_user_profile' ] );

        return esc_url( $url );
    }

    /**
     * Generate profile URL for a user
     *
     * @since 4.3.0
     *
     * @param \WP_User $user     User object.
     * @param array    $settings Directory settings.
     *
     * @return string Profile URL.
     */
    public function get_profile_url( $user, $settings = [] ) {
        $profile_base = $settings['profile_base'] ?? 'username';
        $current_url  = get_permalink();

        if ( 'user_id' === $profile_base ) {
            $slug = $user->ID;
        } else {
            $slug = $user->user_login;
        }

        // Try pretty URL first
        $pretty_url = trailingslashit( $current_url ) . 'user/' . $slug;

        // Check if pretty URLs are enabled
        if ( get_option( 'permalink_structure' ) ) {
            return esc_url( $pretty_url );
        }

        // Fallback to query parameter
        return esc_url( add_query_arg( 'wpuf_user', $user->ID, $current_url ) );
    }

    /**
     * Render no directory message
     *
     * @since 4.3.0
     *
     * @return string HTML output.
     */
    private function render_no_directory_message() {
        $message = __( 'No user directory has been configured yet.', 'wp-user-frontend' );

        if ( current_user_can( 'manage_options' ) ) {
            $admin_url = admin_url( 'admin.php?page=wpuf_userlisting' );
            $message  .= ' <a href="' . esc_url( $admin_url ) . '">' . __( 'Create one now', 'wp-user-frontend' ) . '</a>';
        }

        return '<div class="wpuf-ud-notice">' . wp_kses_post( $message ) . '</div>';
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
