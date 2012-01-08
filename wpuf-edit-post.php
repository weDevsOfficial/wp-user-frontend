<?php
function wpuf_edit_post_shorcode() {
    wpuf_auth_redirect_login(); // if not logged in, redirect to login page
    nocache_headers();
    wpuf_edit_post();
    add_action('wp_footer', 'wpuf_post_form_style');
}
add_shortcode('wpuf_edit', 'wpuf_edit_post_shorcode');

function wpuf_edit_post() {
    global $wpdb, $userdata;

    $post_id = wpuf_is_valid_int($_GET['pid']);

    if($post_id) {

        if (get_option('wpuf_can_edit_post') == 'yes') {

            //validate new post submission
            if(isset($_POST['wpuf_edit_post_submit'])) {
                check_admin_referer('wpuf-edit-post');
                wpuf_validate_post_edit_submit();
            }

            $curpost = get_post($post_id, 'OBJECT');

            if($curpost) {
                wpuf_edit_show_form($curpost);
            } else {
                $error = "Invalid post";
            }
        } else {
            $error = "Post Editing is disabled";
        }
    } else {
        $error = "Invalid post id";
    }

    if (isset($error)) {
        echo '<div class="error">Error: '.$error.'</div>';
    }
}

function wpuf_edit_show_form($post) {
    $post_tags = wp_get_post_tags($post->ID);
    $tagsarray = array();
    foreach ($post_tags as $tag) {
        $tagsarray[] = $tag->name;
    }
    $tagslist = implode(', ', $tagsarray);
    $categories = get_the_category($post->ID);
    ?>
<form name="wpuf_edit_post_form" action="" method="POST">
        <?php wp_nonce_field('wpuf-edit-post') ?>
    <ul class="wpuf-post-form">
        <li>
            <label for="new-post-title">
                Title <span class="required">*</span>
            </label>
            <input type="text" name="wpuf_post_title" id="new-post-title" minlength="2" value="<?php echo esc_html($post->post_title); ?>">
            <div class="clear"></div>
        </li>
        <li>
            <label for="new-post-cat">
                Category:
            </label>
                <?php wp_dropdown_categories('hierarchical=1&hide_empty=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1&selected='.$categories[0]->cat_ID) ?>
            <div class="clear"></div>
        </li>
        <li>
            <label for="new-post-desc">
                Description <span class="required">*</span>
            </label>
            <textarea name="wpuf_post_content" id="new-post-desc" cols="40" rows="8"><?php echo esc_html($post->post_content); ?></textarea>
            <div class="clear"></div>
        </li>

        <li>
            <label for="new-post-tags">
                Tags:
            </label>
            <input type="text" name="wpuf_post_tags" id="new-post-tags" value="<?php echo $tagslist; ?>">
            <div class="clear"></div>
        </li>
        <li>
            <label>&nbsp;</label>
            <input class="wpuf_submit" type="submit" name="wpuf_edit_post_submit" value="Update">
            <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
        </li>
    </ul>
</form>
    <?php
}

function wpuf_validate_post_edit_submit() {
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

    if (!$errors) {
        $post_update = array(
                'ID'            => trim($_POST['post_id']),
                'post_title'    => $title,
                'post_content'  => $content,
                'post_category' => array($cat),
                'tags_input'    => $tags
        );
        $post_id = wp_update_post($post_update);
        //var_dump($post_update);

        if ($post_id) {
            echo '<div class="success">Post updated succesfully.</div>';
        }

    } else {
        echo wpuf_error_msg($errors);
    }
}
