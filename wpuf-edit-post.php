<?php

/**
 * Handles the edit post shortcode
 *
 * @return string generated form by the plugin
 */
function wpuf_edit_post_shorcode() {

    ob_start();

    if ( is_user_logged_in() ) {
        wpuf_edit_post();
    } else {
        printf( __( "This page is restricted. Please %s to view this page.", 'wpuf' ), wp_loginout( '', false ) );
    }

    $content =  ob_get_contents();
    ob_end_clean();

    return $content;
}

add_shortcode( 'wpuf_edit', 'wpuf_edit_post_shorcode' );

/**
 * Main edit post form
 *
 * @global type $wpdb
 * @global type $userdata
 */
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
                //if ( !current_user_can( 'delete_others_posts' ) || ( intval( $userdata->ID ) != intval( $curpost->post_author ) ) ) {
                if ( !current_user_can( 'delete_others_posts' ) && ( $userdata->ID != $curpost->post_author ) ) {
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
    <form name="wpuf_edit_post_form" id="wpuf_edit_post_form" action="" enctype="multipart/form-data" method="POST">
        <?php wp_nonce_field( 'wpuf-edit-post' ) ?>
        <ul class="wpuf-post-form">

            <?php do_action( 'wpuf_add_post_form_top', $post->post_type, $post ); //plugin hook  ?>
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
                    <?php wp_dropdown_categories( 'show_option_none=' . __( '-- Select --', 'wpuf' ) . '&hierarchical=1&hide_empty=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1&class=cat requiredField&exclude=' . $exclude . '&selected=' . $selected ); ?>
                    <div class="clear"></div>
                    <p class="description"><?php echo stripslashes( get_option( 'wpuf_cat_help' ) ); ?></p>
                </li>
            <?php } ?>

            <?php do_action( 'wpuf_add_post_form_description', $post->post_type, $post ); ?>
            <?php wpuf_build_custom_field_form( 'description', true, $post->ID ); ?>

            <li>
                <label for="new-post-desc">
                    <?php echo get_option( 'wpuf_desc_label' ); ?> <span class="required">*</span>
                </label>
                <div style="float:left;">
                    <?php if ( get_option( 'wpuf_editor_type' ) == 'rich' ) { ?>
                        <?php wp_editor( $post->post_content, 'new-post-desc', array('textarea_name' => 'wpuf_post_content', 'teeny' => true, 'textarea_rows' => 8) ); ?>
                    <?php } else { ?>
                        <textarea name="wpuf_post_content" id="new-post-desc" cols="60" rows="8"><?php echo esc_textarea( $post->post_content ); ?></textarea>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </li>

            <?php do_action( 'wpuf_add_post_form_after_description', $post->post_type, $post ); ?>
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

            <?php wpuf_attachment_fields( true, $post->ID ); ?>
            
            <?php do_action( 'wpuf_add_post_form_tags', $post->post_type, $post ); ?>
            <?php wpuf_build_custom_field_form( 'bottom', true, $post->ID ); ?>

            <li>
                <label>&nbsp;</label>
                <input class="wpuf_submit" type="submit" name="wpuf_edit_post_submit" value="<?php _e( 'Update', 'wpuf' ); ?>">
                <input type="hidden" name="wpuf_edit_post_submit" value="yes" />
                <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
            </li>
        </ul>
    </form>
    
    <?php if ( get_option( 'wpuf_allow_attachments' ) == 'yes' ) { ?>
        <div class="wpuf-edit-attachment">
            <?php wpuf_edit_attachment( $post->ID ); ?>
        </div>
    <?php
    }
}

function wpuf_validate_post_edit_submit() {
    global $userdata;

    $errors = array();

    $title = trim( $_POST['wpuf_post_title'] );
    $content = trim( $_POST['wpuf_post_content'] );
    
    $tags = '';
    $cat = '';
    if( isset( $_POST['wpuf_post_tags'] ) ) {
        $tags = wpuf_clean_tags( $_POST['wpuf_post_tags'] );
    }
    
    if( isset( $_POST['cat'] ) ) {
        $cat = trim( $_POST['cat'] );
    }

    //if there is some attachement, validate them
    if ( !empty( $_FILES['wpuf_post_attachments'] ) ) {
        $errors = wpuf_check_upload();
    }

    if ( empty( $title ) ) {
        $errors[] = __( 'Empty post title', 'wpuf' );
    } else {
        $title = trim( strip_tags( $title ) );
    }

    if ( empty( $content ) ) {
        $errors[] = __( 'Empty post content', 'wpuf' );
    } else {
        $content = trim( $content );
    }

    if ( !empty( $tags ) ) {
        $tags = explode( ',', $tags );
    }

    //process the custom fields
    $custom_fields = array();

    $fields = wpuf_get_custom_fields();
    if ( is_array( $fields ) ) {

        foreach ($fields as $cf) {
            if ( array_key_exists( $cf['field'], $_POST ) ) {

                $temp = trim( strip_tags( $_POST[$cf['field']] ) );
                //var_dump($temp, $cf);

                if ( ( $cf['type'] == 'yes' ) && !$temp ) {
                    $errors[] = sprintf( __( '%s is missing', 'wpuf' ), $cf['label'] );
                } else {
                    $custom_fields[$cf['field']] = $temp;
                }
            } //array_key_exists
        } //foreach
    } //is_array

    $errors = apply_filters( 'wpuf_edit_post_validation', $errors );

    if ( !$errors ) {
        $post_update = array(
            'ID' => trim( $_POST['post_id'] ),
            'post_title' => $title,
            'post_content' => $content,
            'post_category' => array($cat),
            'tags_input' => $tags
        );

        //plugin API to extend the functionality
        $post_update = apply_filters( 'wpuf_edit_post_args', $post_update );

        $post_id = wp_update_post( $post_update );

        if ( $post_id ) {
            echo '<div class="success">' . __( 'Post updated succesfully.', 'wpuf' ) . '</div>';

            //upload attachment to the post
            wpuf_upload_attachment( $post_id );

            //add the custom fields
            if ( $custom_fields ) {
                foreach ($custom_fields as $key => $val) {
                    update_post_meta( $post_id, $key, $val, false );
                }
            }

            do_action( 'wpuf_edit_post_after_update', $post_id );
        }
    } else {
        echo wpuf_error_msg( $errors );
    }
}
