<?php
/**
 * User Directory Admin Menu
 *
 * @package WPUF
 * @subpackage Modules/User_Directory
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Modules\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Menu Class
 *
 * Handles the User Directories admin menu registration and rendering.
 * Provides hooks for Pro version to extend functionality.
 *
 * @since 4.3.0
 */
class Admin_Menu {

    /**
     * Constructor
     *
     * @since 4.3.0
     */
    public function __construct() {
        // Hook to wpuf_admin_menu_top with priority 15 to position after Registration Forms
        add_action( 'wpuf_admin_menu_top', [ $this, 'add_menu' ], 15 );
    }

    /**
     * Add User Directories submenu
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function add_menu() {
        /**
         * Filter to allow Pro to take over menu registration
         *
         * When Pro is active, it can return true to prevent Free from registering its menu.
         * Pro will then register its own menu with the same slug.
         *
         * @since 4.3.0
         *
         * @param bool $skip_menu Whether to skip Free menu registration. Default false.
         */
        if ( apply_filters( 'wpuf_ud_skip_free_admin_menu', false ) ) {
            return;
        }

        $hook = add_submenu_page(
            'wp-user-frontend',
            __( 'User Directories', 'wp-user-frontend' ),
            __( 'User Directories', 'wp-user-frontend' ),
            'manage_options',
            'wpuf_userlisting',
            [ $this, 'render_page' ]
        );

        add_action( 'load-' . $hook, [ $this, 'enqueue_scripts' ] );

