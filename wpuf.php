<?php
/*
  Plugin Name: WP User Frontend
  Plugin URI: http://tareq.wedevs.com/2011/01/new-plugin-wordpress-user-frontend/
  Description: Post, Edit, Delete posts and edit profile without coming to backend
  Author: Tareq Hasan
  Version: 0.7
  Author URI: http://tareq.weDevs.com
 */

require_once 'wpuf-functions.php';
require_once 'admin/wpuf-options-value.php';

if ( is_admin() ) {
    require_once 'admin/wpuf-admin.php';
    require_once 'admin/wpuf-admin-meta.php';
    require_once 'admin/wpuf-admin-taxonomy.php';
}

require_once 'admin/wpuf-admin-subscription.php';
require_once 'admin/wpuf-admin-transaction.php';
require_once 'wpuf-dashboard.php';
require_once 'wpuf-add-post.php';
require_once 'wpuf-edit-post.php';
require_once 'wpuf-editprofile.php';
require_once 'wpuf-edit-user.php';
require_once 'wpuf-ajax.php';

//custom hooks
require_once 'extra/custom_hooks.php';
require_once 'wpuf-subscription.php';
require_once 'lib/attachment.php';

class WPUF_Main {

    function __construct() {
        register_activation_hook( __FILE__, array($this, 'install') );
        register_deactivation_hook( __FILE__, array($this, 'uninstall') );

        add_action( 'admin_menu', array($this, 'admin_menu') );
        add_action( 'admin_init', array($this, 'block_admin_access') );

        add_action( 'init', array($this, 'load_textdomain') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
    }

    /**
     * Create tables on plugin activation
     *
     * @global object $wpdb
     */
    function install() {
        global $wpdb;

        flush_rewrite_rules( false );

        $sql_custom = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpuf_customfields (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `field` varchar(30) NOT NULL,
         `type` varchar(20) NOT NULL,
         `values` text NOT NULL,
         `label` varchar(200) NOT NULL,
         `desc` varchar(200) NOT NULL,
         `required` varchar(5) NOT NULL,
         `region` varchar(20) NOT NULL DEFAULT 'top',
         `order` int(1) NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $sql_subscription = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpuf_subscription (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `count` int(5) DEFAULT '0',
        `duration` int(5) NOT NULL DEFAULT '0',
        `cost` float NOT NULL DEFAULT '0',
        `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

        $sql_transaction = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpuf_transaction (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) DEFAULT NULL,
        `status` varchar(255) NOT NULL DEFAULT 'pending_payment',
        `cost` varchar(255) DEFAULT '',
        `post_id` bigint(20) DEFAULT NULL,
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

        $wpdb->query( $sql_custom );
        $wpdb->query( $sql_subscription );
        $wpdb->query( $sql_transaction );
    }

    function uninstall() {

    }

    /**
     * Add's a option page in the admin panel
     */
    function admin_menu() {
        $plugin_page = add_menu_page( 'WP User Frontend', 'WP User Frontend', 'activate_plugins', 'wpuf-admin-opt', 'wpuf_plugin_options', null );
        $plugin_page2 = add_submenu_page( 'wpuf-admin-opt', 'Custom Fields', 'Custom Fields', 'activate_plugins', 'wpuf_custom_fields', 'wpuf_custom_fields' );
        //$plugin_page3 = add_submenu_page( 'wpuf-admin-opt', 'Custom Taxonomies', 'Custom Taxonomies', 'activate_plugins', 'wpuf_custom_tax', 'wpuf_taxonomy_fields' );
        $plugin_page4 = add_submenu_page( 'wpuf-admin-opt', 'Subscription', 'Subscription', 'activate_plugins', 'wpuf_subscription', 'wpuf_subscription_admin' );
        $plugin_page5 = add_submenu_page( 'wpuf-admin-opt', 'Transaction', 'Transaction', 'activate_plugins', 'wpuf_transaction', 'wpuf_transaction' );

        add_action( 'admin_head-' . $plugin_page, array($this, 'admin_scripts') );
        add_action( 'admin_head-' . $plugin_page2, array($this, 'admin_scripts') );
        //add_action( 'admin_head-' . $plugin_page3, 'wpuf_admin_script' );
        add_action( 'admin_head-' . $plugin_page4, array($this, 'admin_scripts') );
    }

    /**
     * Enqueue scripts and styles for admin panel
     */
    function admin_scripts() {
        $path = plugins_url( 'wp-user-frontend' );

        wp_enqueue_script( 'wpuf_admin', $path . '/js/admin.js' );
        wp_enqueue_style( 'wpuf_admin', $path . '/css/admin.css' );
    }

    /**
     * Enqueues Styles and Scripts when the shortcodes are used only
     *
     * @uses has_shortcode()
     * @since 0.2
     */
    function enqueue_scripts() {
        $path = plugins_url( 'wp-user-frontend' );


        require_once ABSPATH . '/wp-admin/includes/template.php';

        wp_enqueue_style( 'wpuf', $path . '/css/wpuf.css' );

        if ( has_shortcode( 'wpuf_addpost' ) || has_shortcode( 'wpuf_edit' ) ) {
            wp_enqueue_script( 'plupload-handlers' );
        }

        wp_enqueue_script( 'wpuf', $path . '/js/wpuf.js', array('jquery') );

        $posting_msg = get_option( 'wpuf_post_submitting_label', 'Please wait...' );
        wp_localize_script( 'wpuf', 'wpuf', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'postingMsg' => $posting_msg,
            'confirmMsg' => __( 'Are you sure?', 'wpuf' ),
            'nonce' => wp_create_nonce( 'wpuf_nonce' ),
            'plupload' => array(
                'runtimes' => 'html5,silverlight,flash,html4',
                'browse_button' => 'wpuf-ft-upload-pickfiles',
                'container' => 'wpuf-ft-upload-container',
                'file_data_name' => 'wpuf_featured_img',
                'max_file_size' => wp_max_upload_size() . 'b',
                'url' => admin_url( 'admin-ajax.php' ) . '?action=wpuf_featured_img&nonce=' . wp_create_nonce( 'wpuf_featured_img' ),
                'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
                'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
                'filters' => array(array('title' => __( 'Allowed Files' ), 'extensions' => '*')),
                'multipart' => true,
                'urlstream_upload' => true,
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

        $access_level = get_option( 'wpuf_admin_security', 'read' );
        $valid_pages = array('admin-ajax.php', 'async-upload.php', 'media-upload.php');

        if ( !current_user_can( $access_level ) && !in_array( $pagenow, $valid_pages ) ) {
            wp_die( __( 'Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'wpuf' ) );
        }
    }

    /**
     * Load the translation file for current language.
     *
     * @since version 0.7
     * @author Tareq Hasan
     */
    function load_textdomain() {
        $locale = apply_filters( 'wpuf_locale', get_locale() );
        $mofile = dirname( __FILE__ ) . "/languages/wpuf-$locale.mo";

        if ( file_exists( $mofile ) ) {
            load_textdomain( 'wpuf', $mofile );
        }
    }

}

$wpuf = new WPUF_Main();