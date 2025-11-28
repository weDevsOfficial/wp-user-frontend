<?php
/**
 * User Directory Free Module
 *
 * Provides limited User Directory functionality in the free version.
 * Full features available in Pro.
 *
 * @package WPUF
 * @subpackage Free
 * @since 4.3.0
 */

namespace WeDevs\Wpuf\Free\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * User Directory Main Class
 *
 * @since 4.3.0
 */
class User_Directory {

    /**
     * Module version
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Maximum directories allowed in free version
     *
     * @var int
     */
    const FREE_DIRECTORY_LIMIT = 1;

    /**
     * Free layout for directory (3x3 round grid)
     *
     * @var string
     */
    const FREE_DIRECTORY_LAYOUT = 'layout-3';

    /**
     * Free layout for profile (centered)
     *
     * @var string
     */
    const FREE_PROFILE_LAYOUT = 'layout-2';

    /**
     * Post type name
     *
     * @var string
     */
    const POST_TYPE = 'wpuf_user_listing';

    /**
     * Singleton instance
     *
     * @var User_Directory|null
     */
    private static $instance = null;

    /**
     * Admin Menu instance
     *
     * @var Admin_Menu|null
     */
    public $admin_menu = null;

    /**
     * Shortcode instance
     *
     * @var Shortcode|null
     */
    public $shortcode = null;

    /**
     * PrettyUrls instance
     *
     * @var PrettyUrls|null
     */
    public $pretty_urls = null;

    /**
     * Get singleton instance
     *
     * @since 4.3.0
     *
     * @return User_Directory
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 4.3.0
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define constants
     *
     * @since 4.3.0
     *
     * @return void
     */
    private function define_constants() {
        if ( ! defined( 'WPUF_UD_FREE_VERSION' ) ) {
            define( 'WPUF_UD_FREE_VERSION', self::VERSION );
        }

        if ( ! defined( 'WPUF_UD_FREE_ROOT' ) ) {
            define( 'WPUF_UD_FREE_ROOT', dirname( __FILE__ ) );
        }

        if ( ! defined( 'WPUF_UD_FREE_VIEWS' ) ) {
            define( 'WPUF_UD_FREE_VIEWS', WPUF_UD_FREE_ROOT . '/views' );
        }

        // Templates constant for template parts (like Pro's WPUF_UD_TEMPLATES)
        if ( ! defined( 'WPUF_UD_FREE_TEMPLATES' ) ) {
            define( 'WPUF_UD_FREE_TEMPLATES', WPUF_UD_FREE_VIEWS );
        }
    }

    /**
     * Include required files
     *
     * @since 4.3.0
     *
     * @return void
     */
    private function includes() {
        require_once WPUF_UD_FREE_ROOT . '/Helpers.php';
        require_once WPUF_UD_FREE_ROOT . '/Post_Type.php';
        require_once WPUF_UD_FREE_ROOT . '/Admin_Menu.php';
        require_once WPUF_UD_FREE_ROOT . '/Shortcode.php';
        require_once WPUF_UD_FREE_ROOT . '/PrettyUrls.php';
        require_once WPUF_UD_FREE_ROOT . '/DirectoryStyles.php';
        require_once WPUF_UD_FREE_ROOT . '/Api/Directory.php';
    }

    /**
     * Initialize hooks
     *
     * @since 4.3.0
     *
     * @return void
     */
    private function init_hooks() {
        // Register post type
        new Post_Type();

        // Admin menu
        if ( is_admin() ) {
            $this->admin_menu = new Admin_Menu();
        }

        // REST API
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // Shortcode
        $this->shortcode = new Shortcode();

        // Pretty URLs
        $this->pretty_urls = new PrettyUrls();

        // Directory Styles (hide page title on profile pages)
        new DirectoryStyles();
    }

    /**
     * Register REST API routes
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function register_rest_routes() {
        $api = new Api\Directory();
        $api->register_routes();
    }

    /**
     * Check if free directory limit has been reached
     *
     * @since 4.3.0
     *
     * @return bool
     */
    public static function has_reached_limit() {
        if ( wpuf_is_pro_active() ) {
            return false;
        }

        $count = self::get_directory_count();

        return $count >= self::FREE_DIRECTORY_LIMIT;
    }

    /**
     * Get the count of directories
     *
     * @since 4.3.0
     *
     * @return int
     */
    public static function get_directory_count() {
        $directories = get_posts( [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        return count( $directories );
    }

    /**
     * Get the first directory (for free version)
     *
     * @since 4.3.0
     *
     * @return \WP_Post|null
     */
    public static function get_first_directory() {
        $directories = get_posts( [
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'ASC',
        ] );

        return ! empty( $directories ) ? $directories[0] : null;
    }

    /**
     * Check if a layout is available in free version
     *
     * @since 4.3.0
     *
     * @param string $layout Layout ID.
     * @param string $type   Layout type (directory or profile).
     *
     * @return bool
     */
    public static function is_layout_free( $layout, $type = 'directory' ) {
        if ( wpuf_is_pro_active() ) {
            return true;
        }

        if ( 'directory' === $type ) {
            return self::FREE_DIRECTORY_LAYOUT === $layout;
        }

        if ( 'profile' === $type ) {
            return self::FREE_PROFILE_LAYOUT === $layout;
        }

        return false;
    }

    /**
     * Get default settings for a new directory
     *
     * @since 4.3.0
     *
     * @return array
     */
    public static function get_default_settings() {
        $defaults = [
            'directory_layout'   => self::FREE_DIRECTORY_LAYOUT,
            'profile_layout'     => self::FREE_PROFILE_LAYOUT,
            'per_page'           => 12,
            'users_per_row'      => 3,
            'orderby'            => 'ID',
            'order'              => 'DESC',
            'roles'              => [],
            'exclude_users'      => '',
            'profile_base'       => 'username',
            'avatar_size'        => '192',
            'profile_size'       => 'medium',
            'searchable_fields'  => [ 'display_name', 'user_login' ],
            'enable_search'      => true,
            'enable_pagination'  => true,
            'profile_tabs'       => [
                'about' => [
                    'label'     => __( 'About', 'wp-user-frontend' ),
                    'is_active' => true,
                ],
            ],
            'about_fields'       => [],
        ];

        /**
         * Filter default directory settings
         *
         * @since 4.3.0
         *
         * @param array $defaults Default settings.
         */
        return apply_filters( 'wpuf_ud_free_default_settings', $defaults );
    }
}
