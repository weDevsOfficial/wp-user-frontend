<?php
/*
Plugin Name: WP User Frontend
Plugin URI: https://wordpress.org/plugins/wp-user-frontend/
Description: Create, edit, delete, manages your post, pages or custom post types from frontend. Create registration forms, frontend profile and more...
Author: Tareq Hasan
Version: 2.3.13
Author URI: http://tareq.weDevs.com
License: GPL2
TextDomain: wpuf
*/

define( 'WPUF_VERSION', '2.3.13' );
define( 'WPUF_FILE', __FILE__ );
define( 'WPUF_ROOT', dirname( __FILE__ ) );
define( 'WPUF_ROOT_URI', plugins_url( '', __FILE__ ) );
define( 'WPUF_ASSET_URI', WPUF_ROOT_URI . '/assets' );

/**
 * Autoload class files on demand
 *
 * `WPUF_Form_Posting` becomes => form-posting.php
 * `WPUF_Dashboard` becomes => dashboard.php
 *
 * @param string $class requested class name
 */
function wpuf_autoload( $class ) {

    if ( stripos( $class, 'WPUF_' ) !== false ) {

        $admin = ( stripos( $class, '_Admin_' ) !== false ) ? true : false;

        if ( $admin ) {
            $class_name = str_replace( array('WPUF_Admin_', '_'), array('', '-'), $class );
            $filename = dirname( __FILE__ ) . '/admin/' . strtolower( $class_name ) . '.php';
        } else {
            $class_name = str_replace( array('WPUF_', '_'), array('', '-'), $class );
            $filename = dirname( __FILE__ ) . '/class/' . strtolower( $class_name ) . '.php';
        }


        if ( file_exists( $filename ) ) {
            require_once $filename;
        }
    }
}

spl_autoload_register( 'wpuf_autoload' );

/**
 * Main bootstrap class for WP User Frontend
 *
 * @package WP User Frontend
 */
class WP_User_Frontend {

    private static $_instance;
    private $is_pro = false;

    function __construct() {

        $this->includes();

        $this->instantiate();

        register_activation_hook( __FILE__, array($this, 'install') );
        register_deactivation_hook( __FILE__, array($this, 'uninstall') );

        // set schedule event
        add_action( 'wpuf_remove_expired_post_hook', array( $this, 'action_to_remove_exipred_post' ) );

        add_action( 'admin_init', array($this, 'block_admin_access') );
        add_action( 'show_admin_bar', array($this, 'show_admin_bar') );

        add_action( 'init', array($this, 'load_textdomain') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );

        // do plugin upgrades
        add_action( 'plugins_loaded', array($this, 'plugin_upgrades') );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );

