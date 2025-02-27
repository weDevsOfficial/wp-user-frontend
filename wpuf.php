<?php
/*
Plugin Name: WP User Frontend
Plugin URI: https://wordpress.org/plugins/wp-user-frontend/
Description: Create, edit, delete, manages your post, pages or custom post types from frontend. Create registration forms, frontend profile and more...
Author: weDevs
Version: 4.0.14
Author URI: https://wedevs.com/?utm_source=WPUF_Author_URI
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

if ( file_exists( $autoload ) ) {
    require_once $autoload;
}

define( 'WPUF_VERSION', '4.0.14' );
define( 'WPUF_FILE', __FILE__ );
define( 'WPUF_ROOT', __DIR__ );
define( 'WPUF_ROOT_URI', plugins_url( '', __FILE__ ) );
define( 'WPUF_ASSET_URI', WPUF_ROOT_URI . '/assets' );
define( 'WPUF_INCLUDES', WPUF_ROOT . '/includes' );

use WeDevs\WpUtils\SingletonTrait;

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
        add_action( 'plugins_loaded', [ $this, 'wpuf_loader' ] );
        add_action( 'plugins_loaded', [ $this, 'process_wpuf_pro_version' ] );
        add_action( 'plugins_loaded', [ $this, 'plugin_upgrades' ] );
        add_action( 'plugins_loaded', [ $this, 'instantiate' ] );

        add_action( 'init', [ $this, 'load_textdomain' ] );

        // do plugin upgrades
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

        add_action( 'widgets_init', [ $this, 'register_widgets' ] );
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        require_once __DIR__ . '/wpuf-functions.php';
        require_once __DIR__ . '/includes/class-frontend-render-form.php';

        // add reCaptcha library if not found
        if ( ! function_exists( 'recaptcha_get_html' ) ) {
            require_once __DIR__ . '/Lib/recaptchalib.php';
            require_once __DIR__ . '/Lib/invisible_recaptcha.php';
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate() {
        $this->container['assets']       = new WeDevs\Wpuf\Assets();
        $this->container['subscription'] = new WeDevs\Wpuf\Admin\Subscription();
        $this->container['fields']       = new WeDevs\Wpuf\Admin\Forms\Field_Manager();
        $this->container['customize']    = new WeDevs\Wpuf\Admin\Customizer_Options();
        $this->container['bank']         = new WeDevs\Wpuf\Lib\Gateway\Bank();
        $this->container['paypal']       = new WeDevs\Wpuf\Lib\Gateway\Paypal();
        $this->container['api']          = new WeDevs\Wpuf\API();
        $this->container['integrations'] = new WeDevs\Wpuf\Integrations();

        if ( is_admin() ) {
            $this->container['admin']        = new WeDevs\Wpuf\Admin();
            $this->container['setup_wizard'] = new WeDevs\Wpuf\Setup_Wizard();
            $this->container['pro_upgrades'] = new WeDevs\Wpuf\Pro_Upgrades();
            $this->container['privacy']      = new WeDevs\Wpuf\WPUF_Privacy();
        } else {
            $this->container['frontend'] = new WeDevs\Wpuf\Frontend();
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $this->container['ajax'] = new WeDevs\Wpuf\Ajax();
        }
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

        $this->container['upgrades'] = new WeDevs\Wpuf\Admin\Upgrades();
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
                /* translators: 1: opening anchor tag, 2: closing anchor tag. */
                echo sprintf( __( 'We\'ve pushed a major update on both <b>WP User Frontend Free</b> and <b>WP User Frontend Pro</b> that requires you to use latest version of both. Please update the WPUF pro to the latest version. <br><strong>Please make sure to take a complete backup of your site before updating.</strong>', 'wp-user-frontend' ), '<a target="_blank" href="https://wordpress.org/plugins/wp-user-frontend/">', '</a>' );
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
            $this->container['free_loader'] = new WeDevs\Wpuf\Free\Free_Loader();
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

        // Insight class instantiate
        $this->container['tracker'] = new WeDevs\Wpuf\Lib\WeDevs_Insights( __FILE__ );
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
        $msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg );
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
        if ( ! $this->is_pro() ) {
            $links[] = '<a href="' . WeDevs\Wpuf\Free\Pro_Prompt::get_pro_url() . '" target="_blank" style="color: red;">Get PRO</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">Settings</a>';
        $links[] = '<a href="https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/" target="_blank">Documentation</a>';

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
        $this->container['widgets'] = new WeDevs\Wpuf\Widgets\Manager();
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
function wpuf() {
    return WP_User_Frontend::instance();
}

// kickoff
wpuf();
