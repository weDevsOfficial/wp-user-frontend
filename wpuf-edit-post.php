<?php

function wpuf_edit_post_shorcode() {
    if ( is_user_logged_in() ) {
        wpuf_edit_post();
    } else {
        printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( '', false ) );
    }

    add_action( 'wp_footer', 'wpuf_post_form_style' );
}

add_shortcode( 'wpuf_edit', 'wpuf_edit_post_shorcode' );

function wpuf_edit_post() {
    global $wpdb, $userdata;

    $post_id = wpuf_is_valid_int( $_GET['pid'] );

    if ( $post_id ) {

        if ( get_option( 'wpuf_can_edit_post' ) == 'yes' ) {

            //delete post attachment
            if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == "del" ) {
                check_admin_referer( 'wpuf_attach_del' );
                $attach_id = intval( $_REQUEST['attach_id'] );

                if ( $attach_id ) {
                    wp_delete_attachment( $attach_id );
                }
            }

            //validate new post submission
            $nonce = $_REQUEST['_wpnonce'];
            if ( isset( $_POST['wpuf_edit_post_submit'] ) && wp_verify_nonce( $nonce, 'wpuf-edit-post' ) ) {
                wpuf_validate_post_edit_submit();
            }

            $curpost = get_post( $post_id, 'OBJECT' );

            if ( $curpost ) {
                if ( intval( $userdata->ID ) != intval( $curpost->post_author ) ) {
                    wp_redirect( site_url() );
                    exit;
                }

                wpuf_edit_show_form( $curpost );
            } else {
                $error = __( 'Invalid post', 'wpuf' );
            }
        } else {
            $error = __( 'Post Editing is disabled', 'wpuf' );
        }
    } else {
        $error = __( 'Invalid post id', 'wpuf' );
    }

    if ( isset( $error ) ) {
        echo '<div class="error">Error: ' . $error . '</div>';
    }
}

function wpuf_edit_show_form( $post ) {
    $post_tags = wp_get_post_tags( $post->ID );
    $tagsarray = array();
    foreach ($post_tags as $tag) {
        $tagsarray[] = $tag->name;
    }
    $tagslist = implode( ', ', $tagsarray );
    $categories = get_the_category( $post->ID );
    ?>
    <form name="wpuf_edit_post_form" action="" enctype="multipart/form-data" method="POST">
        <?php wp_nonce_field( 'wpuf-edit-post' ) ?>
        <ul class="wpuf-post-form">
            <?php wpuf_build_custom_field_form( 'top', true, $post->ID ); ?>
            <li>
                <label for="new-post-title">
                    <?php echo get_option( 'wpuf_title_label' ); ?> <span class="required">*</span>
                </label>
                <input type="text" name="wpuf_post_title" id="new-post-title" minlength="2" value="<?php echo esc_html( $post->post_title ); ?>">
                <div class="clear"></div>
            </li>

            <?php if ( get_option( 'wpuf_allow_choose_cat' ) == 'yes' ) { ?>
                <li>
                    <label for="new-post-cat">
                        <?php echo get_option( 'wpuf_cat_label' ); ?> <span class="required">*</span>
                    </label>
                    <?php
                    $exclude = get_option( 'wpuf_exclude_cat' );
                    $cats = get_the_category( $post->ID );
                    $selected = 0;
                    if ( $cats ) {
                        $selected = $cats[0]->term_id;
                    }
                    ?>
                    <?php wp_dropdown_categories( 'show_option_none=-- Select --&hierarchical=1&hide_empty=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1&class=cat requiredField&exclude=' . $exclude . '&selected=' . $selected ); ?>
                    <div class="clear"></div>
                    <p class="description"><?php echo stripslashes( get_option( 'wpuf_cat_help' ) ); ?></p>
                </li>
            <?php } ?>

            <?php wpuf_build_custom_field_form( 'description', true, $post->ID ); ?>

            <li>
                <label for="new-post-desc">
                    <?php echo get_option( 'wpuf_desc_label' ); ?> <span class="required">*</span>
                </label>
                <div style="float:left;">
                    <?php if ( get_option( 'wpuf_editor_type' ) == 'rich' ) { ?>
                        <?php wp_editor( esc_html( $post->post_content ), 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'teeny' => true, 'textarea_rows' => 8) ); ?>
                    <?php } else { ?>
                        <textarea name="wpuf_post_content" id="new-post-desc" cols="60" rows="8"></textarea>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </li>

            <?php wpuf_build_custom_field_form( 'tag', true, $post->ID ); ?>

            <?php if ( get_option( 'wpuf_allow_tags' ) == 'yes' ) { ?>
                <li>
                    <label for="new-post-tags">
                        <?php echo get_option( 'wpuf_tag_label' ); ?>
                    </label>
                    <input type="text" name="wpuf_post_tags" id="new-post-tags" value="<?php echo $tagslist; ?>">
                    <div class="clear"></div>
                </li>
            <?php } ?>

            <?php wpuf_attachment_fields(); ?>
            <?php wpuf_edit_attachment( $post->ID ); ?>
            <?php wpuf_build_custom_field_form( 'bottom', true, $post->ID ); ?>

            <li>
                <label>&nbsp;</label>
                <input class="wpuf_submit" type="submit" name="wpuf_edit_post_submit" value="<?php _e( 'Update', 'wpuf' ); ?>">
                <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
            </li>
        </ul>
    </form>
    <?php
}

function wpuf_validate_post_edit_submit() {
    global $userdata;

    $errors = array();

    $title = trim( $_POST['wpuf_post_title'] );
    $content = trim( $_POST['wpuf_post_content'] );
    $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
    $cat = trim( $_POST['cat'] );

    //if there is some attachement, validate them
    if ( !empty( $_FILES['wpuf_post_attachments'] ) ) {
        $errors = wpuf_check_upload();
    }

    if ( empty( $title ) ) {
        $errors[] = "Empty post title";
    } else {
        $title = trim( strip_tags( $title ) );
    }

    if ( empty( $content ) ) {
        $errors[] = "Empty post content";
    } else {
        $content = trim( $content );
    }

    if ( !empty( $tags ) ) {
        $tags = explode( ',', $tags );
    }

    do_action( 'wpuf_edit_post_validation' );

    if ( !$errors ) {
        $post_update = array(
            'ID' => trim( $_POST['post_id'] ),
            'post_title' => $title,
            'post_content' => $content,
            'post_category' => array($cat),
            'tags_input' => $tags
        );

        $post_id = wp_update_post( $post_update );

        if ( $post_id ) {
            echo '<div class="success">Post updated succesfully.</div>';

            //upload attachment to the post
            wpuf_upload_attachment( $post_id );

            do_action( 'wpuf_eidt_post_after_update' );
        }
    } else {
        echo wpuf_error_msg( $errors );
    }
}
