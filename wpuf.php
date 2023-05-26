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

        // add_action( 'admin_init', [ $this, 'block_admin_access' ] );

        // add_filter( 'show_admin_bar', [ $this, 'show_admin_bar' ] );

        // enqueue plugin scripts, don't remove priority.
        // If remove or set priority under 1000 then registered styles will not load on WC Marketplace vendor dashboard.
        // we have integration with WC Marketplace plugin since version 3.0 where WC Marketplae vendors' can submit post
        // add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9999 );

        // do plugin upgrades
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ] );

        // add custom css
        // add_action( 'wp_head', [ $this, 'add_custom_css' ] );

        // set schedule event
//        add_action( 'wpuf_remove_expired_post_hook', [ $this, 'action_to_remove_exipred_post' ] );
//        add_action( 'wp_ajax_wpuf_weforms_install', [ $this, 'install_weforms' ] );

        // Insight class instentiate
        // $this->container['tracker'] = new WPUF_WeDevs_Insights( __FILE__ );
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
     * Action when posts expiration date is passed
     *
     * @since 2.2.7
     */
    public function action_to_remove_exipred_post() {
        $args = [
            'meta_key'       => 'wpuf-post_expiration_date',
            'meta_value'     => date( 'Y-m-d' ),
            'meta_compare'   => '<',
            'post_type'      => get_post_types(),
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];

        $mail_subject = apply_filters( 'wpuf_post_expiry_mail_subject', sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'Your Post Has Been Expired', 'wp-user-frontend' ) ) );
        $posts        = get_posts( $args );

        foreach ( $posts as $each_post ) {
            $post_to_update = [
                'ID'          => $each_post->ID,
                'post_status' => get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) ? get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) : 'draft',
            ];

            wp_update_post( $post_to_update );

            $post_url       = get_permalink( $each_post->ID );
            $author_id      = $each_post->post_author;
            $post_author    = get_the_author_meta( 'user_login', $author_id );
            $blogname       = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

            $search = [
                '{post_author}',
                '{post_url}',
                '{blogname}',
                '{post_title}',
                '{post_status}',
            ];

            $replace = [
                $post_author,
                $post_url,
                $blogname,
                $each_post->post_title,
                $each_post->post_status,
            ];

            $message        = get_post_meta( $each_post->ID, 'wpuf-post_expiration_message', true );
            $message        = str_replace( $search, $replace, $message );
            $message        = get_formatted_mail_body( $message, $mail_subject );

            if ( ! empty( $message ) ) {
                wp_mail( get_the_author_meta( 'user_email', $each_post->post_author ), $mail_subject, $message );
            }
        }
        // save an option for debugging purpose
        update_option( 'wpuf_expiry_posts_last_cleaned', date( 'F j, Y g:i a' ) );
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
//        require_once __DIR__ . '/class/encryption-helper.php';
//        require_once __DIR__ . '/lib/gateway/paypal.php';
//        require_once __DIR__ . '/lib/gateway/bank.php';
//        require_once __DIR__ . '/lib/class-wedevs-insights.php';
//
//        require_once WPUF_ROOT . '/includes/class-frontend-render-WPUF_Admin_Form_Free.php';
//        require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-form-builder-field-settings.php';
//
//        // global classes/functions
//        require_once WPUF_ROOT . '/class/upload.php';
//        require_once WPUF_ROOT . '/admin/WPUF_Admin_Form_Template.php';
//        require_once WPUF_ROOT . '/class/post-WPUF_Admin_Form_Template.php';
//        require_once WPUF_ROOT . '/class/WPUF_Subscription_Element.php';
//        require_once WPUF_ROOT . '/class/render-WPUF_Admin_Form_Free.php';
//        require_once WPUF_ROOT . '/class/payment.php';
//        require_once WPUF_ROOT . '/class/frontend-account.php';
//        require_once WPUF_ROOT . '/includes/class-WPUF_Admin_Form_Free.php';
//        require_once WPUF_ROOT . '/includes/class-form-manager.php';
//        require_once WPUF_ROOT . '/includes/class-login-widget.php';
//        require_once WPUF_ROOT . '/includes/setup-wizard.php';
//        require_once WPUF_ROOT . '/includes/countries-state.php';
//        require_once WPUF_ROOT . '/includes/class-billing-address.php';
//        include_once WPUF_ROOT . '/includes/class-gutenblock.php';
//        include_once WPUF_ROOT . '/includes/class-form-preview.php';
//        include_once WPUF_ROOT . '/includes/class-customizer.php';
//        include_once WPUF_ROOT . '/includes/log/class-log.php';
//        include_once WPUF_ROOT . '/includes/log/class-log-wpdb-query.php';
//        //        include_once WPUF_ROOT . '/includes/class-user-prorate.php';
//
//        if ( class_exists( 'WeDevs_Dokan' ) ) {
//            require_once WPUF_ROOT . '/includes/class-dokan-integration.php';
//        }
//
//        if ( class_exists( 'WCMp' ) ) {
//            require_once WPUF_ROOT . '/includes/class-wcmp-integration.php';
//        }
//
//        if ( class_exists( 'WC_Vendors' ) ) {
//            require_once WPUF_ROOT . '/includes/class-wc-vendors-integration.php';
//        }
//
//        require_once WPUF_ROOT . '/includes/class-user.php';
//        require_once WPUF_ROOT . '/includes/class-user-WPUF_Subscription_Element.php';
//
//        if ( is_admin() ) {
//            require_once WPUF_ROOT . '/admin/settings-options.php';
//            require_once WPUF_ROOT . '/admin/class-admin-settings.php';
//            require_once WPUF_ROOT . '/admin/form-handler.php';
//            require_once WPUF_ROOT . '/admin/WPUF_Admin_Form_Free.php';
//            require_once WPUF_ROOT . '/admin/posting.php';
//            require_once WPUF_ROOT . '/admin/class-admin-WPUF_Subscription_Element.php';
//            require_once WPUF_ROOT . '/admin/installer.php';
//            require_once WPUF_ROOT . '/admin/class-admin-welcome.php';
//            require_once WPUF_ROOT . '/admin/promotion.php';
//            require_once WPUF_ROOT . '/admin/WPUF_Admin_Post_Forms_List_Table.php';
//            require_once WPUF_ROOT . '/includes/Free/admin/shortcode-button.php';
//            require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-admin-form-builder.php';
//            require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-admin-form-builder-ajax.php';
//            // include_once WPUF_ROOT . '/lib/class-weforms-upsell.php';
//            include_once WPUF_ROOT . '/includes/class-whats-new.php';
//            include_once WPUF_ROOT . '/includes/class-acf.php';
//            include_once WPUF_ROOT . '/includes/class-privacy.php';
//            include_once WPUF_ROOT . '/admin/dashboard-metabox.php';
//        } else {
//            require_once WPUF_ROOT . '/class/frontend-dashboard.php';
//            require_once WPUF_ROOT . '/includes/Free/class-registration.php';
//        }
//
//        // add reCaptcha library if not found
//        if ( ! function_exists( 'recaptcha_get_html' ) ) {
//            require_once __DIR__ . '/lib/recaptchalib.php';
//            require_once __DIR__ . '/lib/invisible_recaptcha.php';
//        }
//
//        require_once WPUF_ROOT . '/includes/Free/class-login.php';
//        require_once WPUF_ROOT . '/includes/class-frontend-form-WPUF_Post_Form_Template_Post.php';
//        require_once WPUF_ROOT . '/includes/class-field-manager.php';
//        require_once WPUF_ROOT . '/includes/class-pro-upgrades.php';
//        require_once WPUF_ROOT . '/includes/fields/field-trait.php';
//          require_once WPUF_ROOT . '/includes/Assets.php';

