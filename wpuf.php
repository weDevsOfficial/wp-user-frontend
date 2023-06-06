<?php
/*
Plugin Name: WP User Frontend
Plugin URI: https://wordpress.org/plugins/wp-user-frontend/
Description: Create, edit, delete, manages your post, pages or custom post types from frontend. Create registration forms, frontend profile and more...
Author: weDevs
Version: 3.6.5
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
define( 'WPUF_VERSION', '3.6.5' );
define( 'WPUF_FILE', __FILE__ );
define( 'WPUF_ROOT', __DIR__ );
define( 'WPUF_ROOT_URI', plugins_url( '', __FILE__ ) );
define( 'WPUF_ASSET_URI', WPUF_ROOT_URI . '/assets' );
define( 'WPUF_INCLUDES', WPUF_ROOT . '/includes' );

/**
 * Main bootstrap class for WP User Frontend
 */
final class WP_User_Frontend {

    /**
     * Holds various class instances
     *
     * @since 2.5.7
     *
     * @var array
     */
    private $container = [];

    /**
     * Form field value seperator
     *
     * @var string
     */
    public static $field_separator = '| ';

    /**
     * The singleton instance
     *
     * @var WP_User_Frontend
     */
    private static $_instance;

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
     * Fire up the plugin
     */
    public function __construct() {
        require_once __DIR__ . '/vendor/autoload.php';

        if ( ! $this->is_supported_php() ) {
            add_action( 'admin_notices', [ $this, 'php_version_notice' ] );

            return;
        }

        register_activation_hook( __FILE__, [ $this, 'install' ] );
        register_deactivation_hook( __FILE__, [ $this, 'uninstall' ] );

        $this->includes();
        $this->init_hooks();

        // Insight class instantiate
        $this->container['tracker'] = new Wp\User\Frontend\Lib\WPUF_WeDevs_Insights( __FILE__ );

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
        add_action( 'plugins_loaded', [ $this, 'plugin_upgrades' ] );
        add_action( 'plugins_loaded', [ $this, 'instantiate' ], 11 );

        add_action( 'init', [ $this, 'load_textdomain' ] );

        // do plugin upgrades
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @since 2.5.7
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        if ( property_exists( $this, $prop ) ) {
            return $this->{$prop};
        }

        return false;
    }

    /**
     * Schedules the post expiry event
     *
     * @since 2.2.7
     */
    public static function set_schedule_events() {
        if ( ! wp_next_scheduled( 'wpuf_remove_expired_post_hook' ) ) {
            wp_schedule_event(time(), 'daily', 'wpuf_remove_expired_post_hook');
        }
    }

    /**
     * Singleton Instance
     *
     * @return \self
     */
    public static function init() {
        if ( ! self::$_instance ) {
            self::$_instance = new WP_User_Frontend();
        }

        return self::$_instance;
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        require_once __DIR__ . '/wpuf-functions.php';
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate() {
        $this->container['assets']       = new Wp\User\Frontend\Assets();
        $this->container['subscription'] = new Wp\User\Frontend\Admin\Subscription();
        $this->container['fields']       = new Wp\User\Frontend\Admin\Forms\Field_Manager();

        if ( is_admin() ) {
            $this->container['admin']        = new Wp\User\Frontend\Admin();
            $this->container['setup_wizard'] = new Wp\User\Frontend\Setup_Wizard();
            $this->container['pro_upgrades'] = new Wp\User\Frontend\Pro_Upgrades();
        } else {
            $this->container['frontend'] = new Wp\User\Frontend\Frontend();
        }

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $this->container['ajax'] = new Wp\User\Frontend\Ajax();
        }
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    public function install() {
        $installer = new Wp\User\Frontend\Installer();
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

        $this->container['upgrades'] = new Wp\User\Frontend\Admin\Upgrades();
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
            add_action( 'admin_notices', [ $this, 'wpuf_latest_pro_activation_notice' ] );
        } else {
            $this->container['free_loader'] = new Wp\User\Frontend\Free\Free_Loader();
        }
    }

