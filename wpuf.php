<?php
/*
Plugin Name: WP User Frontend
Plugin URI: https://wordpress.org/plugins/wp-user-frontend/
Description: Create, edit, delete, manages your post, pages or custom post types from frontend. Create registration forms, frontend profile and more...
Author: weDevs
Version: 4.3.13
Author URI: https://wedevs.com/?utm_source=WPUF_Author_URI
Requires at least: 6.6
Requires PHP: 7.4
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-user-frontend
Domain Path: /languages
*/

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';

// Tracked separately: another plugin (WPUF Pro ships its own copy of
// wedevs/wp-utils) can satisfy the trait below through its autoloader, which
// would let this file sail past the guard and then fatal on the first class of
// its own that nothing can autoload.
$wpuf_autoload_loaded = file_exists( $autoload );

if ( $wpuf_autoload_loaded ) {
    require_once $autoload;
}

define( 'WPUF_VERSION', '4.3.13' );
// Platform API version (container, REST manager, React admin runtime); Pro checks it (D10).
define( 'WPUF_PLATFORM_VERSION', '1.0' );
define( 'WPUF_FILE', __FILE__ );
define( 'WPUF_ROOT', __DIR__ );
define( 'WPUF_ROOT_URI', plugins_url( '', __FILE__ ) );
define( 'WPUF_ASSET_URI', WPUF_ROOT_URI . '/assets' );
define( 'WPUF_INCLUDES', WPUF_ROOT . '/includes' );

use WeDevs\WpUtils\SingletonTrait;

// vendor/ (the Composer dependencies, including wedevs/wp-utils which provides
// the SingletonTrait used below) is intentionally not committed to the git
// repository. A checkout that has not run `composer install` would otherwise
// fatal on the missing trait when this class is declared, taking the whole
// site down with a white screen. Fail soft with an admin notice instead so a
// fresh clone is recoverable.
if ( ! $wpuf_autoload_loaded || ! trait_exists( SingletonTrait::class ) ) {
    add_action(
        'admin_notices',
        function () {
            $message = sprintf(
                /* translators: %s: the composer install command, shown as code */
                esc_html__( 'WP User Frontend could not load its dependencies. Please run %s inside the plugin directory.', 'wp-user-frontend' ),
                '<code>composer install</code>'
            );

            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                wp_kses( $message, [ 'code' => [] ] )
            );
        }
    );

    return;
}

/**
 * Main bootstrap class for WP User Frontend
 */
final class WP_User_Frontend {
    use SingletonTrait;

    /**
     * Form field value seperator
     *
     * @var string
     */
    public static $field_separator = '| ';

    /**
     * Pro plugin checkup
     *
     * @var bool
     */
    private $is_pro = false;

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.6';

    /**
     * Holds various class instances
     *
     * @since 4.0.9
     *
     * @var array
     */
    public $container = [];

    /**
     * Platform service container (task 2.1), next to the legacy `$container`
     * array whose keys stay as they are.
     *
     * @since WPUF_SINCE
     *
     * @var WeDevs\Wpuf\Platform\Container|null
     */
    private $platform = null;

    /**
     * Platform bootstrap.
     *
     * @since WPUF_SINCE
     *
     * @var WeDevs\Wpuf\Platform\Bootstrap|null
     */
    private $platform_bootstrap = null;

    /**
     * Fire up the plugin
     */
    public function __construct() {
        if ( ! $this->is_supported_php() ) {
            add_action( 'admin_notices', [ $this, 'php_version_notice' ] );

            return;
        }

        register_activation_hook( __FILE__, [ $this, 'install' ] );
        register_deactivation_hook( __FILE__, [ $this, 'uninstall' ] );

        $this->includes();
        $this->init_hooks();

        do_action( 'wpuf_loaded' );
    }