//        if ( is_admin() ) {
//            require_once WPUF_ROOT . '/admin/WPUF_Admin_Post_Forms_List_Table.php';
//        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    public function instantiate() {
        $this->container['assets']       = new Wp\User\Frontend\Assets();
        $this->container['subscription'] = Wp\User\Frontend\Admin\WPUF_Subscription::init();
//        $this->container['upload']          = new WPUF_Upload();
//        $this->container['subscription']    = WPUF_Subscription::init();
//        $this->container['billing_address'] = new WPUF_Ajax_Address_Form();
//        $this->container['block']           = new WPUF_Form_Block();
//        $this->container['log']             = new WPUF_Log();
//
//        if ( class_exists( 'WeDevs_Dokan' ) ) {
//            $this->container['dokan_integration'] = new WPUF_Dokan_Integration();
//        }
//
//        if ( class_exists( 'WCMp' ) ) {
//            $this->container['wcmp_integration'] = new WPUF_WCMp_Integration();
//        }
//
//        if ( class_exists( 'WC_Vendors' ) ) {
//            $this->container['WCV_Integration'] = new WPUF_WC_Vendors_Integration();
//        }

        if ( is_admin() ) {
            $this->container['admin']              = new Wp\User\Frontend\Admin();
            $this->container['pro_upgrades']       = new Wp\User\Frontend\WPUF_Pro_Upgrades();
            //            $this->container['settings']           = WPUF_Admin_Settings::init();
//            $this->container['form_handler']       = new WPUF_Admin_Form_Handler();
//
//            $this->container['admin_posting']      = WPUF_Admin_Posting::init();
//            $this->container['admin_subscription'] = new WPUF_Admin_Subscription();
//            $this->container['admin_installer']    = new WPUF_Admin_Installer();
//            $this->container['admin_promotion']    = new WPUF_Admin_Promotion();
//            $this->container['welcome']            = new WPUF_Admin_Welcome();
//            $this->container['whats_new']          = new WPUF_Whats_New();
//            $this->container['wpuf_acf']           = new WPUF_ACF_Compatibility();
//            $this->container['privacy']            = new WPUF_Privacy();
//            $this->container['dashboard_mb']       = new Dashboard_Metabox();
//            $this->container['forms']              = new WPUF_Form_Manager();
//            $this->container['preview']            = new WPUF_Form_Preview();
//            $this->container['customize']          = new WPUF_Customizer_Options();
//            $this->container['fields']             = new WPUF_Field_Manager();
        } else {
//            $this->container['dashboard']     = new WPUF_Frontend_Dashboard();
//            $this->container['payment']       = new WPUF_Payment();
//            $this->container['registration']  = WPUF_Registration::init();
//            $this->container['login']         = WPUF_Simple_Login::init();
//            $this->container['frontend_form'] = WPUF_Frontend_Form::init();
//            $this->container['paypal']        = new WPUF_Paypal();
//            $this->container['account']       = new WPUF_Frontend_Account();
        }
