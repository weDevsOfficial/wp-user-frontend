<?php
/*
Plugin Name: WP User Frontend
Plugin URI: https://wordpress.org/plugins/wp-user-frontend/
Description: Create, edit, delete, manages your post, pages or custom post types from frontend. Create registration forms, frontend profile and more...
Author: Tareq Hasan
Version: 2.8.7
Author URI: https://tareq.co
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpuf
Domain Path: /languages
*/

define( 'WPUF_VERSION', '2.8.7' );
define( 'WPUF_FILE', __FILE__ );
define( 'WPUF_ROOT', dirname( __FILE__ ) );
define( 'WPUF_ROOT_URI', plugins_url( '', __FILE__ ) );
define( 'WPUF_ASSET_URI', WPUF_ROOT_URI . '/assets' );

/**
 * Main bootstrap class for WP User Frontend
 *
 * @package WP User Frontend
 */
final class WP_User_Frontend {

    /**
     * Holds various class instances
     *
     * @since 2.5.7
     *
     * @var array
     */
    private $container = array();

    /**
     * The singleton instance
     *
     * @var WP_User_Frontend
     */
    private static $_instance;

    /**
     * Pro plugin checkup
     *
     * @var boolean
     */
    private $is_pro = false;

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.4.0';

    /**
     * Fire up the plugin
     */
    public function __construct() {

        if ( ! $this->is_supported_php() ) {
            add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
            return;
        }

        register_activation_hook( __FILE__, array( $this, 'install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

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

        if ( version_compare( PHP_VERSION, $min_php , '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Show notice about PHP version
     *
     * @return void
     */
    function php_version_notice() {

        if ( $this->is_supported_php() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $error = __( 'Your installed PHP Version is: ', 'wpuf' ) . PHP_VERSION . '. ';
        $error .= __( 'The <strong>WP User Frontend</strong> plugin requires PHP version <strong>', 'wpuf' ) . $this->min_php . __( '</strong> or greater.', 'wpuf' );
        ?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
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

        add_action( 'plugins_loaded', array( $this, 'wpuf_loader' ) );
        add_action( 'plugins_loaded', array( $this, 'plugin_upgrades' ) );

        add_action( 'plugins_loaded', array( $this, 'instantiate' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );

        add_action( 'admin_init', array( $this, 'block_admin_access' ) );

        add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // do plugin upgrades
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

        // add custom css
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );

        // set schedule event
        add_action( 'wpuf_remove_expired_post_hook', array( $this, 'action_to_remove_exipred_post' ) );
        add_action( 'wp_ajax_wpuf_weforms_install', array( $this, 'install_weforms' ) );
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

        return $this->{$prop};
    }

    /**
     * Schedules the post expiry event
     *
     * @since 2.2.7
     */
    public static function set_schedule_events() {
        wp_schedule_event( time(), 'daily', 'wpuf_remove_expired_post_hook' );
    }

    /**
     * Action when posts expiration date is passed
     *
     * @since 2.2.7
     */
    public function action_to_remove_exipred_post() {
        $args = array(
            'meta_key'       => 'wpuf-post_expiration_date',
            'meta_value'     => date( 'Y-m-d' ),
            'meta_compare'   => '<',
            'post_type'      => get_post_types(),
            'post_status'    => 'publish',
            'posts_per_page' => -1
        );

        $mail_subject = apply_filters( 'wpuf_post_expiry_mail_subject', sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'Your Post Has Been Expired', 'wpuf' ) ) );
        $posts        = get_posts( $args );

        foreach ( $posts as $each_post ) {
            $post_to_update = array(
                'ID'          => $each_post->ID,
                'post_status' => get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) ? get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) : 'draft'
            );

            wp_update_post( $post_to_update );

            $message = get_post_meta( $each_post->ID, 'wpuf-post_expiration_message', true );

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
        require_once dirname( __FILE__ ) . '/wpuf-functions.php';
        require_once dirname( __FILE__ ) . '/lib/gateway/paypal.php';
        require_once dirname( __FILE__ ) . '/lib/gateway/bank.php';
        require_once dirname( __FILE__ ) . '/lib/class-wedevs-insights.php';

        // global classes/functions
        require_once WPUF_ROOT . '/class/upload.php';
        require_once WPUF_ROOT . '/admin/form-template.php';
        require_once WPUF_ROOT . '/class/post-form-template.php';
        require_once WPUF_ROOT . '/class/subscription.php';
        require_once WPUF_ROOT . '/class/render-form.php';
        require_once WPUF_ROOT . '/class/payment.php';
        require_once WPUF_ROOT . '/class/frontend-form-post.php';
        require_once WPUF_ROOT . '/class/frontend-account.php';
        require_once WPUF_ROOT . '/includes/class-form.php';
        require_once WPUF_ROOT . '/includes/class-form-manager.php';
        require_once WPUF_ROOT . '/includes/class-login-widget.php';
        require_once WPUF_ROOT . '/includes/setup-wizard.php';
        require_once WPUF_ROOT . '/includes/countries-state.php';
        require_once WPUF_ROOT . '/includes/class-billing-address.php';
        include_once WPUF_ROOT . '/includes/class-gutenblock.php';
        include_once WPUF_ROOT . '/includes/class-form-preview.php';

        if ( class_exists( 'WeDevs_Dokan' ) ) {
            require_once WPUF_ROOT . '/includes/class-dokan-integration.php';
        }

        require_once WPUF_ROOT . '/includes/class-user.php';
        require_once WPUF_ROOT . '/includes/class-user-subscription.php';

        if ( is_admin() ) {
            require_once WPUF_ROOT . '/admin/settings-options.php';
            require_once WPUF_ROOT . '/admin/class-admin-settings.php';
            require_once WPUF_ROOT . '/admin/form-handler.php';
            require_once WPUF_ROOT . '/admin/form.php';
            require_once WPUF_ROOT . '/admin/posting.php';
            require_once WPUF_ROOT . '/admin/class-admin-subscription.php';
            require_once WPUF_ROOT . '/admin/installer.php';
            require_once WPUF_ROOT . '/admin/class-admin-welcome.php';
            require_once WPUF_ROOT . '/admin/promotion.php';
            require_once WPUF_ROOT . '/admin/post-forms-list-table.php';
            require_once WPUF_ROOT . '/includes/free/admin/shortcode-button.php';
            require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-admin-form-builder.php';
            require_once WPUF_ROOT . '/admin/form-builder/class-wpuf-admin-form-builder-ajax.php';
            include_once WPUF_ROOT . '/lib/class-weforms-upsell.php';
            include_once WPUF_ROOT . '/includes/class-whats-new.php';
            include_once WPUF_ROOT . '/includes/class-acf.php';

        } else {

            require_once WPUF_ROOT . '/class/frontend-dashboard.php';
            require_once WPUF_ROOT . '/includes/free/class-registration.php';
            require_once WPUF_ROOT . '/includes/free/class-login.php';
        }

        // add reCaptcha library if not found
        if ( ! function_exists( 'recaptcha_get_html' ) ) {
            require_once dirname( __FILE__ ) . '/lib/recaptchalib.php';
            require_once dirname( __FILE__ ) . '/lib/recaptchalib_noCaptcha.php';
            require_once dirname( __FILE__ ) . '/lib/invisible_recaptcha.php';
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    function instantiate() {

        $this->container['upload']                  = new WPUF_Upload();
        $this->container['paypal']                  = new WPUF_Paypal();
        $this->container['form_template']           = new WPUF_Admin_Form_Template();

        $this->container['subscription']            = WPUF_Subscription::init();
        $this->container['frontend_post']           = WPUF_Frontend_Form_Post::init();
        $this->container['account']                 = new WPUF_Frontend_Account();
        $this->container['insights']                = new WPUF_WeDevs_Insights( 'wp-user-frontend', 'WP User Frontend', __FILE__ );
        $this->container['billing_address']         = new WPUF_Ajax_Address_Form();
        $this->container['forms']                   = new WPUF_Form_Manager();
        $this->container['preview']                 = new WPUF_Form_Preview();
        $this->container['block']                   = new WPUF_Form_Block();

        if ( class_exists( 'WeDevs_Dokan' ) ) {
            $this->container['dokan_integration']   = new WPUF_Dokan_Integration();
        }

        if ( is_admin() ) {

            $this->container['settings']           = WPUF_Admin_Settings::init();
            $this->container['form_handler']       = new WPUF_Admin_Form_Handler();
            $this->container['admin_form']         = new WPUF_Admin_Form();
            $this->container['admin_posting']      = WPUF_Admin_Posting::init();
            $this->container['admin_subscription'] = new WPUF_Admin_Subscription();
            $this->container['admin_installer']    = new WPUF_Admin_Installer();
            $this->container['admin_promotion']    = new WPUF_Admin_Promotion();
            $this->container['welcome']            = new WPUF_Admin_Welcome();
            $this->container['whats_new']          = new WPUF_Whats_New();
            $this->container['wpuf_acf']           = new WPUF_ACF_Compatibility();

        } else {

            $this->container['dashboard']       = new WPUF_Frontend_Dashboard();
            $this->container['payment']         = new WPUF_Payment();
            $this->container['login']           = WPUF_Simple_Login::init();
            $this->container['registration']    = WPUF_Registration::init();
        }
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
    function plugin_upgrades() {

        if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        require_once WPUF_ROOT . '/includes/class-upgrades.php';

        $upgrader = new WPUF_Upgrades();

        if ( $upgrader->needs_update() ) {
            $upgrader->perform_updates();
        }
    }

    /**
     * Load wpuf free class if not pro
     *
     * @since 2.5.4
     */
    function wpuf_loader() {
        $is_expired = wpuf_is_license_expired();
        $has_pro    = class_exists( 'WP_User_Frontend_Pro' );

        if ( $has_pro && $is_expired ) {
            add_action( 'admin_notices', array( $this, 'license_expired' ) );
        }

        if ( $has_pro ) {
            $this->is_pro = true;
        } else {

            include dirname( __FILE__ ) . '/includes/free/loader.php';

            $this->container['free_loader'] = new WPUF_Free_Loader();
        }
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
     * Enqueues Styles and Scripts when the shortcodes are used only
     *
     * @uses has_shortcode()
     *
     * @since 0.2
     */
    function enqueue_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        global $post;

        $scheme  = is_ssl() ? 'https' : 'http';
        $api_key = wpuf_get_option( 'gmap_api_key', 'wpuf_general' );

        $load_gmap = apply_filters( 'wpuf_load_gmap_script', true );

        if ( ! empty( $api_key ) && $load_gmap ) {
            wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?libraries=places&key=' . $api_key, array(), null );
        }

        if ( isset( $post->ID ) ) {
            ?>
            <script type="text/javascript" id="wpuf-language-script">
                var error_str_obj = {
                    'required' : '<?php esc_attr_e( 'is required', 'wpuf' ); ?>',
                    'mismatch' : '<?php esc_attr_e( 'does not match', 'wpuf' ); ?>',
                    'validation' : '<?php esc_attr_e( 'is not valid', 'wpuf' ); ?>'
                }
            </script>
            <?php
            wp_enqueue_script( 'wpuf-form', WPUF_ASSET_URI . '/js/frontend-form' . $suffix . '.js', array( 'jquery' ) );
        }

        wp_enqueue_style( 'wpuf-css', WPUF_ASSET_URI . '/css/frontend-forms.css' );

        // register css files for different layouts of frontend form
        wp_register_style( 'wpuf-layout1', WPUF_ASSET_URI . '/css/frontend-form/layout1.css' );
        wp_register_style( 'wpuf-layout2', WPUF_ASSET_URI . '/css/frontend-form/layout2.css' );
        wp_register_style( 'wpuf-layout3', WPUF_ASSET_URI . '/css/frontend-form/layout3.css' );

        wp_enqueue_script( 'wpuf-subscriptions', WPUF_ASSET_URI . '/js/subscriptions.js', array( 'jquery' ), false, true );

        if ( wpuf_get_option( 'load_script', 'wpuf_general', 'on' ) == 'on' ) {
            $this->plugin_scripts();
        } elseif ( wpuf_has_shortcode( 'wpuf_form' ) || wpuf_has_shortcode( 'wpuf_edit' ) || wpuf_has_shortcode( 'wpuf_profile' ) || wpuf_has_shortcode( 'wpuf_dashboard' ) || wpuf_has_shortcode( 'weforms' ) || wpuf_has_shortcode( 'wpuf_account' ) ) {
            $this->plugin_scripts();
        }
    }

    /**
     * add custom css to head
     */
    function add_custom_css() {
        global $post;

        if ( ! is_a( $post, 'WP_Post' ) ) {
            return;
        }

        if ( wpuf_has_shortcode( 'wpuf_form', $post->ID )
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
                <?php echo $custom_css = wpuf_get_option( 'custom_css', 'wpuf_general' ); ?>
            </style>
            <?php

        }
    }


    function plugin_scripts() {

        wp_enqueue_style( 'jquery-ui', WPUF_ASSET_URI . '/css/jquery-ui-1.9.1.custom.css' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'suggest' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'plupload-handlers' );
        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker' ) );
        wp_enqueue_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', array( 'jquery', 'plupload-handlers' ) );

        wp_localize_script( 'wpuf-form', 'wpuf_frontend', array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'error_message' => __( 'Please fix the errors to proceed', 'wpuf' ),
			'nonce'         => wp_create_nonce( 'wpuf_nonce' ),
			'word_limit'    => __( 'Word limit reached', 'wpuf' )
        ) );

        wp_localize_script( 'wpuf-upload', 'wpuf_frontend_upload', array(
			'confirmMsg' => __( 'Are you sure?', 'wpuf' ),
			'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
			'plupload'   => array(
				'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
				'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
				'filters'          => array(
					array(
						'title' => __( 'Allowed Files', 'wpuf' ),
						'extensions' => '*'
					)
				),
				'multipart'        => true,
				'urlstream_upload' => true,
				'warning'          => __( 'Maximum number of files reached!', 'wpuf' ),
				'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'wpuf' ),
				'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'wpuf' )
			)
        ) );
    }

    /**
     * Block user access to admin panel for specific roles
     *
     * @global string $pagenow
     */
    function block_admin_access() {
        global $pagenow;

        // bail out if we are from WP Cli
        if ( defined( 'WP_CLI' ) ) {
            return;
        }

        $access_level = wpuf_get_option( 'admin_access', 'wpuf_general', 'read' );
        $valid_pages  = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' );

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
     * @return void
     */
    function show_admin_bar() {

        if ( !is_user_logged_in() ) {
            return false;
        }

        $roles = wpuf_get_option( 'show_admin_bar', 'wpuf_general', array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) );
        $roles = $roles ? $roles : array();
        $current_user = wp_get_current_user();
        if ( !in_array( $current_user->roles[0], $roles ) ) {
            return false;
        }

        return true;
    }

    /**
     * Load the translation file for current language.
     *
     * @since version 0.7
     * @author Tareq Hasan
     */
    function load_textdomain() {
        load_plugin_textdomain( 'wpuf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * The main logging function
     *
     * @uses error_log
     * @param string $type type of the error. e.g: debug, error, info
     * @param string $msg
     */
    public static function log( $type = '', $msg = '' ) {
        $msg = sprintf( "[%s][%s] %s\n", date( 'd.m.Y h:i:s' ), $type, $msg );
        error_log( $msg, 3, dirname( __FILE__ ) . '/log.txt' );
    }

    /**
     * Returns if the plugin is in PRO version
     *
     * @since 2.3.2
     *
     * @return boolean
     */
    public function is_pro() {
        return $this->is_pro;
    }

    /**
     * Plugin action links
     *
     * @param  array $links
     *
     * @since  2.3.3
     *
     * @return array
     */
    function plugin_action_links( $links ) {

        if ( ! $this->is_pro() ) {
            $links[] = '<a href="' . WPUF_Pro_Prompt::get_pro_url() . '" target="_blank" style="color: red;">Get PRO</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">Settings</a>';
        $links[] = '<a href="https://docs.wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/" target="_blank">Documentation</a>';

        return $links;
    }

    /**
     * Show renew prompt once the license key is expired
     *
     * @since 2.3.13
     *
     * @return void
     */
    function license_expired() {
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
        add_action( 'wp_ajax_wpuf_weforms_install', array( $this, 'install_weforms' ) );
    }
    /**
     * Install weforms plugin via ajax
     *
     * @return void
     */
    public function install_weforms() {

        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpuf-weforms-installer-nonce' ) ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'wpuf' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        if ( file_exists( WP_PLUGIN_DIR . '/weforms/weforms.php' ) ) {
            activate_plugin( 'weforms/weforms.php' );
            wp_send_json_success();
        }

        $plugin = 'weforms';
        $api    = plugins_api( 'plugin_information', array(
            'slug'   => $plugin,
            'fields' => array(
				'sections' => false
			)
        ) );

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