        /**
         * Action fired after User Directory admin menu is registered
         *
         * @since 4.3.0
         *
         * @param string $hook The hook suffix for the menu page.
         */
        do_action( 'wpuf_ud_admin_menu_registered', $hook );
    }

    /**
     * Render the admin page
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function render_page() {
        /**
         * Filter the admin page container ID
         *
         * @since 4.3.0
         *
         * @param string $container_id The container element ID.
         */
        $container_id = apply_filters( 'wpuf_ud_admin_container_id', 'wpuf-ud-free-app' );

        /**
         * Filter the admin page container CSS classes
         *
         * @since 4.3.0
         *
         * @param string $container_class The container element CSS classes.
         */
        $container_class = apply_filters(
            'wpuf_ud_admin_container_class',
            'wpuf-user-directory wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] !wpuf-py-0 wpuf-px-[20px]'
        );
        ?>
        <div id="<?php echo esc_attr( $container_id ); ?>" class="<?php echo esc_attr( $container_class ); ?>">
            <noscript>
                <strong>
                    <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
                </strong>
            </noscript>
            <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
        </div>
        <?php

        if ( function_exists( 'wpuf_load_headway_badge' ) ) {
            wpuf_load_headway_badge();
        }

        /**
         * Action fired after User Directory admin page is rendered
         *
         * @since 4.3.0
         */
        do_action( 'wpuf_ud_admin_page_rendered' );
    }

    /**
     * Enqueue scripts and styles
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        /**
         * Filter to allow Pro to take over script enqueuing
         *
         * When Pro is active, it can return true to prevent Free from enqueuing its scripts.
         * Pro will then enqueue its own scripts.
         *
         * @since 4.3.0
         *
         * @param bool $skip_scripts Whether to skip Free script enqueuing. Default false.
         */
        if ( apply_filters( 'wpuf_ud_skip_free_admin_scripts', false ) ) {
            return;
        }

        // Use the generated asset file for dependencies and version (like Pro does)
        $asset_path = WPUF_ROOT . '/assets/js/wpuf-user-directory-free.asset.php';

        if ( file_exists( $asset_path ) ) {
            $asset = include $asset_path;
        } else {
            // Fallback dependencies
            $asset = [
                'dependencies' => [ 'wp-element', 'wp-i18n', 'wp-components' ],
                'version'      => WPUF_VERSION,
            ];
        }

        /**
         * Filter script handle name
         *
         * @since 4.3.0
         *
         * @param string $handle Script handle name.
         */
        $script_handle = apply_filters( 'wpuf_ud_admin_script_handle', 'wpuf-user-directory-free' );

        // Enqueue React-based script with WordPress dependencies
        wp_enqueue_script(
            $script_handle,
            WPUF_ASSET_URI . '/js/wpuf-user-directory-free.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_enqueue_style(
            'wpuf-user-directory-free',
            WPUF_ASSET_URI . '/css/wpuf-user-directory-free.css',
            [],
            $asset['version']
        );

        /**
         * Filter the localized script variable name
         *
         * @since 4.3.0
         *
         * @param string $var_name JavaScript variable name.
         */
        $localize_var = apply_filters( 'wpuf_ud_admin_localize_var', 'wpuf_ud_free' );

        // Localize script data
        wp_localize_script(
            $script_handle,
            $localize_var,
            $this->get_localized_data()
        );

        /**
         * Action fired after User Directory admin scripts are enqueued
         *
         * @since 4.3.0
         *
         * @param string $script_handle The script handle name.
         */
        do_action( 'wpuf_ud_admin_scripts_enqueued', $script_handle );
    }

    /**
     * Get localized data for JavaScript
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_localized_data() {
        $data = [
            'site_url'             => site_url(),
            'rest_url'             => rest_url(),
            'asset_url'            => WPUF_ASSET_URI,
            'rest_nonce'           => wp_create_nonce( 'wp_rest' ),
            'is_pro_active'        => wpuf_is_pro_active(),
            'upgrade_url'          => $this->get_upgrade_url(),
            'free_directory_limit' => User_Directory::FREE_DIRECTORY_LIMIT,
            'free_directory_layout'=> User_Directory::FREE_DIRECTORY_LAYOUT,
            'free_profile_layout'  => User_Directory::FREE_PROFILE_LAYOUT,
            'roles'                => $this->get_user_roles(),
            'directory_layouts'    => $this->get_directory_layouts(),
            'profile_layouts'      => $this->get_profile_layouts(),
            'avatar_sizes'         => $this->get_avatar_sizes(),
            'profile_sizes'        => $this->get_profile_sizes(),
            'orderby_options'      => $this->get_orderby_options(),
            'profile_tabs'         => $this->get_profile_tabs(),
            'i18n'                 => $this->get_i18n_strings(),
        ];

        /**
         * Filter the localized data for User Directory admin
         *
         * This is the main extension point for Pro to add additional data.
         *
         * @since 4.3.0
         *
         * @param array $data The localized data array.
         */
        return apply_filters( 'wpuf_ud_admin_localized_data', $data );
    }

    /**
     * Get user roles
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_user_roles() {
        global $wp_roles;

        $roles = [];

        if ( ! empty( $wp_roles->roles ) ) {
            foreach ( $wp_roles->roles as $key => $role ) {
                $roles[ $key ] = $role['name'];
            }
        }

        return $roles;
    }

    /**
     * Get directory layouts with free/pro status
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_directory_layouts() {
        $image_base = $this->get_layout_images_url();

        $layouts = [
            'layout-3' => [
                'name'    => __( 'Grid Round', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-3.png',
                'is_free' => true,
            ],
            'layout-1' => [
                'name'    => __( 'Table', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-1.png',
                'is_free' => false,
            ],
            'layout-2' => [
                'name'    => __( 'Grid Square', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-2.png',
                'is_free' => false,
            ],
            'layout-4' => [
                'name'    => __( 'Cards', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-4.png',
                'is_free' => false,
            ],
            'layout-5' => [
                'name'    => __( 'List', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-5.png',
                'is_free' => false,
            ],
            'layout-6' => [
                'name'    => __( 'Compact', 'wp-user-frontend' ),
                'image'   => $image_base . 'directory-layout-6.png',
                'is_free' => false,
            ],
        ];

        /**
         * Filter directory layouts
         *
         * Pro can modify layout availability by changing is_free flags.
         *
         * @since 4.3.0
         *
         * @param array  $layouts    The layouts array.
         * @param string $image_base The base URL for layout images.
         */
        return apply_filters( 'wpuf_ud_directory_layouts', $layouts, $image_base );
    }

    /**
     * Get URL base for layout preview images
     *
     * Tries Pro plugin first, falls back to free plugin assets
     *
     * @since 4.3.0
     *
     * @return string
     */
    private function get_layout_images_url() {
        // Check if Pro module images exist
        if ( defined( 'WPUF_PRO_MODULES' ) ) {
            $pro_images_path = WPUF_PRO_MODULES . '/user-directory/assets/images/';
            if ( file_exists( $pro_images_path . 'directory-layout-1.png' ) ) {
                return plugins_url( 'modules/user-directory/assets/images/', WPUF_PRO_MODULES . '/wpuf-pro.php' );
            }
        }

        // Fall back to free plugin images
        return WPUF_ASSET_URI . '/images/user-directory/';
    }

    /**
     * Get profile layouts with free/pro status
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_profile_layouts() {
        $image_base = $this->get_layout_images_url();

        $layouts = [
            'layout-2' => [
                'name'    => __( 'Centered', 'wp-user-frontend' ),
                'image'   => $image_base . 'profile-layout-2.png',
                'is_free' => true,
            ],
            'layout-1' => [
                'name'    => __( 'Navigator', 'wp-user-frontend' ),
                'image'   => $image_base . 'profile-layout-1.png',
                'is_free' => false,
            ],
            'layout-3' => [
                'name'    => __( 'Spotlight', 'wp-user-frontend' ),
                'image'   => $image_base . 'profile-layout-3.png',
                'is_free' => false,
            ],
        ];

        /**
         * Filter profile layouts
         *
         * Pro can modify layout availability by changing is_free flags.
         *
         * @since 4.3.0
         *
         * @param array  $layouts    The layouts array.
         * @param string $image_base The base URL for layout images.
         */
        return apply_filters( 'wpuf_ud_profile_layouts', $layouts, $image_base );
    }

    /**
     * Get avatar sizes with free/pro status
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_avatar_sizes() {
        $sizes = [
            '192' => [
                'label'   => '192 x 192',
                'is_free' => true,
            ],
            '128' => [
                'label'   => '128 x 128',
                'is_free' => true,
            ],
            '32' => [
                'label'   => '32 x 32',
                'is_free' => false,
            ],
            '48' => [
                'label'   => '48 x 48',
                'is_free' => false,
            ],
            '80' => [
                'label'   => '80 x 80',
                'is_free' => false,
            ],
            '160' => [
                'label'   => '160 x 160',
                'is_free' => false,
            ],
            '256' => [
                'label'   => '256 x 256',
                'is_free' => false,
            ],
        ];

        /**
         * Filter avatar sizes
         *
         * Pro can modify size availability by changing is_free flags.
         *
         * @since 4.3.0
         *
         * @param array $sizes The avatar sizes array.
         */
        return apply_filters( 'wpuf_ud_avatar_sizes', $sizes );
    }

    /**
     * Get profile image sizes with free/pro status
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_profile_sizes() {
        $sizes = [];

        // Get registered image sizes
        $registered_sizes = wp_get_registered_image_subsizes();

        // Free sizes: thumbnail and medium
        $free_sizes = [ 'thumbnail', 'medium' ];

        /**
         * Filter which profile sizes are available in free version
         *
         * @since 4.3.0
         *
         * @param array $free_sizes Array of size names available in free version.
         */
        $free_sizes = apply_filters( 'wpuf_ud_free_profile_sizes', $free_sizes );

        foreach ( $registered_sizes as $name => $size ) {
            $sizes[ $name ] = [
                'label'   => ucfirst( str_replace( '_', ' ', $name ) ) . ' (' . $size['width'] . 'x' . $size['height'] . ')',
                'is_free' => in_array( $name, $free_sizes, true ),
            ];
        }

        /**
         * Filter profile sizes
         *
         * Pro can modify size availability by changing is_free flags.
         *
         * @since 4.3.0
         *
         * @param array $sizes The profile sizes array.
         */
        return apply_filters( 'wpuf_ud_profile_sizes', $sizes );
    }

    /**
     * Get orderby options with free/pro status
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_orderby_options() {
        $options = [
            'ID' => [
                'label'   => __( 'User ID', 'wp-user-frontend' ),
                'is_free' => true,
            ],
            'display_name' => [
                'label'   => __( 'Display Name', 'wp-user-frontend' ),
                'is_free' => false,
            ],
            'user_login' => [
                'label'   => __( 'Username', 'wp-user-frontend' ),
                'is_free' => false,
            ],
            'user_email' => [
                'label'   => __( 'Email', 'wp-user-frontend' ),
                'is_free' => false,
            ],
            'user_registered' => [
                'label'   => __( 'Registration Date', 'wp-user-frontend' ),
                'is_free' => false,
            ],
            'post_count' => [
                'label'   => __( 'Post Count', 'wp-user-frontend' ),
                'is_free' => false,
            ],
        ];

        /**
         * Filter orderby options
         *
         * Pro can modify option availability by changing is_free flags.
         *
         * @since 4.3.0
         *
         * @param array $options The orderby options array.
         */
        return apply_filters( 'wpuf_ud_orderby_options', $options );
    }

    /**
     * Get profile tabs configuration
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_profile_tabs() {
        $tabs = [
            'about' => [
                'label'     => __( 'About', 'wp-user-frontend' ),
                'default'   => 'About',
                'is_active' => true,
                'is_free'   => true,
            ],
            'posts' => [
                'label'     => __( 'Posts', 'wp-user-frontend' ),
                'default'   => 'Posts',
                'is_active' => true,
                'is_free'   => false,
            ],
            'file' => [
                'label'     => __( 'Files', 'wp-user-frontend' ),
                'default'   => 'File/Image',
                'is_active' => true,
                'is_free'   => false,
            ],
            'comments' => [
                'label'     => __( 'Comments', 'wp-user-frontend' ),
                'default'   => 'Comments',
                'is_active' => true,
                'is_free'   => false,
            ],
        ];

        /**
         * Filter profile tabs
         *
         * Pro can add additional tabs or modify availability.
         *
         * @since 4.3.0
         *
         * @param array $tabs The profile tabs array.
         */
        return apply_filters( 'wpuf_ud_profile_tabs', $tabs );
    }

    /**
     * Get i18n strings for JavaScript
     *
     * @since 4.3.0
     *
     * @return array
     */
    private function get_i18n_strings() {
        $strings = [
            'user_directories'    => __( 'User Directories', 'wp-user-frontend' ),
            'new_directory'       => __( 'New Directory', 'wp-user-frontend' ),
            'edit_directory'      => __( 'Edit Directory', 'wp-user-frontend' ),
            'delete_directory'    => __( 'Delete Directory', 'wp-user-frontend' ),
            'search_directories'  => __( 'Search Directories', 'wp-user-frontend' ),
            'no_directories'      => __( 'No directories found', 'wp-user-frontend' ),
            'create_first'        => __( 'Create your first directory', 'wp-user-frontend' ),
            'pro_feature'         => __( 'Pro Feature', 'wp-user-frontend' ),
            'upgrade_to_pro'      => __( 'Upgrade to Pro', 'wp-user-frontend' ),
            'directory_limit'     => __( 'You can only create 1 directory in the free version.', 'wp-user-frontend' ),
            'step_basic'          => __( 'Basic', 'wp-user-frontend' ),
            'step_layout'         => __( 'Directory Layout', 'wp-user-frontend' ),
            'step_profile'        => __( 'Profile Layout', 'wp-user-frontend' ),
            'step_tabs'           => __( 'Profile Tabs', 'wp-user-frontend' ),
            'step_advanced'       => __( 'Advanced', 'wp-user-frontend' ),
            'next'                => __( 'Next', 'wp-user-frontend' ),
            'previous'            => __( 'Previous', 'wp-user-frontend' ),
            'save'                => __( 'Save', 'wp-user-frontend' ),
            'cancel'              => __( 'Cancel', 'wp-user-frontend' ),
            'saving'              => __( 'Saving...', 'wp-user-frontend' ),
            'saved'               => __( 'Saved!', 'wp-user-frontend' ),
            'error_saving'        => __( 'Error saving directory', 'wp-user-frontend' ),
            'confirm_delete'      => __( 'Are you sure you want to delete this directory?', 'wp-user-frontend' ),
            'directory_name'      => __( 'Directory Name', 'wp-user-frontend' ),
            'select_roles'        => __( 'Select Roles', 'wp-user-frontend' ),
            'all_roles'           => __( 'All Roles', 'wp-user-frontend' ),
            'users_per_page'      => __( 'Users Per Page', 'wp-user-frontend' ),
            'users_per_row'       => __( 'Users Per Row', 'wp-user-frontend' ),
            'sort_by'             => __( 'Sort By', 'wp-user-frontend' ),
            'sort_order'          => __( 'Sort Order', 'wp-user-frontend' ),
            'ascending'           => __( 'Ascending', 'wp-user-frontend' ),
            'descending'          => __( 'Descending', 'wp-user-frontend' ),
            'avatar_size'         => __( 'Avatar Size', 'wp-user-frontend' ),
            'profile_image_size'  => __( 'Profile Gallery Image Size', 'wp-user-frontend' ),
            'enable_search'       => __( 'Enable Search', 'wp-user-frontend' ),
            'exclude_users'       => __( 'Exclude Users', 'wp-user-frontend' ),
            'exclude_users_desc'  => __( 'Enter user IDs separated by commas', 'wp-user-frontend' ),
            'shortcode'           => __( 'Shortcode', 'wp-user-frontend' ),
            'copy_shortcode'      => __( 'Copy Shortcode', 'wp-user-frontend' ),
            'copied'              => __( 'Copied!', 'wp-user-frontend' ),
        ];

        /**
         * Filter i18n strings for User Directory admin
         *
         * Pro can add or modify translation strings.
         *
         * @since 4.3.0
         *
         * @param array $strings The i18n strings array.
         */
        return apply_filters( 'wpuf_ud_i18n_strings', $strings );
    }

    /**
     * Get upgrade URL
     *
     * @since 4.3.0
     *
     * @return string
     */
    private function get_upgrade_url() {
        if ( class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' ) ) {
            return \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url();
        }

        return 'https://wedevs.com/wp-user-frontend-pro/pricing/';
    }
}
