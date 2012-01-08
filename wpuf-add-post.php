<?php
function wpuf_add_post_shorcode() {
    wpuf_auth_redirect_login(); // if not logged in, redirect to login page
    nocache_headers();
    wpuf_add_post();
    add_action('wp_footer', 'wpuf_post_form_style');
}
add_shortcode('wpuf_addpost', 'wpuf_add_post_shorcode');


function wpuf_add_post() {

    //validate new post submission
    if(isset($_POST['wpuf_new_post_submit'])) {
        check_admin_referer('wpuf-add-post');
        wpuf_validate_post_submit();
    }

    ?>
<form name="wpuf_new_post_form" action="" method="POST">
        <?php wp_nonce_field('wpuf-add-post') ?>
    <ul class="wpuf-post-form">
        <li>
            <label for="new-post-title">
                Title <span class="required">*</span>
            </label>
            <input type="text" name="wpuf_post_title" id="new-post-title" minlength="2">
            <div class="clear"></div>
        </li>
        <li>
            <label for="new-post-cat">
                Category:
            </label>
                <?php wp_dropdown_categories('hierarchical=1&hide_empty=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1') ?>
            <div class="clear"></div>
        </li>
        <li>
            <label for="new-post-desc">
                Description <span class="required">*</span>
            </label>
            <textarea name="wpuf_post_content" id="new-post-desc" cols="40" rows="8"></textarea>
            <div class="clear"></div>
        </li>

        <li>
            <label for="new-post-tags">
                Tags:
            </label>
            <input type="text" name="wpuf_post_tags" id="new-post-tags">
            <div class="clear"></div>
        </li>
        <li>
            <label>&nbsp;</label>
            <input class="wpuf_submit" type="submit" name="wpuf_new_post_submit" value="Post!">
        </li>
    </ul>
</form>
    <?php
}

function wpuf_validate_post_submit() {
    global $userdata;

    $errors = array();

    $title      = trim($_POST['wpuf_post_title']);
    $content    = trim($_POST['wpuf_post_content']);
    $tags       = wpuf_clean_tags($_POST['wpuf_post_tags']);
    $cat        = trim($_POST['cat']);

    if (empty($title)) {
        $errors[] = "Empty post title";
    } else {
        $title = trim(strip_tags($title));
    }

    if (empty($content)) {
        $errors[] = "Empty post content";
    } else {
        $content = trim($content);
    }

    if (!empty($tags)) {
        $tags = explode(',', $tags);
    }

    $post_status = (get_option('wpuf_post_status')) ? get_option('wpuf_post_status') : 'publish';

    if (!$errors) {
        $my_post = array(
                'post_title'    => $title,
                'post_content'  => $content,
                'post_status'   => $post_status,
                'post_author'   => $userdata->ID,
                'post_category' => array($cat),
                'tags_input'    => $tags
        );
        $post_id = wp_insert_post($my_post);

        if ($post_id) {
            echo '<div class="success">Post Published succesfully.</div>';

            //send mail notification
            if(get_option('wpuf_notify') == 'yes') {
                wpuf_notify_post_mail();
            }
        }

    } else {
        echo wpuf_error_msg($errors);
    }
}
