<?php
/*
Plugin Name: Wordpress User Frontend
Plugin URI: http://tareq.wedevs.com/2011/01/new-plugin-wordpress-user-frontend/
Description: Post, Edit, Delete posts and edit profile without coming to backend
Author: Tareq Hasan
Version: 0.1
Author URI: http://tareq.weDevs.com
*/

require_once 'wpuf-functions.php';
require_once 'wpuf-admin.php';
require_once 'wpuf-dashboard.php';
require_once 'wpuf-add-post.php';
require_once 'wpuf-edit-post.php';
require_once 'wpuf-editprofile.php';

register_activation_hook( __FILE__ , 'wpuf_install' );
register_deactivation_hook( __FILE__ , 'wpuf_uninstall' );

function wpuf_install() {
    wpuf_register_mysettings();
}

function wpuf_uninstall() {
    wpuf_remove_my_settings();
}

/**
 * Registers some default option when installing
 */
function wpuf_register_mysettings() {
    $settings = array(
            'wpuf_post_status'      => 'published',
            'wpuf_notify'           => 'yes',
            'wpuf_can_edit_post'    => 'yes',
            'wpuf_can_del_post'     => 'yes',
            'wpuf_admin_security'   => 'read'
    );

    foreach ($settings as $key => $value) {
        update_option($key, $value);
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
        delete_option($value);
    }
}


function wpuf_restrict_admin_access() {

    $wpuf_access_level = get_option('wpuf_admin_security');
    if (!isset($wpuf_access_level)) $wpuf_access_level = 'read'; // if there's no value then give everyone access

    if ( !current_user_can($wpuf_access_level) ) {
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php _e('Access Denied.') ?></title>
        <link rel="stylesheet" href="<?php bloginfo('url'); ?>/wp-admin/css/install.css" type="text/css" />
    </head>
    <body id="error-page">
        <p><?php _e('Access Denied. Your site administrator has blocked your access to the WordPress back-office.', 'your-gig') ?></p>
    </body>
</html>
        <?php
        exit();
    }
}

if(is_admin()) {
    add_action('admin_init', 'wpuf_restrict_admin_access');
}

// display msg if permalinks aren't setup correctly
function wpuf_permalink_nag() {

    if (current_user_can('manage_options'))
        $msg = sprintf( __('You need to set your <a href="%1$s">permalink custom structure</a> to at least contain <b>/&#37;postname&#37;/</b> before WP User Frontend will work properly.', 'cp'), 'options-permalink.php');

    echo "<div class='error fade'><p>$msg</p></div>";
}

if (!stristr(get_option('permalink_structure'), '%postname%'))
    add_action('admin_notices', 'wpuf_permalink_nag', 3);

