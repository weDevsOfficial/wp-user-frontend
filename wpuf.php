<?php

/*
  Plugin Name: WordPress User Frontend
  Plugin URI: http://tareq.wedevs.com/2011/01/new-plugin-wordpress-user-frontend/
  Description: Post, Edit, Delete posts and edit profile without coming to backend
  Author: Tareq Hasan
  Version: 0.2
  Author URI: http://tareq.weDevs.com
 */

require_once 'wpuf-functions.php';
require_once 'admin/wpuf-options-value.php';
require_once 'admin/wpuf-admin.php';
require_once 'admin/wpuf-admin-meta.php';
require_once 'admin/wpuf-admin-taxonomy.php';
require_once 'admin/wpuf-admin-subscription.php';
require_once 'admin/wpuf-admin-transaction.php';
require_once 'wpuf-dashboard.php';
require_once 'wpuf-add-post.php';
require_once 'wpuf-edit-post.php';
require_once 'wpuf-editprofile.php';
require_once 'wpuf-edit-user.php';

//custom hooks
require_once 'extra/custom_hooks.php';
require_once 'wpuf-subscription.php';

register_activation_hook( __FILE__, 'wpuf_install' );
register_deactivation_hook( __FILE__, 'wpuf_uninstall' );

function wpuf_install() {
    global $wpdb;

    wpuf_register_mysettings();

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

function wpuf_uninstall() {
    wpuf_remove_my_settings();
}

/**
 * Registers some default option when installing
 */
function wpuf_register_mysettings() {
    $settings = array(
        'wpuf_post_status' => 'publish',
        'wpuf_notify' => 'yes',
        'wpuf_can_edit_post' => 'yes',
        'wpuf_can_del_post' => 'yes',
        'wpuf_admin_security' => 'read',
        'wpuf_title_label' => 'Title',
        'wpuf_cat_label' => 'Category',
        'wpuf_desc_label' => 'Description',
        'wpuf_tag_label' => 'Tags',
        'wpuf_post_submit_label' => 'Submit Post!'
    );

    foreach ($settings as $key => $value) {
        update_option( $key, $value );
    }
}

/**
 * Removes all the settings upon plugin uninstall
 */
function wpuf_remove_my_settings() {
    $settings = array(
        'wpuf_post_status',
        'wpuf_notify',
        'wpuf_can_edit_post',
        'wpuf_can_del_post',
        'wpuf_edit_page_url',
        'wpuf_admin_security'
    );

    foreach ($settings as $value) {
        delete_option( $value );
    }
}

/**
 * Add's a option page in the admin panel
 */
function wpuf_plugin_menu() {
    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    $plugin_page = add_menu_page( 'WP User Frontend', 'WP User Frontend', 'activate_plugins', 'wpuf-admin-opt', 'wpuf_plugin_options', null );

    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function )
    $plugin_page2 = add_submenu_page( 'wpuf-admin-opt', 'Custom Fields', 'Custom Fields', 'activate_plugins', 'wpuf_custom_fields', 'wpuf_custom_fields' );

    $plugin_page3 = add_submenu_page( 'wpuf-admin-opt', 'Custom Taxonomies', 'Custom Taxonomies', 'activate_plugins', 'wpuf_custom_tax', 'wpuf_taxonomy_fields' );

    $plugin_page4 = add_submenu_page( 'wpuf-admin-opt', 'Subscription', 'Subscription', 'activate_plugins', 'wpuf_subscription', 'wpuf_subscription_admin' );

    $plugin_page5 = add_submenu_page( 'wpuf-admin-opt', 'Transaction', 'Transaction', 'activate_plugins', 'wpuf_transaction', 'wpuf_transaction' );

    add_action( 'admin_head-' . $plugin_page, 'wpuf_admin_header_style' );
    add_action( 'admin_head-' . $plugin_page2, 'wpuf_admin_header_style' );
    add_action( 'admin_head-' . $plugin_page3, 'wpuf_admin_header_style' );
    add_action( 'admin_head-' . $plugin_page4, 'wpuf_admin_header_style' );

    add_action( 'admin_head-' . $plugin_page, 'wpuf_admin_header_script' );
    add_action( 'admin_head-' . $plugin_page2, 'wpuf_admin_header_script' );
    add_action( 'admin_head-' . $plugin_page3, 'wpuf_admin_header_script' );
    add_action( 'admin_head-' . $plugin_page4, 'wpuf_admin_header_script' );
}

add_action( 'admin_menu', 'wpuf_plugin_menu' );

function wpuf_admin_header_style() {
    $path = plugins_url( 'wp-user-frontend' );

    echo "<link rel='stylesheet' href='$path/css/admin.css' type='text/css'/>";
}

function wpuf_admin_header_script() {
    $path = plugins_url( 'wp-user-frontend' );

    echo "<script src='$path/js/admin.js'></script>";
}

function wpuf_restrict_admin_access() {

    $wpuf_access_level = get_option( 'wpuf_admin_security' );
    if ( !isset( $wpuf_access_level ) || $wpuf_access_level == '' )
        $wpuf_access_level = 'read'; // if there's no value then give everyone access

    if ( !current_user_can( $wpuf_access_level ) ) {
        wp_die( "Access Denied. Your site administrator has blocked your access to the WordPress back-office." );
    }
}

if ( is_admin() ) {
    add_action( 'admin_init', 'wpuf_restrict_admin_access' );
}

// display msg if permalinks aren't setup correctly
function wpuf_permalink_nag() {

    if ( current_user_can( 'manage_options' ) )
        $msg = sprintf( __( 'You need to set your <a href="%1$s">permalink custom structure</a> to at least contain <b>/&#37;postname&#37;/</b> before WP User Frontend will work properly.', '' ), 'options-permalink.php' );

    echo "<div class='error fade'><p>$msg</p></div>";
}

//if not found %postname%, shows a error msg at admin panel
if ( !stristr( get_option( 'permalink_structure' ), '%postname%' ) ) {
    add_action( 'admin_notices', 'wpuf_permalink_nag', 3 );
}

function wpuf_option_values() {
    global $custom_fields;

    wpuf_value_travarse( $custom_fields );
}

function wpuf_value_travarse( $param ) {
    foreach ($param as $key => $value) {
        if ( $value['name'] ) {
            echo '"' . $value['name'] . '" => "' . get_option( $value['name'] ) . '"<br>';
        }
    }
}

//wpuf_option_values();

function wpuf_get_custom_fields() {
    global $wpdb;

    $data = array();

    $fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_customfields", OBJECT );
    if ( $wpdb->num_rows > 0 ) {
        foreach ($fields as $f) {
            $data[] = array(
                'label' => $f->label,
                'field' => $f->field,
                'type' => $f->required
            );
        }

        return $data;
    }

    return false;
}

/**
 * Returns child category dropdown on ajax request
 */
function wpuf_get_child_cats() {
    $parentCat = $_POST['catID'];
    $result = '';
    if ( $parentCat < 1 )
        die( $result );

    if ( get_categories( 'taxonomy=category&child_of=' . $parentCat . '&hide_empty=0' ) ) {
        $result .= wp_dropdown_categories( 'show_option_none=' . __( 'Select one', 'wpuf' ) . '&class=dropdownlist&orderby=name&order=ASC&hide_empty=0&hierarchical=1&taxonomy=category&depth=1&echo=0&child_of=' . $parentCat );
    } else {
        die( '' );
    }

    die( $result );
}

add_action( 'wp_ajax_nopriv_wpuf_get_child_cats', 'wpuf_get_child_cats' );
add_action( 'wp_ajax_wpuf_get_child_cats', 'wpuf_get_child_cats' );