    /**
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function is_supported_php( $min_php = null ) {
        $min_php = $min_php ? $min_php : $this->min_php;

        if ( version_compare( PHP_VERSION, $min_php, '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Show notice about PHP version
     *
     * @return void
     */
    public function php_version_notice() {
        if ( $this->is_supported_php() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $error = __( 'Your installed PHP Version is: ', 'wp-user-frontend' ) . PHP_VERSION . '. ';
        $error .= __( 'The <strong>WP User Frontend</strong> plugin requires PHP version <strong>', 'wp-user-frontend' ) . $this->min_php . __( '</strong> or greater.', 'wp-user-frontend' ); ?>
        <div class="error">
            <p><?php printf( esc_html( $error ) ); ?></p>
        </div>
        <?php
    }

    /**
     * Initialize the hooks
     *
     * @since 2.5.4
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'plugins_loaded', [ $this, 'init_insights' ], 8 );
        add_action( 'plugins_loaded', [ $this, 'wpuf_loader' ] );
        add_action( 'plugins_loaded', [ $this, 'process_wpuf_pro_version' ] );
        add_action( 'plugins_loaded', [ $this, 'plugin_upgrades' ] );
        add_action( 'plugins_loaded', [ $this, 'instantiate' ] );

        add_action( 'init', [ $this, 'load_textdomain' ] );

        // do plugin upgrades
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

        add_action( 'widgets_init', [ $this, 'register_widgets' ] );
    }

    public function init_insights() {
        // Insight class instantiate
        $this->legacy( 'tracker' );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        require_once __DIR__ . '/wpuf-functions.php';

        // add reCaptcha library if not found
        if ( ! function_exists( 'recaptcha_get_html' ) ) {
            require_once __DIR__ . '/Lib/recaptchalib.php';
            require_once __DIR__ . '/Lib/invisible_recaptcha.php';
        }

        // AI Form Builder includes
        require_once __DIR__ . '/includes/AI_Manager.php';

        // Gateway helper functions
        require_once __DIR__ . '/Lib/Gateway/gateway-functions.php';
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate() {
        // Legacy services come from the platform container (one instance for
        // wpuf()->key and the container), built eagerly in this order because
        // their constructors add hooks.
        foreach ( [ 'assets', 'subscription', 'fields', 'customize' ] as $key ) {
            $this->legacy( $key );
        }

        // Initialize legacy gateway classes for backward compatibility
        $this->legacy( 'bank' );
        $this->legacy( 'paypal' );

        // Initialize new gateway manager inside init hook for translation issue
        add_action( 'init', [ $this, 'init_gateway_manager' ] );

        foreach ( [ 'api', 'integrations', 'ai_manager', 'post_form_block' ] as $key ) {
            $this->legacy( $key );
        }

        if ( is_admin() ) {
            foreach ( [ 'admin', 'setup_wizard', 'pro_upgrades', 'privacy' ] as $key ) {
                $this->legacy( $key );
            }

            // Load Frontend when in Elementor editor or Elementor AJAX so shortcodes
            // like wpuf_form and wpuf_account are registered and do_shortcode() works.
            // Without this, is_admin() is true and Frontend is skipped, so do_shortcode()
            // returns the raw shortcode. Covers: (1) editor page ?action=elementor,
            // (2) render_widget AJAX when changing "Select Form" (action=elementor_ajax).
            $get_action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
            $request_act  = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
            $is_elementor = ( $get_action === 'elementor' )
                || ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) && $request_act === 'elementor_ajax' );

            if ( $is_elementor ) {
                $this->legacy( 'frontend' );
            }
        } else {
            $this->legacy( 'frontend' );
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            // Initialize the ajax class inside init hook for translation issue
            add_action( 'init', [ $this, 'init_ajax' ] );
        }

        // Platform services boot after every legacy key is filled, so they can
        // rely on wpuf()->x; fires `wpuf_platform_loaded` once.
        $this->platform_bootstrap()->boot();
    }

    /**
     * The platform service container.
     *
     * @since WPUF_SINCE
     *
     * @return WeDevs\Wpuf\Platform\Container
     */
    public function platform() {
        if ( null === $this->platform ) {
            $this->platform = new WeDevs\Wpuf\Platform\Container();
        }

        return $this->platform;
    }

    /**
     * The platform bootstrap (Pro registers its providers through it on
     * `wpuf_platform_loaded`).
     *
     * @since WPUF_SINCE
     *
     * @return WeDevs\Wpuf\Platform\Bootstrap
     */
    public function platform_bootstrap() {
        if ( null === $this->platform_bootstrap ) {
            $this->platform_bootstrap = new WeDevs\Wpuf\Platform\Bootstrap( $this->platform() );
        }

        return $this->platform_bootstrap;
    }

    /**
     * Build a legacy service from the platform container and keep it under its
     * legacy key (`wpuf()->key`): one instance for both.
     *
     * @since WPUF_SINCE
     *
     * @param string $key Legacy key (see Platform\Providers\LegacyServiceProvider::SERVICES)
     *
     * @return object
     */
    private function legacy( $key ) {
        $this->platform_bootstrap()->register();

        $this->container[ $key ] = $this->platform()->get( WeDevs\Wpuf\Platform\Providers\LegacyServiceProvider::class_of( $key ) );

        return $this->container[ $key ];
    }

    /**
     * The services a classic submission gets from the admin layer (admin-ajax is an admin
     * request), for the React form routes, which run outside wp-admin: the post form
     * templates' after-insert / after-update hooks, then `wpuf_frontend_submission_hooks`
     * for add-ons (Pro builds its user approval, notifications, content filter and
     * registration templates on it). Once per request; nothing to do in wp-admin.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function boot_submission_hooks() {
        if ( is_admin() || did_action( 'wpuf_frontend_submission_hooks' ) ) {
            return;
        }

        $this->platform_bootstrap()->register();
        $this->platform()->get( WeDevs\Wpuf\Platform\Providers\AdminServiceProvider::class_of( 'form_template' ) );

        /**
         * Fires once before a React form route submits, so code that hooks the
         * submission from the admin layer can build itself, as on admin-ajax.
         *
         * @since WPUF_SINCE
         */
        do_action( 'wpuf_frontend_submission_hooks' );
    }

