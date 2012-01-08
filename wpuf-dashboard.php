<?php
/**
 * Handle's user dashboard functionality
 *
 * Insert shortcode [wpuf_dashboard] in a page to
 * show the user dashboard
 *
 * @since Version 0.1
 * @author Tareq Hasan
 */
function wpuf_user_dashboard() {

    wpuf_auth_redirect_login(); // if not logged in, redirect to login page
    nocache_headers();
    wpuf_user_dashboard_post_list();
    add_action('wp_footer', 'wpuf_dashboard_style');
}

add_shortcode('wpuf_dashboard', 'wpuf_user_dashboard');

/**
 * List's all the posts by the user
 *
 * @since version 0.1
 * @author Tareq Hasan
 *
 * @global object $wpdb
 * @global object $userdata
 */
function wpuf_user_dashboard_post_list() {
    global $wpdb, $userdata;

    get_currentuserinfo(); // grabs the user info and puts into vars

    //delete post
    if ($_REQUEST['action'] == "del")
    {
        check_admin_referer('wpuf_del');
        wp_delete_post($_REQUEST['pid']);
        echo '<div class="success">Post Deleted</div>';
    }

    //get the posts
    $sql = "SELECT ID, post_title, post_name, post_status, post_date "
            . "FROM $wpdb->posts "
            . "WHERE post_author = $userdata->ID AND post_type = 'post' "
            . "AND (post_status = 'publish' OR post_status = 'pending' OR post_status = 'draft') ";

    $posts = $wpdb->get_results($sql);
    $count = 1;
    //d($posts);
    ?>
<h2 class="page-head">
    <span class="colour"><?php printf(__("%s's Dashboard", 'your-gig'), $userdata->user_login); ?></span>
</h2>
    <?php if ($posts): ?>
<table class="wpuf-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Options</th>
        </tr>
    </thead>
    <tbody>
        <?php wp_reset_query() ?>
        <?php foreach($posts as $p): ?>
        <tr>
            <td>
                <?php if ($p->post_status == 'pending' || $p->post_status == 'draft') { ?>

                        <?php echo wptexturize($p->post_title); ?>

                <?php } else { ?>

                        <a href="<?php echo get_permalink($p->ID) ?>"><?php echo wptexturize( $p->post_title ); ?></a>

                <?php } ?>
            </td>
            <td>
                <?php wpuf_show_post_status($p->post_status) ?>
            </td>
            <td>
                <?php if(get_option('wpuf_can_edit_post') == 'yes'): ?>
                <?php $edit_page = get_option('wpuf_edit_page_url'); ?>
                    <a href="<?php echo $edit_page ?>?pid=<?php echo $p->ID ?>">Edit</a>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
                    
                <?php if(get_option('wpuf_can_del_post') == 'yes'): ?>
                    <a href="<?php echo wp_nonce_url("?action=del&pid=".$p->ID, 'wpuf_del') ?>" onclick="return confirm('Are you sure to delete this post?');"><span style="color: red;">Delete</span></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <?php else: ?>
        <h3>You currently have no post</h3>
    <?php endif;
}

function wpuf_dashboard_style() {
    ?>
<style type="text/css">
table.wpuf-table {
    border: 1px solid #E7E7E7;
    margin: 0 -1px 24px 0;
    text-align: left;
    width: 100%;
}

table.wpuf-table thead th, table.wpuf-table th {
    color: #888888;
    font-size: 12px;
    font-weight: bold;
    line-height: 18px;
    padding: 9px 24px;
}

table.wpuf-table td {
    border-top: 1px solid #E7E7E7;
    padding: 6px 24px;
}
.success {
    background-color: #DFF2BF;
    border: 1px solid #BCDF7D;
    color: #4F8A10;
    padding: 10px;
    font-size: 13px;
    font-weight: bold;
    margin-bottom: 10px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    text-shadow: 0 1px 0 #FFFFFF;
}
</style>
    <?php
}