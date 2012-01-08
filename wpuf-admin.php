<?php
/**
 * Add's a option page in the admin panel
 */
function wpuf_plugin_menu() {
    add_options_page('WP User Frontend', 'WP User Frontend', 9, 'wpuf-admin-opt', 'wpuf_plugin_options');
}
add_action('admin_menu', 'wpuf_plugin_menu');

function wpuf_plugin_options() {
    global $wpdb;
    ?>
<div class="wrap">
    <h2>WP User Frontend Options</h2>
    <form method="post" action="options.php">
            <?php wp_nonce_field('update-options'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Post Status</th>
                <td>
                        <?php $post_status = get_option('wpuf_post_status'); ?>
                    <select name='wpuf_post_status' class='postform' >
                        <option <?php if($post_status == 'publish') echo "selected" ?> value="publish">Publish</option>
                        <option <?php if($post_status == 'draft') echo "selected" ?> value="draft">Draft</option>
                        <option <?php if($post_status == 'pending') echo "selected" ?> value="pending">Pending</option>
                    </select>
                    <span class="description"></span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Notification Mail</th>
                <td>
                        <?php $wpuf_notify = get_option('wpuf_notify'); ?>
                    <select name='wpuf_notify' class='postform' >
                        <option <?php if($wpuf_notify == 'yes') echo "selected" ?> value="yes">Yes</option>
                        <option <?php if($wpuf_notify == 'no') echo "selected" ?> value="no">No</option>
                    </select>
                    <span class="description">Send Notification mail to admin on Draft/Pending post status</span>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">User Can Edit Post</th>
                <td>
                        <?php $wpuf_can_edit_post = get_option('wpuf_can_edit_post'); ?>
                    <select name='wpuf_can_edit_post' class='postform' >
                        <option <?php if($wpuf_can_edit_post == 'yes') echo "selected" ?> value="yes">Yes</option>
                        <option <?php if($wpuf_can_edit_post == 'no') echo "selected" ?> value="no">No</option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">User Can Delete Post</th>
                <td>
                        <?php $wpuf_can_del_post = get_option('wpuf_can_del_post'); ?>
                    <select name='wpuf_can_del_post' class='postform'>
                        <option <?php if($wpuf_can_del_post == 'yes') echo "selected" ?> value="yes">Yes</option>
                        <option <?php if($wpuf_can_del_post == 'no') echo "selected" ?> value="no">No</option>
                    </select>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">Edit Page URL</th>
                <td>
                    <input type="text" name="wpuf_edit_page_url" value="<?php echo get_option('wpuf_edit_page_url'); ?>">
                    <span class="description">Give the address of your "Edit Page" url</span>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Admin Area Access</th>
                <td>
                    <?php $wpuf_admin_security = get_option('wpuf_admin_security'); ?>
                    <select name="wpuf_admin_security" class="postform">
                        <option <?php if($wpuf_admin_security == 'install_themes') echo "selected" ?> value="install_themes">Admins Only</option>
                        <option <?php if($wpuf_admin_security == 'edit_others_posts') echo "selected" ?> value="edit_others_posts">Admins, Editors</option>
                        <option <?php if($wpuf_admin_security == 'publish_posts') echo "selected" ?> value="publish_posts">Admins, Editors, Authors</option>
                        <option <?php if($wpuf_admin_security == 'edit_posts') echo "selected" ?> value="edit_posts">Admins, Editors, Authors, Contributors</option>
                        <option <?php if($wpuf_admin_security == 'read') echo "selected" ?> value="read">All Access</option>
                        <option <?php if($wpuf_admin_security == 'disable') echo "selected" ?> value="disable">Disable</option>
                    </select>
                    <span class="description">Only this selected levels can visit the admin area</span>
                </td>
            </tr>
        </table>

        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="page_options" value="wpuf_post_status,wpuf_notify, wpuf_can_edit_post, wpuf_can_del_post, wpuf_edit_page_url, wpuf_admin_security" />

        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>

    </form>
</div>
    <?php
}