    /**
     * Initialize the ajax class
     *
     * @since 4.1.4
     *
     * @return void
     */
    public function init_ajax() {
        $this->legacy( 'ajax' );
    }

    /**
     * Initialize the gateway manager
     *
     * @since WPUF_PRO_SINCE
     *
     * @return void
     */
    public function init_gateway_manager() {
        $this->legacy( 'gateway_manager' );
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    public function install() {
        $installer = new WeDevs\Wpuf\Installer();
        $installer->install();
    }

    /**
     * Do plugin upgrades
     *
     * @since 2.2
     *
     * @return void
     */
    public function plugin_upgrades() {
        if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $this->legacy( 'upgrades' );
    }

    /**
     * Check whether the version of wpuf pro is prior to the code restructure
     *
     * @since WPUF_FREE
     *
     * @return void
     */
    public function process_wpuf_pro_version() {
        // check whether the version of wpuf pro is prior to the code restructure
        if ( defined( 'WPUF_PRO_VERSION' ) && version_compare( WPUF_PRO_VERSION, '4', '<' ) ) {
            // deactivate_plugins( WPUF_PRO_FILE );

            add_action( 'admin_notices', [ $this, 'wpuf_upgrade_notice' ] );
        }
    }

    /**
     * Show WordPress error notice if WP User Frontend not found
     *
     * @since 2.4.2
     */
    public function wpuf_upgrade_notice() {
        ?>
        <div class="notice error" id="wpuf-pro-installer-notice" style="padding: 1em; position: relative;">
            <h2><?php esc_html_e( 'Your WP User Frontend Pro is almost ready!', 'wp-user-frontend' ); ?></h2>
            <p>
                <?php
                    echo wp_kses_post(
                        sprintf(
                            /* translators: %1$s: opening link tag to the WP User Frontend plugin page, %2$s: closing link tag */
                            __( 'We\'ve pushed a major update on both <b>WP User Frontend Free</b> and <b>%1$sWP User Frontend Pro%2$s</b> that requires you to use latest version of both. Please update the WPUF pro to the latest version. <br><strong>Please make sure to take a complete backup of your site before updating.</strong>', 'wp-user-frontend' ),
                            '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">',
                            '</a>'
                        )
                    );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Load wpuf Free class if not pro
     *
     * @since 2.5.4
     */
    public function wpuf_loader() {
        $has_pro = class_exists( 'WP_User_Frontend_Pro' );

        if ( $has_pro ) {
            $this->is_pro = true;
        } else {
            $this->legacy( 'free_loader' );

            $this->container['free_loader']->includes();
            $this->container['free_loader']->instantiate();
            $this->container['free_loader']->run_hooks();
        }

        // Remove the what's new option.
        delete_option( 'wpuf_whats_new' );
        delete_option( 'wpufpro_whats_new' );
    }

    /**
     * Manage task on plugin deactivation
     *
     * @return void
     */
    public static function uninstall() {
        wp_clear_scheduled_hook( 'wpuf_remove_expired_post_hook' );
    }

    /**
     * Load the translation file for current language.
     *
     * @since version 0.7
     *
     * @author Tareq Hasan
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'wp-user-frontend', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * The main logging function
     *
     * @uses error_log
     *
     * @param string $type type of the error. e.g: debug, error, info
     * @param string $msg
     */
    public static function log( $type = '', $msg = '' ) {
        $msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg ); // phpcs:ignore
        error_log( $msg, 3, __DIR__ . '/log.txt' );
    }

    /**
     * Returns if the plugin is in PRO version
     *
     * @since 2.3.2
     *
     * @return bool
     */
    public function is_pro() {
        return $this->is_pro;
    }

    /**
     * Plugin action links
     *
     * @param array $links
     *
     * @since  2.3.3
     *
     * @return array
     */
    public function plugin_action_links( $links ) {
        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">' . esc_html( 'Settings' ) . '</a>';
        $links[] = '<a href="https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/" target="_blank"> ' . esc_html( 'Docs' ) . '</a>';

        if ( ! $this->is_pro() ) {
            $links[] = '<a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=installed_plugins" target="_blank" style="color: #64C273;"> ' . esc_html( 'Upgrade to Pro' ) . '</a>';
            $links[] = '<a href="https://wedevs.com/coupons/?utm_source=installed_plugins" target="_blank" style="color: #5368FF;">' . esc_html( 'Check Discounts' ) . '</a>';
        }

        return $links;
    }

    /**
     * Register widgets
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function register_widgets() {
        $this->legacy( 'widgets' );
    }
    public function license_expired() {
        echo '<div class="error">';
        echo '<p>Your <strong>WP User Frontend Pro</strong> License has been expired. Please <a href="https://wedevs.com/account/" target="_blank">renew your license</a>.</p>';
        echo '</div>';
    }

    /**
     * Get the global field seperator for WPUF
     *
     * @since 4.0.0
     *
     * @return string
     */
    public function get_field_seperator() {
        return self::$field_separator;
    }

    /**
     * Answer isset( wpuf()->x ): true for a legacy service or a shared platform service.
     * (Without this, isset() on a magic property is always false.)
     *
     * @since WPUF_SINCE
     *
     * @param string $prop Service key
     *
     * @return bool
     */
    public function __isset( $prop ) {
        return array_key_exists( $prop, $this->container ) || ( null !== $this->platform && $this->platform->is_shared( $prop ) );
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 4.0.9
     *
     * @param string $prop
     *
     * @return object Class Instance
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        // New services live in the platform container; only shared ones are
        // reachable here, anything else (unknown ids included) stays null.
        if ( null !== $this->platform && $this->platform->is_shared( $prop ) ) {
            return $this->platform->get( $prop );
        }

        return null;
    }

    /**
     * Get the DB version key
     *
     * @since 4.0.11
     *
     * @return string
     */
    public function get_db_version_key() {
        return 'wpuf_version';
    }
}

/**
 * Returns the singleton instance
 *
 * @return WP_User_Frontend
 */
// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- wpuf() is the public accessor every add-on calls; it is declared beside the class it returns on purpose.
function wpuf() {
    return WP_User_Frontend::instance();
}

// kickoff
wpuf();