    /**
     * Latest Pro Activation Message
     *
     * @return void
     */
    public function wpuf_latest_pro_activation_notice() {
        if ( ! version_compare( WPUF_PRO_VERSION, '3.1.0', '<' ) ) {
            return;
        }

        $offer_msg = __(
            '<p style="font-size: 13px">
                            <strong class="highlight-text" style="font-size: 18px; display:block; margin-bottom:8px"> UPDATE REQUIRED </strong>
                            WP User Frontend Pro is not working because you are using an old version of WP User Frontend Pro. Please update <strong>WPUF Pro</strong> to >= <strong>v3.1.0</strong> to work with the latest version of WP User Frontend
                        </p>', 'wp-user-frontend'
        );
        ?>
            <div class="notice is-dismissible" id="wpuf-update-offer-notice">
                <table>
                    <tbody>
                        <tr>
                            <td class="image-container">
                                <img src="https://ps.w.org/wp-user-frontend/assets/icon-256x256.png" alt="">
                            </td>
                            <td class="message-container">
                                <?php echo esc_html( $offer_msg ); ?>
                            </td>
                            <td><a href="https://wedevs.com/account/downloads/" class="button button-primary promo-btn" target="_blank"><?php esc_html_e( 'Update WP User Frontend Pro Now', 'wp-user-frontend' ); ?></a></td>
                        </tr>
                    </tbody>
                </table>
                <!-- <a href="https://wedevs.com/account/downloads/" class="button button-primary promo-btn" target="_blank"><?php esc_html_e( 'Update WP User Frontend Pro NOW', 'wp-user-frontend' ); ?></a> -->
            </div><!-- #wpuf-update-offer-notice -->

            <style>
                #wpuf-update-offer-notice {
                    background-size: cover;
                    border: 0px;
                    padding: 10px;
                    opacity: 0;
                    border-left: 3px solid red;
                }

                .wrap > #wpuf-update-offer-notice {
                    opacity: 1;
                }

                #wpuf-update-offer-notice table {
                    border-collapse: collapse;
                    width: 70%;
                }

                #wpuf-update-offer-notice table td {
                    padding: 0;
                }

                #wpuf-update-offer-notice table td.image-container {
                    background-color: #fff;
                    vertical-align: middle;
                    width: 95px;
                }


                #wpuf-update-offer-notice img {
                    max-width: 100%;
                    max-height: 100px;
                    vertical-align: middle;
                    border-radius: 100%;
                }

                #wpuf-update-offer-notice table td.message-container {
                    padding: 0 10px;
                }

                #wpuf-update-offer-notice h2{
                    color: #000;
                    margin-bottom: 10px;
                    font-weight: normal;
                    margin: 16px 0 14px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }


                #wpuf-update-offer-notice h2 span {
                    position: relative;
                    top: 0;
                }

                #wpuf-update-offer-notice p{
                    color: #000;
                    font-size: 14px;
                    margin-bottom: 10px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }

                #wpuf-update-offer-notice p strong.highlight-text{
                    color: #000;
                }

                #wpuf-update-offer-notice p a {
                    color: #000;
                }

                #wpuf-update-offer-notice .notice-dismiss:before {
                    color: #000;
                }

                #wpuf-update-offer-notice span.dashicons-megaphone {
                    position: absolute;
                    bottom: 46px;
                    right: 248px;
                    color: rgba(253, 253, 253, 0.29);
                    font-size: 96px;
                    transform: rotate(-21deg);
                }

                #wpuf-update-offer-notice a.promo-btn{
                    background: #0073aa;
                    /*border-color: #fafafa #fafafa #fafafa;*/
                    box-shadow: 0 1px 0 #fafafa;
                    color: #fff;
                    text-decoration: none;
                    text-shadow: none;
                    position: absolute;
                    top: 40px;
                    right: 26px;
                    height: 40px;
                    line-height: 40px;
                    width: 300px;
                    text-align: center;
                    font-weight: 600;
                }

            </style>
            <script type='text/javascript'>
                jQuery('body').on('click', '#wpuf-update-offer-notice .notice-dismiss', function(e) {
                    e.preventDefault();

                    wp.ajax.post('wpuf-dismiss-update-offer-notice', {
                        dismissed: true
                    });
                });
            </script>

        <?php
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
            $links[] = '<a href="' . Wp\User\Frontend\Free\Pro_Prompt::get_pro_url() . '" target="_blank" style="color: red;">Get PRO</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">Settings</a>';
        $links[] = '<a href="https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/" target="_blank">Documentation</a>';

        return $links;
    }

    /**
     * Add item to the WPUF container
     *
     * @since WPUF_SINCE
     *
     * @param $name
     * @param $object
     *
     * @return void
     */
    public function add_to_container( $name, $object ) {
        $this->container[ $name ] = $object;
    }

    /**
     * Returns the WPUF container that holds all the plugin classes
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_container() {
        return $this->container;
    }
}

/**
 * Returns the singleton instance
 *
 * @return \WP_User_Frontend
 */
function wpuf() {
    return WP_User_Frontend::init();
}

// kickoff
wpuf();