//        if ( ! $this->is_pro ) {
//            $this->container['pro_upgrades'] = new WPUF_Pro_Upgrades();
//        }
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    public static function install() {
        require_once WPUF_ROOT . '/includes/class-installer.php';

        $installer = new WPUF_Installer();
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

        require_once WPUF_ROOT . '/includes/class-upgrades.php';

        $this->container['upgrades'] = new WPUF_Upgrades();
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
            // include __DIR__ . '/includes/Free/WPUF_Free_Loader.php';

            $this->container['free_loader'] = new Wp\User\Frontend\Free\WPUF_Free_Loader();
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
     * add custom css to head
     */
    public function add_custom_css() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        if ( wpuf_has_shortcode( 'wpuf-login', $post->ID )
            || wpuf_has_shortcode( 'wpuf-registration', $post->ID )
            || wpuf_has_shortcode( 'wpuf-meta', $post->ID )
            || wpuf_has_shortcode( 'wpuf_form', $post->ID )
            || wpuf_has_shortcode( 'wpuf_edit', $post->ID )
            || wpuf_has_shortcode( 'wpuf_profile', $post->ID )
            || wpuf_has_shortcode( 'wpuf_dashboard', $post->ID )
            || wpuf_has_shortcode( 'wpuf_sub_pack', $post->ID )
            || wpuf_has_shortcode( 'wpuf-login', $post->ID )
            || wpuf_has_shortcode( 'wpuf_form', $post->ID )
            || wpuf_has_shortcode( 'wpuf_account', $post->ID )
         ) {
            ?>
            <style>
                <?php
                    $custom_css = wpuf_get_option( 'custom_css', 'wpuf_general' );
                    echo esc_html( $custom_css );
                ?>
            </style>
            <?php
        }
    }

    /**
     * Block user access to admin panel for specific roles
     *
     * @global string $pagenow
     */
    public function block_admin_access() {
        global $pagenow;

        // bail out if we are from WP Cli
        if ( defined( 'WP_CLI' ) ) {
            return;
        }

        $access_level = wpuf_get_option( 'admin_access', 'wpuf_general', 'read' );
        $valid_pages  = [ 'admin-ajax.php', 'admin-WPUF_Post_Form_Template_Post.php', 'async-upload.php', 'media-upload.php' ];

        if ( ! current_user_can( $access_level ) && ! in_array( $pagenow, $valid_pages ) ) {
            // wp_die( __( 'Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'wpuf' ) );
            wp_redirect( home_url() );
            exit;
        }
    }

    /**
     * Show/hide admin bar to the permitted user level
     *
     * @since 2.2.3
     *
     * @return bool
     */
    public function show_admin_bar( $val ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $roles        = wpuf_get_option( 'show_admin_bar', 'wpuf_general', [ 'administrator', 'editor', 'author', 'contributor', 'subscriber' ] );
        $roles        = $roles && is_string( $roles ) ? [ strtolower( $roles ) ] : $roles;
        $current_user = wp_get_current_user();

        if ( ! empty( $current_user->roles ) && ! empty( $current_user->roles[0] ) ) {
            if ( ! in_array( $current_user->roles[0], $roles ) ) {
                return false;
            }
        }

        return $val;
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
            $links[] = '<a href="' . WPUF_Pro_Prompt::get_pro_url() . '" target="_blank" style="color: red;">Get PRO</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">Settings</a>';
        $links[] = '<a href="https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/" target="_blank">Documentation</a>';

        return $links;
    }

    /**
     * Show renew prompt once the license key is expired
     *
     * @since 2.3.13
     *
     * @return void
     */
    public function license_expired() {
        echo '<div class="error">';
        echo '<p>Your <strong>WP User Frontend Pro</strong> License has been expired. Please <a href="https://wedevs.com/account/" target="_blank">renew your license</a>.</p>';
        echo '</div>';
    }

    /**
     * If the core isn't installed
     *
     * @return void
     */
    public function maybe_weforms_install() {
        if ( class_exists( 'WeForms' ) ) {
            return;
        }

        // install the core
        add_action( 'wp_ajax_wpuf_weforms_install', [ $this, 'install_weforms' ] );
    }

    /**
     * Install weforms plugin via ajax
     *
     * @return void
     */
    public function install_weforms() {
        $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

        if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-weforms-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'wp-user-frontend' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        if ( file_exists( WP_PLUGIN_DIR . '/weforms/weforms.php' ) ) {
            activate_plugin( 'weforms/weforms.php' );
            wp_send_json_success();
        }

        $plugin = 'weforms';
        $api    = plugins_api(
            'plugin_information', [
                'slug'   => $plugin,
                'fields' => [
                    'sections' => false,
                ],
            ]
        );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        $result = activate_plugin( 'weforms/weforms.php' );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        wp_send_json_success();
    }

    public function add_to_container( $name, $object ) {
        $this->container[ $name ] = $object;
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