        //add custom css
        add_action( 'wp_head', array( $this, 'add_custom_css' ) );
    }

    /**
     * Schedules the post expiry event
     *
     * @since 2.2.7
     */
    public function set_schedule_events(){
        wp_schedule_event( time(), 'daily', 'wpuf_remove_expired_post_hook' );
    }

    /**
     * Action when posts expiration date is passed
     *
     * @since 2.2.7
     */
    public function action_to_remove_exipred_post(){
        $args = array(
            'meta_key'       => 'wpuf-post_expiration_date',
            'meta_value'     => date('Y-m-d'),
            'post_type'      => get_post_types(),
            'post_status'    => 'publish',
            'posts_per_page' => -1
        );

        $mail_subject = apply_filters( 'wpuf_post_expiry_mail_subject', sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'Your Post Has Been Expired', 'wpuf' ) ) );
        $posts        = get_posts( $args );

        foreach ($posts as $each_post) {
            $post_to_update = array(
                'ID'           => $each_post->ID,
                'post_status'  => get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) ? get_post_meta( $each_post->ID, 'wpuf-expired_post_status', true ) : 'draft'
            );

            wp_update_post( $post_to_update );

            if ( $message = get_post_meta( $each_post->ID, 'wpuf-post_expiration_message', true ) ) {
                wp_mail( $each_post->post_author, $mail_subject, $message );
            }
        }
    }

    public static function init() {

        if ( !self::$_instance ) {
            self::$_instance = new WP_User_Frontend();
        }

        return self::$_instance;
    }

    public function includes() {
        require_once dirname( __FILE__ ) . '/wpuf-functions.php';
        require_once dirname( __FILE__ ) . '/lib/gateway/paypal.php';
        require_once dirname( __FILE__ ) . '/lib/gateway/bank.php';

        $is_expired = wpuf_is_license_expired();
        $has_pro    = file_exists( dirname( __FILE__ ) . '/includes/pro/loader.php' );

        // if expired and the pro version, downgrade to the free one and show renew prompt
        if ( $is_expired && $has_pro ) {
            require_once dirname( __FILE__ ) . '/includes/pro/updates.php';

            new WPUF_Updates();

            add_action( 'admin_notices', array( $this, 'license_expired' ) );
        }

        if ( ! $is_expired && $has_pro ) {
            include dirname( __FILE__ ) . '/includes/pro/loader.php';

            $this->is_pro = true;

        } else {
            include dirname( __FILE__ ) . '/includes/free/loader.php';
        }

        if ( is_admin() ) {
            require_once dirname( __FILE__ ) . '/admin/settings-options.php';
        }

        // add reCaptcha library if not found
        if ( !function_exists( 'recaptcha_get_html' ) ) {
            require_once dirname( __FILE__ ) . '/lib/recaptchalib.php';
            require_once dirname( __FILE__ ) . '/lib/recaptchalib_noCaptcha.php';
        }
    }

    /**
     * Instantiate the classes
     *
     * @return void
     */
    function instantiate() {

        new WPUF_Upload();
        new WPUF_Payment();

        WPUF_Frontend_Form_Post::init(); // requires for form preview
        WPUF_Subscription::init();

        if ( is_admin() ) {
            WPUF_Admin_Settings::init();
            new WPUF_Admin_Form();
            new WPUF_Admin_Posting();
            new WPUF_Admin_Subscription();
            new WPUF_Admin_Installer();
        } else {
            new WPUF_Frontend_Dashboard();
        }
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    function install() {
        global $wpdb;

        $this->set_schedule_events();

        flush_rewrite_rules( false );

        $sql_transaction = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpuf_transaction (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) DEFAULT NULL,
            `status` varchar(255) NOT NULL DEFAULT 'pending_payment',
            `cost` varchar(255) DEFAULT '',
            `post_id` varchar(20) DEFAULT NULL,
            `pack_id` bigint(20) DEFAULT NULL,
            `payer_first_name` longtext,
            `payer_last_name` longtext,
            `payer_email` longtext,
            `payment_type` longtext,
            `payer_address` longtext,
            `transaction_id` longtext,
            `created` datetime NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql_transaction );

        update_option( 'wpuf_version', WPUF_VERSION );
    }

    /**
     * Do plugin upgrades
     *
     * @since 2.2
     * @return void
     */
    function plugin_upgrades() {

        if ( ! is_admin() && ! current_user_can( 'manage_options' ) ) {
            return;
        }

        new WPUF_Upgrades( WPUF_VERSION );
    }

    /**
     * Manage task on plugin deactivation
     *
     * @return void
     */
    function uninstall() {
        wp_clear_scheduled_hook( 'wpuf_remove_expired_post_hook' );
    }

    /**
     * Enqueues Styles and Scripts when the shortcodes are used only
     *
     * @uses has_shortcode()
     * @since 0.2
     */
    function enqueue_scripts() {
        global $post;

        $scheme = is_ssl() ? 'https' : 'http';
        wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js' );//?sensor=true


        if ( isset ( $post->ID ) ) {
            ?>
            <script type="text/javascript" id="wpuf-language-script">
                var error_str_obj = {
                    'required' : '<?php _e( 'is required', 'wpuf' ); ?>',
                    'mismatch' : '<?php _e( 'does not match', 'wpuf' ); ?>',
                    'validation' : '<?php _e( 'is not valid', 'wpuf' ); ?>'
                }
            </script>
            <?php
            wp_enqueue_script( 'wpuf-form', WPUF_ASSET_URI . '/js/frontend-form.js', array('jquery') );
            wp_enqueue_script( 'wpuf-conditional-logic', WPUF_ASSET_URI . '/js/conditional-logic.js', array('jquery'), false, true );
        }

        wp_enqueue_style( 'wpuf-css', WPUF_ASSET_URI . '/css/frontend-forms.css' );
        wp_enqueue_script( 'wpuf-subscriptions', WPUF_ASSET_URI . '/js/subscriptions.js', array('jquery'), false, true );

        if ( wpuf_get_option( 'load_script', 'wpuf_general', 'on') == 'on') {
            $this->plugin_scripts();
        } else if ( wpuf_has_shortcode( 'wpuf_form' ) || wpuf_has_shortcode( 'wpuf_edit' ) || wpuf_has_shortcode( 'wpuf_profile' ) || wpuf_has_shortcode( 'wpuf_dashboard' ) ) {
            $this->plugin_scripts();
        }
    }

    /**
     * add custom css to head
     */
    function add_custom_css() {
        global $post;

        if (   wpuf_has_shortcode( 'wpuf_form', $post->ID )
            || wpuf_has_shortcode( 'wpuf_edit', $post->ID )
            || wpuf_has_shortcode( 'wpuf_profile', $post->ID )
            || wpuf_has_shortcode( 'wpuf_dashboard', $post->ID )
            || wpuf_has_shortcode( 'wpuf_sub_pack', $post->ID )
            || wpuf_has_shortcode( 'wpuf-login', $post->ID )
            || wpuf_has_shortcode( 'wpuf_form', $post->ID )
            || wpuf_has_shortcode( 'wpuf_profile', $post->ID )
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
        wp_enqueue_script( 'jquery-ui-timepicker', WPUF_ASSET_URI . '/js/jquery-ui-timepicker-addon.js', array('jquery-ui-datepicker') );
        wp_enqueue_script( 'wpuf-upload', WPUF_ASSET_URI . '/js/upload.js', array('jquery', 'plupload-handlers') );

        wp_localize_script( 'wpuf-form', 'wpuf_frontend', array(
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
            'error_message' => __( 'Please fix the errors to proceed', 'wpuf' ),
            'nonce'         => wp_create_nonce( 'wpuf_nonce' )
        ) );

        wp_localize_script( 'wpuf-upload', 'wpuf_frontend_upload', array(
            'confirmMsg' => __( 'Are you sure?', 'wpuf' ),
            'nonce'      => wp_create_nonce( 'wpuf_nonce' ),
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'plupload'   => array(
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'wpuf-upload-nonce' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart'        => true,
                'urlstream_upload' => true,
            )
        ));
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
        $valid_pages  = array('admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php');

        if ( ! current_user_can( $access_level ) && !in_array( $pagenow, $valid_pages ) ) {
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
        $access_level = wpuf_get_option( 'admin_access', 'wpuf_general', 'read' );

        return current_user_can( $access_level );
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
     * @param  array  $links
     *
     * @since  2.3.3
     *
     * @return array
     */
    function plugin_action_links( $links ) {

        if ( ! $this->is_pro() ) {
            $links[] = '<a href="https://wedevs.com/products/plugins/wp-user-frontend-pro/" target="_blank">Get PRO</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=wpuf-settings' ) . '">Settings</a>';
        $links[] = '<a href="http://docs.wedevs.com/category/plugins/wp-user-frontend-pro/" target="_blank">Documentation</a>';

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
        echo '<p>Your <strong>WP User Frontend Pro</strong> License has been expired and you are now <strong>downgraded</strong> to free version. Please <a href="https://wedevs.com/account/" target="_blank">renew your license</a>.</p>';
        echo '</div>';
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

// kickoff the plugin
wpuf();